<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_perdin extends Model
{   
    use ModelTrait;

    protected $table    = 't_perdin';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_kary_id","date_from","date_to","tugas","tempat_tujuan","provinsi_id","kota_id","kecamatan_id","alamat_tujuan","creator_id","status","created_at","updated_at","nomor","m_posisi_id","m_atasan_id"];

    public $columns     = ["id","m_kary_id","date_from","date_to","tugas","tempat_tujuan","provinsi_id","kota_id","kecamatan_id","alamat_tujuan","creator_id","status","created_at","updated_at","nomor","m_posisi_id","m_atasan_id"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","date_from:date","date_to:date","tugas:string:191","tempat_tujuan:string:191","provinsi_id:bigint","kota_id:bigint","kecamatan_id:bigint","alamat_tujuan:string:191","creator_id:integer","status:string:191","created_at:datetime","updated_at:datetime","nomor:string","m_posisi_id:bigint","m_atasan_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_posisi.id=t_perdin.m_posisi_id","m_kary.id=t_perdin.m_atasan_id"];
    public $details     = [];
    public $heirs       = ["t_rencana_perdin","t_penyelesaian_perdin"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["date_from","date_to","tugas","tempat_tujuan","alamat_tujuan"];
    public $createable  = ["m_kary_id","date_from","date_to","tugas","tempat_tujuan","provinsi_id","kota_id","kecamatan_id","alamat_tujuan","creator_id","status","created_at","updated_at","nomor","m_posisi_id","m_atasan_id"];
    public $updateable  = ["m_kary_id","date_from","date_to","tugas","tempat_tujuan","provinsi_id","kota_id","kecamatan_id","alamat_tujuan","creator_id","status","created_at","updated_at","nomor","m_posisi_id","m_atasan_id"];
    public $searchable  = ["m_kary_id","date_from","date_to","tugas","tempat_tujuan","provinsi_id","kota_id","kecamatan_id","alamat_tujuan","creator_id","status","created_at","updated_at","nomor","m_posisi_id","m_atasan_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
    public function m_atasan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_atasan_id', 'id');
    }
}
