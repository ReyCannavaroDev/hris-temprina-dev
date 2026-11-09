<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_users_respo extends Model
{   
    use ModelTrait;

    protected $table    = 'default_users_respo';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["default_users_id","seq","m_respo_id","is_primary","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","default_users_id","seq","m_respo_id","is_primary","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","default_users_id:bigint","seq:integer","m_respo_id:bigint","is_primary:boolean","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_users.id=default_users_respo.default_users_id","m_respo.id=default_users_respo.m_respo_id","default_users.id=default_users_respo.creator_id","default_users.id=default_users_respo.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["default_users_id","seq","m_respo_id","is_primary","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["default_users_id","seq","m_respo_id","is_primary","is_active","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","default_users_id","seq","m_respo_id","is_primary","is_active","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function default_users() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'default_users_id', 'id');
    }
    public function m_respo() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_respo', 'm_respo_id', 'id');
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
