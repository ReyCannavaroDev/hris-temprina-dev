<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_divisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_divisi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_branch_id","parent_id","nomor","name_old","level","is_active","creator_id","last_editor_id","is_parent","name"];

    public $columns     = ["id","m_branch_id","parent_id","nomor","name_old","level","is_active","creator_id","last_editor_id","created_at","updated_at","is_parent","name"];
    public $columnsFull = ["id:bigint","m_branch_id:bigint","parent_id:bigint","nomor:string","name_old:string","level:string","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","is_parent:boolean","name:integer"];
    public $rules       = [];
    public $joins       = ["m_branch.id=m_divisi.m_branch_id","m_divisi.id=m_divisi.parent_id","default_users.id=m_divisi.creator_id","default_users.id=m_divisi.last_editor_id"];
    public $details     = [];
    public $heirs       = ["t_request_pelatihan","m_tunj_kemahalan","t_work_log","t_work_log","t_final_gaji_det","t_mutasi","t_mutasi","t_perhitungan_gaji","m_assessment_kary","m_dept","m_divisi","m_divisi_lama","m_kary_det_jabatan","m_kary","m_lembur","m_spd","m_standart_gaji","t_grup_kerja","t_jadwal_kerja","t_jadwal_kerja_det","t_spd"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_branch_id"];
    public $createable  = ["m_branch_id","parent_id","nomor","name_old","level","is_active","creator_id","last_editor_id","is_parent","name"];
    public $updateable  = ["m_branch_id","parent_id","nomor","name_old","level","is_active","creator_id","last_editor_id","is_parent","name"];
    public $searchable  = ["id","m_branch_id","parent_id","nomor","name_old","level","is_active","creator_id","last_editor_id","created_at","updated_at","is_parent","name"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function parent() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'parent_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}
