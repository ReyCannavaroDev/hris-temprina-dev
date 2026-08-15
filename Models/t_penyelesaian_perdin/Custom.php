<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class t_penyelesaian_perdin extends \App\Models\BasicModels\t_penyelesaian_perdin
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];
    public $details = ["t_penyelesaian_perdin_det", "t_penyelesaian_perdin_d_laporan"];

    public function t_penyelesaian_perdin_det()
    {
        return $this->hasMany(\App\Models\CustomModels\t_penyelesaian_perdin_det::class, 't_penyelesaian_perdin_id', 'id');
    }

    public function t_penyelesaian_perdin_d_laporan()
    {
        return $this->hasMany(\App\Models\CustomModels\t_penyelesaian_perdin_d_laporan::class, 't_penyelesaian_perdin_id', 'id');
    }

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $nomor = t_perdin::find($arrayData['t_perdin_id'])?->nomor ?? '';
        $newArrayData  = array_merge( $arrayData,[
            // "nomor" => $this->helper->generateNomor("KODE RINCIAN PERDIN"),
            "nomor" => $nomor,
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function public_generateTarif()
    {
        $req = app()->request;
        
        // $posisi_id = $req->posisi_id;
        // $kota_id = $req->kota_id;
        // $provinsi_id = $req->provinsi_id;
        $t_perdin_id = $req->t_perdin;


        $t_rencana = t_rencana_perdin_det::whereHas("t_rencana_perdin", function ($q) use ($t_perdin_id) {
            $q
            // ->where("kota_id", $kota_id)
            // ->where("provinsi_id", $provinsi_id)
            // ->where("posisi_id", $posisi_id)
            ->where("t_perdin_id", $t_perdin_id)
            ->where("status", "APPROVED")
            ;
        })
            ->get()
            ->map(function ($item) {
                return [
                    "komponen" => $item->komponen,
                    "nominal" => $item->nominal,
                    "jumlah" => $item->jumlah,
                    "total" => $item->total,
                    "catatan" => $item->catatan,
                ];
            });

        return response()->json(["data" => $t_rencana]);
    }

     public function custom_app_log($req)
    {
        $conf = [
            "trx_id" => $req->id ?? 0,
            "trx_table" => $this->getTable(),
        ];
        $data = $this->helper->approvalLog($conf);
        return response($data);
    }

    public function custom_app_detail($req)
    {
        $id = $req->id ?? 66;
        $data = $this->helper->approvalDetail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            $data->status = "POSTED";
            $data->save();

            \DB::commit();
            return $this->helper->customResponse("Data berhasil diajukan");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
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

    private function createAppTicket($id, $target_id)
    {
        $tempId = $id;
        $trx = \DB::table("t_penyelesaian_perdin")->find($tempId);
        $conf = [
            "app_name" => "APPROVAL PENYELESAIAN PERDIN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Penyelesaian Perdin",
            "form_name" => "t_penyelesaian_perdin",
            "trx_nomor" => $trx->nomor,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => auth()->user()->id,
            "target_id" => $target_id,
        ];

        $app = $this->helper->approvalCreateTicket($conf);
        if ($app) {
            return true;
        } else {
            return false;
        }
    }

    public function scopekary($model)
    {
        $authId = auth()->user()->id;
        $m_kary = m_kary::whereHas('default_users', function($q)use ($authId){
            $q->where('id', $authId);
        })->first();

        $model->where('t_penyelesaian_perdin.m_kary_id', $m_kary->id);
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

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type,
                    ]);

                    // $t_kbs = t_kbs::find($data->t_kbs_id);
                    // if($t_kbs){
                    //     $t_kbs->update([
                    //         "is_active" => false,
                    //     ]);
                    // }
                    if($req->type === 'APPROVED')
                    {
                        $payload = [
                            "date"        => Carbon::now()->format('Y-m-d'),
                            "t_kbs_id"    => $data->t_kbs_id,
                            "total_real"  => $data->total_biaya ?? 0,
                            "selisih"     => $data->sisa_biaya ?? 0,
                        ];

                        $response = Http::timeout(30)
                            ->post( env('ERP_URL') .'/public/t_kbr/generatekbr', $payload);

                        if (!$response->successful()) {
                            throw new \Exception(
                                'Gagal generate KBR: ' . $response->body()
                            );
                        }
                    }


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