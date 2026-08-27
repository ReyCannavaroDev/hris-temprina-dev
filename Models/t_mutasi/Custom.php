<?php

namespace App\Models\CustomModels;

class t_mutasi extends \App\Models\BasicModels\t_mutasi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        'file_dokumen'
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $arrayData['nomor'] ?? $this->helper->generateNomor("KODE MUTASI"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
    public function scopelanding($model)
    {
        return $model->join('m_general', 'm_general.id', 't_mutasi.jenis_surat');
    }

    public function custom_post($request)
    {
        try {
            \DB::beginTransaction();

            $data = t_mutasi::find($request->id);
            if (!$data) return response()->json(["error" => "Data tidak ditemukan."], 404);

            $karyawan = m_kary::find($data["m_kary_id"]);
            if (!$karyawan) return response()->json(["error" => "Data karyawan tidak ditemukan."], 404);

            // Ambil Kode Tipe Surat dari m_general
            $tipeSurat = m_general::where('group', 'JENIS SURAT')->where('id', $data['jenis_surat'])->first();
            $kodeSurat = $tipeSurat ? $tipeSurat->code : '';

            // 1. Logika Update Jabatan
            if (in_array($kodeSurat, ['J12', 'J09', 'J02'])) { 
                // DEMOSI (J12), PROMOSI (J09), MUTASI (J02) -> Non-aktifkan jabatan lama
                m_kary_det_jabatan::where(function($q) use ($karyawan) {
                        $q->where('m_karyawan_id', $karyawan->id)
                          ->orWhere('m_kary_id', $karyawan->id);
                    })
                    ->where('is_active', true)
                    ->where('is_primary', true)
                    ->update([
                        'end_time' => date('Y-m-d'),
                        'is_primary' => false,
                        'is_active' => false,
                    ]);
            } 
            
            // 2. Tambah Jabatan Baru
            $isPrimaryNew = ($kodeSurat === 'J05') ? false : true;
            // dd($isPrimaryNew, $kodeSurat, $tipeSurat);
            $newJabatan = m_kary_det_jabatan::create([
                'm_kary_id'     => $karyawan->id,
                'm_karyawan_id' => $karyawan->id,
                'm_comp_id'     => $data["m_sbu_baru_id"],
                'm_subcomp_id'  => $data["m_sub_baru_id"],
                'm_branch_id'   => $data["m_branch_baru_id"],
                'm_divisi_id'   => $data["m_divisi_baru_id"],
                'm_posisi_id'   => $data["m_posisi_baru_id"],
                'start_time'    => $data['tgl'],
                'is_primary'    => $isPrimaryNew,
                'is_active'     => true,
            ]);

            // 3. Logika Update Data Utama Karyawan
            // Jika Penambahan Tugas, data profil utama karyawan (m_posisi_id, dll) biasanya tidak berubah
            if ($kodeSurat !== 'J05') {
                $updateDataKary = [
                    "m_comp_id"      => $data["m_sbu_baru_id"],
                    "m_subcomp_id"   => $data["m_sub_baru_id"],
                    "m_divisi_id"    => $data["m_divisi_baru_id"],
                    "m_posisi_id"    => $data["m_posisi_baru_id"],
                    "m_branch_id"    => $data["m_branch_baru_id"],
                ];

                // Khusus J07 (PENGANGKATAN) -> Update status karyawan
                if ($kodeSurat === 'J07') {
                    $updateDataKary["status_kary_id"] = $data['status_kary_baru_id'];
                    $updateDataKary["tgl_pengangkatan"] = $data['tgl'];
                }

                $karyawan->update($updateDataKary);
            }

            // 4. Update Jadwal Kerja (Biasanya mengikuti jadwal baru meskipun penambahan tugas)
            if(isset($data['t_jadwal_kerja_baru_id'])){
                t_jadwal_kerja_d_n::where('m_kary_id', $karyawan->id)
                ->where('status', 'AKTIF')
                ->update(['status' => 'NON AKTIF']);

                t_jadwal_kerja_d_n::create([
                    't_jadwal_kerja_n_id' => $data['t_jadwal_kerja_baru_id'],
                    'm_subcomp_id'        => $data["m_sub_baru_id"],
                    'm_branch_id'         => $data["m_branch_baru_id"],
                    'm_divisi_id'         => $data["m_divisi_baru_id"],
                    'm_kary_id'           => $data['m_kary_id'],
                    'start_date'          => $data['tgl'],
                    'desc'                => 'AUTO GENERATE FROM ' . ($tipeSurat->value ?? 'MUTASI'),
                    'status'              => 'AKTIF'
                ]);
            }

            // Finalize
            $data->update(["status" => "POSTED"]);
            
            \DB::commit();
            return response()->json(["message" => "Proses " . ($tipeSurat->value ?? 'Mutasi') . " berhasil diposting."]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(["error" => "Terjadi kesalahan: " . $e->getMessage()], 500);
        }
    }

    // public function custom_post($request)
    // {
    //     try {
    //         // Begin a database transaction
    //         \DB::beginTransaction();

    //         $data = t_mutasi::find($request->id);
    //         if (!$data) {
    //             return response()->json(
    //                 ["error" => "Data tidak ditemukan."],
    //                 404
    //             );
    //         }

    //         $karyawan = m_kary::find($data["m_kary_id"]);
    //         if (!$karyawan) {
    //             return response()->json(
    //                 ["error" => "Data karyawan tidak ditemukan."],
    //                 404
    //             );
    //         }
    //         $update = $data->update([
    //             "status" => "POSTED",
    //         ]);

    //         $updateKary = $karyawan->update([
    //             "m_comp_id" => $data["m_sbu_baru_id"],
    //             "m_subcomp_id" => $data["m_sub_baru_id"],
    //             "m_divisi_id" => $data["m_divisi_baru_id"],
    //             "m_posisi_id" => $data["m_posisi_baru_id"],
    //             "m_branch_id" => $data["m_branch_baru_id"],
    //             "status_kary_id" => $data['status_kary_baru_id']
    //         ]);

    //         $updateLastJabatan = m_kary_det_jabatan::orderBy('start_time', 'desc')->where('m_karyawan_id',$karyawan['id'])->first();
    //         if($updateLastJabatan){
    //             $updateLastJabatan->update([
    //                 'end_time' => date('Y-m-d'),
    //                 'is_primary' => false,
    //             ]);
    //         }

    //         $updateDetailJabatan = m_kary_det_jabatan::create([
    //             'm_karyawan_id' => $karyawan['id'],
    //             'm_comp_id' => $data["m_sbu_baru_id"],
    //             'm_subcomp_id' => $data["m_sub_baru_id"],
    //             'm_branch_id' => $data["m_branch_baru_id"],
    //             'm_divisi_id' => $data["m_divisi_baru_id"],
    //             'm_posisi_id' => $data["m_posisi_baru_id"],
    //             'start_time' => date('Y-m-d'),
    //             'end_time' => null,
    //             'is_primary' => true,
    //             'is_active' => true,
    //         ]);

    //         $updateLastJadwal = t_jadwal_kerja_d_n::orderBy('start_time', 'desc')->where('m_kary_id',$karyawan['id'])->first();
    //         if($updateLastJadwal){
    //             $updateLastJadwal->update([
    //                 // 'end_time' => date('Y-m-d'),
    //                 // 'is_primary' => false,
    //                 'status' => 'NON AKTIF'
    //             ]);
    //         }

    //         $updateJadwal = t_jadwal_kerja_d_n::create([
    //             't_jadwal_kerja_n_id' => $data['t_jadwal_kerja_baru_id'],
    //             'm_subcomp_id' => $data["m_sub_baru_id"],
    //             'm_branch_id' => $data["m_branch_baru_id"],
    //             'm_divisi_id' => $data["m_divisi_baru_id"],
    //             'm_kary_id' => $data['m_kary_id'],
    //             'start_date' => $data['tgl'],
    //             'desc' => 'AUTO GENERATE FROM MUTASI',
    //             'status' => 'AKTIF'
    //         ]);

    //         if ($update && $updateKary && $updateDetailJabatan && $updateLastJadwal && $updateJadwal) {
    //             // If both updates are successful, commit the transaction
    //             \DB::commit();

    //             return response()->json([
    //                 "message" => "Data berhasil diposting.",
    //             ]);
    //         } else {
    //             // If any update fails, rollback the transaction
    //             \DB::rollBack();

    //             return response()->json(
    //                 ["error" => "Gagal memperbarui status."],
    //                 500
    //             );
    //         }
    //     } catch (\Exception $e) {
    //         // Handle exception, log error messages, etc.

    //         // Rollback the transaction in case of any exception
    //         \DB::rollBack();

    //         return response()->json(
    //             ["error" => "Terjadi kesalahan: " . $e->getMessage()],
    //             500
    //         );
    //     }
    // }
}
