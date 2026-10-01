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
if ((!$tembusan || count($tembusan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $tembusan = \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}

$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;
if ((!$memperhatikan || count($memperhatikan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $memperhatikan = \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}

// Parse data pelatihan dari keterangan (JSON)
$meta = null;
if (!empty($t_mutasi?->keterangan)) {
    try {
        $meta = json_decode($t_mutasi->keterangan, true);
    } catch (\Throwable $th) {}
}

$materiPelatihan = $t_mutasi?->deskripsi ?? 'Training & Sertifikasi';
$pemateri = (!empty($meta['pemateri'])) ? $meta['pemateri'] : '-';
$hariTgl = (!empty($meta['hari_tgl'])) ? $meta['hari_tgl'] : $tanggalIndo;
$jam = (!empty($meta['jam'])) ? $meta['jam'] : '-';
$tempat = (!empty($meta['tempat'])) ? $meta['tempat'] : (!is_array($meta) && !empty($t_mutasi?->keterangan) ? $t_mutasi->keterangan : '-');

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

<div style="font-family:'Times New Roman',serif;width:93%;margin:auto;font-size:11.5px;line-height:1.45;">

  @include('projects.web_sk_kop')

  <!-- JUDUL SURAT (SESUAI DOKUMEN ACUAN & RIIL) -->
  <table style="width:100%;margin-top:16px;">
    <tr>
      <td style="text-align:center;">
        <span style="font-weight:bold;font-size:13.5px;letter-spacing:4px;"><u>S U R A T &nbsp; T U G A S</u></span>
      </td>
    </tr>
    <tr>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:3px;">
        No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
      </td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN (STRUKTUR TABEL FLAT AGAR TIDAK PATAH DI TCPDF) -->
  <table style="width:100%; margin-top:18px; border-collapse:collapse; font-size:11px;">
    @if($memperhatikan && count($memperhatikan) > 0)
      @foreach($memperhatikan as $index => $item)
        <tr>
          <td style="width:20%; vertical-align:top; text-align:left;">
            @if($index === 0) MEMPERHATIKAN @endif
          </td>
          <td style="width:2%; vertical-align:top;">
            @if($index === 0) : @endif
          </td>
          <td style="width:3%; vertical-align:top; text-align:center;">•</td>
          <td style="width:75%; vertical-align:top; text-align:justify; padding-bottom:3px;">
            {{ $item->value ?? $item['value'] }}
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td style="width:20%; vertical-align:top; text-align:left;">MEMPERHATIKAN</td>
        <td style="width:2%; vertical-align:top;">:</td>
        <td style="width:3%; vertical-align:top; text-align:center;">•</td>
        <td style="width:75%; vertical-align:top; text-align:justify; padding-bottom:3px;">
          Keputusan hasil rapat / koordinasi manajemen
        </td>
      </tr>
      <tr>
        <td style="width:20%;"></td>
        <td style="width:2%;"></td>
        <td style="width:3%; vertical-align:top; text-align:center; padding-top:2px;">•</td>
        <td style="width:75%; vertical-align:top; text-align:justify; padding-top:2px;">
          Peningkatan SDM & produktifitas di PT Temprina Media Grafika
        </td>
      </tr>
    @endif
  </table>

  <div></div>

  <!-- MEMUTUSKAN SERTA MENETAPKAN -->
  <table style="width:100%; margin-top:16px; font-size:11px;">
    <tr>
      <td style="width:20%; vertical-align:top; text-align:left;">MEMUTUSKAN<br>SERTA<br>MENETAPKAN</td>
      <td style="width:2%; vertical-align:bottom; padding-bottom:2px;">:</td>
      <td style="width:78%;">

        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td style="width:16%; vertical-align:top;">• Nama</td>
            <td style="width:2.5%; vertical-align:top;">:</td>
            <td style="width:81.5%; font-weight:bold;">{{$t_mutasi?->m_kary?->nama_lengkap ?? '-'}}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">• Tugas</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top; text-align:justify;">{{$materiPelatihan}}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">• Pemateri</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">{{$pemateri}}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">• Hari/Tgl</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">{{$hariTgl}}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">• Jam</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top;">{{$jam}}</td>
          </tr>
          <tr>
            <td style="vertical-align:top;">• Tempat</td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top; text-align:justify;">{!! nl2br(e($tempat)) !!}</td>
          </tr>
        </table>

      </td>
    </tr>
  </table>

  <!-- PARAGRAF PENUTUP -->
  <p style="text-align:justify; margin-top:20px; font-size:11px;">
    Demikian surat tugas ini kami sampaikan guna diperhatikan serta dilaksanakan dengan penuh tanggung jawab.
  </p>

  <!-- BLOK TANDA TANGAN (SESUAI DOKUMEN 2 & 3) -->
  <table style="width:100%; margin-top:18px; font-size: 11px;">
    <tr>
      <td style="width:58%;"></td>
      <td style="width:42%;">
        Dikeluarkan di&nbsp;&nbsp;: {{$kotaTerbit}}<br>
        Pada Tanggal&nbsp;&nbsp;: {{$tanggalTerbit}}
        <div style="height:55px;"></div>
        <div style="font-weight:bold; text-decoration:underline;">
          {{$t_mutasi?->signature?->nama_lengkap ?? 'DEVI NOVARIA PURNAMASARI'}}
        </div>
        <div style="font-style:italic;">
          {{$t_mutasi?->signature?->m_posisi?->name ?? 'Asst. Manager Human Capital'}}
        </div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN (SESUAI DOKUMEN 2 & 3) -->
  <table style="width:100%; margin-top:14px; font-size:10px; border-collapse:collapse;">
    <tr>
      <td style="width:100%;">
        <b>Tembusan :</b><br>
        @if($tembusan && count($tembusan) > 0)
          @foreach($tembusan as $index => $t)
            &nbsp;&nbsp;{{ $index + 1 }}. {{ $t->value ?? $t['value'] }}<br>
          @endforeach
        @else
          &nbsp;&nbsp;1. Direksi<br>
          &nbsp;&nbsp;2. Keuangan
        @endif
      </td>
    </tr>
  </table>

  <!-- FOOTER CABANG TEMPRINA (SESUAI DOKUMEN RIIL TEMPRINA) -->
  <div style="margin-top:35px; border-top:0.5px solid #bbb; padding-top:4px; font-size:6.8px; line-height:1.35; color:#333; text-align:justify;">
    <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
    <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
    <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
    <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
  </div>

</div>
