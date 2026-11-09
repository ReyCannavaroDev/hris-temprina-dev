<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_fingerprint_machine extends Model
{   
    use ModelTrait;

    protected $table    = 'm_fingerprint_machine';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","id_machine","name","creator_id","last_editor_id","created_at","updated_at","is_active"];

    public $columns     = ["id","kode","id_machine","name","creator_id","last_editor_id","created_at","updated_at","is_active"];
    public $columnsFull = ["id:bigint","kode:string:191","id_machine:string:191","name:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","is_active:boolean"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["m_kary"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["kode","id_machine","name","is_active"];
    public $createable  = ["kode","id_machine","name","creator_id","last_editor_id","created_at","updated_at","is_active"];
    public $updateable  = ["kode","id_machine","name","creator_id","last_editor_id","created_at","updated_at","is_active"];
    public $searchable  = ["kode","id_machine","name","creator_id","last_editor_id","created_at","updated_at","is_active"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
