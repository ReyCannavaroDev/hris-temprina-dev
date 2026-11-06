<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class t_lembur extends \App\Models\BasicModels\t_lembur
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = ["doc"];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function scopeFilter($model)
    {
     

        if(app()->request->header('Source') === 'mobile') {
            // $model->where
        }
        
        if (req("date_from") && req("date_to")) {
            return $model
                ->whereBetween("tanggal", [req("date_from"), req("date_to")])
                ->whereRaw(
                    "t_lembur.m_dir_id in(select u.m_dir_id from default_users u where u.id = ?)",
                    [auth()->user()->id ?? 0]
                );
        }
    }

    public function transformRowData( array $row )
    {
        $approval = generate_approval_log::where('trx_table', 't_lembur')->where('trx_id', $row['id']) ->orderBy('created_at', 'desc')->value('action_note');
        return array_merge( $row, [
            'approval_note' => $approval ?? ''
        ] );
    }

    public function hitungMenit($jamMulai, $jamSelesai)
    {
        $start = Carbon::parse($jamMulai);
        $end   = Carbon::parse($jamSelesai);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        // Mengembalikan total selisih dalam menit
        return $end->diffInMinutes($start);
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE LEMBUR"),
            "interval_min" => ($arrayData['jam_mulai'] !== null && $arrayData['jam_selesai'] !== null ) ? $this->hitungMenit($arrayData['jam_mulai'], $arrayData['jam_selesai']) : null,
        ]);
        if (app()->request->header("Source") == "mobile") {
            $newArrayData = array_merge($newArrayData, [
                "nomor" => $this->helper->generateNomor("KODE LEMBUR"),
                "interval_min" => ($arrayData['jam_mulai'] !== null && $arrayData['jam_selesai'] !== null ) ? $this->hitungMenit($arrayData['jam_mulai'], $arrayData['jam_selesai']) : null,
                "status" => "IN APPROVAL",
            ]);
        }
        $newArrayData = array_merge($newArrayData, [
            "pic_id" => @app()->request->pic_id ?? auth()->user()->id,
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        if (app()->request->header("Source") == "mobile") {
            $data = \DB::table('t_lembur')->where('id', $id)->first();
            if($data->status === 'REVISED'){
                $status = 'IN APPROVAL';
            }
        }
        $data = \DB::table('t_lembur')->where('id', $id)->first();

        if (isset($arrayData['jam_mulai']) && isset($arrayData['jam_selesai']) ) {
            $interval = $this->hitungMenit($arrayData['jam_mulai'], $arrayData['jam_selesai']);
        }

        $newArrayData  = array_merge( $arrayData,[
            'status' => $status ?? $data->status,
            'interval_min' => @$interval ?? $data->interval_min,
            "pic_id" => @app()->request->pic_id ?? auth()->user()->id,
        ]);


        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        if (app()->request->header("Source") == "mobile") {
            $app = $this->createAppTicket($model->id);  
        }
    }

    // public function onRetrieved($model){
    //     // inject pic name
    //     if (app()->request->header("Source") == "mobile" || app()->request->detail) {
    //         $model['pic.nama_lengkap'] = m_kary::where('id',$model['pic.m_kary_id'])->pluck('nama_lengkap')->first();
    //     }
    // }

    public function custom_send_approval()
    {
        $target_id = req("target_id");
        $user_target = $target_id ? default_users::where('m_kary_id', $target_id)->first()?->id : null;

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan, coba kembali nanti",
                400
            );
        }

        if (app()->request->header("Source") != "mobile") {
            $spd = t_lembur::find(req("id"));
            if ($spd) {
                $spd->update([
                    "status" => "IN APPROVAL",
                ]);
            }
        }

        return $this->helper->customResponse(
            "Permintaan approval berhasil dibuat"
        );
    }

    private function createAppTicket($id, $target_id = null)
    {
        $trx = $this->find($id);

        // pengajuan lembur untuk bawahan 
        // dengan deteksi kolom pic_id terisi dan pic_id bukan user nya sendiri
        // if(@$trx->pic_id != auth()->user()->id){
        //     $app_name = "APPROVAL LEMBUR UNTUK BAWAHAN";
        // }
        $conf = [
            "app_name" => @$app_name ?? "APPROVAL LEMBUR", 
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Lembur",
            "form_name" => "t_lembur",
            "trx_nomor" => $trx->nomor,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
            "target_id" => $target_id,
        ];

        $app = $this->helper->approvalCreateTicket($conf);
        if ($app) {
            return true;
        } else {
            return false;
        }
    }

    public function custom_post($request)
    {
        $data = $this->find($request->$id);
        if (!$data) {
            return response()->json(["message" => "Data not found"], 404);
        }
        if ($data->status === "DRAFT") {
            // Change the status to post
            $data->update([
                "status" => "POSTED",
            ]);
            // $data->status = 'POSTED';
            // $data->save();
            return response()->json([
                "message" => 'DRAFT status changed to "POSTED"',
            ]);
        } else {
            // If the status is not draft, return a message
            return response()->json(
                ["message" => 'POSTED status is not "DRAFT"'],
                400
            );
        }
    }

    public function custom_progress($req)
    {
        \DB::beginTransaction();

        try {
            $conf = [
                "app_id" => $req->id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED,
                "app_note" => $req->note, // alasan approve
            ];

            $app = $this->helper->approvalProgress($conf);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type,
                    ]);

                    // Additional logic for updating related records or performing other actions
                } else {
                    $data->update([
                        "status" => "IN APPROVAL",
                    ]);
                    // Additional logic for updating related records or performing other actions
                }
            }

            \DB::commit();

            return $this->helper->customResponse("Proses approval berhasil");
        } catch (\Exception $e) {
            \DB::rollback();

            // Log or handle the exception as needed
            return $this->helper->responseCatch($e);

        }
    }

    public function custom_detail($req)
    {
        $id = $req->id ?? 66;
        $data = $this->helper->approvalDetail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }
    public function custom_log($req)
    {
        $conf = [
            "trx_id" => $req->id ?? 0,
            "trx_table" => $this->getTable(),
        ];
        $data = $this->helper->approvalLog($conf);
        return response($data);
    }

     public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{

            $t_lembur = t_lembur::find($req->id);
            $t_lembur->status = 'POSTED';
            $t_lembur->save();

         \DB::commit();
         return $this->helper->customResponse("Lembur berhasil diajukan");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_getKary($req)
    {
        $branch_id = $req->branch_id ?? null;
        $subcomp_id = $req->subcomp_id ?? null;
        $kary_id = $req->kary_id ?? null;
        $search = $req->search ?? null;
        // dd($req);
        $m_kary = t_lembur::whereHas('m_kary', function ($q) use ($subcomp_id, $branch_id) {
                $q->whereHas('m_kary_det_jabatan', function ($q2) use ($subcomp_id, $branch_id) {
                    $q2->when($subcomp_id && $subcomp_id !== 'null' && $subcomp_id !== 'undefined', function ($query) use ($subcomp_id) {
                        $query->where('m_subcomp_id', $subcomp_id);
                    })->when($branch_id && $branch_id !== 'null' && $branch_id !== 'undefined', function ($query) use ($branch_id) {
                        $query->where('m_branch_id', $branch_id);
                    });
                });

                $q->orWhere(function ($q2) use ($subcomp_id, $branch_id) {
                    if ($subcomp_id && $subcomp_id !== 'null' && $subcomp_id !== 'undefined') {
                        $q2->where('m_subcomp_id', $subcomp_id);
                    }
                    if ($branch_id && $branch_id !== 'null' && $branch_id !== 'undefined') {
                        $q2->where('m_branch_id', $branch_id);
                    }
                });
            })
            // ->where('atasan_id', $kary_id
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where("kode", "ILIKE", "%{$search}%")
                        ->orWhere("nama_lengkap", "ILIKE", "%{$search}%");
                });
            })
            ->when($req->filter_kode, function ($q) use ($req) {
                $q->whereHas("m_standart_gaji_n.grading", function($q2) use ($req){
                    $q2->where("code", "ILIKE", "%{$req->filter_kode}%");
                });
            })

             ->when($req->filter_m_zona_nama, function ($q) use ($req) {
                $q->whereHas("m_standart_gaji_n.m_zona", function($q2) use ($req){
                    $q2->where("nama", "ILIKE", "%{$req->filter_m_zona_nama}%");
                });
            })
            // ->when($req->filter_kode, function ($q) use ($req) {
            //     $q->where("kode", "ILIKE", "%{$req->filter_kode}%");
            // })
            ->when($req->filter_nik, function ($q) use ($req) {
                $q->where("nik", "ILIKE", "%{$req->filter_nik}%");
            })
            ->when($req->filter_nama_lengkap, function ($q) use ($req) {
                $q->where(
                    "nama_lengkap",
                    "ILIKE",
                    "%{$req->filter_nama_lengkap}%"
                );
            })
            ->when($req->filter_nama_atasan, function ($q) use ($req) {
                $q->whereHas("atasan", function ($q2) use ($req) {
                    $q2->where(
                        "nama_lengkap",
                        "ILIKE",
                        "%{$req->filter_nama_atasan}%"
                    );
                });
            })
            ->when($req->filter_branch, function ($q) use ($req) {
                $q->whereHas("m_kary_det_jabatan.m_branch", function ($q2) use (
                    $req
                ) {
                    $q2->where("name", "ILIKE", "%{$req->filter_branch}%");
                });
            })
            ->get()
            ->map(function ($lembur) {
                return [
                    "id"        => $lembur->id,
                    "nomor"     => $lembur->nomor,
                    "tanggal"   => \Carbon\Carbon::parse($lembur->tanggal)->format('d/m/Y'),
                    "jam_mulai" => $lembur->jam_mulai,
                    "jam_selesai" => $lembur->jam_selesai,
                    "alasan.id" =>$lembur->alasan?->id,
                    "alasan.value" => $lembur->alasan?->value,
                    "tipe_lembur.id" =>$lembur->tipe_lembur?->id,
                    "tipe_lembur.value" => $lembur->tipe_lembur?->value,
                    "date_from" => \Carbon\Carbon::parse($lembur->date_from)->format('d/m/Y'),
                    "date_to"   => \Carbon\Carbon::parse($lembur->date_to)->format('d/m/Y'),
                    "status" => $lembur->status,
                    "m_kary.id" => $lembur->m_kary_id,
                    "m_kary.m_comp_id" => $lembur->m_kary->m_kary_det_jabatan[0]?->m_comp_id ?? $lembur->m_kary?->m_comp_id,
                    "m_kary.branch_id" => $lembur->m_kary->m_kary_det_jabatan[0]?->m_branch_id ?? $lembur->m_kary?->m_branch_id,
                    "m_kary_m_subcomp_id" => $lembur->m_kary->m_kary_det_jabatan[0]?->m_subcomp_id ?? $lembur->m_kary?->m_subcomp_id,
                    "m_kary.nama_lengkap" => $lembur->m_kary->nama_lengkap,
                    "m_kary.nik" => $lembur->m_kary->nik,
                ];
            });

        return $this->helper->customResponse("Data Karyawan", 200, $m_kary);
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

            $data->update([
                'status' => 'APPROVED'
            ]);

            $log = $this->logHc($data->id);

            \DB::commit();
            return $this->helper->customResponse("Approval berhasil", 200);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error("Error Approve HC: " . $e->getMessage());

            return $this->helper->customResponse(
                "Terjadi kesalahan sistem: " . $e->getMessage(), 
                500
            );
        }
    }

    public function logHc($trxId)
    {
        $prevLog = generate_approval_log::where('trx_id', $trxId)->where('action_type', 'HALF APPROVED');
        if($check = $prevLog->exists()){
            $prev = $prevLog->first();
            $log_insert = generate_approval_log::create([
                'nomor'                     => $prev->nomor,
                'generate_approval_id'      => $prev->id,
                'generate_approval_det_id'  => null,
                'trx_id'                    => $prev->trx_id,
                'trx_table'                 => $prev->trx_table,
                'trx_name'                  => $prev->trx_name,
                'trx_nomor'                 => $prev->trx_nomor,
                'trx_date'                  => $prev->trx_date,
                'form_name'                 => $prev->form_name,
                'trx_creator_id'            => $prev->trx_creator_id,
                'action_type'               => 'APPROVED',
                'action_user_id'            => auth()->user()->id,
                'creator_id'                => auth()->user()->id,
                'action_at'                 => Carbon::now(),
                'action_note'               => 'APPROVED BY HC'
            ]); 
        }
    }

    public function scopegetos($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        return $model->whereIn('m_kary.status_kary_id', $status_harian);

    }

    public function scopegetharian($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN")->where('value', "HARIAN")->pluck('id')->toArray();

        return $model->whereIn('m_kary.status_kary_id', $status_harian);

    }

    public function scopegetnonos($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        return $model->whereNotIn('m_kary.status_kary_id', $status_harian);

    }
}
