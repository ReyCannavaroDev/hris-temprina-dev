@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
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

//dd($memperhatikan);
//dd($terbitDate);
//dd($t_mutasi);

$jadwalLama = t_jadwal_kerja_d_hari_n::whereHas('t_jadwal_kerja_n', function($q) use ($t_mutasi){
  $q->where('id', $t_mutasi?->jadwal_kerja_lama_id);
})->first();

$jadwalBaru = t_jadwal_kerja_d_hari_n::whereHas('t_jadwal_kerja_n', function($q) use ($t_mutasi){
  $q->where('id', $t_mutasi?->jadwal_kerja_baru_id);
})->first();


@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

  <!-- JUDUL -->
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

  <table style="width:100%; margin-top:18px; border-collapse: collapse;">
    <tr>
      <td style="width:25%;"></td>
      
      <td style="width:50%; text-align:center; font-size:11px; font-weight:bold; line-height:1.4;">
        TENTANG <br> 
        MUTASI TEMPAT TUGAS
      </td>
      
      <td style="width:25%;"></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:18px; border-collapse:collapse; font-size:11px;">
  <tr>
    <td style="width:23%; vertical-align:top; text-align:center">MEMPERHATIKAN</td>
    <td style="width:2%; vertical-align:top;">:</td>
    <td style="width:75%; vertical-align:top;">
      {{-- Cek apakah data tersedia dan berupa array/collection --}}
      @if($memperhatikan && count($memperhatikan) > 0)
        <table style="width:100%; border-collapse:collapse;">
          @foreach($memperhatikan as $item)
            <tr>
              <td style="width:3%; vertical-align:top;">•</td>
              <td style="vertical-align:top; text-align:justify; padding-bottom: 4px; width: 100%">
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

  <br>
  <!-- MEMUTUSKAN -->
  <table style="width:100%;margin-top:16px;font-size:11px;border-collapse:collapse;">
    <tr>
      <td style="width:22%;vertical-align:top;text-align:center">MEMUTUSKAN<br>SERTA MENETAPKAN</td>
      <td style="width:2%;vertical-align:top;">:</td>
      <td style="width:76%;">

        <table style="width:100%;border-collapse:collapse;font-size:11px;">

          <!-- POIN 1 -->
          <tr>
            <td style="width:4%;vertical-align:top;">1.</td>
            <td style="width:96%;">
              Terhitung efektif {{$tanggalIndo}} kepada:
            </td>
          </tr>

          <tr>
            <td></td>
            <td>
              <table style="width:100%;margin-top:6px;font-size:10px;line-height:1.3;">
                <tr>
                  <td style="width:18%;">Nama</td>
                  <td style="width:3%;">:</td>
                  <td style="width:79%;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
                </tr>
              </table>
            </td>
          </tr>

          <br>


          <!-- TEMPAT TUGAS LAMA -->
          <tr>
            <td></td>
            <td style="padding-top:6px; font-size: 10px;">
              Tempat Tugas Lama:
            </td>
          </tr>

          <tr>
            <td></td>
            <td>
              <table style="width:100%;margin-top:4px;font-size:10px;">
                <tr>
                  <td style="width:18%;">Jabatan</td>
                  <td style="width:3%;">:</td>
                  <td style="width:79%;">
                     {{$t_mutasi->m_posisi_lama?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_lama?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_lama?->address ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Jam Kerja</td>
                  <td>:</td>
                  <td>{{ $jadwalLama?->waktu_mulai . ' - ' . $jadwalLama?->waktu_akhir}} (Kebutuhan Setempat)</td>
                </tr>
              </table>
            </td>
          </tr>

          <br>

          <!-- TEMPAT TUGAS BARU -->
          <tr>
            <td></td>
            <td style="padding-top:6px; font-size: 10px;">
              Tempat Tugas Baru:
            </td>
          </tr>

          <tr>
            <td></td>
            <td>
              <table style="width:100%;margin-top:4px;font-size:10px;">
                <tr>
                  <td style="width:18%;">Jabatan</td>
                  <td style="width:3%;">:</td>
                  <td style="width:79%;">
                     {{$t_mutasi->m_posisi_baru?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_baru?->name ?? '-'}}<br>
                {{$t_mutasi->m_sub_baru?->address ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Jam Kerja</td>
                  <td>:</td>
                  <td>{{ $jadwalBaru?->waktu_mulai . ' - ' . $jadwalBaru?->waktu_akhir}} (Kebutuhan Setempat)</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- POIN 2 -->
          <tr>
            <td style="vertical-align:top;padding-top:6px; font-size: 10px;">2.</td>
            <td style="padding-top:6px; font-size: 10px;">
              Keputusan ini berlaku sejak tanggal efektif dan akan ditinjau kembali
              apabila di kemudian hari terdapat kekeliruan.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>


  <div></div>

  <!-- PENUTUP -->
  <table style="width:100%;margin-top:26px; font-size: 11px;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        Dikeluarkan di&nbsp;&nbsp;: Surabaya<br>
        Pada Tanggal&nbsp;&nbsp;: {{$tanggalTerbit}}
        <div style="margin-top:18px;font-weight:bold;">
          PT Temprina Media Grafika
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

  <div></div>

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
        -
      @endif
    </td>
  </tr>
</table>

</div>