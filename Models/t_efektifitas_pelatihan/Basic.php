<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_efektifitas_pelatihan extends Model
{   
    use ModelTrait;

    protected $table    = 't_efektifitas_pelatihan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","t_realisasi_pelatihan_id","trainer_id","m_prog_pelatihan_id","tanggal","saran","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","kode","t_realisasi_pelatihan_id","trainer_id","m_prog_pelatihan_id","tanggal","saran","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","kode:string:191","t_realisasi_pelatihan_id:bigint","trainer_id:bigint","m_prog_pelatihan_id:bigint","tanggal:date","saran:text","status:text","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["kode","t_realisasi_pelatihan_id","trainer_id","m_prog_pelatihan_id","tanggal","saran","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["kode","t_realisasi_pelatihan_id","trainer_id","m_prog_pelatihan_id","tanggal","saran","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["kode","t_realisasi_pelatihan_id","trainer_id","m_prog_pelatihan_id","tanggal","saran","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
