<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_klaim_askes_d extends Model
{   
    use ModelTrait;

    protected $table    = 't_klaim_askes_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_klaim_askes_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","created_at","updated_at"];

    public $columns     = ["id","t_klaim_askes_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_klaim_askes_id:integer","nominal:decimal","accepted:decimal","reject:decimal","keterangan:string:191","santunan:string:191","tanggal:date","bukti:string:191","klaim_nama:string:191","klaim_id:integer","klaim_table:string:191","nomor_bukti:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_klaim_askes_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","created_at","updated_at"];
    public $updateable  = ["t_klaim_askes_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","created_at","updated_at"];
    public $searchable  = ["t_klaim_askes_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
