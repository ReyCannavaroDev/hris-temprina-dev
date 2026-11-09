@php
  use App\Models\CustomModels\m_kary;
  use App\Models\CustomModels\t_jadwal_kerja_det_hari;
  use App\Models\CustomModels\t_jadwal_kerja;
  use App\Models\CustomModels\t_cuti;

  use App\Models\CustomModels\default_users;
  use App\Models\CustomModels\presensi_absensi;
  use App\Models\CustomModels\t_jadwal_kerja_d_n;
  use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
  use App\Models\CustomModels\m_libur_nasional;
  use App\Models\CustomModels\m_standart_gaji;
  use Carbon\CarbonPeriod;

  use Illuminate\Support\Facades\DB;
  use Carbon\Carbon;
  $req = app()->request;
  $tipe = $req->tipe_report;

  Carbon::setLocale('id');
  $start = Carbon::parse($req->periode_awal)->startOfDay();
  $end   = Carbon::parse($req->periode_akhir)->startOfDay();
  $period = CarbonPeriod::create($start, $end);
  $startStr = $start->format('Y-m-d');
  $endStr   = $end->format('Y-m-d');

  // 🟢 BARIS BARU: Ambil list hari libur nasional (ditaruh di sini agar query-nya cepat)
  $liburNasional = m_libur_nasional::whereBetween('tanggal', [$startStr, $endStr])->get();
  $liburDates = $liburNasional->pluck('keterangan', 'tanggal')->mapWithKeys(
  fn($keterangan, $tgl) => [Carbon::parse($tgl)->format('Y-m-d') => $keterangan ?? 'LIBUR']
  );

  // Cache nama divisi dari m_general / name_old agar query cepat dan relasi aman
  $allDivisi = \DB::table('m_divisi')->get();
  $generalIds = $allDivisi->pluck('name')->filter(fn($v) => !empty($v) && is_numeric($v))->unique()->toArray();
  $generalMap = !empty($generalIds) ? \DB::table('m_general')->whereIn('id', $generalIds)->pluck('value', 'id')->toArray() : [];

  $divisiNameMap = [];
  foreach ($allDivisi as $div) {
      $divisiNameMap[$div->id] = $generalMap[$div->name] ?? $div->name_old ?? $div->name ?? '-';
  }

  $rekap = [];

  // Pastikan semua input menjadi array dan bersihkan nilai kosong/null
  $kary_ids = array_filter((array) ($req->kary_id ?? []));
  $m_comp_ids = array_filter((array) ($req->m_comp_id ?? []));
  $m_subcomp_ids = array_filter((array) ($req->m_subcomp_id ?? []));
  $m_divisi_ids = array_filter((array) ($req->m_divisi_id ?? []));
  $m_branch_ids = array_filter((array) ($req->m_branch_id ?? []));

  $data = m_kary::query()
      ->with(['m_branch', 'm_divisi'])
      ->where('is_active', true)
      // Filter List Karyawan
      ->when(!empty($kary_ids), function ($q) use ($kary_ids) {
          $q->whereIn('id', $kary_ids);
      })
      // Filter Company (via Sub-Company) berdasarkan diagram
      ->when(!empty($m_comp_ids), function ($q) use ($m_comp_ids) {
          $q->whereHas('m_subcomp', function($q2) use ($m_comp_ids) {
              $q2->whereIn('m_comp_id', $m_comp_ids);
          });
      })
      // Filter Sub-Company
      ->when(!empty($m_subcomp_ids), function ($q) use ($m_subcomp_ids) {
          $q->whereIn('m_subcomp_id', $m_subcomp_ids);
      })
      // Filter Branch
      ->when(!empty($m_branch_ids), function ($q) use ($m_branch_ids) {
          $q->whereIn('m_branch_id', $m_branch_ids);
      })
      // Filter Divisi
      ->when(!empty($m_divisi_ids), function ($q) use ($m_divisi_ids) {
          $q->whereIn('m_divisi_id', $m_divisi_ids);
      })
      ->orderBy('nama_lengkap', 'asc')
      ->get();
    
@endphp
<span style="font-weight:bold; font-size: 10pt">{{$tipe}}</span><br/>
<span style="font-weight:bold; font-size: 9pt"> Periode  {{$req->periode}}</span><br/></br>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> HK : Hari Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> H : Masuk Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> I : Ijin</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> A : Tidak Masuk Kerja / Tanpa Keterangan</span><br/>
<!-- <span style="font-weight:bold; font-style: italic; font-size: 8pt"> CI AVG : Rata-rata Jam Checkin</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CO AVG : Rata-rata Jam Checkout</span><br/> -->
<br/>
<table v-else class="table-auto w-full" cellpadding="3">
  <thead class="bg-[#c6c6c6]">
    <tr>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 25%;">Karyawan</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 20%;">Cabang</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 20%;">Divisi</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">HK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">H</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">I</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">A</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">Denda Telat</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 3.5%;">Denda Absen</th>
      <!-- <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CI AVG</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CO AVG</th> -->
    </tr>
  </thead>
  <tbody>
    @foreach($data as $dt)
        @php
          $absensi = $dt->hitungRekap($dt->id, $req->periode_awal, $req->periode_akhir);
          $jumlah_denda_telat = 0;
          $jumlah_denda_absen = 0;

          $user = default_users::where('m_kary_id', $dt->id)->first();

          if($user) {
            // 1. Ambil Data Presensi & Cuti (Tetap di luar loop harian)
            $presensi = presensi_absensi::with(['t_jadwal_kerja_d_hari_n.m_jam_kerja', 'm_jam_kerja'])
            ->where('default_user_id', $user->id)
            ->whereBetween('tanggal', [$startStr, $endStr])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

            $cuti = t_cuti::where('m_kary_id', $dt->id)
            ->where("status", "APPROVED")
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('date_from', [$startStr, $endStr])
                  ->orWhereBetween('date_to', [$startStr, $endStr])
                  ->orWhere(function ($q2) use ($startStr, $endStr) {
                      $q2->where('date_from', '<=', $startStr)
                         ->where('date_to', '>=', $endStr);
                  });
            })->get();

            $cutiDates = [];
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

            // ==============================================================
            // 🚀 OPTIMASI: PINDAHKAN QUERY DATABASE KE LUAR LOOPING HARIAN
            // ==============================================================

            // A. Query Semua Jadwal Karyawan (tanpa LIKE pada bigint m_kary_id)
            $jadwals = \DB::table('t_jadwal_kerja_d_n as d')
                ->leftJoin('t_jadwal_kerja_n as j', 'j.id', '=', 'd.t_jadwal_kerja_n_id')
                ->select('d.*', 'j.status as master_status')
                ->where('d.m_kary_id', (int)$dt->id)
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
                    ->where('d.m_kary_id', (int)$dt->id)
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
                    ->where('m_kary_id', (int)$dt->id)
                    ->orderByDesc('id')
                    ->first();
                if ($legacyDet && !empty($legacyDet->t_jadwal_kerja_id)) {
                    $masterJadwalIds = collect([(int)$legacyDet->t_jadwal_kerja_id]);
                }
            }

            // B. Simpan Jadwal Harian ke dalam Collection memory
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

            $dayMap = [
                'senin' => 1, 'monday' => 1,
                'selasa' => 2, 'tuesday' => 2,
                'rabu' => 3, 'wednesday' => 3,
                'kamis' => 4, 'thursday' => 4,
                'jumat' => 5, 'jum\'at' => 5, 'friday' => 5,
                'sabtu' => 6, 'saturday' => 6,
                'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
            ];

            // C. Query Standar Gaji (preload sekali per karyawan, pilih per tanggal di loop)
            $standartGajiList = m_standart_gaji::where("m_kary_id", $dt->id)
                ->where(function ($q) use ($endStr) {
                    $q->whereNull('start_date')
                      ->orWhereDate('start_date', '<=', $endStr);
                })
                ->orderByDesc("start_date")
                ->orderByDesc("id")
                ->get();
            // ==============================================================

            // LOOPING HARIAN SEKARANG BEBAS DARI QUERY DATABASE
            foreach ($period as $tanggal) {
            $key = $tanggal->format('Y-m-d');
            $dayNumIso = (int)$tanggal->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
            $nama_hari_id = strtolower(trim($tanggal->translatedFormat('l')));
            $nama_hari_en = strtolower(trim($tanggal->format('l')));
            $data_presensi = $presensi[$key] ?? null;

            $activeJadwal = $jadwals->first(function ($j) use ($key) {
                return empty($j->start_date) || Carbon::parse($j->start_date)->format('Y-m-d') <= $key;
            }) ?? $jadwals->first();

            $activeMasterId = $activeJadwal ? ($activeJadwal->t_jadwal_kerja_n_id ?? ($activeJadwal->jadwal_kerja_id ?? null)) : ($masterJadwalIds->first());
            $jadwal_hari_collection = collect();
            if ($activeMasterId) {
                $jadwal_hari_collection = $jadwalHarisMap->get($activeMasterId) ?? collect();
                if ($jadwal_hari_collection->isEmpty()) {
                    $jadwal_hari_collection = $legacyHarisMap->get($activeMasterId) ?? collect();
                }
            }

            // Prioritas snapshot presensi, fallback ke jadwal aktif
            $jam_kerja_row = $data_presensi?->t_jadwal_kerja_d_hari_n;
            if (!$jam_kerja_row) {
                $jam_kerja_row = $jadwal_hari_collection->first(function($row) use ($dayNumIso, $nama_hari_id, $nama_hari_en, $dayMap) {
                    $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
                    $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
                    $calcDayNum = $dayMap[$rDayLower] ?? 0;

                    if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
                    if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
                    if ($rDayLower === $nama_hari_id || $rDayLower === $nama_hari_en) return true;
                    return false;
                });
            }

            $jadwalMulai = $data_presensi?->t_jadwal_kerja_d_hari_n?->waktu_mulai ?? ($jam_kerja_row?->waktu_mulai ?? ($data_presensi?->m_jam_kerja?->waktu_mulai ?? null));

            $tipe_hari = 'KERJA';
            if ($jam_kerja_row) {
                $tipe_hari = strtoupper(trim((string)($jam_kerja_row->tipe_hari ?? 'KERJA')));
                if (empty($tipe_hari)) $tipe_hari = 'KERJA';
            } else {
                if ($jadwal_hari_collection->isNotEmpty()) {
                    $tipe_hari = 'LIBUR';
                } else {
                    $tipe_hari = ($dayNumIso === 7) ? 'LIBUR' : 'KERJA';
                }
            }

            $isCuti = isset($cutiDates[$key]);
            $isLiburNasional = isset($liburDates[$key]);
            $isHariKerja = ($tipe_hari === 'KERJA') && !$isLiburNasional;

            if ($isLiburNasional) $tipe_hari = $liburDates[$key];

            $status = $data_presensi?->status ?? 'NOT ATTEND';
            if ($isCuti) $status = $cutiDates[$key];

            if (!$data_presensi && ($tipe_hari === 'LIBUR' || $isLiburNasional)) {
                $status = 'LIBUR';
            }

            $telat = 0;
            $jamTelat = 0;
            if (
                $isHariKerja
                && !$isCuti
                && $data_presensi?->checkin_time
                && $jadwalMulai
                && $jadwalMulai !== '00:00:00'
                && $jadwalMulai !== '00:00'
            ) {
                $diff = Carbon::parse($key . ' ' . $jadwalMulai)->diffInMinutes(Carbon::parse($key . ' ' . $data_presensi->checkin_time), false);
                $telat = $diff > 0 ? (int)$diff : 0;
                if ($telat > 0) {
                    $jamTelat = floor($telat / 60) + 1;
                }
            }

            $standart_gaji = $standartGajiList->first(function ($sg) use ($key) {
                return empty($sg->start_date) || Carbon::parse($sg->start_date)->format('Y-m-d') <= $key;
            }) ?? $standartGajiList->first();

            $transport = $standart_gaji?->tunjangan_transport ?? 0;
            $produktifitas = $standart_gaji?->tunjangan_produktifitas ?? 0;
            $pokok = $standart_gaji?->gaji_pokok ?? 0;

            // Hitung Denda Telat
            $denda = (($transport + $produktifitas) / 173) * $jamTelat;
            $jumlah_denda_telat += $denda;

            // Hitung Denda Absen
            $dendaAbsen = (($status === 'NOT ATTEND' || $status === 'WORKING') && $isHariKerja && !$isCuti) ? (($transport + $produktifitas + $pokok) / 173) * 7 : 0;
            $jumlah_denda_absen += $dendaAbsen;
            }
          }

        @endphp
        <tr>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->kode }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 25%;">{{ $dt->nama_lengkap }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 20%;">{{ $dt->m_branch?->name ?? '-' }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 20%;">{{ $divisiNameMap[$dt->m_divisi_id] ?? ($dt->m_divisi?->name_old ?? '-') }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; width: 3.5%">{{ $absensi['hari_kerja'] }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; width: 3.5%;">{{ $absensi['jumlah_hadir'] }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; width: 3.5%;">{{ $absensi['jumlah_cuti'] }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; width: 3.5%;">{{ max(0, $absensi['hari_kerja'] - ($absensi['jumlah_hadir'] + $absensi['jumlah_cuti'])) }}</td>

          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; font-weight: bold;">
            {{ "Rp " . number_format($jumlah_denda_telat, 0, ',', '.') }}
          </td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; font-weight: bold;">
            {{ "Rp " . number_format($jumlah_denda_absen, 0, ',', '.') }}
          </td>
          <!-- <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right;">{{ $dt->checkin_avg }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right;">{{ $dt->checkout_avg }}</td> -->
        </tr>
    @endforeach
  </tbody>
</table>
