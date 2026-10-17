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

$signature = $t_mutasi?->signature;
if (!$signature && !empty($t_mutasi?->signature_id)) {
    try {
        $signature = \App\Models\CustomModels\m_kary::find($t_mutasi->signature_id);
    } catch (\Throwable $th) {}
}
$signatureNama = $signature?->nama_lengkap ?? '';
$signatureJabatan = $signature?->m_posisi?->name ?? '';
@endphp

<div style="font-family:'Times New Roman',serif;width:93%;margin:auto;font-size:11.5px;line-height:1.5;">

  @include('projects.web_sk_kop', ['kop' => 'tugas'])

  <!-- JUDUL SURAT (SESUAI DOKUMEN ACUAN & RIIL) -->
  <table style="width:100%; border-collapse:collapse;">
    <!-- ============================================================== -->
    <!-- PENGATUR SPASI KOP KE SURAT TUGAS:                             -->
    <!-- Ubah kedua angka '18px' di bawah ini (misal: 10px, 20px, 30px) -->
    <!-- ============================================================== -->
    <tr style="line-height:22px;">
      <td style="height:22px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="text-align:center;">
        <span style="font-weight:bold;font-size:13.5px;letter-spacing:2px;"><u>SURAT TUGAS</u></span>
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
    <tr>
      <td style="width:20%; vertical-align:top; text-align:left; font-weight:bold;">MEMPERHATIKAN</td>
      <td style="width:2.5%; vertical-align:top; text-align:center;">:</td>
      <td style="width:77.5%;"></td>
    </tr>
    @if($memperhatikan && count($memperhatikan) > 0)
      @foreach($memperhatikan as $item)
        <tr>
          <td></td>
          <td style="vertical-align:top; text-align:center; padding-top:2px;">•</td>
          <td style="vertical-align:top; text-align:justify; padding-top:2px; padding-bottom:2px;">
            {{ $item->value ?? $item['value'] }}
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td></td>
        <td style="vertical-align:top; text-align:center; padding-top:2px;">•</td>
        <td style="vertical-align:top; text-align:justify; padding-top:2px; padding-bottom:2px;">
          Keputusan hasil rapat / koordinasi manajemen
        </td>
      </tr>
      <tr>
        <td></td>
        <td style="vertical-align:top; text-align:center; padding-top:2px;">•</td>
        <td style="vertical-align:top; text-align:justify; padding-top:2px; padding-bottom:2px;">
          Peningkatan SDM & produktifitas di PT Temprina Media Grafika
        </td>
      </tr>
    @endif
  </table>

  <div></div>

  <!-- MEMUTUSKAN SERTA MENETAPKAN -->
  <table style="width:100%; margin-top:16px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="font-weight:bold; line-height:1.35;">MEMUTUSKAN</td>
    </tr>
    <tr>
      <td style="font-weight:bold; line-height:1.35;">&nbsp;&nbsp;SERTA</td>
    </tr>
    <tr>
      <td style="font-weight:bold; line-height:1.35;">MENETAPKAN&nbsp;&nbsp;&nbsp;&nbsp;:</td>
    </tr>
  </table>

  <!-- ISI RINCIAN TUGAS (DI BAWAH PAS DENGAN BULLET POINT) -->
  <table style="width:100%; margin-top:6px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:3%; vertical-align:top; text-align:center;">•</td>
      <td style="width:14%; vertical-align:top;">Nama</td>
      <td style="width:2.5%; vertical-align:top; text-align:center;">:</td>
      <td style="width:80.5%; vertical-align:top;">{{ $t_mutasi?->m_kary?->nama_lengkap ?? '-' }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">•</td>
      <td style="vertical-align:top; padding-top:3px;">Tugas</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; text-align:justify; padding-top:3px;">{{ $materiPelatihan }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">•</td>
      <td style="vertical-align:top; padding-top:3px;">Pemateri</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">{{ $pemateri }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">•</td>
      <td style="vertical-align:top; padding-top:3px;">Hari/Tgl</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">{{ $hariTgl }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">•</td>
      <td style="vertical-align:top; padding-top:3px;">Jam</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">{{ $jam }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">•</td>
      <td style="vertical-align:top; padding-top:3px;">Tempat</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; text-align:justify; padding-top:3px;">{!! nl2br(e($tempat)) !!}</td>
    </tr>
  </table>

  <!-- PARAGRAF PENUTUP -->
  <table style="width:100%; margin-top:16px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="text-align:justify; line-height:1.45;">
        Demikian surat tugas ini kami sampaikan guna diperhatikan serta dilaksanakan dengan penuh tanggung jawab.
      </td>
    </tr>
  </table>

  <!-- BLOK TANDA TANGAN (DI SEBELAH KIRI SESUAI DOKUMEN ACUAN) -->
  <table style="width:100%; border-collapse:collapse; font-size:11px;">
    <!-- ============================================================== -->
    <!-- PENGATUR JEDA / ENTER SEBELUM DIKELUARKAN DI:                  -->
    <!-- Ubah kedua angka '25px' di bawah ini (misal: 15px, 30px, 40px) -->
    <!-- ============================================================== -->
    <tr style="line-height:25px;">
      <td colspan="2" style="height:25px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="width:50%; vertical-align:top; text-align:left;">
        Dikeluarkan di : {{ $kotaTerbit }}<br>
        Pada Tanggal&nbsp;&nbsp;: {{ $tanggalTerbit }}
        @if(!empty($signatureNama))
          <!-- ============================================================== -->
          <!-- PENGATUR JARAK / RUANG TANDA TANGAN (JEDA TANGGAL KE NAMA):    -->
          <!-- Ubah kedua angka '50px' di bawah ini (misal: 35px, 60px, 70px) -->
          <!-- ============================================================== -->
          <div style="line-height:5px; height:50px; font-size:1px;">&nbsp;</div>
          <div style="line-height:1.2;">
            <b><u>{{ trim($signatureNama) }}</u></b><br>
            @if(!empty($signatureJabatan))
              <i>{{ trim($signatureJabatan) }}</i>
            @endif
          </div>
        @endif
      </td>
      <td style="width:50%;"></td>
    </tr>
  </table>

  <!-- TEMBUSAN (SESUAI DOKUMEN 2 & 3) -->
  @if($tembusan && count($tembusan) > 0)
  <table style="width:100%; border-collapse:collapse; font-size:10px;">
    <!-- ============================================================== -->
    <!-- PENGATUR JEDA DARI JABATAN KE TEMBUSAN:                        -->
    <!-- Ubah kedua angka '14px' di bawah ini (misal: 10px, 20px, 30px) -->
    <!-- ============================================================== -->
    <tr style="line-height:14px;">
      <td style="height:14px; font-size:1px;">&nbsp;</td>
    </tr>
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

  @php
    $countTembusan = ($tembusan && count($tembusan) > 0) ? count($tembusan) : 0;
    
    // FORMULA JEDA FOOTER OTOMATIS:
    // Menghitung tinggi secara dinamis berdasarkan JUMLAH BARIS tembusan.
    // - Jika 0 tembusan: jeda 195px (footer tidak naik)
    // - Jika 1 tembusan: jeda ~148px
    // - Jika 2 tembusan: jeda ~136px
    // - Jika 4 tembusan: jeda ~112px
    // - Jika 6 tembusan: jeda ~88px (otomatis menyusut agar tidak bablas ke halaman 2)
    if ($countTembusan === 0) {
        $jedaFooterPx = 195;
    } else {
        $jedaFooterPx = max(15, 160 - ($countTembusan * 12));
    }
  @endphp

  <!-- ============================================================== -->
  <!-- PENGATUR JEDA KE FOOTER (DINAMIS SESUAI JUMLAH TEMBUSAN):      -->
  <!-- ============================================================== -->
  <table style="width:100%; border-collapse:collapse;">
    <tr style="line-height:{{ $jedaFooterPx }}px;">
      <td style="height:{{ $jedaFooterPx }}px; font-size:1px;">&nbsp;</td>
    </tr>
  </table>

  <!-- FOOTER SURAT -->
  <!-- Ubah 'align' di bawah ini: 'center' (tengah), 'justify' (rata kanan-kiri), 'left' (kiri), 'right' (kanan) -->
  @include('projects.web_sk_footer', ['footer' => 'tugas', 'align' => 'center'])

</div>
