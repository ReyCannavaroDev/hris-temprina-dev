<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_level_posisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_level_posisi';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["level_name","is_active","creator_id","last_editor_id","created_at","updated_at","sequence"];

    public $columns     = ["id","level_name","is_active","creator_id","last_editor_id","created_at","updated_at","sequence"];
    public $columnsFull = ["id:bigint","level_name:string:191","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","sequence:integer"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["m_tarif_perdin","m_tunj_kemahalan","m_approval_det","m_tunjangan_jabatan"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["level_name","is_active"];
    public $createable  = ["level_name","is_active","creator_id","last_editor_id","created_at","updated_at","sequence"];
    public $updateable  = ["level_name","is_active","creator_id","last_editor_id","created_at","updated_at","sequence"];
    public $searchable  = ["level_name","is_active","creator_id","last_editor_id","created_at","updated_at","sequence"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
