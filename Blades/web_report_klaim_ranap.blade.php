@php
  $req = app()->request;
  $filter_type = $req->filter_type ?? 'transaksi';
  
  $query = \DB::table('t_klaim_ranap as tk')
      ->join('m_kary as k', 'k.id', '=', 'tk.m_kary_id')
      ->leftJoin('t_klaim_ranap_d as tkd', 'tkd.t_klaim_ranap_id', '=', 'tk.id')
      ->where('tk.status', '!=', 'DRAFT')
      ->select(
          'tk.nomor as no_klaim',
          'tk.status',
          'k.nik',
          'k.nama_lengkap',
          'tkd.tanggal as tanggal_kuitansi',
          'tkd.klaim_nama',
          'tkd.nominal as nominal_pengajuan',
          'tkd.accepted as nominal_disetujui',
          'tkd.reject as nominal_ditolak',
          'tkd.keterangan'
      );

  $subTitle = 'Laporan Klaim Rawat Inap';

  // Apply filters
  if ($filter_type === 'transaksi') {
      $subTitle = 'Klaim Rawat Inap Per Transaksi';
      if ($req->m_kary_id) {
          $query->where('tk.m_kary_id', $req->m_kary_id);
          $karyawan = \DB::table('m_kary')->where('id', $req->m_kary_id)->first();
          if ($karyawan) {
              $subTitle .= ' - ' . $karyawan->nama_lengkap . ' (' . $karyawan->nik . ')';
          }
      }
      if ($req->nomor) {
          $query->where('tk.nomor', 'like', '%' . $req->nomor . '%');
          $subTitle .= ' - No. Transaksi: ' . $req->nomor;
      }
  } elseif ($filter_type === 'periode') {
      if ($req->periode_awal && $req->periode_akhir) {
          $startDate = null;
          $endDate = null;
 
          if (strpos($req->periode_awal, '/') !== false) {
              $startObj = \DateTime::createFromFormat("d/m/Y", trim($req->periode_awal));
              if ($startObj) $startDate = $startObj->format('Y-m-d');
          } else {
              $startDate = trim($req->periode_awal);
          }

          if (strpos($req->periode_akhir, '/') !== false) {
              $endObj = \DateTime::createFromFormat("d/m/Y", trim($req->periode_akhir));
              if ($endObj) $endDate = $endObj->format('Y-m-d');
          } else {
              $endDate = trim($req->periode_akhir);
          }

          if ($startDate && $endDate) {
              // Filter by hospitalization period overlap
              $query->where('tk.periode_awal', '<=', $endDate)
                    ->where('tk.periode_akhir', '>=', $startDate);
                    
              $subTitle = 'Klaim Rawat Inap Periode: ' . date('d-m-Y', strtotime($startDate)) . ' s/d ' . date('d-m-Y', strtotime($endDate));
          }
      } else {
          $subTitle = 'Klaim Rawat Inap Periode';
      }
  }

  $raw = $query->orderBy('tk.id')->orderBy('tkd.id')->get();

  // Calculate totals
  $totalPengajuan = 0;
  $totalDisetujui = 0;
  $totalDitolak = 0;
  foreach ($raw as $d) {
      $totalPengajuan += (float)($d->nominal_pengajuan ?? 0);
      $totalDisetujui += (float)($d->nominal_disetujui ?? 0);
      $totalDitolak += (float)($d->nominal_ditolak ?? 0);
  }

  // Indonesian Date formatting for signature block
  $indonesianMonths = [
      'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
      'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
      'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
      'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
  ];
  $englishMonth = date('F');
  $indonesianMonth = $indonesianMonths[$englishMonth] ?? $englishMonth;
  $dateNowFormatted = date('d') . ' ' . $indonesianMonth . ' ' . date('Y');
@endphp

<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #2d3748; margin: 10px;">
  <!-- KOP SURAT / HEADER DOKUMEN -->
  <table width="100%" style="border-collapse: collapse; border-bottom: 2px solid #2d3748; padding-bottom: 10px; margin-bottom: 20px;">
    <tr>
      <td style="text-align: left; vertical-align: middle;">
        <span style="font-size: 15pt; font-weight: bold; letter-spacing: 0.5px; color: #1a365d;">TEMPRINA MEDIA GRAFIKA</span><br/>
        <span style="font-size: 8pt; color: #718096; line-height: 14px;">Jl. Raya Sumengko Km 30, Wringinanom, Gresik<br/>Telp: (031) 8985222 | Email: info@temprina.co.id</span>
      </td>
      <td style="text-align: right; vertical-align: middle; line-height: 18px;">
        <span style="font-size: 12pt; font-weight: bold; color: #2d3748; text-transform: uppercase;">Laporan Klaim Rawat Inap</span><br/>
        <span style="font-size: 9pt; color: #4a5568; font-weight: 500;">{{ $subTitle }}</span>
      </td>
    </tr>
  </table>

  <!-- TABEL LAPORAN -->
  <table width="100%" style="font-size: 10px; border-collapse: collapse; border: 1px solid #cbd5e0; margin-bottom: 20px; table-layout: fixed;" cellpadding="6">
    <thead>
      <tr style="background-color: #edf2f7; border-bottom: 2px solid #cbd5e0; color: #2d3748;">
        <th width="35" style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">No</th>
        <th width="130" style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">No. Klaim</th>
        <th width="80" style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">NIK</th>
        <th width="150" style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Nama Karyawan</th>
        <th width="85" style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">Tgl Kuitansi</th>
        <th width="130" style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Pasien / Klaim</th>
        <th width="130" style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold;">Nominal</th>
        <th width="280" style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Keterangan</th>
        <th width="80" style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">Status</th>
      </tr>
    </thead>
    <tbody>
      @if(count($raw) === 0)
        <tr>
          <td colspan="9" style="border: 1px solid #cbd5e0; text-align: center; padding: 20px; font-style: italic; color: #718096; background-color: #ffffff;">
            Tidak ditemukan data klaim rawat inap yang cocok dengan filter.
          </td>
        </tr>
      @else
        @foreach($raw as $i => $d)
          @php
              $backgroundColor = $i % 2 === 1 ? '#f7fafc' : '#ffffff'; 
              $status = strtoupper($d->status ?? '');
              if ($status === 'APPROVED') {
                  $statusColor = '#22543d'; // Green
              } elseif ($status === 'REJECTED') {
                  $statusColor = '#742a2a'; // Red
              } elseif ($status === 'REVISED') {
                  $statusColor = '#7b341e'; // Orange
              } else {
                  $statusColor = '#4a5568'; // Gray
              }
          @endphp
          <tr style="background-color: {{$backgroundColor}}; color: #2d3748; border-bottom: 1px solid #e2e8f0;">
            <td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px;">{{ $i+1 }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px; font-weight: 500;">{{ $d->no_klaim ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px;">{{ $d->nik ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->nama_lengkap ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px;">{{ $d->tanggal_kuitansi ? date('d-m-Y', strtotime($d->tanggal_kuitansi)) : '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->klaim_nama ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: right; padding: 5px; font-weight: 500;">{{ $d->nominal_pengajuan ? 'Rp ' . number_format((float)$d->nominal_pengajuan, 0, ',', '.') : 'Rp 0' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->keterangan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px; font-weight: bold; color: {{ $statusColor }}">{{ $status }}</td>
          </tr>
        @endforeach
      @endif
    </tbody>
    @if(count($raw) > 0)
      <tfoot>
        <tr style="background-color: #edf2f7; font-weight: bold; border-top: 2px solid #cbd5e0; border-bottom: 2px solid #cbd5e0; color: #2d3748;">
          <td colspan="6" style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold; padding: 6px;">TOTAL :</td>
          <td style="border: 1px solid #cbd5e0; text-align: right; padding: 6px; font-weight: bold;">Rp {{ number_format($totalPengajuan, 0, ',', '.') }}</td>
          <td colspan="2" style="border: 1px solid #cbd5e0; background-color: #edf2f7;"></td>
        </tr>
      </tfoot>
    @endif
  </table>
</div>

