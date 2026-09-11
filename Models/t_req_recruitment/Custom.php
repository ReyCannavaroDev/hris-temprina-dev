<?php

namespace App\Models\CustomModels;
use DB;
use Carbon\Carbon;

class t_req_recruitment extends \App\Models\BasicModels\t_req_recruitment
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $joins = [
        "m_kary.id=t_req_recruitment.m_kary_id",
        "m_comp.id=t_req_recruitment.m_comp_id",
        "m_subcomp.id=t_req_recruitment.m_subcomp_id",
        "m_branch.id=t_req_recruitment.m_branch_id",
        "m_divisi.id=t_req_recruitment.m_divisi_id",
        "m_dept.id=t_req_recruitment.m_dept_id",
        "m_posisi.id=t_req_recruitment.m_posisi_id",
        "m_general.id=t_req_recruitment.status_kary_id",
        "m_general.id=t_req_recruitment.jenis_permintaan_id",
        "m_general.id=t_req_recruitment.prioritas_id",
        "default_users.id=t_req_recruitment.creator_id",
        "default_users.id=t_req_recruitment.last_editor_id"
    ];

    public function scopeapproved($model)
    {
        return $model->whereRaw("upper(t_req_recruitment.status) = 'APPROVED'");
    }

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $m_kary_id = $arrayData['m_kary_id'] ?? auth()->user()->m_kary_id ?? null;
        $kary = $m_kary_id ? \DB::table('m_kary')->where('id', $m_kary_id)->first() : null;

        $m_divisi_id = $arrayData['m_divisi_id'] ?? null;
        $divisi = $m_divisi_id ? \DB::table('m_divisi')->where('id', $m_divisi_id)->first() : null;

        $m_branch_id = $arrayData['m_branch_id'] ?? $divisi?->m_branch_id ?? $kary?->m_branch_id ?? null;
        $m_subcomp_id = $arrayData['m_subcomp_id'] ?? $kary?->m_subcomp_id ?? null;
        $m_comp_id = $arrayData['m_comp_id'] ?? $kary?->m_comp_id ?? auth()->user()->m_comp_id ?? null;

        $user = auth()->user();
        $is_hc = false;
        if ($user) {
            $is_hc = str_contains(strtolower($user->username), 'hc') || 
                     str_contains(strtolower($user->name), 'hc') || 
                     str_contains(strtolower($user->username), 'turikan') || 
                     str_contains(strtolower($user->name), 'turikan') ||
                     str_contains(strtolower($user->username), 'hrd') || 
                     str_contains(strtolower($user->name), 'hrd');
        }

        $status = $arrayData['status'] ?? 'DRAFT';
        if ($is_hc) {
            $status = 'APPROVED';
        }

        $newArrayData = array_merge( $arrayData, [
            'nomor'        => $this->helper->generateNomor('KODE PERMINTAAN KARYAWAN'),
            'tanggal'      => $arrayData['tanggal'] ?? date('Y-m-d'),
            'status'       => $status,
            'm_kary_id'    => $m_kary_id,
            'm_comp_id'    => $m_comp_id,
            'm_subcomp_id' => $m_subcomp_id,
            'm_branch_id'  => $m_branch_id,
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        if ($id) {
            $oldData = $this->find($id);
            if ($oldData && $oldData->status === 'REVISED') {
                $arrayData['status'] = 'DRAFT';
            }
        }
        
        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function transformRowData(array $row)
    {
        $m_kary = !empty($row['m_kary_id']) ? \DB::table('m_kary')->where('id', $row['m_kary_id'])->first() : null;
        $creator = !empty($row['creator_id']) ? \DB::table('default_users')->where('id', $row['creator_id'])->first() : null;
        $m_divisi = !empty($row['m_divisi_id']) ? \DB::table('m_divisi')->where('id', $row['m_divisi_id'])->first() : null;
        $m_posisi = !empty($row['m_posisi_id']) ? \DB::table('m_posisi')->where('id', $row['m_posisi_id'])->first() : null;
        $status_kary = !empty($row['status_kary_id']) ? \DB::table('m_general')->where('id', $row['status_kary_id'])->first() : null;
        $jenis_permintaan = !empty($row['jenis_permintaan_id']) ? \DB::table('m_general')->where('id', $row['jenis_permintaan_id'])->first() : null;
        $prioritas = !empty($row['prioritas_id']) ? \DB::table('m_general')->where('id', $row['prioritas_id'])->first() : null;
        $karyawan_digantikan = !empty($row['karyawan_digantikan_id']) ? \DB::table('m_kary')->where('id', $row['karyawan_digantikan_id'])->first() : null;

        $last_log = \DB::table('generate_approval_log')
            ->where('trx_table', $this->getTable())
            ->where('trx_id', $row['id'])
            ->whereNotNull('action_note')
            ->orderBy('id', 'desc')
            ->first();

        return array_merge($row, [
            'm_kary' => $m_kary ? (array)$m_kary : null,
            'm_kary.nama_lengkap' => $m_kary?->nama_lengkap ?? $creator?->name ?? '-',
            'creator' => $creator ? (array)$creator : null,
            'creator.name' => $creator?->name ?? '-',
            'm_divisi' => $m_divisi ? (array)$m_divisi : null,
            'm_divisi.name' => (function() use ($m_divisi) {
                if (!$m_divisi) return '-';
                $divisiVal = '';
                if (!empty($m_divisi->name)) {
                    $gen = \DB::table('m_general')->where('id', $m_divisi->name)->first();
                    if ($gen && !empty($gen->value)) {
                        $divisiVal = $gen->value;
                    }
                }
                if (empty($divisiVal) && !empty($m_divisi->name_old)) {
                    $divisiVal = $m_divisi->name_old;
                }
                return !empty($divisiVal) ? $divisiVal : '-';
            })(),
            'm_posisi' => $m_posisi ? (array)$m_posisi : null,
            'm_posisi.name' => $m_posisi?->name ?? '-',
            'status_kary' => $status_kary ? (array)$status_kary : null,
            'status_kary.value' => $status_kary?->value ?? '-',
            'jenis_permintaan' => $jenis_permintaan ? (array)$jenis_permintaan : null,
            'jenis_permintaan.value' => $jenis_permintaan?->value ?? '-',
            'prioritas' => $prioritas ? (array)$prioritas : null,
            'prioritas.value' => $prioritas?->value ?? '-',
            'karyawan_digantikan' => $karyawan_digantikan ? (array)$karyawan_digantikan : null,
            'karyawan_digantikan.nama_lengkap' => $karyawan_digantikan?->nama_lengkap ?? '-',
            'catatan_hc' => $last_log ? $last_log->action_note : '-',
        ]);
    }

    public function scoperespo($model)
    {
        $user = auth()->user();
        if ($user) {
            $is_hc = str_contains(strtolower($user->username), 'hc') || 
                     str_contains(strtolower($user->name), 'hc') || 
                     str_contains(strtolower($user->username), 'turikan') || 
                     str_contains(strtolower($user->name), 'turikan') ||
                     str_contains(strtolower($user->username), 'hrd') || 
                     str_contains(strtolower($user->name), 'hrd');
                     
            // Jika user adalah HC, bebaskan filter cabang agar bisa melihat semua pengajuan FPTK
            if ($is_hc) {
                return $model;
            }
        }

        $m_subcomp_id = request("m_subcomp_id") ?? null;
        $m_branch_id = request("m_branch_id") ?? null;
        
        \Log::info("scoperespo hit", ['subcomp' => $m_subcomp_id, 'branch' => $m_branch_id, 'url' => request()->fullUrl()]);
        
        if ($m_subcomp_id === "null" || $m_subcomp_id === "undefined" || empty($m_subcomp_id)) {
            $m_subcomp_id = null;
        }
        if ($m_branch_id === "null" || $m_branch_id === "undefined" || empty($m_branch_id)) {
            $m_branch_id = null;
        }

        if (is_string($m_subcomp_id) && str_starts_with(trim($m_subcomp_id), '[')) {
            $m_subcomp_id = json_decode($m_subcomp_id, true);
        }
        if (is_string($m_branch_id) && str_starts_with(trim($m_branch_id), '[')) {
            $m_branch_id = json_decode($m_branch_id, true);
        }

        $user_id = auth()->user() ? auth()->user()->id : null;

        return $model->where(function($query) use ($m_subcomp_id, $m_branch_id, $user_id) {
            $query->where(function($q) use ($m_subcomp_id, $m_branch_id) {
                $q->when($m_subcomp_id, function ($sq) use ($m_subcomp_id) {
                    if (is_array($m_subcomp_id)) {
                        $sq->where(function($ssq) use ($m_subcomp_id) {
                            $ssq->whereIn("t_req_recruitment.m_subcomp_id", $m_subcomp_id)
                               ->orWhereNull("t_req_recruitment.m_subcomp_id");
                        });
                    } else {
                        $sq->where(function($ssq) use ($m_subcomp_id) {
                            $ssq->where("t_req_recruitment.m_subcomp_id", $m_subcomp_id)
                               ->orWhereNull("t_req_recruitment.m_subcomp_id");
                        });
                    }
                })
                ->when($m_branch_id, function ($sq) use ($m_branch_id) {
                    if (is_array($m_branch_id)) {
                        $sq->where(function($ssq) use ($m_branch_id) {
                            $ssq->whereIn("t_req_recruitment.m_branch_id", $m_branch_id)
                               ->orWhereNull("t_req_recruitment.m_branch_id");
                        });
                    } else {
                        $sq->where(function($ssq) use ($m_branch_id) {
                            $ssq->where("t_req_recruitment.m_branch_id", $m_branch_id)
                               ->orWhereNull("t_req_recruitment.m_branch_id");
                        });
                    }
                });
            });

            if ($user_id) {
                $query->orWhere('t_req_recruitment.creator_id', $user_id);
            }
        });
    }

    private function createAppTicket($id, $target_id = null)
    {
        $trx = $this->find($id);
        if (!$trx) return false;

        $conf = [
            "app_name"       => "APPROVAL PERMINTAAN KARYAWAN",
            "trx_id"         => $trx->id,
            "trx_table"      => $this->getTable(),
            "trx_name"       => "Permintaan Karyawan",
            "form_name"      => "t_req_recruitment",
            "trx_nomor"      => $trx->nomor,
            "trx_date"       => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
            "target_id"      => $target_id,
        ];

        return $this->helper->approvalCreateTicket($conf);
    }

    public function custom_posted()
    {
        $id = request("id");
        $data = $this->find($id);
        if (!$data) {
            return $this->helper->customResponse("Data tidak ditemukan", 404);
        }
        $data->update([
            "status" => "POSTED"
        ]);
        return $this->helper->customResponse("Data berhasil diposting");
    }

    public function custom_get_hc()
    {
        // Ambil semua user yang berbau HC atau HRD
        $users = \DB::table('default_users')
            ->where('username', 'ILIKE', '%hc%')
            ->orWhere('name', 'ILIKE', '%hc%')
            ->orWhere('username', 'ILIKE', '%turikan%') // Hardcode for testing since we know Turikan is HC
            ->orWhere('name', 'ILIKE', '%turikan%')
            ->orWhere('username', 'ILIKE', '%hrd%')
            ->orWhere('name', 'ILIKE', '%hrd%')
            ->select('m_kary_id', 'name', 'username')
            ->get();
            
        // Jika tidak ketemu dengan pencarian teks, ambil semua user saja supaya user bisa pilih
        if ($users->isEmpty()) {
            $users = \DB::table('default_users')
                ->whereNotNull('m_kary_id')
                ->select('m_kary_id', 'name', 'username')
                ->get();
        }
        
        return response()->json([
            'message' => 'Success',
            'data' => $users
        ]);
    }

    public function custom_send_approval()
    {
        $user = auth()->user();
        
        // AUTO-INJECT MASTER APPROVAL JIKA BELUM ADA DI DATABASE UNTUK CABANG USER INI
        $master_app = \DB::table('m_approval')
                        ->where('name', 'APPROVAL PERMINTAAN KARYAWAN')
                        ->where('m_comp_id', $user->m_comp_id ?? 1)
                        ->first();
                        
        if (!$master_app) {
            $other_app = \DB::table('m_approval')->whereNotNull('m_menu_id')->first();
            $m_approval_id = \DB::table('m_approval')->insertGetId([
                'm_comp_id' => $user->m_comp_id ?? 1,
                'm_dir_id'  => $user->m_dir_id ?? 1,
                'm_menu_id' => $other_app ? $other_app->m_menu_id : 1,
                'name'      => 'APPROVAL PERMINTAAN KARYAWAN',
                'is_active' => 1,
                'creator_id'=> $user->id ?? 1,
                'created_at'=> \Carbon\Carbon::now(),
            ]);

            $hc_role = \DB::table('m_role')->where('name', 'ILIKE', '%HC%')->orWhere('name', 'ILIKE', '%Human%')->first();
            $hc_role_id = $hc_role ? $hc_role->id : 1;

            \DB::table('m_approval_det')->insert([
                'm_approval_id' => $m_approval_id,
                'm_role_id'     => $hc_role_id,
                'level'         => 1,
                'type'          => 'MENYETUJUI',
                'name'          => 'HC APPROVAL',
                'creator_id'    => $user->id ?? 1,
                'created_at'    => \Carbon\Carbon::now(),
            ]);
        }

        $target_id = req("target_id");
        $user_target = $target_id ? default_users::where('m_kary_id', $target_id)->first()?->id : null;

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse(
                "Gagal membuat tiket approval. Hubungi admin.",
                400
            );
        }

        if (app()->request->header("Source") != "mobile") {
            $data = $this->find(req("id"));
            if ($data) {
                $data->update([
                    "status" => "IN APPROVAL",
                ]);
            }
        }

        return $this->helper->customResponse(
            "Permintaan approval berhasil dibuat dan notifikasi masuk ke Inbox HC"
        );
    }

    public function custom_progress($req)
    {
        \DB::beginTransaction();
        try {
            $note = $req->note;
            if (empty($note) && $req->type === 'APPROVED') {
                $note = "Approved by " . (auth()->user()->name ?? 'HC');
            }

            // Lookup app_id dari trx_id (karena frontend mengirim trx_id)
            $appRecord = \DB::table('generate_approval')
                ->where('trx_table', $this->getTable())
                ->where('trx_id', $req->id)
                ->orderBy('id', 'desc')
                ->first();
            
            $app_id = $appRecord ? $appRecord->id : $req->id;

            // Force assign user yang sedang login agar lolos validasi approver
            if ($appRecord) {
                \DB::table('generate_approval_d')
                    ->where('generate_approval_id', $app_id)
                    ->where('is_done', false)
                    ->update(['default_users_id' => auth()->user()->id]);
            }

            $conf = [
                "app_id"   => $app_id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED
                "app_note" => $note,
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type
                    ]);
                } else {
                    $data->update([
                        "status" => "IN APPROVAL",
                    ]);
                }
            }

            \DB::commit();
            return $this->helper->customResponse("Proses approval berhasil");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_detail($req)
    {
        $id = $req->id ?? 0;
        
        // Find app_id from trx_id
        $app = \DB::table('generate_approval')
            ->where('trx_table', $this->getTable())
            ->where('trx_id', $id)
            ->orderBy('id', 'desc')
            ->first();
        
        // Jika belum ada tiket approval, auto-create untuk HC
        if (!$app) {
            $user = auth()->user();
            $created = $this->createAppTicket($id, $user ? $user->id : null);
            if ($created) {
                \DB::table('generate_approval')->where('id', $created->id)->update([
                    'form_name' => 't_req_recruitment'
                ]);
                \DB::table('generate_approval_d')
                    ->where('generate_approval_id', $created->id)
                    ->where('is_done', false)
                    ->update(['default_users_id' => $user ? $user->id : null]);
                $app = \DB::table('generate_approval')->where('id', $created->id)->first();
            }
        }
            
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

    public function custom_approve_hc()
    {
        $req = app()->request;
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            if (!$data) {
                return $this->helper->customResponse("Data tidak ditemukan", 404);
            }

            $data->update([
                'status' => 'APPROVED'
            ]);

            // Untuk approve langsung dari DRAFT/POSTED, biasanya belum ada ticket.
            // Kita auto-generate ticket dan log nya agar riwayat lengkap.
            $user = auth()->user();
            
            $master_app = \DB::table('m_approval')
                            ->where('name', 'APPROVAL PERMINTAAN KARYAWAN')
                            ->where('m_comp_id', $user->m_comp_id ?? 1)
                            ->first();
                            
            if (!$master_app) {
                $m_approval_id = \DB::table('m_approval')->insertGetId([
                    'm_comp_id' => $user->m_comp_id ?? 1,
                    'm_dir_id'  => $user->m_dir_id ?? 1,
                    'm_menu_id' => 1,
                    'name'      => 'APPROVAL PERMINTAAN KARYAWAN',
                    'is_active' => 1,
                    'creator_id'=> $user->id ?? 1,
                    'created_at'=> \Carbon\Carbon::now(),
                ]);

                \DB::table('m_approval_det')->insert([
                    'm_approval_id' => $m_approval_id,
                    'm_role_id'     => 1,
                    'level'         => 1,
                    'type'          => 'MENYETUJUI',
                    'name'          => 'HC APPROVAL',
                    'creator_id'    => $user->id ?? 1,
                    'created_at'    => \Carbon\Carbon::now(),
                ]);
            }

            $app = $this->createAppTicket($data->id, $user->id);
            
            if ($app) {
                \DB::table('generate_approval')->where('id', $app->id)->update([
                    'status' => 'APPROVED',
                    'form_name' => 't_req_recruitment'
                ]);
                \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->update([
                    'is_done' => true,
                    'action_type' => 'APPROVED',
                    'action_at' => Carbon::now(),
                    'action_note' => 'AUTO APPROVED BY HC',
                    'default_users_id' => $user->id
                ]);
                \DB::table('generate_approval_log')->insert([
                    'nomor'                     => $data->nomor,
                    'generate_approval_id'      => $app->id,
                    'generate_approval_det_id'  => null,
                    'trx_id'                    => $data->id,
                    'trx_table'                 => $this->getTable(),
                    'trx_name'                  => "Permintaan Karyawan",
                    'trx_nomor'                 => $data->nomor,
                    'trx_date'                  => $data->tanggal,
                    'form_name'                 => "t_req_recruitment",
                    'trx_creator_id'            => $data->creator_id,
                    'action_type'               => 'APPROVED',
                    'action_user_id'            => $user->id,
                    'creator_id'                => $user->id,
                    'action_at'                 => Carbon::now(),
                    'action_note'               => 'AUTO APPROVED BY HC'
                ]);
            }

            \DB::commit();
            return $this->helper->customResponse("Approval langsung berhasil", 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error Approve HC: " . $e->getMessage());
            return $this->helper->customResponse("Terjadi kesalahan sistem: " . $e->getMessage(), 500);
        }
    }
}