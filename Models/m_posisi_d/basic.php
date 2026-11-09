<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_posisi_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_posisi_d';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_posisi_id","sub_jabatan","is_active","creator_id","last_editor_id","is_head"];

    public $columns     = ["id","m_posisi_id","sub_jabatan","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $columnsFull = ["id:bigint","m_posisi_id:bigint","sub_jabatan:string:191","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","is_head:boolean"];
    public $rules       = [];
    public $joins       = ["m_posisi.id=m_posisi_d.m_posisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["sub_jabatan"];
    public $createable  = ["m_posisi_id","sub_jabatan","is_active","creator_id","last_editor_id","is_head"];
    public $updateable  = ["m_posisi_id","sub_jabatan","is_active","creator_id","last_editor_id","is_head"];
    public $searchable  = ["id","m_posisi_id","sub_jabatan","is_active","creator_id","last_editor_id","created_at","updated_at","is_head"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
}
