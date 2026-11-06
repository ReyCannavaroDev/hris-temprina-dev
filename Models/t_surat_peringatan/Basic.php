<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_surat_peringatan extends Model
{   
    use ModelTrait;

    protected $table    = 't_surat_peringatan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","m_kary_id","tgl","level_sp","m_sbu_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","file_dokumen","status","masa_berlaku","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","nomor","m_kary_id","tgl","level_sp","m_sbu_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","file_dokumen","status","masa_berlaku","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","tgl:date","level_sp:bigint","m_sbu_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","m_posisi_id:bigint","signature_id:bigint","file_dokumen:string:191","status:string:50","masa_berlaku:integer","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","status"];
    public $createable  = ["nomor","m_kary_id","tgl","level_sp","m_sbu_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","file_dokumen","status","masa_berlaku","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["nomor","m_kary_id","tgl","level_sp","m_sbu_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","file_dokumen","status","masa_berlaku","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["nomor","m_kary_id","tgl","level_sp","m_sbu_id","m_branch_id","m_divisi_id","m_posisi_id","signature_id","file_dokumen","status","masa_berlaku","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
