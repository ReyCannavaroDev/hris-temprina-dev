<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_pengajuan_pekerjaan extends Model
{   
    use ModelTrait;

    protected $table    = 't_pengajuan_pekerjaan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at","m_branch_id"];

    public $columns     = ["id","kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at","m_branch_id"];
    public $columnsFull = ["id:bigint","kode:string:191","m_divisi_id:bigint","jenis_pekerjaan_id:bigint","pekerjaan:string:191","start_date:date","deadline_date:date","pic_id:bigint","m_divisi_pic_id:bigint","request_id:bigint","pekerjaan_sebelumnya_id:bigint","keterangan:string:191","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","m_branch_id:bigint"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["pekerjaan"];
    public $createable  = ["kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at","m_branch_id"];
    public $updateable  = ["kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at","m_branch_id"];
    public $searchable  = ["kode","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","pic_id","m_divisi_pic_id","request_id","pekerjaan_sebelumnya_id","keterangan","status","creator_id","last_editor_id","created_at","updated_at","m_branch_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
