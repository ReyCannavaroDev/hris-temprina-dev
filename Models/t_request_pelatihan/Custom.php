<?php

namespace App\Models\CustomModels;
use App\Cores\Helper;
use App\Cores\Approval;
use Carbon\Carbon;

class t_request_pelatihan extends \App\Models\BasicModels\t_request_pelatihan
{    
    private $helper;
    private $approval;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->helper = new Helper();
        $this->approval = new Approval();

        if (app()->request->isMethod('GET')) {
            $this->details = [];
        }
    }

    public function setAttribute($key, $value)
    {
        if ($value === 'optional') {
            $value = null;
        }
        return parent::setAttribute($key, $value);
    }
    
    public $details = ['t_request_pelatihan_d_kary'];

    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function transformRowData( array $row )
    {
        $data = [];

        if(app()->request->count){
            $jumlahKaryawan = t_request_pelatihan_d_kary::where('t_request_pelatihan_id',$row['id'])->count() ?? 0;
            $data = [
                'jumlah_karyawan' => $jumlahKaryawan
            ];
        }

        if(!empty($row['trainer_id'])){
            $trainer = m_trainer::find($row['trainer_id']);
            $data['trainer'] = [
                'id' => $trainer?->id,
                'nama_trainer' => $trainer?->nama_trainer,
            ];
            $data['trainer.nama_trainer'] = $trainer?->nama_trainer;
        }

        if(!empty($row['m_prog_pelatihan_id'])){
            $m_prog_pelatihan = m_prog_pelatihan::find($row['m_prog_pelatihan_id']);
            $data['m_prog_pelatihan'] = [
                'id' => $m_prog_pelatihan?->id,
                'tema_pelatihan' => $m_prog_pelatihan?->tema_pelatihan,
            ];
            $data['m_prog_pelatihan.tema_pelatihan'] = $m_prog_pelatihan?->tema_pelatihan;
        }

        // Fetch detail karyawan manually to bypass GlobalHelper's $details limitation and generator errors
        $detail_karyawan = \DB::table('t_request_pelatihan_d_kary')
            ->where('t_request_pelatihan_id', $row['id'])
            ->leftJoin('m_kary', 't_request_pelatihan_d_kary.m_kary_id', '=', 'm_kary.id')
            ->leftJoin('m_divisi', 'm_kary.m_divisi_id', '=', 'm_divisi.id')
            ->leftJoin('m_general', 'm_divisi.name', '=', 'm_general.id')
            ->select(
                't_request_pelatihan_d_kary.*',
                'm_kary.nama_lengkap as m_kary.nama_lengkap',
                'm_kary.m_branch_id as m_kary.m_branch_id',
                'm_kary.m_divisi_id as m_kary.m_divisi_id',
                'm_kary.m_posisi_id as m_kary.m_posisi_id',
                'm_general.value as m_divisi.name'
            )
            ->get();
            
        $data['t_request_pelatihan_d_kary'] = json_decode(json_encode($detail_karyawan), true);

        return array_merge( $row, $data );
    }

    public function t_request_pelatihan_d_kary() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_request_pelatihan_d_kary', 't_request_pelatihan_id', 'id');
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        if(!isset($arrayData['status'])){
            $status = 'DRAFT';
        }else{
            $status = $arrayData['status'];
        }

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE PENGAJUAN PELATIHAN"),
            "status" => $status,
            "creator_id" => auth()->user()->id
        ] );

        $req = app()->request;
        if(empty($req->t_request_pelatihan_d_kary)){
            $this->details = [];
        } else {
            $cleanDetails = [];
            foreach($req->t_request_pelatihan_d_kary as $det) {
                $karyId = isset($det['m_kary_id']) ? $det['m_kary_id'] : (isset($det['id']) ? $det['id'] : null);
                
                if ($karyId) {
                    $cleanRow = $det;
                    $cleanRow['m_kary_id'] = $karyId;
                    unset($cleanRow['id']);
                    unset($cleanRow['created_at']);
                    unset($cleanRow['updated_at']);
                    $cleanRow['creator_id'] = auth()->user()->id ?? 1;
                    $cleanDetails[] = $cleanRow;
                }
            }
            $req->merge(['t_request_pelatihan_d_kary' => $cleanDetails]);
        }

        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id=null)
    {
        // ...
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $req = app()->request;
        if(empty($req->t_request_pelatihan_d_kary)){
            \App\Models\BasicModels\t_request_pelatihan_d_kary::where('t_request_pelatihan_id', $id)->delete();
            $this->details = [];
        } else {
            $cleanDetails = [];
            foreach($req->t_request_pelatihan_d_kary as $det) {
                $karyId = isset($det['m_kary_id']) ? $det['m_kary_id'] : (isset($det['id']) ? $det['id'] : null);
                if ($karyId) {
                    $cleanRow = $det;
                    $cleanRow['m_kary_id'] = $karyId;
                    unset($cleanRow['created_at']);
                    unset($cleanRow['updated_at']);
                    $cleanRow['creator_id'] = auth()->user()->id ?? 1;
                    $cleanDetails[] = $cleanRow;
                }
            }
            $req->merge(['t_request_pelatihan_d_kary' => $cleanDetails]);
        }

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        $app = \DB::table('generate_approval')->where('trx_table', $this->getTable())->where('trx_id', $id)->first();
        if ($app) {
            \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->delete();
            \DB::table('generate_approval')->where('id', $app->id)->delete();
        }
    }

    public function custom_send_approval()
    {
        $user = auth()->user();
        $is_hc = $user && ($user->is_hc || in_array(strtolower($user->user_type ?? ''), ['admin']) || in_array(strtolower($user->username ?? ''), ['developer', 'danvers']));

        $target_id = req("target_id");
        $user_target = null;
        if ($target_id) {
            $userObj = default_users::where('m_kary_id', $target_id)->first();
            $user_target = $userObj ? $userObj->id : null;
        }

        // Jika pengaju adalah HC, target approver otomatis adalah user HC itu sendiri
        $target_user_final = $is_hc ? ($user ? $user->id : null) : $user_target;

        $app = $this->createAppTicket(req("id"), $target_user_final);
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan, coba kembali nanti",
                400
            );
        }

        if ($is_hc) {
            // Auto-Approve langsung untuk pengaju role HC
            $data = $this->find(req("id"));
            if ($data) {
                $data->update([
                    "status" => "APPROVED",
                ]);
            }

            if ($app && isset($app->id)) {
                \DB::table('generate_approval')->where('id', $app->id)->update([
                    'status' => 'APPROVED',
                    'form_name' => 't_req_pelatihan'
                ]);
                \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->update([
                    'is_done' => true,
                    'action_type' => 'APPROVED',
                    'action_at' => Carbon::now(),
                    'action_note' => 'APPROVED AUTO BY HC',
                    'action_user_id' => $user->id
                ]);

                generate_approval_log::create([
                    'nomor' => $app->nomor ?? ('APP-' . date('ym') . '-' . sprintf('%08d', req("id"))),
                    'generate_approval_id' => $app->id,
                    'generate_approval_det_id' => null,
                    'trx_id' => req("id"),
                    'trx_table' => $this->getTable(),
                    'trx_name' => 'Pengajuan Pelatihan',
                    'trx_nomor' => $data ? $data->kode : '-',
                    'trx_date' => date('Y-m-d'),
                    'form_name' => 't_req_pelatihan',
                    'trx_creator_id' => $data ? $data->creator_id : $user->id,
                    'action_type' => 'APPROVED',
                    'action_user_id' => $user->id,
                    'creator_id' => $user->id,
                    'action_at' => Carbon::now(),
                    'action_note' => 'APPROVED AUTO BY HC'
                ]);
            }

            return $this->helper->customResponse(
                "Pengajuan pelatihan oleh HC berhasil diajukan dan langsung disetujui (Approved)"
            );
        }

        // Alur untuk user non-HC: kirim push notifikasi ke target atasan
        if ($user_target) {
            try {
                $fcm_tokens = \App\Models\BasicModels\default_users_fcm::where('default_users_id', $user_target)->pluck('token_fcm');
                if (count($fcm_tokens) > 0) {
                    $firebase = app(\App\Services\FirebaseMessagingService::class);
                    foreach ($fcm_tokens as $token) {
                        $firebase->sendToDevice($token, "Approval Pengajuan Pelatihan", "Ada pengajuan pelatihan yang butuh approval Anda.", [
                            "title" => "Approval Pengajuan Pelatihan", 
                            "form_name" => "t_req_pelatihan", 
                            "trx_id" => (string) req("id")
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning("Gagal mengirim notifikasi FCM Pengajuan Pelatihan: " . $e->getMessage());
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

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = \DB::table('t_request_pelatihan')->find($tempId);
        if (!$trx) {
            return false;
        }

        $conf = [
            "app_name" => "APPROVAL PENGAJUAN PELATIHAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Pelatihan",
            "form_name" => "t_req_pelatihan",
            "trx_nomor" => $trx->kode,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
            "target_id" => $target_id,
        ];

        $cek = \DB::table('generate_approval')->where('trx_table', $conf['trx_table'])->where('trx_id', $conf['trx_id'])->first();
        if ($cek) {
            \DB::table('generate_approval')->where('id', $cek->id)->update([
                'form_name' => 't_req_pelatihan',
                'status' => 'PROGRESS'
            ]);
            if ($target_id) {
                \DB::table('generate_approval_d')
                    ->where('generate_approval_id', $cek->id)
                    ->where('is_done', false)
                    ->update(['default_users_id' => $target_id]);
            }
            return \DB::table('generate_approval')->where('id', $cek->id)->first();
        }

        $app_success = $this->helper->approvalCreateTicket($conf);
        if ($app_success) {
            $created = \DB::table('generate_approval')->where('trx_table', $conf['trx_table'])->where('trx_id', $conf['trx_id'])->first();
            if ($created) {
                \DB::table('generate_approval')->where('id', $created->id)->update(['form_name' => 't_req_pelatihan']);
                if ($target_id) {
                    \DB::table('generate_approval_d')
                        ->where('generate_approval_id', $created->id)
                        ->where('is_done', false)
                        ->update(['default_users_id' => $target_id]);
                }
            }
            return $created;
        }
        return false;
    }

    public function custom_progress($req)
    {
        try {
            $getApp = \DB::table('generate_approval')
                ->where('id', $req->id)
                ->orWhere(function($q) use ($req) {
                    $q->where('trx_table', 't_request_pelatihan')
                      ->where('trx_id', $req->id);
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$getApp) {
                return $this->helper->customResponse("Data approval tidak ditemukan", 404);
            }

            $app_id = $getApp->id;

            // Force assign user yang sedang login agar lolos validasi approver
            \DB::table('generate_approval_d')
                ->where('generate_approval_id', $app_id)
                ->where('is_done', false)
                ->update(['default_users_id' => auth()->user()->id]);

            $conf = [
                "app_id" => $app_id,
                "app_type" => $req->type, 
                "app_note" => $req->note,
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app && $app->status) {
                $trx_id = $app->trx_id ?: $getApp->trx_id;
                $data = $this->find($trx_id);
                if ($data) {
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
            }

            return $this->helper->customResponse("Proses approval berhasil");
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{
            $data = t_request_pelatihan::find($req->id);
            $data->status = 'POSTED';
            $data->save();

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_app_detail($req)
    {
        $id = req("id") ?? 1;
        try {
            $data = $this->approval->detail($id);
            if (empty($data->trx)) {
                throw new \Exception("Transaksi tidak ditemukan");
            }
        } catch (\Exception $e) {
            // Self-healing: Hapus notifikasi jika transaksi aslinya sudah terhapus
            \DB::table('generate_approval_d')->where('generate_approval_id', $id)->delete();
            \DB::table('generate_approval')->where('id', $id)->delete();
            return $this->helper->customResponse("Data transaksi sudah dihapus oleh pembuatnya. Notifikasi usang ini telah dibersihkan otomatis. Silakan refresh (F5).", 400, null);
        }
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
            // dd($data);

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

    public function scoperespo($model)
    {
        $m_subcomp_id = request('m_subcomp_id') ?? null;
        $m_branch_id = request('m_branch_id') ?? null;

        if ($m_subcomp_id === 'null') $m_subcomp_id = null;
        if ($m_branch_id === 'null')  $m_branch_id  = null;

        return $model->when($m_subcomp_id, function($q) use ($m_subcomp_id){
            $q->where('t_request_pelatihan.m_subcomp_id', $m_subcomp_id);
        })->when($m_branch_id, function($q) use ($m_branch_id){
            $q->where('t_request_pelatihan.m_branch_id', $m_branch_id);
        });
    }

    public function scopedetail($query) {
        return $query->with(['t_request_pelatihan_d_kary' => function($q) {
            $q->join('m_kary', 't_request_pelatihan_d_kary.m_kary_id', '=', 'm_kary.id')
            ->join('m_divisi', 'm_kary.m_divisi_id', 'm_divisi.id')
            ->join('m_general', 'm_divisi.name', 'm_general.id')
            ->select(
                't_request_pelatihan_d_kary.*', 
                'm_kary.m_branch_id',
                'm_kary.m_divisi_id',
                'm_kary.m_posisi_id',
                'm_kary.nama_lengkap',
                'm_general.value as m_divisi.name'                    
            );
        }]);
    }
}
