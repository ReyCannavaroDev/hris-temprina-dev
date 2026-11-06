<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_approval_det extends Model
{   
    use ModelTrait;

    protected $table    = 'm_approval_det';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_approval_id","level","m_role_id","creator_id","last_editor_id","type","default_users_id","is_full","is_skip","name","is_active","m_comp_id","m_subcomp_id","m_branch_id","m_level_posisi_id"];

    public $columns     = ["id","m_approval_id","level","m_role_id","creator_id","last_editor_id","created_at","updated_at","type","default_users_id","is_full","is_skip","name","is_active","m_comp_id","m_subcomp_id","m_branch_id","m_level_posisi_id"];
    public $columnsFull = ["id:bigint","m_approval_id:bigint","level:integer","m_role_id:bigint","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","type:string:10","default_users_id:bigint","is_full:boolean","is_skip:boolean","name:string","is_active:boolean","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_level_posisi_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_approval.id=m_approval_det.m_approval_id","m_role.id=m_approval_det.m_role_id","default_users.id=m_approval_det.creator_id","default_users.id=m_approval_det.last_editor_id","default_users.id=m_approval_det.default_users_id","m_comp.id=m_approval_det.m_comp_id","m_subcomp.id=m_approval_det.m_subcomp_id","m_branch.id=m_approval_det.m_branch_id","m_level_posisi.id=m_approval_det.m_level_posisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["level","type"];
    public $createable  = ["m_approval_id","level","m_role_id","creator_id","last_editor_id","type","default_users_id","is_full","is_skip","name","is_active","m_comp_id","m_subcomp_id","m_branch_id","m_level_posisi_id"];
    public $updateable  = ["m_approval_id","level","m_role_id","creator_id","last_editor_id","type","default_users_id","is_full","is_skip","name","is_active","m_comp_id","m_subcomp_id","m_branch_id","m_level_posisi_id"];
    public $searchable  = ["id","m_approval_id","level","m_role_id","creator_id","last_editor_id","created_at","updated_at","type","default_users_id","is_full","is_skip","name","is_active","m_comp_id","m_subcomp_id","m_branch_id","m_level_posisi_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_approval() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_approval', 'm_approval_id', 'id');
    }
    public function m_role() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_role', 'm_role_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function default_users() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'default_users_id', 'id');
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
    public function m_level_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_level_posisi', 'm_level_posisi_id', 'id');
    }
}
