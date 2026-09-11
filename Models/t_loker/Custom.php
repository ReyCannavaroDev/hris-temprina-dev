<?php

namespace App\Models\CustomModels;
use DB;
use Carbon\Carbon;

class t_loker extends \App\Models\BasicModels\t_loker
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');

        $this->heirs = array_values(array_unique(array_merge($this->heirs, [
            "t_loker_d_kualifikasi",
        ])));
        $this->detailsChild = array_values(array_unique(array_merge($this->detailsChild, [
            "t_loker_d_kualifikasi",
        ])));

        if (app()->request->isMethod('GET')) {
            $this->details = [];
            $this->detailsChild = [];
        }
    }

    public $details = ['t_loker_d_kualifikasi'];

    public $fileColumns = [ /*file_column*/];

    public $joins = [
        "t_req_recruitment.id=t_loker.t_req_recruitment_id",
        "m_comp.id=t_loker.m_comp_id",
        "m_subcomp.id=t_loker.m_subcomp_id",
        "m_branch.id=t_loker.m_branch_id",
        "m_divisi.id=t_loker.m_divisi_id",
        "m_posisi.id=t_loker.m_posisi_id",
        "m_general.id=t_loker.jenis_loker_id",
        "m_general.id=t_loker.prioritas_id",
        "m_general.id=t_loker.jk_id",
        "m_general.id=t_loker.status_kary_id",
        "default_users.id=t_loker.creator_id",
        "default_users.id=t_loker.last_editor_id"
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function transformRowData(array $row)
    {
        $data = [];
        $details = \DB::table('t_loker_d_kualifikasi')
            ->where('t_loker_id', $row['id'])
            ->orderBy('id', 'asc')
            ->get();
        $detArr = json_decode(json_encode($details), true);
        $data['t_loker_d_kualifikasi'] = $detArr;

        $m_divisi = !empty($row['m_divisi_id']) ? \DB::table('m_divisi')->where('id', $row['m_divisi_id'])->first() : null;
        if ($m_divisi) {
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
            $data['m_divisi'] = (array)$m_divisi;
            $data['m_divisi.name'] = !empty($divisiVal) ? $divisiVal : '-';
            $data['m_divisi.nama'] = !empty($divisiVal) ? $divisiVal : '-';
        }

        return array_merge($row, $data);
    }

    public function t_loker_d_kualifikasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_loker_d_kualifikasi', 't_loker_id', 'id');
    }

    private function prepareDetailRequest($id = null)
    {
        $req = app()->request;
        $details = $req->t_loker_d_kualifikasi ?? [];

        if (empty($details)) {
            if ($id) {
                \App\Models\BasicModels\t_loker_d_kualifikasi::where('t_loker_id', $id)->delete();
            }
            $this->details = [];
            return;
        }

        $cleanDetails = [];
        foreach ($details as $det) {
            $det = is_array($det) ? $det : (array) $det;
            $val = trim($det['value'] ?? '');
            if ($val === '') continue;

            $cleanRow = [
                'value' => $val,
            ];

            if ($id && !empty($det['id'])) {
                $isExisting = \App\Models\BasicModels\t_loker_d_kualifikasi::where('id', $det['id'])
                    ->where('t_loker_id', $id)
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

            $deleteQuery = \App\Models\BasicModels\t_loker_d_kualifikasi::where('t_loker_id', $id);
            if (!empty($keepIds)) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();
        }

        $req->merge(['t_loker_d_kualifikasi' => $cleanDetails]);
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->prepareDetailRequest();

        $newArrayData = array_merge($arrayData, [
            'nomor' => $this->helper->generateNomor('KODE LOWONGAN PEKERJAAN'),
            'status' => $arrayData['status'] ?? 'OPEN',
            'tgl_dibuka' => !empty($arrayData['tgl_dibuka']) ? $arrayData['tgl_dibuka'] : date('Y-m-d'),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?? $model->id ?? null;
        $req = app()->request;
        $details = $req->t_loker_d_kualifikasi ?? [];
        if (!empty($details) && !empty($targetId)) {
            \DB::table('t_loker_d_kualifikasi')->where('t_loker_id', $targetId)->delete();
            foreach ($details as $det) {
                $val = is_array($det) ? ($det['value'] ?? '') : ($det->value ?? '');
                if (trim($val) !== '') {
                    \DB::table('t_loker_d_kualifikasi')->insert([
                        't_loker_id' => $targetId,
                        'value' => trim($val),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->prepareDetailRequest($id);

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?? $model->id ?? null;
        $req = app()->request;
        $details = $req->t_loker_d_kualifikasi ?? [];
        if (isset($req->t_loker_d_kualifikasi) && !empty($targetId)) {
            \DB::table('t_loker_d_kualifikasi')->where('t_loker_id', $targetId)->delete();
            foreach ($details as $det) {
                $val = is_array($det) ? ($det['value'] ?? '') : ($det->value ?? '');
                if (trim($val) !== '') {
                    \DB::table('t_loker_d_kualifikasi')->insert([
                        't_loker_id' => $targetId,
                        'value' => trim($val),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function public_lowongan($req)
    {
        $paginate = $req->paginate ?? 3;
        return t_loker::with(['m_comp', 'm_dir', 'm_dept', 'jenis_loker', 'prioritas'])->paginate($paginate);
    }

    public function public_detLowongan($req)
    {
        $id = $req->id;

        $data = t_loker::with(['m_comp', 'm_dir', 'm_dept', 'jenis_loker', 'prioritas'])->where('id', $id)->first();

        if (!$data) {
            return response()->json(['errors' => 'Data Tidak ada']);
        }

        return $data;
    }

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            $data->status = 'POSTED';
            $data->save();

            \DB::commit();
            return $this->helper->customResponse("Data berhasil diposting");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function scoperespo($model)
    {
        $user = auth()->user();
        $is_admin = false;
        if ($user) {
            $is_admin = $user->is_hc ||
                strtolower($user->user_type ?? '') === 'admin' ||
                in_array(strtolower($user->username ?? ''), ['developer', 'admin', 'danvers']);
        }

        if ($is_admin) {
            return $model;
        }

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
                $q->where("t_loker.m_subcomp_id", $m_subcomp_id);
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                $q->where("t_loker.m_branch_id", $m_branch_id);
            });
    }

    public static function updateStatusLoker($lokerId)
    {
        if (!$lokerId)
            return;

        $loker = \DB::table('t_loker')->where('id', $lokerId)->first();
        if (!$loker)
            return;

        // Ambil kuota kebutuhan personil dari loker atau FPTK
        $kebutuhan = $loker->jumlah ?? 1;
        if (!empty($loker->t_req_recruitment_id)) {
            $fptk = \DB::table('t_req_recruitment')->where('id', $loker->t_req_recruitment_id)->first();
            if ($fptk && !empty($fptk->jumlah_kebutuhan)) {
                $kebutuhan = $fptk->jumlah_kebutuhan;
            }
        }

        // Hitung total pelamar yang berstatus Diterima / Approved pada hasil tes
        $totalDiterima = \DB::table('t_hasil_tes')
            ->where('t_loker_id', $lokerId)
            ->where(function ($q) {
                $q->whereRaw("upper(status) = 'DITERIMA'")
                    ->orWhereRaw("upper(status) = 'APPROVED'");
            })
            ->count();

        // Tentukan status dinamis loker (OPEN, PROGRESS, CLOSED)
        $newStatus = 'OPEN';
        if ($totalDiterima >= $kebutuhan && $kebutuhan > 0) {
            $newStatus = 'CLOSED';
        } elseif ($totalDiterima > 0) {
            $newStatus = 'PROGRESS';
        }

        \DB::table('t_loker')->where('id', $lokerId)->update([
            'status' => $newStatus,
            'updated_at' => Carbon::now()
        ]);
    }

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = $this->find($tempId);
        $conf = [
            "app_name" => "APPROVAL LOWONGAN PEKERJAAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Lowongan Pekerjaan",
            "form_name" => "t_loker",
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
}