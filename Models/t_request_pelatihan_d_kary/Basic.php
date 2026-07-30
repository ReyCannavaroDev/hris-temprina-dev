<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_request_pelatihan_d_kary extends Model
{   
    use ModelTrait;

    protected $table    = 't_request_pelatihan_d_kary';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_request_pelatihan_id","m_kary_id","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_request_pelatihan_id","m_kary_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_request_pelatihan_id:bigint","m_kary_id:bigint","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_request_pelatihan_id","m_kary_id","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_request_pelatihan_id","m_kary_id","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_request_pelatihan_id","m_kary_id","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
