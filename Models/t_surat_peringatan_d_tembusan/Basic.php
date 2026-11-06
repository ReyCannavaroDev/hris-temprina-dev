<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_surat_peringatan_d_tembusan extends Model
{   
    use ModelTrait;

    protected $table    = 't_surat_peringatan_d_tembusan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_surat_peringatan_id","value","created_at","updated_at"];

    public $columns     = ["id","t_surat_peringatan_id","value","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_surat_peringatan_id:bigint","value:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_surat_peringatan_id","value","created_at","updated_at"];
    public $updateable  = ["t_surat_peringatan_id","value","created_at","updated_at"];
    public $searchable  = ["t_surat_peringatan_id","value","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
