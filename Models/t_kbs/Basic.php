<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_kbs extends Model
{   
    use ModelTrait;

    protected $table    = 't_kbs';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_kary_id","nomor","nominal","is_active","created_at","updated_at","keterangan","tanggal"];

    public $columns     = ["id","m_kary_id","nomor","nominal","is_active","created_at","updated_at","keterangan","tanggal"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","nomor:string:191","nominal:integer","is_active:boolean","created_at:datetime","updated_at:datetime","keterangan:string:191","tanggal:date"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_kary_id","nomor","nominal","is_active","created_at","updated_at","keterangan","tanggal"];
    public $updateable  = ["m_kary_id","nomor","nominal","is_active","created_at","updated_at","keterangan","tanggal"];
    public $searchable  = ["m_kary_id","nomor","nominal","is_active","created_at","updated_at","keterangan","tanggal"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
