<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Slip Gaji</title>
  <style>
    /* Aturan margin untuk cetak (menjamin border tidak terpotong) */
    @page {
      margin: 10mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      margin: 0;
      background: #e0e0e0;
    }

    /* Penyesuaian margin agar .slip berada di area cetak yang aman */
    .slip {
      width: 95mm;
      min-height: 138mm;
      border: 1px solid #000;
      padding: 6mm;
      background: white;
      box-sizing: border-box;
      margin: 10mm;
      /* Atur margin di semua sisi untuk memposisikan di kiri atas */
    }

    h2 {
      margin: 0;
      font-size: 14px;
    }

    p {
      margin: 2px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    .page {
      width: 210mm;
      /* A4 */
      height: 297mm;
      /* A4 */
      padding: 5mm;
      box-sizing: border-box;
      background: #e0e0e0;
      display: grid;
      grid-template-columns: 1fr 1fr;
      /* 2 kolom */
      grid-template-rows: 1fr 1fr;
      /* 2 baris */
      gap: 5mm;
      page-break-after: always;
    }

    td {
      padding: 2px 3px;
      vertical-align: top;
    }

    /* Styling untuk tombol cetak */
    .print-button {
      display: block;
      margin: 10px auto;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f0f0f0;
    }

    /* Media Query untuk cetak */
    @media print {
      body {
        background: none;
      }

      .slip-container {
        gap: 4mm;
        padding: 0;
      }

      .slip {
        margin: 1px;
      }

      .print-button {
        display: none;
      }
    }
  </style>
</head>

<body>
  @php
  $req = app()->request;
  use App\Models\CustomModels\t_final_gaji;
  use App\Models\CustomModels\t_final_gaji_det;
  use App\Models\CustomModels\t_final_gaji_det_rincian;
  use App\Models\CustomModels\m_kary;
  use App\Models\CustomModels\m_company;
  use App\Models\CustomModels\m_subcomp;
  use Illuminate\Support\Carbon;
  Carbon::setLocale('id');


  $t_final_gaji_det = t_final_gaji_det::whereHas('t_final_gaji', function ($q) use ($req) {
  $q->where('id', $req->f_id);
  })
  ->when($req->m_kary_id, function ($q) use ($req) {
  $q->where('m_kary_id', $req->m_kary_id);
  })
  ->get();

  //$tgl = $t_final_gaji_det[0]?->periode;
  //$periode = $tgl ? Carbon::parse($tgl)->translatedFormat('F Y') : '-';

  $tgl = $t_final_gaji_det[0]?->periode;
  
  if ($tgl) {
      $parts = explode(' - ', $tgl);
      
      $dateString = str_replace('/', '-', $parts[0]); 
      
      $periode = Carbon::parse($dateString)->translatedFormat('F Y');
  } else {
      $periode = '-';
  }

  //dd($t_final_gaji_det);

  @endphp
  @foreach($t_final_gaji_det as $i => $data)
  @if($i % 4 == 0)
  <div class="page"> {{-- buka halaman baru setiap 4 slip --}}
    @endif
    @php
    $rincian = $data->t_final_gaji_det_rincian;
    $company = m_subcomp::find($data->m_kary?->m_subcomp_id)?->m_company()?->first() ?? null;
    //dd($rincian->where('name', 'gaji_pokok')?->first()?->value);
    //dd($data->m_kary?->status_kary?->value ?? '-');
    @endphp
    <div class="slip">

      <div style="text-align: center; margin-bottom: 10px;">
        <h2>SLIP GAJI</h2>
        <p>{{ $periode }}</p>
        <p style="font-weight: bold;">{{$company?->name ?? 'TEMPRINA MEDIA GRAFIKA'}}</p>
        <p>{{'Jl.' . $company?->address . ', ' . $company?->district?->value . ' ' . $company?->city?->value ?? 'Jl. Satria Darma II No.82 KP. Sawah Bogo, Tambun Selatan Bekasi - 17510'}}</p>
      </div>
     
      <div style="margin-bottom: 10px;">
        <table>
          <tr>
            <td><b>Nama Pegawai</b></td>
            <td>: {{$data->m_kary->nama_lengkap}}</td>
          </tr>
          <tr>
            <td><b>Status Pegawai</b></td>
            <td>: {{$data->m_kary?->status_kary?->value ?? '-'}}</td>
          </tr>
        </table>
      </div>

      <hr>
      <hr>

      <div style="margin-bottom: 10px;">
        <b>Upah Tetap :</b>
        <table style="margin-top: 3px;">
          <tr>
            <td>- Upah Pokok</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name', 'gaji_pokok')?->first()?->value ??
              0), 0, ',', '.')}}</td>
          </tr>
          <tr>
            <td>- Tunjangan Transport</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name',
              'tunjangan_transport')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
          <tr>
            <td>- Tunjangan Produktifitas</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name',
              'tunjangan_produktifitas')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
        </table>
      </div>

      <div style="margin-bottom: 10px;">
        <b>Tunjangan-tunjangan :</b>
        <table style="margin-top: 3px;">
          <tr>
            <td>- Tunjangan Jabatan *</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name',
              'tunjangan_posisi')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
          <tr>
            <td>- Tunjangan Fungsional *</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name',
              'tunjangan_fungsional')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
          <tr>
            <td>- Tunjangan Kemahalan *</td>
            <td style="text-align: right;">{{ number_format(($rincian->where('name',
              'tunjangan_kemahalan')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
        </table>
      </div>

      <div style="margin-bottom: 8px;">
        @php
        $thp = ($rincian->where('name', 'gaji_pokok')?->first()?->value ?? 0) + ($rincian->where('name',
        'tunjangan_transport')?->first()?->value ?? 0) + ($rincian->where('name',
        'tunjangan_produktifitas')?->first()?->value ?? 0) + ($rincian->where('name',
        'tunjangan_posisi')?->first()?->value ?? 0) + ($rincian->where('name', 'tunjangan_fungsional')?->first()?->value
        ?? 0) + ($rincian->where('name', 'tunjangan_kemahalan')?->first()?->value ?? 0);
        @endphp
        <table>
          <tr>
            <td><b>TOTAL GAJI (THP)</b></td>
            <td style="text-align: right; font-weight: bold;">{{number_format($thp, 0, ',', '.')}}</td>
          </tr>
        </table>
      </div>

      <div style="margin-bottom: 8px;">
        @php
        $komponenTHP = [
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_produktifitas',
        'tunjangan_posisi',
        'tunjangan_fungsional',
        'tunjangan_kemahalan',
        ];

        $lain = ($rincian ?? collect())
        ->whereNotIn('name', $komponenTHP)
        ->where('factor', '+');
        @endphp

        @if($lain->isNotEmpty())
        <table>
          @foreach($lain as $d)
          <tr>
            <td>- {{ $d->name ?? '' }}</td>
            <td style="text-align: right;">
              {{ number_format($d->value ?? 0, 0, ',', '.') }}
            </td>
          </tr>
          @endforeach
        </table>
        @else
        <p>Tidak ada data tambahan</p>
        @endif
      </div>


      <div style="margin-bottom: 10px;">
        <b>Potongan-potongan</b>
        @php
        $potongan = ($rincian ?? collect())->where('factor', '-') ?? collect();
        @endphp
        @if($potongan->isNotEmpty())
        <table style="margin-top: 3px;">
          @foreach($potongan as $p)
          <tr>
            <td>- {{ $p->name ?? '' }}</td>
            <td style="text-align: right;">
              {{ number_format($p->value ?? 0, 0, ',', '.') }}
            </td>
          </tr>
          @endforeach
        </table>
        @else
        <p style="margin-top: 3px;">Tidak ada potongan</p>
        @endif
      </div>


      <div>
        <table>
          <tr>
            <td style="font-weight: bold;">Jumlah Diterima</td>
            <td style="text-align: right; font-weight: bold;">{{number_format(($rincian->where('name', 'Total Gaji')?->first()?->value ?? 0), 0, ',', '.')}}</td>
          </tr>
        </table>
        <p>Ket : Dokumen ini sah tanpa memerlukan tanda tangan.</p>

      </div>

    </div>
    @if($i % 4 == 3 || $loop->last)
  </div> {{-- tutup halaman setelah 4 slip atau terakhir --}}
  @endif
  @endforeach
  <button class="print-button" onclick="window.print()">Print or Save as PDF</button>
</body>

</html>