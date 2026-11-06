<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_subcomp extends Model
{   
    use ModelTrait;

    protected $table    = 'm_subcomp';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","code","name","province_id","city_id","district_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_company_id"];

    public $columns     = ["id","m_comp_id","code","name","province_id","city_id","district_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","m_company_id"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","code:string:100","name:string:200","province_id:bigint","city_id:bigint","district_id:bigint","address:string:200","npwp:string:200","phone1:string:200","phone2:string:200","email:string:200","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime","m_company_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_comp.id=m_subcomp.m_comp_id","m_general.id=m_subcomp.province_id","m_general.id=m_subcomp.city_id","m_general.id=m_subcomp.district_id","default_users.id=m_subcomp.creator_id","default_users.id=m_subcomp.last_editor_id","m_company.id=m_subcomp.m_company_id"];
    public $details     = [];
    public $heirs       = ["t_request_pelatihan","generate_approval_log","m_respo_copy1","t_mutasi","t_mutasi","generate_approval","m_assessment_kary","m_approval_det","m_branch","m_kary_det_jabatan","m_kary","m_respo","m_spd","t_jadwal_kerja","t_req_recruitment"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_comp_id","name","province_id","city_id","district_id","address","npwp","phone1","is_active"];
    public $createable  = ["m_comp_id","code","name","province_id","city_id","district_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_company_id"];
    public $updateable  = ["m_comp_id","code","name","province_id","city_id","district_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_company_id"];
    public $searchable  = ["id","m_comp_id","code","name","province_id","city_id","district_id","address","npwp","phone1","phone2","email","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","m_company_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function province() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'province_id', 'id');
    }
    public function city() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'city_id', 'id');
    }
    public function district() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'district_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function m_company() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_company', 'm_company_id', 'id');
    }
}
