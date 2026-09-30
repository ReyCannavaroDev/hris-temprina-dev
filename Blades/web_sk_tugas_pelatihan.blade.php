@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? null;
$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;

$materiPelatihan = $t_mutasi?->deskripsi ?? 'Training & Sertifikasi';
$keteranganDetail = $t_mutasi?->keterangan ?? '-';

$sbuCode = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : ($t_mutasi?->m_sub_lama?->m_company?->name ?? 'PT Temprina Media Grafika');
$kotaTerbit = $t_mutasi?->m_sub_lama?->m_branch?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';
@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

  @include('projects.web_sk_kop')

  <!-- JUDUL -->
  <table style="width:100%;margin-top:15px;">
    <tr>
      <td style="width:30%;"></td>
      <td
        style="width:40%;text-align:center;font-weight:bold;font-size:16px;border-bottom:2px solid black;height:28px;">
        SURAT TUGAS
      </td>
      <td style="width:30%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:4px;">
        No. {{$t_mutasi?->nomor ?? '-'}}
      </td>
      <td></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:20px; border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:23%; vertical-align:top; text-align:left">MEMPERHATIKAN</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:75%; vertical-align:top;">
        @if($memperhatikan && count($memperhatikan) > 0)
          <table style="width:100%; border-collapse:collapse;">
            @foreach($memperhatikan as $item)
              <tr>
                <td style="width:3%; vertical-align:top;">•</td>
                <td style="vertical-align:top; text-align:justify; padding-bottom: 4px;">
                  {{ $item->value ?? $item['value'] }}
                </td>
              </tr>
            @endforeach
          </table>
        @else
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              <td style="width:3%; vertical-align:top;">•</td>
              <td style="vertical-align:top; text-align:justify; padding-bottom: 4px;">
                Keputusan hasil rapat / koordinasi manajemen
              </td>
            </tr>
            <tr>
              <td style="width:3%; vertical-align:top;">•</td>
              <td style="vertical-align:top; text-align:justify; padding-bottom: 4px;">
                Peningkatan SDM & produktifitas di PT Temprina Media Grafika
              </td>
            </tr>
          </table>
        @endif
      </td>
    </tr>
  </table>

  <div></div>

  <!-- MEMUTUSKAN SERTA MENETAPKAN -->
  <table style="width:100%;margin-top:16px; font-size:11px;">
    <tr>
      <td style="width:23%;vertical-align:top; text-align:left">MEMUTUSKAN<br>SERTA MENETAPKAN</td>
      <td style="width:2%;vertical-align:top;">:</td>
      <td style="width:75%;">

        <table style="width:100%;border-collapse:collapse;">
          <tr>
            <td style="width:24%;">• Nama</td>
            <td style="width:3%;">:</td>
            <td style="width:73%; font-weight:bold;">{{$t_mutasi?->m_kary?->nama_lengkap ?? '-'}}</td>
          </tr>
          <tr>
            <td>• Tugas</td>
            <td>:</td>
            <td>{{$materiPelatihan}}</td>
          </tr>
          <tr>
            <td>• Keterangan / Jadwal</td>
            <td>:</td>
            <td>{{$keteranganDetail}}</td>
          </tr>
          <tr>
            <td>• Tanggal Tugas</td>
            <td>:</td>
            <td>{{$tanggalIndo}}</td>
          </tr>
          <tr>
            <td>• Penempatan</td>
            <td>:</td>
            <td>{{$t_mutasi?->m_posisi_lama?->name ?? '-'}} - {{$t_mutasi?->m_sub_lama?->name ?? '-'}}</td>
          </tr>
        </table>

      </td>
    </tr>
  </table>

  <p style="text-align:justify; margin-top:20px; font-size:11px;">
    Demikian surat tugas ini kami sampaikan guna diperhatikan serta dilaksanakan dengan penuh tanggung jawab.
  </p>

  <!-- PENUTUP -->
  <table style="width:100%;margin-top:20px; font-size: 11px;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        Dikeluarkan di&nbsp;&nbsp;: {{$kotaTerbit}}<br>
        Pada Tanggal&nbsp;&nbsp;: {{$tanggalTerbit}}
        <div style="margin-top:14px;font-weight:bold;">
          {{$companyName}}
        </div>
        <div></div>
        <div style="margin-top:50px;font-weight:bold;text-decoration:underline;">
          {{$t_mutasi?->signature?->nama_lengkap ?? 'DEVI NOVARIA PURNAMASARI'}}
        </div>
        <div>
          {{$t_mutasi?->signature?->m_posisi?->name ?? 'Asst. Manager Human Capital'}}
        </div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN -->
  <table style="width:100%; margin-top:20px; font-size: 10px; border-collapse: collapse;">
    <tr>
      <td style="width:15%; vertical-align:top;">Tembusan</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:83%; vertical-align:top;">
        @if($tembusan && count($tembusan) > 0)
          @foreach($tembusan as $index => $t)
            {{ $index + 1 }}. {{ $t->value ?? $t['value'] }}<br>
          @endforeach
        @else
          1. Direksi<br>
          2. Keuangan
        @endif
      </td>
    </tr>
  </table>

  @include('projects.web_sk_footer')

</div>
