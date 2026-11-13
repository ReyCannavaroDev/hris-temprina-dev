<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_n extends \App\Models\BasicModels\t_jadwal_kerja_n
{
    private $skipSaveDetailsOnUpdate = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends[] = 't_jadwal_kerja_det_hari';
        $this->appends[] = 't_jadwal_kerja_d_hari_n';
    }

    public function getTJadwalKerjaDetHariAttribute()
    {
        return $this->attributes['t_jadwal_kerja_det_hari'] ?? [];
    }

    public function getTJadwalKerjaDHariNAttribute()
    {
        return $this->attributes['t_jadwal_kerja_d_hari_n'] ?? ($this->attributes['t_jadwal_kerja_det_hari'] ?? []);
    }

    private function buildFormattedDetails($id): array
    {
        $orderSql = "CASE 
            WHEN COALESCE(day_num, 0) > 0 THEN day_num 
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('senin', 'monday') THEN 1
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('selasa', 'tuesday') THEN 2
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('rabu', 'wednesday') THEN 3
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('kamis', 'thursday') THEN 4
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('jumat', 'jum''at', 'friday') THEN 5
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('sabtu', 'saturday') THEN 6
            WHEN LOWER(TRIM(COALESCE(day, ''))) IN ('minggu', 'ahad', 'sunday') THEN 7
            ELSE 99 
        END ASC, id ASC";

        $dayMap = [
            'senin' => 1, 'monday' => 1,
            'selasa' => 2, 'tuesday' => 2,
            'rabu' => 3, 'wednesday' => 3,
            'kamis' => 4, 'thursday' => 4,
            'jumat' => 5, "jum'at" => 5, 'friday' => 5,
            'sabtu' => 6, 'saturday' => 6,
            'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
        ];

        $rawDetails = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $id)
            ->orderByRaw($orderSql)
            ->get();

        if ($rawDetails->isEmpty()) {
            $rawDetails = \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $id)
                ->orderByRaw($orderSql)
                ->get();
        }

        $formattedDetails = [];
        foreach ($rawDetails as $det) {
            $det = (array) $det;

            $tipeHari = strtoupper(trim((string)($det['tipe_hari'] ?? 'KERJA')));
            if (empty($tipeHari)) $tipeHari = 'KERJA';
            $det['tipe_hari'] = $tipeHari;

            if ($tipeHari !== 'LIBUR' && !empty($det['m_jam_kerja_id'])) {
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
                    $det['waktu_mulai'] = $m_jam_kerja->waktu_mulai;
                    $det['waktu_akhir'] = $m_jam_kerja->waktu_akhir;
                    $det['jam_kerja'] = $m_jam_kerja->id;
                }
            } elseif ($tipeHari === 'LIBUR') {
                $det['m_jam_kerja_id'] = null;
                $det['jam_kerja'] = null;
                $det['waktu_mulai'] = '00:00:00';
                $det['waktu_akhir'] = '00:00:00';
                $det['start'] = '00:00:00';
                $det['end'] = '00:00:00';
                $det['start_jam_kerja'] = '00:00:00';
                $det['end_jam_kerja'] = '00:00:00';
            } else {
                $det['start'] = $det['waktu_mulai'] ?? '--:--';
                $det['end'] = $det['waktu_akhir'] ?? '--:--';
                $det['start_jam_kerja'] = $det['waktu_mulai'] ?? '--:--';
                $det['end_jam_kerja'] = $det['waktu_akhir'] ?? '--:--';
            }

            if (!isset($det['day']) && isset($det['hari'])) $det['day'] = $det['hari'];

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

        return $formattedDetails;
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if (!$id) return $row;

        $result = $row;
        $formattedDetails = $this->buildFormattedDetails($id);
        $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
        $result['t_jadwal_kerja_d_hari_n'] = $formattedDetails;

        return $result;
    }

    public function readAfter($model, $result)
    {
        $id = is_array($result) ? ($result['id'] ?? null) : (is_object($result) ? ($result->id ?? null) : null);
        if ($id) {
            $formattedDetails = $this->buildFormattedDetails($id);

            if (is_array($result)) {
                $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
                $result['t_jadwal_kerja_d_hari_n'] = $formattedDetails;
            } elseif (is_object($result)) {
                $result->t_jadwal_kerja_det_hari = $formattedDetails;
                $result->t_jadwal_kerja_d_hari_n = $formattedDetails;
                if (method_exists($result, 'setAttribute')) {
                    $result->setAttribute('t_jadwal_kerja_det_hari', $formattedDetails);
                    $result->setAttribute('t_jadwal_kerja_d_hari_n', $formattedDetails);
                }
            }
        }
        return $result;
    }

    private function extractDetailsInput(array $arrayData = []): ?array
    {
        $req = app('request');
        if (isset($arrayData['t_jadwal_kerja_d_hari_n']) && is_array($arrayData['t_jadwal_kerja_d_hari_n'])) {
            return $arrayData['t_jadwal_kerja_d_hari_n'];
        }
        if (isset($arrayData['t_jadwal_kerja_det_hari']) && is_array($arrayData['t_jadwal_kerja_det_hari'])) {
            return $arrayData['t_jadwal_kerja_det_hari'];
        }
        $payload = $req->input('t_jadwal_kerja_d_hari_n') ?? $req->input('t_jadwal_kerja_det_hari');
        return is_array($payload) ? $payload : null;
    }

    private function validateDetailsPayload(?array $details): ?string
    {
        if ($details === null) return null;

        foreach ($details as $idx => $d) {
            $d = (array)$d;
            $no = $idx + 1;
            $tipeHari = strtoupper(trim((string)($d['tipe_hari'] ?? 'KERJA')));
            if ($tipeHari === 'LIBUR') continue;

            $jamKerjaId = $d['m_jam_kerja_id'] ?? null;
            $mulai = trim((string)($d['waktu_mulai'] ?? $d['start'] ?? ''));
            $akhir = trim((string)($d['waktu_akhir'] ?? $d['end'] ?? ''));
            $hasTimes = !empty($mulai) && $mulai !== '--:--' && !empty($akhir) && $akhir !== '--:--';

            if (empty($jamKerjaId) && !$hasTimes) {
                $dayLabel = $d['hari'] ?? $d['day'] ?? "baris {$no}";
                return "Detail hari {$dayLabel} bertipe KERJA wajib memilih Jam Kerja.";
            }
        }

        return null;
    }

    private function isUsedInPresensi($targetId): bool
    {
        if (!$targetId) return false;

        return \DB::table('presensi_absensi')
            ->where(function ($q) use ($targetId) {
                $q->where('t_jadwal_kerja_n_id', $targetId)
                  ->orWhere('jadwal_kerja_id', $targetId)
                  ->orWhereIn('t_jadwal_kerja_d_hari_n_id', function ($sub) use ($targetId) {
                      $sub->select('id')
                          ->from('t_jadwal_kerja_d_hari_n')
                          ->where('t_jadwal_kerja_n_id', $targetId);
                  })
                  ->orWhereIn('t_jadwal_kerja_d_n_id', function ($sub) use ($targetId) {
                      $sub->select('id')
                          ->from('t_jadwal_kerja_d_n')
                          ->where('t_jadwal_kerja_n_id', $targetId);
                  });
            })
            ->exists();
    }

    private function hasScheduleChanged($targetId, array $arrayData, ?array $details): bool
    {
        $existing = \DB::table('t_jadwal_kerja_n')->where('id', $targetId)->first();
        if (!$existing) return false;

        foreach (['keterangan', 'm_comp_id', 'm_subcomp_id', 'm_branch_id', 'm_divisi_id'] as $field) {
            if (array_key_exists($field, $arrayData)) {
                $newVal = $arrayData[$field] === '' ? null : $arrayData[$field];
                $oldVal = $existing->{$field} === '' ? null : $existing->{$field};
                if ((string)$newVal !== (string)$oldVal) {
                    return true;
                }
            }
        }

        if ($details === null) return false;

        $existingDetails = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $targetId)
            ->get();
        if ($existingDetails->isEmpty()) {
            $existingDetails = \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $targetId)
                ->get();
        }

        if (count($details) !== $existingDetails->count()) {
            return true;
        }

        $dayMap = [
            'senin' => 1, 'monday' => 1,
            'selasa' => 2, 'tuesday' => 2,
            'rabu' => 3, 'wednesday' => 3,
            'kamis' => 4, 'thursday' => 4,
            'jumat' => 5, "jum'at" => 5, 'friday' => 5,
            'sabtu' => 6, 'saturday' => 6,
            'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
        ];

        $normalizeByDay = function ($rows) use ($dayMap) {
            $out = [];
            foreach ($rows as $idx => $r) {
                $r = (array)$r;
                $dayNameLower = strtolower(trim((string)($r['hari'] ?? $r['day'] ?? '')));
                $dayNum = !empty($r['day_num']) ? (int)$r['day_num'] : ($dayMap[$dayNameLower] ?? ($idx + 1));
                $tipeHari = strtoupper(trim((string)($r['tipe_hari'] ?? 'KERJA')));
                $isLibur = ($tipeHari === 'LIBUR');
                $mulai = $isLibur ? '00:00:00' : trim((string)($r['waktu_mulai'] ?? $r['start'] ?? '00:00:00'));
                $akhir = $isLibur ? '00:00:00' : trim((string)($r['waktu_akhir'] ?? $r['end'] ?? '00:00:00'));
                if ($mulai === '00:00' || $mulai === '--:--' || $mulai === '') $mulai = '00:00:00';
                if ($akhir === '00:00' || $akhir === '--:--' || $akhir === '') $akhir = '00:00:00';

                $out[$dayNum] = [
                    'tipe_hari' => $tipeHari,
                    'm_jam_kerja_id' => $isLibur ? '' : (string)($r['m_jam_kerja_id'] ?? ''),
                    'waktu_mulai' => substr($mulai, 0, 5),
                    'waktu_akhir' => substr($akhir, 0, 5),
                ];
            }
            ksort($out);
            return $out;
        };

        return $normalizeByDay($details) !== $normalizeByDay($existingDetails);
    }

    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        $details = $this->extractDetailsInput($arrayData);
        if ($err = $this->validateDetailsPayload($details)) {
            return [
                "model" => $model,
                "data" => $arrayData,
                "errors" => [$err],
            ];
        }

        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        return ["model" => $model, "data" => $arrayData];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $targetId = $id ?: ($model->id ?? null);
        $details = $this->extractDetailsInput($arrayData);

        if ($targetId && $this->isUsedInPresensi($targetId)) {
            $this->skipSaveDetailsOnUpdate[$targetId] = true;
            if ($this->hasScheduleChanged($targetId, $arrayData, $details)) {
                return [
                    "model" => $model,
                    "data" => $arrayData,
                    "errors" => ["Master jadwal kerja tidak dapat diubah karena sudah digunakan pada riwayat absensi karyawan. Silakan nonaktifkan jadwal ini dan buat master jadwal baru dengan tanggal mulai (start date) baru di Jadwal Kerja Karyawan."],
                ];
            }
        } else {
            if ($err = $this->validateDetailsPayload($details)) {
                return [
                    "model" => $model,
                    "data" => $arrayData,
                    "errors" => [$err],
                ];
            }
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
        $targetId = $id ?: ($model->id ?? null);
        if ($targetId && empty($this->skipSaveDetailsOnUpdate[$targetId])) {
            $this->saveDetails($targetId, $arrayData);
        }
        return $arrayData;
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?: ($model->id ?? null);
        if ($targetId && $this->isUsedInPresensi($targetId)) {
            throw new \Exception("Master jadwal kerja tidak dapat dihapus karena sudah digunakan pada riwayat absensi karyawan. Silakan nonaktifkan jadwal ini dan buat master jadwal kerja baru.");
        }

        $isAssigned = $targetId ? \DB::table('t_jadwal_kerja_d_n')
            ->where('t_jadwal_kerja_n_id', $targetId)
            ->exists() : false;
        if ($isAssigned) {
            throw new \Exception("Master jadwal kerja tidak dapat dihapus karena masih tertaut pada data Jadwal Kerja Karyawan.");
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    private function saveDetails($id, $arrayData)
    {
        $details = $this->extractDetailsInput($arrayData);
        if (empty($details)) {
            return;
        }

        $dayMap = [
            'senin' => 1, 'monday' => 1,
            'selasa' => 2, 'tuesday' => 2,
            'rabu' => 3, 'wednesday' => 3,
            'kamis' => 4, 'thursday' => 4,
            'jumat' => 5, "jum'at" => 5, 'friday' => 5,
            'sabtu' => 6, 'saturday' => 6,
            'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
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

        $existingNew = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $id)
            ->get();

        $existingLegacy = \DB::table('t_jadwal_kerja_det_hari')
            ->where('t_jadwal_kerja_id', $id)
            ->get();

        $now = \Carbon\Carbon::now();
        $keptNewIds = [];
        $keptLegacyIds = [];

        foreach ($details as $idx => $d) {
            $d = (array)$d;
            $dayName = $d['hari'] ?? $d['day'] ?? null;
            $dayNameLower = strtolower(trim((string)$dayName));
            $dayNum = !empty($d['day_num']) ? (int)$d['day_num'] : ($dayMap[$dayNameLower] ?? ($idx + 1));

            if (empty($dayName) && isset($nameMap[$dayNum])) {
                $dayName = $nameMap[$dayNum];
            }

            $tipeHari = strtoupper(trim((string)($d['tipe_hari'] ?? 'KERJA')));
            if (empty($tipeHari)) $tipeHari = 'KERJA';
            $isLibur = ($tipeHari === 'LIBUR');

            $jamKerjaId = $isLibur ? null : (!empty($d['m_jam_kerja_id']) ? $d['m_jam_kerja_id'] : null);
            $waktuMulai = $d['waktu_mulai'] ?? $d['start'] ?? null;
            $waktuAkhir = $d['waktu_akhir'] ?? $d['end'] ?? null;

            if (!$isLibur && $jamKerjaId && (empty($waktuMulai) || $waktuMulai === '--:--' || empty($waktuAkhir) || $waktuAkhir === '--:--')) {
                $mJamKerja = \DB::table('m_jam_kerja')->where('id', $jamKerjaId)->first();
                if ($mJamKerja) {
                    $waktuMulai = $mJamKerja->waktu_mulai;
                    $waktuAkhir = $mJamKerja->waktu_akhir;
                }
            }

            if ($isLibur || empty($waktuMulai) || $waktuMulai === '--:--' || $waktuMulai === '00:00') {
                $waktuMulai = '00:00:00';
            }
            if ($isLibur || empty($waktuAkhir) || $waktuAkhir === '--:--' || $waktuAkhir === '00:00') {
                $waktuAkhir = '00:00:00';
            }

            $matchedNew = $existingNew->first(function($row) use ($dayNum, $dayName, $d) {
                if (!empty($d['id']) && $row->id == $d['id']) return true;
                if (!empty($row->day_num) && (int)$row->day_num === $dayNum) return true;
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
                        'updated_at'     => $now,
                    ]);
                $keptNewIds[] = $matchedNew->id;
            } else {
                $insertedNewId = \DB::table('t_jadwal_kerja_d_hari_n')->insertGetId([
                    't_jadwal_kerja_n_id' => $id,
                    'm_jam_kerja_id'      => $jamKerjaId,
                    'day'                 => $dayName,
                    'day_num'             => $dayNum,
                    'tipe_hari'           => $tipeHari,
                    'waktu_mulai'         => $waktuMulai,
                    'waktu_akhir'         => $waktuAkhir,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
                $keptNewIds[] = $insertedNewId;
            }

            $matchedLeg = $existingLegacy->first(function($row) use ($dayNum, $dayName, $d) {
                if (!empty($d['id']) && $row->id == $d['id']) return true;
                if (!empty($row->day_num) && (int)$row->day_num === $dayNum) return true;
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
                        'updated_at'     => $now,
                    ]);
                $keptLegacyIds[] = $matchedLeg->id;
            } else {
                $insertedLegId = \DB::table('t_jadwal_kerja_det_hari')->insertGetId([
                    't_jadwal_kerja_id' => $id,
                    'm_jam_kerja_id'    => $jamKerjaId,
                    'day'               => $dayName,
                    'day_num'           => $dayNum,
                    'tipe_hari'         => $tipeHari,
                    'waktu_mulai'       => $waktuMulai,
                    'waktu_akhir'       => $waktuAkhir,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $keptLegacyIds[] = $insertedLegId;
            }
        }

        // Hapus hari-hari yang dibuang / tidak ada di form (mis. hari Minggu dihapus)
        if (!empty($keptNewIds)) {
            \DB::table('t_jadwal_kerja_d_hari_n')
                ->where('t_jadwal_kerja_n_id', $id)
                ->whereNotIn('id', $keptNewIds)
                ->delete();
        }
        if (!empty($keptLegacyIds)) {
            \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $id)
                ->whereNotIn('id', $keptLegacyIds)
                ->delete();
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

