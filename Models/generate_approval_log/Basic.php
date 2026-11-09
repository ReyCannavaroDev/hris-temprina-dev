<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class generate_approval_log extends Model
{   
    use ModelTrait;

    protected $table    = 'generate_approval_log';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","m_dir_id","nomor","generate_approval_id","generate_approval_det_id","trx_table","trx_id","trx_name","trx_nomor","trx_date","trx_object","trx_creator_id","action_type","action_user_id","action_at","action_note","creator_id","last_editor_id","m_subcomp_id","m_branch_id","generate_approval_d_id","approver_id"];

    public $columns     = ["id","m_comp_id","m_dir_id","nomor","generate_approval_id","generate_approval_det_id","trx_table","trx_id","trx_name","trx_nomor","trx_date","trx_object","trx_creator_id","action_type","action_user_id","action_at","action_note","creator_id","last_editor_id","created_at","updated_at","m_subcomp_id","m_branch_id","generate_approval_d_id","approver_id"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","m_dir_id:bigint","nomor:string:191","generate_approval_id:bigint","generate_approval_det_id:bigint","trx_table:string:191","trx_id:bigint","trx_name:string:191","trx_nomor:string:191","trx_date:date","trx_object:string:191","trx_creator_id:bigint","action_type:string:191","action_user_id:bigint","action_at:datetime","action_note:string:191","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","m_subcomp_id:bigint","m_branch_id:bigint","generate_approval_d_id:bigint","approver_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_comp.id=generate_approval_log.m_comp_id","m_dir.id=generate_approval_log.m_dir_id","generate_approval.id=generate_approval_log.generate_approval_id","generate_approval_det.id=generate_approval_log.generate_approval_det_id","default_users.id=generate_approval_log.action_user_id","default_users.id=generate_approval_log.creator_id","default_users.id=generate_approval_log.last_editor_id","m_subcomp.id=generate_approval_log.m_subcomp_id","m_respo.id=generate_approval_log.m_branch_id","generate_approval_det.id=generate_approval_log.generate_approval_d_id","default_users.id=generate_approval_log.approver_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["trx_table","trx_id","trx_name","trx_date","action_type"];
    public $createable  = ["m_comp_id","m_dir_id","nomor","generate_approval_id","generate_approval_det_id","trx_table","trx_id","trx_name","trx_nomor","trx_date","trx_object","trx_creator_id","action_type","action_user_id","action_at","action_note","creator_id","last_editor_id","m_subcomp_id","m_branch_id","generate_approval_d_id","approver_id"];
    public $updateable  = ["m_comp_id","m_dir_id","nomor","generate_approval_id","generate_approval_det_id","trx_table","trx_id","trx_name","trx_nomor","trx_date","trx_object","trx_creator_id","action_type","action_user_id","action_at","action_note","creator_id","last_editor_id","m_subcomp_id","m_branch_id","generate_approval_d_id","approver_id"];
    public $searchable  = ["id","m_comp_id","m_dir_id","nomor","generate_approval_id","generate_approval_det_id","trx_table","trx_id","trx_name","trx_nomor","trx_date","trx_object","trx_creator_id","action_type","action_user_id","action_at","action_note","creator_id","last_editor_id","created_at","updated_at","m_subcomp_id","m_branch_id","generate_approval_d_id","approver_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_dir_id', 'id');
    }
    public function generate_approval() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\generate_approval', 'generate_approval_id', 'id');
    }
    public function generate_approval_det() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\generate_approval_det', 'generate_approval_det_id', 'id');
    }
    public function action_user() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'action_user_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function m_subcomp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_subcomp_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_respo', 'm_branch_id', 'id');
    }
    public function generate_approval_d() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\generate_approval_det', 'generate_approval_d_id', 'id');
    }
    public function approver() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'approver_id', 'id');
    }
}
