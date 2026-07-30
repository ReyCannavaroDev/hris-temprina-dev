<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_klaim_askes extends Model
{   
    use ModelTrait;

    protected $table    = 't_klaim_askes';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:191","m_kary_id:integer","periode_awal:date","periode_akhir:date","total_nominal:decimal","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [
    "nomor"=> "unique:t_klaim_askes,nomor"
	];
    public $required    = ["m_kary_id"];
    public $createable  = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
