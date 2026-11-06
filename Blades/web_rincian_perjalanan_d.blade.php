@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_rencana_perdin;
Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_rencana_perdin = t_rencana_perdin::find($id);

$carbonDate = Carbon::parse($t_rencana_perdin->tgl);
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

$terbitDate = Carbon::parse($t_rencana_perdin?->updated_at ?? $t_rencana_perdin?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$datefrom = Carbon::parse($t_rencana_perdin->t_perdin->date_from);
$datefrom = $datefrom->translatedFormat('d F Y');

$dateto = Carbon::parse($t_rencana_perdin->t_perdin->date_to);
$dateto = $dateto->translatedFormat('d F Y');

$rician_biaya = $t_rencana_perdin->t_rencana_perdin_det;
//dd($rician_biaya);



@endphp

<div></div>
<div style="width:700px; margin:0 auto; font-family:'Times New Roman', serif; font-size:10px; line-height:1.6;">
  <!-- Header -->
  <table style="width:100%; border-collapse:collapse; text-align:center; margin-bottom:10px;">
    <tr>
      <td style="width:15%;"></td>
      <td style="width:70%; font-weight:bold; font-size:16px; padding-bottom:5px;">
        RENCANA BIAYA PERJALANAN DINAS
      </td>
      <td style="width:15%;"></td>
    </tr>
  </table>

  <table>
    <tr>
      <td></td>
    </tr>
  </table>

  <!-- Data pegawai -->
  <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
    <tr><td style="width:130px;">Nama</td><td style="width:10px;">:</td><td>{{$t_rencana_perdin->m_kary?->nama_lengkap ?? '-'}}</td></tr>
    <tr><td>Jabatan</td><td>:</td><td>{{$t_rencana_perdin->t_perdin?->m_posisi?->name ?? '-'}}</td></tr>
    <tr><td>Tanggal Berangkat</td><td>:</td><td>{{$datefrom ?? '-'}}</td></tr>
    <tr><td>Tanggal Kembali</td><td>:</td><td>{{$dateto ?? '-'}}</td></tr>
    <tr><td>Tujuan Perjalanan Dinas</td><td>:</td><td>{{$t_rencana_perdin?->t_perdin?->tempat_tujuan ?? '-'}}</td></tr>
    <tr><td>Tugas</td><td>:</td><td>{{$t_rencana_perdin?->t_perdin?->tugas ?? '-'}}</td></tr>
  </table>

    <!-- Rincian Biaya -->
 <table style="width:100%; border-collapse:collapse; margin-top:15px; font-size:10px;">
    <tr>
        <td colspan="8" style="font-weight:bold; padding-bottom:5px;">Rincian Biaya :</td>
    </tr>

    @php 
    $grandTotal = 0; 
    @endphp
    @foreach($t_rencana_perdin->t_rencana_perdin_det as $index => $det)
        @php $grandTotal += $det->total; @endphp
        <tr>
            <td style="width:3%; text-align:right; vertical-align: top;">{{ $index + 1 }}.</td>
            <td style="width:28%; vertical-align: top;">{{ $det->komponen }}</td>
            <td style="width:2%; text-align:center; vertical-align: top;">:</td>
            <td style="width:12%; text-align:center; vertical-align: top;">
                {{ $det->jumlah }} Hari x Rp.
            </td>
            <td style="width:10%; text-align:right; vertical-align: top;">
                {{ number_format($det->nominal, 0, ',', '.') }}
            </td>
            <td style="width:5%;"></td>
            <td style="width:10%; text-align:right; vertical-align: top;">Rp.</td>
            <td style="width:15%; text-align:right; vertical-align: top;">
                {{ number_format($det->total, 0, ',', '.') }}
            </td>
        </tr>
        @if($det->catatan)
        <tr>
            <td></td>
            <td colspan="7" style="font-style: italic; color: #555; padding-bottom: 5px;">
                Catatan: {{ $det->catatan }}
            </td>
        </tr>
        @endif
    @endforeach

    <tr>
        <td colspan="6"></td>
        <td style="font-weight:bold; text-align:right; padding-top:5px; border-top:1px solid #000;">TOTAL</td>
        <td style="text-align:right; font-weight:bold; padding-top:5px; border-top:1px solid #000;">
            Rp. {{ number_format($grandTotal, 0, ',', '.') }}
        </td>
    </tr>
</table>


  <!-- Pembebanan -->
  <table style="width:100%; border-collapse:collapse; margin-top:15px;">
    <tr>
      <td colspan="3" style="font-weight:bold;">Pembebanan :</td>
    </tr>
    <tr>
      <td style="width:150px;">Nama Pabrik / Proyek</td>
      <td style="width:10px;">:</td>
      <td>-</td>
    </tr>
    <tr>
      <td colspan="3">*) Harus dibuatkan rekening dan diserahkan ke atasan</td>
    </tr>
  </table>

  <!-- Tanda tangan -->
  <br><br>
  <table style="width:100%; border-collapse:collapse; margin-top:20px;">
    <tr>
      <td style="width:50%; vertical-align:top;">
        {{$t_rencana_perdin->t_perdin->m_kary?->m_subcomp?->city?->value ?? '-'}}, {{$tanggalTerbit}}<br>
        Pemohon,<br><br><br><br>
        <u>{{$t_rencana_perdin->m_kary?->nama_lengkap ?? '-'}}</u><br>
        Karyawan
      </td>
      <td style="width:50%; vertical-align:top; text-align:left;">
        <br><br>Menyetujui,<br>
        <br><br><br>
        <u>{{$t_rencana_perdin->t_perdin->m_atasan?->nama_lengkap ?? '-'}}</u><br>
        Atasan
      </td>
    </tr>
  </table>

  <!-- <div style="margin-top:20px; text-align:left; font-size:10px;">
    <span style="font-style:italic;">Copy :</span> ARSY AMALIA
  </div> -->
</div>
