<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_jobdesc_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_jobdesc_d';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_jobdesc_id","jobdesc","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","m_jobdesc_id","jobdesc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_jobdesc_id:bigint","jobdesc:text","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_jobdesc.id=m_jobdesc_d.m_jobdesc_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["jobdesc"];
    public $createable  = ["m_jobdesc_id","jobdesc","is_active","creator_id","last_editor_id"];
    public $updateable  = ["m_jobdesc_id","jobdesc","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","m_jobdesc_id","jobdesc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_jobdesc() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_jobdesc', 'm_jobdesc_id', 'id');
    }
}
