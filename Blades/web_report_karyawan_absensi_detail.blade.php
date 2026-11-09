@php
$req = app()->request;
$tipe = $req->tipe_report;
use App\Models\CustomModels\default_users;
use App\Models\CustomModels\presensi_absensi;
use App\Models\CustomModels\t_cuti;
use App\Models\CustomModels\t_lembur;
use App\Models\CustomModels\m_kary;
use App\Models\CustomModels\t_jadwal_kerja_d_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\m_libur_nasional;
use App\Models\CustomModels\m_standart_gaji;

Carbon::setLocale('id');

$start = Carbon::parse($req->periode_awal)->startOfDay();
$end   = Carbon::parse($req->periode_akhir)->startOfDay();
$period = CarbonPeriod::create($start, $end);
$startStr = $start->format('Y-m-d');
$endStr   = $end->format('Y-m-d');

// Query Filter Karyawan
$kary_ids = array_filter((array) ($req->kary_id ?? []));
$m_comp_ids = array_filter((array) ($req->m_comp_id ?? []));
$m_subcomp_ids = array_filter((array) ($req->m_subcomp_id ?? []));
$m_divisi_ids = array_filter((array) ($req->m_divisi_id ?? []));
$m_branch_ids = array_filter((array) ($req->m_branch_id ?? []));

$query = m_kary::query();
$query->where('is_active', true);
$query->when(!empty($kary_ids), fn($q) => $q->whereIn('id', $kary_ids));
$query->when(!empty($m_comp_ids), fn($q) => $q->whereHas('m_subcomp', fn($q2) => $q2->whereIn('m_comp_id', $m_comp_ids)));
$query->when(!empty($m_subcomp_ids), fn($q) => $q->whereIn('m_subcomp_id', $m_subcomp_ids));
$query->when(!empty($m_branch_ids), fn($q) => $q->whereIn('m_branch_id', $m_branch_ids));
$query->when(!empty($m_divisi_ids), fn($q) => $q->whereIn('m_divisi_id', $m_divisi_ids));

$listKaryawan = $query->pluck('id');

$liburNasional = m_libur_nasional::whereBetween('tanggal', [$startStr, $endStr])->get();
$liburDates = $liburNasional->pluck('keterangan', 'tanggal')->mapWithKeys(
    fn($keterangan, $tgl) => [Carbon::parse($tgl)->format('Y-m-d') => $keterangan ?? 'LIBUR']
);

$dayMap = [
    'senin' => 1, 'monday' => 1,
    'selasa' => 2, 'tuesday' => 2,
    'rabu' => 3, 'wednesday' => 3,
    'kamis' => 4, 'thursday' => 4,
    'jumat' => 5, 'jum\'at' => 5, 'friday' => 5,
    'sabtu' => 6, 'saturday' => 6,
    'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
];
@endphp

@foreach($listKaryawan as $index => $kary_id)
@php
    $hasil = [];
    $jumlah_telat = 0;
    $jumlah_lembur = 0;
    $jumlah_hadir = 0;
    $cutiDates = [];
    $jumlah_denda = 0;
    $jumlah_denda_absen = 0;

    $m_kary = m_kary::find($kary_id);
    if(!$m_kary) continue;

    $user = default_users::where('m_kary_id', $kary_id)->first();
    if(!$user) continue;

    $presensi = presensi_absensi::with(['t_jadwal_kerja_d_hari_n.m_jam_kerja', 'm_jam_kerja'])
        ->where('default_user_id', $user->id)
        ->whereBetween('tanggal', [$startStr, $endStr])
        ->get()
        ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

    $cuti = t_cuti::where('m_kary_id', $kary_id)
        ->where("status", "APPROVED")
        ->where(function ($q) use ($startStr, $endStr) {
            $q->whereBetween('date_from', [$startStr, $endStr])
              ->orWhereBetween('date_to', [$startStr, $endStr])
              ->orWhere(function ($q2) use ($startStr, $endStr) {
                  $q2->where('date_from', '<=', $startStr)
                     ->where('date_to', '>=', $endStr);
              });
        })->get();

    foreach ($cuti as $c) {
        $cStart = Carbon::parse($c->date_from)->startOfDay();
        $cEnd = Carbon::parse($c->date_to)->startOfDay();
        if ($cStart->lessThan($start)) $cStart = $start->copy();
        if ($cEnd->greaterThan($end)) $cEnd = $end->copy();
        if ($cStart->lte($cEnd)) {
            foreach (CarbonPeriod::create($cStart, $cEnd) as $tgl) {
                $cutiDates[$tgl->format('Y-m-d')] = $c->keterangan ?? 'CUTI';
            }
        }
    }

    // 1. Preload semua jadwal kerja karyawan (tanpa query LIKE pada kolom bigint)
    $jadwals = \DB::table('t_jadwal_kerja_d_n as d')
        ->leftJoin('t_jadwal_kerja_n as j', 'j.id', '=', 'd.t_jadwal_kerja_n_id')
        ->select('d.*', 'j.status as master_status')
        ->where('d.m_kary_id', (int)$m_kary->id)
        ->where(function ($q) use ($endStr) {
            $q->whereNull('d.start_date')
              ->orWhereDate('d.start_date', '<=', $endStr);
        })
        ->orderByRaw("CASE WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' AND UPPER(COALESCE(j.status, 'AKTIF')) = 'AKTIF' THEN 0 WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' THEN 1 ELSE 2 END ASC")
        ->orderByDesc('d.start_date')
        ->orderByDesc('d.id')
        ->get();

    if ($jadwals->isEmpty()) {
        $jadwals = \DB::table('t_jadwal_kerja_d_n as d')
            ->leftJoin('t_jadwal_kerja_n as j', 'j.id', '=', 'd.t_jadwal_kerja_n_id')
            ->select('d.*', 'j.status as master_status')
            ->where('d.m_kary_id', (int)$m_kary->id)
            ->orderByRaw("CASE WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' AND UPPER(COALESCE(j.status, 'AKTIF')) = 'AKTIF' THEN 0 WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' THEN 1 ELSE 2 END ASC")
            ->orderByDesc('d.id')
            ->get();
    }

    $masterJadwalIds = $jadwals->map(fn($j) => $j->t_jadwal_kerja_n_id ?? ($j->jadwal_kerja_id ?? null))
        ->filter()
        ->unique()
        ->values();

    if ($masterJadwalIds->isEmpty()) {
        $legacyDet = \DB::table('t_jadwal_kerja_det')
            ->where('m_kary_id', (int)$m_kary->id)
            ->orderByDesc('id')
            ->first();
        if ($legacyDet && !empty($legacyDet->t_jadwal_kerja_id)) {
            $masterJadwalIds = collect([(int)$legacyDet->t_jadwal_kerja_id]);
        }
    }

    // 2. Preload detail hari kerja (t_jadwal_kerja_d_hari_n + fallback t_jadwal_kerja_det_hari)
    $jadwalHarisMap = $masterJadwalIds->isNotEmpty()
        ? \DB::table('t_jadwal_kerja_d_hari_n')
            ->whereIn('t_jadwal_kerja_n_id', $masterJadwalIds)
            ->get()
            ->groupBy('t_jadwal_kerja_n_id')
        : collect();

    $legacyHarisMap = $masterJadwalIds->isNotEmpty()
        ? \DB::table('t_jadwal_kerja_det_hari')
            ->whereIn('t_jadwal_kerja_id', $masterJadwalIds)
            ->get()
            ->groupBy('t_jadwal_kerja_id')
        : collect();

    // 3. Preload lembur APPROVED & standar gaji di luar loop harian
    $lemburByDate = t_lembur::where('m_kary_id', $kary_id)
        ->whereBetween('tanggal', [$startStr, $endStr])
        ->where('status', 'APPROVED')
        ->get()
        ->groupBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

    $standartGajiList = m_standart_gaji::where("m_kary_id", $m_kary->id)
        ->where(function ($q) use ($endStr) {
            $q->whereNull('start_date')
              ->orWhereDate('start_date', '<=', $endStr);
        })
        ->orderByDesc("start_date")
        ->orderByDesc("id")
        ->get();

    foreach ($period as $tanggal) {
        $key = $tanggal->format('Y-m-d');
        $data = $presensi[$key] ?? null;

        $dayNumIso = (int)$tanggal->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
        $namaHariId = strtolower(trim($tanggal->translatedFormat('l')));
        $namaHariEn = strtolower(trim($tanggal->format('l')));

        $activeJadwal = $jadwals->first(function ($j) use ($key) {
            return empty($j->start_date) || Carbon::parse($j->start_date)->format('Y-m-d') <= $key;
        }) ?? $jadwals->first();

        $activeMasterId = $activeJadwal ? ($activeJadwal->t_jadwal_kerja_n_id ?? ($activeJadwal->jadwal_kerja_id ?? null)) : ($masterJadwalIds->first());
        $jadwalHariCollection = collect();
        if ($activeMasterId) {
            $jadwalHariCollection = $jadwalHarisMap->get($activeMasterId) ?? collect();
            if ($jadwalHariCollection->isEmpty()) {
                $jadwalHariCollection = $legacyHarisMap->get($activeMasterId) ?? collect();
            }
        }

        $jam_kerja_row = $data?->t_jadwal_kerja_d_hari_n;
        if (!$jam_kerja_row) {
            $jam_kerja_row = $jadwalHariCollection->first(function($row) use ($dayNumIso, $namaHariId, $namaHariEn, $dayMap) {
                $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
                $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
                $calcDayNum = $dayMap[$rDayLower] ?? 0;

                if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
                if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
                if ($rDayLower === $namaHariId || $rDayLower === $namaHariEn) return true;
                return false;
            });
        }

        $jadwalMulai = $data?->t_jadwal_kerja_d_hari_n?->waktu_mulai ?? ($jam_kerja_row?->waktu_mulai ?? ($data?->m_jam_kerja?->waktu_mulai ?? null));
        $jadwalAkhir = $data?->t_jadwal_kerja_d_hari_n?->waktu_akhir ?? ($jam_kerja_row?->waktu_akhir ?? ($data?->m_jam_kerja?->waktu_akhir ?? null));
        $hasValidTimes = !empty($jadwalMulai)
            && !empty($jadwalAkhir)
            && !($jadwalMulai === '00:00:00' && $jadwalAkhir === '00:00:00')
            && !($jadwalMulai === '00:00' && $jadwalAkhir === '00:00');

        // Tipe Hari
        $tipe_hari = 'KERJA';
        if ($jam_kerja_row) {
            $tipe_hari = strtoupper(trim((string)($jam_kerja_row->tipe_hari ?? 'KERJA')));
            if (empty($tipe_hari)) $tipe_hari = 'KERJA';
        } else {
            if ($jadwalHariCollection->isNotEmpty()) {
                $tipe_hari = 'LIBUR';
            } else {
                $tipe_hari = ($dayNumIso === 7) ? 'LIBUR' : 'KERJA';
            }
        }

        $isCuti = isset($cutiDates[$key]);
        $isLiburNasional = isset($liburDates[$key]);
        $isHariKerja = ($tipe_hari === 'KERJA') && !$isLiburNasional;

        if ($isLiburNasional) $tipe_hari = $liburDates[$key];

        $status = $data?->status ?? 'NOT ATTEND';
        if ($isCuti) $status = $cutiDates[$key];

        if (!$data && ($tipe_hari === 'LIBUR' || $isLiburNasional)) {
            $status = 'LIBUR';
        }

        // Hitung Telat (hanya hari KERJA, bukan LIBUR/Cuti/Libur Nasional)
        $telat = 0;
        $jamTelat = 0;
        if (
            $isHariKerja
            && !$isCuti
            && $data?->checkin_time
            && $jadwalMulai
            && $jadwalMulai !== '00:00:00'
            && $jadwalMulai !== '00:00'
        ) {
            $diff = Carbon::parse($key . ' ' . $jadwalMulai)->diffInMinutes(Carbon::parse($key . ' ' . $data->checkin_time), false);
            $telat = $diff > 0 ? (int)$diff : 0;
            if ($telat > 0) {
                $jumlah_telat++;
                $jamTelat = floor($telat / 60) + 1;
            }
        }

        // Hitung Lembur (akumulasi semua t_lembur APPROVED & support lintas hari)
        $lembur = 0;
        if (isset($lemburByDate[$key]) && $data?->checkout_time) {
            $allowOvertime = false;
            if (!$isHariKerja || !$hasValidTimes) {
                $allowOvertime = !empty($data->checkin_time);
            } else {
                $shiftStartDt = Carbon::parse($key . ' ' . $jadwalMulai);
                $shiftEndDt = Carbon::parse($key . ' ' . $jadwalAkhir);
                if ($shiftEndDt->lessThan($shiftStartDt)) {
                    $shiftEndDt->addDay();
                }

                $coDate = !empty($data->checkout_date) ? Carbon::parse($data->checkout_date)->format('Y-m-d') : $key;
                $coDt = Carbon::parse($coDate . ' ' . $data->checkout_time);
                if ($coDate === $key && !empty($data->checkin_time)) {
                    $ciDt = Carbon::parse($key . ' ' . $data->checkin_time);
                    if ($coDt->lessThan($ciDt)) {
                        $coDt->addDay();
                    }
                }
                if ($coDt->greaterThan($shiftEndDt)) {
                    $allowOvertime = true;
                }
            }

            if ($allowOvertime) {
                foreach ($lemburByDate[$key] as $t_lembur) {
                    $tglMulaiL = Carbon::parse($t_lembur->tanggal)->format('Y-m-d');
                    $tglSelesaiL = !empty($t_lembur->tanggal_selesai)
                        ? Carbon::parse($t_lembur->tanggal_selesai)->format('Y-m-d')
                        : $tglMulaiL;
                    $mulaiL = Carbon::parse($tglMulaiL . ' ' . $t_lembur->jam_mulai);
                    $selesaiL = Carbon::parse($tglSelesaiL . ' ' . $t_lembur->jam_selesai);
                    if ($tglSelesaiL === $tglMulaiL && $selesaiL->lessThan($mulaiL)) {
                        $selesaiL->addDay();
                    }
                    if ($selesaiL->greaterThan($mulaiL)) {
                        $lembur += $selesaiL->diffInMinutes($mulaiL);
                    }
                }
            }
        }
        if ($lembur > 0) $jumlah_lembur++;

        $standart_gaji = $standartGajiList->first(function ($sg) use ($key) {
            return empty($sg->start_date) || Carbon::parse($sg->start_date)->format('Y-m-d') <= $key;
        }) ?? $standartGajiList->first();

        $transport = $standart_gaji?->tunjangan_transport ?? 0;
        $produktifitas = $standart_gaji?->tunjangan_produktifitas ?? 0;
        $pokok = $standart_gaji?->gaji_pokok ?? 0;

        $denda = (($transport + $produktifitas) / 173) * $jamTelat;

        $jumlah_denda += $denda;
        $denda_format = "Rp " . number_format($denda, 0, ',', '.');

        $dendaAbsen = (($status === 'NOT ATTEND' || $status === 'WORKING') && $isHariKerja && !$isCuti) ? (($transport + $produktifitas + $pokok) / 173) * 7 : 0;
        $jumlah_denda_absen += $dendaAbsen;
        $dendaAbsen_format = "Rp " . number_format($dendaAbsen, 0, ',', '.');

        $checkoutDisplay = $data?->checkout_time ?? '-';
        if (!empty($data?->checkout_time) && !empty($data?->checkout_date)) {
            $coDateStr = Carbon::parse($data->checkout_date)->format('Y-m-d');
            if ($coDateStr !== $key) {
                $checkoutDisplay = $data->checkout_time . ' (' . $coDateStr . ')';
            }
        }

        if ($tipe_hari === 'LIBUR') {
            $jamKerjaDisplay = 'LIBUR';
        } elseif ($hasValidTimes) {
            $jamKerjaDisplay = "$jadwalMulai - $jadwalAkhir";
        } else {
            $jamKerjaDisplay = '-';
        }

        $hasil[] = [
            'tanggal'  => $key,
            'hari'     => strtoupper($tanggal->translatedFormat('l')),
            'tipe'     => $tipe_hari,
            'status'   => $status,
            'checkin'  => $data?->checkin_time ?? '-',
            'checkout' => $checkoutDisplay,
            'jam_kerja'=> $jamKerjaDisplay,
            'telat'    => $telat,
            'lembur'   => $lembur,
            'jamTelat' => $jamTelat,
            'denda'    => $denda_format,
            'denda_absen' => $dendaAbsen_format,
        ];
    }
@endphp

{{-- PAGE BREAK UNTUK PRINT --}}
<div style="{{ !$loop->first ? 'page-break-before: always;' : '' }}">
    <span style="font-weight:bold; font-size: 10pt"> Absensi Karyawan Detail</span><br/>
    <span style="font-weight:bold; font-size: 7pt"> {{ $m_kary->nik }} - {{ $m_kary->nama_lengkap }}</span><br/>
    <span style="font-weight:bold; font-size: 7pt"> Periode {{ $req->periode_awal }} s/d {{ $req->periode_akhir }}</span><br/><br/>

    <table style="width: 100%; font-size: 7pt; border-collapse: collapse;" cellpadding="2">
        <thead>
            <tr style="background-color: #c6c6c6;">
                <th style="border:0.5px solid black;">Tanggal</th>
                <th style="border:0.5px solid black;">Tipe Hari</th>
                <th style="border:0.5px solid black;">Status</th>
                <th style="border:0.5px solid black;">Checkin</th>
                <th style="border:0.5px solid black;">Checkout</th>
                <th style="border:0.5px solid black;">Jam Kerja</th>   
                <th style="border:0.5px solid black;">Terlambat (Min)</th>
                <th style="border:0.5px solid black;">Lembur (Min)</th>
                <th style="border:0.5px solid black;">Terlambat (Jam)</th>
                <th style="border:0.5px solid black;">Denda Terlambat</th>
                <th style="border:0.5px solid black;">Denda Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hasil as $dt)
                @php
                    if(in_array($dt['status'], ['ATTEND', 'WORKING'])) $jumlah_hadir++;
                    
                    $rowStyle = "";
                    if ($dt['status'] === 'NOT ATTEND' && $dt['tipe'] === 'KERJA') {
                        $rowStyle = "background-color: #f8d7da; color: #721c24;";
                    } elseif ($dt['tipe'] !== 'KERJA' || isset($cutiDates[$dt['tanggal']])) {
                        $rowStyle = "background-color: #d4edda; color: #155724;";
                    }elseif($dt['telat'] > 0){
                        $rowStyle = "background-color: #f8d7da; color: #721c24;";
                    }
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['hari'] }}, {{ $dt['tanggal'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['tipe'] }}</td>
                    <td style="border:0.5px solid black; text-align: center; font-weight: bold;">{{ $dt['status'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['checkin'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['checkout'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['jam_kerja'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['telat'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['lembur'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['jamTelat'] }} Jam</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['denda'] }}</td>
                    <td style="border:0.5px solid black; text-align: center;">{{ $dt['denda_absen'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <table style="width: 30%; font-size: 7pt;">
        <tr><td>Jumlah Hari Kerja</td><td>:</td><td>{{ collect($hasil)->where('tipe', 'KERJA')->count() }}</td></tr>
        <tr><td>Jumlah Hadir</td><td>:</td><td>{{ $jumlah_hadir }}</td></tr>
        <tr><td>Jumlah Telat</td><td>:</td><td>{{ $jumlah_telat }}</td></tr>
        <tr><td>Jumlah Lembur</td><td>:</td><td>{{ $jumlah_lembur }}</td></tr>
        <tr><td>Jumlah Cuti</td><td>:</td><td>{{ count($cutiDates) }}</td></tr>
        <tr><td>Denda Terlambat</td><td>:</td><td>{{ "Rp " . number_format($jumlah_denda, 0, ',', '.') }}</td></tr>
        <tr><td>Denda Tidak Hadir</td><td>:</td><td>{{ "Rp " . number_format($jumlah_denda_absen, 0, ',', '.') }}</td></tr>
    </table>
</div>
@endforeach