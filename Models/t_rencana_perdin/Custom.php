<?php

namespace App\Models\CustomModels;
use App\Cores\Helper;
use App\Cores\Approval;
use Carbon\Carbon;

class t_rencana_perdin extends \App\Models\BasicModels\t_rencana_perdin
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        /*file_column*/
    ];

    public function t_rencana_perdin_det()
    {
        return $this->hasMany('App\Models\BasicModels\t_rencana_perdin_det', 't_rencana_perdin_id', 'id');
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $perdin = t_perdin::find($arrayData['t_perdin_id']);
        $nomor = $perdin?->nomor ?? '';
        $newArrayData = array_merge($arrayData, [
            "nomor" => $nomor,
        ]);

        $this->details = ["t_rencana_perdin_det"];
        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->details = ["t_rencana_perdin_det"];
        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if ($id && (!isset($row['t_rencana_perdin_det']) || empty($row['t_rencana_perdin_det']))) {
            $details = \DB::table('t_rencana_perdin_det')
                ->where('t_rencana_perdin_id', $id)
                ->get();
            $row['t_rencana_perdin_det'] = json_decode(json_encode($details), true) ?? [];
        }
        return $row;
    }


    public function custom_generateTarif()
    {
        $req = app()->request;
        $posisi_id = $req->posisi_id ?? $req->m_posisi_id;
        $level_posisi_id = $req->m_level_posisi_id;

        if (!$level_posisi_id && $posisi_id) {
            $level_posisi_id = \DB::table('m_level_posisi_d')
                ->where('m_posisi_id', $posisi_id)
                ->value('m_level_posisi_id');
        }

        if (!$level_posisi_id && !empty($req->m_kary_id)) {
            $posisi_id = \DB::table('m_kary')->where('id', $req->m_kary_id)->value('m_posisi_id');
            if ($posisi_id) {
                $level_posisi_id = \DB::table('m_level_posisi_d')
                    ->where('m_posisi_id', $posisi_id)
                    ->value('m_level_posisi_id');
            }
        }

        if (!$level_posisi_id) {
            return response()->json(["data" => []]);
        }

        $tarifHeader = \DB::table('m_tarif_perdin')
            ->where('m_level_posisi_id', $level_posisi_id)
            ->where('is_active', true)
            ->first();

        if (!$tarifHeader) {
            $tarifHeader = \DB::table('m_tarif_perdin')
                ->where('m_level_posisi_id', $level_posisi_id)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$tarifHeader) {
            return response()->json(["data" => []]);
        }

        $details = \DB::table('m_tarif_perdin_det')
            ->where('m_tarif_perdin_id', $tarifHeader->id)
            ->get()
            ->map(function ($item) {
                return [
                    "komponen" => $item->komponen,
                    "nominal"  => (float) $item->nominal,
                    "jumlah"   => 1,
                    "total"    => (float) $item->nominal,
                    "catatan"  => $item->catatan ?? '',
                ];
            });

        return response()->json(["data" => $details]);
    }

    public function public_generateTarif()
    {
        return $this->custom_generateTarif();
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
            $data = t_rencana_perdin::find($req->id);
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
            $trx = \DB::table("t_rencana_perdin")->find(req("id"));
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
                        $firebase->sendToDevice($token, "Approval Rencana Perdin", "Ada pengajuan rencana perdin yang butuh approval Anda.", ["title" => "Approval Rencana Perdin"]);
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
            "Permintaan approval berhasil dibuat beserta notifikasi"
        );
    }

    private function createAppTicket($id, $target_id)
    {
        $tempId = $id;
        $trx = \DB::table("t_rencana_perdin")->find($tempId);
        $trxDate = Date("Y-m-d");
        if ($trx?->t_perdin_id) {
            $perdin = t_perdin::find($trx->t_perdin_id);
            if ($perdin?->date_from) {
                $trxDate = Carbon::parse($perdin->date_from)->format('Y-m-d');
            }
        }
        $conf = [
            "app_name" => "APPROVAL RINCIAN PERDIN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Rincian Perdin",
            "form_name" => "t_pengajuan_perdin",
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
        if ($check = $prevLog->exists()) {
            $prev = $prevLog->first();
            $log_insert = generate_approval_log::create([
                'nomor' => $prev->nomor,
                'generate_approval_id' => $prev->id,
                'generate_approval_det_id' => null,
                'trx_id' => $prev->trx_id,
                'trx_table' => $prev->trx_table,
                'trx_name' => $prev->trx_name,
                'trx_nomor' => $prev->trx_nomor,
                'trx_date' => $prev->trx_date,
                'form_name' => $prev->form_name,
                'trx_creator_id' => $prev->trx_creator_id,
                'action_type' => 'APPROVED',
                'action_user_id' => auth()->user()->id,
                'creator_id' => auth()->user()->id,
                'action_at' => Carbon::now(),
                'action_note' => 'APPROVED BY HC'
            ]);
        }
    }

    public function scopelanding($model)
    {
        return $model;
    }
}