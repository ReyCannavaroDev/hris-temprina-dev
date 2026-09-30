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

//dd($memperhatikan);
//dd($terbitDate);
//dd($t_mutasi);


$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

  @include('projects.web_sk_kop')

  <!-- JUDUL -->
  <table style="width:100%;margin-top:20px;">
    <tr>
      <td style="width:25%;"></td>
      <td
        style="width:50%;text-align:center;font-weight:bold;font-size:15px;border-bottom:2px solid black;height:28px;letter-spacing:1px;">
        S U R A T   K E P U T U S A N
      </td>
      <td style="width:25%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-size:11px;padding-top:4px;">
        No. {{$t_mutasi->nomor ?? '-'}}
      </td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:2px;">
        Direksi {{$companyName}}
      </td>
      <td></td>
    </tr>
  </table>

  <!-- SUB JUDUL -->
  <table style="width:100%;margin-top:16px;">
    <tr>
      <td style="width:40%;"></td>
      <td style="width:20%;text-align:center;font-size:11px;">
        Tentang
      </td>
      <td style="width:40%;"></td>
    </tr>
    <tr>
      <td style="width:20%;"></td>
      <td style="width:60%;text-align:center;font-size:11px;font-weight:bold;padding-top:4px;">
        PENGANGKATAN KARYAWAN TETAP Th. {{$carbonDate->year}}
      </td>
      <td style="width:20%;"></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:16px; border-collapse:collapse;">
    <tr>
      <td style="width:33%; vertical-align:top; text-align:left;">Direksi PT Temprina Media Grafika</td>
    </tr>
  </table>

  <table style="width:100%; margin-top:16px; border-collapse:collapse;">
    <tr>
      <td style="width:23%; vertical-align:top; text-align:left;">MENIMBANG</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:75%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td style="width:80%; vertical-align:top;">Bahwa nama karyawan {{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}<br>{{$t_mutasi->status_kary_lama?->value ?? '-'}} - PT Temprina Media Grafika</td>
            <td style="vertical-align:top; text-align:justify; padding-bottom: 4px;">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <div></div>

  <!-- MEMUTUSKAN -->
  <table style="width:100%;margin-top:16px;">
    <tr>
      <td style="width:23%; vertical-align:top; text-align:left;">MENGINGAT
      </td>
      <td style="width:2%;vertical-align:top;">:</td>
      <td style="width:76%;">

        <table style="width:100%;border-collapse:collapse;">
           @foreach($memperhatikan as $item)
            <tr>
              <td style="width:3%; vertical-align:top;">{{ $loop->iteration }}</td>
              <td style="vertical-align:top; text-align:justify; padding-bottom: 4px; width: 100%">
                {{ $item->value ?? $item['value'] }}
              </td>
            </tr>
            @endforeach
        </table>

      </td>
    </tr>
  </table>

  <div></div>

  <!-- JUDUL MEMUTUSKAN -->
  <table style="width:100%; margin-top:20px;">
    <tr>
      <td style="text-align:center; font-size:12px; font-weight:bold; letter-spacing:1px;">
        MEMUTUSKAN
      </td>
    </tr>
  </table>


  <!-- ISI MEMUTUSKAN -->
  <table style="width:100%; margin-top:18px; border-collapse:collapse;">
    <tr>
      <td style="width:23%; vertical-align:top; text-align:left;">MENETAPKAN
      </td>
    </tr>
  </table>

  <table style="width:100%; margin-top:16px; border-collapse:collapse;">
    <tr>
      <td style="width:23%; vertical-align:top; text-align:left; font-size:11px">Pertama</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:75%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td rowspan="10" style="width:5%; vertical-align:top;">~</td>
            <td style="width:30%; vertical-align:top; text-align:justify; padding-bottom: 4px;">Mengangkat Karyawan</td>
            <td style="width:5%; vertical-align:top;">:</td>
            <td style="width:60%; vertical-align:top; text-align:left;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
          </tr>
          <tr>
            <td style="width:30%; vertical-align:top; text-align:justify; padding-bottom: 4px;">Tanggal Lahir</td>
            <td style="width:5%; vertical-align:top;">:</td>
            <td style="width:60%; vertical-align:top; text-align:left;">{{$t_mutasi->m_kary?->tgl_lahir ?? '-'}}</td>
          </tr>
          <tr>
            <td style="width:30%; vertical-align:top; text-align:justify; padding-bottom: 4px;">Bagian dan Tempat</td>
            <td style="width:5%; vertical-align:top;">:</td>
            <td style="width:60%; vertical-align:top; text-align:left;">{{$t_mutasi->m_posisi_baru?->name ?? '-'}} - {{$t_mutasi->m_branch_baru?->name}}</td>
          </tr>
          <tr>
            <td style="width:30%; vertical-align:top; text-align:justify; padding-bottom: 4px;">Karyawan Tetap</td>
            <td style="width:5%; vertical-align:top;">:</td>
            <td style="width:60%; vertical-align:top; text-align:left;">{{$tanggalIndo ?? '-'}}</td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td style="width:23%; vertical-align:top; text-align:left; font-size:11px">Kedua</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:75%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td rowspan="10" style="width:5%; vertical-align:top;">~</td>
            <td style="width:95%; vertical-align:top; text-align:left;">Karyawan yang namanya tersebut di atas harus
              memenuhi semua kewajiban
              dan tanggung jawab yang ada serta mentaati segala peraturan atau hukum
              yang ada / berlaku dan tunduk terhadap keputusan Perusahaan.</td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td style="width:23%; vertical-align:top; text-align:left; font-size:11px">Ketiga</td>
      <td style="width:2%; vertical-align:top;">:</td>
      <td style="width:75%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td rowspan="10" style="width:5%; vertical-align:top;">~</td>
            <td style="width:95%; vertical-align:top; text-align:left;">Surat keputusan ini berlaku sejak tanggal
              ditetapkan. Jika di kemudian hari
              terdapat kesalahan dalam surat keputusan ini, maka akan diadakan perbaikan
              seperlunya.</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <div></div>
  <div></div>



  <!-- PENUTUP -->
  <table style="width:100%;margin-top:26px;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        Ditetapkan di&nbsp;&nbsp;: {{$kotaTerbit}}<br>
        Pada Tanggal&nbsp;&nbsp;: {{$tanggalTerbit}}
        <div style="margin-top:18px;font-weight:bold;">
          {{$companyName}}
        </div>
        <div></div>
        <div style="margin-top:50px;font-weight:bold;text-decoration:underline;">
            {{$t_mutasi->signature?->nama_lengkap ?? ''}}
        </div>
        <div>
            {{$t_mutasi->signature?->m_posisi?->name ?? ''}}
        </div>
      </td>
    </tr>
  </table>

  @include('projects.web_sk_footer')

</div>