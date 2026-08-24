<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class t_efektifitas_pelatihan extends \App\Models\BasicModels\t_efektifitas_pelatihan
{    
    private $helper;
    private $approval;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->approval = getCore("Approval");

        $this->joins = array_values(array_unique(array_merge($this->joins, [
            "m_prog_pelatihan.id=t_efektifitas_pelatihan.m_prog_pelatihan_id",
            "m_trainer.id=t_efektifitas_pelatihan.trainer_id"
        ])));

        if (app()->request->isMethod('GET')) {
            $this->details = [];
            $this->detailsChild = [];
        }
    }

    public $details = ['t_efektifitas_pelatihan_detail'];
    
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
            $data['trainer.nama_trainer'] = $trainer?->nama_trainer;
        }

        if(!empty($row['creator_id'])){
            $user = default_users::find($row['creator_id']);
            $kary = $user?->m_kary_id ? m_kary::find($user->m_kary_id) : null;
            $data['m_kary'] = [
                'id' => $kary?->id,
                'nama_lengkap' => $kary?->nama_lengkap,
            ];
            $data['m_kary.nama_lengkap'] = $kary?->nama_lengkap;
        }

        $detail = \DB::table('t_efektifitas_pelatihan_detail')
            ->where('t_efektifitas_pelatihan_id', $row['id'])
            ->leftJoin('m_kary', 't_efektifitas_pelatihan_detail.m_kary_id', '=', 'm_kary.id')
            ->select(
                't_efektifitas_pelatihan_detail.*',
                'm_kary.nama_lengkap as m_kary.nama_lengkap'
            )
            ->orderBy('t_efektifitas_pelatihan_detail.sequence', 'asc')
            ->orderBy('t_efektifitas_pelatihan_detail.id', 'asc')
            ->get();

        $data['t_efektifitas_pelatihan_detail'] = json_decode(json_encode($detail), true);

        return array_merge( $row, $data );
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $this->prepareDetailRequest();

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE EFEKTIFITAS PELATIHAN"),
            "creator_id" => auth()->user()->id
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
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

    private function prepareDetailRequest($id=null)
    {
        $req = app()->request;
        $details = $req->t_efektifitas_pelatihan_detail ?? [];
        $realisasiId = $req->t_realisasi_pelatihan_id ?? null;
        $atasanId = default_users::find(auth()->user()->id)?->m_kary_id;

        if (empty($details)) {
            if ($id) {
                \App\Models\BasicModels\t_efektifitas_pelatihan_detail::where('t_efektifitas_pelatihan_id', $id)->delete();
            }
            $this->details = [];
            trigger_error("Detail efektifitas pelatihan belum terisi");
        }

        if (!$realisasiId) {
            trigger_error("Realisasi pelatihan wajib dipilih");
        }

        $allowedKaryIds = $atasanId ? \DB::table('t_realisasi_pelatihan_d_kary as d')
            ->join('m_kary', 'm_kary.id', '=', 'd.m_kary_id')
            ->where('d.t_realisasi_pelatihan_id', $realisasiId)
            ->where('m_kary.atasan_id', $atasanId)
            ->pluck('m_kary.id')
            ->toArray() : [];

        if (empty($allowedKaryIds)) {
            trigger_error("Tidak ada peserta pelatihan yang dapat dinilai oleh akun ini");
        }

        $cleanDetails = [];
        foreach ($details as $det) {
            $det = is_array($det) ? $det : (array) $det;
            $mKaryId = $det['m_kary_id'] ?? null;

            if (!$mKaryId || !in_array($mKaryId, $allowedKaryIds)) {
                continue;
            }

            if (empty($det['komponen_efektifitas'])) {
                continue;
            }

            $cleanDetails[] = [
                'm_kary_id' => $mKaryId,
                'komponen_efektifitas' => $det['komponen_efektifitas'],
                'nilai' => $det['nilai'] ?? null,
                'sequence' => $det['sequence'] ?? null,
            ];
        }

        if (empty($cleanDetails)) {
            trigger_error("Detail efektifitas pelatihan belum terisi");
        }

        if ($id) {
            \App\Models\BasicModels\t_efektifitas_pelatihan_detail::where('t_efektifitas_pelatihan_id', $id)->delete();
        }

        $req->merge(['t_efektifitas_pelatihan_detail' => $cleanDetails]);
    }

    public function t_efektifitas_pelatihan_detail() :HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_efektifitas_pelatihan_detail', 't_efektifitas_pelatihan_id', 'id');
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
        $conf = [
            "app_name" => "APPROVAL EFEKTIFITAS PELATIHAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Efektifitas Pelatihan",
            "form_name" => "t_efektifitas_pelatihan",
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

    public function custom_posted($req)
    {
        \DB::beginTransaction();
        try{
            $data = $this->find($req->id);
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
        $id = $req->id ?? 1;
        $data = $this->approval->detail($id);
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