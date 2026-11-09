<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_comp_outsourcing extends Model
{   
    use ModelTrait;

    protected $table    = 'm_comp_outsourcing';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","email","website","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_comp_outsourcing_id"];

    public $columns     = ["id","code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","email","website","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","m_comp_outsourcing_id"];
    public $columnsFull = ["id:bigint","code:string:100","name:string:200","address:text","prov_id:bigint","city_id:bigint","district_id:bigint","postcode:string:30","nama_npwp:string:200","npwp:string:20","phone1:string:20","phone2:string:20","email:string:50","website:string:20","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime","m_comp_outsourcing_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_general.id=m_comp_outsourcing.prov_id","m_general.id=m_comp_outsourcing.city_id","m_general.id=m_comp_outsourcing.district_id","default_users.id=m_comp_outsourcing.creator_id","default_users.id=m_comp_outsourcing.last_editor_id","m_comp_outsourcing.id=m_comp_outsourcing.m_comp_outsourcing_id"];
    public $details     = [];
    public $heirs       = ["m_comp_outsourcing"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","is_active"];
    public $createable  = ["code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","email","website","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_comp_outsourcing_id"];
    public $updateable  = ["code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","email","website","is_active","creator_id","last_editor_id","deletor_id","deleted_at","m_comp_outsourcing_id"];
    public $searchable  = ["id","code","name","address","prov_id","city_id","district_id","postcode","nama_npwp","npwp","phone1","phone2","email","website","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at","m_comp_outsourcing_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function prov() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'prov_id', 'id');
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
    public function m_comp_outsourcing() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp_outsourcing', 'm_comp_outsourcing_id', 'id');
    }
}
