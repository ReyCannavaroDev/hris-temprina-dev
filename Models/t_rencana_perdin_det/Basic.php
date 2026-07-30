<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_rencana_perdin_det extends Model
{   
    use ModelTrait;

    protected $table    = 't_rencana_perdin_det';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["komponen","nominal","jumlah","total","catatan","created_at","updated_at","t_rencana_perdin_id"];

    public $columns     = ["id","komponen","nominal","jumlah","total","catatan","created_at","updated_at","t_rencana_perdin_id"];
    public $columnsFull = ["id:bigint","komponen:string:191","nominal:integer","jumlah:integer","total:integer","catatan:string:191","created_at:datetime","updated_at:datetime","t_rencana_perdin_id:bigint"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["komponen","nominal","jumlah","total","catatan","created_at","updated_at","t_rencana_perdin_id"];
    public $updateable  = ["komponen","nominal","jumlah","total","catatan","created_at","updated_at","t_rencana_perdin_id"];
    public $searchable  = ["komponen","nominal","jumlah","total","catatan","created_at","updated_at","t_rencana_perdin_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
