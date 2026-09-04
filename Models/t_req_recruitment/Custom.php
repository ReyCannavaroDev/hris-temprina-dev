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
    
    public $fileColumns = [ /*file_column*/ ];

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
        "m_kary.id=t_req_recruitment.karyawan_digantikan_id",
        "m_general.id=t_req_recruitment.prioritas_id",
        "t_loker.id=t_req_recruitment.t_loker_id",
        "default_users.id=t_req_recruitment.creator_id",
        "default_users.id=t_req_recruitment.last_editor_id"
    ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[
            'nomor'   => $this->helper->generateNomor('KODE PERMINTAAN KARYAWAN'),
            'tanggal' => $arrayData['tanggal'] ?? date('Y-m-d'),
            'status'  => $arrayData['status'] ?? 'DRAFT',
            'm_kary_id' => $arrayData['m_kary_id'] ?? auth()->user()->m_kary_id ?? null,
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function scoperespo($model)
    {
        $m_subcomp_id = request("m_subcomp_id") ?? null;
        $m_branch_id = request("m_branch_id") ?? null;

        if ($m_subcomp_id === "null" || $m_subcomp_id === "undefined" || empty($m_subcomp_id)) {
            $m_subcomp_id = null;
        }
        if ($m_branch_id === "null" || $m_branch_id === "undefined" || empty($m_branch_id)) {
            $m_branch_id = null;
        }

        return $model
            ->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                $q->where("t_req_recruitment.m_subcomp_id", $m_subcomp_id);
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                $q->where("t_req_recruitment.m_branch_id", $m_branch_id);
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

    public function custom_send_approval()
    {
        $target_id = req("target_id");
        $user_target = $target_id ? default_users::where('m_kary_id', $target_id)->first()?->id : null;

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan saat memproses approval, coba kembali nanti",
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
        \DB::beginTransaction();
        try {
            $conf = [
                "app_id"   => $req->id,
                "app_type" => $req->type, // APPROVED, REVISED, REJECTED
                "app_note" => $req->note,
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
        $data = $this->helper->approvalDetail($id);
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
}