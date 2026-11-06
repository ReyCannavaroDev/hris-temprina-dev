<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class presensi_absensi extends Model
{   
    use ModelTrait;

    protected $table    = 'presensi_absensi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","default_user_id","tanggal","status","checkin_time","checkin_foto","checkin_lat","checkin_long","checkin_address","checkin_region","checkin_on_scope","checkout_time","checkout_foto","checkout_lat","checkout_long","checkout_address","checkout_region","checkout_on_scope","creator_id","last_editor_id","catatan_in","catatan_out","is_face","in_scope","m_jam_kerja_id","t_jadwal_kerja_det_hari_id","jadwal_kerja_id","shift","t_jadwal_kerja_n_id","t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n_id","jam_kerja_diakui"];

    public $columns     = ["id","m_comp_id","default_user_id","tanggal","status","checkin_time","checkin_foto","checkin_lat","checkin_long","checkin_address","checkin_region","checkin_on_scope","checkout_time","checkout_foto","checkout_lat","checkout_long","checkout_address","checkout_region","checkout_on_scope","creator_id","last_editor_id","created_at","updated_at","catatan_in","catatan_out","is_face","in_scope","m_jam_kerja_id","t_jadwal_kerja_det_hari_id","jadwal_kerja_id","shift","t_jadwal_kerja_n_id","t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n_id","jam_kerja_diakui"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","default_user_id:bigint","tanggal:date","status:string:191","checkin_time:time","checkin_foto:string:191","checkin_lat:string:191","checkin_long:string:191","checkin_address:string:191","checkin_region:string:191","checkin_on_scope:boolean","checkout_time:time","checkout_foto:string:191","checkout_lat:string:191","checkout_long:string:191","checkout_address:string:191","checkout_region:string:191","checkout_on_scope:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","catatan_in:text","catatan_out:text","is_face:boolean","in_scope:boolean","m_jam_kerja_id:bigint","t_jadwal_kerja_det_hari_id:bigint","jadwal_kerja_id:bigint","shift:string:191","t_jadwal_kerja_n_id:bigint","t_jadwal_kerja_d_n_id:bigint","t_jadwal_kerja_d_hari_n_id:bigint","jam_kerja_diakui:bigint"];
    public $rules       = [];
    public $joins       = ["m_comp.id=presensi_absensi.m_comp_id","default_users.id=presensi_absensi.default_user_id","default_users.id=presensi_absensi.creator_id","default_users.id=presensi_absensi.last_editor_id","m_jam_kerja.id=presensi_absensi.m_jam_kerja_id","t_jadwal_kerja_det_hari.id=presensi_absensi.t_jadwal_kerja_det_hari_id","t_jadwal_kerja.id=presensi_absensi.jadwal_kerja_id","t_jadwal_kerja_n.id=presensi_absensi.t_jadwal_kerja_n_id","t_jadwal_kerja_d_n.id=presensi_absensi.t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n.id=presensi_absensi.t_jadwal_kerja_d_hari_n_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["tanggal","status","checkin_time","checkin_on_scope"];
    public $createable  = ["m_comp_id","default_user_id","tanggal","status","checkin_time","checkin_foto","checkin_lat","checkin_long","checkin_address","checkin_region","checkin_on_scope","checkout_time","checkout_foto","checkout_lat","checkout_long","checkout_address","checkout_region","checkout_on_scope","creator_id","last_editor_id","catatan_in","catatan_out","is_face","in_scope","m_jam_kerja_id","t_jadwal_kerja_det_hari_id","jadwal_kerja_id","shift","t_jadwal_kerja_n_id","t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n_id","jam_kerja_diakui"];
    public $updateable  = ["m_comp_id","default_user_id","tanggal","status","checkin_time","checkin_foto","checkin_lat","checkin_long","checkin_address","checkin_region","checkin_on_scope","checkout_time","checkout_foto","checkout_lat","checkout_long","checkout_address","checkout_region","checkout_on_scope","creator_id","last_editor_id","catatan_in","catatan_out","is_face","in_scope","m_jam_kerja_id","t_jadwal_kerja_det_hari_id","jadwal_kerja_id","shift","t_jadwal_kerja_n_id","t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n_id","jam_kerja_diakui"];
    public $searchable  = ["id","m_comp_id","default_user_id","tanggal","status","checkin_time","checkin_foto","checkin_lat","checkin_long","checkin_address","checkin_region","checkin_on_scope","checkout_time","checkout_foto","checkout_lat","checkout_long","checkout_address","checkout_region","checkout_on_scope","creator_id","last_editor_id","created_at","updated_at","catatan_in","catatan_out","is_face","in_scope","m_jam_kerja_id","t_jadwal_kerja_det_hari_id","jadwal_kerja_id","shift","t_jadwal_kerja_n_id","t_jadwal_kerja_d_n_id","t_jadwal_kerja_d_hari_n_id","jam_kerja_diakui"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function default_user() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'default_user_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
    public function m_jam_kerja() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_jam_kerja', 'm_jam_kerja_id', 'id');
    }
    public function t_jadwal_kerja_det_hari() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_det_hari', 't_jadwal_kerja_det_hari_id', 'id');
    }
    public function jadwal_kerja() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja', 'jadwal_kerja_id', 'id');
    }
    public function t_jadwal_kerja_n() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_n', 't_jadwal_kerja_n_id', 'id');
    }
    public function t_jadwal_kerja_d_n() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_d_n', 't_jadwal_kerja_d_n_id', 'id');
    }
    public function t_jadwal_kerja_d_hari_n() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_jadwal_kerja_d_hari_n', 't_jadwal_kerja_d_hari_n_id', 'id');
    }
}
