<?php

namespace App\Cores;

use Carbon\Carbon;
use DB;
use App\Models\CustomModels\generate_num;
use App\Models\CustomModels\generate_num_type;
use App\Models\CustomModels\generate_num_log;
use App\Models\CustomModels\generate_num_det;
use App\Models\CustomModels\m_approval;
use App\Models\CustomModels\m_approval_det;
use App\Models\CustomModels\generate_approval;
use App\Models\CustomModels\generate_approval_det;
use App\Models\CustomModels\generate_approval_d;
use App\Models\CustomModels\generate_approval_log;
use App\Models\CustomModels\m_kary;
use App\Models\CustomModels\default_users;
use App\Models\CustomModels\m_level_posisi;
use \stdClass;
use App\Services\FirebaseMessagingService;
use Illuminate\Support\Facades\Schema;


class Helper
{
    function __construct()
    {
        $this->timestamp = \Carbon\Carbon::now();
    }

    public function generateNomor($nama, $counter = true, $static = null, $date = null)
    {
        // check header config
        $generate_num = generate_num::where("nama", $nama)
            ->where("is_active", true)
            ->first();

        if (!$static && !$generate_num) {
            trigger_error("Format penomoran tidak ditemukan");
        }

        DB::beginTransaction();

        try {
            // check details config and assemble code
            $temporaryCode = "";

            if ($static) {
                $generate_num_det = $static;
            } else {
                $generate_num_det = generate_num_det::where(
                    "generate_num_id",
                    $generate_num->id
                )
                    ->orderBy("seq", "asc")
                    ->get();
            }

            foreach ($generate_num_det as $tnd) {
                $trx_type = generate_num_type::find(
                    @$tnd["generate_num_type_id"]
                );

                if ($trx_type) {
                    if ($trx_type->ref_type === "text") {
                        // type text
                        $temporaryCode .= (string) $trx_type->value;
                    } elseif (
                        in_array($trx_type->ref_type, ["day", "month", "year"])
                    ) {
                        // type dating
                        // $temporaryCode .= date($trx_type->value);
                        $timestamp = $date ? strtotime($date) : time();
                        $temporaryCode .= date($trx_type->value, $timestamp);
                    } elseif ($trx_type->ref_type === "seq") {
                        // type seq
                        $table = "generate_num";
                        $length = (int) $trx_type->value ?? 6;
                        $lastDataQuery = generate_num_log::where(
                            "nama",
                            @$generate_num->nama
                        )
                            ->where("table", $table)
                            ->orderBy("created_at", "DESC");

                        $latest = $lastDataQuery->pluck("seq")->first();

                        if (!$latest) {
                            $latest = "";

                            for ($i = 0; $i < $length; $i++) {
                                $latest .= "0";
                            }
                        }

                        $latest = sprintf("%0" . $length . "d", $latest + 1);
                        $temporaryCode .= $latest;

                        if ($counter && !$static) {
                            if ($lastDataQuery->exists()) {
                                generate_num_log::where("table", $table)
                                    ->where("nama", $generate_num->nama)
                                    ->update([
                                        "value" => $temporaryCode,
                                        "seq" => $latest,
                                    ]);
                            } else {
                                generate_num_log::create([
                                    "table" => $table,
                                    "nama" => $generate_num->nama,
                                    "value" => $temporaryCode,
                                    "seq" => $latest,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->responseCatch($e);
        }

        return $temporaryCode;
    }

    public function terbilang($x)
    {
        $angka = [
            "",
            "Satu",
            "Dua",
            "Tiga",
            "Empat",
            "Lima",
            "Enam",
            "Tujuh",
            "Delapan",
            "Sembilan",
            "Sepuluh",
            "Sebelas",
        ];

        if ($x < 12) {
            return " " . $angka[$x];
        } elseif ($x < 20) {
            return $this->terbilang($x - 10) . " Belas ";
        } elseif ($x < 100) {
            return $this->terbilang($x / 10) .
                " Puluh " .
                $this->terbilang($x % 10);
        } elseif ($x < 200) {
            return "Seratus" . $this->terbilang($x - 100);
        } elseif ($x < 1000) {
            return $this->terbilang($x / 100) .
                " Ratus" .
                $this->terbilang($x % 100);
        } elseif ($x < 2000) {
            return "Seribu" . $this->terbilang($x - 1000);
        } elseif ($x < 1000000) {
            return $this->terbilang($x / 1000) .
                " Ribu " .
                $this->terbilang($x % 1000);
        } elseif ($x < 1000000000) {
            return $this->terbilang($x / 1000000) .
                " Juta " .
                $this->terbilang($x % 1000000);
        }
    }

    public function responseValidate($validator)
    {
        $err = [];
        $errText = "";
        $error = $validator->messages()->toArray();

        foreach ($error as $key => $value) {
            $err[$key] = $value[0];
            if (count($error) > 1) {
                $errText .= $value[0] . "<br>";
            } else {
                $errText .= $value[0];
            }
        }

        $data = [
            "errors" => $err,
            "errorText" => $errText,
        ];

        return response(
            [
                "timestamp" => Carbon::now()->format("d-m-Y H:i:s"),
                "code" => 422,
                "message" => "Cek kembali form yang anda kirim.",
                "data" => $data,
            ],
            422
        );
    }

    public function customResponse(
        $message = "OK",
        $code = 200,
        $basic = null,
        $noData = true
    ) {
        if (!in_array($code, [200, 201])) {
            $err = [];
            $errText = "";
            $error = [$basic ?? $message];

            if (!$basic) {
                foreach ($error as $key => $value) {
                    $err[$key] = $value;
                    if ($key != 0) {
                        $errText .= $value . "<br>";
                    } else {
                        $errText .= $value;
                    }
                }

                $data = [
                    "errors" => $err,
                    "errorText" => $errText,
                ];
            } else {
                $data = $basic ?? [$message];
            }
        } else {
            if (!$noData) {
                $data = [
                    "data" => $basic ?? [$message],
                ];
            } else {
                $data = $basic ?? [$message];
            }
        }

        return response(
            [
                "timestamp" => Carbon::now()->format("d-m-Y H:i:s"),
                "code" => $code,
                "message" => $message,
                "data" => $data,
            ],
            $code
        );
    }

    public function responseCatch($e)
    {
        return response(
            [
                "timestamp" => Carbon::now()->format("d-m-Y H:i:s"),
                "code" => 400,
                "message" => $e->getMessage(),
                "data" => [
                    "errors" => [
                        $e->getMessage() .
                        "-" .
                        $e->getLine() .
                        "-" .
                        $e->getFile(),
                    ],
                    "errorText" => $e->getMessage(),
                ],
            ],
            400
        );
    }

    const STATUS_MENYETUJUI = "MENYETUJUI";
    const STATUS_MENGETAHUI = "MENGETAHUI";
    const STATUS_PROGRESS = "PROGRESS";
    const STATUS_APPROVED = "APPROVED";
    const STATUS_HALF_APPROVED = "HALF APPROVED";
    const STATUS_REJECTED = "REJECTED";
    const STATUS_REVISED = "REVISED";

    protected function checkUserCanCreateTicket($m_approval)
    {
        $text = "Anda tidak memiliki akses untuk membuat tiket approval ini";
        $userAuth = auth()->user();
        $next = true;
        $kode = "";

        $fixedConfig = "select d.* from m_approval_det d join m_approval a on a.id = d.m_approval_id where a.id= ? AND type = 'MENGAJUKAN' ORDER BY d.level ASC";
        $details = DB::select($fixedConfig, [$m_approval->id]);

        if (!count($details)) {
            $text = "Setting approval tipe MENGAJUKAN tidak ditemukan";
            $next = false;
            $kode = "0000001";
        }

        $detail = $details[0];

        if ($detail->default_users_id && $detail->default_users_id !== $userAuth->id) {
            $text = "Anda tidak memiliki akses untuk membuat tiket approval ini";
            $next = false;
            $kode = "0000003";
        }

        if (!$next) {
            trigger_error($text . " | Warning Code : $kode");
        }
    }

    protected function checkUserCanApprove($approval_det)
    {
        $text = "Anda tidak memiliki akses untuk melanjutkan approval ini";
        $userAuth = auth()->user();
        $next = true;
        $kode = "";

        if ($approval_det->type !== self::STATUS_MENYETUJUI && $approval_det->type !== self::STATUS_MENGETAHUI) {
            $next = false;
            $kode = "0000001";
        }

        // $check = generate_approval::whereRaw("generate_approval.status = 'PROGRESS' 
        //         and generate_approval.id = $approval_det->generate_approval_id
        //         and case when generate_approval.next_approve_det_id is not null then
        //             generate_approval.next_approve_det_id = $userAuth->id
        //         else
        //             generate_approval.id in(select d.generate_approval_id from generate_approval_d d)
        //         end
        //     ")->exists();
       $check = generate_approval::where('status', 'PROGRESS')
                ->where('id', $approval_det->generate_approval_id)
                ->whereHas('generate_approval_d', function($q) use ($userAuth){
                    $q->where('default_users_id', $userAuth->id);
                })
                ->exists();
        // dd($check, $approval_det->generate_approval_id, $userAuth->id);
            
        if(!$check){
            $next = false;
            $kode = "0000002, $text";
        }

        $check_log = generate_approval_log::where('generate_approval_d_id', $approval_det->id)->exists();
        // dd($approval_det->type, self::STATUS_MENYETUJUI, $check_log, $next);

        if ($check_log) {
            $next = false;
            $kode = "0000003, Approval sudah dilakukan sebelumnya";
        }

        if (!$next) {
            trigger_error($text . " | Warning Code : $kode");
        }
    }

    public function approvalCreateTicket(array $config, bool $errorOnFailed = false)
    {
        $app_name = @$config["app_name"];
        $trx_id = @$config["trx_id"];
        $trx_table = @$config["trx_table"];
        $trx_name = @$config["trx_name"];
        $form_name = @$config["form_name"];
        $trx_nomor = @$config["trx_nomor"];
        $trx_date = @$config["trx_date"];
        $trx_creator_id = @$config["trx_creator_id"];
        //target default_user_id approval
        $target_id = @$config["target_id"];

        // dd($config);

        if (!$trx_table) {
            trigger_error("Config trx_table diperlukan");
        }

        if (!$trx_name) {
            trigger_error("Config trx_name diperlukan");
        }

        if (!$form_name) {
            trigger_error("Config form_name diperlukan");
        }

        if (!$trx_nomor) {
            trigger_error("Config trx_nomor diperlukan");
        }

        if (!$trx_date) {
            trigger_error("Config trx_date diperlukan");
        }

        if (!$trx_creator_id) {
            trigger_error("Config trx_creator_id diperlukan");
        }

        $m_approval = m_approval_det::join("m_approval as a", "a.id", "m_approval_det.m_approval_id")
            ->where("a.name", $app_name)
            ->first();
        
        if (!$m_approval) {
            trigger_error("Maaf data approval $app_name tidak ditemukan");
        }

        $fixedConfig = "select d.* from m_approval_det d join m_approval a on a.id = d.m_approval_id where a.id= ? AND type<>'MENGAJUKAN' ORDER BY d.level ASC";
        $details = DB::select($fixedConfig, [$m_approval->m_approval_id]);

        //$this->checkUserCanCreateTicket($m_approval);

        // $check_approve_atasan = m_approval_det::join('m_role','m_role.id','m_approval_det.m_role_id')
        //===
        // $check_approve_atasan = m_approval_det::
        //     where('m_approval_id',$m_approval->id)
        //     ->where('level', 2)
        //     ->first();
        // // dd($check_approve_atasan);
        // $trx = DB::table($trx_table)
        // ->select('m_kary_id')
        // ->where('id', $trx_id)
        // ->first();

        // if(strtolower(@$check_approve_atasan->name) == 'atasan'){
        //     $atasan_id = m_kary::join('default_users as u','u.m_kary_id','m_kary.id')->where('u.id',auth()->user()->id)->pluck('atasan_id')->first() ?? 0;
        //     $user_atasan_id = default_users::whereRaw("default_users.m_kary_id = $atasan_id")->orderBy('id','asc')->pluck('id')->first();
        // }

        // //new
        
        // $atasan_id = m_kary::where('id', $trx->m_kary_id)?->first()?->atasan_id ?? 0;
        // // dd($trx->m_kary_id, $atasan_id);    
        // $user_atasan_id = default_users::where('m_kary_id', $atasan_id)->pluck('id')->first();
        //===

        $check_approve_atasan = m_approval_det::
            where('m_approval_id', $m_approval->id)
            ->where('level', 2)
            ->first();

        $columns = Schema::getColumnListing($trx_table);

        // default value
        $m_kary_id = null;
        $creator_id = null;

        // ambil data sesuai kolom yang tersedia
        $query = DB::table($trx_table)->where('id', $trx_id);

        if (in_array('m_kary_id', $columns)) {
            $query->addSelect('m_kary_id');
        }
        if (in_array('creator_id', $columns)) {
            $query->addSelect('creator_id');
        }

        $trx = $query->first();

        // dd($target_id);
        if (!$target_id) {
            // fallback: kalau gak ada m_kary_id, pakai creator_id
            $m_kary_id = $trx->m_kary_id ?? $trx->creator_id ?? 0;
            // ambil atasan dari m_kary
            $atasan_id = m_kary::where('id', $m_kary_id)?->first()?->atasan_id ?? 0;
            // ambil user atasan
            $user_atasan_id = default_users::where('m_kary_id', $atasan_id)->pluck('id')->first();
        }else{
            $user_atasan_id = $target_id;
        }


        // dd($atasan_id, $user_atasan_id);

        //ambil level atasnya
        // $m_kary = m_kary::find($m_kary_id);
        // $m_posisi = $m_kary?->m_posisi_id ?? 0;
        // $m_branch_id = $m_kary?->m_branch_id ?? 0;
        // $m_subcomp_id = $m_kary?->m_subcomp_id ?? 0;

        // $level_posisi = $m_posisi
        //     ? m_level_posisi::whereHas('m_level_posisi_d', fn($q) => 
        //         $q->where('m_posisi_id', $m_posisi)
        //     )->first()
        //     : null;

        // $higher_level = $level_posisi
        //     ? m_level_posisi::where('sequence', '>', $level_posisi->sequence)
        //         ->orderBy('sequence')
        //         ->first()
        //     : null;

        $header = [
            // "m_subcomp_id" => $m_subcomp_id,
            // "m_branch_id" => $m_branch_id,
            "nomor" => $this->generateNomor('KODE APPROVAL'),
            "m_approval_id" => $m_approval->m_approval_id,
            "trx_id" => $trx_id,
            "trx_table" => $trx_table,
            "trx_name" => $trx_name,
            "form_name" => $form_name,
            "trx_nomor" => $trx_nomor,
            "trx_date" => $trx_date,
            "trx_creator_id" => $trx_creator_id,
            "creator_id" => auth()->user()->id,
            "status" => self::STATUS_PROGRESS,
            // "last_approve_id" => @$user_atasan_id ?? null7
            // "last_editor_id" => @$user_atasan_id ?? null

        ];

        DB::beginTransaction();

        try {
            $g_app = generate_approval::create($header);

            // dd($details);
            foreach ($details as $idx => $d) {
                $d->generate_approval_id = $g_app->id;
                // dd($d, $g_app);
              
                $generate_approval_det = generate_approval_d::create([
                    "generate_approval_id" => $g_app->id,
                    "level" => $d->level,
                    "urutan_level" => $idx + 1,
                    "type" => $d->type,
                    "default_users_id" => $d->default_users_id ?? $user_atasan_id,
                    "is_full" => $d->is_full,
                    "is_skip" => $d->is_skip,
                    "assigned_at" => $this->timestamp,
                    "creator_id" => auth()->user()->id,
                    // "m_level_posisi_id" => $d->m_level_posisi_id ?? $higher_level?->id,
                ]);

                // dd($generate_approval_det);

            }

            
            // insert log pengajuan
            $fixedConfigPengajuan = "select d.* from m_approval_det d 
                join m_approval a on a.id = d.m_approval_id 
                where a.id= ? AND type = 'MENGAJUKAN' ORDER BY d.level ASC";
            $pengajuan = DB::select($fixedConfigPengajuan, [ $m_approval->m_approval_id ]);
            if(count($pengajuan)){
                $log_insert = generate_approval_log::create([
                    'nomor'                     => $g_app->nomor,
                    'generate_approval_id'      => $g_app->id,
                    'generate_approval_det_id'  => null,
                    'trx_id'                    => $g_app->trx_id,
                    'trx_table'                 => $g_app->trx_table,
                    'trx_name'                  => $g_app->trx_name,
                    'trx_nomor'                 => $g_app->trx_nomor,
                    'trx_date'                  => $g_app->trx_date,
                    'form_name'                 => $g_app->form_name,
                    'trx_creator_id'            => $g_app->trx_creator_id,
                    'action_type'               => 'MENGAJUKAN',
                    'action_user_id'            => auth()->user()->id,
                    'creator_id'                => auth()->user()->id,
                    'approver_id'               => $generate_approval_det->default_users_id,
                    'action_at'                 => $this->timestamp,
                    'action_note'               => ''
                ]); 
            }

            // dd($pengajuan);

            //update last approver by detail approval
            $last_det_approver = generate_approval_d::where('generate_approval_id',$g_app->id)
                ->where('type','MENYETUJUI')
                ->where('is_done',false)
                ->orderBy('id','asc')->pluck('id')->first();

            // dd($last_det_approver);
            generate_approval::where('id',$g_app->id)->update([
                'last_editor_id' => $last_det_approver ,
                'next_approve_det_id' => $last_det_approver,
            ]);


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            if ($errorOnFailed) {
                trigger_error($e->getMessage());
            }
            return false;
        }
        return true;
    }

    public function approvalProgress(array $config, bool $errorOnFailed = false)
    {
        $app_id = $config["app_id"];
        $app_type = $config["app_type"];
        $app_note = $config["app_note"];

        $generate_approval = generate_approval::where('id', $app_id)->first();

        if (!$generate_approval) {
            trigger_error("Maaf data approval tidak ditemukan");
        }

        if (!$app_type) {
            trigger_error("Tipe approval diperlukan!");
        }

        if (!in_array($app_type, [self::STATUS_HALF_APPROVED, self::STATUS_APPROVED, self::STATUS_REVISED, self::STATUS_REJECTED])) {
            trigger_error("Tipe approval tidak sesuai");
        }

        if (!$app_note) {
            trigger_error("Catatan approval diperlukan!");
        }

        $fixedConfig = "select d.* from generate_approval_d d join generate_approval a on a.id = d.generate_approval_id where a.id = ? and d.is_done = false order by d.urutan_level limit 1";
        $process_data = DB::select($fixedConfig, [$app_id]);

        if (!count($process_data)) {
            trigger_error("Maaf data approval tidak ditemukan");
        }

        $process_data = $process_data[0];
        // dd($process_data);
        $this->checkUserCanApprove($process_data);
        
        DB::beginTransaction();

        try {
            if ($process_data->is_full) {
                $whereRawUpdate = "generate_approval_id = $generate_approval->id and urutan_level >= $process_data->urutan_level";
            } else {
                $whereRawUpdate = "generate_approval_id = $generate_approval->id and id = $process_data->id";
            }
            
            // Update ticket approval sesuai kondisi di atas
            $check = generate_approval_d::whereRaw($whereRawUpdate)->update([
                'action_at' => $this->timestamp,
                'action_type' => $app_type,
                'action_user_id' => auth()->user()->id,
                'action_note' => $app_note,
                'is_done' => true,
            ]);
            // Check approval level berikutnya
            $outstanding = generate_approval_d::where('generate_approval_id', $generate_approval->id)
                ->where('is_done', false)
                ->where('urutan_level', '>', $process_data->urutan_level)
                ->exists();
            
            // Jika masih ada outstanding, update waktu assign approval selanjutnya
            if ($outstanding && $app_type != 'REJECTED') {
                $finish = false;
                //update last approver by detail approval
               $last_det_approver = generate_approval_d::where('generate_approval_id',$generate_approval->id)
                ->where('type','MENYETUJUI')
                ->where('is_done',false)
                ->orderBy('id','asc')->pluck('id')->first();

                generate_approval::where('id',$generate_approval->id)->update([
                    'last_editor_id' => $last_det_approver
                ]);

                generate_approval_d::where('id',$last_det_approver)->update(['assigned_at' => $this->timestamp]);
                // generate_approval::find($generate_approval->id)->update(['last_approve_id'=>null]);
            } else {
                $finish = true;

                // Jika tidak ada, update status header approval
                generate_approval::find($generate_approval->id)->update(['status' => $app_type, 'last_editor_id'=>null,'next_approve_det_id'=>null]);
            }
            $log_insert = generate_approval_log::create([
                'nomor'                     => $generate_approval->nomor,
                'generate_approval_id'      => $generate_approval->id,
                'generate_approval_det_id'  => @$proccess_data->id ?? 0,
                'trx_id'                    => $generate_approval->trx_id,
                'trx_table'                 => $generate_approval->trx_table,
                'trx_name'                  => $generate_approval->trx_name,
                'trx_nomor'                 => $generate_approval->trx_nomor,
                'trx_date'                  => $generate_approval->trx_date,
                'form_name'                 => $generate_approval->form_name,
                'trx_creator_id'            => $generate_approval->trx_creator_id,
                'action_type'               => $app_type,
                'action_user_id'            => auth()->user()->id,
                'approver_id'               => $process_data->default_users_id,
                'action_at'                 => $this->timestamp,
                'action_note'               => $app_note
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            if ($errorOnFailed) {
                trigger_error($e->getMessage());
            }

            $app = new stdClass();
            $app->status = false;
            $app->trx_id = 0;

            return $app;
        }

        $app = new stdClass();
        $app->status = true;
        $app->finish = $finish;
        $app->trx_id = $generate_approval->trx_id;

        return $app;
    }


    public function approvalOustanding()
    {
        $userAuth = auth()->user();
        $model = new generate_approval;

        $data = generate_approval::selectRaw("generate_approval.*,(select u.name from default_users u where u.id = generate_approval.creator_id) creator")
            ->leftJoin('default_users', 'default_users.id', 'generate_approval.creator_id')
            ->whereRaw("generate_approval.status = 'PROGRESS' 
                and case when generate_approval.last_approve_id is not null then
                    generate_approval.last_approve_id = $userAuth->id
                else
                    generate_approval.id in(select d.generate_approval_id from generate_approval_det d where 
                        d.m_role_id in(select r.m_role_id from m_role_access r where r.user_id = $userAuth->id)
                           and d.is_done = false and d.id = generate_approval.last_approve_det_id
                    )
                end
            ")
            ->orderBy('generate_approval.id', 'desc')
            ->search(['trx_name', 'nomor', 'trx_date', 'trx_nomor', 'default_users.name'])
            ->paginate(app()->request->paginate ?? 50);

        return $data;
    }

    public function approvalDetail($id)
    {
        $app = DB::table('generate_approval')->selectRaw("*,(select u.name from default_users u where u.id = generate_approval.creator_id) creator")->where('id', $id)->first();

        if (!$app) {
            trigger_error("Approval tidak ditemukan");
        }

        $app->tahap_saat_ini = DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->where('is_done', false)->orderBy('urutan_level', 'asc')->pluck('urutan_level')->first() ?? 0;
        $app->tahap_total = DB::table('generate_approval_d')->where('generate_approval_id', $app->id)->count();

        $trx = DB::table($app->trx_table)->where('id', $app->trx_id)->first();

        $data = new stdClass();
        $data->approval = $app;
        $data->approval_log = $this->approvalLog(['trx_id' => $app->trx_id, 'trx_table' => $app->trx_table]);
        $data->trx = $trx;

        return $data;
    }

    public function approvalLog($conf)
    {
        $trx_id = @$conf['trx_id'];
        $trx_table = @$conf['trx_table'];

        $data = generate_approval_log::selectRaw("*,(select u.name from default_users u where u.id = generate_approval_log.action_user_id) action_user, (select u.name from default_users u where u.id = generate_approval_log.approver_id) target_approval")
            ->where('trx_table', $trx_table)
            ->where('trx_id', $trx_id)
            ->orderBy('action_at', 'asc')
            ->get();

        return $data;
    }

    function snakeCaseToCapitalize($str)
    {
        $words = explode('_', $str);
        $capitalizedWords = array_map('ucfirst', $words);
        $result = implode(' ', $capitalizedWords);

        return $result;
    }

    public function postData($key, $table, $app = false, $trx_no = null, $field = 'no') 
    {
        $msg = null;
        \DB::beginTransaction();
        try {
            $text = "Status data berhasil diubah";
            $new_status_id = $this->getNewStatusSequential($key, $table->status_id, true) ?: null;
            if (!$new_status_id) throw new \Exception('Data general status untuk transaksi ini tidak ditemukan');

            $obj = [
                'status_id' => $new_status_id
            ];
            if($trx_no) $obj[$field] = $trx_no;
            $check = $table->update($obj);
            \DB::commit();
            $msg = ['success' => $text, 'data' => $table, 'last_status_id' => $new_status_id];
        }
        catch (\Exception $e) {
            \DB::rollback();
            $msg = ['error' => $e->getMessage()];
        }
        return $msg;
    }

    private function getNewStatusSequential($key, $status_id, $trx_id=null, $forward=true)
    {
        $new_status_id = null;
        // hanya berlaku sampai pergantian status ke 'APPROVED'
        if ($trx_id) {
            $last_seq = \DB::table('m_general')->where('id', $status_id)->pluck('value2')->first();

            $last_seq = (int)$last_seq;
            // treatment kode status REVISED (21)
            if($last_seq === 21){
                $last_seq = 2; // kembalikan ke draft (1) sebagai kode status default
            }

            
            if ($forward) {
                $last_seq = $last_seq+1;
            } else if (!$forward) {
                $last_seq = $last_seq-1;
            }
            $last_seq = (string)$last_seq;
            $new_status_id = \DB::table('m_general')->where([
                ['group', '=', 'STATUS TRANSAKSI'],
                ['key1', '=', $key],
                ['value2', '=', $last_seq],
                ['is_active', '=', true]
            ])->pluck('id')->first();
        }
        else {
            $new_status_id = \DB::table('m_general')->where([
                ['group', '=', 'STATUS TRANSAKSI'],
                ['key1', '=', $key],
                ['value2', '=', '1'],
                ['is_active', '=', true]
            ])->pluck('id')->first();
        }

        return $new_status_id;
    }

}
