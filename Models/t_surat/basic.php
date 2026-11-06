<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_surat extends Model
{   
    use ModelTrait;

    protected $table    = 't_surat';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nomor","m_kary_id","tgl","jenis_surat","status_kary_id","m_sbu_id","m_sub_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","no_dokumen","file_dokumen","keterangan","status","creator_id","last_editor_id"];

    public $columns     = ["id","nomor","m_kary_id","tgl","jenis_surat","status_kary_id","m_sbu_id","m_sub_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","no_dokumen","file_dokumen","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","tgl:date","jenis_surat:bigint","status_kary_id:bigint","m_sbu_id:bigint","m_sub_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","m_posisi_id:bigint","signature_id:bigint","no_dokumen:string:191","file_dokumen:string:191","keterangan:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_surat.m_kary_id","m_general.id=t_surat.jenis_surat","m_general.id=t_surat.status_kary_id","m_comp.id=t_surat.m_sbu_id","m_subcomp.id=t_surat.m_sub_id","m_branch.id=t_surat.m_branch_id","m_divisi.id=t_surat.m_divisi_id","m_posisi.id=t_surat.m_posisi_id","m_kary.id=t_surat.signature_id","default_users.id=t_surat.creator_id","default_users.id=t_surat.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","tgl","status_kary_id","no_dokumen"];
    public $createable  = ["nomor","m_kary_id","tgl","jenis_surat","status_kary_id","m_sbu_id","m_sub_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","no_dokumen","file_dokumen","keterangan","status","creator_id","last_editor_id"];
    public $updateable  = ["nomor","m_kary_id","tgl","jenis_surat","status_kary_id","m_sbu_id","m_sub_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","no_dokumen","file_dokumen","keterangan","status","creator_id","last_editor_id"];
    public $searchable  = ["id","nomor","m_kary_id","tgl","jenis_surat","status_kary_id","m_sbu_id","m_sub_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","no_dokumen","file_dokumen","keterangan","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function jenis_surat() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'jenis_surat', 'id');
    }
    public function status_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'status_kary_id', 'id');
    }
    public function m_sbu() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_sbu_id', 'id');
    }
    public function m_sub() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_sub_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
    public function signature() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'signature_id', 'id');
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
