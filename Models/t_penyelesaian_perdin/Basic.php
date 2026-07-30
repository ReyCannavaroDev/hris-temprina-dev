<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_penyelesaian_perdin extends Model
{   
    use ModelTrait;

    protected $table    = 't_penyelesaian_perdin';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_perdin_id","m_kary_id","total_biaya","status","created_at","updated_at","t_kbs_id","sisa_biaya","nomor","nominal_kbs","no_kbs","creator_id","last_editor_id"];

    public $columns     = ["id","t_perdin_id","m_kary_id","total_biaya","status","created_at","updated_at","t_kbs_id","sisa_biaya","nomor","nominal_kbs","no_kbs","creator_id","last_editor_id"];
    public $columnsFull = ["id:bigint","t_perdin_id:bigint","m_kary_id:bigint","total_biaya:decimal","status:string:191","created_at:datetime","updated_at:datetime","t_kbs_id:bigint","sisa_biaya:decimal","nomor:string:191","nominal_kbs:decimal","no_kbs:string","creator_id:integer","last_editor_id:integer"];
    public $rules       = [];
    public $joins       = ["t_perdin.id=t_penyelesaian_perdin.t_perdin_id","m_kary.id=t_penyelesaian_perdin.m_kary_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_perdin_id","m_kary_id","total_biaya","status","created_at","updated_at","t_kbs_id","sisa_biaya","nomor","nominal_kbs","no_kbs","creator_id","last_editor_id"];
    public $updateable  = ["t_perdin_id","m_kary_id","total_biaya","status","created_at","updated_at","t_kbs_id","sisa_biaya","nomor","nominal_kbs","no_kbs","creator_id","last_editor_id"];
    public $searchable  = ["t_perdin_id","m_kary_id","total_biaya","status","created_at","updated_at","t_kbs_id","sisa_biaya","nomor","nominal_kbs","no_kbs","creator_id","last_editor_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function t_perdin() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_perdin', 't_perdin_id', 'id');
    }
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
}
