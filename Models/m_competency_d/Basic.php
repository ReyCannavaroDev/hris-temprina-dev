<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_competency_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_competency_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_competency_id","competency","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_competency_id","competency","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_competency_id:bigint","competency:text","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_competency.id=m_competency_d.m_competency_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["competency"];
    public $createable  = ["m_competency_id","competency","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_competency_id","competency","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_competency_id","competency","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_competency() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_competency', 'm_competency_id', 'id');
    }
}
