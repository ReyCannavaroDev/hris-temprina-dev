<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_prog_pelatihan_d_divisi extends Model
{   
    use ModelTrait;

    protected $table    = 'm_prog_pelatihan_d_divisi';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_prog_pelatihan_id","m_divisi_id","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_prog_pelatihan_id","m_divisi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_prog_pelatihan_id:bigint","m_divisi_id:bigint","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_prog_pelatihan_id","m_divisi_id"];
    public $createable  = ["m_prog_pelatihan_id","m_divisi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_prog_pelatihan_id","m_divisi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_prog_pelatihan_id","m_divisi_id","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
