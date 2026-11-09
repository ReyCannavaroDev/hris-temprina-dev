<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_standart_gaji_harian_d extends Model
{   
    use ModelTrait;

    protected $table    = 'm_standart_gaji_harian_d';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["m_standart_gaji_harian_id","komponen_tipe_id","komponen_id","faktor","nilai","periode","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","m_standart_gaji_harian_id","komponen_tipe_id","komponen_id","faktor","nilai","periode","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_standart_gaji_harian_id:bigint","komponen_tipe_id:bigint","komponen_id:bigint","faktor:string:191","nilai:decimal","periode:string:191","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["faktor","nilai","periode"];
    public $createable  = ["m_standart_gaji_harian_id","komponen_tipe_id","komponen_id","faktor","nilai","periode","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["m_standart_gaji_harian_id","komponen_tipe_id","komponen_id","faktor","nilai","periode","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["m_standart_gaji_harian_id","komponen_tipe_id","komponen_id","faktor","nilai","periode","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
