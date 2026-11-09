<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_kary_d_lokasi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_kary_d_lokasi';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_kary_id","presensi_lokasi_id","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_kary_id","presensi_lokasi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","presensi_lokasi_id:bigint","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=m_kary_d_lokasi.m_kary_id","presensi_lokasi.id=m_kary_d_lokasi.presensi_lokasi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["m_kary_id","presensi_lokasi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_kary_id","presensi_lokasi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_kary_id","presensi_lokasi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function presensi_lokasi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\presensi_lokasi', 'presensi_lokasi_id', 'id');
    }
}
