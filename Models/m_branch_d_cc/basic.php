<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_branch_d_cc extends Model
{   
    use ModelTrait;

    protected $table    = 'm_branch_d_cc';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_branch_id","seq","cost_centre_id","note","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","m_branch_id","seq","cost_centre_id","note","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","m_branch_id:bigint","seq:integer","cost_centre_id:bigint","note:text","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_branch.id=m_branch_d_cc.m_branch_id","m_general.id=m_branch_d_cc.cost_centre_id","default_users.id=m_branch_d_cc.creator_id","default_users.id=m_branch_d_cc.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["seq","cost_centre_id"];
    public $createable  = ["m_branch_id","seq","cost_centre_id","note","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["m_branch_id","seq","cost_centre_id","note","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","m_branch_id","seq","cost_centre_id","note","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function cost_centre() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'cost_centre_id', 'id');
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
