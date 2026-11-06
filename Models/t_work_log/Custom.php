<?php

namespace App\Models\CustomModels;
use App\Cores\Approval;
use App\Cores\Helper;
use App\Cores\Respo;

class t_work_log extends \App\Models\BasicModels\t_work_log
{    
    private $helper;
    private $generateCode;
    private $respo;
    private $approval;

    private const FORM_NAME = 'LAPORAN PEKERJAAN';

    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();
        $this->respo = new Respo();
        $this->approval = new Approval();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        if(!isset($arrayData['status'])){
            $status = 'DRAFT';
        }else{
            $status = $arrayData['status'];
        }

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE LAPORAN PEKERJAAN"),
            "status" => $status,
            "creator_id" => auth()->user()->id
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }
    

    // public function custom_send_approval($req)
    // {
    //     $data = null;
    //     $response_msg = null;

    //     if ($req->filled('id')) {
    //         try{
    //             $data = $this->where('id', $req->id)->first();
    //             if(!$data) return $this->helper->customResponse("Data transaksi ID {$req->id} tidak ditemukan.",404);

    //             $app = $this->approval->createTicket([
    //                 "app_name"       => "APPROVAL ". self::FORM_NAME,
    //                 "trx_id"         => $data->id,
    //                 "trx_table"      => $this->getTable(),
    //                 "trx_name"       => \Str::title("Pengajuan ". self::FORM_NAME),
    //                 "form_name"      => "t_work_log",
    //                 "trx_nomor"      => $data->kode,
    //                 "trx_date"       => \Carbon::parse($data->created_at)->toDateString(),
    //                 "trx_creator_id" => $data->creator_id,
    //             ], true);
    //             if (!$app) return $this->helper->customResponse("Terjadi kesalahan, coba kembali nanti",400);
    //             // $response_msg = $this->helper->postData(self::FORM_NAME, $data, true);
    //         }catch(\Exception $e){
    //             return $this->helper->responseCatch($e);
    //         }
    //     }

    //     // if (isset($response_msg['error'])) {
    //     //     return $this->helper->responseCatch($response_msg['error']);
    //     // }
    //     return $this->helper->customResponse("Success", 200, $data, true);
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
            $spd = t_work_log::find(req("id"));
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
        $tempId = $id;
        $trx = \DB::table('t_work_log')->find($tempId);
        $conf = [
            "app_name" => "APPROVAL LAPORAN PEKERJAAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Laporan Pekerjaan",
            "form_name" => "t_work_log",
            "trx_nomor" => $trx->kode,
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


    // public function custom_progress($req)
    // {
    //     \DB::beginTransaction();
    //     try {
    //         $conf = [
    //             "app_id" => $req->id,
    //             "app_type" => $req->type, // APPROVED, REVISED, REJECTED,
    //             "app_note" => $req->note, // alasan approve
    //         ];

    //         $app = $this->approval->progress($conf, true);
    //         if ($app->status) {
    //             $data = $this->find($app->trx_id);
    //             if ($app->finish) {
    //                 // $response_msg = $this->helper->postData(self::FORM_NAME, $data, true);
    //                 $data->status = $req->type;
    //                 $data->save();
    //                 // INSERT STOCK
    //                 // $type = "SJ";
    //                 // $r_stock = new r_stock;
    //                 // $r_stock_d = new r_stock_d;
    //                 // $conf = $this->helper->mappingStockConfVariable($data, $type, $this->table);
    //                 // $stock = $r_stock->addStockHeader($conf);

    //                 // $data_d = t_sj_d::select('*')->where("t_sj_d.t_sj_id", $data->id)->get();
    //                 // foreach($data_d as $d){
    //                 //     $conf = $this->helper->mappingStockConfDetailVariable($d, 'OUT', $this->table, $stock->id, $data->warehouse_id);
    //                 //     $return = $r_stock_d->addStockDetail($conf);
    //                 // }
    //             }
    //         }

    //         \DB::commit();
    //         return $this->helper->customResponse("Proses approval berhasil");
    //     } catch (\Exception $e) {
    //         \DB::rollback();
    //         return $this->helper->responseCatch($e);
    //     }
    // }

    public function custom_app_detail($req)
    {
        $id = $req->id ?? 1;
        $data = $this->approval->detail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_app_log($req)
    {
        $conf = [
            "trx_id" => $req->id ?? 0,
            "trx_table" => $this->getTable(),
            "trx_name" => \Str::title('Pengajuan '. self::FORM_NAME),
        ];
        $data = $this->approval->log($conf);
        return response($data);
    }

    
    public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{

            $t_cuti = t_work_log::find($req->id);
            $t_cuti->status = 'POSTED';
            $t_cuti->save();

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