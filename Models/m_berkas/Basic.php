<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_berkas extends Model
{   
    use ModelTrait;

    protected $table    = 'm_berkas';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["nama","desc","url","creator_id","last_editor_id","m_subcomp_id","m_branch_id","publish","kategori_id"];

    public $columns     = ["id","nama","desc","url","creator_id","last_editor_id","created_at","updated_at","m_subcomp_id","m_branch_id","publish","kategori_id"];
    public $columnsFull = ["id:bigint","nama:string:191","desc:text","url:text","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","m_subcomp_id:smallint","m_branch_id:smallint","publish:boolean","kategori_id:integer"];
    public $rules       = [];
    public $joins       = ["m_general.id=m_berkas.kategori_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama","url"];
    public $createable  = ["nama","desc","url","creator_id","last_editor_id","m_subcomp_id","m_branch_id","publish","kategori_id"];
    public $updateable  = ["nama","desc","url","creator_id","last_editor_id","m_subcomp_id","m_branch_id","publish","kategori_id"];
    public $searchable  = ["id","nama","desc","url","creator_id","last_editor_id","created_at","updated_at","m_subcomp_id","m_branch_id","publish","kategori_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function kategori() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'kategori_id', 'id');
    }
}
