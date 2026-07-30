<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_rencana_perdin extends Model
{   
    use ModelTrait;

    protected $table    = 't_rencana_perdin';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_perdin_id","m_kary_id","status","created_at","updated_at","total_biaya","nomor"];

    public $columns     = ["id","t_perdin_id","m_kary_id","status","created_at","updated_at","total_biaya","nomor"];
    public $columnsFull = ["id:bigint","t_perdin_id:bigint","m_kary_id:bigint","status:string:191","created_at:datetime","updated_at:datetime","total_biaya:decimal","nomor:string:191"];
    public $rules       = [];
    public $joins       = ["t_perdin.id=t_rencana_perdin.t_perdin_id","m_kary.id=t_rencana_perdin.m_kary_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_perdin_id","m_kary_id","status","created_at","updated_at","total_biaya","nomor"];
    public $updateable  = ["t_perdin_id","m_kary_id","status","created_at","updated_at","total_biaya","nomor"];
    public $searchable  = ["t_perdin_id","m_kary_id","status","created_at","updated_at","total_biaya","nomor"];
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
