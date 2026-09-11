<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_pelamar_det_dokumen extends Model
{   
    use ModelTrait;

    protected $table    = 't_pelamar_det_dokumen';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_pelamar_id","nama_dokumen","file","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","t_pelamar_id","nama_dokumen","file","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_pelamar_id:bigint","nama_dokumen:string:191","file:text","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = [""];
    public $createable  = ["t_pelamar_id","nama_dokumen","file","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["t_pelamar_id","nama_dokumen","file","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["t_pelamar_id","nama_dokumen","file","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
