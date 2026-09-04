<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use DB;

class t_hasil_tes extends \App\Models\BasicModels\t_hasil_tes
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $joins = [
        "t_pelamar.id=t_hasil_tes.t_pelamar_id",
        "t_loker.id=t_hasil_tes.t_loker_id",
        "m_general.id=t_hasil_tes.tahapan_id",
        "default_users.id=t_hasil_tes.creator_id",
        "default_users.id=t_hasil_tes.last_editor_id"
    ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[
            'nomor'  => $this->helper->generateNomor('KODE HASIL TES PELAMAR'),
            'status' => $arrayData['status'] ?? 'PENDING'
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_postData($request)
    {
        $data = t_hasil_tes::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $update = $data->update([
                'status' => "POSTED"
            ]);

            if ($update) {
                return response()->json(['message' => 'Data berhasil diposting.']);
            } else {
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            // Handle exception, log error messages, etc.
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function custom_registerKary($request)
    {
      $data = t_hasil_tes::find($request->id);

      if (!$data) {
          return response()->json(['error' => 'Data tidak ditemukan.'], 404);
      }

        try {

            $pelamar = t_pelamar::find($data->t_pelamar_id);
            $loker = t_loker::find($data->t_loker_id);

            // dd($loker);

            if (!$pelamar) {
                return response()->json(['error' => 'Data pelamar terkait tidak ditemukan.'], 404);
            }

            $kode = @$this->helper->generateNomor("KODE KARYAWAN");

            $kary = m_kary::updateOrCreate(
                  ['nik' => $pelamar->no_ktp], 
                  [
                    'ref_id'          => $pelamar->id,
                    'm_comp_id'       => $loker->m_comp_id ?? null,
                    'm_subcomp_id'    => $loker->m_subcomp_id ?? null,
                    'm_branch_id'     => $loker->m_branch_id ?? null,
                    'm_divisi_id'     => $loker->m_divisi_id ?? null,
                    'm_posisi_id'     => $loker->m_posisi_id ?? null,
                    
                    'nip'             => $kode,
                    'kode'            => $kode,
                    'no_registrasi'   => substr($kode, -10),
                    'nik'             => $pelamar->ktp_no,
                    'nama_depan'      => $pelamar->nama_depan,
                    'nama_belakang'   => $pelamar->nama_belakang,
                    'nama_lengkap'    => $pelamar->nama_lengkap,
                    'nama_panggilan'  => $pelamar->nama_lengkap,
                    
                    // Data Personal
                    'jk_id'           => $pelamar->jk_id,
                    'tempat_lahir'    => $pelamar->tempat_lahir,
                    'tgl_lahir'       => $pelamar->tgl_lahir,
                    'email'           => $pelamar->email,
                    'no_tlp'          => $pelamar->telp,
                    
                    // Sosial Media
                    'ig'              => $pelamar->ig,
                    'x'               => $pelamar->x,
                    'facebook'        => $pelamar->facebook,
                    'linkedin'        => $pelamar->linkedin,
                    
                    // Status & Tanggal
                    'tgl_masuk'       => Carbon::now()->toDateString(),
                    'is_active'       => true,
                    'is_sync'         => false,
                    'status_kary_id'  => $loker->status_kary_id ?? null,
                    
                    // Audit Trail
                    'creator_id'      => auth()->id() ?? $req->creator_id,
                    'last_editor_id'  => auth()->id() ?? $req->last_editor_id,
                ]
              );

              DB::table('m_kary_det_jabatan')
                  ->where(function($q) use ($kary) {
                      $q->where('m_karyawan_id', $kary->id)
                        ->orWhere('m_kary_id', $kary->id);
                  })
                  ->delete();

              // Insert Jabatan baru berdasarkan Loker yang dilamar
              DB::table('m_kary_det_jabatan')->insert([
                  'm_kary_id'      => $kary->id,
                  'm_karyawan_id'  => $kary->id,
                  'm_comp_id'      => $loker->m_comp_id ?? null,
                  'm_subcomp_id'   => $loker->m_subcomp_id ?? null, // Sesuaikan jika ada di t_loker
                  'm_branch_id'    => $loker->m_branch_id ?? null,
                  'm_divisi_id'    => $loker->m_divisi_id ?? null,
                  'm_posisi_id'    => $loker->m_posisi_id ?? null,
                  'start_time'     => null, // Tanggal mulai menjabat (hari ini)
                  'end_time'       => null,                  // Kosong karena jabatan aktif
                  'desc'           => 'Jabatan awal dari hasil seleksi loker: ' . ($loker->nomor ?? ''),
                  'is_primary'     => true,                  // Jabatan utama
                  'is_active'      => true,
                  'creator_id'     => auth()->id() ?? $req->creator_id,
                  'last_editor_id' => auth()->id() ?? $req->last_editor_id,
                  'created_at'     => Carbon::now(),
              ]);

              // --- Detail Organisasi ---
              DB::table('m_kary_det_org')->where('m_kary_id', $kary->id)->delete();
              foreach ($pelamar->t_pelamar_det_org as $det) {
                  DB::table('m_kary_det_org')->insert(array_merge($det->toArray(), ['m_kary_id' => $kary->id]));
              }

              // --- Detail Pelatihan ---
              DB::table('m_kary_det_pel')->where('m_kary_id', $kary->id)->delete();
              foreach ($pelamar->t_pelamar_det_pel as $det) {
                  DB::table('m_kary_det_pel')->insert(array_merge($det->toArray(), ['m_kary_id' => $kary->id]));
              }

              // --- Detail Pendidikan ---
              DB::table('m_kary_det_pend')->where('m_kary_id', $kary->id)->delete();
              foreach ($pelamar->t_pelamar_det_pend as $det) {
                  DB::table('m_kary_det_pend')->insert(array_merge($det->toArray(), ['m_kary_id' => $kary->id]));
              }

              // --- Detail Pengalaman Kerja ---
              DB::table('m_kary_det_pk')->where('m_kary_id', $kary->id)->delete();
              foreach ($pelamar->t_pelamar_det_pk as $det) {
                  DB::table('m_kary_det_pk')->insert(array_merge($det->toArray(), ['m_kary_id' => $kary->id]));
              }

              // --- Detail Prestasi ---
              DB::table('m_kary_det_pres')->where('m_kary_id', $kary->id)->delete();
              foreach ($pelamar->t_pelamar_det_pres as $det) {
                  DB::table('m_kary_det_pres')->insert(array_merge($det->toArray(), ['m_kary_id' => $kary->id]));
              }
              



              if ($kary) {
                  // Opsional: Update status di tabel hasil tes bahwa pelamar sudah jadi karyawan
                  $data->update(['status' => 'HIRED']);
                  
                  return response()->json([
                      'success' => true,
                      'message' => 'Registrasi karyawan berhasil.',
                      'data'    => $kary
                  ]);
              }

            if ($update) {
                return response()->json(['message' => 'Registrasi karyawan berhasil.']);
            } else {
                return response()->json(['error' => 'Gagal registrasi karyawan.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function custom_addTahapanColumn()
    {
        try {
            \DB::statement("ALTER TABLE t_hasil_tes ADD COLUMN IF NOT EXISTS tahapan_id BIGINT NULL");
            return response()->json(['success' => true, 'message' => 'Kolom tahapan_id berhasil ditambahkan ke tabel t_hasil_tes!']);
        } catch (\Throwable $e) {
            try {
                \DB::statement("ALTER TABLE t_hasil_tes ADD tahapan_id BIGINT NULL");
                return response()->json(['success' => true, 'message' => 'Kolom tahapan_id berhasil ditambahkan ke tabel t_hasil_tes!']);
            } catch (\Throwable $err) {
                return response()->json(['error' => $err->getMessage()], 500);
            }
        }
    }
}