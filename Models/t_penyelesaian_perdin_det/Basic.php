<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_penyelesaian_perdin_det extends Model
{   
    use ModelTrait;

    protected $table    = 't_penyelesaian_perdin_det';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_penyelesaian_perdin_id","komponen","nominal","jumlah","total","catatan","created_at","updated_at"];

    public $columns     = ["id","t_penyelesaian_perdin_id","komponen","nominal","jumlah","total","catatan","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_penyelesaian_perdin_id:bigint","komponen:string:191","nominal:integer","jumlah:integer","total:integer","catatan:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_penyelesaian_perdin_id","komponen","nominal","jumlah","total","catatan","created_at","updated_at"];
    public $updateable  = ["t_penyelesaian_perdin_id","komponen","nominal","jumlah","total","catatan","created_at","updated_at"];
    public $searchable  = ["t_penyelesaian_perdin_id","komponen","nominal","jumlah","total","catatan","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
