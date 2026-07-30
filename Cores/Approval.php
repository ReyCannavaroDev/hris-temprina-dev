<?php
namespace App\Cores;

use \stdClass;
use Illuminate\Support\Facades\DB;
use App\Models\CustomModels\m_approval;
use App\Models\CustomModels\m_approval_det;
use App\Models\CustomModels\default_users;
use App\Models\CustomModels\generate_approval;
use App\Models\CustomModels\generate_approval_d;
use App\Models\CustomModels\generate_approval_log;
use App\Models\CustomModels\m_kary;
use App\Models\CustomModels\m_level_posisi;
use App\Models\CustomModels\m_level_posisi_d;


class Approval
{
    const STATUS_MENGAJUKAN = "MENGAJUKAN";
    const STATUS_MENYETUJUI = "MENYETUJUI";
    const STATUS_PROGRESS = "PROGRESS";
    const STATUS_APPROVED = "APPROVED";
    const STATUS_REJECTED = "REJECTED";
    const STATUS_REVISED = "REVISED";

    public function createTicket(array $config, bool $errorOnFailed = false)
    {
        $app_name           = @$config["app_name"];
        $app_identifier     = @$config["app_identifier"];
        $trx_id             = @$config["trx_id"];
        $trx_table          = @$config["trx_table"];
        $trx_name           = @$config["trx_name"];
        $form_name          = @$config["form_name"];
        $trx_nomor          = @$config["trx_nomor"] ?? '-';
        $trx_date           = @$config["trx_date"];
        $trx_creator_id     = @$config["trx_creator_id"];
        // $min_trans_value    = @$config["min_trans_value"];
        // $max_trans_value    = @$config["max_trans_value"];

        // // min max value transaction
        // $min_value      = @$config["min_value"];
        // $max_value      = @$config["max_value"];

        if (!$app_name)         trigger_error("Config app_name diperlukan");
        if (!$trx_id)           trigger_error("Config trx_id diperlukan");
        if (!$trx_table)        trigger_error("Config trx_table diperlukan");
        if (!$trx_name)         trigger_error("Config trx_name diperlukan");
        if (!$form_name)        trigger_error("Config form_name diperlukan");
        if (!$trx_date)         trigger_error("Config trx_date diperlukan");
        if (!$trx_creator_id)   trigger_error("Config trx_creator_id diperlukan");

        $m_approval = m_approval_det::join("m_approval as a", "a.id", "m_approval_det.m_approval_id")
            ->where("a.name", $app_name)
            ->first();

        $m_approval = m_approval::where('name', $app_name)->where('is_active', true);
        if($app_identifier) $m_approval = $m_approval->where('identifier', $app_identifier);
        $m_approval = $m_approval->first();

        if (!$m_approval) trigger_error("Maaf data approval $app_name tidak ditemukan");

        $details = \DB::select($this->fixedQueryMApp('<>'), [$m_approval->id]);

        $header = [
            "m_comp_id"         => auth()->user()->m_comp_id,
            "m_dir_id"          => auth()->user()->m_dir_id,
            "nomor"             => getCore('Helper')->generateNomor('GENERATE APPROVAL'),
            "m_approval_id"     => $m_approval->id,
            "trx_id"            => $trx_id,
            "trx_table"         => $trx_table,
            "trx_name"          => $trx_name,
            "form_name"         => $form_name,
            "trx_nomor"         => $trx_nomor,
            "trx_date"          => $trx_date,
            "trx_creator_id"    => $trx_creator_id,
            "creator_id"        => auth()->user()->id,
            "status"            => self::STATUS_PROGRESS,
            "last_action_id"    => auth()->user()->id
        ];

        \DB::beginTransaction();
        try {
            $this->checkUserCanCreateTicket($m_approval);
            $this->checkBeforeCreateTicket($config);
            $g_app = generate_approval::create($header);
            foreach ($details as $idx => $d) {
                $det = generate_approval_d::create([
                    "m_comp_id"             => $d->m_comp_id,
                    "m_subcomp_id"          => $d->m_subcomp_id,
                    "m_branch_id"           => $d->m_branch_id,
                    "generate_approval_id"  => $g_app->id,
                    "level"                 => $d->level,
                    "urutan_level"          => $idx + 1,
                    "type"                  => $d->type,
                    "default_users_id"      => $d->default_users_id,
                    "is_full"               => $d->is_full,
                    "is_skip"               => $d->is_skip,
                    // "min_trans_value"       => $min_trans_value,
                    // "max_trans_value"       => $max_trans_value,
                    "is_done"               => $idx == 0 ? true : false, // id pertama inject is_done true
                    "action_user_id"        => $idx == 0 ? auth()->user()->id : null, // id pertama inject user create approval
                    "action_at"             => $idx == 0 ? date('Y-m-d H:i:s') : null, // id pertama inject datetime now()
                    "assigned_at"           => date('Y-m-d H:i:s'),
                    "creator_id"            => auth()->user()->id
                ]);
                if($idx == 1){
                    // first ID
                    generate_approval::where('id', $g_app->id)->update([
                        'last_action_id' => auth()->user()->id,
                        'next_approve_det_id' => $det->id
                    ]);
                }
            }

            
            // insert log pengajuan
            $detailPengajuan = generate_approval_d::where('generate_approval_id', $g_app->id)->where('type',self::STATUS_MENGAJUKAN)->first();
            if($detailPengajuan){
                $log_insert = generate_approval_log::create([
                    "m_comp_id"                 => @$detailPengajuan->m_comp_id,
                    "m_subcomp_id"              => @$detailPengajuan->m_subcomp_id,
                    "m_branch_id"               => @$detailPengajuan->m_branch_id,
                    'nomor'                     => $g_app->no,
                    'generate_approval_id'      => $g_app->id,
                    'generate_approval_d_id'    => @$detailPengajuan->id,
                    'trx_id'                    => $g_app->trx_id,
                    'trx_table'                 => $g_app->trx_table,
                    'trx_name'                  => $g_app->trx_name,
                    'trx_nomor'                 => $g_app->trx_nomor,
                    'trx_date'                  => $g_app->trx_date,
                    'form_name'                 => $g_app->form_name,
                    'trx_creator_id'            => $g_app->trx_creator_id,
                    'action_type'               => self::STATUS_MENGAJUKAN,
                    'action_user_id'            => auth()->user()->id,
                    'creator_id'                => auth()->user()->id,
                    'action_at'                 => date('Y-m-d H:i:s'),
                    'action_note'               => ''
                ]); 
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            if ($errorOnFailed) {
                trigger_error($e->getMessage().'-'.$e->getLine());
            }
            return false;
        }
        return true;
    }

    private function checkUserCanCreateTicket($m_approval)
    {
        $text = "Anda tidak memiliki akses untuk membuat tiket approval ini";
        $userAuth = auth()->user();
        $next = true;
        $code = "";

        $respo_active = getCore('Respo')->checkRespoActive();
        $m_comp_id = @$respo_active->m_comp_id;
        $m_subcomp_id = @$respo_active->m_subcomp_id;
        $m_branch_id = @$respo_active->m_branch_id;
        
        // $details = \DB::select($this->fixedQueryMApp('=', self::STATUS_MENGAJUKAN), [$m_approval->id]);
        $sql = "select d.* from m_approval_det d 
            join m_approval a on a.id = d.m_approval_id 
            where a.id = ? and type = ? order by d.level asc";
        
        $details = \DB::select($sql, [$m_approval->id, self::STATUS_MENGAJUKAN]);


        if (!count($details)) {
            $text = "Setting approval tipe MENGAJUKAN tidak ditemukan";
            $next = false;
            $code = "0000001";
        }

        
        $next = false;
        foreach ($details as $detail) {
            if ($detail->default_users_id == $userAuth->id){
                if ($detail->m_comp_id && $detail->m_subcomp_id && $detail->m_branch_id) {
                    if($detail->m_comp_id != $m_comp_id && $detail->m_subcomp_id != $m_subcomp_id && $detail->m_branch_id != $m_branch_id){
                        $text = "Anda tidak memiliki akses untuk membuat tiket approval ini";
                        $next = false;
                        $code = "0000004";
                        trigger_error("$text | Warning Code : $code");
                    }
                }
                $next = true;
                break;
            }
        }

        if ($next === false) {
            $text = "Anda tidak memiliki akses untuk membuat tiket approval ini";
            $code = "0000003";
            trigger_error("$text | Warning Code : $code");
        }

        // if (!$next) {
        //     trigger_error($text . " | Warning Code : $code");
        // }
    }

    private function checkBeforeCreateTicket($conf)
    {
        $text = "Status tidak memungkinkan untuk dilanjutkan proses approval";
        $userAuth = auth()->user();
        $next = true;
        $code = "";

        $check = generate_approval::where('trx_table', $conf['trx_table'])
                    ->where('trx_id', $conf['trx_id'])
                    ->where('status','PROGRESS')
                    ->exists();

        $check2 = \DB::table($conf['trx_table'])
                    ->where('id', $conf['trx_id'])
                    ->first ();
        // $check2StatusGen = getBasic('m_general')->where('id', @$check2->status_id)->where('is_active', true)->whereRaw("value2 not in('2','21')")->exists();
        // $check2StatusGen = true;

        if ($check && !@$conf['bypass_status_validation']) {
            $text = "Sudah terdapat data approval sebelumnya untuk transaksi ini. Selesaikan terlebih dahulu approval terkait.";
            $next = false;
            $code = "00000101";
        }

        // if (!@$conf['bypass_status_validation']) {
        //     $text = "Status transaksi tidak memungkinkan untuk melanjutkan approval.";
        //     $next = false;
        //     $code = "00000102";
        // }

        if (!$next) {
            trigger_error($text . " | Warning Code : $code");
        }
    }

    protected function fixedQueryMApp($operator = '<>', $status='')
    {
        return "select d.* from m_approval_det d join m_approval a on a.id = d.m_approval_id where a.id = ? and type $operator '$status' order by d.level asc";
    }

    public function progress(array $config, bool $errorOnFailed = false)
    {
        // PROGRESS APPROVAL
        $app_id = $config["app_id"];
        $app_type = $config["app_type"];
        $app_note = $config["app_note"];

        $g_app = generate_approval::where('id', $app_id)->first();

        if (!$g_app)    trigger_error("Maaf data approval tidak ditemukan");

        if (!$app_type) trigger_error("Tipe approval diperlukan!");

        if (!in_array($app_type, [self::STATUS_APPROVED, self::STATUS_REVISED, self::STATUS_REJECTED])) {
            trigger_error("Tipe approval tidak sesuai");
        }

        if (in_array($app_type, [self::STATUS_REVISED, self::STATUS_REJECTED])) {
            if (!$app_note) trigger_error("Catatan approval diperlukan!");
        }

        $fixedConfig = "select d.* from generate_approval_d d join generate_approval a on a.id = d.generate_approval_id where a.id = ? and d.is_done = false order by d.urutan_level limit 1";
        $process_data = \DB::select($fixedConfig, [$g_app->id]);


        if (!count($process_data)) trigger_error("Maaf data approval tidak ditemukan");

        $process_data = $process_data[0];
        
        \DB::beginTransaction();
        try {
            $this->checkUserCanApprove($process_data);
            $whereRawUpdate = "generate_approval_id = $g_app->id and id = $process_data->id";
            
            // Update ticket approval sesuai kondisi di atas
            $check = generate_approval_d::whereRaw($whereRawUpdate)->update([
                'action_at' => date('Y-m-d H:i:s'),
                'action_type' => $app_type,
                'action_user_id' => auth()->user()->id,
                'action_note' => $app_note,
                'is_done' => true,
            ]);
            // Check approval level berikutnya
            $outstanding = generate_approval_d::where('generate_approval_id', $g_app->id)
                ->where('is_done', false)
                ->where('urutan_level', '>', $process_data->urutan_level)
                ->first();
            
            // Jika masih ada outstanding, update waktu assign approval selanjutnya
            if ($outstanding && $app_type != self::STATUS_REJECTED && $app_type != self::STATUS_REVISED) {
                $finish = false;
                //update last approver by detail approval
               $last_det_approver = generate_approval_d::where('generate_approval_id',$g_app->id)
                ->where('type','MENYETUJUI')
                ->where('is_done',false)
                ->orderBy('id','asc')->pluck('id')->first();

                generate_approval::where('id',$g_app->id)->update([
                    'last_action_id' => auth()->user()->id,
                    'next_approve_det_id' => $outstanding->id
                ]);

                generate_approval_d::where('id',$last_det_approver)->update(['assigned_at' => date('Y-m-d H:i:s')]);
                generate_approval::find($g_app->id)->update(['last_approve_id'=>null]);
            } else {
                $finish = true;
                // Jika tidak ada, update status header approval
                generate_approval::find($g_app->id)->update([
                    'status' => $app_type, 
                    'last_action_id' => auth()->user()->id,
                    'next_approve_det_id'=>null
                ]);
            }
            $log_insert = generate_approval_log::create([
                'nomor'                     => $g_app->no,
                'm_comp_id'                 => @$process_data->m_comp_id,
                'm_subcomp_id'              => @$process_data->m_subcomp_id,
                'm_branch_id'               => @$process_data->m_branch_id,
                'generate_approval_id'      => $g_app->id,
                'generate_approval_d_id'    => @$proccess_data->id ?? 0,
                'trx_id'                    => $g_app->trx_id,
                'trx_table'                 => $g_app->trx_table,
                'trx_name'                  => $g_app->trx_name,
                'trx_nomor'                 => $g_app->trx_nomor,
                'trx_date'                  => $g_app->trx_date,
                'form_name'                 => $g_app->form_name,
                'trx_creator_id'            => $g_app->trx_creator_id,
                'action_type'               => $app_type,
                'action_user_id'            => auth()->user()->id,
                'action_at'                 => date('Y-m-d H:i:s'),
                'action_note'               => $app_note
            ]);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            if ($errorOnFailed) trigger_error($e->getMessage());
            $app = new stdClass();
            $app->status = false;
            $app->trx_id = 0;
            return $app;
        }
        $app = new stdClass();
        $app->status = true;
        $app->finish = $finish;
        $app->trx_id = $g_app->trx_id;
        return $app;
    }

    private function checkUserCanApprove($approval)
    {
        $text = "Anda tidak memiliki akses untuk menyetujui tiket approval ini";
        $userAuth = auth()->user();
        $next = true;
        $code = "";

        $respo_active = getCore('Respo')->checkRespoActive();
        $m_comp_id = @$respo_active->m_comp_id;
        $m_subcomp_id = @$respo_active->m_subcomp_id;
        $m_branch_id = @$respo_active->m_branch_id;


        if ($approval->default_users_id && $approval->default_users_id !== $userAuth->id) {
            $text = "Anda tidak memiliki akses untuk menyetujui tiket approval ini";
            $next = false;
            $code = "0000201";
        }

        if ($approval->m_comp_id && $approval->m_subcomp_id && $approval->m_branch_id) {
            if($approval->m_comp_id != $m_comp_id && $approval->m_subcomp_id != $m_subcomp_id && $approval->m_branch_id != $m_branch_id){
                $text = "Anda tidak memiliki akses untuk menyetujui tiket approval ini";
                $next = false;
                $code = "0000202";
            }
        }

        if (!$next) {
            trigger_error($text . " | Warning Code : $code");
        }
    }

    public function outstanding()
    {
        $user_id = auth()->user()->id;
        $model = new generate_approval;

        $data = generate_approval::selectRaw("generate_approval.*,(select u.name from default_users u where u.id = generate_approval.creator_id) creator")
            ->leftJoin('default_users', 'default_users.id', 'generate_approval.creator_id')
            ->whereRaw("
                generate_approval.status = 'PROGRESS' 
                and generate_approval.id 
                    in(select generate_approval_id from generate_approval_d d 
                    join default_users s on s.id = d.default_users_id
                    where d.id = generate_approval.next_approve_det_id and s.id = ?)", [$user_id])
            ->orderBy('generate_approval.id', 'desc')
            ->search(['trx_name', 'nomor', 'trx_date', 'trx_nomor', 'default_users.name'])
            ->paginate(app()->request->paginate ?? 50);

        return $data;
    }

    // public function outstanding()
    // {
    //     $user_id = auth()->user()->id;

    //     $m_posisi = m_kary::whereHas('default_users', function($q) use ($user_id){
    //         $q->where('id', $user_id);
    //     })->first()?->m_posisi_id;

    //     $level_posisi_sequence = $m_posisi
    //         ? m_level_posisi::whereHas('m_level_posisi_d', fn($q) =>
    //             $q->where('m_posisi_id', $m_posisi)
    //         )->first()?->sequence
    //         : null;

    //     if (!$level_posisi_sequence) {
    //         return generate_approval::whereRaw('1=0')->paginate(50);
    //     }

    //     $data = generate_approval::selectRaw("
    //             generate_approval.*,
    //             (select u.name from default_users u where u.id = generate_approval.creator_id) as creator
    //         ")
    //         ->leftJoin('default_users', 'default_users.id', 'generate_approval.creator_id')
    //         ->whereRaw("
    //             generate_approval.status = 'PROGRESS'
    //             AND generate_approval.id IN (
    //                 SELECT d.generate_approval_id 
    //                 FROM generate_approval_d d 
    //                 JOIN m_level_posisi lp ON lp.id = d.m_level_posisi_id
    //                 WHERE d.id = generate_approval.next_approve_det_id
    //                 AND lp.sequence <= ?
    //             )
    //         ", [$level_posisi_sequence])
    //         ->orderBy('generate_approval.id', 'desc')
    //         ->search(['trx_name', 'nomor', 'trx_date', 'trx_nomor', 'default_users.name'])
    //         ->paginate(app()->request->paginate ?? 50);

    //     return $data;
    // }


    // public function outstanding()
    // {
    //     $user_id = auth()->user()->id;

    //     // ===========================
    //     // 1. Posisi user
    //     // ===========================
    //     $m_posisi = m_kary::whereHas('default_users', function($q) use ($user_id){
    //         $q->where('id', $user_id);
    //     })->first()?->m_posisi_id;

    //     // ===========================
    //     // 2. Level posisi
    //     // ===========================
    //     $level_posisi = $m_posisi
    //         ? m_level_posisi::whereHas('m_level_posisi_d', fn($q) =>
    //             $q->where('m_posisi_id', $m_posisi)
    //         )->first()
    //         : null;
        
    //     if (!$level_posisi) {
    //         return generate_approval::whereRaw("1 = 0")->paginate(50);
    //     }

    //     // ===========================
    //     // 3. Ambil semua level yang >= sequence user
    //     // ===========================
    //     $posisi_yang_boleh = m_level_posisi::where('sequence', '>=', $level_posisi->sequence)
    //         ->pluck('id')
    //         ->toArray();


    //     $posisi_ids = m_level_posisi_d::whereIn('m_level_posisi_id', $posisi_yang_boleh)
    //         ->pluck('m_posisi_id')
    //         ->toArray();

    //     // ===========================
    //     // 4. Ambil semua user yang memegang posisi tsb
    //     // ===========================
    //     $user_ids_yang_boleh = m_kary::whereIn('m_posisi_id', $posisi_ids)
    //         ->with('default_users:id')
    //         ->get()
    //         ->pluck('default_users.*.id')
    //         ->flatten()
    //         ->filter(fn($x) => !empty($x) && is_numeric($x)) // <── FILTER WAJIB
    //         ->unique()
    //         ->values()
    //         ->toArray();

    //     // Jika tetap kosong → langsung return kosong
    //     if (empty($user_ids_yang_boleh)) {
    //         return generate_approval::whereRaw("1 = 0")->paginate(50);
    //     }

    //     // Convert array menjadi format SQL aman
    //     $ids_sql = implode(',', $user_ids_yang_boleh);

    //     // ===========================
    //     // 5. Query approval
    //     // ===========================
    //     $data = generate_approval::selectRaw("
    //                 generate_approval.*,
    //                 (select u.name from default_users u where u.id = generate_approval.creator_id) as creator
    //             ")
    //         ->leftJoin('default_users', 'default_users.id', 'generate_approval.creator_id')
    //         ->where('generate_approval.status', 'PROGRESS')
    //         ->whereRaw("
    //             generate_approval.id in (
    //                 select d.generate_approval_id 
    //                 from generate_approval_d d 
    //                 join default_users s on s.id = d.default_users_id
    //                 where d.id = generate_approval.next_approve_det_id
    //                 and s.id in ($ids_sql)
    //             )
    //         ")
    //         ->orderBy('generate_approval.id', 'desc')
    //         ->search(['trx_name', 'nomor', 'trx_date', 'trx_nomor', 'default_users.name'])
    //         ->paginate(app()->request->paginate ?? 50);

    //     return $data;
    // }



    public function detail($id)
    {
        $app = \DB::table('generate_approval')->selectRaw("*, to_char(generate_approval.created_at,'DD-MM-YYYY HH24:MI:SS') created_at, (select u.name from default_users u where u.id = generate_approval.creator_id) creator")->where('id', $id)->first();

        if (!$app) {
            trigger_error("Approval tidak ditemukan");
        }

        $app->tahap_saat_ini = \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->where('is_done', false)->orderBy('urutan_level', 'asc')->pluck('urutan_level')->first() ?? 0;
        $app->tahap_total = \DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->count();

        $trx = \DB::table($app->trx_table)->where('id', $app->trx_id)->first();

        $data = new stdClass();
        $data->approval = $app;
        $data->approval_log = $this->log(['trx_id' => $app->trx_id, 'trx_table' => $app->trx_table]);
        $data->trx = $trx;

        return $data;
    }

    public function log($conf)
    {
        $trx_id = @$conf['trx_id'];
        $trx_table = @$conf['trx_table'];
        $trx_name = @$conf['trx_name'];

        $rawSql = "";
        if(isset($trx_name)){
            $rawSql = " and a.trx_name = '$trx_name'";
        }


        $data = \DB::select("
            select 
                a.id generate_approval_id, d.id, (case when d.action_type != 'APPROVED' then d.type || ' - (' || d.action_type || ')' else d.type end ) action_type, a.trx_nomor, 
                (case
                    --ambil log untuk ambil data level pengajuan
                    when d.type = 'MENGAJUKAN' then (select s.name from default_users s where s.id = l.action_user_id limit 1)
                    when d.type = 'MENYETUJUI' and d.action_user_id is null then s.name
                    else u.name
                end) action_user, 
                coalesce((case 
                    --ambil log untuk ambil data level pengajuan
                    when d.type = 'MENGAJUKAN' then to_char(l.action_at,'DD-MM-YYYY HH24:MI:SS')
                    when d.type = 'MENYETUJUI' and a.status != 'PROGRESS' and d.action_at is null and l.action_at is null then '-' 
                    else to_char(d.action_at,'DD-MM-YYYY HH24:MI:SS')
                end), 'Menunggu Approval') action_at, 
                d.action_note
            from generate_approval_d d 
            join generate_approval a on a.id = d.generate_approval_id
            left join default_users u on u.id = d.action_user_id
            left join default_users s on s.id = d.default_users_id
            left join generate_approval_log l on l.generate_approval_d_id = d.id
            where a.trx_table = ? and a.trx_id = ? $rawSql
            order by a.id, d.level 
        ", [$trx_table,$trx_id]);
        
        return $data;
    }

    public function lastLog($conf)
    {
        $trx_id = @$conf['trx_id'];
        $trx_table = @$conf['trx_table'];
        $trx_name = @$conf['trx_name'];

        $data = \DB::table('generate_approval_log')
                    ->select('generate_approval_log.*','default_users.name as approval_user')
                    ->leftJoin('default_users','default_users.id','generate_approval_log.action_user_id')
                    ->where('trx_table', $trx_table)->where('trx_id', $trx_id);

        if($trx_name) $data = $data->where('trx_name', $trx_name);

        return $data->orderBy('id','desc')->first();
    }
}
