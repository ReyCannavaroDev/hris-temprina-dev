<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_posisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_posisi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["name","is_active","creator_id","last_editor_id","is_parent","m_divisi_id"];

    public $columns     = ["id","name","is_active","creator_id","last_editor_id","created_at","updated_at","is_parent","m_divisi_id"];
    public $columnsFull = ["id:bigint","name:string","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","is_parent:boolean","m_divisi_id:bigint"];
    public $rules       = [];
    public $joins       = ["default_users.id=m_posisi.creator_id","default_users.id=m_posisi.last_editor_id","m_general.id=m_posisi.m_divisi_id"];
    public $details     = [];
    public $heirs       = ["t_perdin","m_tunj_kemahalan","m_competency","t_mutasi","t_mutasi","m_jobdesc","m_kary_det_jobdesc","m_kary_det_jabatan","m_kary","m_spd","m_tunjangan_jabatan","m_standart_gaji","t_spd"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["name"];
    public $createable  = ["name","is_active","creator_id","last_editor_id","is_parent","m_divisi_id"];
    public $updateable  = ["name","is_active","creator_id","last_editor_id","is_parent","m_divisi_id"];
    public $searchable  = ["id","name","is_active","creator_id","last_editor_id","created_at","updated_at","is_parent","m_divisi_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'm_divisi_id', 'id');
    }
}
