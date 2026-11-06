<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_standart_gaji_harian extends Model
{   
    use ModelTrait;

    protected $table    = 'm_standart_gaji_harian';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","m_kary_id","start_date","m_zona_id","m_posisi_id","grading_id","gaji_pokok","check_late","desc","is_active","creator_id","last_editor_id","created_at","updated_at","total_jam_kerja"];

    public $columns     = ["id","kode","m_kary_id","start_date","m_zona_id","m_posisi_id","grading_id","gaji_pokok","check_late","desc","is_active","creator_id","last_editor_id","created_at","updated_at","total_jam_kerja"];
    public $columnsFull = ["id:bigint","kode:string:50","m_kary_id:bigint","start_date:date","m_zona_id:bigint","m_posisi_id:bigint","grading_id:bigint","gaji_pokok:decimal","check_late:boolean","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","total_jam_kerja:decimal"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["gaji_pokok","is_active"];
    public $createable  = ["kode","m_kary_id","start_date","m_zona_id","m_posisi_id","grading_id","gaji_pokok","check_late","desc","is_active","creator_id","last_editor_id","created_at","updated_at","total_jam_kerja"];
    public $updateable  = ["kode","m_kary_id","start_date","m_zona_id","m_posisi_id","grading_id","gaji_pokok","check_late","desc","is_active","creator_id","last_editor_id","created_at","updated_at","total_jam_kerja"];
    public $searchable  = ["kode","m_kary_id","start_date","m_zona_id","m_posisi_id","grading_id","gaji_pokok","check_late","desc","is_active","creator_id","last_editor_id","created_at","updated_at","total_jam_kerja"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
