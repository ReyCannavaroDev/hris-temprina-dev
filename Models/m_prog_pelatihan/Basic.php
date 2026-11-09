<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_prog_pelatihan extends Model
{   
    use ModelTrait;

    protected $table    = 'm_prog_pelatihan';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["kode","tema_pelatihan","sasaran","sifat_penyelenggara","jumlah_peserta","total_budget","month","is_active","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","kode","tema_pelatihan","sasaran","sifat_penyelenggara","jumlah_peserta","total_budget","month","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","kode:string:191","tema_pelatihan:string:191","sasaran:string:191","sifat_penyelenggara:string:191","jumlah_peserta:integer","total_budget:decimal","month:date","is_active:boolean","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["kode","tema_pelatihan","sifat_penyelenggara","jumlah_peserta","total_budget","month"];
    public $createable  = ["kode","tema_pelatihan","sasaran","sifat_penyelenggara","jumlah_peserta","total_budget","month","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["kode","tema_pelatihan","sasaran","sifat_penyelenggara","jumlah_peserta","total_budget","month","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["kode","tema_pelatihan","sasaran","sifat_penyelenggara","jumlah_peserta","total_budget","month","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
