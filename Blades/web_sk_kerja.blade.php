@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
use App\Models\CustomModels\m_kary_det_jabatan;
Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

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

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

@include('projects.web_sk_kop')

<table style="width:100%; margin-top:20px; font-family:'Times New Roman', serif;">
  <tr>
    <td style="width:20%;"></td>

    <td style="
        width:60%;
        text-align:center;
        font-weight:bold;
        font-size:18px;
        border-bottom:3px solid black;
        padding-bottom:4px;
        letter-spacing:0.5px;">
      SURAT KETERANGAN KERJA
    </td>

    <td style="width:20%;"></td>
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
</table>

<div></div>
<div></div>

<table style="font-family:Times New Roman; font-size:14px;">
  <tr>
    <td>Yang bertanda tangan di bawah ini :</td>
  </tr>
</table>
<table cellspacing="0" style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:14px;">
  <tr>
    <td rowspan="4" style="width: 5%;"></td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->signature?->nama_lengkap}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Bagian</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_posisi->name}}<br>{{$t_mutasi->signature?->m_subcomp?->name}}<br>{{$t_mutasi->signature?->m_subcomp?->address}}</td>
  </tr>
</table>
<table style="font-family:Times New Roman; font-size:14px;">
  <tr>
    <td>Menerangkan bahwa :</td>
  </tr>
  <tr>
    <td></td>
  </tr>
</table>
<table cellspacing="0" style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:14px;">
  <tr>
    <td rowspan="5" style="width: 5%;"></td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Status</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->status_kary_lama?->value ?? '-'}} {{ ' ' . $t_mutasi->m_sub_lama?->name ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Bagian</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->m_posisi_lama?->name}} {{ ' ' . $t_mutasi->m_sub_lama?->name}}<br>{{ $t_mutasi->m_sub_lama?->address}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Awal Kerja</td>
    <td style="width: 3%;">:</td>
    <td>{{$awalKerja}} s/d {{$akhirKerja}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Keperluan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->deskripsi ?? $t_mutasi->keterangan ?? '-'}}</td>
  </tr>
    <tr>
    <td></td>
  </tr>
</table>
<table style="font-family:Times New Roman; font-size:14px;">
  <tr>
    <td>Demikian surat keterangan kerja ini dibuat agar dapat dipergunakan sebagaimana mestinya.</td>
  </tr>
</table>
<div></div>
<div></div>
<table style="font-family:Times New Roman; font-size:14px; margin-top:20px;">
  <tr>
    <td>{{$kotaTerbit}}, {{$tanggalTerbit}}</td>
  </tr>
  <tr>
    <td><strong>{{$companyName}}</strong></td>
  </tr>
  <tr>
    <td><div style="height:55px;"></div></td>
  </tr>
  <tr>
    <td>
      <span style="font-weight:bold; text-decoration:underline; text-align: left;">
          {{$t_mutasi->signature?->nama_lengkap ?? ''}}
      </span>
    </td>
  </tr>

  <tr>
    <td>
      {{$t_mutasi->signature?->m_posisi?->name ?? ''}}
    </td>
  </tr>

</table>

@include('projects.web_sk_footer')

</div>