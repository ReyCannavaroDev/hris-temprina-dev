@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id');
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_mutasi->t_mutasi_d_tembusan ?? null;
$memperhatikan = $t_mutasi->t_mutasi_d_memperhatikan ?? null;

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

@endphp

<div style="font-family:'Times New Roman',serif;width:85%;margin:auto;font-size:12px;line-height:1.6;">

  @include('projects.web_sk_kop')

  <table style="width:100%;margin-top:20px;">
    <tr>
      <td style="width:30%;"></td>
      <td
        style="width:40%;text-align:center;font-weight:bold;font-size:15px;border-bottom:2px solid black;height:28px;">
        SURAT KEPUTUSAN
      </td>
      <td style="width:30%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:4px;">
        No. {{$t_mutasi?->nomor}}
      </td>
      <td></td>
    </tr>
  </table>

  <div></div>

  <table style="width:100%;margin-top:18px;">
    <tr>
      <td style="width:40%;"></td>
      <td style="width:25%;text-align:center;font-size:11px;">
        TENTANG PENAMBAHAN TUGAS
      </td>
      <td style="width:40%;"></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:20px; border-collapse:collapse; table-layout:fixed;">
    <tr>
      <td rowspan="2" style="width:140px; text-align:center; padding:4px;">
        MEMPERHATIKAN
      </td>
      <td rowspan="2" style="width:20px; text-align:center; padding:4px;">
        :
      </td>
      <td style="font-size: 11px; width:auto;">
        @if($memperhatikan && count($memperhatikan) > 0)
        <table style="width:100%; border-collapse:collapse;">
          @foreach($memperhatikan as $item)
          <tr>
            <td style="width:3%; vertical-align:top;">•</td>
            <td style="vertical-align:top; text-align:justify; padding-bottom: 4px; width:100%">
              {{ $item->value ?? $item['value'] }}
            </td>
          </tr>
          @endforeach
        </table>
        @else
        -
        @endif
      </td>
    </tr>
  </table>

  <table style="width:100%; margin-top:20px; border-collapse:collapse; table-layout:fixed;">
    <tr>
      <td rowspan="12" style="width:140px; text-align:center; padding:4px;">
        MEMUTUSKAN SERTA MENETAPKAN
      </td>
      <td rowspan="12" style="width:20px; text-align:center; padding:4px;">
        :
      </td>
      <td colspan="3" style="font-size: 11px; width:auto;">
          Terhitung efektif {{$tanggalIndo}} kepada:
      </td>
    </tr>
    <tr>
      <td style="font-size: 11px; width:15%; text-align:left;">
         Nama
      </td>
      <td style="font-size: 11px; width:3%;">
        :
      </td>
      <td style="font-size: 11px; width:auto;">
          {{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}
      </td>
    </tr>
    <tr>
      <td style="font-size: 11px; width:15%; text-align:left;">
        Bagian
      </td>
      <td style="font-size: 11px; width:3%;">
        :
      </td>
      <td style="font-size: 11px; width:auto;">
          {{$t_mutasi->m_posisi_lama?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_lama?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_lama?->address ?? '-'}}
      </td>
    </tr>
    <!-- Point 2 -->
    <tr>
      <td colspan="3" style="font-size:11px; width:auto;">
        <span style="">2. Tambahan tugas :</span>
      </td>
    </tr>
    <tr>
      <td style="font-size: 11px; width:25%; text-align:center;">
        {{$t_mutasi->m_posisi_baru?->name ?? '-'}} - 
                {{$t_mutasi->m_sub_baru?->name ?? '-'}}<br>
      </td>
    </tr>


    <!-- Point 3 -->
    <tr>
      <td colspan="3" style="font-size:11px; width:auto;">
        <span style="">Keputusan ini berlaku sejak tanggal efektif dan akan ditinjau kembali jika diperlukan.</span>
      </td>
    </tr>
  </table>

  <div></div>

  <!-- PENUTUP -->
  <table style="width:100%;margin-top:40px;">
    <tr>
      <td rowspan="7" style="width:60%;margin-top:40px;"></td>
      <td rowspan="7" style="width:2%;margin-top:40px;"></td>
      <td style="width:auto;">Dikeluarkan di : {{$kotaTerbit}}</td>
    </tr>
    <tr>
      <td> Pada Tanggal : {{$tanggalTerbit}}</td>
    </tr>
    <tr>
      <td><strong>{{$companyName}}</strong></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
      <td style="border-bottom: 2px solid black;width:22%"> {{$t_mutasi->signature?->nama_lengkap ?? ''}} </td>
      <td style=""></td>
    </tr>
    <tr>
      <td> {{$t_mutasi->signature?->m_posisi?->name ?? ''}} </td>
    </tr>
  </table>
  <div></div>

  <!-- TEMBUSAN -->
  <table style="width:100%;margin-top:30px;font-size:11px;border-collapse:collapse;">
    <tr>
      <td style="width:15%;vertical-align:top;">Tembusan</td>
      <td style="width:3%;vertical-align:top;">:</td>
      <td style="width:82%;vertical-align:top;">
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

  @include('projects.web_sk_footer')

</div>