<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_comp extends Model
{   
    use ModelTrait;

    protected $table    = 'm_comp';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["code","name","city_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","nama","desc"];

    public $columns     = ["id","code","name","city_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","nama","desc"];
    public $columnsFull = ["id:bigint","code:string:100","name:string:200","city_id:bigint","address:string:200","npwp:string:200","phone1:string:200","phone2:string:200","email:string:200","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime","nama:string","desc:string:255"];
    public $rules       = [];
    public $joins       = ["m_general.id=m_comp.city_id","default_users.id=m_comp.creator_id","default_users.id=m_comp.last_editor_id"];
    public $details     = [];
    public $heirs       = ["t_request_pelatihan","m_media","generate_approval_log","m_tunj_kemahalan","default_users","m_approval","m_contract","m_jam_kerja","m_jarak_kota","m_kary_det_org","m_kary_det_pel","m_menu","m_pengesahan_doc","m_respo_copy1","m_spd_det_transport","t_final_gaji","t_mutasi","t_mutasi","m_kary_det_kel","generate_approval","m_assessment_kary","m_dir","m_general","m_kary_det_bhs","m_kary_det_kartu","m_kary_det_pemb","m_kary_det_pend","m_kary_det_pk","m_kary_det_pres","m_approval_det","m_branch","m_kary_det_jabatan","m_kary","m_knd_dinas","m_libur_nasional","m_lokasi","m_pengurangan_gaji","m_periode","m_respo","m_role","m_role_access","m_role_det","m_spd","m_subcomp","m_zona","presensi_lokasi","t_cuti","presensi_absensi","m_standart_gaji","t_cuti_adjustment","t_grup_kerja","t_jadwal_kerja","t_lembur","t_potongan","t_potongan_det_bayar","t_riwayat_posisi","t_rpd","t_sgp","t_spd","t_req_recruitment"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["name"];
    public $createable  = ["code","name","city_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","nama","desc"];
    public $updateable  = ["code","name","city_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","nama","desc"];
    public $searchable  = ["id","code","name","city_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","nama","desc"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function city() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'city_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}
