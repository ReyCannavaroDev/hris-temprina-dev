<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_cost_center extends Model
{   
    use ModelTrait;

    protected $table    = 'm_cost_center';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["cost_name","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","cost_name","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","cost_name:string:191","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["cost_name"];
    public $createable  = ["cost_name","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["cost_name","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["cost_name","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
