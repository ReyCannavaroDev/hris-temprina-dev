<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_loker_d_kualifikasi extends Model
{   
    use ModelTrait;

    protected $table    = 't_loker_d_kualifikasi';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_loker_id","value","created_at","updated_at"];

    public $columns     = ["id","t_loker_id","value","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_loker_id:bigint","value:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_loker_id","value","created_at","updated_at"];
    public $updateable  = ["t_loker_id","value","created_at","updated_at"];
    public $searchable  = ["t_loker_id","value","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
