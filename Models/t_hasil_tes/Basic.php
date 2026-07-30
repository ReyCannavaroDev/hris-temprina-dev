<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_hasil_tes extends Model
{   
    use ModelTrait;

    protected $table    = 't_hasil_tes';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","t_pelamar_id","t_loker_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","nomor","t_pelamar_id","t_loker_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","nomor:string:50","t_pelamar_id:bigint","t_loker_id:bigint","deskripsi:text","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["t_pelamar_id","t_loker_id"];
    public $createable  = ["nomor","t_pelamar_id","t_loker_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["nomor","t_pelamar_id","t_loker_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["nomor","t_pelamar_id","t_loker_id","deskripsi","status","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
