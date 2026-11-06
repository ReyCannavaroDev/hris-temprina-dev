<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_assessment_kary_d extends Model
{   
    use ModelTrait;

    protected $table    = 't_assessment_kary_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_assessment_kary_id:bigint","nama_assessment:string:191","nama_kategori:string:191","total_nilai:integer","bobot:integer","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["t_assessment_kary.id=t_assessment_kary_d.t_assessment_kary_id"];
    public $details     = ["t_assessment_kary_sub_d"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot"];
    public $createable  = ["t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_assessment_kary_id","nama_assessment","nama_kategori","total_nilai","bobot","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function t_assessment_kary_sub_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_assessment_kary_sub_d', 't_assessment_kary_d_id', 'id');
    }
    
    
    public function t_assessment_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_assessment_kary', 't_assessment_kary_id', 'id');
    }
}
