<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_tunjangan_jabatan extends Model
{   
    use ModelTrait;

    protected $table    = 'm_tunjangan_jabatan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_posisi_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at","m_level_posisi_id"];

    public $columns     = ["id","m_posisi_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at","m_level_posisi_id"];
    public $columnsFull = ["id:bigint","m_posisi_id:bigint","kode:string:50","besaran:decimal","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","m_level_posisi_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_posisi.id=m_tunjangan_jabatan.m_posisi_id","default_users.id=m_tunjangan_jabatan.creator_id","default_users.id=m_tunjangan_jabatan.last_editor_id","m_level_posisi.id=m_tunjangan_jabatan.m_level_posisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["besaran","is_active"];
    public $createable  = ["m_posisi_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at","m_level_posisi_id"];
    public $updateable  = ["m_posisi_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at","m_level_posisi_id"];
    public $searchable  = ["m_posisi_id","kode","besaran","desc","is_active","creator_id","last_editor_id","created_at","updated_at","m_level_posisi_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function m_level_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_level_posisi', 'm_level_posisi_id', 'id');
    }
}
