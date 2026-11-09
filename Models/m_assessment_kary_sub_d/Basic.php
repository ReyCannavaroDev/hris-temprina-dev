<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_assessment_kary_sub_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_assessment_kary_sub_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_assessment_kary_d_id","keterangan","creator_id","last_editor_id","created_at","updated_at","nilai"];

    public $columns     = ["id","m_assessment_kary_d_id","keterangan","creator_id","last_editor_id","created_at","updated_at","nilai"];
    public $columnsFull = ["id:bigint","m_assessment_kary_d_id:bigint","keterangan:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","nilai:integer"];
    public $rules       = [];
    public $joins       = ["m_assessment_kary_d.id=m_assessment_kary_sub_d.m_assessment_kary_d_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_assessment_kary_d_id","keterangan","nilai"];
    public $createable  = ["m_assessment_kary_d_id","keterangan","creator_id","last_editor_id","created_at","updated_at","nilai"];
    public $updateable  = ["m_assessment_kary_d_id","keterangan","creator_id","last_editor_id","created_at","updated_at","nilai"];
    public $searchable  = ["m_assessment_kary_d_id","keterangan","creator_id","last_editor_id","created_at","updated_at","nilai"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_assessment_kary_d() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_assessment_kary_d', 'm_assessment_kary_d_id', 'id');
    }
}
