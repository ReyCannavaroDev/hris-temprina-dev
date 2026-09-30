@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
use App\Models\CustomModels\m_kary_det_jabatan;
use App\Models\CustomModels\m_standart_gaji;
Carbon::setLocale('id');
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
$tanggalNoDay = $carbonDate->translatedFormat('d F Y');
$tanggal = $carbonDate->translatedFormat('d/m/Y');


$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_mutasi->t_mutasi_d_tembusan ?? null;
$memperhatikan = $t_mutasi->t_mutasi_d_memperhatikan ?? null;

$karyawan_jabatan = m_kary_det_jabatan::where('m_karyawan_id', $t_mutasi->m_kary_id)
->orderBy('start_time', 'asc')
->first();

$awalKerja = $karyawan_jabatan ? Carbon::parse($karyawan_jabatan->tgl_mulai)->translatedFormat('d F Y') : '-';
$akhirKerja = $karyawan_jabatan && $karyawan_jabatan->tgl_selesai
? Carbon::parse($karyawan_jabatan->tgl_selesai)->translatedFormat('d F Y')
: 'Sekarang';

$tgl_lahir = $t_mutasi->m_kary->tgl_lahir;
$umur = Carbon::parse($tgl_lahir)->diffInYears($carbonDate);

$lastEdu = $t_mutasi->m_kary->m_kary_det_pend()->where('is_pend_terakhir', true)->first();
$pendidikanTerakhir = $lastEdu ? $lastEdu->jurusan . " " . $lastEdu->nama_sekolah : "-";

$kontrak = $t_mutasi->m_kary->m_kary_det_jabatan()->where('is_active', true)->where('is_primary', true)->first();
$durasiKontrak = $kontrak ? Carbon::parse($kontrak->start_time)->translatedFormat('d F Y') . " sampai dengan " .
Carbon::parse($kontrak->end_time)->translatedFormat('d F Y') : "-";

$salary = m_standart_gaji::where('m_kary_id', $t_mutasi->m_kary_id)->first();
$gapok = $salary ? $salary->gaji_pokok : 0;
$tprod = $salary ? $salary->tunjangan_produktifitas : 0;
$ttrans = $salary ? $salary->tunjangan_transport : 0;
$totalSalary = $gapok + $tprod + $ttrans;

$gapok = number_format($gapok, 0, ',', '.');
$tprod = number_format($tprod, 0, ',', '.');
$ttrans = number_format($ttrans, 0, ',', '.');
$totalSalary = number_format($totalSalary, 0, ',', '.');



$kompensasiRaw = $t_mutasi?->kompensasi ?? 0;
$kompensasi = number_format($kompensasiRaw, 0, ',', '.');

$sbuCode = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : $compRaw;
$kotaTerbit = $t_mutasi?->m_sub_lama?->m_branch?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:11px;line-height:1.4;">

@include('projects.web_sk_kop')

<table style="width:100%; margin-top:15px; font-family:'Times New Roman', serif;">
  <tr>
    <td style="width:18%;"></td>

    <td style="
        width:64%;
        text-align:center;
        font-weight:bold;
        font-size:14px;
        border-bottom:2px solid black;
        padding-bottom:4px;
        letter-spacing:0.5px;">
      PERJANJIAN KERJA WAKTU TERTENTU
    </td>

    <td style="width:18%;"></td>
  </tr>

  <tr>
    <td></td>

    <td style="
        text-align:center;
        font-size:13px;
        padding-top:6px;">
      <span style="font-weight:bold;">No.</span> {{$t_mutasi?->nomor}}
    </td>

    <td></td>
  </tr>

  <tr>
    <td></td>
  </tr>
</table>

<table style="font-family:Times New Roman; font-size:11px;">
  <tr>
    <td>Kami yang bertanda tangan di bawah ini :</td>
  </tr>
</table>
<table cellspacing="0" style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:11px;">
  <tr>
    <td rowspan="4" style="width: 5%;">1.</td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->signature?->nama_lengkap ?? ''}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jabatan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_posisi?->name ?? ''}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Alamat Perusahaan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_subcomp?->m_company?->address ?? ''}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jenis Usaha</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_subcomp?->m_comp?->name ?? ''}}</td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <tr>
    <td></td>
    <td colspan="3">Bertindak untuk dan atas nama Direksi PT {{$t_mutasi->signature?->m_subcomp?->m_company?->name ??
      ''}}, selanjutnya disebut sebagai PIHAK
      KESATU.</td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <tr>
    <td rowspan="699" style="width: 5%;">2.</td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
  </tr>

  <tr>
    <td style="width: 25%;">NIK</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->m_kary?->nik ?? '-'}}</td>
  </tr>

  <tr>
    <td style="width: 25%;">Jenis Kelamin</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->m_kary?->jk?->value ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Usia</td>
    <td style="width: 3%;">:</td>
    <td>{{$umur}} TAHUN</td>
  </tr>

  <tr>
    <td style="width: 25%;">Pendidikan</td>
    <td style="width: 3%;">:</td>
    <td>{{$pendidikanTerakhir}}</td>
  </tr>

  <tr>
    <td style="width: 25%;">Bagian</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi?->m_posisi_lama?->name ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Penempatan</td>
    <td style="width: 3%;">:</td>
    <td>PT {{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}}<br>
{{$t_mutasi->m_sub_lama?->m_company?->address ?? '-'}}</td>
  </tr>


  <tr>
    <td></td>
  </tr>
</table>

<table style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:11px;">
  <tr>
    <td colspan="3">Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai PIHAK KEDUA.</td>
  </tr>


  <tr>
    <td></td>
  </tr>

  <tr>
    <td colspan="3">Menerangkan bahwa pada hari ini tanggal {{$tanggalNoDay}} kedua belah pihak telah bersepakat untuk
      mengadakan perjanjian
      kerja dengan syarat-syarat tersebut di bawah ini :</td>
  </tr>
</table>

<tr>
  <td></td>
</tr>

<table width="100%" 
  style="font-family:Times New Roman; font-size:11px; border-collapse:collapse;">

  <!-- PASAL 1 -->
  <tr>
    <td colspan="2"><b>PASAL 1. MULAI DAN JANGKA WAKTU BERLAKUNYA PERJANJIAN KERJA :</b></td>
  </tr>
  <tr>
    <td colspan="2">
      Terhitung {{$durasiKontrak}}.
    </td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <!-- PASAL 2 -->
  <tr>
    <td colspan="2"><b>PASAL 2. PENEMPATAN DAN TUGAS :</b></td>
  </tr>
  <tr>
    <td width="5%" style="vertical-align:top;">2.1</td>
    <td width="95%" style="vertical-align:top;">Pihak Kedua bersedia dipekerjakan pada bagian tersebut di atas atau dialihkan ke bagian lain atau cabang lain
      karena kebutuhan perusahaan.
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;">2.2</td>
    <td style="vertical-align:top;">Pihak Kedua bersedia dipekerjakan pada bagian tersebut di atas atau dialihkan ke bagian lain atau cabang lain
      karena kebutuhan perusahaan.
    </td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <!-- PASAL 3 -->
  <tr>
    <td colspan="2"><b>PASAL 3. PEMUTUSAN HUBUNGAN KERJA :</b></td>
  </tr>
  <tr>
    <td style="vertical-align:top;">3.1</td>
    <td style="vertical-align:top;">Masa kerja berakhir, terkecuali diperpanjang jika telah disetujui oleh kedua pihak.
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;">3.2</td>
    <td style="vertical-align:top;">Melakukan pelanggaran atas tata tertib perusahaan dan/atau sebagaimana ditentukan
      dalam peraturan perusahaan yang berlaku.</td>
  </tr>
  <tr>
    <td style="vertical-align:top;">3.3</td>
    <td style="vertical-align:top;">Melakukan pemalsuan atau manipulasi data lamaran, laporan pekerjaan atau melakukan
      kecurangan (Fraud).</td>
  </tr>
  <tr>
    <td style="vertical-align:top;">3.4</td>
    <td style="vertical-align:top;">Menolak perintah atasan yang sah dan/atau menolak untuk ditugaskan pada bagian lain
      atau lokasi lain atau cabang lain di perusahaan.</td>
  </tr>
  <tr>
    <td style="vertical-align:top;">3.5</td>
    <td style="vertical-align:top;">Karyawan mengundurkan diri sebelum berakhirnya jangka waktu perjanjian (kontrak), karyawan berkewajiban membayar
      ganti rugi atas sisa kontrak yang belum dijalani.
    </td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <!-- PASAL 4 -->
  <tr>
    <td colspan="2"><b>PASAL 4. KOMPENSASI :</b></td>
  </tr>
  <tr>
    <td style="vertical-align:top;">4.1</td>
    <td style="vertical-align:top;">Pihak kedua bersedia diberikan upah yang bersifat HONORARIUM -
      Sebesar Rp. {{$totalSalary}},- per bulan.
      <br>
      <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td width="5%"></td>
          <td>1. Gaji Pokok : Rp. {{$gapok}},-</td>
        </tr>
        <tr>
          <td></td>
          <td>2. Tunjangan transport : Rp. {{$ttrans}},-</td>
        </tr>
        <tr>
          <td></td>
          <td>3. Tunjangan produktifitas : Rp. {{$tprod}},-</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;">4.2</td>
    <td style="vertical-align:top;">Pajak atas penghasilan (PPH Pasal 21) ditanggung oleh perusahaan sebatas penghasilan selama bekerja di perusahaan.
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;">4.3</td>
    <td style="vertical-align:top;">BPJS Kesehatan, BPJS Ketenagakerjaan, BPJS Pensiun dibayarkan proporsional oleh Perusahaan dan Karyawan sesuai
      Peraturan Pemerintah.
    </td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <!-- PASAL 5 -->
  <tr>
    <td colspan="2"><b>PASAL 5. PENUTUP :</b></td>
  </tr>
  <tr>
    <td style="vertical-align:top;">5.1</td>
    <td style="vertical-align:top;">Demikian perjanjian kerja ini dibuat tanpa paksaan dari pihak manapun juga dan setelah disetujui dan
      ditandatangani kedua belah pihak.
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;">5.2</td>
    <td style="vertical-align:top;">Perjanjian Kerja ini berlaku sejak tanggal ditetapkan.
    </td>
  </tr>

  <tr>
    <td></td>
  </tr>

</table>

<table style="width:100%; margin-top:20px;">
  <tr>
    <td style="width:50%; text-align:left;">PIHAK KESATU<br><strong>{{$companyName}}</strong></td>
    <td style="width:50%; text-align:right;">PIHAK KEDUA</td>
  </tr>
  <tr>
    <td colspan="2"><div style="height:55px;"></div></td>
  </tr>
  <tr style="font-size: 11px;">
    <td style="width:50%; text-align:left;">
      <b><u>{{$t_mutasi->signature?->nama_lengkap ?? '-'}}</u></b><br>
      {{$t_mutasi->signature?->m_posisi?->name ?? ''}}
    </td>
    <td style="width:50%; text-align:right;">
      <b><u>{{$t_mutasi?->m_kary?->nama_lengkap ?? '-'}}</u></b><br>
      {{$t_mutasi?->m_posisi_baru?->name ?? $t_mutasi?->m_posisi_lama?->name ?? 'Karyawan'}}
    </td>
  </tr>
</table>

@include('projects.web_sk_footer')

</div>