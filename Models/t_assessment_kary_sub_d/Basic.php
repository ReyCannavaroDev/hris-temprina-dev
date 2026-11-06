<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_assessment_kary_sub_d extends Model
{   
    use ModelTrait;

    protected $table    = 't_assessment_kary_sub_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_assessment_kary_d_id","nama_keterangan","nilai","is_selected","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_assessment_kary_d_id","nama_keterangan","nilai","is_selected","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_assessment_kary_d_id:bigint","nama_keterangan:string:191","nilai:integer","is_selected:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["t_assessment_kary_d.id=t_assessment_kary_sub_d.t_assessment_kary_d_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["t_assessment_kary_d_id","nama_keterangan","nilai"];
    public $createable  = ["t_assessment_kary_d_id","nama_keterangan","nilai","is_selected","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_assessment_kary_d_id","nama_keterangan","nilai","is_selected","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_assessment_kary_d_id","nama_keterangan","nilai","is_selected","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function t_assessment_kary_d() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_assessment_kary_d', 't_assessment_kary_d_id', 'id');
    }
}
