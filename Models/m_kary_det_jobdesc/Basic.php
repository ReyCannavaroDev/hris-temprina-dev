<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_kary_det_jobdesc extends Model
{   
    use ModelTrait;

    protected $table    = 'm_kary_det_jobdesc';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_kary_id","m_posisi_id","jobdesc","is_active","creator_id","last_editor_id","m_karyawan_id"];

    public $columns     = ["id","m_kary_id","m_posisi_id","jobdesc","is_active","creator_id","last_editor_id","created_at","updated_at","m_karyawan_id"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","m_posisi_id:bigint","jobdesc:text","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","m_karyawan_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_kary.id=m_kary_det_jobdesc.m_kary_id","m_posisi.id=m_kary_det_jobdesc.m_posisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["jobdesc","is_active"];
    public $createable  = ["m_kary_id","m_posisi_id","jobdesc","is_active","creator_id","last_editor_id","m_karyawan_id"];
    public $updateable  = ["m_kary_id","m_posisi_id","jobdesc","is_active","creator_id","last_editor_id","m_karyawan_id"];
    public $searchable  = ["id","m_kary_id","m_posisi_id","jobdesc","is_active","creator_id","last_editor_id","created_at","updated_at","m_karyawan_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
}
