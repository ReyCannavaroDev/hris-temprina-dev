<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_media extends Model
{   
    use ModelTrait;

    protected $table    = 'm_media';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["kode","nama","kategori_id","m_comp_id","file_path","keterangan","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","kode","nama","kategori_id","m_comp_id","file_path","keterangan","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","kode:string:50","nama:string:100","kategori_id:bigint","m_comp_id:bigint","file_path:string:255","keterangan:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_general.id=m_media.kategori_id","m_comp.id=m_media.m_comp_id","default_users.id=m_media.creator_id","default_users.id=m_media.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama"];
    public $createable  = ["kode","nama","kategori_id","m_comp_id","file_path","keterangan","is_active","creator_id","last_editor_id"];
    public $updateable  = ["kode","nama","kategori_id","m_comp_id","file_path","keterangan","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","kode","nama","kategori_id","m_comp_id","file_path","keterangan","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function kategori() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'kategori_id', 'id');
    }
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
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
