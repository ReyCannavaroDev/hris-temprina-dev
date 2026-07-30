<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_realisasi_pelatihan extends Model
{   
    use ModelTrait;

    protected $table    = 't_realisasi_pelatihan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","t_request_pelatihan_id"];

    public $columns     = ["id","kode","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","t_request_pelatihan_id"];
    public $columnsFull = ["id:bigint","kode:string:191","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint","trainer_id:bigint","m_prog_pelatihan_id:bigint","date_from:date","date_to:date","desc:string:191","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","sarana:string:191","t_request_pelatihan_id:bigint"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["date_from","date_to"];
    public $createable  = ["kode","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","t_request_pelatihan_id"];
    public $updateable  = ["kode","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","t_request_pelatihan_id"];
    public $searchable  = ["kode","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","t_request_pelatihan_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
