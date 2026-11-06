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

  $rekap = [];
  //if(!$req->kary_id ){
  //  $data = m_kary::where('m_branch_id', $req->m_branch_id)
  //  ->where('m_divisi_id', $req->m_divisi_id)
  //  ->with(['m_branch', 'm_divisi.name'])
  //  ->get();
  //}else{
  //  $data = m_kary::where('id', $req->kary_id)->with(['m_branch', 'm_divisi.name'])->get();
  //}

  // Pastikan semua input menjadi array
  $kary_ids = (array) ($req->kary_id ?? []);
  $m_comp_ids = (array) ($req->m_comp_id ?? []);
  $m_subcomp_ids = (array) ($req->m_subcomp_id ?? []);
  $m_divisi_ids = (array) ($req->m_divisi_id ?? []);
  $m_branch_ids = (array) ($req->m_branch_id ?? []);

  $data = m_kary::query()
      ->with(['m_branch', 'm_divisi.name'])
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
            $jadwal = t_jadwal_kerja_d_n::where('m_kary_id', $dt->id)
            ->whereDate('start_date', '<=', $end) // Cek batas akhir periode
            ->orderByDesc('start_date')
            ->first();

            // B. Simpan Jadwal Harian ke dalam Collection memory
            $jadwal_hari_collection = collect();
            if($jadwal) {
            $jadwal_hari_collection = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
            ->get()
            ->keyBy('day'); // Jadikan nama hari sebagai key
            }

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
            $nama_hari = $tanggal->translatedFormat('l'); // 'Monday', 'Tuesday', dll
            $data_presensi = $presensi[$key] ?? null;

            // Ambil jadwal harian dari Collection memory, bukan database
            $jam_kerja_row = $jadwal_hari_collection->get($nama_hari);

            $jadwalMulai = $data_presensi?->t_jadwal_kerja_d_hari_n?->waktu_mulai ?? $jam_kerja_row?->waktu_mulai;

            $telat = 0;
            if ($data_presensi?->checkin_time && $jadwalMulai) {
            $diff = Carbon::parse($jadwalMulai)->diffInMinutes(Carbon::parse($data_presensi->checkin_time), false);
            $telat = $diff > 0 ? $diff : 0;
            }

            $status = $data_presensi?->status ?? 'NOT ATTEND';
            if (isset($cutiDates[$key])) $status = $cutiDates[$key];

            $tipe_hari = $jam_kerja_row?->tipe_hari ?? 'LIBUR';
            if (isset($liburDates[$key])) $tipe_hari = $liburDates[$key];

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
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 20%;">{{ $dt->m_divisi?->name?->value ?? '-' }}</td>
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
