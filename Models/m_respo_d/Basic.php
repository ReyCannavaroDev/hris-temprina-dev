<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_respo_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_respo_d';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_respo_id","m_role_id","note","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","m_respo_id","m_role_id","note","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","m_respo_id:bigint","m_role_id:bigint","note:string:200","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_respo.id=m_respo_d.m_respo_id","m_role.id=m_respo_d.m_role_id","default_users.id=m_respo_d.creator_id","default_users.id=m_respo_d.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_role_id"];
    public $createable  = ["m_respo_id","m_role_id","note","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["m_respo_id","m_role_id","note","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","m_respo_id","m_role_id","note","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_respo() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_respo', 'm_respo_id', 'id');
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
}
