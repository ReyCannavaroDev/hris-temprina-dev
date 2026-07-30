<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_penyelesaian_perdin_d_laporan extends Model
{   
    use ModelTrait;

    protected $table    = 't_penyelesaian_perdin_d_laporan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_penyelesaian_perdin_id","tgl","kegitatan","person","hasil","verifikasi_id","created_at","updated_at"];

    public $columns     = ["id","t_penyelesaian_perdin_id","tgl","kegitatan","person","hasil","verifikasi_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_penyelesaian_perdin_id:bigint","tgl:date","kegitatan:string:191","person:string:191","hasil:string:191","verifikasi_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_penyelesaian_perdin_id","tgl","kegitatan","person","hasil","verifikasi_id","created_at","updated_at"];
    public $updateable  = ["t_penyelesaian_perdin_id","tgl","kegitatan","person","hasil","verifikasi_id","created_at","updated_at"];
    public $searchable  = ["t_penyelesaian_perdin_id","tgl","kegitatan","person","hasil","verifikasi_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
