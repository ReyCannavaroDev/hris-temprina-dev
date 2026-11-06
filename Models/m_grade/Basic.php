<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_grade extends Model
{   
    use ModelTrait;

    protected $table    = 'm_grade';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_zona_id","code","keterangan","score","min","max","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_zona_id","code","keterangan","score","min","max","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_zona_id:bigint","code:string:191","keterangan:string:191","score:integer","min:decimal","max:decimal","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_zona.id=m_grade.m_zona_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["code","score","min","max"];
    public $createable  = ["m_zona_id","code","keterangan","score","min","max","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_zona_id","code","keterangan","score","min","max","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_zona_id","code","keterangan","score","min","max","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_zona() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_zona', 'm_zona_id', 'id');
    }
}
