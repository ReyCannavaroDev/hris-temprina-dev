<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_tunjangan_fungsional extends Model
{   
    use ModelTrait;

    protected $table    = 'm_tunjangan_fungsional';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_branch_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_branch_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_branch_id:bigint","kode:string:50","besaran:decimal","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_branch.id=m_tunjangan_fungsional.m_branch_id","default_users.id=m_tunjangan_fungsional.creator_id","default_users.id=m_tunjangan_fungsional.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["besaran","is_active"];
    public $createable  = ["m_branch_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_branch_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_branch_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
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
