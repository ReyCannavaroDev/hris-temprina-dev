<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_jadwal_kerja_d_hari_n extends Model
{   
    use ModelTrait;

    protected $table    = 't_jadwal_kerja_d_hari_n';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["t_jadwal_kerja_n_id","day","tipe_hari","tanggal","day_num","m_jam_kerja_id","waktu_mulai","waktu_akhir","created_at","updated_at"];

    public $columns     = ["id","t_jadwal_kerja_n_id","day","tipe_hari","tanggal","day_num","m_jam_kerja_id","waktu_mulai","waktu_akhir","created_at","updated_at"];
    public $columnsFull = ["id:bigint","t_jadwal_kerja_n_id:bigint","day:string:191","tipe_hari:string:191","tanggal:date","day_num:integer","m_jam_kerja_id:bigint","waktu_mulai:time","waktu_akhir:time","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["presensi_absensi"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["day","tipe_hari","day_num","waktu_mulai","waktu_akhir"];
    public $createable  = ["t_jadwal_kerja_n_id","day","tipe_hari","tanggal","day_num","m_jam_kerja_id","waktu_mulai","waktu_akhir","created_at","updated_at"];
    public $updateable  = ["t_jadwal_kerja_n_id","day","tipe_hari","tanggal","day_num","m_jam_kerja_id","waktu_mulai","waktu_akhir","created_at","updated_at"];
    public $searchable  = ["t_jadwal_kerja_n_id","day","tipe_hari","tanggal","day_num","m_jam_kerja_id","waktu_mulai","waktu_akhir","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
