<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_efektifitas_pelatihan_detail extends Model
{   
    use ModelTrait;

    protected $table    = 't_efektifitas_pelatihan_detail';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["created_at","updated_at","komponen_efektifitas","nilai","m_kary_id","t_efektifitas_pelatihan_id","sequence"];

    public $columns     = ["id","created_at","updated_at","komponen_efektifitas","nilai","m_kary_id","t_efektifitas_pelatihan_id","sequence"];
    public $columnsFull = ["id:bigint","created_at:datetime","updated_at:datetime","komponen_efektifitas:string:191","nilai:integer","m_kary_id:bigint","t_efektifitas_pelatihan_id:bigint","sequence:integer"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id"];
    public $createable  = ["created_at","updated_at","komponen_efektifitas","nilai","m_kary_id","t_efektifitas_pelatihan_id","sequence"];
    public $updateable  = ["created_at","updated_at","komponen_efektifitas","nilai","m_kary_id","t_efektifitas_pelatihan_id","sequence"];
    public $searchable  = ["created_at","updated_at","komponen_efektifitas","nilai","m_kary_id","t_efektifitas_pelatihan_id","sequence"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
