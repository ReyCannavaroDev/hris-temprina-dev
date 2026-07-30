<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_request_pelatihan extends Model
{   
    use ModelTrait;

    protected $table    = 't_request_pelatihan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id"];

    public $columns     = ["id","kode","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id"];
    public $columnsFull = ["id:bigint","kode:string:191","trainer_id:bigint","m_prog_pelatihan_id:bigint","date_from:date","date_to:date","desc:string:191","status:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","sarana:string","m_comp_id:bigint","m_subcomp_id:bigint","m_branch_id:bigint","m_divisi_id:bigint"];
    public $rules       = [];
    public $joins       = ["m_comp.id=t_request_pelatihan.m_comp_id","m_subcomp.id=t_request_pelatihan.m_subcomp_id","m_branch.id=t_request_pelatihan.m_branch_id","m_divisi.id=t_request_pelatihan.m_divisi_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["date_from","date_to"];
    public $createable  = ["kode","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id"];
    public $updateable  = ["kode","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id"];
    public $searchable  = ["kode","trainer_id","m_prog_pelatihan_id","date_from","date_to","desc","status","creator_id","last_editor_id","created_at","updated_at","sarana","m_comp_id","m_subcomp_id","m_branch_id","m_divisi_id"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_subcomp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_subcomp_id', 'id');
    }
    public function m_branch() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function m_divisi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
}
