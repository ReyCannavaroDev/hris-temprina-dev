<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_klaim_ranap_d extends Model
{   
    use ModelTrait;

    protected $table    = 't_klaim_ranap_d';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["t_klaim_ranap_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","santunan_id","is_active"];

    public $columns     = ["id","t_klaim_ranap_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","santunan_id","is_active","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_klaim_ranap_id:integer","nominal:decimal","accepted:decimal","reject:decimal","keterangan:string:191","santunan:string:191","tanggal:date","bukti:string:191","klaim_nama:string:191","klaim_id:integer","klaim_table:string:191","nomor_bukti:string:191","santunan_id:integer","is_active:boolean","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["t_klaim_ranap.id=t_klaim_ranap_d.t_klaim_ranap_id","m_general.id=t_klaim_ranap_d.santunan_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["is_active"];
    public $createable  = ["t_klaim_ranap_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","santunan_id","is_active"];
    public $updateable  = ["t_klaim_ranap_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","santunan_id","is_active"];
    public $searchable  = ["id","t_klaim_ranap_id","nominal","accepted","reject","keterangan","santunan","tanggal","bukti","klaim_nama","klaim_id","klaim_table","nomor_bukti","santunan_id","is_active","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function t_klaim_ranap() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_klaim_ranap', 't_klaim_ranap_id', 'id');
    }
    public function santunan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'santunan_id', 'id');
    }
}
