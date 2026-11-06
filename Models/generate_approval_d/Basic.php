<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class generate_approval_d extends Model
{   
    use ModelTrait;

    protected $table    = 'generate_approval_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["generate_approval_id","level","urutan_level","type","m_comp_id","m_subcomp_id","m_branch_id","default_users_id","is_full","is_skip","assigned_at","action_type","action_user_id","action_at","action_note","is_done","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","generate_approval_id","level","urutan_level","type","m_comp_id","m_subcomp_id","m_branch_id","default_users_id","is_full","is_skip","assigned_at","action_type","action_user_id","action_at","action_note","is_done","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","generate_approval_id:bigint","level:integer","urutan_level:integer","type:string:191","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","default_users_id:bigint","is_full:boolean","is_skip:boolean","assigned_at:datetime","action_type:string:191","action_user_id:bigint","action_at:datetime","action_note:string:191","is_done:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["level","urutan_level","type","assigned_at","is_done"];
    public $createable  = ["generate_approval_id","level","urutan_level","type","m_comp_id","m_subcomp_id","m_branch_id","default_users_id","is_full","is_skip","assigned_at","action_type","action_user_id","action_at","action_note","is_done","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["generate_approval_id","level","urutan_level","type","m_comp_id","m_subcomp_id","m_branch_id","default_users_id","is_full","is_skip","assigned_at","action_type","action_user_id","action_at","action_note","is_done","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["generate_approval_id","level","urutan_level","type","m_comp_id","m_subcomp_id","m_branch_id","default_users_id","is_full","is_skip","assigned_at","action_type","action_user_id","action_at","action_note","is_done","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
