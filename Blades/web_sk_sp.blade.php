@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_surat_peringatan;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_general;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_surat_peringatan = t_surat_peringatan::find($id);
$t_mutasi = null;
if (!$t_surat_peringatan) {
    $t_mutasi = t_mutasi::find($id);
}

$nomor = $t_surat_peringatan?->nomor ?? $t_mutasi?->nomor ?? '-';
$tgl = $t_surat_peringatan?->tgl ?? $t_mutasi?->tgl ?? now();
$carbonDate = Carbon::parse($tgl);
$tanggalOnly = $carbonDate->translatedFormat('d F Y');

$karyawan = $t_surat_peringatan?->m_kary ?? $t_mutasi?->m_kary;
$namaKaryawan = $karyawan?->nama_lengkap ?? '-';
$jabatanKaryawan = $karyawan?->m_posisi?->name ?? $t_mutasi?->m_posisi_baru?->name ?? $t_mutasi?->m_posisi_lama?->name ?? '-';
$alamatKaryawan = $karyawan?->address ?? '-';

$levelSpName = 'Surat Peringatan (SP) I (Satu)';
if ($t_surat_peringatan?->level_sp) {
    $level_sp = m_general::find($t_surat_peringatan->level_sp);
    $levelSpName = $level_sp?->value ?? 'Surat Peringatan';
} elseif ($t_mutasi?->keterangan) {
    $levelSpName = $t_mutasi->keterangan;
}

$city = $t_surat_peringatan?->m_kary?->m_branch?->city?->value 
      ?? $t_mutasi?->m_branch_baru?->kota 
      ?? $t_mutasi?->m_branch_lama?->kota 
      ?? $t_mutasi?->m_kary?->m_branch?->kota 
      ?? 'Surabaya';

$masaBerlaku = $t_surat_peringatan?->masa_berlaku ?? 6;

$pelanggaran = $t_surat_peringatan?->t_surat_peringatan_d_pelanggaran 
             ?? $t_mutasi?->t_mutasi_d_memperhatikan 
             ?? collect();

$tembusan = $t_surat_peringatan?->t_surat_peringatan_d_tembusan 
          ?? $t_mutasi?->t_mutasi_d_tembusan 
          ?? null;

$signature = $t_surat_peringatan?->signature ?? $t_mutasi?->signature;
$namaTtd = $signature?->nama_lengkap ?? '-';
$jabatanTtd = $signature?->m_posisi?->name ?? '-';

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $karyawan?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $karyawan?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

  @include('projects.web_sk_kop')

  <table cellspacing="0" cellpadding="0"
    style="width:100%; margin-top:20px; font-family:'Times New Roman'; font-size:12px; line-height:1.3;">
    <tr>
      <td style="width:70%; padding:0;">No. &nbsp;{{$nomor}}</td>
      <td style="width:30%; text-align:right; padding:0;">{{$city}}, {{$tanggalOnly}}</td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:4px;">Hal : <strong>{{$levelSpName}}</strong></td>
    </tr>
  </table>

  <div style="margin-top:15px; font-size:12px;">
    Diberikan kepada :
  </div>

  <table cellspacing="0" style="width:100%; margin-top:10px; font-family:'Times New Roman'; font-size:11px; line-height:1.5;">
    <tr>
      <td style="width: 5%;"></td>
      <td style="width: 20%; vertical-align:top;">Nama</td>
      <td style="width: 3%; vertical-align:top;">:</td>
      <td style="width: 72%; vertical-align:top; font-weight:bold;">{{$namaKaryawan}}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Jabatan</td>
      <td style="vertical-align:top;">:</td>
      <td style="vertical-align:top;">{{$jabatanKaryawan}} {{$companyName}}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Alamat</td>
      <td style="vertical-align:top;">:</td>
      <td style="vertical-align:top;">{{$alamatKaryawan}}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Kesalahan</td>
      <td style="vertical-align:top;">:</td>
      <td style="vertical-align:top;">
        @if(count($pelanggaran) > 0)
          @foreach($pelanggaran as $index => $item)
            {{ $index + 1 }}. {{ $item->value ?? $item['value'] ?? '-' }}<br>
          @endforeach
        @elseif($t_mutasi?->deskripsi)
          {{ $t_mutasi->deskripsi }}
        @else
          -
        @endif
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top;">Jenis Sanksi</td>
      <td style="vertical-align:top;">:</td>
      <td style="vertical-align:top;">1. {{ $levelSpName }} atas kesalahan di atas</td>
    </tr>
  </table>

  <table cellspacing="0" cellpadding="0" style="width:100%; margin-top:15px; font-family:'Times New Roman'; font-size:12px; line-height:1.5;">
    <tr>
      <td style="text-align:justify;">
        {{$levelSpName}} ini berlaku selama {{$masaBerlaku}} bulan terhitung dari tanggal dikeluarkan, apabila yang
        bersangkutan masih melakukan pelanggaran lagi maka perusahaan dapat memberikan Surat Peringatan
        berikutnya atau sesuai dengan Undang - Undang yang berlaku.
      </td>
    </tr>
    <tr>
      <td style="padding-top:12px; text-align:justify;">
        Dengan adanya {{$levelSpName}} yang diberikan kepada Saudara/i ini maka manajemen
        berharap agar Saudara/i dapat lebih baik lagi dalam hal kontrol, konsentrasi dan koordinasi tugas di
        lingkungan kerja Saudara/i sehari-hari. Atas perhatiannya disampaikan terima kasih.
      </td>
    </tr>
  </table>

  <!-- TANDA TANGAN -->
  <table cellspacing="0" cellpadding="0"
    style="width:100%; margin-top:25px; font-family:'Times New Roman'; font-size:12px; line-height:1.2;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        Hormat Kami,<br>
        <strong>{{$companyName}}</strong>
        <div style="height:55px;"></div>
        <div style="font-weight:bold; text-decoration:underline;">{{$namaTtd}}</div>
        <div>{{$jabatanTtd}}</div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN -->
  <table style="font-family:'Times New Roman'; width:100%; margin-top:20px; font-size: 11px; border-collapse: collapse;">
    <tr>
      <td style="width:12%; vertical-align:top;">Tembusan</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:86%; vertical-align:top;">
        @if($tembusan && count($tembusan) > 0)
          @foreach($tembusan as $index => $t)
            {{ $index + 1 }}. {{ $t->value ?? $t['value'] ?? '-' }}<br>
          @endforeach
        @else
          1. Direksi<br>
          2. Operational Manager<br>
          3. HRD
        @endif
      </td>
    </tr>
  </table>

  @include('projects.web_sk_footer')

</div>