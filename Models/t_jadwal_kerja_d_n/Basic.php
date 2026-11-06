<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_jadwal_kerja_d_n extends Model
{   
    use ModelTrait;

    protected $table    = 't_jadwal_kerja_d_n';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_jadwal_kerja_n_id","m_subcomp_id","m_branch_id","m_divisi_id","m_kary_id","start_date","desc","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_jadwal_kerja_n_id","m_subcomp_id","m_branch_id","m_divisi_id","m_kary_id","start_date","desc","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_jadwal_kerja_n_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","m_kary_id:bigint","start_date:date","desc:string:191","status:string:191","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["presensi_absensi"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","desc","status"];
    public $createable  = ["t_jadwal_kerja_n_id","m_subcomp_id","m_branch_id","m_divisi_id","m_kary_id","start_date","desc","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_jadwal_kerja_n_id","m_subcomp_id","m_branch_id","m_divisi_id","m_kary_id","start_date","desc","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_jadwal_kerja_n_id","m_subcomp_id","m_branch_id","m_divisi_id","m_kary_id","start_date","desc","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
