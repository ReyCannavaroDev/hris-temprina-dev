@php
	$req = app()->request;
	$filter_type = $req->filter_type ?? 'transaksi';
	$isExcel = ($req->export === 'xls' || $req->export === 'xlsx');

	$hasSantunanId = \Schema::hasColumn('t_klaim_askes_d', 'santunan_id');

	$query = \DB::table('t_klaim_askes as tk')
	->join('m_kary as k', 'k.id', '=', 'tk.m_kary_id')
	->leftJoin('t_klaim_askes_d as tkd', 'tkd.t_klaim_askes_id', '=', 'tk.id')
	->where('tk.status', '!=', 'DRAFT');

	if ($hasSantunanId) {
	$query->leftJoin('m_general as mg', 'mg.id', '=', 'tkd.santunan_id')
	->select(
	'tk.nomor as no_klaim',
	'tk.status',
	'k.nik',
	'k.nama_lengkap',
	'tkd.tanggal as tanggal_kuitansi',
	'tk.created_at as tanggal_buat',
	'tkd.klaim_nama',
	'mg.value as jenis_santunan',
	'tkd.nominal as nominal_pengajuan',
	'tkd.accepted as nominal_disetujui',
	'tkd.reject as nominal_ditolak',
	'tkd.keterangan'
	);
	} else {
	$query->select(
	'tk.nomor as no_klaim',
	'tk.status',
	'k.nik',
	'k.nama_lengkap',
	'tkd.tanggal as tanggal_kuitansi',
	'tk.created_at as tanggal_buat',
	'tkd.klaim_nama',
	'tkd.santunan as jenis_santunan',
	'tkd.nominal as nominal_pengajuan',
	'tkd.accepted as nominal_disetujui',
	'tkd.reject as nominal_ditolak',
	'tkd.keterangan'
	);
	}

	$subTitle = 'Laporan Klaim Askes';

	// Parse Periode Awal & Akhir request dates securely to DB Y-m-d format
	$p_awal = null;
	$p_akhir = null;

	if ($req->periode_awal) {
	$val = trim($req->periode_awal);
	if (strpos($val, '/') !== false) {
	$dObj = \DateTime::createFromFormat("d/m/Y", $val);
	if ($dObj) $p_awal = $dObj->format('Y-m-d');
	} else {
	$p_awal = $val;
	}
	}

	if ($req->periode_akhir) {
	$val = trim($req->periode_akhir);
	if (strpos($val, '/') !== false) {
	$dObj = \DateTime::createFromFormat("d/m/Y", $val);
	if ($dObj) $p_akhir = $dObj->format('Y-m-d');
	} else {
	$p_akhir = $val;
	}
	}

	// Apply filters
	if ($filter_type === 'semua') {
	$subTitle = 'Semua Riwayat Klaim Askes';
	} elseif ($filter_type === 'transaksi') {
	$subTitle = 'Klaim Askes Per Transaksi';
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
	if ($p_awal && $p_akhir) {
	$query->whereBetween('tk.created_at', [$p_awal . ' 00:00:00', $p_akhir . ' 23:59:59']);
	$subTitle .= ' (Periode: ' . date('d/m/Y', strtotime($p_awal)) . ' s/d ' . date('d/m/Y', strtotime($p_akhir)) . ')';
	} elseif ($p_awal) {
	$query->where('tk.created_at', '>=', $p_awal . ' 00:00:00');
	$subTitle .= ' (Mulai: ' . date('d/m/Y', strtotime($p_awal)) . ')';
	} elseif ($p_akhir) {
	$query->where('tk.created_at', '<=', $p_akhir . ' 23:59:59');
	$subTitle .= ' (Hingga: ' . date('d/m/Y', strtotime($p_akhir)) . ')';
	}
	} elseif ($filter_type === 'periode') {
	$subTitle = 'Klaim Askes Periode';
	if ($p_awal && $p_akhir) {
	$query->where('tk.periode_awal', '>=', $p_awal)
	->where('tk.periode_akhir', '<=', $p_akhir);
	$subTitle .= ' : ' . date('d/m/Y', strtotime($p_awal)) . ' s/d ' . date('d/m/Y', strtotime($p_akhir));
	} elseif ($p_awal) {
	$query->where('tk.periode_awal', '>=', $p_awal);
	$subTitle .= ' (Mulai: ' . date('d/m/Y', strtotime($p_awal)) . ')';
	} elseif ($p_akhir) {
	$query->where('tk.periode_akhir', '<=', $p_akhir);
	$subTitle .= ' (Hingga: ' . date('d/m/Y', strtotime($p_akhir)) . ')';
	}
	} elseif ($filter_type === 'minggu') {
	if ($req->bulan && $req->tahun && $req->minggu) {
	$year = (int)$req->tahun;
	$month = (int)$req->bulan;

	if ($req->minggu === '1') {
	$startDay = '01';
	$endDay = '07';
	} elseif ($req->minggu === '2') {
	$startDay = '08';
	$endDay = '14';
	} elseif ($req->minggu === '3') {
	$startDay = '15';
	$endDay = '22';
	} else {
	$startDay = '23';
	$endDay = date('t', strtotime("$year-$month-01"));
	}

	$startDate = sprintf('%04d-%02d-%02d', $year, $month, $startDay);
	$endDate = sprintf('%04d-%02d-%02d', $year, $month, $endDay);
	$query->whereBetween('tk.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
	$subTitle = 'Klaim Askes Periode: ' . date('d-m-Y', strtotime($startDate)) . ' s/d ' . date('d-m-Y', strtotime($endDate));
	} else {
	$subTitle = 'Klaim Askes Periode Mingguan';
	}
	} elseif ($filter_type === 'bulan') {
	$months = [
	'1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
	'5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
	'9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
	];
	$monthLabel = isset($months[$req->bulan]) ? $months[$req->bulan] : '';
	$subTitle = 'Klaim Askes Periode Bulan: ' . $monthLabel . ' ' . ($req->tahun ?? '');

	if ($req->bulan && $req->tahun) {
	$query->whereMonth('tk.created_at', $req->bulan)
	->whereYear('tk.created_at', $req->tahun);
	}
	} elseif ($filter_type === 'tahun') {
	$subTitle = 'Klaim Askes Periode Tahun: ' . ($req->tahun ?? '');
	if ($req->tahun) {
	$query->whereYear('tk.created_at', $req->tahun);
	}
	}

	$raw = $query->orderBy('tk.created_at', 'asc')->orderBy('tkd.id', 'asc')->get();

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
	<table @if(!$isExcel) width="100%" @endif style="border-collapse: collapse; border-bottom: 2px solid #2d3748; padding-bottom: 10px; margin-bottom: 20px; table-layout: fixed;">
			<colgroup>
			<col style="width: 35px;">
			<col style="width: 120px;">
			<col style="width: 75px;">
			<col style="width: 140px;">
			<col style="width: 80px;">
			<col style="width: 120px;">
			<col style="width: 120px;">
			<col style="width: 120px;">
			<col style="width: 90px;">
			<col style="width: 90px;">
			<col style="width: 90px;">
			<col style="width: 160px;">
			<col style="width: 75px;">
			</colgroup>
			<tr>
				<td colspan="6" style="text-align: left; vertical-align: middle;">
					<span style="font-size: 15pt; font-weight: bold; letter-spacing: 0.5px; color: #1a365d;">TEMPRINA MEDIA GRAFIKA</span><br/>
						<span style="font-size: 8pt; color: #718096; line-height: 14px;">Jl. Raya Sumengko Km 30, Wringinanom, Gresik<br/>Telp: (031) 8985222 | Email: info@temprina.co.id</span>
					</td>
					<td colspan="7" style="text-align: right; vertical-align: middle; line-height: 18px;">
						<span style="font-size: 12pt; font-weight: bold; color: #2d3748; text-transform: uppercase;">Laporan Klaim Askes</span><br/>
							<span style="font-size: 9pt; color: #4a5568; font-weight: 500;">{{ $subTitle }}</span>
						</td>
					</tr>
				</table>

				<!-- TABEL LAPORAN -->
				<table @if(!$isExcel) width="100%" @endif style="font-size: 10px; border-collapse: collapse; border: 1px solid #cbd5e0; margin-bottom: 20px; table-layout: fixed;" cellpadding="6">
						<colgroup>
						<col style="width: 35px;">
						<col style="width: 120px;">
						<col style="width: 75px;">
						<col style="width: 140px;">
						<col style="width: 80px;">
						<col style="width: 120px;">
						<col style="width: 120px;">
						<col style="width: 120px;">
						<col style="width: 90px;">
						<col style="width: 90px;">
						<col style="width: 90px;">
						<col style="width: 160px;">
						<col style="width: 75px;">
						</colgroup>
						<thead>
							<tr style="background-color: #edf2f7; border-bottom: 2px solid #cbd5e0; color: #2d3748;">
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">No</th>
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">No. Klaim</th>
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">NIK</th>
								<th style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Nama Karyawan</th>
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">Tgl Kuitansi</th>
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">Tgl Buat </th>
								<th style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Pasien / Klaim</th>
								<th style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Jenis Santunan</th>
								<th style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold;">Pengajuan</th>
								<th style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold;">Disetujui</th>
								<th style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold;">Ditolak</th>
								<th style="border: 1px solid #cbd5e0; text-align: left; font-weight: bold;">Keterangan</th>
								<th style="border: 1px solid #cbd5e0; text-align: center; font-weight: bold;">Status</th>
							</tr>
						</thead>
						<tbody>
							@if(count($raw) === 0)
								<tr>
									<td colspan="13" style="border: 1px solid #cbd5e0; text-align: center; padding: 20px; font-style: italic; color: #718096; background-color: #ffffff;">
										Tidak ditemukan data klaim askes yang cocok dengan filter.
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
										<td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px;">{{ $d->tanggal_buat ? date('d-m-Y H:i', strtotime($d->tanggal_buat)) : '-' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->klaim_nama ?? '-' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->jenis_santunan ?? '-' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: right; padding: 5px; font-weight: 500;">{{ $d->nominal_pengajuan ? 'Rp ' . number_format((float)$d->nominal_pengajuan, 0, ',', '.') : 'Rp 0' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: right; padding: 5px; font-weight: 500; color: #276749;">{{ $d->nominal_disetujui ? 'Rp ' . number_format((float)$d->nominal_disetujui, 0, ',', '.') : 'Rp 0' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: right; padding: 5px; font-weight: 500; color: #9b2c2c;">{{ $d->nominal_ditolak ? 'Rp ' . number_format((float)$d->nominal_ditolak, 0, ',', '.') : 'Rp 0' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: left; padding: 5px;">{{ $d->keterangan ?? '-' }}</td>
										<td style="border: 1px solid #cbd5e0; text-align: center; padding: 5px; font-weight: bold; color: {{ $statusColor }}">{{ $status }}</td>
									</tr>
								@endforeach
							@endif
						</tbody>
						@if(count($raw) > 0)
							<tfoot>
							<tr style="background-color: #edf2f7; font-weight: bold; border-top: 2px solid #cbd5e0; border-bottom: 2px solid #cbd5e0; color: #2d3748;">
								<td colspan="8" style="border: 1px solid #cbd5e0; text-align: right; font-weight: bold; padding: 6px;">TOTAL :</td>
								<td style="border: 1px solid #cbd5e0; text-align: right; padding: 6px; font-weight: bold;">Rp {{ number_format($totalPengajuan, 0, ',', '.') }}</td>
								<td style="border: 1px solid #cbd5e0; text-align: right; padding: 6px; font-weight: bold; color: #276749;">Rp {{ number_format($totalDisetujui, 0, ',', '.') }}</td>
								<td style="border: 1px solid #cbd5e0; text-align: right; padding: 6px; font-weight: bold; color: #9b2c2c;">Rp {{ number_format($totalDitolak, 0, ',', '.') }}</td>
								<td colspan="2" style="border: 1px solid #cbd5e0; background-color: #edf2f7;"></td>
							</tr>
							</tfoot>
						@endif
					</table>


				</div>
