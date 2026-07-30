<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_mutasi_d_memperhatikan extends Model
{   
    use ModelTrait;

    protected $table    = 't_mutasi_d_memperhatikan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_mutasi_id","value","created_at","updated_at"];

    public $columns     = ["id","t_mutasi_id","value","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_mutasi_id:bigint","value:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_mutasi_id","value","created_at","updated_at"];
    public $updateable  = ["t_mutasi_id","value","created_at","updated_at"];
    public $searchable  = ["t_mutasi_id","value","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
