<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_bonus extends Model
{   
    use ModelTrait;

    protected $table    = 't_bonus';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","m_kary_id","jenis_bonus_id","date_from","date_to","no_doc","doc","nilai","keterangan","status","m_comp_id","m_subcomp_id","m_branch_id","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","nomor","m_kary_id","jenis_bonus_id","date_from","date_to","no_doc","doc","nilai","keterangan","status","m_comp_id","m_subcomp_id","m_branch_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","m_kary_id:bigint","jenis_bonus_id:bigint","date_from:date","date_to:date","no_doc:string:191","doc:string:191","nilai:decimal","keterangan:text","status:string:50","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","jenis_bonus_id","date_from","date_to","nilai"];
    public $createable  = ["nomor","m_kary_id","jenis_bonus_id","date_from","date_to","no_doc","doc","nilai","keterangan","status","m_comp_id","m_subcomp_id","m_branch_id","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["nomor","m_kary_id","jenis_bonus_id","date_from","date_to","no_doc","doc","nilai","keterangan","status","m_comp_id","m_subcomp_id","m_branch_id","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["nomor","m_kary_id","jenis_bonus_id","date_from","date_to","no_doc","doc","nilai","keterangan","status","m_comp_id","m_subcomp_id","m_branch_id","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
