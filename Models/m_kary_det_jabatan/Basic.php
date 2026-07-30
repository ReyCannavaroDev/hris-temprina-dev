<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_kary_det_jabatan extends Model
{   
    use ModelTrait;

    protected $table    = 'm_kary_det_jabatan';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_posisi_id","start_time","end_time","desc","is_primary","is_active","creator_id","last_editor_id","m_divisi_id","m_karyawan_id","m_company_id"];

    public $columns     = ["id","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_posisi_id","start_time","end_time","desc","is_primary","is_active","creator_id","last_editor_id","created_at","updated_at","m_divisi_id","m_karyawan_id","m_company_id"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_posisi_id:bigint","start_time:date","end_time:date","desc:text","is_primary:boolean","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","m_divisi_id:bigint","m_karyawan_id:bigint","m_company_id:integer"];
    public $rules       = [];
    public $joins       = ["m_kary.id=m_kary_det_jabatan.m_kary_id","m_comp.id=m_kary_det_jabatan.m_comp_id","m_subcomp.id=m_kary_det_jabatan.m_subcomp_id","m_branch.id=m_kary_det_jabatan.m_branch_id","m_posisi.id=m_kary_det_jabatan.m_posisi_id","m_divisi.id=m_kary_det_jabatan.m_divisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["is_active"];
    public $createable  = ["m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_posisi_id","start_time","end_time","desc","is_primary","is_active","creator_id","last_editor_id","m_divisi_id","m_karyawan_id","m_company_id"];
    public $updateable  = ["m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_posisi_id","start_time","end_time","desc","is_primary","is_active","creator_id","last_editor_id","m_divisi_id","m_karyawan_id","m_company_id"];
    public $searchable  = ["id","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_posisi_id","start_time","end_time","desc","is_primary","is_active","creator_id","last_editor_id","created_at","updated_at","m_divisi_id","m_karyawan_id","m_company_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_subcomp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_subcomp_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
}
