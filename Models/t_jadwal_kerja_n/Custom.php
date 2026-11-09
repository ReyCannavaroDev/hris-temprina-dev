<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_n extends \App\Models\BasicModels\t_jadwal_kerja_n
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends[] = 't_jadwal_kerja_det_hari';
    }

    public function getTJadwalKerjaDetHariAttribute()
    {
        return $this->attributes['t_jadwal_kerja_det_hari'] ?? [];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if (!$id) return $row;

        $result = $row;
        $orderSql = "CASE 
            WHEN COALESCE(day_num, 0) > 0 THEN day_num 
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('senin', 'monday') THEN 1
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('selasa', 'tuesday') THEN 2
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('rabu', 'wednesday') THEN 3
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('kamis', 'thursday') THEN 4
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('jumat', 'friday') THEN 5
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('sabtu', 'saturday') THEN 6
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('minggu', 'sunday') THEN 7
            ELSE 99 
        END ASC, id ASC";

        $dayMap = [
            'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7,
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7
        ];

        $rawDetails = \DB::table('t_jadwal_kerja_det_hari')
            ->where('t_jadwal_kerja_id', $id)
            ->orderByRaw($orderSql)
            ->get();

        if ($rawDetails->isEmpty()) {
            $rawDetails = \DB::table('t_jadwal_kerja_d_hari_n')
                ->where('t_jadwal_kerja_n_id', $id)
                ->orderByRaw($orderSql)
                ->get();
        }
            
        $formattedDetails = [];
        foreach ($rawDetails as $det) {
            $det = (array) $det;
            
            if (!empty($det['m_jam_kerja_id'])) {
                $m_jam_kerja = \DB::table('m_jam_kerja')->where('id', $det['m_jam_kerja_id'])->first();
                if ($m_jam_kerja) {
                    $det['m_jam_kerja'] = (array) $m_jam_kerja;
                    $det['m_jam_kerja.kode'] = $m_jam_kerja->kode;
                    $det['m_jam_kerja.waktu_mulai'] = $m_jam_kerja->waktu_mulai;
                    $det['m_jam_kerja.waktu_akhir'] = $m_jam_kerja->waktu_akhir;
                    $det['start_jam_kerja'] = $m_jam_kerja->waktu_mulai;
                    $det['end_jam_kerja'] = $m_jam_kerja->waktu_akhir;
                    $det['start'] = $m_jam_kerja->waktu_mulai;
                    $det['end'] = $m_jam_kerja->waktu_akhir;
                    $det['jam_kerja'] = $m_jam_kerja->id; // set model for select
                }
            } else {
                $det['start'] = $det['waktu_mulai'] ?? '--:--';
                $det['end'] = $det['waktu_akhir'] ?? '--:--';
                $det['start_jam_kerja'] = $det['waktu_mulai'] ?? '--:--';
                $det['end_jam_kerja'] = $det['waktu_akhir'] ?? '--:--';
            }
            
            if (!isset($det['day']) && isset($det['hari'])) $det['day'] = $det['hari'];
            if (!isset($det['tipe_hari'])) $det['tipe_hari'] = 'KERJA';
            
            $dayName = $det['day'] ?? $det['hari'] ?? '';
            $dayLower = strtolower(trim($dayName));
            if (empty($det['day_num']) || (int)$det['day_num'] === 0) {
                $det['day_num'] = $dayMap[$dayLower] ?? 0;
            }

            $formattedDetails[] = $det;
        }
        
        usort($formattedDetails, function($a, $b) {
            return (int)($a['day_num'] ?? 99) <=> (int)($b['day_num'] ?? 99);
        });

        $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
        
        return $result;
    }

    public function readAfter($model, $result)
    {
        if (isset($result['id'])) {
            $orderSql = "CASE 
                WHEN COALESCE(day_num, 0) > 0 THEN day_num 
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('senin', 'monday') THEN 1
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('selasa', 'tuesday') THEN 2
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('rabu', 'wednesday') THEN 3
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('kamis', 'thursday') THEN 4
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('jumat', 'friday') THEN 5
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('sabtu', 'saturday') THEN 6
                WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('minggu', 'sunday') THEN 7
                ELSE 99 
            END ASC, id ASC";

            $dayMap = [
                'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7,
                'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7
            ];

            $rawDetails = \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $result['id'])
                ->orderByRaw($orderSql)
                ->get();

            if ($rawDetails->isEmpty()) {
                $rawDetails = \DB::table('t_jadwal_kerja_d_hari_n')
                    ->where('t_jadwal_kerja_n_id', $result['id'])
                    ->orderByRaw($orderSql)
                    ->get();
            }
                
            $formattedDetails = [];
            foreach ($rawDetails as $det) {
                $det = (array) $det;
                
                if (!empty($det['m_jam_kerja_id'])) {
                    $m_jam_kerja = \DB::table('m_jam_kerja')->where('id', $det['m_jam_kerja_id'])->first();
                    if ($m_jam_kerja) {
                        $det['m_jam_kerja'] = (array) $m_jam_kerja;
                        $det['m_jam_kerja.kode'] = $m_jam_kerja->kode;
                        $det['m_jam_kerja.waktu_mulai'] = $m_jam_kerja->waktu_mulai;
                        $det['m_jam_kerja.waktu_akhir'] = $m_jam_kerja->waktu_akhir;
                        $det['start_jam_kerja'] = $m_jam_kerja->waktu_mulai;
                        $det['end_jam_kerja'] = $m_jam_kerja->waktu_akhir;
                        $det['start'] = $m_jam_kerja->waktu_mulai;
                        $det['end'] = $m_jam_kerja->waktu_akhir;
                        $det['jam_kerja'] = $m_jam_kerja->id; // set model for select
                    }
                } else {
                    $det['start'] = $det['waktu_mulai'] ?? '--:--';
                    $det['end'] = $det['waktu_akhir'] ?? '--:--';
                    $det['start_jam_kerja'] = $det['waktu_mulai'] ?? '--:--';
                    $det['end_jam_kerja'] = $det['waktu_akhir'] ?? '--:--';
                }
                
                if (!isset($det['day']) && isset($det['hari'])) $det['day'] = $det['hari'];
                if (!isset($det['tipe_hari'])) $det['tipe_hari'] = 'KERJA';
                
                $dayName = $det['day'] ?? $det['hari'] ?? '';
                $dayLower = strtolower(trim($dayName));
                if (empty($det['day_num']) || (int)$det['day_num'] === 0) {
                    $det['day_num'] = $dayMap[$dayLower] ?? 0;
                }

                $formattedDetails[] = $det;
            }
            
            usort($formattedDetails, function($a, $b) {
                return (int)($a['day_num'] ?? 99) <=> (int)($b['day_num'] ?? 99);
            });

            if (is_array($result)) {
                $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
            } elseif (is_object($result)) {
                $result->t_jadwal_kerja_det_hari = $formattedDetails;
                if (method_exists($result, 'setAttribute')) {
                    $result->setAttribute('t_jadwal_kerja_det_hari', $formattedDetails);
                }
            }
        }
        return $result;
    }
    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        // Pindahkan proses simpan detail ke Before agar pasti jalan!
        if ($id) {
            $this->saveDetails($id, $arrayData);
        }
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        return ["model" => $model, "data" => $arrayData];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        if ($id) {
            $this->saveDetails($id, $arrayData);
        }
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        return ["model" => $model, "data" => $arrayData];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?: ($model->id ?? null);
        if ($targetId) {
            $this->saveDetails($targetId, $arrayData);
        }
        return $arrayData;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        // Kosongkan karena sudah di-handle di Before
        return $arrayData;
    }

    private function saveDetails($id, $arrayData)
    {
        $payload = app('request')->input('t_jadwal_kerja_det_hari') ?? app('request')->input('t_jadwal_kerja_d_hari_n');
        $details = $arrayData['t_jadwal_kerja_det_hari'] ?? $arrayData['t_jadwal_kerja_d_hari_n'] ?? $payload ?? [];

        \DB::table('t_jadwal_kerja_det_hari')->where('t_jadwal_kerja_id', $id)->delete();
        \DB::table('t_jadwal_kerja_d_hari_n')->where('t_jadwal_kerja_n_id', $id)->delete();
        
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

        $insertsLegacy = [];
        $insertsNew = [];
        foreach ($details as $idx => $d) {
            $dayName = $d['hari'] ?? $d['day'] ?? null;
            $dayNameLower = strtolower(trim((string)$dayName));
            $dayNum = !empty($d['day_num']) ? (int)$d['day_num'] : ($dayMap[$dayNameLower] ?? ($idx + 1));
            
            if (empty($dayName) && isset($nameMap[$dayNum])) {
                $dayName = $nameMap[$dayNum];
            }

            $tipeHari = $d['tipe_hari'] ?? 'KERJA';
            $isLibur = ($tipeHari === 'LIBUR');
            $waktuMulai = $d['waktu_mulai'] ?? $d['start'] ?? null;
            $waktuAkhir = $d['waktu_akhir'] ?? $d['end'] ?? null;
            if ($isLibur || empty($waktuMulai) || $waktuMulai === '--:--') {
                $waktuMulai = '00:00:00';
            }
            if ($isLibur || empty($waktuAkhir) || $waktuAkhir === '--:--') {
                $waktuAkhir = '00:00:00';
            }
            $jamKerjaId = $isLibur ? null : ($d['m_jam_kerja_id'] ?? null);

            $insertsLegacy[] = [
                't_jadwal_kerja_id' => $id,
                'm_jam_kerja_id'    => $jamKerjaId,
                'day'               => $dayName,
                'day_num'           => $dayNum,
                'tipe_hari'         => $tipeHari,
                'waktu_mulai'       => $waktuMulai,
                'waktu_akhir'       => $waktuAkhir,
                'created_at'        => \Carbon\Carbon::now(),
                'updated_at'        => \Carbon\Carbon::now(),
            ];

            $insertsNew[] = [
                't_jadwal_kerja_n_id' => $id,
                'm_jam_kerja_id'      => $jamKerjaId,
                'day'                 => $dayName,
                'day_num'             => $dayNum,
                'tipe_hari'           => $tipeHari,
                'waktu_mulai'         => $waktuMulai,
                'waktu_akhir'         => $waktuAkhir,
                'created_at'          => \Carbon\Carbon::now(),
                'updated_at'          => \Carbon\Carbon::now(),
            ];
        }

        if (!empty($insertsLegacy)) {
            \DB::table('t_jadwal_kerja_det_hari')->insert($insertsLegacy);
        }
        if (!empty($insertsNew)) {
            \DB::table('t_jadwal_kerja_d_hari_n')->insert($insertsNew);
        }
    }

    public function t_jadwal_kerja_d_hari_n()
    {
        return $this->hasMany(\App\Models\CustomModels\t_jadwal_kerja_d_hari_n::class, 't_jadwal_kerja_n_id', 'id');
    }

    public function t_jadwal_kerja_det_hari()
    {
        return $this->hasMany(\App\Models\CustomModels\t_jadwal_kerja_det_hari::class, 't_jadwal_kerja_id', 'id');
    }

    public function t_jadwal_kerja_d_n()
    {
        return $this->hasMany(\App\Models\CustomModels\t_jadwal_kerja_d_n::class, 't_jadwal_kerja_n_id', 'id');
    }
}

