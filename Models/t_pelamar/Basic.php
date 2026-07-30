<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_pelamar extends Model
{   
    use ModelTrait;

    protected $table    = 't_pelamar';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["nomor","nama_depan","nama_belakang","nama_lengkap","ktp_no","tanggal","telp","jk_id","tempat_lahir","tgl_lahir","ig","x","facebook","linkedin","email","status","creator_id","last_editor_id","created_at","updated_at","nama_panggilan"];

    public $columns     = ["id","nomor","nama_depan","nama_belakang","nama_lengkap","ktp_no","tanggal","telp","jk_id","tempat_lahir","tgl_lahir","ig","x","facebook","linkedin","email","status","creator_id","last_editor_id","created_at","updated_at","nama_panggilan"];
    public $columnsFull = ["id:bigint","nomor:string:50","nama_depan:string:191","nama_belakang:string:191","nama_lengkap:string:191","ktp_no:string:191","tanggal:date","telp:string:191","jk_id:bigint","tempat_lahir:string:100","tgl_lahir:date","ig:string:100","x:string:100","facebook:string:100","linkedin:string:100","email:string:100","status:string:50","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","nama_panggilan:string:191"];
    public $rules       = [];
    public $joins       = [];
    public $details     = ["t_pelamar_det_pres","t_pelamar_det_kartu","t_pelamar_det_bhs","t_pelamar_det_org","t_pelamar_det_pel","t_pelamar_det_pend","t_pelamar_det_peng","t_pelamar_det_pk"];
    public $heirs       = ["m_kary"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["ktp_no","tanggal","telp","tgl_lahir"];
    public $createable  = ["nomor","nama_depan","nama_belakang","nama_lengkap","ktp_no","tanggal","telp","jk_id","tempat_lahir","tgl_lahir","ig","x","facebook","linkedin","email","status","creator_id","last_editor_id","created_at","updated_at","nama_panggilan"];
    public $updateable  = ["nomor","nama_depan","nama_belakang","nama_lengkap","ktp_no","tanggal","telp","jk_id","tempat_lahir","tgl_lahir","ig","x","facebook","linkedin","email","status","creator_id","last_editor_id","created_at","updated_at","nama_panggilan"];
    public $searchable  = ["nomor","nama_depan","nama_belakang","nama_lengkap","ktp_no","tanggal","telp","jk_id","tempat_lahir","tgl_lahir","ig","x","facebook","linkedin","email","status","creator_id","last_editor_id","created_at","updated_at","nama_panggilan"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function t_pelamar_det_pres() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_pres', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_kartu() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_kartu', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_bhs() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_bhs', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_org() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_org', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_pel() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_pel', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_pend() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_pend', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_peng() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_peng', 't_pelamar_id', 'id');
    }
    public function t_pelamar_det_pk() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_pelamar_det_pk', 't_pelamar_id', 'id');
    }
    
    
}
