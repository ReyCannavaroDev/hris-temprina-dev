<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_evaluasi_pelatihan_detail extends Model
{   
    use ModelTrait;

    protected $table    = 't_evaluasi_pelatihan_detail';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["jenis_evaluasi","komponen_evaluasi","nilai","created_at","updated_at","t_evaluasi_pelatihan_id"];

    public $columns     = ["id","jenis_evaluasi","komponen_evaluasi","nilai","created_at","updated_at","t_evaluasi_pelatihan_id"];
    public $columnsFull = ["id:bigint","jenis_evaluasi:string:191","komponen_evaluasi:string:191","nilai:integer","created_at:datetime","updated_at:datetime","t_evaluasi_pelatihan_id:bigint"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["jenis_evaluasi","komponen_evaluasi","nilai","created_at","updated_at","t_evaluasi_pelatihan_id"];
    public $updateable  = ["jenis_evaluasi","komponen_evaluasi","nilai","created_at","updated_at","t_evaluasi_pelatihan_id"];
    public $searchable  = ["jenis_evaluasi","komponen_evaluasi","nilai","created_at","updated_at","t_evaluasi_pelatihan_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
