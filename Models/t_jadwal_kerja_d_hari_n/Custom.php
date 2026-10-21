<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_d_hari_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_d_hari_n extends \App\Models\BasicModels\t_jadwal_kerja_d_hari_n
{
    public $columns = [
        "id", "t_jadwal_kerja_n_id", "m_jam_kerja_id", "tipe_hari", "day", "day_num"
    ];

    public function t_jadwal_kerja_n()
    {
        return $this->belongsTo(\App\Models\CustomModels\t_jadwal_kerja_n::class, 't_jadwal_kerja_n_id', 'id');
    }

    public function m_jam_kerja()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_jam_kerja::class, 'm_jam_kerja_id', 'id');
    }
}

