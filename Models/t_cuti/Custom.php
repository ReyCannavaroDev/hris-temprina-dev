<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class t_cuti extends \App\Models\BasicModels\t_cuti
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        'attachment'
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function transformRowData( array $row )
    {
        $approval = generate_approval_log::where('trx_table', 't_cuti')->where('trx_id', $row['id']) ->orderBy('created_at', 'desc')->value('action_note');
        return array_merge( $row, [
            'approval_note' => $approval ?? ''
        ] );
    }

    

    private function hitungHari($from , $to) {
        $dateFrom = Carbon::parse($from);
        $dateTo = Carbon::parse($to);

        $period = CarbonPeriod::create($dateFrom, $dateTo);
        $tanggalMerah = \DB::table('m_libur_nasional')->pluck('tanggal')->toArray();
        $businessDays = 0;

        foreach ($period as $date) {
            if ($date->dayOfWeek != Carbon::SUNDAY && !in_array($date->format('Y-m-d'), $tanggalMerah)) {
                $businessDays++;
            }
        }
        return $businessDays;
    }

    public function custom_hitungHari($req) {
        $from = $req->date_from;
        $to = $req->date_to;
        if(app()->request->header("Source") == "mobile"){
            $dateFrom = Carbon::parse($from);
            $dateTo = Carbon::parse($to);
        }else{
             $dateFrom = Carbon::createFromFormat('d/m/Y', $from);
            $dateTo = Carbon::createFromFormat('d/m/Y', $to);
        }

        // Membuat rentang tanggal antara date_from dan date_to
        $period = CarbonPeriod::create($dateFrom, $dateTo);

        // Menghitung jumlah hari kerja
        $tanggalMerah = \DB::table('m_libur_nasional')->pluck('tanggal')->toArray();
        $businessDays = 0;

        foreach ($period as $date) {
            if ($date->dayOfWeek != Carbon::SUNDAY && !in_array($date->format('Y-m-d'), $tanggalMerah)) {
                $businessDays++;
            }
        }
        
        return $businessDays;
    }

    private function hitungMenit($from, $to) {
        $dateFrom = Carbon::parse($from);
        $dateTo = Carbon::parse($to);

        // Calculate the difference in minutes for datetime range
        $minutesDifference = $dateFrom->diffInMinutes($dateTo);

        return $minutesDifference;
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {   
        if(!isset($arrayData['m_kary_id'])){
            return $this->helper->customResponse(
                "Akun ini tidak Tersambung dengan data karyawan manapun !",
                422
            );
        }

        $interval = null;
        $interval_min = null;

        if (isset($arrayData['date_from']) && isset($arrayData['date_to']) && (!isset($arrayData['time_from']) || $arrayData['time_from'] === null || $arrayData['time_from'] === '') && (!isset($arrayData['time_to']) || $arrayData['time_to'] === null || $arrayData['time_to'] === '')) {
            $interval = $this->hitungHari($arrayData['date_from'], $arrayData['date_to']);
        }

        if (isset($arrayData['time_from']) && isset($arrayData['time_to'])) {
            $interval_min = $this->hitungMenit($arrayData['time_from'], $arrayData['time_to']);
        }

        $m_kary = new m_kary;
        $info_cuti = $m_kary->infoCuti($arrayData['m_kary_id']);
        // dd($info_cuti);
        
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE CUTI"),
            "interval" => $interval,
            "interval_min" => $interval_min
        ]);
        if (app()->request->header("Source") == "mobile") {
            $newArrayData = array_merge($newArrayData, [
                "status" => "IN APPROVAL",
                "interval" => $interval,
                "interval_min" => $interval_min
            ]);
        }

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {

        $interval = null;
        $interval_min = null;

        if (isset($arrayData['date_from']) && isset($arrayData['date_to']) && (!isset($arrayData['time_from']) || $arrayData['time_from'] === null || $arrayData['time_from'] === '') && (!isset($arrayData['time_to']) || $arrayData['time_to'] === null || $arrayData['time_to'] === '')) {
            $interval = $this->hitungHari($arrayData['date_from'], $arrayData['date_to']);
        }

        if (isset($arrayData['time_from']) && isset($arrayData['time_to'])) {
            $interval_min = $this->hitungMenit($arrayData['time_from'], $arrayData['time_to']);
        }

        $data = t_cuti::where('id', $id)->first();
        if (app()->request->header("Source") == "mobile") {
            if($data["status"] === 'REVISED'){
                $status = 'IN APPROVAL';
            }
        }
        $newArrayData  = array_merge( $arrayData,[
            'status' => $status ?? $data['status'],
            "interval" => $interval ?? $data['interval'],
            "interval_min" => $interval_min ?? $data['interval_min'] 
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

      public function updateAfterTransaction( $newdata, $olddata, $data, $meta )
    {
        if (app()->request->header("Source") == "mobile") {
            $app = $this->createAppTicket($newdata['id']);  
        }
    }

    public function public_tes($req)
    {
        $id = "4";
        $data = t_cuti::where('id', (int)$id)->first();
        return $data;
    }

    // public function updateAfter( $model, $arrayData, $metaData, $id=null )
    // {
    //     if (app()->request->header("Source") == "mobile") {
    //         $data = t_cuti::where('id', 4)->first();
    //         if(@$data) { 
    //             dd($data);
    //             }
    //         if(@$data->status === 'REVISED'){
    //             $status = 'IN APPROVAL';
    //             $app = $this->createAppTicket($id);
    //              if (!$app) {
    //                 return $this->helper->customResponse(
    //                     "Approval tidak tersedia untuk atribut user anda",
    //                     400
    //                 );
    //             }
    //         }
    //     }
    //     $newArrayData = $arrayData;
    //     if(@$status){
    //         $newArrayData  = array_merge( $newArrayData,[
    //             'status' => $status 
    //         ]);
    //     }

    //     return [
    //         "model"  => $model,
    //         "data"   => $newArrayData,
    //         // "errors" => ['error1']
    //     ];
    // }

    // public function custom_post ($request) {
    //     $data = t_cuti::find($request->$id);
    //     if (!$data) {
    //         return response()->json(['message' => 'Data not found'], 404);
    //     }
    //     if ($data->status === 'DRAFT') {
    //         // Change the status to post
    //         $data->update([
    //             "status" => "POSTED"
    //             ]);
    //         // $data->status = 'POSTED';
    //         // $data->save();
    //         return response()->json(['message' => 'DRAFT status changed to "POSTED"']);
    //     } else {
    //         // If the status is not draft, return a message
    //         return response()->json(['message' => 'POSTED status is not "DRAFT"'], 400);
    //     }
    // }

    


    // public function updateSisaCuti($data){
    //     $m_kary = m_kary::find($data->m_kary_id);
    //     if($data?->status?->value_2){
    //         $m_kary->
    //     }
        
    // }

    public function custom_getKary($req)
    {
        $branch_id = $req->branch_id ?? null;
        $subcomp_id = $req->subcomp_id ?? null;
        $kary_id = $req->kary_id ?? null;
        $search = $req->search ?? null;
        // dd($req);
        $m_kary = t_cuti::whereHas("m_kary", function($q) use($subcomp_id, $branch_id){
                $q->whereHas("m_kary_det_jabatan", function ($q2) use (
                    $subcomp_id,
                    $branch_id
                ) {
                    $q2->when($subcomp_id, function ($query, $subcomp_id) {
                        if ($subcomp_id != "null") {
                            $query->where("m_subcomp_id", $subcomp_id);
                        }
                    })->when($branch_id, function ($query, $branch_id) {
                        if ($branch_id != "null") {
                            $query->where("m_branch_id", $branch_id);
                        }
                    });
                });
                // $q->when($kary_id, function($q2) use ($kary_id){
                //     $q2->where('id', $kary_id);
                // });
                $q->orWhere(function ($q3) use ($subcomp_id, $branch_id) {
                    if ($subcomp_id != 'null' && $subcomp_id != 'undefined') {
                        $q3->where('m_subcomp_id', $subcomp_id);
                    }
                    if ($branch_id != 'null' && $branch_id != 'undefined') {
                        $q3->where('m_branch_id', $branch_id);
                    }
                });
            })
            // ->where('atasan_id', $kary_id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where("kode", "ILIKE", "%{$search}%")
                        ->orWhere("nama_lengkap", "ILIKE", "%{$search}%");
                });
            })
            
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
            ->map(function ($cuti) {
                return [
                    "id"        => $cuti->id,
                    "nomor"     => $cuti->nomor,
                    "alasan.id" =>$cuti->alasan?->id,
                    "alasan.value" => $cuti->alasan?->value,
                    "tipe_cuti.id" =>$cuti->tipe_cuti?->id,
                    "tipe_cuti.value" => $cuti->tipe_cuti?->value,
                    "date_from" => \Carbon\Carbon::parse($cuti->date_from)->format('d/m/Y'),
                    "date_to"   => \Carbon\Carbon::parse($cuti->date_to)->format('d/m/Y'),
                    "status" => $cuti->status,
                    "m_kary.id" => $cuti->m_kary_id,
                    "m_kary.m_comp_id" => $cuti->m_kary?->m_kary_det_jabatan[0]?->m_comp_id,
                    "m_kary.branch_id" => $cuti->m_kary?->m_kary_det_jabatan[0]?->m_branch_id,
                    "m_kary_m_subcomp_id" => $cuti->m_kary?->m_kary_det_jabatan[0]?->m_subcomp_id,
                    "m_kary.nama_lengkap" => $cuti->m_kary->nama_lengkap,
                    "m_kary.nik" => $cuti->m_kary->nik,
                ];
            });

        return $this->helper->customResponse("Data Karyawan", 200, $m_kary);
    }

    public function scopeowndata($model)
    {
        $m_kary_id = m_kary::whereHas('default_users', function($q){
            $q->where('id', auth()->user()->id);
        })->first()->id;

        return $model->where('t_cuti.m_kary_id', $m_kary_id);
    }


    public function scopebranch($model)
    {
        return $model->join("m_branch as b", "b.id", "m_kary.m_branch_id")
        ->addSelect("b.name as m_branch.name");
    }

    public function scoperespo($model)
    {
        $m_subcomp_id = request("m_subcomp_id") ?? null;
        $m_branch_id = request("m_branch_id") ?? null;

        if ($m_subcomp_id === "null") {
            $m_subcomp_id = null;
        }
        if ($m_branch_id === "null") {
            $m_branch_id = null;
        }

        return $model
            ->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                $q->where("m_kary.m_subcomp_id", $m_subcomp_id);
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                $q->where("m_kary.m_branch_id", $m_branch_id);
            });
    }

    //STATUS & APPROVAL

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = $this->find($tempId);
        $conf = [
            "app_name" => "APPROVAL CUTI",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Cuti",
            "form_name" => "t_cuti",
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
            $data = $this->find(req("id"));
            if ($data) {
                $data->update([
                    "status" => "IN APPROVAL",
                ]);
            }
        }

        return $this->helper->customResponse(
            "Permintaan approval berhasil dibuat"
        );
    }

    public function custom_progress($req)
    {
        // Start a database transaction
        \DB::beginTransaction();

        try {
            $conf = [
                "app_id" => $req->id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED,
                "app_note" => $req->note, // alasan approve
            ];
            // dd($conf);

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

            $data = $this->find($req->id);
            $data->status = 'POSTED';
            $data->save();

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
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
}
