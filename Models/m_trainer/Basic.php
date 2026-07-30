<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_trainer extends Model
{   
    use ModelTrait;

    protected $table    = 'm_trainer';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","nama_trainer","jenis_training_id","alamat","no_hp","cp","is_active","creator_id","last_editor_id","created_at","updated_at","tipe_trainer"];

    public $columns     = ["id","kode","nama_trainer","jenis_training_id","alamat","no_hp","cp","is_active","creator_id","last_editor_id","created_at","updated_at","tipe_trainer"];
    public $columnsFull = ["id:bigint","kode:string:191","nama_trainer:string:191","jenis_training_id:bigint","alamat:text","no_hp:string:191","cp:string:191","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime","tipe_trainer:string:191"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama_trainer","jenis_training_id","alamat"];
    public $createable  = ["kode","nama_trainer","jenis_training_id","alamat","no_hp","cp","is_active","creator_id","last_editor_id","created_at","updated_at","tipe_trainer"];
    public $updateable  = ["kode","nama_trainer","jenis_training_id","alamat","no_hp","cp","is_active","creator_id","last_editor_id","created_at","updated_at","tipe_trainer"];
    public $searchable  = ["kode","nama_trainer","jenis_training_id","alamat","no_hp","cp","is_active","creator_id","last_editor_id","created_at","updated_at","tipe_trainer"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
