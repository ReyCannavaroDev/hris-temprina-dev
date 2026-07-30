<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_tarif_perdin extends Model
{   
    use ModelTrait;

    protected $table    = 'm_tarif_perdin';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["total_biaya","is_active","created_at","updated_at","kode","desc","m_level_posisi_id"];

    public $columns     = ["id","total_biaya","is_active","created_at","updated_at","kode","desc","m_level_posisi_id"];
    public $columnsFull = ["id:bigint","total_biaya:decimal","is_active:boolean","created_at:datetime","updated_at:datetime","kode:string:191","desc:string:191","m_level_posisi_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_level_posisi.id=m_tarif_perdin.m_level_posisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["desc"];
    public $createable  = ["total_biaya","is_active","created_at","updated_at","kode","desc","m_level_posisi_id"];
    public $updateable  = ["total_biaya","is_active","created_at","updated_at","kode","desc","m_level_posisi_id"];
    public $searchable  = ["total_biaya","is_active","created_at","updated_at","kode","desc","m_level_posisi_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_level_posisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_level_posisi', 'm_level_posisi_id', 'id');
    }
}
