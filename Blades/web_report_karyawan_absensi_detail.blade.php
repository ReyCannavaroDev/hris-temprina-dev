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

$start = Carbon::parse($req->periode_awal);
$end   = Carbon::parse($req->periode_akhir);
$period = CarbonPeriod::create($start, $end);

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

$liburNasional = m_libur_nasional::whereBetween('tanggal', [$start, $end])->get();
$liburDates = $liburNasional->pluck('keterangan', 'tanggal')->mapWithKeys(
    fn($keterangan, $tgl) => [Carbon::parse($tgl)->format('Y-m-d') => $keterangan ?? 'LIBUR']
);
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

    $presensi = presensi_absensi::with('t_jadwal_kerja_d_hari_n')
        ->where('default_user_id', $user->id)
        ->whereBetween('tanggal', [$start, $end])
        ->get()
        ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

    $cuti = t_cuti::where('m_kary_id', $kary_id)
        ->where("status", "APPROVED")
        ->where(fn($q) => 
            $q->whereBetween('date_from', [$start, $end])
              ->orWhereBetween('date_to', [$start, $end])
        )->get();

    foreach ($cuti as $c) {
        foreach (CarbonPeriod::create($c->date_from, $c->date_to) as $tgl) {
            $cutiDates[$tgl->format('Y-m-d')] = $c->keterangan ?? 'CUTI';
        }
    }

    // 1. Ambil jadwal karyawan dari t_jadwal_kerja_d_n
    $jadwal = t_jadwal_kerja_d_n::where(function($q) use ($m_kary) {
            $q->where('m_kary_id', $m_kary->id)
              ->orWhere('m_kary_id', (string)$m_kary->id)
              ->orWhere('m_kary_id', 'like', '%"' . $m_kary->id . '"%')
              ->orWhere('m_kary_id', 'like', '%[' . $m_kary->id . ']%');
        })
        ->where(function($q) use ($end) {
            $q->whereNull('start_date')
              ->orWhereDate('start_date', '<=', $end);
        })
        ->orderByDesc('start_date')
        ->orderByDesc('id')
        ->first();

    if (!$jadwal) {
        $jadwal = t_jadwal_kerja_d_n::where(function($q) use ($m_kary) {
                $q->where('m_kary_id', $m_kary->id)
                  ->orWhere('m_kary_id', (string)$m_kary->id)
                  ->orWhere('m_kary_id', 'like', '%"' . $m_kary->id . '"%');
            })
            ->orderByDesc('id')
            ->first();
    }

    $masterJadwalId = $jadwal?->t_jadwal_kerja_n_id ?? $jadwal?->jadwal_kerja_id ?? null;
    if (!$masterJadwalId) {
        $legacyDet = \DB::table('t_jadwal_kerja_det')
            ->where('m_kary_id', $m_kary->id)
            ->orderByDesc('id')
            ->first();
        if ($legacyDet) {
            $masterJadwalId = $legacyDet->t_jadwal_kerja_id ?? null;
        }
    }

    // 2. Ambil list hari detail (cek t_jadwal_kerja_d_hari_n, fallback ke t_jadwal_kerja_det_hari)
    $jadwalHariCollection = collect();
    if ($masterJadwalId) {
        $jadwalHariCollection = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $masterJadwalId)
            ->get();

        if ($jadwalHariCollection->isEmpty()) {
            $jadwalHariCollection = \DB::table('t_jadwal_kerja_det_hari')
                ->where(function($q) use ($masterJadwalId) {
                    $q->where('t_jadwal_kerja_id', $masterJadwalId)
                      ->orWhere('t_jadwal_kerja_n_id', $masterJadwalId);
                })
                ->get();
        }
    }

    $dayMap = [
        'senin' => 1, 'monday' => 1,
        'selasa' => 2, 'tuesday' => 2,
        'rabu' => 3, 'wednesday' => 3,
        'kamis' => 4, 'thursday' => 4,
        'jumat' => 5, 'jum\'at' => 5, 'friday' => 5,
        'sabtu' => 6, 'saturday' => 6,
        'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
    ];

    foreach ($period as $tanggal) {
        $key = $tanggal->format('Y-m-d');
        $data = $presensi[$key] ?? null;

        $dayNumIso = (int)$tanggal->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
        $namaHariId = strtolower(trim($tanggal->translatedFormat('l')));
        $namaHariEn = strtolower(trim($tanggal->format('l')));

        $jam_kerja_row = $jadwalHariCollection->first(function($row) use ($dayNumIso, $namaHariId, $namaHariEn, $dayMap) {
            $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
            $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
            $calcDayNum = $dayMap[$rDayLower] ?? 0;

            if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
            if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
            if ($rDayLower === $namaHariId || $rDayLower === $namaHariEn) return true;
            return false;
        });

        $jadwalMulai = $data?->t_jadwal_kerja_d_hari_n?->waktu_mulai ?? $jam_kerja_row?->waktu_mulai;
        $jadwalAkhir = $data?->t_jadwal_kerja_d_hari_n?->waktu_akhir ?? $jam_kerja_row?->waktu_akhir;

        // Hitung Telat
        $telat = 0;
        if ($data?->checkin_time && $jadwalMulai && $jadwalMulai !== '00:00:00' && $jadwalMulai !== '00:00') {
            $diff = Carbon::parse($jadwalMulai)->diffInMinutes(Carbon::parse($data->checkin_time), false);
            $telat = $diff > 0 ? $diff : 0;
        }
        if($telat > 0) $jumlah_telat++;

        // Hitung Lembur
        $lembur = 0;
        if ($data?->checkout_time && $jadwalAkhir && $jadwalAkhir !== '00:00:00' && $jadwalAkhir !== '00:00') {
            if(Carbon::parse($data->checkout_time)->greaterThan(Carbon::parse($jadwalAkhir))){
                $t_lembur = t_lembur::where('m_kary_id', $kary_id)->where('tanggal', $key)->where("status", "APPROVED")->first();
                if($t_lembur) $lembur = Carbon::parse($t_lembur->jam_selesai)->diffInMinutes($t_lembur->jam_mulai);
            }
        }
        if($lembur > 0) $jumlah_lembur++;

        $status = $data?->status ?? 'NOT ATTEND';
        if (isset($cutiDates[$key])) $status = $cutiDates[$key];

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

        if (isset($liburDates[$key])) $tipe_hari = $liburDates[$key];

        if (!$data && ($tipe_hari === 'LIBUR' || isset($liburDates[$key]))) {
            $status = 'LIBUR';
        }

        $jamTelat = 0;
        if($telat > $data?->checkout_time){
          $diff = Carbon::parse($jadwalMulai)->diffInMinutes(Carbon::parse($data->checkin_time), false);
          
          if ($diff > 0) {

          $jamTelat = floor($diff / 60) + 1;

          }
        }

        
        //$standart_gaji = m_standart_gaji::where('m_kary_id', $m_kary->id)->first();
        
        //logic baru jika data standar gaji sudah diisi semua start datenya @lek adi
        $standart_gaji = m_standart_gaji::where("m_kary_id", $m_kary->id)
            ->whereDate('start_date', '<=', $key)
            ->orderByDesc("start_date")
            ->first();

        $transport = $standart_gaji?->tunjangan_transport ?? 0;
        $produktifitas = $standart_gaji?->tunjangan_produktifitas ?? 0;
        $pokok = $standart_gaji?->gaji_pokok ?? 0;

        $denda = (($transport + $produktifitas) / 173) * $jamTelat;

        $jumlah_denda += $denda;
        $denda_format = "Rp " . number_format($denda, 0, ',', '.');

        $dendaAbsen = (($status === 'NOT ATTEND' || $status === 'WORKING') && $tipe_hari === 'KERJA') ? (($transport + $produktifitas + $pokok) / 173) * 7 : 0;
        $jumlah_denda_absen += $dendaAbsen;
        $dendaAbsen_format = "Rp " . number_format($dendaAbsen, 0, ',', '.');

        // $standart_gaji["tunjangan_transport"] + @$standart_gaji["tunjangan_produktifitas"]) / 173) *
        // $total_terlambat;

        $hasil[] = [
            'tanggal'  => $key,
            'hari'     => strtoupper($tanggal->translatedFormat('l')),
            'tipe'     => $tipe_hari,
            'status'   => $status,
            'checkin'  => $data?->checkin_time ?? '-',
            'checkout' => $data?->checkout_time ?? '-',
            'jam_kerja'=> ($jadwalMulai && $jadwalAkhir) ? "$jadwalMulai - $jadwalAkhir" : "-",
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