<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_jobdesc extends Model
{   
    use ModelTrait;

    protected $table    = 'm_jobdesc';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_posisi_id","desc","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","m_posisi_id","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_posisi_id:bigint","desc:text","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_posisi.id=m_jobdesc.m_posisi_id"];
    public $details     = ["m_jobdesc_d"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["desc","is_active"];
    public $createable  = ["m_posisi_id","desc","is_active","creator_id","last_editor_id"];
    public $updateable  = ["m_posisi_id","desc","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","m_posisi_id","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function m_jobdesc_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_jobdesc_d', 'm_jobdesc_id', 'id');
    }
    
    
    public function m_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_posisi', 'm_posisi_id', 'id');
    }
}
