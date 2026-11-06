@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_penyelesaian_perdin;
use App\Models\CustomModels\m_kary;
Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_penyelesaian_perdin = t_penyelesaian_perdin::find($id);

$carbonDate = Carbon::parse($t_penyelesaian_perdin->tgl);
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

$terbitDate = Carbon::parse($t_penyelesaian_perdin?->updated_at ?? $t_penyelesaian_perdin?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$start = Carbon::parse($t_penyelesaian_perdin->t_perdin->date_from);
$end = Carbon::parse($t_penyelesaian_perdin->t_perdin->date_to);

$selisihHari = $start->diffInDays($end) + 1;

$datefrom = $start->translatedFormat('d F Y');
$dateto = $end->translatedFormat('d F Y');

$rician_biaya = $t_penyelesaian_perdin->t_penyelesaian_perdin_det;
$rician_kegiatan = $t_penyelesaian_perdin->t_penyelesaian_perdin_d_laporan;

//dd($rician_biaya);

$creator_id = $t_penyelesaian_perdin->creator_id;
$kary_creator = m_kary::whereHas('default_users', function($q) use ($creator_id){
  $q->where('id', $creator_id);
})->first();

@endphp
<div style="font-family: 'Times New Roman', serif; width: 90%; margin: auto; font-size: 12px; line-height: 1.5;">
  <h2 style="text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px;">RINCIAN PERDIN</h2>

  <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 11px;">
    <tr>
      <td style="width: 1%;"></td>
      <td style="width: 10%;">Nama</td>
      <td style="width: 2%; text-align: center;">:</td>
      <td style="width: 86%;">{{$t_penyelesaian_perdin->m_kary?->nama_lengkap ?? '-'}} ( {{$t_penyelesaian_perdin->t_perdin->m_posisi->name ?? ''}} )</td>
      <td style="width: 1%;"></td>
    </tr>
    <tr>
      <td style="width: 1%;"></td>
      <td>Tujuan</td>
      <td style="text-align: center;">:</td>
      <td>{{$t_penyelesaian_perdin?->t_perdin?->tempat_tujuan ?? '-'}}</td>
      <td style="width: 1%;"></td>
    </tr>
    <tr>
      <td style="width: 1%;"></td>
      <td>Tanggal</td>
      <td style="text-align: center;">:</td>
      <td>{{$datefrom}} s/d {{$dateto}} ({{$selisihHari}} Hari)</td>
      <td style="width: 1%;"></td>
    </tr>
  </table>

  <h3 style="font-size: 11px; font-weight: bold; margin-top: 15px;">I. PERINCIAN PERDIN DAN BIAYA - BIAYA :</h3>

  <table style="width: 100%; border-collapse: collapse; font-size:10px;">
    @php $totalI = 0; @endphp
    @foreach($t_penyelesaian_perdin->t_penyelesaian_perdin_det as $index => $det)
        @php $totalI += $det->total; @endphp
        <tr>
            <td style="width:6%"></td>
            <td style="width:3%">{{ $index + 1 }}</td>
            <td style="width:45%">
                {{ $det->komponen }} 
                @if($det->jumlah > 0) , {{ $det->jumlah }} hari @endif
            </td>
            <td style="width:20%;">
                @if($det->nominal > 0) @ Rp. {{ number_format($det->nominal, 0, ',', '.') }} @endif
            </td>
            <td style="width:5%">Rp</td>
            <td style="width:5%"></td>
            <td style="width:15%; text-align: right;">{{ number_format($det->total, 0, ',', '.') }},-</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="4"></td>
        <td style="border-bottom: 1px solid black;"></td>
        <td style="border-bottom: 1px solid black;"></td>
        <td style="border-bottom: 1px solid black;"></td>
    </tr>

    <tr>
        <td colspan="4"></td>
        <td style="font-weight: bold;">Rp</td>
        <td></td>
        <td style="font-weight: bold; text-align: right;">{{ number_format($totalI, 0, ',', '.') }},-</td>
    </tr>

    <tr>
        <td colspan="4" style="font-size: 11px; font-weight: bold; padding-top: 15px;">II. KAS BON SEMENTARA :</td>
        <td style="font-weight: bold; padding-top: 15px;">Rp</td>
        <td style="padding-top: 15px;"></td>
        <td style="font-weight: bold; text-align: right; padding-top: 15px;">
            {{ number_format($t_penyelesaian_perdin->nominal_kbs ?? 0, 0, ',', '.') }},-
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">{{ $t_penyelesaian_perdin->no_kbs ?? '-' }}</td>
        <td style="width:20%;">@ {{ number_format($t_penyelesaian_perdin->nominal_kbs ?? 0, 0, ',', '.') }}</td>
        <td colspan="3"></td>
    </tr>

    <tr>
        <td colspan="4"></td>
        <td style="border-bottom: 1px solid black;"></td>
        <td style="border-bottom: 1px solid black;"></td>
        <td style="border-bottom: 1px solid black;"></td>
    </tr>

    @php 
        $selisih = $totalI - ($t_penyelesaian_perdin->nominal_kbs ?? 0);
    @endphp
    <tr>
        <td colspan="4" style="font-size: 11px; font-weight: bold; padding-top: 15px;">III. LEBIH/KURANG (I - II) :</td>
        <td style="font-weight: bold; padding-top: 15px;">Rp</td>
        <td style="padding-top: 15px;"></td>
        <td style="font-weight: bold; text-align: right; padding-top: 15px;">
            {{ number_format(abs($selisih), 0, ',', '.') }},-
        </td>
    </tr>
</table>

  <br><br><br>
  <table>
    <tr>
      <td colspan="5" style="font-size: 10px;">{{$t_penyelesaian_perdin->t_perdin->m_kary?->m_subcomp?->city?->value ?? '-'}}, {{$tanggalTerbit}}<br>
      </td>
    </tr>
  </table>
  <table style="font-size: 10px;">
    <tr>
      <td style="width: 20%">Karyawan</td>
      <td style="width: 15%"></td>
      <td style="width: 28%">{{$t_penyelesaian_perdin?->t_perdin?->m_atasan?->m_posisi?->name ?? '-'}}</td>
      <td style="width: 15%"></td>
      <td style="width: 17%">{{$kary_creator->m_posisi?->name ?? '-'}}</td>
      <td></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td style="width: 20%">({{$t_penyelesaian_perdin?->m_kary?->nama_lengkap ?? '-'}})</td>
      <td style="width: 15%"></td>
      <td style="width: 28%">({{$t_penyelesaian_perdin?->t_perdin?->m_atasan?->nama_lengkap ?? '-'}})</td>
      <td style="width: 15%"></td>
      <td style="width: 17%">({{$kary_creator->nama_lengkap ?? '-'}})</td>
      <td ></td>
    </tr>
  </table>
</div>

<div style="page-break-after: always;"></div>

<div style="font-family: Arial, Helvetica, sans-serif; font-size:12px;">

<h3 style="text-align:center;">LAPORAN PEKERJAAN PERJALANAN DINAS</h3>

<table style="width:100%; font-size:12px;">
    <tr>
    <td></td>
    <td></td>
    <td colspan="4"></td>
  </tr>
  <tr>
    <td style="width:150px;">Nama</td>
    <td style="width:10px;">:</td>
    <td colspan="4">{{$t_penyelesaian_perdin->m_kary?->nama_lengkap ?? '-'}}</td>
  </tr>

  <tr>
    <td>Tempat Tugas</td>
    <td>:</td>
    <td colspan="4">{{$t_penyelesaian_perdin->t_perdin?->tempat_tujuan ?? '-'}}</td>
  </tr>

  <tr>
    <td>Pekerjaan</td>
    <td>:</td>
    <td colspan="4">{{$t_penyelesaian_perdin->t_perdin?->tugas ?? '-'}}</td>
  </tr>

  <tr>
    <td>Waktu</td>
    <td>:</td>
    <td colspan="4">{{$selisihHari}} Hari</td>
  </tr>

  <tr>
    <td>Mulai</td>
    <td>:</td>
    <td colspan="4">{{$datefrom}}</td>
  </tr>

  <tr>
    <td>Sampai</td>
    <td>:</td>
    <td colspan="4">{{$dateto}}</td>
  </tr>

  <tr>
    <td></td>
    <td></td>
    <td colspan="4"></td>
  </tr>
</table>

<br>

<table>
  <tr>
    <td>Laporan Pekerjaan :</td>
  </tr>
</table>

<br>

<table style="width:100%; border-collapse:collapse; font-size:11px;">
  <tr>
    <th style="border:1px solid #000; width:10%;">Hari/Tgl</th>
    <th style="border:1px solid #000; width:14%;">Kegiatan/Hari</th>
    <th style="border:1px solid #000; width:23%;">Person (Yang ditemui)</th>
    <th style="border:1px solid #000; width:27%;">Hasil Koordinasi/Kegiatan</th>
    <th style="border:1px solid #000; width:26%;">TTD/Diverifikasi</th>
  </tr>

  @foreach($rician_kegiatan as $rk)
  <tr>
    <td style="border:1px solid #000; height:25px;">{{$rk?->tgl ?? '-'}}</td>
    <td style="border:1px solid #000;">{{$rk?->kegiatan ?? '-'}}</td>
    <td style="border:1px solid #000;">{{$rk?->person ?? '-'}}</td>
    <td style="border:1px solid #000;">{{$rk?->hasil ?? '-'}}</td>
    <td style="border:1px solid #000;">{{$rk?->verifikasi?->nama_lengkap ?? '-'}}</td>
  </tr>
  @endforeach

  <tr>
    <td></td>
    <td></td>
    <td ></td>
    <td ></td>
    <td ></td>
  </tr>

</table>

<br>

<table>
    <tr>
      <td colspan="5" style="font-size: 10px;">{{$t_penyelesaian_perdin->t_perdin->m_kary?->m_subcomp?->city?->value ?? '-'}}, {{$tanggalTerbit}}<br>
      </td>
    </tr>
  </table>
  <table style="font-size: 10px;">
    <tr>
      <td style="width: 20%">Karyawan</td>
      <td style="width: 15%"></td>
      <td style="width: 28%">{{$t_penyelesaian_perdin?->t_perdin?->m_atasan?->m_posisi?->name ?? '-'}}</td>
      <td style="width: 15%"></td>
      <td style="width: 17%">{{$kary_creator->m_posisi?->name ?? '-'}}</td>
      <td></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td colspan="5"></td>
    </tr>
    <tr>
      <td style="width: 20%">({{$t_penyelesaian_perdin?->m_kary?->nama_lengkap ?? '-'}})</td>
      <td style="width: 15%"></td>
      <td style="width: 28%">({{$t_penyelesaian_perdin?->t_perdin?->m_atasan?->nama_lengkap ?? '-'}})</td>
      <td style="width: 15%"></td>
      <td style="width: 17%">({{$kary_creator->nama_lengkap ?? '-'}})</td>
      <td ></td>
    </tr>
  </table>

</div>