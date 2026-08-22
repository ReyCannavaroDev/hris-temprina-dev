<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_assessment_kary extends Model
{
    use ModelTrait;

    protected $table = 'm_assessment_kary';
    protected $guarded = ['id'];
    protected $casts = ['created_at' => 'datetime:d-m-Y', 'updated_at' => 'datetime:d-m-Y'];
    protected $fillable = ["m_comp_id", "m_subcomp_id", "m_branch_id", "m_divisi_id", "deskripsi", "type", "is_active", "creator_id", "last_editor_id", "created_at", "updated_at"];

    public $columns = ["id", "m_comp_id", "m_subcomp_id", "m_branch_id", "m_divisi_id", "deskripsi", "type", "is_active", "creator_id", "last_editor_id", "created_at", "updated_at"];
    public $columnsFull = ["id:bigint", "m_comp_id:bigint", "m_subcomp_id:bigint", "m_branch_id:bigint", "m_divisi_id:bigint", "deskripsi:string:191", "type:bigint", "is_active:boolean", "creator_id:integer", "last_editor_id:integer", "created_at:datetime", "updated_at:datetime"];
    public $rules = [];
    public $joins = ["m_comp.id=m_assessment_kary.m_comp_id", "m_subcomp.id=m_assessment_kary.m_subcomp_id", "m_branch.id=m_assessment_kary.m_branch_id", "m_divisi.id=m_assessment_kary.m_divisi_id", "m_general.id=m_assessment_kary.type"];
    public $details = ["m_assessment_kary_d", "m_assessment_kary_d_level"];
    public $heirs = ["t_assessment_kary"];
    public $detailsChild = ["m_assessment_kary_sub_d"];
    public $detailsHeirs = [];
    public $unique = [];
    public $required = ["m_comp_id", "deskripsi", "type"];
    public $createable = ["m_comp_id", "m_subcomp_id", "m_branch_id", "m_divisi_id", "deskripsi", "type", "is_active", "creator_id", "last_editor_id", "created_at", "updated_at"];
    public $updateable = ["m_comp_id", "m_subcomp_id", "m_branch_id", "m_divisi_id", "deskripsi", "type", "is_active", "creator_id", "last_editor_id", "created_at", "updated_at"];
    public $searchable = ["m_comp_id", "m_subcomp_id", "m_branch_id", "m_divisi_id", "deskripsi", "type", "is_active", "creator_id", "last_editor_id", "created_at", "updated_at"];
    public $deleteable = true;
    public $cascade = true;
    public $deleteOnUse = false;


    public function m_assessment_kary_d(): \HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_assessment_kary_d', 'm_assessment_kary_id', 'id');
    }

    public function m_assessment_kary_d_level(): \HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_assessment_kary_d_level', 'm_assessment_kary_id', 'id');
    }


    public function m_comp(): \BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_subcomp(): \BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_subcomp', 'm_subcomp_id', 'id');
    }
    public function m_branch(): \BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }
    public function m_divisi(): \BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
    }
    public function type(): \BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'type', 'id');
    }
}
