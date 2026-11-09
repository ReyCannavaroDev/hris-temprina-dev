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
  $start = Carbon::parse($req->periode_awal);
  $end   = Carbon::parse($req->periode_akhir);
  $period = CarbonPeriod::create($start, $end);

  // 🟢 BARIS BARU: Ambil list hari libur nasional (ditaruh di sini agar query-nya cepat)
  $liburNasional = m_libur_nasional::whereBetween('tanggal', [$start, $end])->get();
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
            $presensi = presensi_absensi::with('t_jadwal_kerja_d_hari_n')
            ->where('default_user_id', $user->id)
            ->whereBetween('tanggal', [$start, $end])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

            $cuti = t_cuti::where('m_kary_id', $dt->id)
            ->where("status", "APPROVED")
            ->where(fn($q) =>
            $q->whereBetween('date_from', [$start, $end])
            ->orWhereBetween('date_to', [$start, $end])
            )->get();

            $cutiDates = [];
            foreach ($cuti as $c) {
            foreach (CarbonPeriod::create($c->date_from, $c->date_to) as $tgl) {
            $cutiDates[$tgl->format('Y-m-d')] = $c->keterangan ?? 'CUTI';
            }
            }

            // ==============================================================
            // 🚀 OPTIMASI: PINDAHKAN QUERY DATABASE KE LUAR LOOPING HARIAN
            // ==============================================================

            // A. Query Jadwal Induk (cukup 1x per karyawan)
            $jadwal = t_jadwal_kerja_d_n::where(function($q) use ($dt) {
                    $q->where('m_kary_id', $dt->id)
                      ->orWhere('m_kary_id', (string)$dt->id)
                      ->orWhere('m_kary_id', 'like', '%"' . $dt->id . '"%')
                      ->orWhere('m_kary_id', 'like', '%[' . $dt->id . ']%');
                })
                ->where(function($q) use ($end) {
                    $q->whereNull('start_date')
                      ->orWhereDate('start_date', '<=', $end);
                })
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first();

            if (!$jadwal) {
                $jadwal = t_jadwal_kerja_d_n::where(function($q) use ($dt) {
                        $q->where('m_kary_id', $dt->id)
                          ->orWhere('m_kary_id', (string)$dt->id)
                          ->orWhere('m_kary_id', 'like', '%"' . $dt->id . '"%');
                    })
                    ->orderByDesc('id')
                    ->first();
            }

            $masterJadwalId = $jadwal?->t_jadwal_kerja_n_id ?? $jadwal?->jadwal_kerja_id ?? null;
            if (!$masterJadwalId) {
                $legacyDet = \DB::table('t_jadwal_kerja_det')
                    ->where('m_kary_id', $dt->id)
                    ->orderByDesc('id')
                    ->first();
                if ($legacyDet) {
                    $masterJadwalId = $legacyDet->t_jadwal_kerja_id ?? null;
                }
            }

            // B. Simpan Jadwal Harian ke dalam Collection memory
            $jadwal_hari_collection = collect();
            if ($masterJadwalId) {
                $jadwal_hari_collection = \DB::table('t_jadwal_kerja_d_hari_n')
                    ->where('t_jadwal_kerja_n_id', $masterJadwalId)
                    ->get();

                if ($jadwal_hari_collection->isEmpty()) {
                    $jadwal_hari_collection = \DB::table('t_jadwal_kerja_det_hari')
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

            // C. Query Standar Gaji (cukup 1x per karyawan)
            $standart_gaji = m_standart_gaji::where("m_kary_id", $dt->id)
            ->whereDate('start_date', '<=', $end)
            ->orderByDesc("start_date")
            ->first();

            $transport = $standart_gaji?->tunjangan_transport ?? 0;
            $produktifitas = $standart_gaji?->tunjangan_produktifitas ?? 0;
            $pokok = $standart_gaji?->gaji_pokok ?? 0;
            // ==============================================================

            // LOOPING HARIAN SEKARANG BEBAS DARI QUERY DATABASE
            foreach ($period as $tanggal) {
            $key = $tanggal->format('Y-m-d');
            $dayNumIso = (int)$tanggal->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
            $nama_hari_id = strtolower(trim($tanggal->translatedFormat('l')));
            $nama_hari_en = strtolower(trim($tanggal->format('l')));
            $data_presensi = $presensi[$key] ?? null;

            // Ambil jadwal harian dari Collection memory dengan fallback ISO day_num -> hari indo -> hari english
            $jam_kerja_row = $jadwal_hari_collection->first(function($row) use ($dayNumIso, $nama_hari_id, $nama_hari_en, $dayMap) {
                $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
                $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
                $calcDayNum = $dayMap[$rDayLower] ?? 0;

                if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
                if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
                if ($rDayLower === $nama_hari_id || $rDayLower === $nama_hari_en) return true;
                return false;
            });

            $jadwalMulai = $data_presensi?->t_jadwal_kerja_d_hari_n?->waktu_mulai ?? $jam_kerja_row?->waktu_mulai;

            $telat = 0;
            if ($data_presensi?->checkin_time && $jadwalMulai && $jadwalMulai !== '00:00:00' && $jadwalMulai !== '00:00') {
            $diff = Carbon::parse($jadwalMulai)->diffInMinutes(Carbon::parse($data_presensi->checkin_time), false);
            $telat = $diff > 0 ? $diff : 0;
            }

            $status = $data_presensi?->status ?? 'NOT ATTEND';
            if (isset($cutiDates[$key])) $status = $cutiDates[$key];

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

            if (isset($liburDates[$key])) $tipe_hari = $liburDates[$key];

            if (!$data_presensi && ($tipe_hari === 'LIBUR' || isset($liburDates[$key]))) {
                $status = 'LIBUR';
            }

            $jamTelat = 0;
            if($telat > 0) {
            $jamTelat = floor($telat / 60) + 1;
            }

            // Hitung Denda Telat
            $denda = (($transport + $produktifitas) / 173) * $jamTelat;
            $jumlah_denda_telat += $denda;

            // Hitung Denda Absen
            $dendaAbsen = (($status === 'NOT ATTEND' || $status === 'WORKING') && $tipe_hari === 'KERJA') ? (($transport + $produktifitas + $pokok) / 173) * 7 : 0;
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
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right; width: 3.5%;">{{ $absensi['hari_kerja'] - ($absensi['jumlah_hadir'] + $absensi['jumlah_cuti']) }}</td>

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
