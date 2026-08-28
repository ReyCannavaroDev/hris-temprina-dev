<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class t_penyelesaian_perdin extends \App\Models\BasicModels\t_penyelesaian_perdin
{    
    private $helper;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->helper = getCore('Helper');
        if (app()->request->isMethod('GET')) {
            $this->details = [];
        }
    }
    
    public $fileColumns    = [ /*file_column*/ ];
    public $details = ["t_penyelesaian_perdin_det", "t_penyelesaian_perdin_d_laporan"];

    public function t_penyelesaian_perdin_det()
    {
        return $this->hasMany('App\Models\BasicModels\t_penyelesaian_perdin_det', 't_penyelesaian_perdin_id', 'id');
    }

    public function t_penyelesaian_perdin_d_laporan()
    {
        return $this->hasMany('App\Models\BasicModels\t_penyelesaian_perdin_d_laporan', 't_penyelesaian_perdin_id', 'id');
    }

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $nomor = t_perdin::find($arrayData['t_perdin_id'])?->nomor ?? '';
        $newArrayData  = array_merge( $arrayData,[
            "nomor" => $nomor,
        ] );

        $req = app()->request;
        $details = [];
        if (!empty($req->t_penyelesaian_perdin_det) && is_array($req->t_penyelesaian_perdin_det)) {
            $cleanDetails = [];
            foreach ($req->t_penyelesaian_perdin_det as $row) {
                $cleanRow = $row;
                unset($cleanRow['id']);
                unset($cleanRow['created_at']);
                unset($cleanRow['updated_at']);
                $cleanRow['jumlah'] = (int)($cleanRow['jumlah'] ?? 1);
                $cleanRow['nominal'] = (float)($cleanRow['nominal'] ?? 0);
                $cleanRow['total'] = (float)($cleanRow['total'] ?? ($cleanRow['nominal'] * $cleanRow['jumlah']));
                $cleanDetails[] = $cleanRow;
            }
            $req->merge(['t_penyelesaian_perdin_det' => $cleanDetails]);
            $details[] = 't_penyelesaian_perdin_det';
        }
        if (!empty($req->t_penyelesaian_perdin_d_laporan) && is_array($req->t_penyelesaian_perdin_d_laporan)) {
            $cleanLap = [];
            foreach ($req->t_penyelesaian_perdin_d_laporan as $row) {
                $cleanRow = $row;
                unset($cleanRow['id']);
                unset($cleanRow['created_at']);
                unset($cleanRow['updated_at']);
                $cleanLap[] = $cleanRow;
            }
            $req->merge(['t_penyelesaian_perdin_d_laporan' => $cleanLap]);
            $details[] = 't_penyelesaian_perdin_d_laporan';
        }
        $this->details = $details;

        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $req = app()->request;
        $details = [];
        if (!empty($req->t_penyelesaian_perdin_det) && is_array($req->t_penyelesaian_perdin_det)) {
            $cleanDetails = [];
            foreach ($req->t_penyelesaian_perdin_det as $row) {
                $cleanRow = $row;
                unset($cleanRow['created_at']);
                unset($cleanRow['updated_at']);
                $cleanRow['jumlah'] = (int)($cleanRow['jumlah'] ?? 1);
                $cleanRow['nominal'] = (float)($cleanRow['nominal'] ?? 0);
                $cleanRow['total'] = (float)($cleanRow['total'] ?? ($cleanRow['nominal'] * $cleanRow['jumlah']));
                $cleanDetails[] = $cleanRow;
            }
            $req->merge(['t_penyelesaian_perdin_det' => $cleanDetails]);
            $details[] = 't_penyelesaian_perdin_det';
        } else {
            \DB::table('t_penyelesaian_perdin_det')->where('t_penyelesaian_perdin_id', $id)->delete();
        }

        if (!empty($req->t_penyelesaian_perdin_d_laporan) && is_array($req->t_penyelesaian_perdin_d_laporan)) {
            $cleanLap = [];
            foreach ($req->t_penyelesaian_perdin_d_laporan as $row) {
                $cleanRow = $row;
                unset($cleanRow['created_at']);
                unset($cleanRow['updated_at']);
                $cleanLap[] = $cleanRow;
            }
            $req->merge(['t_penyelesaian_perdin_d_laporan' => $cleanLap]);
            $details[] = 't_penyelesaian_perdin_d_laporan';
        } else {
            \DB::table('t_penyelesaian_perdin_d_laporan')->where('t_penyelesaian_perdin_id', $id)->delete();
        }

        $this->details = $details;

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if ($id) {
            if (!isset($row['t_penyelesaian_perdin_det']) || empty($row['t_penyelesaian_perdin_det'])) {
                $details = \DB::table('t_penyelesaian_perdin_det')
                    ->where('t_penyelesaian_perdin_id', $id)
                    ->get();
                $row['t_penyelesaian_perdin_det'] = json_decode(json_encode($details), true) ?? [];
            }
            if (!isset($row['t_penyelesaian_perdin_d_laporan']) || empty($row['t_penyelesaian_perdin_d_laporan'])) {
                $laporan = \DB::table('t_penyelesaian_perdin_d_laporan')
                    ->where('t_penyelesaian_perdin_id', $id)
                    ->get();
                $row['t_penyelesaian_perdin_d_laporan'] = json_decode(json_encode($laporan), true) ?? [];
            }
        }
        return $row;
    }

    public function public_generateTarif()
    {
        $req = app()->request;
        $t_perdin_id = $req->t_perdin ?? $req->t_perdin_id;

        if (!$t_perdin_id) {
            return response()->json(["data" => []]);
        }

        // Ambil rincian rencana perdin yang statusnya APPROVED
        $details = \DB::table('t_rencana_perdin_det as d')
            ->join('t_rencana_perdin as r', 'r.id', '=', 'd.t_rencana_perdin_id')
            ->where('r.t_perdin_id', $t_perdin_id)
            ->whereRaw("upper(r.status) = 'APPROVED'")
            ->select('d.komponen', 'd.nominal', 'd.jumlah', 'd.total', 'd.catatan')
            ->get();

        // Fallback: jika belum ada APPROVED atau format status berbeda, ambil rincian rencana perdin terkait
        if ($details->isEmpty()) {
            $details = \DB::table('t_rencana_perdin_det as d')
                ->join('t_rencana_perdin as r', 'r.id', '=', 'd.t_rencana_perdin_id')
                ->where('r.t_perdin_id', $t_perdin_id)
                ->select('d.komponen', 'd.nominal', 'd.jumlah', 'd.total', 'd.catatan')
                ->get();
        }

        $formatted = $details->map(function ($item) {
            return [
                "komponen" => $item->komponen,
                "nominal"  => (float) $item->nominal,
                "jumlah"   => (int) $item->jumlah,
                "total"    => (float) $item->total,
                "catatan"  => $item->catatan,
            ];
        });

        return response()->json(["data" => $formatted]);
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
        $user_target = $target_id ? \App\Models\BasicModels\default_users::where('m_kary_id', $target_id)->first()?->id : null;

        if (!$user_target) {
            $trx = \DB::table("t_penyelesaian_perdin")->find(req("id"));
            $m_kary_id = $trx->m_kary_id ?? $trx->creator_id ?? 0;
            $atasan_id = \App\Models\BasicModels\m_kary::where('id', $m_kary_id)->first()?->atasan_id ?? 0;
            $user_target = \App\Models\BasicModels\default_users::where('m_kary_id', $atasan_id)->pluck('id')->first();
        }

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan, coba kembali nanti",
                400
            );
        }

        if ($user_target) {
            try {
                $fcm_tokens = \App\Models\BasicModels\default_users_fcm::where('default_users_id', $user_target)->pluck('token_fcm');
                if (count($fcm_tokens) > 0) {
                    $firebase = app(\App\Services\FirebaseMessagingService::class);
                    foreach ($fcm_tokens as $token) {
                        $firebase->sendToDevice($token, "Approval Penyelesaian Perdin", "Ada pengajuan penyelesaian perdin yang butuh approval Anda.", ["title" => "Approval Penyelesaian Perdin"]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore FCM notification error
            }
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
        $trxDate = Date("Y-m-d");
        if ($trx?->t_perdin_id) {
            $perdin = t_perdin::find($trx->t_perdin_id);
            if ($perdin?->date_from) {
                $trxDate = Carbon::parse($perdin->date_from)->format('Y-m-d');
            }
        }
        $conf = [
            "app_name" => "APPROVAL PENYELESAIAN PERDIN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Penyelesaian Perdin",
            "form_name" => "t_penyelesaian_perdin",
            "trx_nomor" => $trx->nomor,
            "trx_date" => $trxDate,
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
        $user = auth()->user();
        $user_id = $user->id ?? 0;
        $m_kary_id = $user->m_kary_id ?? \App\Models\BasicModels\m_kary::whereHas('default_users', function($q) use ($user_id){
            $q->where('id', $user_id);
        })->first()?->id;

        if ($m_kary_id) {
            $model->where('t_penyelesaian_perdin.m_kary_id', $m_kary_id);
        }
    }

    public function custom_progress($req)
    {
        // Start a database transaction
        \DB::beginTransaction();

        try {
            $getApp = \DB::table('generate_approval')
                ->where('trx_table', $this->getTable())
                ->where('trx_id', $req->id)
                ->orderBy('id', 'desc')
                ->first();

            $app_id = $getApp ? $getApp->id : $req->id;

            $type = strtoupper($req->type ?? 'APPROVED');
            if ($type === 'APPROVE') $type = 'APPROVED';
            if ($type === 'REJECT') $type = 'REJECTED';
            if ($type === 'REVISE') $type = 'REVISED';

            $note = $req->note ?: ($req->note_approval ?: ($req->catatan ?: ($type === 'APPROVED' ? 'Approved' : '-')));

            $conf = [
                "app_id" => $app_id,
                "app_type" => $type,
                "app_note" => $note,
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $type,
                    ]);

                    // $t_kbs = t_kbs::find($data->t_kbs_id);
                    // if($t_kbs){
                    //     $t_kbs->update([
                    //         "is_active" => false,
                    //     ]);
                    // }
                    if ($type === 'APPROVED' && !empty($data->t_kbs_id) && env('ERP_URL')) {
                        try {
                            $payload = [
                                "date"        => Carbon::now()->format('Y-m-d'),
                                "t_kbs_id"    => $data->t_kbs_id,
                                "total_real"  => $data->total_biaya ?? 0,
                                "selisih"     => $data->sisa_biaya ?? 0,
                            ];

                            $response = Http::timeout(10)
                                ->post(env('ERP_URL') . '/public/t_kbr/generatekbr', $payload);

                            if (!$response->successful()) {
                                \Log::error('Gagal generate KBR: ' . $response->body());
                            }
                        } catch (\Throwable $e) {
                            \Log::error('Exception generate KBR: ' . $e->getMessage());
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