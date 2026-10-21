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

$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? null;
if ((!$tembusan || count($tembusan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $tembusan = \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}
if ($tembusan && count($tembusan) > 0) {
    $tembusan = collect($tembusan)->unique(function ($item) {
        return trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
    })->values();
}

$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;
if ((!$memperhatikan || count($memperhatikan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $memperhatikan = \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}
if ($memperhatikan && count($memperhatikan) > 0) {
    $memperhatikan = collect($memperhatikan)->unique(function ($item) {
        return trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
    })->values();
}

$jadwalLama = null;
if (!empty($t_mutasi?->jadwal_kerja_lama_id)) {
    try {
        $jadwalLama = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $t_mutasi->jadwal_kerja_lama_id)
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '')
            ->first();
    } catch (\Throwable $th) {
        $jadwalLama = null;
    }
}

$jadwalBaru = null;
if (!empty($t_mutasi?->jadwal_kerja_baru_id)) {
    try {
        $jadwalBaru = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $t_mutasi->jadwal_kerja_baru_id)
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '')
            ->first();
    } catch (\Throwable $th) {
        $jadwalBaru = null;
    }
}

$formatJam = function($jadwal) {
    if (!$jadwal || empty($jadwal->waktu_mulai)) {
        return '08:00 - 17:00 (Kebutuhan Setempat)';
    }
    $mulai = substr($jadwal->waktu_mulai, 0, 5);
    $akhir = !empty($jadwal->waktu_akhir) ? substr($jadwal->waktu_akhir, 0, 5) : (!empty($jadwal->waktu_selesai) ? substr($jadwal->waktu_selesai, 0, 5) : '17:00');
    return "{$mulai} - {$akhir} (Kebutuhan Setempat)";
};

$jamKerjaLama = $formatJam($jadwalLama);
$jamKerjaBaru = $formatJam($jadwalBaru);

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

$subJudul = strtoupper($t_mutasi?->jenis_surat?->value ?? 'PROMOSI JABATAN');
if (!str_contains($subJudul, 'JABATAN')) {
    $subJudul .= ' JABATAN';
}

$signature = $t_mutasi?->signature;
if (!$signature && !empty($t_mutasi?->signature_id)) {
    try {
        $signature = \App\Models\CustomModels\m_kary::find($t_mutasi->signature_id);
    } catch (\Throwable $th) {}
}
$signatureNama = $signature?->nama_lengkap ?? '';
$signatureJabatan = $signature?->m_posisi?->name ?? '';

// Icon Pointer Panah 3D Wingdings Base64 (100% persis template fisik & kompatibel TCPDF)
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAF4SURBVFhHxZehb4NAGMVfTjQoUtVUkckqMjnVICcnJ5GTkzjmkJOd25/An4CcJFPIZWqZIpshE4TlNdC0X2gDtHf3S56ivPea47sDAFjCMisAbwDu5AWTRAAaALmtIo5S6r0tYa3INcMdx2lc161tFUkYvNlsmjiOrRThUnwuFou6LMuGslEkYFgYhk2HjSKvDMqybFfCdJG5Uurb87y6qqqDEiaL3NM8iiKZv8NEkZTGeZ7L7AN0FlkqpX583+9dComuIg80S5JE5h1FR5FsNpvVRVHIrJNcsshKKfW3Xq9rjuZYpWnaBEHQnTOTi3Qn5iXFIoOZA/jtMRmrAsALgKdWg9keVGcoa0/cSfC1reoxHSIG83w5i+2GNFJc31tpNIWbHvNTmvSEn4KGMqRPHwBCefO50FAGSX3pCCZOay4D94Mf299p4djYle2mpC2Y9I0dg7lxcEPSzv7YscizqWDSjV0XbPzbkWPHl9IrecEE/LdWgq3xD8U6LuwJEeE6AAAAAElFTkSuQmCC';
@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">@include('projects.web_sk_kop')<!-- JUDUL --><table style="width:100%;border-collapse:collapse;">
    <!-- ============================================================== -->
    <!-- PENGATUR SPASI KOP KE JUDUL:                                  -->
    <!-- Ubah kedua angka '10px' di bawah ini (misal: 5px, 15px, 25px) -->
    <!-- Jika ingin tanpa spasi sama sekali, ubah jadi 0px atau hapus <tr> ini -->
    <!-- ============================================================== -->
    <tr style="line-height:10px;">
      <td colspan="3" style="height:10px;font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="width:30%;"></td>
      <td
        style="width:40%;text-align:center;font-weight:bold;font-size:15px;border-bottom:2px solid black;line-height:1.2;padding-bottom:2px;">
        SURAT KEPUTUSAN
      </td>
      <td style="width:30%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:4px;">
        No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
      </td>
      <td></td>
    </tr>
  </table>

  <div></div>

  <!-- SUB JUDUL -->
  <table style="width:100%;margin-top:16px;border-collapse:collapse;">
    <tr>
      <td style="width:25%;"></td>
      <td style="width:50%;text-align:center;font-size:11px;font-weight:bold;line-height:1.4;">
        TENTANG <br> {{$subJudul}}
      </td>
      <td style="width:25%;"></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:16px; border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:22%; vertical-align:top; font-weight:bold;">MEMPERHATIKAN</td>
      <td style="width:3%; vertical-align:top;">:</td>
      <td style="width:75%;"></td>
    </tr>
    @if($memperhatikan && count($memperhatikan) > 0)
      @foreach($memperhatikan as $item)
        <tr>
          <td></td>
          <td style="vertical-align:top; text-align:center;">•</td>
          <td style="vertical-align:top; padding-bottom:3px; text-align:justify;">
            {{ $item->value ?? $item['value'] }}
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td></td>
        <td style="vertical-align:top; text-align:center;">•</td>
        <td style="vertical-align:top; padding-bottom:3px; text-align:justify;">
          Kebutuhan operasional serta pembinaan sumber daya manusia di lingkungan perusahaan.
        </td>
      </tr>
    @endif
  </table>

  <!-- MEMUTUSKAN -->
  <table style="width:100%; margin-top:14px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:22%; vertical-align:top; font-weight:bold; line-height:1.4;">MEMUTUSKAN</td>
      <td style="width:3%;"></td>
      <td style="width:75%;"></td>
    </tr>
    <tr>
      <td style="vertical-align:top; font-weight:bold; line-height:1.4;">&nbsp;&nbsp;SERTA</td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td style="vertical-align:top; font-weight:bold; line-height:1.4;">MENETAPKAN</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td></td>
    </tr>

    <!-- POIN 1 (SEJAJAR DENGAN POSISI DI MEMPERHATIKAN) -->
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:6px;">1.</td>
      <td style="vertical-align:top; padding-top:6px;">
        Terhitung efektif {{$tanggalIndo}}
      </td>
    </tr>

    <!-- DETAIL KARYAWAN -->
    <tr>
      <td></td>
      <td></td>
      <td style="padding-top:4px;">
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <!-- NAMA -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:2px;">
              <img src="{{ $arrowIcon }}" width="12" height="12" alt="►">
            </td>
            <td style="width:14%; vertical-align:top;">Nama</td>
            <td style="width:3%; vertical-align:top;">:</td>
            <td style="width:79%; vertical-align:top;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
          </tr>

          <!-- BAGIAN LAMA -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:2px;">
              <img src="{{ $arrowIcon }}" width="12" height="12" alt="►">
            </td>
            <td style="vertical-align:top;">Bagian</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">
              {{ $t_mutasi->m_posisi_lama?->name ?? '-' }}<br>
              {{ $t_mutasi->m_sub_lama?->name ?? '-' }}<br>
              {{ $t_mutasi->m_sub_lama?->address ?? '' }}
            </td>
          </tr>

          <!-- JAM KERJA LAMA -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:2px;">
              <img src="{{ $arrowIcon }}" width="12" height="12" alt="►">
            </td>
            <td style="vertical-align:top;">Jam Kerja</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">
              {{ $jamKerjaLama }}
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- TEMPAT TUGAS BARU -->
    <tr>
      <td></td>
      <td></td>
      <td style="padding-top:10px; font-size:11px;"><u>Tempat Tugas Baru</u> :</td>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td style="padding-top:4px;">
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <!-- BAGIAN BARU -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:2px;">
              <img src="{{ $arrowIcon }}" width="12" height="12" alt="►">
            </td>
            <td style="width:14%; vertical-align:top;">Bagian</td>
            <td style="width:3%; vertical-align:top;">:</td>
            <td style="width:79%; vertical-align:top;">
              {{ $t_mutasi->m_posisi_baru?->name ?? '-' }}<br>
              {{ $t_mutasi->m_sub_baru?->name ?? '-' }}<br>
              {{ $t_mutasi->m_sub_baru?->address ?? '' }}
            </td>
          </tr>

          <!-- JAM KERJA BARU -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:2px;">
              <img src="{{ $arrowIcon }}" width="12" height="12" alt="►">
            </td>
            <td style="vertical-align:top;">Jam Kerja</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">
              {{ $jamKerjaBaru }}
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- POIN 2 -->
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:12px;">2.</td>
      <td style="vertical-align:top; padding-top:12px; text-align:justify;">Keputusan ini berlaku sejak tanggal efektif dan akan ditinjau kembali jika diperlukan</td>
    </tr>
  </table>

  <div></div>

  <!-- PENUTUP -->
  <table style="width:100%; margin-top:16px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:50%;"></td>
      <td style="width:50%; vertical-align:top;">
        <table cellpadding="0" cellspacing="0" style="font-size:11px;">
          <tr>
            <td width="80" style="vertical-align:top;">Dikeluarkan di</td>
            <td width="15" align="center" style="vertical-align:top;">:</td>
            <td width="150" style="vertical-align:top;">{!! str_replace(' ', '&nbsp;', $kotaTerbit) !!}</td>
          </tr>
          <tr>
            <td width="80" style="vertical-align:top;">Pada tanggal</td>
            <td width="15" align="center" style="vertical-align:top;">:</td>
            <td width="150" style="vertical-align:top;">{!! str_replace(' ', '&nbsp;', $tanggalTerbit) !!}</td>
          </tr>
        </table>
        <div style="margin-top:10px;">
          {{ $companyName }}
        </div>
        <div style="height:48px;"></div>
        <div style="line-height:1.2;">
          @if(!empty($signatureNama))
            <u><b>{{ $signatureNama }}</b></u><br>
          @endif
          @if(!empty($signatureJabatan))
            <span style="font-style:italic;">{{ $signatureJabatan }}</span>
          @endif
        </div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN -->
  @if($tembusan && count($tembusan) > 0)
  <table style="width:100%; margin-top:10px; font-size:10px; border-collapse:collapse;">
    <tr>
      <td style="width:100%;">
        <b>Tembusan :</b><br>
        @foreach($tembusan as $index => $t)
          &nbsp;&nbsp;{{ $index + 1 }}. {{ $t->value ?? $t['value'] }}<br>
        @endforeach
      </td>
    </tr>
  </table>
  @endif

</div>