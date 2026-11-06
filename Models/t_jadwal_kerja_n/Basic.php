<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_jadwal_kerja_n extends Model
{   
    use ModelTrait;

    protected $table    = 't_jadwal_kerja_n';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","keterangan:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["t_mutasi","t_mutasi","presensi_absensi"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
