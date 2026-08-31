<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class t_evaluasi_pelatihan extends \App\Models\BasicModels\t_evaluasi_pelatihan
{    
    private $helper;
    private $approval;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->approval = getCore("Approval");

        $this->joins = array_values(array_unique(array_merge($this->joins, [
            "m_kary.id=t_evaluasi_pelatihan.m_kary_id",
            "m_prog_pelatihan.id=t_evaluasi_pelatihan.m_prog_pelatihan_id",
            "m_trainer.id=t_evaluasi_pelatihan.trainer_id"
        ])));

        if (app()->request->isMethod('GET')) {
            $this->details = [];
            $this->detailsChild = [];
        }
    }

    public $details = ['t_evaluasi_pelatihan_detail'];
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
    public function transformRowData( array $row )
    {
        $data = [];

        if(!empty($row['m_prog_pelatihan_id'])){
            $program = m_prog_pelatihan::find($row['m_prog_pelatihan_id']);
            $data['m_prog_pelatihan'] = [
                'id' => $program?->id,
                'tema_pelatihan' => $program?->tema_pelatihan,
            ];
            $data['m_prog_pelatihan.id'] = $program?->id;
            $data['m_prog_pelatihan.tema_pelatihan'] = $program?->tema_pelatihan;
        }

        if(!empty($row['trainer_id'])){
            $trainer = m_trainer::find($row['trainer_id']);
            $data['trainer'] = [
                'id' => $trainer?->id,
                'nama_trainer' => $trainer?->nama_trainer,
            ];
            $data['trainer.id'] = $trainer?->id;
            $data['trainer.nama_trainer'] = $trainer?->nama_trainer;
        }

        if(!empty($row['m_kary_id'])){
            $kary = m_kary::find($row['m_kary_id']);
            $data['m_kary'] = [
                'id' => $kary?->id,
                'nama_lengkap' => $kary?->nama_lengkap,
            ];
            $data['m_kary.id'] = $kary?->id;
            $data['m_kary.nama_lengkap'] = $kary?->nama_lengkap;
        }

        $detail = \DB::table('t_evaluasi_pelatihan_detail')
            ->where('t_evaluasi_pelatihan_id', $row['id'])
            ->orderBy('id', 'asc')
            ->get();

        $data['t_evaluasi_pelatihan_detail'] = json_decode(json_encode($detail), true);

        return array_merge( $row, $data );
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        if(!isset($arrayData['status'])){
            $status = 'DRAFT';
        }else{
            $status = $arrayData['status'];
        }

        $userId = auth()->user()->id;
        $user = default_users::find($userId);
        $karyId = $arrayData['m_kary_id'] ?? ($user ? $user->m_kary_id : null);

        // Validasi: hanya peserta terdaftar yang dapat mengisi evaluasi pelatihan terkait
        if (!empty($arrayData['t_realisasi_pelatihan_id']) && $karyId) {
            $isPeserta = \DB::table('t_realisasi_pelatihan_d_kary')
                ->where('t_realisasi_pelatihan_id', $arrayData['t_realisasi_pelatihan_id'])
                ->where('m_kary_id', $karyId)
                ->exists();

            if (!$isPeserta) {
                response()->json([
                    'timestamp' => \Carbon\Carbon::now()->format('d-m-Y H:i:s'),
                    'code' => 400,
                    'message' => 'Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.',
                    'data' => [
                        'errors' => ['Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.'],
                        'errorText' => 'Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.'
                    ]
                ], 400)->send();
                exit;
            }
        }

        $this->prepareDetailRequest();

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE EVALUASI PELATIHAN"),
            "m_kary_id" => $karyId,
            "status" => $status,
            "creator_id" => $userId
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $userId = auth()->user()->id;
        $user = default_users::find($userId);
        $karyId = $arrayData['m_kary_id'] ?? ($user ? $user->m_kary_id : null);

        if (!empty($arrayData['t_realisasi_pelatihan_id']) && $karyId) {
            $isPeserta = \DB::table('t_realisasi_pelatihan_d_kary')
                ->where('t_realisasi_pelatihan_id', $arrayData['t_realisasi_pelatihan_id'])
                ->where('m_kary_id', $karyId)
                ->exists();

            if (!$isPeserta) {
                response()->json([
                    'timestamp' => \Carbon\Carbon::now()->format('d-m-Y H:i:s'),
                    'code' => 400,
                    'message' => 'Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.',
                    'data' => [
                        'errors' => ['Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.'],
                        'errorText' => 'Anda bukan peserta dari pelatihan ini. Evaluasi hanya dapat diisi oleh peserta pelatihan terkait.'
                    ]
                ], 400)->send();
                exit;
            }
        }

        $this->prepareDetailRequest($id);

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function scopelanding($model)
    {
        $userId = auth()->user()->id;
        $user = default_users::find($userId);
        $karyId = $user ? $user->m_kary_id : null;

        $isHcOrAdmin = false;
        try {
            $roles = \DB::table('default_role_users')
                ->join('default_roles', 'default_roles.id', '=', 'default_role_users.role_id')
                ->where('default_role_users.user_id', $userId)
                ->pluck('default_roles.name')
                ->toArray();
            $isHcOrAdmin = in_array('HC', $roles) || in_array('SUPERADMIN', $roles) || in_array('ADMIN', $roles);
        } catch (\Throwable $e) {}

        if (!$isHcOrAdmin) {
            return $model->where(function($q) use ($karyId, $userId) {
                if ($karyId) {
                    $q->where('t_evaluasi_pelatihan.m_kary_id', $karyId);
                } else {
                    $q->where('t_evaluasi_pelatihan.creator_id', $userId);
                }
            });
        }

        return $model;
    }

    public function custom_count_pending($req)
    {
        $userId = auth()->user()->id;
        $user = default_users::find($userId);
        $karyId = $user ? $user->m_kary_id : null;

        $query = \DB::table('t_evaluasi_pelatihan')->where('status', 'DRAFT');

        $isHcOrAdmin = false;
        try {
            $roles = \DB::table('default_role_users')
                ->join('default_roles', 'default_roles.id', '=', 'default_role_users.role_id')
                ->where('default_role_users.user_id', $userId)
                ->pluck('default_roles.name')
                ->toArray();
            $isHcOrAdmin = in_array('HC', $roles) || in_array('SUPERADMIN', $roles) || in_array('ADMIN', $roles);
        } catch (\Throwable $e) {}

        if (!$isHcOrAdmin) {
            $query->where(function($q) use ($karyId, $userId) {
                if ($karyId) {
                    $q->where('m_kary_id', $karyId);
                } else {
                    $q->where('creator_id', $userId);
                }
            });
        }

        $count = $query->count();
        return $this->helper->customResponse("OK", 200, ['pending_count' => $count]);
    }

    private function prepareDetailRequest($id=null)
    {
        $req = app()->request;
        $details = $req->t_evaluasi_pelatihan_detail ?? [];

        if (empty($details)) {
            if ($id) {
                \App\Models\BasicModels\t_evaluasi_pelatihan_detail::where('t_evaluasi_pelatihan_id', $id)->delete();
            }
            $this->details = [];
            return;
        }

        $cleanDetails = [];
        foreach ($details as $det) {
            $det = is_array($det) ? $det : (array) $det;

            if (empty($det['jenis_evaluasi']) && empty($det['komponen_evaluasi'])) {
                continue;
            }

            $cleanRow = [
                'jenis_evaluasi' => $det['jenis_evaluasi'] ?? null,
                'komponen_evaluasi' => $det['komponen_evaluasi'] ?? null,
                'nilai' => $det['nilai'] ?? null,
            ];

            if ($id && !empty($det['id'])) {
                $isExistingDetail = \App\Models\BasicModels\t_evaluasi_pelatihan_detail::where('id', $det['id'])
                    ->where('t_evaluasi_pelatihan_id', $id)
                    ->exists();

                if ($isExistingDetail) {
                    $cleanRow['id'] = $det['id'];
                }
            }

            $cleanDetails[] = $cleanRow;
        }

        if ($id) {
            $keepIds = array_values(array_filter(array_map(function ($row) {
                return $row['id'] ?? null;
            }, $cleanDetails)));

            $deleteQuery = \App\Models\BasicModels\t_evaluasi_pelatihan_detail::where('t_evaluasi_pelatihan_id', $id);
            if (!empty($keepIds)) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();
        }

        $req->merge(['t_evaluasi_pelatihan_detail' => $cleanDetails]);
    }

    public function t_evaluasi_pelatihan_detail() :HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_evaluasi_pelatihan_detail', 't_evaluasi_pelatihan_id', 'id');
    }

    public function custom_app_detail($req)
    {
        $id = $req->id ?? 1;
        $data = $this->approval->detail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try {
            $data = $this->find($req->id);
            if (!$data) {
                return $this->helper->customResponse("Data tidak ditemukan", 404);
            }
            $data->update([
                'status' => 'POSTED'
            ]);

            // Selesaikan tiket notifikasi pengisian evaluasi di generate_approval
            $app = \DB::table('generate_approval')
                ->where('trx_table', 't_evaluasi_pelatihan')
                ->where('trx_id', $req->id)
                ->first();

            if ($app) {
                \DB::table('generate_approval')->where('id', $app->id)->update([
                    'status' => 'APPROVED',
                    'updated_at' => Carbon::now()
                ]);
                \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->update([
                    'is_done' => true,
                    'action_type' => 'APPROVED',
                    'action_at' => Carbon::now(),
                    'action_user_id' => auth()->user()->id ?? $data->creator_id,
                    'updated_at' => Carbon::now()
                ]);
            }

            \DB::commit();
            return $this->helper->customResponse("Data evaluasi berhasil diposting");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_send_approval()
    {
        $target_id = req("target_id");
        $user_target = $target_id ? default_users::where('m_kary_id', $target_id)->first()?->id : null;
        // dd($user_target, $target_id);

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

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = $this->find($tempId);
        $conf = [
            "app_name" => "APPROVAL EVALUASI PELATIHAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Evaluasi Pelatihan",
            "form_name" => "t_evaluasi_pelatihan",
            "trx_nomor" => $trx->kode,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id ?? auth()->user()->id,
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
            // dd($app);
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