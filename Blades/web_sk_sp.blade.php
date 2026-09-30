@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_surat_peringatan;
use App\Models\CustomModels\m_general;


Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_surat_peringatan = t_surat_peringatan::find($id);
$level_sp = m_general::find($t_surat_peringatan->level_sp);

$carbonDate = Carbon::parse($t_surat_peringatan->tgl);
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
$tanggalOnly = $carbonDate->translatedFormat('d F Y');


$terbitDate = Carbon::parse($t_surat_peringatan?->updated_at ?? $t_surat_peringatan?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_surat_peringatan->t_surat_peringatan_d_tembusan ?? null;
$pelanggaran = $t_surat_peringatan->t_surat_peringatan_d_pelanggaran ?? null;

$city = $t_surat_peringatan?->m_kary?->m_branch?->city?->value ?? '-';

@endphp

<div></div>
<table cellspacing="0" cellpadding="0"
  style="width:100%; margin-top:20px; font-family:'Times New Roman'; font-size:12px; line-height:1.1;">
  <tr>
    <td style="padding:0;">{{$city}}, {{$tanggalOnly}}</td>
  </tr>
  <tr>
    <td style="padding:0;">No.  {{$t_surat_peringatan?->nomor}}</td>
  </tr>
</table>

<div></div>

<table cellspacing="0" cellpadding="0" style="font-family:'Times New Roman'; font-size:14px;">
  <tr>
    <td>Hal : <strong> {{$level_sp->value ?? '-'}}</strong></td>
  </tr>
</table>

<div></div>

<table cellspacing="0" cellpadding="0" style="font-family:'Times New Roman'; font-size:12px;">
  <tr>
    <td>Diberikan kepada :</td>
  </tr>
</table>

<div></div>

<table cellspacing="0" style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:11px;">
  <tr>
    <td rowspan="5" style="width: 5%;"></td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_surat_peringatan->m_kary?->nama_lengkap ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jabatan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_surat_peringatan->m_kary?->m_posisi?->name ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Alamat</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_surat_peringatan->m_kary?->address ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Kesalahan</td>
    <td style="width: 3%;">:</td>
   <td>
        @php
            $pelanggaran = $t_surat_peringatan->t_surat_peringatan_d_pelanggaran ?? collect();
        @endphp

        @if($pelanggaran->isNotEmpty())
            @foreach($pelanggaran as $index => $item)
                {{ $index + 1 }}. {{ $item->value }}<br>
            @endforeach
        @else
            -
        @endif
    </td>
  </tr>
  <tr>
    <td style="width: 25%;">Jenis Sanksi :</td>
    <td style="width: 3%;">:</td>
    <td>1. {{$level_sp->value ?? '-'}} atas kesalahan di atas</td>
  </tr>

  <tr>
    <td></td>
  </tr>
</table>

<table cellspacing="0" cellpadding="0" style="width:100%; font-family:'Times New Roman'; font-size:12px;">
  <tr>
    <td>{{$level_sp->value ?? '-'}} ini berlaku selama {{$t_surat_peringatan->masa_berlaku}} bulan terhitung dari tanggal dikeluarkan, apabila yang
      bersangkutan masih melakukan pelanggaran lagi maka perusahaan dapat memberikan Surat Peringatan
      berikutnya atau sesuai dengan Undang - Undang yang berlaku.</td>
  </tr>
  <tr>
    <td></td>
  </tr>
  <tr>
    <td>Dengan adanya {{$level_sp->value ?? '-'}} yang diberikan kepada Saudara/i ini maka manajemen
      berharap agar Saudara/i dapat lebih baik lagi dalam hal kontrol, konsentrasi dan koordinasi tugas di
      lingkungan kerja Saudara/i sehari-hari. atas perhatiannya disampaikan terima kasih.</td>
  </tr>
</table>

<div></div>

<table cellspacing="0" cellpadding="0"
  style="width:100%; margin-top:20px; font-family:'Times New Roman'; font-size:12px; line-height:1.1;">
  <tr>
    <td>Hormat Kami,</td>
  </tr>
  <tr>
    <td>PT Temprina Media Grafika</td>
  </tr>
  <tr>
    <td></td>
  </tr>
  <tr>
    <td></td>
  </tr>
  <tr>
    <td></td>
  </tr>
   <tr>
    <td></td>
  </tr>
   <tr>
    <td></td>
  </tr>
  <tr>
    <td style="text-decoration:underline;">{{$t_surat_peringatan->signature?->nama_lengkap ?? '-'}}</td>
  </tr>
  <tr>
    <td >{{$t_surat_peringatan->signature?->m_posisi?->name ?? '-'}}</td>
  </tr>
</table>

<div></div>

 <!-- TEMBUSAN -->
  <table style="font-family:'Times New Roman'; width:100%; margin-top:20px; font-size: 11px; border-collapse: collapse;">
  <tr>
    <td style="width:15%; vertical-align:top;">Tembusan</td>
    <td style="width:2%; vertical-align:top;">:</td>
    <td style="width:83%; vertical-align:top;">
      @if($tembusan && count($tembusan) > 0)
        @foreach($tembusan as $index => $t)
          {{ $index + 1 }}. {{ $t->value ?? $t['value'] }}<br>
        @endforeach
      @else
        -
      @endif
    </td>
  </tr>
</table>