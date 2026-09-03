<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_assessment_kary extends Model
{   
    use ModelTrait;

    protected $table    = 't_assessment_kary';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["tanggal","m_kary_id","atasan_id","m_assessment_kary_id","tipe_penilaian","catatan_1","catatan_2","catatan_3","catatan_4","rata_rata","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","tanggal","m_kary_id","atasan_id","m_assessment_kary_id","tipe_penilaian","catatan_1","catatan_2","catatan_3","catatan_4","rata_rata","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","tanggal:date","m_kary_id:bigint","atasan_id:bigint","m_assessment_kary_id:bigint","tipe_penilaian:string:191","catatan_1:text","catatan_2:text","catatan_3:text","catatan_4:text","rata_rata:decimal","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_assessment_kary.m_kary_id","m_kary.id=t_assessment_kary.atasan_id","m_assessment_kary.id=t_assessment_kary.m_assessment_kary_id"];
    public $details     = ["t_assessment_kary_d"];
    public $heirs       = [];
    public $detailsChild= ["t_assessment_kary_sub_d"];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["tanggal","m_kary_id","atasan_id","m_assessment_kary_id","rata_rata","status"];
    public $createable  = ["tanggal","m_kary_id","atasan_id","m_assessment_kary_id","tipe_penilaian","catatan_1","catatan_2","catatan_3","catatan_4","rata_rata","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["tanggal","m_kary_id","atasan_id","m_assessment_kary_id","tipe_penilaian","catatan_1","catatan_2","catatan_3","catatan_4","rata_rata","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["tanggal","m_kary_id","atasan_id","m_assessment_kary_id","tipe_penilaian","catatan_1","catatan_2","catatan_3","catatan_4","rata_rata","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function t_assessment_kary_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_assessment_kary_d', 't_assessment_kary_id', 'id');
    }
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function atasan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'atasan_id', 'id');
    }
    public function m_assessment_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_assessment_kary', 'm_assessment_kary_id', 'id');
    }
}
