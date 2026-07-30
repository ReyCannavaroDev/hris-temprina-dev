<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_hasil_tes_det extends Model
{   
    use ModelTrait;

    protected $table    = 't_hasil_tes_det';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_hasil_tes_id","tanggal","nama_tes","nilai_tes","dokumen","created_at","updated_at"];

    public $columns     = ["id","t_hasil_tes_id","tanggal","nama_tes","nilai_tes","dokumen","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_hasil_tes_id:bigint","tanggal:date","nama_tes:string:191","nilai_tes:decimal","dokumen:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["t_hasil_tes_id","tanggal"];
    public $createable  = ["t_hasil_tes_id","tanggal","nama_tes","nilai_tes","dokumen","created_at","updated_at"];
    public $updateable  = ["t_hasil_tes_id","tanggal","nama_tes","nilai_tes","dokumen","created_at","updated_at"];
    public $searchable  = ["t_hasil_tes_id","tanggal","nama_tes","nilai_tes","dokumen","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
