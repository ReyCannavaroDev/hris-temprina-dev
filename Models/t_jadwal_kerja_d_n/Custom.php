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
            $baseData['created_at'] = \Carbon\Carbon::now();
            $baseData['updated_at'] = \Carbon\Carbon::now();
            
            // Hapus detailArr dari baseData agar tidak error column not found
            unset($baseData['t_jadwal_kerja_d_hari_n']);
            unset($baseData['t_jadwal_kerja_det_hari']);
            unset($baseData['detailArr']);

            foreach ($karyawanIds as $kId) {
                if (empty($kId)) continue;
                $insertData = $baseData;
                $insertData['m_kary_id'] = is_array($kId) ? ($kId['id'] ?? $kId['m_kary_id']) : $kId;
                
                \DB::table($table)->insert($insertData);
            }
        }

        $this->saveJadwalDetails($arrayData, $id);
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['detailArr']);

        return [
            "model" => $model,
            "data"  => $arrayData
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $this->saveJadwalDetails($arrayData, $id);
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['detailArr']);

        return [
            "model" => $model,
            "data"  => $arrayData
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        return $arrayData;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        return $arrayData;
    }

    private function saveJadwalDetails($arrayData, $id = null)
    {
        $payload = app('request')->all();
        $jadwalId = $arrayData['t_jadwal_kerja_n_id'] ?? $payload['t_jadwal_kerja_n_id'] ?? null;

        if (!$jadwalId && $id) {
            $jadwalId = \DB::table('t_jadwal_kerja_d_n')->where('id', $id)->value('t_jadwal_kerja_n_id');
        }

        if (!$jadwalId) return;

        $details = $arrayData['t_jadwal_kerja_d_hari_n'] 
            ?? $arrayData['t_jadwal_kerja_det_hari'] 
            ?? $payload['t_jadwal_kerja_d_hari_n'] 
            ?? $payload['t_jadwal_kerja_det_hari'] 
            ?? $payload['detailArr'] 
            ?? null;

        if (empty($details) || !is_array($details)) return;

        $dayMap = [
            'senin' => 1, 'monday' => 1,
            'selasa' => 2, 'tuesday' => 2,
            'rabu' => 3, 'wednesday' => 3,
            'kamis' => 4, 'thursday' => 4,
            'jumat' => 5, 'jum\'at' => 5, 'friday' => 5,
            'sabtu' => 6, 'saturday' => 6,
            'minggu' => 7, 'sunday' => 7,
        ];

        $nameMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        try {
            $existingNew = \DB::table('t_jadwal_kerja_d_hari_n')
                ->where('t_jadwal_kerja_n_id', $jadwalId)
                ->get();
        } catch (\Throwable $e) {
            $existingNew = collect();
        }

        try {
            $existingLegacy = \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $jadwalId)
                ->get();
        } catch (\Throwable $e) {
            $existingLegacy = collect();
        }

        foreach ($details as $idx => $d) {
            $d = (array) $d;
            $rawDay = trim((string)($d['day'] ?? $d['hari'] ?? ''));
            $dayNum = !empty($d['day_num']) ? (int)$d['day_num'] : ($dayMap[strtolower($rawDay)] ?? ($idx + 1));
            $dayName = $rawDay ?: ($nameMap[$dayNum] ?? 'Senin');

            $tipeHari = strtoupper(trim((string)($d['tipe_hari'] ?? 'KERJA')));
            $isLibur = ($tipeHari === 'LIBUR');

            $waktuMulai = $d['waktu_mulai'] ?? $d['start'] ?? null;
            $waktuAkhir = $d['waktu_akhir'] ?? $d['end'] ?? null;
            if ($isLibur || empty($waktuMulai) || $waktuMulai === '--:--' || $waktuMulai === '00:00') {
                $waktuMulai = '00:00:00';
            }
            if ($isLibur || empty($waktuAkhir) || $waktuAkhir === '--:--' || $waktuAkhir === '00:00') {
                $waktuAkhir = '00:00:00';
            }
            $jamKerjaId = $isLibur ? null : ($d['m_jam_kerja_id'] ?? null);

            // Update atau insert ke t_jadwal_kerja_d_hari_n (in-place update, jangan delete agar tidak melanggar foreign key)
            try {
                $matchedNew = $existingNew->first(function($row) use ($dayNum, $dayName, $d) {
                    if (!empty($d['id']) && $row->id == $d['id']) return true;
                    if (!empty($row->day_num) && $row->day_num == $dayNum) return true;
                    return strtolower(trim((string)$row->day)) === strtolower(trim((string)$dayName));
                });

                if ($matchedNew) {
                    \DB::table('t_jadwal_kerja_d_hari_n')
                        ->where('id', $matchedNew->id)
                        ->update([
                            'm_jam_kerja_id' => $jamKerjaId,
                            'day'            => $dayName,
                            'day_num'        => $dayNum,
                            'tipe_hari'      => $tipeHari,
                            'waktu_mulai'    => $waktuMulai,
                            'waktu_akhir'    => $waktuAkhir,
                            'updated_at'     => \Carbon\Carbon::now(),
                        ]);
                } else {
                    \DB::table('t_jadwal_kerja_d_hari_n')->insert([
                        't_jadwal_kerja_n_id' => $jadwalId,
                        'm_jam_kerja_id'      => $jamKerjaId,
                        'day'                 => $dayName,
                        'day_num'             => $dayNum,
                        'tipe_hari'           => $tipeHari,
                        'waktu_mulai'         => $waktuMulai,
                        'waktu_akhir'         => $waktuAkhir,
                        'created_at'          => \Carbon\Carbon::now(),
                        'updated_at'          => \Carbon\Carbon::now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Error saving t_jadwal_kerja_d_hari_n: ' . $e->getMessage());
            }

            // Update atau insert ke t_jadwal_kerja_det_hari (in-place update, jangan delete agar tidak melanggar foreign key)
            try {
                $matchedLeg = $existingLegacy->first(function($row) use ($dayNum, $dayName, $d) {
                    if (!empty($d['id']) && $row->id == $d['id']) return true;
                    if (!empty($row->day_num) && $row->day_num == $dayNum) return true;
                    return strtolower(trim((string)$row->day)) === strtolower(trim((string)$dayName));
                });

                if ($matchedLeg) {
                    \DB::table('t_jadwal_kerja_det_hari')
                        ->where('id', $matchedLeg->id)
                        ->update([
                            'm_jam_kerja_id' => $jamKerjaId,
                            'day'            => $dayName,
                            'day_num'        => $dayNum,
                            'tipe_hari'      => $tipeHari,
                            'waktu_mulai'    => $waktuMulai,
                            'waktu_akhir'    => $waktuAkhir,
                            'updated_at'     => \Carbon\Carbon::now(),
                        ]);
                } else {
                    \DB::table('t_jadwal_kerja_det_hari')->insert([
                        't_jadwal_kerja_id' => $jadwalId,
                        'm_jam_kerja_id'    => $jamKerjaId,
                        'day'               => $dayName,
                        'day_num'           => $dayNum,
                        'tipe_hari'         => $tipeHari,
                        'waktu_mulai'       => $waktuMulai,
                        'waktu_akhir'       => $waktuAkhir,
                        'created_at'        => \Carbon\Carbon::now(),
                        'updated_at'        => \Carbon\Carbon::now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Error saving t_jadwal_kerja_det_hari: ' . $e->getMessage());
            }
        }
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

    public function scoperespo($model)
    {
        $m_subcomp_id = request("m_subcomp_id");
        $m_branch_id = request("m_branch_id");

        if (empty($m_subcomp_id) || $m_subcomp_id === "null" || $m_subcomp_id === "undefined" || $m_subcomp_id === "[object Object]") {
            $m_subcomp_id = null;
        }
        if (empty($m_branch_id) || $m_branch_id === "null" || $m_branch_id === "undefined" || $m_branch_id === "[object Object]") {
            $m_branch_id = null;
        }

        return $model
            ->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                $q->where(function ($subQ) use ($m_subcomp_id) {
                    if (is_numeric($m_subcomp_id)) {
                        $subQ->where("t_jadwal_kerja_d_n.m_subcomp_id", $m_subcomp_id)
                             ->orWhere(function($qk) use ($m_subcomp_id) {
                                 $qk->whereHas('m_kary', function($q2) use ($m_subcomp_id) {
                                     $q2->where('m_subcomp_id', $m_subcomp_id);
                                 });
                             });
                    } else if (is_array($m_subcomp_id)) {
                        $subQ->whereIn("t_jadwal_kerja_d_n.m_subcomp_id", $m_subcomp_id)
                             ->orWhere(function($qk) use ($m_subcomp_id) {
                                 $qk->whereHas('m_kary', function($q2) use ($m_subcomp_id) {
                                     $q2->whereIn('m_subcomp_id', $m_subcomp_id);
                                 });
                             });
                    }
                });
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                $q->where(function ($subQ) use ($m_branch_id) {
                    if (is_numeric($m_branch_id)) {
                        $subQ->where("t_jadwal_kerja_d_n.m_branch_id", $m_branch_id)
                             ->orWhere(function($qk) use ($m_branch_id) {
                                 $qk->whereHas('m_kary', function($q2) use ($m_branch_id) {
                                     $q2->where('m_branch_id', $m_branch_id);
                                 });
                             });
                    } else if (is_array($m_branch_id)) {
                        $subQ->whereIn("t_jadwal_kerja_d_n.m_branch_id", $m_branch_id)
                             ->orWhere(function($qk) use ($m_branch_id) {
                                 $qk->whereHas('m_kary', function($q2) use ($m_branch_id) {
                                     $q2->whereIn('m_branch_id', $m_branch_id);
                                 });
                             });
                    }
                });
            });
    }
}
