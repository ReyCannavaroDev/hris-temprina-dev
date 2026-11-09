<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_assessment_kary_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_assessment_kary_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_assessment_kary_id","nama_assessment","kategori","bobot","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_assessment_kary_id","nama_assessment","kategori","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_assessment_kary_id:bigint","nama_assessment:string:191","kategori:bigint","bobot:integer","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_assessment_kary.id=m_assessment_kary_d.m_assessment_kary_id","m_general.id=m_assessment_kary_d.kategori"];
    public $details     = ["m_assessment_kary_sub_d"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_assessment_kary_id","nama_assessment","kategori","bobot"];
    public $createable  = ["m_assessment_kary_id","nama_assessment","kategori","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_assessment_kary_id","nama_assessment","kategori","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_assessment_kary_id","nama_assessment","kategori","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function m_assessment_kary_sub_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_assessment_kary_sub_d', 'm_assessment_kary_d_id', 'id');
    }
    
    
    public function m_assessment_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_assessment_kary', 'm_assessment_kary_id', 'id');
    }
    public function kategori() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'kategori', 'id');
    }
}
