<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_costcentre extends Model
{   
    use ModelTrait;

    protected $table    = 'm_costcentre';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_general_id","parent_id","nomor","name","level","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_general_id","parent_id","nomor","name","level","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_general_id:bigint","parent_id:bigint","nomor:string:100","name:string:100","level:string:20","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_general.id=m_costcentre.m_general_id","m_costcentre.id=m_costcentre.parent_id","default_users.id=m_costcentre.creator_id","default_users.id=m_costcentre.last_editor_id"];
    public $details     = [];
    public $heirs       = ["m_costcentre"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_general_id","nomor","name","level"];
    public $createable  = ["m_general_id","parent_id","nomor","name","level","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_general_id","parent_id","nomor","name","level","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_general_id","parent_id","nomor","name","level","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_general() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'm_general_id', 'id');
    }
    public function parent() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_costcentre', 'parent_id', 'id');
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
