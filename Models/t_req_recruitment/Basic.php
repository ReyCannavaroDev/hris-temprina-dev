<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_req_recruitment extends Model
{   
    use ModelTrait;

    protected $table    = 't_req_recruitment';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i",
    "tanggal"=> "datetime:d-m-Y",
    "tgl_dibutuhkan"=> "datetime:d-m-Y"
	];
    protected $fillable = ["nomor","tanggal","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_dept_id","m_posisi_id","jumlah_kebutuhan","status_kary_id","jenis_permintaan_id","karyawan_digantikan_id","tgl_dibutuhkan","prioritas_id","alasan","status","t_loker_id","creator_id","last_editor_id"];

    public $columns     = ["id","nomor","tanggal","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_dept_id","m_posisi_id","jumlah_kebutuhan","status_kary_id","jenis_permintaan_id","karyawan_digantikan_id","tgl_dibutuhkan","prioritas_id","alasan","status","t_loker_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","tanggal:date","m_kary_id:bigint","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","m_dept_id:bigint","m_posisi_id:bigint","jumlah_kebutuhan:integer","status_kary_id:bigint","jenis_permintaan_id:bigint","karyawan_digantikan_id:bigint","tgl_dibutuhkan:date","prioritas_id:bigint","alasan:text","status:string:50","t_loker_id:bigint","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_req_recruitment.m_kary_id","m_comp.id=t_req_recruitment.m_comp_id","m_subcomp.id=t_req_recruitment.m_subcomp_id","m_branch.id=t_req_recruitment.m_branch_id","m_divisi.id=t_req_recruitment.m_divisi_id","m_dept.id=t_req_recruitment.m_dept_id","m_posisi.id=t_req_recruitment.m_posisi_id","m_general.id=t_req_recruitment.status_kary_id","m_general.id=t_req_recruitment.jenis_permintaan_id","m_kary.id=t_req_recruitment.karyawan_digantikan_id","m_general.id=t_req_recruitment.prioritas_id","t_loker.id=t_req_recruitment.t_loker_id","default_users.id=t_req_recruitment.creator_id","default_users.id=t_req_recruitment.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_divisi_id","m_posisi_id","jumlah_kebutuhan","tgl_dibutuhkan"];
    public $createable  = ["nomor","tanggal","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_dept_id","m_posisi_id","jumlah_kebutuhan","status_kary_id","jenis_permintaan_id","karyawan_digantikan_id","tgl_dibutuhkan","prioritas_id","alasan","status","t_loker_id","creator_id","last_editor_id"];
    public $updateable  = ["nomor","tanggal","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_dept_id","m_posisi_id","jumlah_kebutuhan","status_kary_id","jenis_permintaan_id","karyawan_digantikan_id","tgl_dibutuhkan","prioritas_id","alasan","status","t_loker_id","creator_id","last_editor_id"];
    public $searchable  = ["id","nomor","tanggal","m_kary_id","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","m_dept_id","m_posisi_id","jumlah_kebutuhan","status_kary_id","jenis_permintaan_id","karyawan_digantikan_id","tgl_dibutuhkan","prioritas_id","alasan","status","t_loker_id","creator_id","last_editor_id","created_at","updated_at"];
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
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function m_dept() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dept', 'm_dept_id', 'id');
    }
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
    public function status_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'status_kary_id', 'id');
    }
    public function jenis_permintaan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'jenis_permintaan_id', 'id');
    }
    public function karyawan_digantikan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'karyawan_digantikan_id', 'id');
    }
    public function prioritas() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'prioritas_id', 'id');
    }
    public function t_loker() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_loker', 't_loker_id', 'id');
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
