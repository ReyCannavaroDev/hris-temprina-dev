<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

Carbon::setLocale('id');

class presensi_absensi extends \App\Models\BasicModels\presensi_absensi
{
    private $helper;

    private $now;

    protected static ?bool $hasCheckoutDateColumn = null;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
        $this->now = \Carbon::now();

        if ($this->hasCheckoutDateColumn()) {
            foreach (['fillable', 'columns', 'createable', 'updateable', 'searchable'] as $prop) {
                if (isset($this->{$prop}) && is_array($this->{$prop}) && !in_array('checkout_date', $this->{$prop})) {
                    $this->{$prop}[] = 'checkout_date';
                }
            }
        }
    }

    protected function hasCheckoutDateColumn(): bool
    {
        if (self::$hasCheckoutDateColumn === null) {
            try {
                self::$hasCheckoutDateColumn = \Schema::hasColumn($this->getTable(), 'checkout_date');
            } catch (\Throwable $e) {
                self::$hasCheckoutDateColumn = false;
            }
        }
        return self::$hasCheckoutDateColumn;
    }

    protected function normalizeDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string)$value);
        if ($trimmed === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $trimmed)->format('Y-m-d');
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        try {
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        if (!empty($arrayData['tanggal'])) {
            $normalizedTanggal = $this->normalizeDate((string)$arrayData['tanggal']);
            if ($normalizedTanggal) {
                $arrayData['tanggal'] = $normalizedTanggal;
            }
        }

        if (!$this->hasCheckoutDateColumn()) {
            unset($arrayData['checkout_date']);
        } elseif (array_key_exists('checkout_date', $arrayData)) {
            if ($arrayData['checkout_date'] === '' || $arrayData['checkout_date'] === null) {
                $arrayData['checkout_date'] = null;
            } else {
                $normalizedCo = $this->normalizeDate((string)$arrayData['checkout_date']);
                if ($normalizedCo) {
                    $arrayData['checkout_date'] = $normalizedCo;
                }
                if (!empty($arrayData['tanggal']) && !empty($arrayData['checkout_date']) && $arrayData['checkout_date'] < $arrayData['tanggal']) {
                    return [
                        "model" => $model,
                        "data" => $arrayData,
                        "errors" => ["Tanggal checkout tidak boleh lebih kecil dari tanggal presensi."]
                    ];
                }
            }
        }
        return ["model" => $model, "data" => $arrayData];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        if (!empty($arrayData['tanggal'])) {
            $normalizedTanggal = $this->normalizeDate((string)$arrayData['tanggal']);
            if ($normalizedTanggal) {
                $arrayData['tanggal'] = $normalizedTanggal;
            }
        }

        if (!$this->hasCheckoutDateColumn()) {
            unset($arrayData['checkout_date']);
        } elseif (array_key_exists('checkout_date', $arrayData)) {
            if ($arrayData['checkout_date'] === '' || $arrayData['checkout_date'] === null) {
                $arrayData['checkout_date'] = null;
            } else {
                $normalizedCo = $this->normalizeDate((string)$arrayData['checkout_date']);
                if ($normalizedCo) {
                    $arrayData['checkout_date'] = $normalizedCo;
                }
                $existingTanggal = $arrayData['tanggal'] ?? ($id ? DB::table($this->getTable())->where('id', $id)->value('tanggal') : null);
                $existingTanggal = $existingTanggal ? $this->normalizeDate((string)$existingTanggal) : null;
                if ($existingTanggal && !empty($arrayData['checkout_date']) && $arrayData['checkout_date'] < $existingTanggal) {
                    return [
                        "model" => $model,
                        "data" => $arrayData,
                        "errors" => ["Tanggal checkout tidak boleh lebih kecil dari tanggal presensi."]
                    ];
                }
            }
        }
        return ["model" => $model, "data" => $arrayData];
    }

    private function matchDayRow($detailsCollection, Carbon $dateObj)
    {
        if (!$detailsCollection || $detailsCollection->isEmpty()) {
            return null;
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

        $dayNumIso = (int)$dateObj->dayOfWeekIso;
        $namaHariId = strtolower(trim($dateObj->translatedFormat('l')));
        $namaHariEn = strtolower(trim($dateObj->format('l')));

        return $detailsCollection->first(function ($row) use ($dayNumIso, $namaHariId, $namaHariEn, $dayMap) {
            $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
            $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
            $calcDayNum = $dayMap[$rDayLower] ?? 0;

            if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
            if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
            if ($rDayLower === $namaHariId || $rDayLower === $namaHariEn) return true;
            return false;
        });
    }

    private function resolveJadwalKerjaHari($karyId, $dateStr): array
    {
        $empty = [
            'jadwal' => null,
            'jam_kerja_row' => null,
            'm_jam_kerja' => null,
            'm_jam_kerja_id' => null,
            't_jadwal_kerja_n_id' => null,
            't_jadwal_kerja_d_n_id' => null,
            't_jadwal_kerja_d_hari_n_id' => null,
            'shift' => null,
        ];

        if (!$karyId) {
            return $empty;
        }

        $dateObj = Carbon::parse($dateStr);
        $targetDateStr = $dateObj->format('Y-m-d');

        $jadwal = DB::table('t_jadwal_kerja_d_n as d')
            ->leftJoin('t_jadwal_kerja_n as j', 'j.id', '=', 'd.t_jadwal_kerja_n_id')
            ->select('d.*', 'j.keterangan as master_keterangan', 'j.status as master_status')
            ->where('d.m_kary_id', $karyId)
            ->where(function ($q) use ($targetDateStr) {
                $q->whereNull('d.start_date')
                  ->orWhereDate('d.start_date', '<=', $targetDateStr);
            })
            ->orderByRaw("CASE WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' AND UPPER(COALESCE(j.status, 'AKTIF')) = 'AKTIF' THEN 0 WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' THEN 1 ELSE 2 END ASC")
            ->orderByDesc('d.start_date')
            ->orderByDesc('d.id')
            ->first();

        $masterJadwalId = $jadwal->t_jadwal_kerja_n_id ?? null;
        $jamKerjaRow = null;
        $fromNewTable = false;

        if ($masterJadwalId) {
            $newRows = DB::table('t_jadwal_kerja_d_hari_n')
                ->where('t_jadwal_kerja_n_id', $masterJadwalId)
                ->get();
            if ($newRows->isNotEmpty()) {
                $jamKerjaRow = $this->matchDayRow($newRows, $dateObj);
                $fromNewTable = $jamKerjaRow !== null;
            } else {
                $legRows = DB::table('t_jadwal_kerja_det_hari')
                    ->where('t_jadwal_kerja_id', $masterJadwalId)
                    ->get();
                $jamKerjaRow = $this->matchDayRow($legRows, $dateObj);
            }
        }

        if (!$jamKerjaRow) {
            $legacyDet = DB::table('t_jadwal_kerja_det')
                ->where('m_kary_id', $karyId)
                ->orderByDesc('id')
                ->first();
            if ($legacyDet && !empty($legacyDet->t_jadwal_kerja_id)) {
                $legRows = DB::table('t_jadwal_kerja_det_hari')
                    ->where('t_jadwal_kerja_id', $legacyDet->t_jadwal_kerja_id)
                    ->get();
                $jamKerjaRow = $this->matchDayRow($legRows, $dateObj);
            }
        }

        $mJamKerja = null;
        $mJamKerjaId = null;
        $shift = null;

        if ($jamKerjaRow) {
            $tipeHari = strtoupper(trim((string)($jamKerjaRow->tipe_hari ?? 'KERJA')));
            if (empty($tipeHari)) $tipeHari = 'KERJA';

            if ($tipeHari === 'LIBUR') {
                $mJamKerjaId = null;
                $shift = 'LIBUR';
            } else {
                $mJamKerjaId = $jamKerjaRow->m_jam_kerja_id ?? null;
                if ($mJamKerjaId) {
                    $mJamKerja = DB::table('m_jam_kerja')->where('id', $mJamKerjaId)->first();
                }
                $wMulai = $mJamKerja->waktu_mulai ?? ($jamKerjaRow->waktu_mulai ?? null);
                $wAkhir = $mJamKerja->waktu_akhir ?? ($jamKerjaRow->waktu_akhir ?? null);
                if ($mJamKerja && !empty($mJamKerja->desc)) {
                    $shift = $mJamKerja->desc;
                } elseif ($mJamKerja && !empty($mJamKerja->kode)) {
                    $shift = $mJamKerja->kode;
                } elseif (!empty($wMulai) && !empty($wAkhir)) {
                    $shift = $tipeHari . ' (' . substr((string)$wMulai, 0, 5) . ' - ' . substr((string)$wAkhir, 0, 5) . ')';
                } elseif (!empty($jadwal->master_keterangan)) {
                    $shift = $jadwal->master_keterangan;
                } else {
                    $shift = 'REGULER';
                }
            }
        } elseif ($jadwal && !empty($jadwal->master_keterangan)) {
            $shift = $jadwal->master_keterangan;
        }

        return [
            'jadwal' => $jadwal,
            'jam_kerja_row' => $jamKerjaRow,
            'm_jam_kerja' => $mJamKerja,
            'm_jam_kerja_id' => $mJamKerjaId,
            't_jadwal_kerja_n_id' => $masterJadwalId,
            't_jadwal_kerja_d_n_id' => $jadwal->id ?? null,
            't_jadwal_kerja_d_hari_n_id' => $fromNewTable ? ($jamKerjaRow->id ?? null) : null,
            'shift' => $shift,
        ];
    }

    private function isWithinShiftTolerance($absenRow, $karyId, Carbon $now): bool
    {
        if (!$absenRow || empty($absenRow->checkin_time)) {
            return false;
        }

        $absenDateStr = Carbon::parse($absenRow->tanggal)->format('Y-m-d');
        $jamKerjaRow = null;
        if (!empty($absenRow->t_jadwal_kerja_d_hari_n_id)) {
            $jamKerjaRow = DB::table('t_jadwal_kerja_d_hari_n')
                ->where('id', $absenRow->t_jadwal_kerja_d_hari_n_id)
                ->first();
        }

        $resolved = null;
        if (!$jamKerjaRow) {
            $resolved = $this->resolveJadwalKerjaHari($karyId, $absenDateStr);
            $jamKerjaRow = $resolved['jam_kerja_row'];
        }

        $mJamKerja = null;
        $mJamKerjaId = $absenRow->m_jam_kerja_id ?? ($jamKerjaRow->m_jam_kerja_id ?? null);
        if ($mJamKerjaId) {
            $mJamKerja = DB::table('m_jam_kerja')->where('id', $mJamKerjaId)->first();
        }

        $waktuMulai = $jamKerjaRow->waktu_mulai ?? ($mJamKerja->waktu_mulai ?? null);
        $waktuAkhir = $jamKerjaRow->waktu_akhir ?? ($mJamKerja->waktu_akhir ?? null);
        $isValidScheduleTimes = !empty($waktuMulai)
            && !empty($waktuAkhir)
            && !($waktuMulai === '00:00:00' && $waktuAkhir === '00:00:00')
            && !($waktuMulai === '00:00' && $waktuAkhir === '00:00');

        if ($isValidScheduleTimes) {
            $mulaiTime = Carbon::parse($waktuMulai)->format('H:i:s');
            $akhirTime = Carbon::parse($waktuAkhir)->format('H:i:s');
            $waktuAkhirShift = Carbon::parse($absenDateStr . ' ' . $akhirTime);

            if ($akhirTime < $mulaiTime || !empty($mJamKerja->is_hari_berikutnya)) {
                $waktuAkhirShift->addDay();
            }

            $checkinCarbon = Carbon::parse($absenDateStr . ' ' . $absenRow->checkin_time);
            if ($waktuAkhirShift->lessThan($checkinCarbon) && $absenRow->checkin_time >= '16:00:00') {
                return $now->greaterThanOrEqualTo($checkinCarbon) && $checkinCarbon->diffInHours($now) <= 16;
            }

            $batasCheckout = $waktuAkhirShift->copy()->addHours(4);
            return $now->lessThanOrEqualTo($batasCheckout);
        }

        if ($absenRow->checkin_time >= '16:00:00') {
            $checkinCarbon = Carbon::parse($absenDateStr . ' ' . $absenRow->checkin_time);
            return $now->greaterThanOrEqualTo($checkinCarbon) && $checkinCarbon->diffInHours($now) <= 16;
        }

        return false;
    }

    public $fileColumns = [];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function onRetrieved($model)
    {
        $backendUrl = rtrim(env('BACKEND_URL'), '/');
        $fotoColumns = ['checkout_foto', 'checkin_foto'];

        foreach ($fotoColumns as $column) {
            $value = $model->{$column};

            if (!empty($value) && !str_starts_with(strtolower($value), 'http')) {
                $model->{$column} = $backendUrl . '/' . ltrim($value, '/');
            }
        }
    }

    public function default_users()
    {
        return $this->BelongsTo(
            "App\Models\BasicModels\default_users",
            "default_user_id",
            "id"
        );
    }

    public function custom_get_by_daily($req)
    {
        // $req->month = $req->month . '-01';
        // $data = \DB::select("
        //     SELECT json_agg(json_build_object(
        //         'all_days_of_month', all_days_of_month,
        //         'date_to_idn', date_to_idn,
        //         'day_name_idn', day_name_idn,
        //         'type', type,
        //         'presentase', presentase,
        //         'attend', attend,
        //         'cuti', cuti,
        //         'alpha', alpha,
        //         'total_kary', total_kary
        //     )) AS monthly_report
        //     FROM generate_monthly_report(?,?,?)", [$req->month, $req->divisi_id, $req->dept_id]);

        // if (count($data)) {
        //     return $this->helper->customResponse('OK', 200, json_decode($data[0]->monthly_report));
        // } else {
        //     return $this->helper->customResponse('OK', 200, []);
        // }

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $divisiId = $req->divisi_id ?? $req->m_divisi_id;
        $os_id = $req->m_os_id ?? null;
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->pluck('id');

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $totalKary = default_users::whereHas("m_kary", function ($q) use (
            $req,
            $divisiId,
            $status_os
        ) {
            $q->where("is_active", true)
                ->whereNotIn('status_kary_id', $status_os);

            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->count();

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $divisiId, $status_os) {
                $q1->where("is_active", true)
                    ->whereNotIn('status_kary_id', $status_os);

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($divisiId)) {
                    $q1->where('m_divisi_id', $divisiId);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function ($q) use ($req, $divisiId, $status_os) {
                    $q->where("is_active", true)
                        ->whereNotIn('status_kary_id', $status_os);

                    if (!empty($req->m_subcomp_id)) {
                        $q->where('m_subcomp_id', $req->m_subcomp_id);
                    }
                    if (!empty($req->m_branch_id)) {
                        $q->where('m_branch_id', $req->m_branch_id);
                    }
                    if (!empty($divisiId)) {
                        $q->where('m_divisi_id', $divisiId);
                    }
                })
                ->where("status", "APPROVED")
                ->whereDate("date_from", "<=", $date)
                ->whereDate("date_to", ">=", $date)
                ->count();

            $alpha = max(0, $totalKary - $hadir - $cuti);

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    public function custom_get_by_daily_os($req)
    {

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $divisiId = $req->divisi_id ?? $req->m_divisi_id;
        $os_id = $req->m_os_id ?? null;

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->pluck('id');

        $totalKary = default_users::whereHas("m_kary", function ($q) use ($req, $divisiId, $os_id, $status_os) {
            $q->where("is_active", true)
                ->whereIn('status_kary_id', $status_os)
                ->when($os_id, function ($q2) use ($os_id) {
                    $q2->where('m_company_outsourcing_id', $os_id);
                });

            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->count();

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $divisiId, $os_id, $status_os) {
                $q1->where("is_active", true)
                    ->whereIn('status_kary_id', $status_os)
                    ->when($os_id, function ($q2) use ($os_id) {
                        $q2->where('m_company_outsourcing_id', $os_id);
                    });

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($divisiId)) {
                    $q1->where('m_divisi_id', $divisiId);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function ($q) use ($req, $divisiId, $os_id, $status_os) {
                    $q->where("is_active", true)
                        ->whereIn('status_kary_id', $status_os)
                        ->when($os_id, function ($q2) use ($os_id) {
                            $q2->where('m_company_outsourcing_id', $os_id);
                        });

                    if (!empty($req->m_subcomp_id)) {
                        $q->where('m_subcomp_id', $req->m_subcomp_id);
                    }
                    if (!empty($req->m_branch_id)) {
                        $q->where('m_branch_id', $req->m_branch_id);
                    }
                    if (!empty($divisiId)) {
                        $q->where('m_divisi_id', $divisiId);
                    }
                })
                ->where("status", "APPROVED")
                ->whereDate("date_from", "<=", $date)
                ->whereDate("date_to", ">=", $date)
                ->count();

            $alpha = max(0, $totalKary - $hadir - $cuti);

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    public function custom_get_by_daily_harian($req)
    {

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $divisiId = $req->divisi_id ?? $req->m_divisi_id;

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $status_harian = m_general::where('group', 'STATUS KARYAWAN')->where('value', 'HARIAN')
            ->pluck('id');

        $totalKary = default_users::whereHas("m_kary", function ($q) use ($req, $divisiId, $status_harian) {
            $q->where("is_active", true)
                ->whereIn('status_kary_id', $status_harian);

            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->count();

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $divisiId, $status_harian) {
                $q1->where("is_active", true)
                    ->whereIn('status_kary_id', $status_harian);

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($divisiId)) {
                    $q1->where('m_divisi_id', $divisiId);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function ($q) use ($req, $divisiId, $status_harian) {
                    $q->where("is_active", true)
                        ->whereIn('status_kary_id', $status_harian);

                    if (!empty($req->m_subcomp_id)) {
                        $q->where('m_subcomp_id', $req->m_subcomp_id);
                    }
                    if (!empty($req->m_branch_id)) {
                        $q->where('m_branch_id', $req->m_branch_id);
                    }
                    if (!empty($divisiId)) {
                        $q->where('m_divisi_id', $divisiId);
                    }
                })
                ->where("status", "APPROVED")
                ->whereDate("date_from", "<=", $date)
                ->whereDate("date_to", ">=", $date)
                ->count();

            $alpha = max(0, $totalKary - $hadir - $cuti);

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    private function formatPresensiByDateRow($user, string $dateStr): array
    {
        $absensi = $user->presensi_absensi->first();
        $jadwal = $absensi && $absensi->t_jadwal_kerja_d_hari_n ? $absensi->t_jadwal_kerja_d_hari_n : null;
        $mJamKerja = $absensi?->m_jam_kerja ?? ($jadwal?->m_jam_kerja ?? null);
        $resolved = null;

        if (!$jadwal && !empty($user->m_kary_id)) {
            $resolved = $this->resolveJadwalKerjaHari($user->m_kary_id, $dateStr);
            $jadwal = $resolved['jam_kerja_row'];
            $mJamKerja = $mJamKerja ?? $resolved['m_jam_kerja'];
        }

        if ($absensi) {
            $status = $absensi->status;
            $absensiData = $absensi;
        } else {
            $status = "NOT ATTEND";
            $absensiData = ['status' => 'NOT ATTEND'];
        }

        $tipeHari = strtoupper(trim((string)($jadwal->tipe_hari ?? '')));
        if ($tipeHari === 'LIBUR') {
            $jamKerjaText = 'LIBUR';
            $descText = 'LIBUR';
        } else {
            $wMulai = $jadwal->waktu_mulai ?? ($mJamKerja->waktu_mulai ?? null);
            $wAkhir = $jadwal->waktu_akhir ?? ($mJamKerja->waktu_akhir ?? null);
            $jamKerjaText = (!empty($wMulai) && !empty($wAkhir)) ? ($wMulai . ' - ' . $wAkhir) : '-';
            $descText = $mJamKerja?->desc ?? ($absensi?->shift ?? ($resolved['shift'] ?? '-'));
        }

        return [
            'm_kary_id'           => $user->m_kary_id,
            'default_user_id'     => $user->id,
            'presensi_absensi_id' => $absensi?->id ?? 0,
            'kode'                => $user->kode,
            'nama_lengkap'        => $user->m_kary->nama_lengkap ?? null,
            'absensi'             => $absensiData,
            'status'              => $status,
            'jam_kerja'           => $jamKerjaText,
            'desc'                => $descText,
            'shift'               => $absensi?->shift ?? ($resolved['shift'] ?? $descText),
        ];
    }

    public function custom_get_by_date($req)
    {
        $req = app()->request;
        $divisiId = $req->divisi_id ?? $req->m_divisi_id;
        $dateStr = $req->date ?: date('Y-m-d');
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->pluck('id');

        $kary = default_users::with([
            'm_kary',
            'presensi_absensi' => function ($q) use ($dateStr) {
                $q->where('tanggal', $dateStr)
                  ->with(['t_jadwal_kerja_d_hari_n.m_jam_kerja', 'm_jam_kerja']);
            }
        ])
        ->whereHas('m_kary', function ($q) use ($req, $divisiId, $status_os) {
            $q->where('is_active', true)
              ->whereNotIn('status_kary_id', $status_os);

            if (!empty($req->m_os_id)) {
                $q->where('m_company_outsourcing_id', $req->m_os_id);
            }
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->get()
        ->map(function ($user) use ($dateStr) {
            return $this->formatPresensiByDateRow($user, $dateStr);
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap'] ?? '');
        })
        ->values();

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_get_by_date_os($req)
    {
        $req = app()->request;
        $divisiId = $req->divisi_id ?? $req->m_divisi_id;
        $dateStr = $req->date ?: date('Y-m-d');
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->pluck('id');

        $kary = default_users::with([
            'm_kary',
            'presensi_absensi' => function ($q) use ($dateStr) {
                $q->where('tanggal', $dateStr)
                  ->with(['t_jadwal_kerja_d_hari_n.m_jam_kerja', 'm_jam_kerja']);
            }
        ])
        ->whereHas('m_kary', function ($q) use ($req, $divisiId, $status_os) {
            $q->where('is_active', true)
              ->whereIn('status_kary_id', $status_os);
            if (!empty($req->m_os_id)) {
                $q->where('m_company_outsourcing_id', $req->m_os_id);
            }
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->get()
        ->map(function ($user) use ($dateStr) {
            return $this->formatPresensiByDateRow($user, $dateStr);
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap'] ?? '');
        })
        ->values();

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_get_by_date_harian($req)
    {
        $req = app()->request;
        $divisiId = $req->divisi_id ?? $req->m_divisi_id;
        $dateStr = $req->date ?: date('Y-m-d');
        $status_harian = m_general::where('group', 'STATUS KARYAWAN')->where('value', 'HARIAN')
            ->pluck('id');

        $kary = default_users::with([
            'm_kary',
            'presensi_absensi' => function ($q) use ($dateStr) {
                $q->where('tanggal', $dateStr)
                  ->with(['t_jadwal_kerja_d_hari_n.m_jam_kerja', 'm_jam_kerja']);
            }
        ])
        ->whereHas('m_kary', function ($q) use ($req, $divisiId, $status_harian) {
            $q->where('is_active', true)
              ->whereIn('status_kary_id', $status_harian);
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($divisiId)) {
                $q->where('m_divisi_id', $divisiId);
            }
        })
        ->get()
        ->map(function ($user) use ($dateStr) {
            return $this->formatPresensiByDateRow($user, $dateStr);
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap'] ?? '');
        })
        ->values();

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) {
            return $this->helper->responseValidate($validator);
        }

        $userId = auth()->user()->id;
        $kary_id = default_users::find($userId)?->m_kary_id;

        $now = \Carbon\Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $yesterdayStr = $now->copy()->subDay()->format('Y-m-d');

        // 1. Cek shift kemarin yang belum checkout (masih dalam toleransi)
        $presensiYesterday = $this->where('default_user_id', $userId)
            ->where('tanggal', $yesterdayStr)
            ->first();

        if (
            $presensiYesterday
            && $presensiYesterday->status != 'ATTEND'
            && !empty($presensiYesterday->checkin_time)
            && $this->isWithinShiftTolerance($presensiYesterday, $kary_id, $now)
        ) {
            return $this->helper->customResponse(
                "Anda masih memiliki shift aktif kemarin yang belum checkout. Silakan checkout terlebih dahulu.",
                422
            );
        }

        // 2. Cek duplikasi checkin hari ini
        $check_exists_absen = $this->where("tanggal", $todayStr)
            ->where("default_user_id", $userId)
            ->exists();
        if ($check_exists_absen) {
            return $this->helper->customResponse(
                "Anda sudah checkin hari ini",
                422
            );
        }

        // 3. Proses Upload Gambar
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = auth()->user()->username . ':::' . md5(time()) . '.webp';

            $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
            $destination = public_path('uploads/presensi/' . $subfolder);

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    $src = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'png':
                    $src = imagecreatefrompng($file->getRealPath());
                    imagepalettetotruecolor($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file->getRealPath());
                    break;
                default:
                    return $this->helper->customResponse("Format gambar tidak didukung", 422);
            }

            if (!$src) {
                return $this->helper->customResponse("Gagal memproses file gambar", 422);
            }

            imagewebp($src, $destination . '/' . $fileName, 80);
            imagedestroy($src);

            $storedPath = 'uploads/presensi/' . $subfolder . '/' . $fileName;
        } else {
            return $this->helper->customResponse("IMAGE NOT VALID", 422);
        }

        // 4. Simpan ke DB beserta snapshot jadwal & jam kerja
        DB::beginTransaction();
        try {
            $distance = $kary_id ? $this->distance($req->lat, $req->long, $kary_id) : false;
            $data = [];
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = $req->catatan_in ?? null;
            }

            $snapshot = $this->resolveJadwalKerjaHari($kary_id, $todayStr);
            $data["m_jam_kerja_id"] = $snapshot['m_jam_kerja_id'];
            $data["t_jadwal_kerja_d_n_id"] = $snapshot['t_jadwal_kerja_d_n_id'];
            $data["t_jadwal_kerja_n_id"] = $snapshot['t_jadwal_kerja_n_id'];
            $data["t_jadwal_kerja_d_hari_n_id"] = $snapshot['t_jadwal_kerja_d_hari_n_id'];
            $data["shift"] = $snapshot['shift'];

            $new = $this->create([
                'tanggal'                    => $todayStr,
                'status'                     => 'WORKING',
                'checkin_time'               => $now->format('H:i:s'),
                'checkin_foto'               => $storedPath,
                'checkin_lat'                => $data['checkin_lat'],
                'checkin_long'               => $data['checkin_long'],
                'checkin_address'            => $data['checkin_address'],
                'checkin_region'             => $data['region'],
                'checkin_on_scope'           => $data['on_scope'],
                'catatan_in'                 => $data['catatan_in'],
                'default_user_id'            => $userId,
                'creator_id'                 => $userId,
                'm_jam_kerja_id'             => $snapshot['m_jam_kerja_id'],
                't_jadwal_kerja_n_id'        => $snapshot['t_jadwal_kerja_n_id'],
                't_jadwal_kerja_d_n_id'      => $snapshot['t_jadwal_kerja_d_n_id'],
                't_jadwal_kerja_d_hari_n_id' => $snapshot['t_jadwal_kerja_d_hari_n_id'],
                'shift'                      => $snapshot['shift'],
            ]);

            DB::commit();
            return $this->helper->customResponse("Checkin berhasil", 200, array_merge($data, [
                'id'     => $new->id,
                'status' => $new->status,
            ]));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkin gagal, coba kembali nanti - " . $e->getMessage(), 400);
        }
    }

    public function custom_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) {
            return $this->helper->responseValidate($validator);
        }

        $userId = auth()->user()->id;
        $kary_id = default_users::find($userId)?->m_kary_id;

        // Cek apakah dia OS / bukan
        $status_os_ids = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();
        $kary = m_kary::find($kary_id);
        $is_outsource = $kary ? in_array($kary->status_kary_id, $status_os_ids) : false;

        $now = \Carbon\Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay();
        $yesterdayStr = $yesterday->format('Y-m-d');

        // ====================================================================
        // 1. CARI DATA PRESENSI AKTIF (Cek Hari Ini -> Lalu Cek Kemarin)
        // ====================================================================
        $presensiAktif = $this->where('default_user_id', $userId)
            ->where('tanggal', $todayStr)
            ->first();

        // Jika hari ini belum checkin atau statusnya sudah ATTEND, kita cek data kemarin
        if (!$presensiAktif || $presensiAktif->status == 'ATTEND') {
            $presensiKemarin = $this->where('default_user_id', $userId)
                ->where('tanggal', $yesterdayStr)
                ->first();

            // Jika ada data kemarin yang belum checkout (masih WORKING)
            if ($presensiKemarin && $presensiKemarin->status != 'ATTEND' && $presensiKemarin->checkin_time) {
                $isValidForYesterday = $this->isWithinShiftTolerance($presensiKemarin, $kary_id, $now);

                if ($isValidForYesterday) {
                    $presensiAktif = $presensiKemarin; // Gunakan presensi kemarin untuk di-checkout
                } else if (!$presensiAktif) {
                    return $this->helper->customResponse("Waktu checkout untuk shift kemarin sudah lewat. Anda juga belum checkin hari ini.", 422);
                }
            }
        }

        // ====================================================================
        // 2. VALIDASI STATUS AKHIR
        // ====================================================================
        if (!$presensiAktif) {
            return $this->helper->customResponse("Anda belum checkin hari ini", 422);
        }

        if ($presensiAktif->status == 'ATTEND') {
            return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
        }

        // ====================================================================
        // 3. PROSES UPLOAD GAMBAR
        // ====================================================================
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = auth()->user()->username . ':::' . md5(time()) . '.webp';

            $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
            $destination = public_path('uploads/presensi/' . $subfolder);

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    $src = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'png':
                    $src = imagecreatefrompng($file->getRealPath());
                    imagepalettetotruecolor($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file->getRealPath());
                    break;
                default:
                    return $this->helper->customResponse("Format gambar tidak didukung", 422);
            }

            if (!$src) {
                return $this->helper->customResponse("Gagal memproses file gambar", 422);
            }

            imagewebp($src, $destination . '/' . $fileName, 80);
            imagedestroy($src);

            $storedPath = 'uploads/presensi/' . $subfolder . '/' . $fileName;
        } else {
            return $this->helper->customResponse("IMAGE NOT VALID", 422);
        }

        // ====================================================================
        // 4. PROSES DATABASE CHECKOUT (Transaksi)
        // ====================================================================
        DB::beginTransaction();
        try {
            // Jarak Scope
            $distance = $kary_id ? $this->distance($req->lat, $req->long, $kary_id) : false;
            $onScope = $distance ? true : false;
            $regionName = $distance ? $distance->nama : "Out Scope";
            $catatanOut = $distance ? null : ($req->catatan_out ?? null);

            // Jam Kerja Diakui (Outsource) dengan full Datetime
            $jam_kerja_diakui = null;
            if ($is_outsource && $presensiAktif->checkin_time) {
                $checkinDateStr = Carbon::parse($presensiAktif->tanggal)->format('Y-m-d');
                $checkinCarbon = \Carbon\Carbon::parse($checkinDateStr . ' ' . $presensiAktif->checkin_time);
                if ($checkinCarbon->lessThanOrEqualTo($now)) {
                    $jam_kerja_diakui = min(round($checkinCarbon->diffInMinutes($now) / 60, 2), 7);
                }
            }

            $updatePayload = [
                "checkout_time"     => $now->format("H:i:s"),
                "checkout_foto"     => $storedPath,
                "checkout_lat"      => $req->lat,
                "checkout_long"     => $req->long,
                "checkout_address"  => $req->address,
                "checkout_region"   => $regionName,
                "checkout_on_scope" => $onScope,
                "catatan_out"       => $catatanOut,
                "status"            => "ATTEND",
                "jam_kerja_diakui"  => $jam_kerja_diakui,
            ];

            if ($this->hasCheckoutDateColumn()) {
                $updatePayload["checkout_date"] = $todayStr;
            }

            // Pertahankan snapshot dari saat checkin; backfill hanya jika belum terisi
            if (
                empty($presensiAktif->m_jam_kerja_id)
                && empty($presensiAktif->t_jadwal_kerja_n_id)
                && empty($presensiAktif->t_jadwal_kerja_d_hari_n_id)
            ) {
                $fallbackSnapshot = $this->resolveJadwalKerjaHari($kary_id, $presensiAktif->tanggal);
                $updatePayload["m_jam_kerja_id"] = $fallbackSnapshot['m_jam_kerja_id'];
                $updatePayload["t_jadwal_kerja_n_id"] = $fallbackSnapshot['t_jadwal_kerja_n_id'];
                $updatePayload["t_jadwal_kerja_d_n_id"] = $fallbackSnapshot['t_jadwal_kerja_d_n_id'];
                $updatePayload["t_jadwal_kerja_d_hari_n_id"] = $fallbackSnapshot['t_jadwal_kerja_d_hari_n_id'];
                if (empty($presensiAktif->shift)) {
                    $updatePayload["shift"] = $fallbackSnapshot['shift'];
                }
            } elseif (empty($presensiAktif->shift)) {
                $fallbackSnapshot = $this->resolveJadwalKerjaHari($kary_id, $presensiAktif->tanggal);
                if (!empty($fallbackSnapshot['shift'])) {
                    $updatePayload["shift"] = $fallbackSnapshot['shift'];
                }
            }

            // Eksekusi Update ke Database
            $presensiAktif->update($updatePayload);

            DB::commit();
            return $this->helper->customResponse("Checkout berhasil", 200, $presensiAktif);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkout gagal, coba kembali nanti - " . $e->getMessage(), 500);
        }
    }


    private function distance($lat, $long, $kary_id)
    {
        // dd($lat, $long, $kary_id);
        $distance = DB::select("select distance_location(?,?,?)", [$lat, $long, $kary_id]);
        if (count($distance)) {
            $location = json_decode($distance[0]->distance_location);
            return @$location[0] ?? false;
        } else {
            return false;
        }
    }

    public function custom_distance_check($req)
    {
        $kary_id = default_users::find(auth()->user()->id)?->m_kary_id ?? 0;
        //dd(default_users::find(auth()->user()->id), $kary_id);
        $distance = $this->distance($req->lat, $req->long, $kary_id);
        if ($distance) {
            $data["on_scope"] = true;
            $data["region"] = $distance->nama;
            $data["checkout_lat"] = $distance->lat;
            $data["checkout_long"] = $distance->long;
            $data["checkout_address"] = $req->address;
        } else {
            $data["on_scope"] = false;
            $data["region"] = "Out Scope";
            $data["checkout_lat"] = $req->lat;
            $data["checkout_long"] = $req->long;
            $data["checkout_address"] = $req->address;
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    // public function custom_status($model)
    // {
    //     // $now = app()->request->now ? Carbon::parse(app()->request->now) : $this->now;
    //     $now = $this->now;
    //     $workHour = $this->check_presensi_by_work_hour(auth()->user()->id);
    //     // dd($workHour);

    //     if ($now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
    //         $data = [
    //             'status' => $this->where(
    //                 "created_at",
    //                 ">=",
    //                 $workHour["waktu_mulai_with_date"]
    //             )->where("created_at", "<=", $workHour['waktu_akhir_with_date'])->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
    //         ];
    //     } else {
    //         $data = [
    //             'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
    //         ];
    //     }
    //     return $this->helper->customResponse("OK", 200, $data);
    // }

    public function custom_status($model)
    {
        $userId = auth()->user()->id ?? 0;

        $now = \Carbon\Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $yesterdayStr = $now->copy()->subDay()->format('Y-m-d');

        // 1. Cek Presensi Hari Ini Terlebih Dahulu
        $presensiHariIni = $this->where("tanggal", $todayStr)
            ->where("default_user_id", $userId)
            ->first();

        if ($presensiHariIni) {
            return $this->helper->customResponse("OK", 200, [
                "status" => $presensiHariIni->status ?? "NOT ATTEND"
            ]);
        }

        // 2. Jika tidak ada presensi hari ini, cek kemarin
        $presensiKemarin = $this->where("tanggal", $yesterdayStr)
            ->where("default_user_id", $userId)
            ->first();

        $data = [ "status" => 'NOT ATTEND' ];

        if ($presensiKemarin) {
            $status = $presensiKemarin->status ?? null;
            $checkin = $presensiKemarin->checkin_time ?? null;

            // Jika statusnya belum ATTEND (artinya sudah checkin kemarin tapi belum checkout)
            if ($status && $status != 'ATTEND' && $checkin) {
                $karyId = default_users::find($userId)?->m_kary_id;
                if ($this->isWithinShiftTolerance($presensiKemarin, $karyId, $now)) {
                    $data = [ "status" => $status ];
                }
            }
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function scopeFilter($model)
    {
        if (req('date_from') && req('date_to')) {
            return $model->whereBetween('tanggal', [req('date_from'), req('date_to')])->where('default_user_id', auth()->user()->id ?? 0);
        }
    }

    public function custom_get_absen_old($req)
    {
        $periode = ($req->periode ?? date('Y-m')) . '-1';
        $m_kary_id = auth()->user()->m_kary_id;

        $data = \DB::select("
            select * from employee_attendance_detail(?, ?);
        ", [$periode, $m_kary_id ?? 0]);

        // transform object for mobile
        foreach ($data as $dt) {
            $dt->status = @json_decode($dt->absensi)->status ?? null;
            $dt->tanggal = @json_decode($dt->absensi)->tanggal ?? null;
            $dt->catatan_in = @json_decode($dt->absensi)->catatan_in ?? null;
            $dt->catatan_out = @json_decode($dt->absensi)->catatan_out ?? null;
            $dt->checkin_lat = @json_decode($dt->absensi)->checkin_lat ?? null;
            $dt->checkin_foto = @json_decode($dt->absensi)->checkin_foto ? url('') . '/' . @json_decode($dt->absensi)->checkin_foto : null;
            $dt->checkin_long = @json_decode($dt->absensi)->checkin_long ?? null;
            $dt->checkin_time = @json_decode($dt->absensi)->checkin_time ?? null;
            $dt->checkout_lat = @json_decode($dt->absensi)->checkout_lat ?? null;
            $dt->checkout_foto = @json_decode($dt->absensi)->checkout_foto ? url('') . '/' . @json_decode($dt->absensi)->checkout_foto : null;
            $dt->checkout_long = @json_decode($dt->absensi)->checkout_long ?? null;
            $dt->checkout_time = @json_decode($dt->absensi)->checkout_time ?? null;
            $dt->checkin_region = @json_decode($dt->absensi)->checkin_region ?? null;
            $dt->checkin_address = @json_decode($dt->absensi)->checkin_address ?? null;
            $dt->checkout_region = @json_decode($dt->absensi)->checkout_region ?? null;
            $dt->checkin_on_scope = @json_decode($dt->absensi)->checkin_on_scope ?? null;
            $dt->checkout_address = @json_decode($dt->absensi)->checkout_address ?? null;
            $dt->checkout_on_scope = @json_decode($dt->absensi)->checkout_on_scope ?? null;
            $dt->presensi_absensi_id = @json_decode($dt->absensi)->presensi_absensi_id ?? null;
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_get_absen($req)
    {
        $reqPeriode = trim((string)($req->periode ?? date('Y-m')));
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $reqPeriode, $m)) {
            $periodeDate = Carbon::createFromDate((int)$m[1], (int)$m[2], 1)->startOfDay();
        } else {
            $periodeDate = Carbon::parse($reqPeriode . '-01');
        }

        $authUser = auth()->user();
        $userId = $authUser->id ?? 0;
        $m_kary_id = $authUser->m_kary_id ?? null;
        if (!$m_kary_id && $userId) {
            $m_kary_id = default_users::find($userId)?->m_kary_id;
        }

        $kary = $m_kary_id ? DB::table('m_kary')->where('id', $m_kary_id)->first() : null;
        $karyJson = json_encode([
            'nik' => $kary->nik ?? '',
            'm_kary_id' => $m_kary_id,
            'nama_lengkap' => $kary->nama_lengkap ?? ''
        ]);

        $startDate = $periodeDate->copy()->startOfMonth();
        $endDate = $periodeDate->copy()->endOfMonth();
        $startStr = $startDate->format('Y-m-d');
        $endStr = $endDate->format('Y-m-d');

        $absensis = DB::table('presensi_absensi')
            ->where('default_user_id', $userId)
            ->whereBetween('tanggal', [$startStr, $endStr])
            ->get()
            ->keyBy(function ($row) {
                return $this->normalizeDate((string)($row->tanggal ?? '')) ?? substr((string)($row->tanggal ?? ''), 0, 10);
            });

        $cutis = $m_kary_id
            ? DB::table('t_cuti')
                ->where('m_kary_id', $m_kary_id)
                ->where('status', 'APPROVED')
                ->whereDate('date_from', '<=', $endStr)
                ->whereDate('date_to', '>=', $startStr)
                ->get()
            : collect();

        $liburs = DB::table('m_libur_nasional')
            ->whereBetween('tanggal', [$startStr, $endStr])
            ->get()
            ->keyBy(function ($row) {
                return $this->normalizeDate((string)($row->tanggal ?? '')) ?? substr((string)($row->tanggal ?? ''), 0, 10);
            });

        $jadwals = $m_kary_id
            ? DB::table('t_jadwal_kerja_d_n as d')
                ->leftJoin('t_jadwal_kerja_n as j', 'j.id', '=', 'd.t_jadwal_kerja_n_id')
                ->select('d.*', 'j.status as master_status', 'j.keterangan as master_keterangan')
                ->where('d.m_kary_id', $m_kary_id)
                ->where(function ($q) use ($endStr) {
                    $q->whereNull('d.start_date')
                      ->orWhereDate('d.start_date', '<=', $endStr);
                })
                ->orderByRaw("CASE WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' AND UPPER(COALESCE(j.status, 'AKTIF')) = 'AKTIF' THEN 0 WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' THEN 1 ELSE 2 END ASC")
                ->orderByDesc('d.start_date')
                ->orderByDesc('d.id')
                ->get()
            : collect();

        $jadwalIds = $jadwals->pluck('t_jadwal_kerja_n_id')->filter()->unique()->values();
        $jadwalHarisNew = $jadwalIds->isNotEmpty()
            ? DB::table('t_jadwal_kerja_d_hari_n')
                ->whereIn('t_jadwal_kerja_n_id', $jadwalIds)
                ->get()
                ->groupBy('t_jadwal_kerja_n_id')
            : collect();
        $jadwalHarisLegacy = $jadwalIds->isNotEmpty()
            ? DB::table('t_jadwal_kerja_det_hari')
                ->whereIn('t_jadwal_kerja_id', $jadwalIds)
                ->get()
                ->groupBy('t_jadwal_kerja_id')
            : collect();

        $legacyDetKary = ($jadwals->isEmpty() && $m_kary_id)
            ? DB::table('t_jadwal_kerja_det')
                ->where('m_kary_id', $m_kary_id)
                ->orderByDesc('id')
                ->first()
            : null;
        $legacyDetHaris = ($legacyDetKary && !empty($legacyDetKary->t_jadwal_kerja_id))
            ? DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $legacyDetKary->t_jadwal_kerja_id)
                ->get()
            : collect();

        // Preload snapshot detail hari jika ada pada presensi_absensi
        $snapshotHariIds = $absensis->pluck('t_jadwal_kerja_d_hari_n_id')->filter()->unique()->values();
        $snapshotHariMap = $snapshotHariIds->isNotEmpty()
            ? DB::table('t_jadwal_kerja_d_hari_n')
                ->whereIn('id', $snapshotHariIds)
                ->get()
                ->keyBy('id')
            : collect();

        $hasCheckoutDate = $this->hasCheckoutDateColumn();
        $daysInMonth = $periodeDate->daysInMonth;
        $collection = collect();

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = $periodeDate->copy()->day($i);
            $dateStr = $currentDate->format('Y-m-d');
            $hariIndo = ucfirst($currentDate->translatedFormat('l'));

            $type = 'Hari Kerja';
            $status = 'NOT ATTEND';

            $absen = $absensis->get($dateStr);
            $jamKerjaRow = null;

            // Prioritaskan snapshot yang terkunci pada transaksi presensi
            if ($absen && !empty($absen->t_jadwal_kerja_d_hari_n_id)) {
                $jamKerjaRow = $snapshotHariMap->get($absen->t_jadwal_kerja_d_hari_n_id);
            }

            if (!$jamKerjaRow) {
                $activeJadwal = $jadwals->first(function ($j) use ($dateStr) {
                    if (empty($j->start_date)) {
                        return true;
                    }
                    $jStart = $this->normalizeDate((string)$j->start_date) ?? substr((string)$j->start_date, 0, 10);
                    return $jStart <= $dateStr;
                });

                if ($activeJadwal) {
                    $daysSetup = $jadwalHarisNew->get($activeJadwal->t_jadwal_kerja_n_id);
                    if (!$daysSetup || $daysSetup->isEmpty()) {
                        $daysSetup = $jadwalHarisLegacy->get($activeJadwal->t_jadwal_kerja_n_id);
                    }
                    if ($daysSetup && $daysSetup->isNotEmpty()) {
                        $jamKerjaRow = $this->matchDayRow($daysSetup, $currentDate);
                    }
                } elseif ($legacyDetHaris->isNotEmpty()) {
                    $jamKerjaRow = $this->matchDayRow($legacyDetHaris, $currentDate);
                }
            }

            if ($jamKerjaRow && strtoupper(trim((string)($jamKerjaRow->tipe_hari ?? ''))) === 'LIBUR') {
                $type = 'Hari Libur';
                $status = 'LIBUR';
            }

            foreach ($cutis as $c) {
                $cFrom = $this->normalizeDate((string)($c->date_from ?? '')) ?? substr((string)($c->date_from ?? ''), 0, 10);
                $cTo = $this->normalizeDate((string)($c->date_to ?? '')) ?? substr((string)($c->date_to ?? ''), 0, 10);
                if ($cFrom && $cTo && $dateStr >= $cFrom && $dateStr <= $cTo) {
                    $type = 'Cuti';
                    $status = 'CUTI';
                    break;
                }
            }

            if ($liburs->has($dateStr)) {
                $type = 'Libur Nasional';
                $status = 'LIBUR';
            }

            if ($absen) {
                $status = $absen->status ?? 'ATTEND';
            }

            $item = [
                "kary" => $karyJson,
                "all_days_of_month" => $dateStr,
                "date_to_idn" => $currentDate->format('d-m-Y'),
                "day_name_idn" => $hariIndo,
                "type" => $type,
                "absensi" => json_encode(["status" => $status]),
                "status" => $status,
                "tanggal" => $absen ? $absen->tanggal : null,
                "checkout_date" => $hasCheckoutDate ? ($absen->checkout_date ?? null) : null,
                "shift" => $absen->shift ?? null,
                "catatan_in" => $absen->catatan_in ?? null,
                "catatan_out" => $absen->catatan_out ?? null,
                "checkin_lat" => $absen->checkin_lat ?? null,
                "checkin_foto" => $absen->checkin_foto ?? null,
                "checkin_long" => $absen->checkin_long ?? null,
                "checkin_time" => $absen->checkin_time ?? null,
                "checkout_lat" => $absen->checkout_lat ?? null,
                "checkout_foto" => $absen->checkout_foto ?? null,
                "checkout_long" => $absen->checkout_long ?? null,
                "checkout_time" => $absen->checkout_time ?? null,
                "checkin_region" => $absen->checkin_region ?? null,
                "checkin_address" => $absen->checkin_address ?? null,
                "checkout_region" => $absen->checkout_region ?? null,
                "checkin_on_scope" => $absen->checkin_on_scope ?? null,
                "checkout_address" => $absen->checkout_address ?? null,
                "checkout_on_scope" => $absen->checkout_on_scope ?? null,
                "presensi_absensi_id" => $absen->id ?? null,
            ];

            $collection->push($item);
        }

        return $this->helper->customResponse("OK", 200, $collection);
    }

    public function custom_get_absen_new($req)
    {
        return $this->custom_get_absen($req);
    }


    private function get_jam_kerja($userId, $date, $checkin, $checkout)
    {
        $karyId = default_users::where('id', $userId)->first()->m_kary_id ?? 0;
        $kary = m_kary::where("id", $karyId)->first();
        if (!$kary) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        $matched_id = null;
        $toleranceMinutes = 30;

        $checkinCarbon = \Carbon::parse($date . ' ' . $checkin);
        $checkoutCarbon = \Carbon::parse($date . ' ' . $checkout);
        if ($checkoutCarbon < $checkinCarbon) {
            $checkoutCarbon->addDay();
        }

        $jam_kerja_ids = json_decode($kary['m_jam_kerja_id'], true);
        if ($jam_kerja_ids === null || !is_array($jam_kerja_ids)) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        foreach ($jam_kerja_ids as $jam_kerja_id) {
            $jamKerja = m_jam_kerja::find($jam_kerja_id);
            if (!$jamKerja)
                continue;

            $startWork = \Carbon::parse($date . ' ' . $jamKerja->waktu_mulai);
            $endWork = \Carbon::parse($date . ' ' . $jamKerja->waktu_akhir);

            if ($jamKerja->is_hari_berikutnya || $endWork->lessThan($startWork)) {
                $endWork = $endWork->addDay();
            }

            $isCheckinNearStart = $checkinCarbon->between(
                $startWork->copy()->subMinutes($toleranceMinutes),
                $startWork->copy()->addMinutes($toleranceMinutes)
            );

            $isCheckoutNearEnd = $checkoutCarbon->between(
                $endWork->copy()->subMinutes($toleranceMinutes),
                $endWork->copy()->addMinutes($toleranceMinutes)
            );

            $isCheckinInRange = $checkinCarbon->between($startWork, $endWork);
            $isCheckoutInRange = $checkoutCarbon->between($startWork, $endWork);

            if ($isCheckinNearStart && $isCheckoutNearEnd) {
                $matched_id = $jamKerja;
                break;
            } elseif ($isCheckinInRange && $isCheckoutInRange) {
                $matched_id = $jamKerja;
                break;
            }
        }
        if (!$matched_id) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }

        $jadwalKerja = $this->get_jadwal_kerja($matched_id, $date);

        return $matched_id = [
            't_jadwal_kerja_id' => $jadwalKerja['t_jadwal_kerja_id'] ?? null,
            't_jadwal_kerja_det_hari_id' => $jadwalKerja['t_jadwal_kerja_det_hari_id'] ?? null,
            'm_jam_kerja_id' => $matched_id->id,
            'nama_jam_kerja' => $matched_id->desc,
        ];
    }

    private function get_jadwal_kerja($matched_id, $date)
    {
        $jadwalKerja = t_jadwal_kerja::with([
            't_jadwal_kerja_det_hari' => function ($q) use ($matched_id, $date) {
                $q->where('m_jam_kerja_id', $matched_id->id)->where('day_num', \Carbon::parse($date)->dayOfWeekIso)
                    ->with([
                        't_jadwal_kerja_det' => function ($q2) {
                            // $q2->where('m_kary_id', $karyId);
                        }
                    ]);
            }
        ])
            ->whereHas('t_jadwal_kerja_det_hari', function ($q) use ($matched_id) {
                $q->where('m_jam_kerja_id', $matched_id->id);
            })
            ->first();
        return [
            't_jadwal_kerja_id' => $jadwalKerja->id ?? null,
            't_jadwal_kerja_det_hari_id' => $jadwalKerja->t_jadwal_kerja_det_hari[0]->id ?? null,
        ];
    }

    private function check_presensi_by_work_hour($id)
    {
        $getKaryID = default_users::where('id', $id)->first();
        $karyID = $getKaryID->m_kary_id ?? 0;

        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $yesterdayDate = $now->copy()->subDay();
        $yesterdayStr = $yesterdayDate->toDateString();

        $todayResolved = $this->resolveJadwalKerjaHari($karyID, $todayStr);
        $data = $todayResolved['jam_kerja_row'];
        if (!$data || empty($data->waktu_mulai) || empty($data->waktu_akhir)) {
            return [
                'yesterday' => false,
                'waktu_mulai_with_date' => null,
                'waktu_akhir_with_date' => null,
            ];
        }

        $checkinTime = Carbon::parse($todayStr . ' ' . $data->waktu_mulai);
        $checkoutTime = Carbon::parse($todayStr . ' ' . $data->waktu_akhir);
        if ($checkoutTime->lessThan($checkinTime)) {
            $checkoutTime->addDay();
        }

        $yesterdayResolved = $this->resolveJadwalKerjaHari($karyID, $yesterdayStr);
        $data_yesterday = $yesterdayResolved['jam_kerja_row'];
        if (!$data_yesterday || empty($data_yesterday->waktu_mulai) || empty($data_yesterday->waktu_akhir)) {
            return [
                'yesterday' => false,
                'waktu_mulai_with_date' => null,
                'waktu_akhir_with_date' => null,
            ];
        }

        $yesterdayKey = $data_yesterday->day ?? $yesterdayDate->translatedFormat('l');
        $parseWorkHourYesterday = [
            $yesterdayKey => [$data_yesterday->waktu_mulai, $data_yesterday->waktu_akhir]
        ];

        $checkYesterdayPresence = $this->checkYesterdayPresence($parseWorkHourYesterday, $id);

        if ($checkYesterdayPresence) {
            $yTanggal = Carbon::parse($checkYesterdayPresence->tanggal)->format('Y-m-d');
            $parseStart = \Carbon::parse($yTanggal . ' ' . $data_yesterday->waktu_mulai)->subHours(2);
            $parseEnd = \Carbon::parse($yTanggal . ' ' . $data_yesterday->waktu_akhir)->addHours(2);
            if ($parseStart->greaterThan($parseEnd)) {
                $parseEnd->addDay();
            }

            return [
                'yesterday' => true,
                'waktu_mulai_with_date' => $parseStart->toDateTimeString(),
                'waktu_akhir_with_date' => $parseEnd->toDateTimeString(),
            ];
        }

        if ($now->greaterThanOrEqualTo($checkinTime->copy()->subHour()) && $now->lessThanOrEqualTo($checkoutTime->copy()->addHour())) {
            return [
                'yesterday' => false,
                'waktu_mulai_with_date' => null,
                'waktu_akhir_with_date' => null,
            ];
        }

        return [
            'yesterday' => false,
            'waktu_mulai_with_date' => null,
            'waktu_akhir_with_date' => null,
        ];
    }

    private function checkYesterdayPresence($shift, $id)
    {
        $checkPresence = null;
        $now = \Carbon::now();
        $yesterday = $now
            ->copy()
            ->subDay()
            ->toDateString();

        foreach ($shift as $index => $single) {
            if (empty($single[0]) || empty($single[1])) {
                continue;
            }
            $shiftParse = [];
            if ($single[0] > $single[1]) {
                $shiftParse[] = \Carbon::parse(
                    $yesterday . " " . $single[0]
                )->toDateTimeString();
                $shiftParse[] = \Carbon::parse(
                    $now->copy()->toDateString() . " " . $single[1]
                )->toDateTimeString();
            } else {
                foreach ($single as $time) {
                    $shiftParse[] = \Carbon::parse(
                        $yesterday . " " . $time
                    )->toDateTimeString();
                }
            }

            $checkPresence = $this->select("*")
                ->where("default_user_id", $id ?? 0)
                ->whereNotNull("tanggal")
                ->whereNotNull("checkin_time")
                ->whereRaw("make_timestamp(EXTRACT(YEAR FROM tanggal)::int, EXTRACT(MONTH FROM tanggal)::int, EXTRACT(DAY FROM tanggal)::int, EXTRACT(HOUR FROM checkin_time)::int, EXTRACT(MINUTE FROM checkin_time)::int, 0) between ? and ?", $shiftParse)
                ->orderBy("tanggal", "desc")
                ->whereIn("status", ["WORKING", "BREAK", "RESUME WORKING", "ATTEND"])
                ->first();

            if ($checkPresence) {
                return $checkPresence;
            }
        }
        return $checkPresence;
    }

    public function custom_test_jam_kerja($req)
    {

        $result = $this->get_jadwal_kerja_n(
            $req->user_id,
            $req->date,
            $req->checkin,
            $req->checkout
        );
        return response()->json($result);
    }

    private function get_jadwal_kerja_n($userId, $date, $checkin, $checkout)
    {
        $targetDate = Carbon::parse($date);
        $targetDateStr = $targetDate->format('Y-m-d');
        $karyId = default_users::where('id', $userId)->first()->m_kary_id ?? 0;
        if (!$karyId) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }

        $resolved = $this->resolveJadwalKerjaHari($karyId, $targetDateStr);
        $jamKerjaRow = $resolved['jam_kerja_row'];
        $jadwal = $resolved['jadwal'];

        if (!$jamKerjaRow) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }

        return [
            't_jadwal_kerja_id' => $resolved['t_jadwal_kerja_n_id'],
            't_jadwal_kerja_det_hari_id' => $jamKerjaRow->id ?? null,
            'm_jam_kerja_id' => $resolved['m_jam_kerja_id'],
            'nama_jam_kerja' => $jadwal->master_keterangan ?? ($resolved['shift'] ?? null),
        ];
    }

    private function get_jam_kerja_n($getJadwalKerja, $dayNum, $checkin, $checkout)
    {
        $collectJamKerja = collect($getJadwalKerja->t_jadwal_kerja_d_hari_n);
        $filtered = $collectJamKerja
            ->where('day_num', $dayNum)
            ->first();
        if (!$filtered) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        return [
            't_jadwal_kerja_id' => $getJadwalKerja->id ?? null,
            't_jadwal_kerja_det_hari_id' => $filtered->id ?? null,
            'm_jam_kerja_id' => $filtered->m_jam_kerja_id ?? null,
            'nama_jam_kerja' => $getJadwalKerja->keterangan ?? null,
        ];
    }

}
