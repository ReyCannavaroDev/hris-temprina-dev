@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_surat_peringatan;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_general;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_surat_peringatan = t_surat_peringatan::find($id);
$t_mutasi = null;
if (!$t_surat_peringatan) {
    $t_mutasi = t_mutasi::find($id);
}

$nomor = $t_surat_peringatan?->nomor ?? $t_mutasi?->nomor ?? '-';
$tgl = $t_surat_peringatan?->tgl ?? $t_mutasi?->tgl ?? now();
$carbonDate = Carbon::parse($tgl);
$tanggalOnly = $carbonDate->translatedFormat('d F Y');

$karyawan = $t_surat_peringatan?->m_kary ?? $t_mutasi?->m_kary;
$namaKaryawan = strtoupper($karyawan?->nama_lengkap ?? '-');

$posisiName = trim($t_mutasi?->m_posisi_lama?->name ?? $karyawan?->m_posisi?->name ?? '-');
$subcompName = trim($t_mutasi?->m_sub_lama?->name ?? $karyawan?->m_subcomp?->name ?? '');

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $karyawan?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $karyawan?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? $karyawan?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : $compRaw;

if (!empty($subcompName) && !str_contains(strtoupper($posisiName), strtoupper($subcompName))) {
    $jabatanKaryawan = $posisiName . ' ' . $subcompName;
} else {
    $jabatanKaryawan = $posisiName;
}

$jabatanLengkap = str_contains(strtoupper($jabatanKaryawan), strtoupper(str_replace('PT ', '', $companyName)))
    ? $jabatanKaryawan
    : trim($jabatanKaryawan . ' ' . $companyName);

$alamatKaryawan = $karyawan?->address 
               ?? $karyawan?->m_subcomp?->address 
               ?? $t_mutasi?->m_sub_lama?->address 
               ?? $t_mutasi?->m_branch_lama?->address 
               ?? '-';

// Resolusi Level SP (I, II, atau III) secara presisi
$rawLevel = '';
if ($t_surat_peringatan?->level_sp) {
    $level_sp = m_general::find($t_surat_peringatan->level_sp);
    $rawLevel = $level_sp?->value ?? '';
}
if (empty($rawLevel)) {
    $rawLevel = $t_mutasi?->keterangan 
             ?: ($t_mutasi?->tipe_mutasi 
             ?: ($t_mutasi?->jenis_surat ? m_general::find($t_mutasi->jenis_surat)?->value : ''));
}

$upperLevel = strtoupper($rawLevel ?? '');
if (str_contains($upperLevel, 'III') || str_contains($upperLevel, ' 3') || str_contains($upperLevel, 'TIGA')) {
    $spRomawi = 'III';
    $spText = 'Tiga';
} elseif (str_contains($upperLevel, 'II') || str_contains($upperLevel, ' 2') || str_contains($upperLevel, 'DUA')) {
    $spRomawi = 'II';
    $spText = 'Dua';
} else {
    $spRomawi = 'I';
    $spText = 'Satu';
}

$spLevelLabel = "{$spRomawi} ({$spText})";
$halTitle = "Surat Peringatan (SP) {$spLevelLabel}";
$sanksiTitle = "Peringatan {$spLevelLabel} atas kesalahan di atas";
$spBerlakuTitle = "Surat Peringatan {$spLevelLabel}";
$spHarapanTitle = "surat peringatan {$spLevelLabel}";

$city = $t_surat_peringatan?->m_kary?->m_branch?->kota 
      ?? $t_mutasi?->m_branch_baru?->kota 
      ?? $t_mutasi?->m_branch_lama?->kota 
      ?? $t_mutasi?->m_kary?->m_branch?->kota 
      ?? 'Surabaya';

$masaBerlaku = $t_surat_peringatan?->masa_berlaku ?? 6;

$pelanggaran = $t_surat_peringatan?->t_surat_peringatan_d_pelanggaran 
             ?? $t_mutasi?->t_mutasi_d_memperhatikan 
             ?? collect();

$tembusan = $t_surat_peringatan?->t_surat_peringatan_d_tembusan 
          ?? $t_mutasi?->t_mutasi_d_tembusan 
          ?? null;

$signature = $t_surat_peringatan?->signature ?? $t_mutasi?->signature;
$namaTtd = $signature?->nama_lengkap ?? '( ........................................ )';
$jabatanTtd = $signature?->m_posisi?->name ?? 'Kadiv. Human Capital';

// Icon Wedge Arrow Hitam Base64 (Identik acuan resmi dan anti tanda tanya '?' di font PDF)
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAXklEQVR4nJ2S0Q4AEAwDV/H/v1zxQIqFWZ93rrGBpGVSUpQJCOBLDa06YJIIG5fXAL4aLEa1arwGoc+B0+AwjkG7pDeoPxCl8gG+APPA3XZbi2uM7HGC3RYB5nz2yBvH5jIZAPwxNAAAAABJRU5ErkJggg==';
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:auto; font-size:12pt; line-height:1.3; color:#000;">

  <!-- KOP SURAT RESMI -->
  @include('projects.web_sk_kop')

  @if(!$isJpBooks)
    <!-- DOUBLE LINE SEPARATOR (TABEL AMAN TCPDF TANPA BALOK HITAM) -->
    <table style="width:100%; border-collapse:collapse; margin-top:2px; margin-bottom:8px;">
      <tr><td style="border-bottom:1px solid #000; height:1px; line-height:1px; font-size:1px;"></td></tr>
      <tr><td style="border-bottom:2px solid #000; height:2px; line-height:2px; font-size:1px;"></td></tr>
    </table>
  @endif

  <!-- TANGGAL, NOMOR & PERIHAL (RATA KIRI SESUAI ACUAN RESMI HRIS/SP KARYAWAN.pdf) -->
  <table width="100%" style="width:100%; border-collapse:collapse; font-size:12pt; line-height:1.3;">
    <tr>
      <td style="text-align:left;">
        {{ $city }}, {{ $tanggalOnly }}<br>
        No. {{ $nomor }}
      </td>
    </tr>
    <tr>
      <td style="padding-top:6px;">
        Hal : <b>{{ $halTitle }}</b>
      </td>
    </tr>
  </table>

  <!-- PEMBUKA -->
  <div style="margin-top:14px; margin-bottom:6px; font-size:12pt;">
    Diberikan kepada :
  </div>

  <!-- TABEL DATA KARYAWAN & PELANGGARAN (RAPAT & PROPORSIONAL DENGAN HANGING INDENT) -->
  <table width="100%" style="width:100%; border-collapse:collapse; font-size:12pt; line-height:1.3;">
    <tr>
      <td width="5%" style="width:5%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; white-space:nowrap; padding:1.5px 0;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Nama
      </td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="78%" style="width:78%; vertical-align:top; padding:1.5px 0;"><b>{{ $namaKaryawan }}</b></td>
    </tr>
    <tr>
      <td width="5%" style="width:5%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; white-space:nowrap; padding:1.5px 0;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Jabatan
      </td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="78%" style="width:78%; vertical-align:top; padding:1.5px 0;">{{ $jabatanLengkap }}</td>
    </tr>
    <tr>
      <td width="5%" style="width:5%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; white-space:nowrap; padding:1.5px 0;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Alamat
      </td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="78%" style="width:78%; vertical-align:top; padding:1.5px 0;">{{ $alamatKaryawan }}</td>
    </tr>
    <tr>
      <td width="5%" style="width:5%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; white-space:nowrap; padding:1.5px 0;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Kesalahan
      </td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="78%" style="width:78%; vertical-align:top; text-align:justify; padding:1.5px 0;">
        @php
          $rawList = [];
          if (count($pelanggaran) > 0) {
              foreach ($pelanggaran as $p) {
                  $rawList[] = trim($p->value ?? $p['value'] ?? '');
              }
          } elseif (!empty($t_mutasi?->catatan)) {
              $rawList = explode("\n", str_replace("\r", "", $t_mutasi->catatan));
          } elseif (!empty($t_mutasi?->deskripsi)) {
              $rawList = explode("\n", str_replace("\r", "", $t_mutasi->deskripsi));
          }

          $itemsKesalahan = [];
          foreach ($rawList as $l) {
              $trimmed = trim($l);
              if ($trimmed === '') continue;
              
              // Bersihkan penomoran awal misal '1.', '2.', '3)', '-', '*', bullet
              $text = preg_replace('/^(?:\d+[\.\)\-]\s*|[-*•]\s*)/u', '', $trimmed);
              $text = trim($text);
              if ($text === '') continue;

              // Deteksi cerdas apakah baris ini kelanjutan dari baris sebelumnya:
              // 1. Diawali huruf kecil (contoh: 'tertulis...', 'yang...', 'atas...')
              // 2. Baris sebelumnya berakhiran tanda hubung/garis miring (contoh: 'tertulis/tidak') atau kata gantung
              // 3. Diawali kata sambung lanjutan
              $isContinuation = false;
              if (count($itemsKesalahan) > 0) {
                  $prev = $itemsKesalahan[count($itemsKesalahan) - 1];
                  $firstChar = mb_substr($text, 0, 1);
                  
                  if (mb_strtolower($firstChar) === $firstChar && !is_numeric($firstChar) && !in_array($firstChar, ['[', '(', '{', '"', "'"])) {
                      $isContinuation = true;
                  } elseif (preg_match('/[-\/,]$/u', $prev) || preg_match('/\b(dan|atau|tidak|secara|yang)$/iu', $prev)) {
                      $isContinuation = true;
                  } elseif (preg_match('/^(?:tertulis|yang|dan|atau|oleh|dengan|di|pada|ke|atas)\b/iu', $text) && !str_ends_with($prev, '.')) {
                      $isContinuation = true;
                  }
              }

              if ($isContinuation && count($itemsKesalahan) > 0) {
                  $itemsKesalahan[count($itemsKesalahan) - 1] .= ' ' . $text;
              } else {
                  $itemsKesalahan[] = $text;
              }
          }
        @endphp
        @if(count($itemsKesalahan) > 0)
          <table style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:12pt; line-height:1.3;">
            @foreach($itemsKesalahan as $idx => $itemText)
              <tr>
                <td style="width:4%; vertical-align:top; text-align:left; padding:0 0 1px 0;">{{ $idx + 1 }}.</td>
                <td style="width:96%; vertical-align:top; text-align:justify; padding:0 0 1px 0;">{{ $itemText }}</td>
              </tr>
            @endforeach
          </table>
        @else
          -
        @endif
      </td>
    </tr>
    <tr>
      <td colspan="4" style="height:4px; line-height:4px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td width="5%" style="width:5%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; white-space:nowrap; padding:1.5px 0;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Jenis Sanksi
      </td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="78%" style="width:78%; vertical-align:top; padding:1.5px 0;">
        1. {{ $sanksiTitle }}
      </td>
    </tr>
  </table>

  <!-- PARAGRAF KETENTUAN MASA BERLAKU -->
  <div style="margin-top:14px; font-size:12pt; line-height:1.32; text-align:justify;">
    {{ $spBerlakuTitle }} ini berlaku selama <b>{{ $masaBerlaku }} bulan</b> terhitung dari tanggal dikeluarkan, apabila yang bersangkutan masih melakukan pelanggaran lagi maka perusahaan dapat memberikan Surat Peringatan berikutnya atau sesuai dengan Undang-Undang yang berlaku.
  </div>

  <!-- PARAGRAF PEMBINAAN & HARAPAN MANAJEMEN -->
  <div style="margin-top:10px; font-size:12pt; line-height:1.32; text-align:justify;">
    Dengan adanya {{ $spHarapanTitle }} yang diberikan kepada Saudara/i ini maka manajemen berharap agar Saudara/i dapat lebih baik lagi dalam hal kontrol, konsentrasi dan koordinasi tugas di lingkungan kerja Saudara/i sehari-hari. Atas perhatiannya disampaikan terima kasih.
  </div>

  <!-- TANDA TANGAN (LEFT-ALIGNED SESUAI ACUAN RESMI HRIS/SP KARYAWAN.pdf) -->
  <table style="width:100%; border-collapse:collapse; margin-top:16px; font-size:12pt; line-height:1.3;">
    <tr>
      <td style="width:55%; vertical-align:top; text-align:left;">
        Hormat Kami,<br>
        {{ $companyName }}<br><br><br><br>
        <b>{{ $namaTtd }}</b><br>
        <b>{{ $jabatanTtd }}</b>
      </td>
      <td style="width:45%;">&nbsp;</td>
    </tr>
  </table>

  <!-- TEMBUSAN RESMI KORPORAT -->
  <div style="margin-top:14px; font-size:11pt; line-height:1.3;">
    Tembusan :<br>
    @if($tembusan && count($tembusan) > 0)
      @foreach($tembusan as $index => $t)
        @php
          $tVal = trim($t->value ?? $t['value'] ?? '-');
          $cleanTVal = preg_replace('/^\d+[\.\)]\s*/', '', $tVal);
        @endphp
        &nbsp;&nbsp;{{ $index + 1 }}. {{ $cleanTVal }}<br>
      @endforeach
    @else
      &nbsp;&nbsp;1. Direksi<br>
      &nbsp;&nbsp;2. Operational Manager<br>
      &nbsp;&nbsp;3. HRD
    @endif
  </div>

  <!-- FOOTER CABANG RESMI (FLOW BIASA RINGKAS, PASTI MUAT DI HALAMAN 1) -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <div style="margin-top:18px; border-top:0.5px solid #bbb; padding-top:2px; font-size:6.2px; line-height:1.18; color:#333; text-align:justify;">
      <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
      <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
      <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
      <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
    </div>
  @endif

</div>