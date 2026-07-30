<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_mutasi extends Model
{   
    use ModelTrait;

    protected $table    = 't_mutasi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nomor","m_kary_id","tgl","tipe_mutasi","jenis_surat","status_kary_lama_id","m_sbu_lama_id","m_sub_lama_id","m_branch_lama_id","m_divisi_lama_id","m_posisi_lama_id","status_kary_baru_id","m_sbu_baru_id","m_sub_baru_id","m_branch_baru_id","m_divisi_baru_id","m_posisi_baru_id","no_dokumen","file_dokumen","deskripsi","catatan","keterangan","status","creator_id","last_editor_id","signature_id","jadwal_kerja_lama_id","jadwal_kerja_baru_id","kompensasi"];

    public $columns     = ["id","nomor","m_kary_id","tgl","tipe_mutasi","jenis_surat","status_kary_lama_id","m_sbu_lama_id","m_sub_lama_id","m_branch_lama_id","m_divisi_lama_id","m_posisi_lama_id","status_kary_baru_id","m_sbu_baru_id","m_sub_baru_id","m_branch_baru_id","m_divisi_baru_id","m_posisi_baru_id","no_dokumen","file_dokumen","deskripsi","catatan","keterangan","status","creator_id","last_editor_id","created_at","updated_at","signature_id","jadwal_kerja_lama_id","jadwal_kerja_baru_id","kompensasi"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","tgl:date","tipe_mutasi:string:191","jenis_surat:bigint","status_kary_lama_id:bigint","m_sbu_lama_id:bigint","m_sub_lama_id:bigint","m_branch_lama_id:bigint","m_divisi_lama_id:bigint","m_posisi_lama_id:bigint","status_kary_baru_id:bigint","m_sbu_baru_id:bigint","m_sub_baru_id:bigint","m_branch_baru_id:bigint","m_divisi_baru_id:bigint","m_posisi_baru_id:bigint","no_dokumen:string:191","file_dokumen:string:191","deskripsi:text","catatan:string:191","keterangan:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","signature_id:bigint","jadwal_kerja_lama_id:bigint","jadwal_kerja_baru_id:bigint","kompensasi:decimal"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_mutasi.m_kary_id","m_general.id=t_mutasi.jenis_surat","m_general.id=t_mutasi.status_kary_lama_id","m_comp.id=t_mutasi.m_sbu_lama_id","m_subcomp.id=t_mutasi.m_sub_lama_id","m_branch.id=t_mutasi.m_branch_lama_id","m_divisi.id=t_mutasi.m_divisi_lama_id","m_posisi.id=t_mutasi.m_posisi_lama_id","m_general.id=t_mutasi.status_kary_baru_id","m_comp.id=t_mutasi.m_sbu_baru_id","m_subcomp.id=t_mutasi.m_sub_baru_id","m_branch.id=t_mutasi.m_branch_baru_id","m_divisi.id=t_mutasi.m_divisi_baru_id","m_posisi.id=t_mutasi.m_posisi_baru_id","default_users.id=t_mutasi.creator_id","default_users.id=t_mutasi.last_editor_id","m_kary.id=t_mutasi.signature_id","t_jadwal_kerja_n.id=t_mutasi.jadwal_kerja_lama_id","t_jadwal_kerja_n.id=t_mutasi.jadwal_kerja_baru_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","tgl","tipe_mutasi","status_kary_lama_id"];
    public $createable  = ["nomor","m_kary_id","tgl","tipe_mutasi","jenis_surat","status_kary_lama_id","m_sbu_lama_id","m_sub_lama_id","m_branch_lama_id","m_divisi_lama_id","m_posisi_lama_id","status_kary_baru_id","m_sbu_baru_id","m_sub_baru_id","m_branch_baru_id","m_divisi_baru_id","m_posisi_baru_id","no_dokumen","file_dokumen","deskripsi","catatan","keterangan","status","creator_id","last_editor_id","signature_id","jadwal_kerja_lama_id","jadwal_kerja_baru_id","kompensasi"];
    public $updateable  = ["nomor","m_kary_id","tgl","tipe_mutasi","jenis_surat","status_kary_lama_id","m_sbu_lama_id","m_sub_lama_id","m_branch_lama_id","m_divisi_lama_id","m_posisi_lama_id","status_kary_baru_id","m_sbu_baru_id","m_sub_baru_id","m_branch_baru_id","m_divisi_baru_id","m_posisi_baru_id","no_dokumen","file_dokumen","deskripsi","catatan","keterangan","status","creator_id","last_editor_id","signature_id","jadwal_kerja_lama_id","jadwal_kerja_baru_id","kompensasi"];
    public $searchable  = ["id","nomor","m_kary_id","tgl","tipe_mutasi","jenis_surat","status_kary_lama_id","m_sbu_lama_id","m_sub_lama_id","m_branch_lama_id","m_divisi_lama_id","m_posisi_lama_id","status_kary_baru_id","m_sbu_baru_id","m_sub_baru_id","m_branch_baru_id","m_divisi_baru_id","m_posisi_baru_id","no_dokumen","file_dokumen","deskripsi","catatan","keterangan","status","creator_id","last_editor_id","created_at","updated_at","signature_id","jadwal_kerja_lama_id","jadwal_kerja_baru_id","kompensasi"];
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
    public function status_kary_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'status_kary_lama_id', 'id');
    }
    public function m_sbu_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_sbu_lama_id', 'id');
    }
    public function m_sub_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_sub_lama_id', 'id');
    }
    public function m_branch_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_lama_id', 'id');
    }
    public function m_divisi_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_lama_id', 'id');
    }
    public function m_posisi_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_lama_id', 'id');
    }
    public function status_kary_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'status_kary_baru_id', 'id');
    }
    public function m_sbu_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_sbu_baru_id', 'id');
    }
    public function m_sub_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_sub_baru_id', 'id');
    }
    public function m_branch_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_baru_id', 'id');
    }
    public function m_divisi_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_baru_id', 'id');
    }
    public function m_posisi_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_baru_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function signature() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'signature_id', 'id');
    }
    public function jadwal_kerja_lama() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_n', 'jadwal_kerja_lama_id', 'id');
    }
    public function jadwal_kerja_baru() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_n', 'jadwal_kerja_baru_id', 'id');
    }
}
