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

    public $joins = [];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData = array_merge( $arrayData, [
            'nomor'        => $this->helper->generateNomor('KODE PERMINTAAN KARYAWAN'),
            'tanggal'      => $arrayData['tanggal'] ?? date('Y-m-d'),
            'status'       => $arrayData['status'] ?? 'DRAFT',
            'm_kary_id'    => $arrayData['m_kary_id'] ?? auth()->user()->m_kary_id ?? null,
            'm_comp_id'    => $arrayData['m_comp_id'] ?? auth()->user()->m_comp_id ?? null,
            'm_subcomp_id' => $arrayData['m_subcomp_id'] ?? auth()->user()->m_subcomp_id ?? null,
            'm_branch_id'  => $arrayData['m_branch_id'] ?? auth()->user()->m_branch_id ?? null,
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
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

        return array_merge($row, [
            'm_kary' => $m_kary ? (array)$m_kary : null,
            'm_kary.nama_lengkap' => $m_kary?->nama_lengkap ?? $creator?->name ?? '-',
            'creator' => $creator ? (array)$creator : null,
            'creator.name' => $creator?->name ?? '-',
            'm_divisi' => $m_divisi ? (array)$m_divisi : null,
            'm_divisi.name' => $m_divisi?->name ?? '-',
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
        ]);
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
                if (is_array($m_subcomp_id)) {
                    $q->whereIn("t_req_recruitment.m_subcomp_id", $m_subcomp_id);
                } else {
                    $q->where("t_req_recruitment.m_subcomp_id", $m_subcomp_id);
                }
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                if (is_array($m_branch_id)) {
                    $q->whereIn("t_req_recruitment.m_branch_id", $m_branch_id);
                } else {
                    $q->where("t_req_recruitment.m_branch_id", $m_branch_id);
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