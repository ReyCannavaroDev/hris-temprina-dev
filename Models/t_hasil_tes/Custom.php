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

        $this->heirs = array_values(array_unique(array_merge($this->heirs, [
            "t_hasil_tes_det",
        ])));
        $this->detailsChild = array_values(array_unique(array_merge($this->detailsChild, [
            "t_hasil_tes_det",
        ])));

        if (app()->request->isMethod('GET')) {
            $this->details = [];
            $this->detailsChild = [];
        }
    }

    public $details = ['t_hasil_tes_det'];
    
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

    public function transformRowData(array $row)
    {
        $data = [];
        $details = \DB::table('t_hasil_tes_det')
            ->where('t_hasil_tes_id', $row['id'])
            ->orderBy('id', 'asc')
            ->get();
        $detArr = json_decode(json_encode($details), true);
        foreach ($detArr as &$d) {
            if (isset($d['nilai_tes']) && is_numeric($d['nilai_tes'])) {
                $d['nilai_tes'] = $d['nilai_tes'] + 0;
            }
        }
        $data['t_hasil_tes_det'] = $detArr;

        return array_merge($row, $data);
    }

    public function t_hasil_tes_det(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_hasil_tes_det', 't_hasil_tes_id', 'id');
    }

    private function prepareDetailRequest($id = null)
    {
        $req = app()->request;
        $details = $req->t_hasil_tes_det ?? [];

        if (empty($details)) {
            if ($id) {
                \App\Models\BasicModels\t_hasil_tes_det::where('t_hasil_tes_id', $id)->delete();
            }
            $this->details = [];
            return;
        }

        $cleanDetails = [];
        foreach ($details as $det) {
            $det = is_array($det) ? $det : (array) $det;
            $cleanRow = [
                'tanggal'   => !empty($det['tanggal']) ? $det['tanggal'] : date('Y-m-d'),
                'nama_tes'  => $det['nama_tes'] ?? null,
                'nilai_tes' => isset($det['nilai_tes']) && $det['nilai_tes'] !== '' && is_numeric($det['nilai_tes']) ? floatval($det['nilai_tes']) : null,
                'dokumen'   => $det['dokumen'] ?? null,
            ];

            if ($id && !empty($det['id'])) {
                $isExisting = \App\Models\BasicModels\t_hasil_tes_det::where('id', $det['id'])
                    ->where('t_hasil_tes_id', $id)
                    ->exists();
                if ($isExisting) {
                    $cleanRow['id'] = $det['id'];
                }
            }

            $cleanDetails[] = $cleanRow;
        }

        if ($id) {
            $keepIds = array_values(array_filter(array_map(function ($row) {
                return $row['id'] ?? null;
            }, $cleanDetails)));

            $deleteQuery = \App\Models\BasicModels\t_hasil_tes_det::where('t_hasil_tes_id', $id);
            if (!empty($keepIds)) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();
        }

        $req->merge(['t_hasil_tes_det' => $cleanDetails]);
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->prepareDetailRequest();

        $t_loker_id = $arrayData['t_loker_id'] ?? null;
        $t_pelamar_id = $arrayData['t_pelamar_id'] ?? null;
        $tahapan_id = $arrayData['tahapan_id'] ?? null;

        // Proteksi Duplikasi: 1 Pelamar hanya boleh memiliki 1 data transaksi hasil tes
        if (!empty($t_pelamar_id)) {
            $isDuplicate = \DB::table('t_hasil_tes')
                ->where('t_pelamar_id', $t_pelamar_id)
                ->exists();

            if ($isDuplicate) {
                return [
                    "model"  => $model,
                    "data"   => $arrayData,
                    "errors" => ["Pelamar ini sudah memiliki data transaksi hasil tes."]
                ];
            }
        }

        $inputStatus = strtoupper($arrayData['status'] ?? 'PENDING');
        if (!in_array($inputStatus, ['PENDING', 'PROSES'])) {
            $inputStatus = 'PENDING';
        }

        $newArrayData = array_merge($arrayData, [
            'nomor'  => $this->helper->generateNomor('KODE HASIL TES PELAMAR'),
            'status' => $inputStatus
        ]);

        return [
            "model" => $model,
            "data"  => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        if (!empty($arrayData['t_pelamar_id'])) {
            \DB::table('t_pelamar')
                ->where('id', $arrayData['t_pelamar_id'])
                ->update(['status' => 'PROSES']);
        }
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?? $model->id ?? null;
        if ($targetId) {
            $existing = \DB::table('t_hasil_tes')->where('id', $targetId)->first();
            if ($existing) {
                $st = strtoupper($existing->status ?? '');
                if (!in_array($st, ['PENDING', 'DRAFT', 'REVISED', ''])) {
                    return [
                        "model"  => $model,
                        "data"   => $arrayData,
                        "errors" => ["Data hasil tes dengan status {$st} sudah terkunci dan tidak dapat diedit kembali."]
                    ];
                }
            }
        }

        if (isset($arrayData['status'])) {
            $inputStatus = strtoupper($arrayData['status']);
            if (!in_array($inputStatus, ['PENDING', 'PROSES', 'REVISED'])) {
                unset($arrayData['status']);
            }
        }

        $this->prepareDetailRequest($id);

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?? $model->id ?? null;
        if ($targetId) {
            $existing = \DB::table('t_hasil_tes')->where('id', $targetId)->first();
            if ($existing) {
                $st = strtoupper($existing->status ?? '');
                if (in_array($st, ['HALF APPROVED', 'DITERIMA'])) {
                    throw new \Exception("Data hasil tes dengan status {$st} sudah diproses dan tidak dapat dihapus.");
                }

                // Kembalikan status pelamar menjadi POSTED
                if (!empty($existing->t_pelamar_id)) {
                    \DB::table('t_pelamar')
                        ->where('id', $existing->t_pelamar_id)
                        ->update(['status' => 'POSTED']);
                }
            }

            // Hapus tiket approval jika ada
            $app = \DB::table('generate_approval')->where('trx_table', $this->getTable())->where('trx_id', $targetId)->first();
            if ($app) {
                \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->delete();
                \DB::table('generate_approval')->where('id', $app->id)->delete();
            }
        }
    }

    private function createAppTicket($id, $target_id = null)
    {
        $trx = $this->find($id);
        if (!$trx) return false;

        $user = auth()->user();

        // AUTO-INJECT MASTER APPROVAL JIKA BELUM ADA DI DATABASE
        $hasApprovalConfig = \DB::table('m_approval_det as d')
            ->join('m_approval as a', 'a.id', '=', 'd.m_approval_id')
            ->where('a.name', 'APPROVAL HASIL TES PELAMAR')
            ->exists();

        if (!$hasApprovalConfig) {
            $master_app = \DB::table('m_approval')
                ->where('name', 'APPROVAL HASIL TES PELAMAR')
                ->first();

            $other_app = \DB::table('m_approval')->whereNotNull('m_menu_id')->first();
            $menu = \DB::table('m_menu')
                ->where('endpoint', 'ILIKE', '%hasil_tes%')
                ->orWhere('path', 'ILIKE', '%hasil_tes%')
                ->orWhere('menu', 'ILIKE', '%hasil%tes%')
                ->first();
            $menu_id = $menu ? $menu->id : ($other_app ? $other_app->m_menu_id : 1);

            if (!$master_app) {
                $m_approval_id = \DB::table('m_approval')->insertGetId([
                    'm_comp_id'  => $user->m_comp_id ?? 1,
                    'm_dir_id'   => $user->m_dir_id ?? 1,
                    'm_menu_id'  => $menu_id,
                    'name'       => 'APPROVAL HASIL TES PELAMAR',
                    'is_active'  => 1,
                    'creator_id' => $user->id ?? 1,
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            } else {
                $m_approval_id = $master_app->id;
            }

            $hasDet = \DB::table('m_approval_det')->where('m_approval_id', $m_approval_id)->exists();
            if (!$hasDet) {
                \DB::table('m_approval_det')->insert([
                    'm_approval_id' => $m_approval_id,
                    'm_role_id'     => 1,
                    'level'         => 1,
                    'type'          => 'MENYETUJUI',
                    'name'          => 'USER APPROVAL',
                    'creator_id'    => $user->id ?? 1,
                    'created_at'    => \Carbon\Carbon::now(),
                ]);
            }
        }

        // Cari pemohon FPTK dari loker terkait
        $loker = \DB::table('t_loker')->where('id', $trx->t_loker_id)->first();
        $targetUserId = null;
        if ($target_id) {
            $targetUserId = $target_id;
        } elseif ($loker && !empty($loker->t_req_recruitment_id)) {
            $fptk = \DB::table('t_req_recruitment')->where('id', $loker->t_req_recruitment_id)->first();
            if ($fptk && !empty($fptk->creator_id)) {
                $targetUserId = $fptk->creator_id;
            } elseif ($fptk && !empty($fptk->m_kary_id)) {
                $targetUserId = \DB::table('default_users')->where('m_kary_id', $fptk->m_kary_id)->value('id');
            }
        }

        $conf = [
            "app_name"       => "APPROVAL HASIL TES PELAMAR",
            "trx_id"         => $trx->id,
            "trx_table"      => $this->getTable(),
            "trx_name"       => "Hasil Test Lamaran Kerja",
            "form_name"      => "t_hasil_test",
            "trx_nomor"      => $trx->nomor,
            "trx_date"       => date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
            "target_id"      => $targetUserId,
        ];

        return $this->helper->approvalCreateTicket($conf);
    }

    public function custom_send_approval()
    {
        $target_id = req("target_id");
        $user_target = $target_id ? \App\Models\BasicModels\default_users::where('m_kary_id', $target_id)->first()?->id : null;

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse("Terjadi kesalahan, coba kembali nanti", 400);
        }

        $data = $this->find(req("id"));
        if ($data) {
            $data->update([
                "status" => "PROSES",
            ]);
            if ($data->t_pelamar_id) {
                \DB::table('t_pelamar')
                    ->where('id', $data->t_pelamar_id)
                    ->update(['status' => 'PROSES']);
            }
        }

        return $this->helper->customResponse("Permintaan approval hasil tes berhasil dikirim");
    }

    public function custom_progress($req)
    {
        \DB::beginTransaction();
        try {
            $note = $req->note;
            if (empty($note) && $req->type === 'APPROVED') {
                $note = "Approved by " . (auth()->user()->name ?? 'User');
            }

            // Lookup app_id dari trx_id jika frontend mengirimkan trx_id
            $appRecord = \DB::table('generate_approval')
                ->where('trx_table', $this->getTable())
                ->where('trx_id', $req->id)
                ->orderBy('id', 'desc')
                ->first();

            $app_id = $appRecord ? $appRecord->id : $req->id;

            $conf = [
                "app_id"   => $app_id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED
                "app_note" => $note,
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    if ($req->type === 'APPROVED') {
                        // Persetujuan Tahap 1 oleh User / Manager Pemohon FPTK -> status HALF APPROVED
                        $data->update([
                            "status" => "HALF APPROVED"
                        ]);
                        if ($data->t_pelamar_id) {
                            \DB::table('t_pelamar')
                                ->where('id', $data->t_pelamar_id)
                                ->update(['status' => 'PROSES']);
                        }
                    } elseif ($req->type === 'REJECTED') {
                        $data->update([
                            "status" => "TIDAK DITERIMA"
                        ]);
                        if ($data->t_pelamar_id) {
                            \DB::table('t_pelamar')
                                ->where('id', $data->t_pelamar_id)
                                ->update(['status' => 'DITOLAK']);
                        }
                    } elseif ($req->type === 'REVISED') {
                        $data->update([
                            "status" => "REVISED"
                        ]);
                        if ($data->t_pelamar_id) {
                            \DB::table('t_pelamar')
                                ->where('id', $data->t_pelamar_id)
                                ->update(['status' => 'PROSES']);
                        }
                    }
                } else {
                    $data->update([
                        "status" => "PROSES",
                    ]);
                    if ($data->t_pelamar_id) {
                        \DB::table('t_pelamar')
                            ->where('id', $data->t_pelamar_id)
                            ->update(['status' => 'PROSES']);
                    }
                }
            }

            \DB::commit();
            return $this->helper->customResponse("Proses approval berhasil diproses");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_detail($req)
    {
        $id = $req->id ?? 0;
        
        // Find app_id from trx_id (or if $id is already app_id)
        $app = \DB::table('generate_approval')
            ->where('trx_table', $this->getTable())
            ->where('trx_id', $id)
            ->orderBy('id', 'desc')
            ->first();

        $app_id = $app ? $app->id : $id;
        $data = $this->helper->approvalDetail($app_id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_log($req)
    {
        $conf = [
            "trx_id"    => $req->id ?? 0,
            "trx_table" => $this->getTable(),
        ];
        $data = $this->helper->approvalLog($conf);
        return response($data);
    }

    public function custom_approveHC()
    {
        $req = app()->request;

        try {
            \DB::beginTransaction();

            $data = $this->find($req->id);

            if (!$data) {
                return $this->helper->customResponse("Data tidak ditemukan", 404);
            }

            // Update status menjadi DITERIMA (Final Approval oleh Head of HC)
            $data->update([
                'status' => 'DITERIMA'
            ]);

            if ($data->t_pelamar_id) {
                \DB::table('t_pelamar')
                    ->where('id', $data->t_pelamar_id)
                    ->update(['status' => 'DITERIMA']);
            }

            // Auto-sync pelamar ke Master Karyawan & trigger update kuota loker dinamis
            $this->syncPelamarToKaryawan($data);
            \App\Models\CustomModels\t_loker::updateStatusLoker($data->t_loker_id);

            $this->logHc($data->id);

            \DB::commit();
            return $this->helper->customResponse("Approval HC berhasil. Data pelamar resmi diterima dan disinkronkan ke Master Karyawan.", 200);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error("Error Approve HC Hasil Tes: " . $e->getMessage());

            return $this->helper->customResponse(
                "Terjadi kesalahan sistem: " . $e->getMessage(),
                500
            );
        }
    }

    public function logHc($trxId)
    {
        $app = \DB::table('generate_approval')
            ->where('trx_id', $trxId)
            ->where('trx_table', $this->getTable())
            ->first();

        if ($app) {
            \DB::table('generate_approval_log')->insert([
                'nomor'                    => $app->nomor,
                'generate_approval_id'     => $app->id,
                'generate_approval_det_id' => null,
                'trx_id'                   => $app->trx_id,
                'trx_table'                => $app->trx_table,
                'trx_name'                 => $app->trx_name,
                'trx_nomor'                => $app->trx_nomor,
                'trx_date'                 => $app->trx_date,
                'form_name'                => $app->form_name,
                'trx_creator_id'           => $app->trx_creator_id,
                'action_type'              => 'APPROVED',
                'action_user_id'           => auth()->user()?->id ?? 1,
                'creator_id'               => auth()->user()?->id ?? 1,
                'action_at'                => \Carbon\Carbon::now(),
                'action_note'              => 'DIKETAHUI & DISETUJUI OLEH HC'
            ]);
        }
    }

    public function custom_postData($request)
    {
        $data = t_hasil_tes::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $update = $data->update([
                'status' => "PROSES"
            ]);

            if ($update) {
                return response()->json(['message' => 'Data berhasil diproses.']);
            } else {
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function syncPelamarToKaryawan($data)
    {
        try {
            $pelamar = \App\Models\BasicModels\t_pelamar::find($data->t_pelamar_id);
            $loker = \App\Models\BasicModels\t_loker::find($data->t_loker_id);

            if (!$pelamar) return false;

            $kode = $this->helper->generateNomor("KODE KARYAWAN");

            $kary = \App\Models\BasicModels\m_kary::updateOrCreate(
                ['nik' => $pelamar->ktp_no],
                [
                    'm_comp_id'       => $loker->m_comp_id ?? null,
                    'm_subcomp_id'    => $loker->m_subcomp_id ?? null,
                    'm_branch_id'     => $loker->m_branch_id ?? null,
                    'm_divisi_id'     => $loker->m_divisi_id ?? null,
                    'm_posisi_id'     => $loker->m_posisi_id ?? null,
                    'kode'            => $kode,
                    'nik'             => $pelamar->ktp_no,
                    'nama_depan'      => $pelamar->nama_depan,
                    'nama_belakang'   => $pelamar->nama_belakang,
                    'nama_lengkap'    => $pelamar->nama_lengkap ?? ($pelamar->nama_depan . ' ' . $pelamar->nama_belakang),
                    'nama_panggilan'  => $pelamar->nama_panggilan ?? $pelamar->nama_depan,
                    'jk_id'           => $pelamar->jk_id,
                    'tempat_lahir'    => $pelamar->tempat_lahir,
                    'tgl_lahir'       => $pelamar->tgl_lahir,
                    'email'           => $pelamar->email,
                    'no_tlp'          => $pelamar->telp,
                    'ig'              => $pelamar->ig,
                    'x'               => $pelamar->x,
                    'facebook'        => $pelamar->facebook,
                    'linkedin'        => $pelamar->linkedin,
                    'tgl_masuk'       => Carbon::now()->toDateString(),
                    'is_active'       => true,
                    'status_kary_id'  => $loker->status_kary_id ?? null,
                    'creator_id'      => auth()->id() ?? $data->creator_id,
                    'last_editor_id'  => auth()->id() ?? $data->last_editor_id,
                ]
            );

            // 1. Detail Jabatan
            \DB::table('m_kary_det_jabatan')
                ->where('m_kary_id', $kary->id)
                ->delete();

            \DB::table('m_kary_det_jabatan')->insert([
                'm_kary_id'      => $kary->id,
                'm_comp_id'      => $loker->m_comp_id ?? null,
                'm_subcomp_id'   => $loker->m_subcomp_id ?? null,
                'm_branch_id'    => $loker->m_branch_id ?? null,
                'm_divisi_id'    => $loker->m_divisi_id ?? null,
                'm_posisi_id'    => $loker->m_posisi_id ?? null,
                'desc'           => 'Jabatan awal dari seleksi loker: ' . ($loker->nomor ?? ''),
                'is_primary'     => true,
                'is_active'      => true,
                'creator_id'     => auth()->id() ?? $data->creator_id,
                'created_at'     => Carbon::now(),
            ]);

            // 2. Detail Organisasi
            $pOrg = \DB::table('t_pelamar_det_org')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pOrg->isNotEmpty()) {
                \DB::table('m_kary_det_org')->where('m_kary_id', $kary->id)->delete();
                foreach ($pOrg as $row) {
                    \DB::table('m_kary_det_org')->insert([
                        'm_kary_id'     => $kary->id,
                        'nama'          => $row->nama ?? null,
                        'tahun'         => $row->tahun ?? null,
                        'jenis_org_id'  => $row->jenis_org_id ?? null,
                        'kota_id'       => $row->kota_id ?? null,
                        'posisi'        => $row->posisi ?? null,
                        'desc'          => $row->desc ?? null,
                        'created_at'    => Carbon::now()
                    ]);
                }
            }

            // 3. Detail Pelatihan
            $pPel = \DB::table('t_pelamar_det_pel')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pPel->isNotEmpty()) {
                \DB::table('m_kary_det_pel')->where('m_kary_id', $kary->id)->delete();
                foreach ($pPel as $row) {
                    \DB::table('m_kary_det_pel')->insert([
                        'm_kary_id'  => $kary->id,
                        'nama_pel'   => $row->nama_pel ?? null,
                        'tahun'      => $row->tahun ?? null,
                        'nama_lem'   => $row->nama_lem ?? null,
                        'kota_id'    => $row->kota_id ?? null,
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            // 4. Detail Pendidikan
            $pPend = \DB::table('t_pelamar_det_pend')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pPend->isNotEmpty()) {
                \DB::table('m_kary_det_pend')->where('m_kary_id', $kary->id)->delete();
                foreach ($pPend as $row) {
                    \DB::table('m_kary_det_pend')->insert([
                        'm_kary_id'         => $kary->id,
                        'tingkat_id'        => $row->tingkat_id ?? null,
                        'nama_sekolah'      => $row->nama_sekolah ?? null,
                        'tahun_masuk'       => $row->tahun_masuk ?? null,
                        'tahun_lulus'       => $row->tahun_lulus ?? null,
                        'kota_id'           => $row->kota_id ?? null,
                        'nilai'             => $row->nilai ?? null,
                        'jurusan'           => $row->jurusan ?? null,
                        'is_pend_terakhir'  => $row->is_pend_terakhir ?? 0,
                        'ijazah_no'         => $row->ijazah_no ?? null,
                        'ijazah_foto'       => $row->ijazah_foto ?? null,
                        'created_at'        => Carbon::now()
                    ]);
                }
            }

            // 5. Detail Pengalaman Kerja Formal (PK)
            $pPk = \DB::table('t_pelamar_det_pk')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pPk->isNotEmpty()) {
                \DB::table('m_kary_det_pk')->where('m_kary_id', $kary->id)->delete();
                foreach ($pPk as $row) {
                    \DB::table('m_kary_det_pk')->insert([
                        'm_kary_id'        => $kary->id,
                        'instansi'         => $row->instansi ?? null,
                        'bidang_usaha'     => $row->bidang_usaha ?? null,
                        'no_tlp'           => $row->no_tlp ?? null,
                        'posisi'           => $row->posisi ?? null,
                        'thn_masuk'        => $row->thn_masuk ?? null,
                        'thn_keluar'       => $row->thn_keluar ?? null,
                        'alamat_kantor'    => $row->alamat_kantor ?? null,
                        'kota_id'          => $row->kota_id ?? null,
                        'surat_referensi'  => $row->surat_referensi ?? null,
                        'created_at'       => Carbon::now()
                    ]);
                }
            }

            // 6. Detail Prestasi
            $pPres = \DB::table('t_pelamar_det_pres')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pPres->isNotEmpty()) {
                \DB::table('m_kary_det_pres')->where('m_kary_id', $kary->id)->delete();
                foreach ($pPres as $row) {
                    \DB::table('m_kary_det_pres')->insert([
                        'm_kary_id'        => $kary->id,
                        'nama_pres'        => $row->nama_pres ?? null,
                        'tahun'            => $row->tahun ?? null,
                        'tingkat_pres_id'  => $row->tingkat_pres_id ?? null,
                        'desc'             => $row->desc ?? null,
                        'created_at'       => Carbon::now()
                    ]);
                }
            }

            // 7. Detail Bahasa
            $pBhs = \DB::table('t_pelamar_det_bhs')->where('t_pelamar_id', $pelamar->id)->get();
            if ($pBhs->isNotEmpty()) {
                \DB::table('m_kary_det_bhs')->where('m_kary_id', $kary->id)->delete();
                foreach ($pBhs as $row) {
                    \DB::table('m_kary_det_bhs')->insert([
                        'm_kary_id'       => $kary->id,
                        'bhs_dikuasai'    => $row->bhs_dikuasai ?? null,
                        'nilai_lisan'     => $row->nilai_lisan ?? null,
                        'level_lisan'     => $row->level_lisan ?? null,
                        'nilai_tertulis'  => $row->nilai_tertulis ?? null,
                        'level_tertulis'  => $row->level_tertulis ?? null,
                        'desc'            => $row->desc ?? null,
                        'created_at'      => Carbon::now()
                    ]);
                }
            }

            return $kary;
        } catch (\Exception $e) {
            \Log::error("Error syncPelamarToKaryawan: " . $e->getMessage());
            return false;
        }
    }

    public function custom_registerKary($request)
    {
        $data = t_hasil_tes::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $res = $this->syncPelamarToKaryawan($data);
        if ($res) {
            $data->update(['status' => 'DITERIMA']);
            \App\Models\CustomModels\t_loker::updateStatusLoker($data->t_loker_id);
            return response()->json([
                'success' => true,
                'message' => 'Registrasi karyawan berhasil.',
                'data'    => $res
            ]);
        }

        return response()->json(['error' => 'Gagal registrasi karyawan.'], 500);
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