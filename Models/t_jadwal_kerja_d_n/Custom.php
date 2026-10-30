<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_d_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_d_n extends \App\Models\BasicModels\t_jadwal_kerja_d_n
{
    public function __construct()
    {
        parent::__construct();

        $this->joins = array_values(array_unique(array_merge($this->joins ?? [], [
            "m_kary.id=t_jadwal_kerja_d_n.m_kary_id",
            "m_branch.id=t_jadwal_kerja_d_n.m_branch_id",
            "m_posisi.id=m_kary.m_posisi_id",
            "t_jadwal_kerja_n.id=t_jadwal_kerja_d_n.t_jadwal_kerja_n_id",
        ])));
    }

    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        $payload = app('request')->all();
        $karyawanIds = $payload['m_kary_id'] ?? $arrayData['m_kary_id'] ?? [];
        
        if (is_string($karyawanIds)) {
            $decoded = json_decode($karyawanIds, true);
            $karyawanIds = json_last_error() === JSON_ERROR_NONE ? $decoded : explode(',', $karyawanIds);
        }

        if (empty($karyawanIds) || !is_array($karyawanIds)) {
            // Jika kosong atau bukan array (hanya 1), biarkan saja lewat
            return [
                "model" => $model,
                "data"  => $arrayData
            ];
        }

        // Ambil ID pertama untuk di-insert oleh generator native
        $firstKaryId = array_shift($karyawanIds);
        $arrayData['m_kary_id'] = is_array($firstKaryId) ? ($firstKaryId['id'] ?? $firstKaryId['m_kary_id']) : $firstKaryId;

        // Jika masih ada sisa karyawan yang dipilih, insert manual sisanya
        if (count($karyawanIds) > 0) {
            $table = $model->getTable();
            $baseData = $arrayData;
            $baseData['created_at'] = now();
            $baseData['updated_at'] = now();
            
            // Hapus detailArr dari baseData agar tidak error column not found
            unset($baseData['t_jadwal_kerja_d_hari_n']);
            unset($baseData['detailArr']);

            foreach ($karyawanIds as $kId) {
                if (empty($kId)) continue;
                $insertData = $baseData;
                $insertData['m_kary_id'] = is_array($kId) ? ($kId['id'] ?? $kId['m_kary_id']) : $kId;
                
                \DB::table($table)->insert($insertData);
            }
        }

        return [
            "model" => $model,
            "data"  => $arrayData
        ];
    }
    
    public function transformRowData(array $row)
    {
        $id = $row['id'] ?? null;
        if (!$id) return $row;

        // Karyawan & Posisi
        if (!empty($row['m_kary_id'])) {
            $kary = \DB::table('m_kary')->where('id', $row['m_kary_id'])->first();
            if ($kary) {
                $row['m_kary'] = (array)$kary;
                $row['nama_lengkap'] = $kary->nama_lengkap ?? null;
                $row['m_kary.nama_lengkap'] = $kary->nama_lengkap ?? null;
                
                $posisiId = $kary->m_posisi_id ?? null;
                if (!empty($posisiId)) {
                    $posisi = \DB::table('m_posisi')->where('id', $posisiId)->first();
                    if ($posisi) {
                        $row['m_kary']['m_posisi'] = (array)$posisi;
                        $row['m_posisi'] = (array)$posisi;
                        $row['m_posisi.name'] = $posisi->name ?? null;
                        $row['posisi_name'] = $posisi->name ?? null;
                    }
                }

                // Jika branch belum ada di row, ambil dari karyawan
                if (empty($row['m_branch_id']) && !empty($kary->m_branch_id)) {
                    $row['m_branch_id'] = $kary->m_branch_id;
                }
            }
        }

        // Cabang
        if (!empty($row['m_branch_id'])) {
            $branch = \DB::table('m_branch')->where('id', $row['m_branch_id'])->first();
            if ($branch) {
                $row['m_branch'] = (array)$branch;
                $row['m_branch.name'] = $branch->name ?? null;
                $row['branch_name'] = $branch->name ?? null;
            }
        }

        // Subcomp
        if (!empty($row['m_subcomp_id'])) {
            $subcomp = \DB::table('m_subcomp')->where('id', $row['m_subcomp_id'])->first();
            if ($subcomp) {
                $row['m_subcomp'] = (array)$subcomp;
                $row['m_subcomp.name'] = $subcomp->name ?? null;
            }
        }

        // Jadwal Kerja
        if (!empty($row['t_jadwal_kerja_n_id'])) {
            $jadwal = \DB::table('t_jadwal_kerja_n')->where('id', $row['t_jadwal_kerja_n_id'])->first();
            if ($jadwal) {
                $row['t_jadwal_kerja_n'] = (array)$jadwal;
                $row['t_jadwal_kerja_n.keterangan'] = $jadwal->keterangan ?? null;
                $row['jadwal_kerja'] = $jadwal->keterangan ?? null;
            }
        }

        return $row;
    }

    public function t_jadwal_kerja_n()
    {
        return $this->belongsTo(\App\Models\CustomModels\t_jadwal_kerja_n::class, 't_jadwal_kerja_n_id', 'id');
    }

    public function m_kary()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_kary::class, 'm_kary_id', 'id');
    }
}
