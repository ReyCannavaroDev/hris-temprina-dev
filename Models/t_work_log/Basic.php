<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_work_log extends Model
{   
    use ModelTrait;

    protected $table    = 't_work_log';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","finish_date","pic_id","user","progress","sebelum","tindakan","setelah","keterangan","status","approve_id","recognized_id","score","creator_id","last_editor_id","created_at","updated_at","kode","status_id","request_id","t_work_log_id","m_divisi_pic_id","m_branch_id"];

    public $columns     = ["id","m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","finish_date","pic_id","user","progress","sebelum","tindakan","setelah","keterangan","status","approve_id","recognized_id","score","creator_id","last_editor_id","created_at","updated_at","kode","status_id","request_id","t_work_log_id","m_divisi_pic_id","m_branch_id"];
    public $columnsFull = ["id:bigint","m_divisi_id:bigint","jenis_pekerjaan_id:bigint","pekerjaan:string:191","start_date:date","deadline_date:date","finish_date:date","pic_id:bigint","user:bigint","progress:decimal","sebelum:string:191","tindakan:string:191","setelah:string:191","keterangan:string:191","status:string:191","approve_id:bigint","recognized_id:bigint","score:integer","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","kode:string","status_id:bigint","request_id:bigint","t_work_log_id:bigint","m_divisi_pic_id:bigint","m_branch_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_divisi.id=t_work_log.m_divisi_id","m_general.id=t_work_log.jenis_pekerjaan_id","m_kary.id=t_work_log.pic_id","m_kary.id=t_work_log.user","m_kary.id=t_work_log.approve_id","m_kary.id=t_work_log.recognized_id","m_general.id=t_work_log.status_id","m_kary.id=t_work_log.request_id","m_divisi.id=t_work_log.m_divisi_pic_id","m_branch.id=t_work_log.m_branch_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["pekerjaan"];
    public $createable  = ["m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","finish_date","pic_id","user","progress","sebelum","tindakan","setelah","keterangan","status","approve_id","recognized_id","score","creator_id","last_editor_id","created_at","updated_at","kode","status_id","request_id","t_work_log_id","m_divisi_pic_id","m_branch_id"];
    public $updateable  = ["m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","finish_date","pic_id","user","progress","sebelum","tindakan","setelah","keterangan","status","approve_id","recognized_id","score","creator_id","last_editor_id","created_at","updated_at","kode","status_id","request_id","t_work_log_id","m_divisi_pic_id","m_branch_id"];
    public $searchable  = ["m_divisi_id","jenis_pekerjaan_id","pekerjaan","start_date","deadline_date","finish_date","pic_id","user","progress","sebelum","tindakan","setelah","keterangan","status","approve_id","recognized_id","score","creator_id","last_editor_id","created_at","updated_at","kode","status_id","request_id","t_work_log_id","m_divisi_pic_id","m_branch_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function jenis_pekerjaan() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'jenis_pekerjaan_id', 'id');
    }
    public function pic() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'pic_id', 'id');
    }
    public function user() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'user', 'id');
    }
    public function approve() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'approve_id', 'id');
    }
    public function recognized() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'recognized_id', 'id');
    }
    public function status() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'status_id', 'id');
    }
    public function request() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'request_id', 'id');
    }
    public function m_divisi_pic() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_pic_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
}
