<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_laporan_pekerjaan extends Model
{   
    use ModelTrait;

    protected $table    = 't_laporan_pekerjaan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_pengajuan_pekerjaan_id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","m_branch_id","status","sebelum","tindakan","setelah","finish_date","keterangan","score","catatan_penilaian","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_pengajuan_pekerjaan_id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","m_branch_id","status","sebelum","tindakan","setelah","finish_date","keterangan","score","catatan_penilaian","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_pengajuan_pekerjaan_id:bigint","kode:string:191","m_divisi_id:bigint","jenis_pekerjaan_id:bigint","pekerjaan:string:191","start_date:date","deadline_date:date","pic_id:bigint","m_divisi_pic_id:bigint","request_id:bigint","pekerjaan_sebelumnya_id:bigint","m_branch_id:bigint","status:string:191","sebelum:string:191","tindakan:string:191","setelah:string:191","finish_date:date","keterangan:string:191","score:string:191","catatan_penilaian:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["pekerjaan"];
    public $createable  = ["t_pengajuan_pekerjaan_id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","m_branch_id","status","sebelum","tindakan","setelah","finish_date","keterangan","score","catatan_penilaian","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_pengajuan_pekerjaan_id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","m_branch_id","status","sebelum","tindakan","setelah","finish_date","keterangan","score","catatan_penilaian","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_pengajuan_pekerjaan_id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","m_branch_id","status","sebelum","tindakan","setelah","finish_date","keterangan","score","catatan_penilaian","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
