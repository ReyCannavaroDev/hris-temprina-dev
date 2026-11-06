<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_respo extends Model
{   
    use ModelTrait;

    protected $table    = 'm_respo';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","m_subcomp_id","m_branch_id","name","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","m_comp_id","m_subcomp_id","m_branch_id","name","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","name:string:100","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_comp.id=m_respo.m_comp_id","m_subcomp.id=m_respo.m_subcomp_id","m_branch.id=m_respo.m_branch_id","default_users.id=m_respo.creator_id","default_users.id=m_respo.last_editor_id"];
    public $details     = ["m_respo_d_copy1","m_respo_d"];
    public $heirs       = ["generate_approval_log","default_users_respo"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["name"];
    public $createable  = ["m_comp_id","m_subcomp_id","m_branch_id","name","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["m_comp_id","m_subcomp_id","m_branch_id","name","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","m_comp_id","m_subcomp_id","m_branch_id","name","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function m_respo_d_copy1() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_respo_d_copy1', 'm_respo_id', 'id');
    }
    public function m_respo_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_respo_d', 'm_respo_id', 'id');
    }
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_subcomp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_subcomp_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
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
