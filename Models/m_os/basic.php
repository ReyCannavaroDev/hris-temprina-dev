<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_os extends Model
{   
    use ModelTrait;

    protected $table    = 'm_os';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["kode","nama","desc","is_active"];

    public $columns     = ["id","kode","nama","desc","is_active","created_at","updated_at"];
    public $columnsFull = ["id:bigint","kode:string:100","nama:string:100","desc:string:100","is_active:boolean","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama","is_active"];
    public $createable  = ["kode","nama","desc","is_active"];
    public $updateable  = ["kode","nama","desc","is_active"];
    public $searchable  = ["id","kode","nama","desc","is_active","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
