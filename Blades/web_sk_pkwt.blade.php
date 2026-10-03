@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_kary_det_jabatan;
use App\Models\CustomModels\m_standart_gaji;

Carbon::setLocale('id');
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
$tanggalNoDay = $carbonDate->translatedFormat('d F Y');
$tanggal = $carbonDate->translatedFormat('d/m/Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$sbuCode = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

// Data Pihak Kesatu (Perusahaan)
$namaPihak1 = $t_mutasi->signature?->nama_lengkap ?? 'RE TEST';
$jabatanPihak1 = $t_mutasi->signature?->m_posisi?->name ?? 'Direktur';
$alamatPerusahaan = $t_mutasi->signature?->m_subcomp?->m_company?->address 
                 ?? $t_mutasi->m_kary?->m_branch?->alamat
                 ?? 'Jl. Raya Sumengko Km 30 - 31 Wringinanom Gresik';
$jenisUsaha = $isJpBooks ? 'Penerbitan & Percetakan Buku' : 'Commercial Printing';

// Data Pihak Kedua (Karyawan)
$namaPihak2 = strtoupper($t_mutasi->m_kary?->nama_lengkap ?? '-');
$nikPihak2 = $t_mutasi->m_kary?->nik ?? '-';
$jkPihak2 = $t_mutasi->m_kary?->jk?->value ?? ($t_mutasi->m_kary?->jenis_kelamin ?? 'Laki-Laki');

$tglLahir = $t_mutasi->m_kary?->tgl_lahir;
$umur = $tglLahir ? Carbon::parse($tglLahir)->diffInYears($carbonDate) . ' TAHUN' : '-';

$lastEdu = $t_mutasi->m_kary?->m_kary_det_pend()->where('is_pend_terakhir', true)->first();
$pendidikanTerakhir = $lastEdu ? ($lastEdu->jurusan ? $lastEdu->jurusan . ' - ' : '') . $lastEdu->nama_sekolah : ($lastEdu->nama_sekolah ?? '-');

$bagian = $t_mutasi->m_posisi_baru?->name 
        ?? $t_mutasi->m_posisi_lama?->name 
        ?? $t_mutasi->m_kary?->m_posisi?->name 
        ?? '-';

$alamatPenempatan = $t_mutasi->m_branch_baru?->alamat 
                  ?? $t_mutasi->m_branch_lama?->alamat 
                  ?? $t_mutasi->m_kary?->m_branch?->alamat 
                  ?? 'Jl. Raya Sumengko Km 30 - 31 Wringinanom Gresik';

// Durasi Kontrak
$kontrak = $t_mutasi->m_kary?->m_kary_det_jabatan()->where('is_active', true)->where('is_primary', true)->first();
if ($kontrak && $kontrak->start_time && $kontrak->end_time) {
    $durasiKontrak = Carbon::parse($kontrak->start_time)->translatedFormat('d F Y') . " sampai dengan " . Carbon::parse($kontrak->end_time)->translatedFormat('d F Y');
} else {
    $startTgl = $carbonDate;
    $endTgl = $carbonDate->copy()->addYear()->subDay();
    $durasiKontrak = $startTgl->translatedFormat('d F Y') . " sampai dengan " . $endTgl->translatedFormat('d F Y');
}

// Kompensasi / Gaji
$salary = m_standart_gaji::where('m_kary_id', $t_mutasi->m_kary_id)->first();
$gapok = $salary ? (float)$salary->gaji_pokok : 0;
$tprod = $salary ? (float)$salary->tunjangan_produktifitas : 0;
$ttrans = $salary ? (float)$salary->tunjangan_transport : 0;
$totalSalary = $gapok + $tprod + $ttrans;

if ($totalSalary <= 0 && !empty($t_mutasi->kompensasi)) {
    $totalSalary = (float)$t_mutasi->kompensasi;
}

$gapokFormatted = number_format($gapok, 0, ',', '.');
$tprodFormatted = number_format($tprod, 0, ',', '.');
$ttransFormatted = number_format($ttrans, 0, ',', '.');
$totalSalaryFormatted = number_format($totalSalary, 0, ',', '.');

// Media Kop Base64
$mediaTMG = \DB::table('m_media')->where('is_active', true)->where('kode', 'LOGO-TMG')->first();
$mediaISO = \DB::table('m_media')->where('is_active', true)->where('kode', 'BANNER-ISO-TEMPRINA')->first();

$resolveMediaPath = function($rawPath) {
    if (empty($rawPath)) return '';
    $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
    $clean = ltrim($clean, '/');

    $candidates = [
        $clean,
        'uploads/m_media/' . $clean,
        'uploads/' . $clean,
        'storage/' . $clean,
        'images/' . $clean
    ];

    foreach ($candidates as $cand) {
        $full = function_exists('public_path') ? public_path($cand) : (function_exists('base_path') ? base_path('public/' . $cand) : ('/opt/www/hris/public/' . $cand));
        if ($full && file_exists($full) && is_file($full)) {
            $type = pathinfo($full, PATHINFO_EXTENSION);
            return 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
        }
    }
    return $rawPath;
};

$logoTmgSrc = $mediaTMG ? $resolveMediaPath($mediaTMG->file_path) : '';
$bannerIsoSrc = $mediaISO ? $resolveMediaPath($mediaISO->file_path) : '';
@endphp

<!-- HALAMAN 1 -->
<div style="font-family:'Times New Roman', Times, serif; width:100%; font-size:10px; line-height:1.3; color:#000;">

  <!-- KOP SURAT RESMI -->
  @if($isJpBooks)
    @include('projects.web_sk_kop')
  @else
    <table style="width:100%; border-collapse:collapse; margin-bottom:0;">
      <tr>
        <td style="width:38%; vertical-align:middle;">
          @if(!empty($logoTmgSrc))
            <img src="{{ $logoTmgSrc }}" style="width:160px; height:auto;" alt="Temprina Jawa Pos Group">
          @else
            <div style="font-weight:bold; font-size:19px; color:#005596; letter-spacing:1px;">temprina</div>
            <div style="font-size:9px; font-weight:bold; color:#f37023; letter-spacing:2px; margin-top:-2px;">Jawa Pos Group</div>
          @endif
        </td>
        <td style="width:25%; vertical-align:middle; text-align:center;">
          @if(!empty($bannerIsoSrc))
            <img src="{{ $bannerIsoSrc }}" style="width:140px; height:auto;" alt="ISO Certifications">
          @endif
        </td>
        <td style="width:37%; vertical-align:top; text-align:right; font-size:7.2px; line-height:1.2; color:#222;">
          <b style="font-size:7.8px;">Head Office:</b><br>
          Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik<br>
          Telp: 031 898 2999, Fax: Office 031-898 2065<br>
          Marketing: 031-898 1777, Purchasing: 031-898 2066<br>
          HRD: 031-898 3622<br>
          www.temprina.com E-mail: temprina@temprina.com
        </td>
      </tr>
    </table>
    <!-- DOUBLE LINE SEPARATOR -->
    <div style="border-top:1px solid #000; margin-top:4px;"></div>
    <div style="border-top:2px solid #000; margin-top:1.5px; margin-bottom:6px;"></div>
  @endif

  <!-- JUDUL SURAT -->
  <table style="width:100%; border-collapse:collapse; margin-top:2px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:13px; text-decoration:underline; letter-spacing:0.5px;">
        PERJANJIAN KERJA WAKTU TERTENTU
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:10.5px; padding-top:2px;">
        <b>No.</b> {{ $t_mutasi?->nomor ?? '-' }}
      </td>
    </tr>
  </table>

  <!-- PEMBUKA PIHAK -->
  <div style="font-size:10px; margin-top:5px; margin-bottom:2px;">
    Kami yang bertanda tangan di bawah ini :
  </div>

  <table style="width:100%; border-collapse:collapse; font-size:10px; margin-top:2px;">
    <!-- PIHAK KESATU -->
    <tr>
      <td style="width:4%; vertical-align:top;">1.</td>
      <td style="width:26%; vertical-align:top;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:67%; vertical-align:top; font-weight:bold;">{{ $namaPihak1 }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Jabatan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $jabatanPihak1 }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Nama Perusahaan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ strtoupper($companyName) }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Alamat Perusahaan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $alamatPerusahaan }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Jenis Usaha</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $jenisUsaha }}</td>
    </tr>
    <tr>
      <td></td>
      <td colspan="3" style="padding-top:2px; padding-bottom:3px; text-align:justify;">
        Bertindak untuk dan atas nama Direksi {{ $companyName }}, selanjutnya disebut sebagai <b>PIHAK KESATU</b>.
      </td>
    </tr>

    <!-- PIHAK KEDUA -->
    <tr>
      <td style="vertical-align:top;">2.</td>
      <td style="vertical-align:top;">Nama</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top; font-weight:bold;">{{ $namaPihak2 }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">NIK</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $nikPihak2 }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Jenis Kelamin</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $jkPihak2 }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Usia</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $umur }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Pendidikan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $pendidikanTerakhir }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Bagian</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top; font-weight:bold;">{{ $bagian }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Penempatan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">
        {{ strtoupper($companyName) }}<br>
        {{ $alamatPenempatan }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan="3" style="padding-top:2px; padding-bottom:3px; text-align:justify;">
        Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai <b>PIHAK KEDUA</b>.
      </td>
    </tr>
  </table>

  <div style="font-size:9.5px; margin-top:3px; margin-bottom:3px; text-align:justify;">
    Menerangkan bahwa pada hari ini tanggal {{ $tanggalNoDay }} kedua belah pihak telah bersepakat untuk mengadakan perjanjian kerja dengan syarat-syarat tersebut di bawah ini :
  </div>

  <!-- ISI PASAL 1 S/D 4 -->
  <table style="width:100%; border-collapse:collapse; font-size:9.2px; line-height:1.2;">
    <!-- PASAL 1 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px;">
        PASAL 1. MULAI DAN JANGKA WAKTU BERLAKUNYA PERJANJIAN KERJA :
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-bottom:2px;">
        Terhitung {{ $durasiKontrak }}.
      </td>
    </tr>

    <!-- PASAL 2 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px;">
        PASAL 2. PENEMPATAN DAN TUGAS :
      </td>
    </tr>
    <tr>
      <td style="width:4%; vertical-align:top;">2.1.</td>
      <td style="width:96%; vertical-align:top; text-align:justify;">
        Pihak Kedua bersedia dipekerjakan pada bagian tersebut di atas atau dialihkan ke bagian lain atau cabang lain karena kebutuhan perusahaan.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">2.2.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Pihak Kedua serta wajib memenuhi segala ketentuan yang diatur dalam peraturan perusahaan dan/atau kebijakan Perusahaan.
      </td>
    </tr>

    <!-- PASAL 3 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px;">
        PASAL 3. PEMUTUSAN HUBUNGAN KERJA (terjadi dengan sendirinya dan/ atau dapat dilakukan jika) :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">3.1.</td>
      <td style="vertical-align:top; text-align:justify;">
        Masa kerja berakhir, terkecuali diperpanjang jika telah disetujui oleh kedua pihak.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">3.2.</td>
      <td style="vertical-align:top; text-align:justify;">
        Melakukan pelanggaran atas tata tertib perusahaan dan/atau sebagaimana ditentukan dalam peraturan perusahaan yang berlaku.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">3.3.</td>
      <td style="vertical-align:top; text-align:justify;">
        Melakukan pemalsuan atau manipulasi data lamaran, laporan pekerjaan atau melakukan kecurangan (Fraud).
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">3.4.</td>
      <td style="vertical-align:top; text-align:justify;">
        Menolak perintah atasan yang sah dan/atau menolak untuk ditugaskan pada bagian lain atau lokasi lain atau pada cabang lain di perusahaan.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">3.5.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Karyawan mengundurkan diri sebelum berakhirnya jangka waktu perjanjian (kontrak), karyawan berkewajiban membayar ganti rugi atas sisa kontrak yang belum dijalani. Perusahaan berhak tidak memberikan kompensasi dalam hal karyawan belum melakukan pembayaran ganti rugi dan /atau melakukan pengunduran diri yang tidak memenuhi ketentuan peraturan perundang-undangan yang berlaku.
      </td>
    </tr>

    <!-- PASAL 4 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px;">
        PASAL 4. KOMPENSASI :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">4.1.</td>
      <td style="vertical-align:top; text-align:justify;">
        Pihak kedua bersedia diberikan upah yang bersifat HONORARIUM - Sebesar Rp. {{ $totalSalaryFormatted }},- per bulan.
        <table style="width:100%; border-collapse:collapse; margin-top:1px; margin-bottom:1px; font-size:9.2px;">
          <tr>
            <td style="width:3%;"></td>
            <td style="width:97%;">1. Gaji Pokok : Rp. {{ $gapokFormatted }},-</td>
          </tr>
          <tr>
            <td></td>
            <td>2. Tunjangan transport : Rp. {{ $ttransFormatted }},-</td>
          </tr>
          <tr>
            <td></td>
            <td>3. Tunjangan produktifitas : Rp. {{ $tprodFormatted }},-</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">4.2.</td>
      <td style="vertical-align:top; text-align:justify;">
        Pajak atas penghasilan ( PPH Pasal 21 ) ditanggung oleh perusahaan sebatas penghasilan selama bekerja di perusahaan.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">4.3.</td>
      <td style="vertical-align:top; text-align:justify;">
        BPJS Kesehatan, BPJS Ketenagakerjaan, BPJS Pensiun dibayarkan Proposional oleh Perusahaan dan Karyawan sesuai Peraturan Pemerintah.
      </td>
    </tr>
  </table>

</div>

<!-- PAGE BREAK KE HALAMAN 2 -->
<div style="page-break-before:always;"></div>

<!-- HALAMAN 2 -->
<div style="font-family:'Times New Roman', Times, serif; width:100%; font-size:10.5px; line-height:1.4; color:#000; padding-top:24px;">

  <!-- PASAL 5. PENUTUP -->
  <table style="width:100%; border-collapse:collapse; font-size:10.5px; line-height:1.4;">
    <tr>
      <td colspan="2" style="font-weight:bold;">
        PASAL 5. PENUTUP :
      </td>
    </tr>
    <tr>
      <td style="width:4%; vertical-align:top;">5.1.</td>
      <td style="width:96%; vertical-align:top; text-align:justify;">
        Demikian perjanjian kerja ini dibuat tanpa paksaan dari pihak manapun juga dan setelah disetujui, dan ditandatangani kedua belah pihak.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">5.2.</td>
      <td style="vertical-align:top; text-align:justify;">
        Perjanjian Kerja ini berlaku sejak tanggal ditetapkan, jika dikemudian hari terdapat penambahan dan/atau perubahan, maka akan dibuat dalam addendum perjanjian secara tertulis.
      </td>
    </tr>
  </table>

  <!-- TANGGAL PENETAPAN -->
  <table style="width:100%; margin-top:28px; font-size:10.5px; border-collapse:collapse;">
    <tr>
      <td style="width:14%;">Ditetapkan di</td>
      <td style="width:2%;">:</td>
      <td style="width:84%;">{{ $kotaTerbit }}</td>
    </tr>
    <tr>
      <td>Tanggal</td>
      <td>:</td>
      <td>{{ $tanggalTerbit }}</td>
    </tr>
  </table>

  <!-- TANDA TANGAN PIHAK KESATU & PIHAK KEDUA -->
  <table style="width:100%; margin-top:28px; font-size:10.5px; border-collapse:collapse;">
    <tr>
      <td style="width:50%; text-align:left; font-weight:bold;">
        PIHAK KESATU
      </td>
      <td style="width:50%; text-align:left; font-weight:bold;">
        PIHAK KEDUA
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:70px;"></td>
    </tr>
    <tr>
      <td style="width:50%; vertical-align:top;">
        <b style="text-decoration:underline;">{{ $namaPihak1 }}</b><br>
        <span>{{ $jabatanPihak1 }}</span>
      </td>
      <td style="width:50%; vertical-align:top;">
        <b style="text-decoration:underline;">{{ $namaPihak2 }}</b><br>
        <span>Karyawan</span>
      </td>
    </tr>
  </table>

</div>