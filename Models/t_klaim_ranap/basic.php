<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_klaim_ranap extends Model
{   
    use ModelTrait;

    protected $table    = 't_klaim_ranap';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:191","m_kary_id:integer","periode_awal:date","periode_akhir:date","total_nominal:decimal","status:string:191","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_klaim_ranap.m_kary_id"];
    public $details     = ["t_klaim_ranap_d"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [
    "nomor"=> "unique:t_klaim_ranap,nomor"
	];
    public $required    = ["m_kary_id","is_active"];
    public $createable  = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","is_active","creator_id","last_editor_id"];
    public $updateable  = ["nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","nomor","m_kary_id","periode_awal","periode_akhir","total_nominal","status","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function t_klaim_ranap_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_klaim_ranap_d', 't_klaim_ranap_id', 'id');
    }
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
}
