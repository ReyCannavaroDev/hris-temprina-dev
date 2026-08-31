<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class t_realisasi_pelatihan extends \App\Models\BasicModels\t_realisasi_pelatihan
{   
    private $helper;
    private $approval;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->approval = getCore("Approval");

        $this->joins = array_values(array_unique(array_merge($this->joins, [
            "m_prog_pelatihan.id=t_realisasi_pelatihan.m_prog_pelatihan_id"
        ])));
        $this->heirs = array_values(array_unique(array_merge($this->heirs, [
            "t_realisasi_pelatihan_d_kary",
        ])));
        $this->detailsChild = array_values(array_unique(array_merge($this->detailsChild, [
            "t_realisasi_pelatihan_d_kary",
        ])));

        if (app()->request->isMethod('GET')) {
            $this->details = [];
            $this->detailsChild = [];
        }
    }

    public $details = ['t_realisasi_pelatihan_d_kary'];
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function transformRowData( array $row )
    {
        $data = [];

        if(app()->request->count){
            $jumlahKaryawan = t_realisasi_pelatihan_d_kary::where('t_realisasi_pelatihan_id',$row['id'])->count() ?? 0;
            $data = [
                'jumlah_karyawan' => $jumlahKaryawan
            ];
        }

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

        $detail_karyawan = \DB::table('t_realisasi_pelatihan_d_kary')
            ->where('t_realisasi_pelatihan_id', $row['id'])
            ->leftJoin('m_kary', 't_realisasi_pelatihan_d_kary.m_kary_id', '=', 'm_kary.id')
            ->leftJoin('m_branch', 'm_kary.m_branch_id', '=', 'm_branch.id')
            ->leftJoin('m_divisi', 'm_kary.m_divisi_id', '=', 'm_divisi.id')
            ->leftJoin('m_general', 'm_divisi.name', '=', 'm_general.id')
            ->leftJoin('m_posisi', 'm_kary.m_posisi_id', '=', 'm_posisi.id')
            ->select(
                't_realisasi_pelatihan_d_kary.*',
                'm_kary.nama_lengkap as m_kary.nama_lengkap',
                'm_kary.m_branch_id as m_kary.m_branch_id',
                'm_kary.m_divisi_id as m_kary.m_divisi_id',
                'm_kary.m_posisi_id as m_kary.m_posisi_id',
                'm_branch.name as m_branch.name',
                'm_general.value as m_divisi.name',
                'm_posisi.name as m_posisi.name'
            )
            ->get();

        $data['t_realisasi_pelatihan_d_kary'] = json_decode(json_encode($detail_karyawan), true);

        return array_merge( $row, $data );
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        if(!isset($arrayData['status'])){
            $status = 'ACTIVE';
        }else{
            $status = $arrayData['status'];
        }

        $this->prepareDetailRequest();

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE REALISASI PELATIHAN"),
            "status" => $status,
            "creator_id" => auth()->user()->id
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id=null)
    {
        $status = $arrayData['status'] ?? ($model->status ?? 'ACTIVE');
        if (strtoupper($status) === 'ACTIVE') {
            $realisasi = $model instanceof self ? $model : $this->find($id ?? ($model->id ?? null));
            if ($realisasi) {
                $this->createEvaluasiPeserta($realisasi);
            }
        }

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $this->prepareDetailRequest($id);

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function updateAfter($model, $arrayData, $metaData, $id=null)
    {
        $status = $arrayData['status'] ?? ($model->status ?? 'ACTIVE');
        if (strtoupper($status) === 'ACTIVE') {
            $realisasi = $model instanceof self ? $model : $this->find($id ?? ($model->id ?? null));
            if ($realisasi) {
                $this->createEvaluasiPeserta($realisasi);
            }
        }

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    private function prepareDetailRequest($id=null)
    {
        $req = app()->request;
        $details = $req->t_realisasi_pelatihan_d_kary ?? [];

        if (empty($details)) {
            if ($id) {
                \App\Models\BasicModels\t_realisasi_pelatihan_d_kary::where('t_realisasi_pelatihan_id', $id)->delete();
            }
            $this->details = [];
            return;
        }

        $cleanDetails = [];
        foreach ($details as $det) {
            $det = is_array($det) ? $det : (array) $det;
            $karyId = $det['m_kary_id'] ?? (!$id ? ($det['id'] ?? null) : null);

            if (!$karyId) {
                continue;
            }

            $cleanRow = [
                'm_kary_id' => $karyId,
                'creator_id' => auth()->user()->id ?? 1,
            ];

            if ($id && !empty($det['id'])) {
                $isExistingDetail = \App\Models\BasicModels\t_realisasi_pelatihan_d_kary::where('id', $det['id'])
                    ->where('t_realisasi_pelatihan_id', $id)
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

            $deleteQuery = \App\Models\BasicModels\t_realisasi_pelatihan_d_kary::where('t_realisasi_pelatihan_id', $id);
            if (!empty($keepIds)) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();
        }

        $req->merge(['t_realisasi_pelatihan_d_kary' => $cleanDetails]);
    }

    public function t_realisasi_pelatihan_d_kary() :HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_realisasi_pelatihan_d_kary', 't_realisasi_pelatihan_id', 'id');
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

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = $this->find($tempId);
        //dd($this->getTable(), $trx->id, Date("Y-m-d"), $trx->creator_id ?? auth()->user()->id, $target_id);
        $conf = [
            "app_name" => "APPROVAL REALISASI PELATIHAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Realisasi Pelatihan",
            "form_name" => "t_realisasi_pelatihan",
            "trx_nomor" => $trx->kode ?? '001',  
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id ?? auth()->user()->id,
            "target_id" => $target_id,
        ];
        //dd($conf);
        $app = $this->helper->approvalCreateTicket($conf);
        //dd($app);
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

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type
                    ]);
                    if ($req->type == 'APPROVED') {
                        $this->createEvaluasiPeserta($data);
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

    public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{
            $data = $this->find($req->id);
            $data->status = 'POSTED';
            $data->save();
            $this->createEvaluasiPeserta($data);

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function createEvaluasiPeserta($data)
    {
        if (!$data || empty($data->id)) {
            return;
        }

        $peserta = t_realisasi_pelatihan_d_kary::where('t_realisasi_pelatihan_id', $data->id)
            ->whereNotNull('m_kary_id')
            ->pluck('m_kary_id')
            ->unique()
            ->values();

        // Fallback jika pemanggilan terjadi saat detail di database belum selesai ter-commit
        if (!count($peserta) && !empty(app()->request->t_realisasi_pelatihan_d_kary)) {
            $rawPeserta = app()->request->t_realisasi_pelatihan_d_kary;
            $karyIds = [];
            foreach ($rawPeserta as $p) {
                $kId = is_array($p) ? ($p['m_kary_id'] ?? null) : ($p->m_kary_id ?? null);
                if ($kId) {
                    $karyIds[] = $kId;
                }
            }
            $peserta = collect($karyIds)->unique()->values();
        }

        if (!count($peserta)) {
            return;
        }

        $program = $data->m_prog_pelatihan_id ? m_prog_pelatihan::find($data->m_prog_pelatihan_id) : null;

        foreach ($peserta as $m_kary_id) {
            $creatorId = default_users::where('m_kary_id', $m_kary_id)->orderBy('id', 'asc')->pluck('id')->first() ?? auth()->user()->id;
            $evaluasi = t_evaluasi_pelatihan::where('t_realisasi_pelatihan_id', $data->id)
                ->where('m_kary_id', $m_kary_id)
                ->first();

            if (!$evaluasi) {
                $evaluasi = t_evaluasi_pelatihan::create([
                    "kode" => $this->helper->generateNomor("KODE EVALUASI PELATIHAN"),
                    "trainer_id" => $data->trainer_id,
                    "m_kary_id" => $m_kary_id,
                    "m_prog_pelatihan_id" => $data->m_prog_pelatihan_id,
                    "t_realisasi_pelatihan_id" => $data->id,
                    "tanggal" => date('Y-m-d'),
                    "status" => "DRAFT",
                    "creator_id" => $creatorId
                ]);
            } else {
                $evaluasi->update([
                    "trainer_id" => $evaluasi->trainer_id ?? $data->trainer_id,
                    "m_prog_pelatihan_id" => $evaluasi->m_prog_pelatihan_id ?? $data->m_prog_pelatihan_id,
                ]);
            }

            // Buat / sinkronkan tiket notifikasi di generate_approval untuk akun peserta
            $this->createNotificationTicketForPeserta($evaluasi, $creatorId, $program);

            $this->sendEvaluasiNotification($m_kary_id, $evaluasi, $program);
        }
    }

    private function createNotificationTicketForPeserta($evaluasi, $creatorId, $program=null)
    {
        if (!$evaluasi || empty($evaluasi->id) || !$creatorId) {
            return;
        }

        try {
            $existingApp = \DB::table('generate_approval')
                ->where('trx_table', 't_evaluasi_pelatihan')
                ->where('trx_id', $evaluasi->id)
                ->first();

            $tema = $program?->tema_pelatihan ?? (isset($evaluasi->m_prog_pelatihan_id) ? m_prog_pelatihan::find($evaluasi->m_prog_pelatihan_id)?->tema_pelatihan : '');
            $trxName = 'Evaluasi Pelatihan' . ($tema ? ' - ' . $tema : '');

            if (!$existingApp) {
                $gAppId = \DB::table('generate_approval')->insertGetId([
                    'nomor' => 'NOTIF-' . date('ym') . '-' . sprintf('%08d', $evaluasi->id),
                    'trx_id' => $evaluasi->id,
                    'trx_table' => 't_evaluasi_pelatihan',
                    'trx_name' => $trxName,
                    'form_name' => 't_evaluasi_pelatihan',
                    'trx_nomor' => $evaluasi->kode,
                    'trx_date' => $evaluasi->tanggal ?? date('Y-m-d'),
                    'trx_creator_id' => auth()->user()->id ?? $creatorId,
                    'creator_id' => auth()->user()->id ?? $creatorId,
                    'status' => 'PROGRESS',
                    'last_action_id' => auth()->user()->id ?? $creatorId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                $detId = \DB::table('generate_approval_d')->insertGetId([
                    'generate_approval_id' => $gAppId,
                    'level' => 1,
                    'urutan_level' => 1,
                    'type' => 'MENYETUJUI',
                    'default_users_id' => $creatorId,
                    'is_done' => false,
                    'assigned_at' => Carbon::now(),
                    'creator_id' => auth()->user()->id ?? $creatorId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                \DB::table('generate_approval')->where('id', $gAppId)->update([
                    'next_approve_det_id' => $detId,
                    'last_editor_id' => $detId
                ]);
            } else {
                \DB::table('generate_approval')->where('id', $existingApp->id)->update([
                    'status' => 'PROGRESS',
                    'trx_name' => $trxName,
                    'trx_nomor' => $evaluasi->kode,
                    'updated_at' => Carbon::now()
                ]);

                \DB::table('generate_approval_d')
                    ->where('generate_approval_id', $existingApp->id)
                    ->where('is_done', false)
                    ->update([
                        'default_users_id' => $creatorId,
                        'updated_at' => Carbon::now()
                    ]);
            }
        } catch (\Throwable $e) {
            \Log::warning("Gagal membuat tiket notifikasi evaluasi peserta: " . $e->getMessage());
        }
    }

    public function sendEvaluasiNotification($m_kary_id, $evaluasi, $program=null)
    {
        $userIds = default_users::where('m_kary_id', $m_kary_id)->pluck('id');
        if (!count($userIds)) {
            return;
        }

        $tokens = \App\Models\BasicModels\default_users_fcm::whereIn('default_users_id', $userIds)->pluck('token_fcm');
        if (!count($tokens)) {
            return;
        }

        $title = "Evaluasi Pelatihan";
        $tema = $program?->tema_pelatihan ?? (isset($evaluasi->m_prog_pelatihan_id) ? m_prog_pelatihan::find($evaluasi->m_prog_pelatihan_id)?->tema_pelatihan : '');
        $body = "Silakan isi evaluasi pelatihan" . ($tema ? " " . $tema : "") . ".";

        try {
            $firebase = app(\App\Services\FirebaseMessagingService::class);
            foreach ($tokens as $token) {
                try {
                    $firebase->sendToDevice($token, $title, $body, [
                        "title" => $title,
                        "form_name" => "t_evaluasi_pelatihan",
                        "trx_id" => (string) $evaluasi->id,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Gagal mengirim notifikasi evaluasi pelatihan: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("FirebaseMessagingService tidak tersedia atau error: " . $e->getMessage());
        }
    }

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

            $this->createEvaluasiPeserta($data);
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

    public function scoperespo($model)
    {
        $m_subcomp_id = request('m_subcomp_id') ?? null;
        $m_branch_id = request('m_branch_id') ?? null;

        if ($m_subcomp_id === 'null') $m_subcomp_id = null;
        if ($m_branch_id === 'null')  $m_branch_id  = null;

        return $model->when($m_subcomp_id, function($q) use ($m_subcomp_id){
            $q->where('t_realisasi_pelatihan.m_subcomp_id', $m_subcomp_id);
        })->when($m_branch_id, function($q) use ($m_branch_id){
            $q->where('t_realisasi_pelatihan.m_branch_id', $m_branch_id);
        });
    }

    public function scopeowndata($model)
    {
        $m_kary_id = default_users::find(auth()->user()->id)?->m_kary_id;
        // dd($m_kary_id);
        return $model->whereHas('t_realisasi_pelatihan_d_kary', function($q) use ($m_kary_id){
            $q->where('m_kary_id', $m_kary_id);
        });
    }

    public function scopeefektifitas($model)
    {
        $userId = auth()->user()?->id ?? auth()->id();
        $m_kary_id = default_users::find($userId)?->m_kary_id;
        if (!$m_kary_id) {
            return $model->whereRaw('1 = 0');
        }

        $subordinateIds = \DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id FROM m_kary WHERE atasan_id = ?
                UNION
                SELECT k.id FROM m_kary k
                INNER JOIN subordinates s ON k.atasan_id = s.id
            )
            SELECT id FROM subordinates
        ", [$m_kary_id]);

        $ids = array_column($subordinateIds, 'id');
        if (empty($ids)) {
            $ids = [-1];
        }

        return $model->whereIn('t_realisasi_pelatihan.id', function($query) use ($ids) {
            $query->select('d.t_realisasi_pelatihan_id')
                ->from('t_realisasi_pelatihan_d_kary as d')
                ->whereIn('d.m_kary_id', $ids);
        });
    }
}   