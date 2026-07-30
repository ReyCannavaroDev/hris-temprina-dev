<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_loker extends Model
{   
    use ModelTrait;

    protected $table    = 't_loker';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_posisi_id","title","jenis_loker_id","prioritas_id","tgl_dibuka","tgl_akhir","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","jk_id","status_kary_id","jumlah"];

    public $columns     = ["id","nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_posisi_id","title","jenis_loker_id","prioritas_id","tgl_dibuka","tgl_akhir","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","jk_id","status_kary_id","jumlah"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","m_posisi_id:bigint","title:string:191","jenis_loker_id:bigint","prioritas_id:bigint","tgl_dibuka:date","tgl_akhir:date","deskripsi:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","jk_id:bigint","status_kary_id:bigint","jumlah:bigint"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_posisi_id","title","jenis_loker_id","prioritas_id","tgl_dibuka","tgl_akhir","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","jk_id","status_kary_id","jumlah"];
    public $updateable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_posisi_id","title","jenis_loker_id","prioritas_id","tgl_dibuka","tgl_akhir","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","jk_id","status_kary_id","jumlah"];
    public $searchable  = ["nomor","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_posisi_id","title","jenis_loker_id","prioritas_id","tgl_dibuka","tgl_akhir","deskripsi","status","creator_id","last_editor_id","created_at","updated_at","jk_id","status_kary_id","jumlah"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
