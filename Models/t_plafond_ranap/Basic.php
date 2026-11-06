<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_plafond_ranap extends Model
{   
    use ModelTrait;

    protected $table    = 't_plafond_ranap';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_kary_id","tahun","plafond","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_kary_id","tahun","plafond","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","tahun:smallint","plafond:decimal","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_kary_id","tahun","plafond","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_kary_id","tahun","plafond","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_kary_id","tahun","plafond","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
