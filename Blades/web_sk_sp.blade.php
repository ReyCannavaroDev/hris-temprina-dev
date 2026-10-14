@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_surat_peringatan;
use App\Models\CustomModels\m_general;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = null;
$t_surat_peringatan = null;

if ($id) {
    try {
        $t_mutasi = t_mutasi::find($id);
    } catch (\Throwable $e) {}
    if (!$t_mutasi) {
        try {
            $t_surat_peringatan = t_surat_peringatan::find($id);
        } catch (\Throwable $e) {}
    }
}

// Nomor Dokumen
$nomorRaw = !empty($t_mutasi?->no_dokumen) ? $t_mutasi->no_dokumen : ($t_mutasi?->nomor ?? $t_surat_peringatan?->nomor ?? '-');
$cleanNomor = preg_replace('/^no\.?\s*/i', '', trim($nomorRaw));
$nomor = !empty($cleanNomor) ? $cleanNomor : '-';

// Tanggal
$tgl = $t_mutasi?->tgl ?? $t_surat_peringatan?->tgl ?? now();
$carbonDate = Carbon::parse($tgl);
$tanggalOnly = $carbonDate->translatedFormat('d F Y');

// Karyawan
$karyawan = $t_mutasi?->m_kary ?? $t_surat_peringatan?->m_kary;
if (!$karyawan && !empty($t_mutasi?->m_kary_id)) {
    try {
        $karyawan = \App\Models\CustomModels\m_kary::find($t_mutasi->m_kary_id) 
                 ?? \App\Models\BasicModels\m_kary::find($t_mutasi->m_kary_id);
    } catch (\Throwable $e) {}
}
$namaKaryawan = strtoupper($karyawan?->nama_lengkap ?? '-');

// Perusahaan & Jabatan
$posisiName = trim($t_mutasi?->m_posisi_lama?->name ?? $karyawan?->m_posisi?->name ?? '-');

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $karyawan?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $karyawan?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? $karyawan?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : $compRaw;

$jabatanLengkap = str_contains(strtoupper($posisiName), strtoupper(str_replace('PT ', '', $companyName)))
    ? $posisiName
    : trim($posisiName . ' ' . $companyName);

// Alamat Karyawan
$alamatKaryawan = $karyawan?->address 
               ?? $karyawan?->alamat 
               ?? $karyawan?->m_subcomp?->address 
               ?? $t_mutasi?->m_sub_lama?->address 
               ?? $t_mutasi?->m_branch_lama?->address 
               ?? '-';

// Resolusi Level SP (I, II, atau III)
$rawLevel = trim($t_mutasi?->keterangan ?? '');
if (empty($rawLevel)) {
    $rawLevel = trim($t_mutasi?->deskripsi ?? '');
}
if (empty($rawLevel) && $t_surat_peringatan?->level_sp) {
    try {
        $level_sp = m_general::find($t_surat_peringatan->level_sp);
        $rawLevel = $level_sp?->value ?? '';
    } catch (\Throwable $e) {}
}

$upperLevel = strtoupper($rawLevel ?? '');
if (preg_match('/\b(III|3|TIGA)\b/i', $upperLevel)) {
    $spRomawi = 'III';
    $spText = 'Tiga';
} elseif (preg_match('/\b(II|2|DUA)\b/i', $upperLevel)) {
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

// Kota Cabang
$branchObj = $t_mutasi?->m_branch_lama 
          ?? $t_mutasi?->m_branch_baru 
          ?? $karyawan?->m_branch 
          ?? $t_surat_peringatan?->m_kary?->m_branch;

$subcompObj = $t_mutasi?->m_sub_lama ?? $karyawan?->m_subcomp;

$city = $branchObj?->kota 
      ?? $branchObj?->name 
      ?? $subcompObj?->kota 
      ?? $subcompObj?->name 
      ?? 'Bekasi';

$city = preg_replace('/^(cabang|branch)\s+/i', '', trim($city));
$city = ucfirst(strtolower($city));

$masaBerlaku = $t_surat_peringatan?->masa_berlaku ?? 6;

// Kesalahan / Pelanggaran
$detailMemperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? $t_surat_peringatan?->t_surat_peringatan_d_pelanggaran ?? null;
if ((!$detailMemperhatikan || count($detailMemperhatikan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $detailMemperhatikan = \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}

$rawList = [];
if ($detailMemperhatikan && count($detailMemperhatikan) > 0) {
    foreach ($detailMemperhatikan as $m) {
        $val = trim(is_array($m) ? ($m['value'] ?? '') : ($m->value ?? ''));
        if ($val !== '') $rawList[] = $val;
    }
}

if (empty($rawList) && !empty($t_mutasi?->catatan)) {
    $lines = explode("\n", str_replace("\r", "", $t_mutasi->catatan));
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l !== '') $rawList[] = $l;
    }
}

if (empty($rawList) && !empty($t_mutasi?->deskripsi)) {
    $lines = explode("\n", str_replace("\r", "", $t_mutasi->deskripsi));
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l !== '') $rawList[] = $l;
    }
}

$itemsKesalahan = [];
foreach ($rawList as $l) {
    $trimmed = trim($l);
    if ($trimmed === '') continue;
    $text = preg_replace('/^(?:\d+[\.\)\-]\s*|[-*•]\s*)/u', '', $trimmed);
    $text = trim($text);
    if ($text !== '') {
        $itemsKesalahan[] = $text;
    }
}

// Tembusan
$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? $t_surat_peringatan?->t_surat_peringatan_d_tembusan ?? null;
if ((!$tembusan || count($tembusan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $tembusan = \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}

// Penandatangan
$signature = $t_mutasi?->signature ?? $t_surat_peringatan?->signature;
$sigId = $t_mutasi?->signature_id ?? $t_surat_peringatan?->signature_id;
if (!$signature && !empty($sigId)) {
    try {
        $signature = \App\Models\CustomModels\m_kary::find($sigId) 
                  ?? \App\Models\BasicModels\m_kary::find($sigId);
    } catch (\Throwable $e) {}
}

$namaTtd = !empty($signature?->nama_lengkap) ? strtoupper($signature->nama_lengkap) : 'NAMA YANG TTD';
$jabatanTtd = 'JABATAN';
if ($signature) {
    $posName = $signature->m_posisi?->name ?? '';
    if (empty($posName) && !empty($signature->m_posisi_id)) {
        try {
            $pos = \App\Models\CustomModels\m_posisi::find($signature->m_posisi_id);
            if ($pos) $posName = $pos->name;
        } catch (\Throwable $e) {}
    }
    $jabatanTtd = !empty($posName) ? strtoupper($posName) : 'KADIV. HUMAN CAPITAL';
}

// Icon Wedge Arrow Hitam Base64 (Anti tanda tanya '?' di font PDF dan seragam)
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAXklEQVR4nJ2S0Q4AEAwDV/H/v1zxQIqFWZ93rrGBpGVSUpQJCOBLDa06YJIIG5fXAL4aLEa1arwGoc+B0+AwjkG7pDeoPxCl8gG+APPA3XZbi2uM7HGC3RYB5nz2yBvH5jIZAPwxNAAAAABJRU5ErkJggg==';
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:0 auto; padding:0; font-size:11pt; line-height:1.25; color:#000;">

  @if(request('kop'))
    <!-- KOP SURAT RESMI (JIKA DIREQUEST VIA PARAMETER) -->
    @include('projects.web_sk_kop')
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
      <tr><td style="border-bottom:1px solid #000; height:1px; line-height:1px; font-size:1px;">&nbsp;</td></tr>
      <tr><td style="border-bottom:2px solid #000; height:2px; line-height:2px; font-size:1px;">&nbsp;</td></tr>
      <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
    </table>
  @endif

  <!-- TANGGAL, NOMOR & PERIHAL (RATA KIRI LURUS) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.25;">
    <tr>
      <td style="text-align:left; margin:0; padding:0;">{{ $city }}, {{ $tanggalOnly }}<br>No. {{ $nomor }}</td>
    </tr>
    <tr>
      <td style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="text-align:left; margin:0; padding:0;">Hal : <b>{{ $halTitle }}</b></td>
    </tr>
  </table>

  <!-- PEMBUKA (LURUS PERSIS KIRI) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.25;">
    <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:left; margin:0; padding:0;">Diberikan kepada :</td>
    </tr>
    <tr><td style="height:4px; line-height:4px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- TABEL DATA KARYAWAN & PELANGGARAN (DENGAN INDENT & TITIK DUA RATA LURUS) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.25;">
    <tr>
      <td width="3%" style="width:3%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="16%" style="width:16%; vertical-align:top; white-space:nowrap; padding:1.5px 0;"><img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Nama</td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1.5px 0;"><b>{{ $namaKaryawan }}</b></td>
    </tr>
    <tr>
      <td width="3%" style="width:3%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="16%" style="width:16%; vertical-align:top; white-space:nowrap; padding:1.5px 0;"><img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Jabatan</td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1.5px 0;">{{ $jabatanLengkap }}</td>
    </tr>
    <tr>
      <td width="3%" style="width:3%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="16%" style="width:16%; vertical-align:top; white-space:nowrap; padding:1.5px 0;"><img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Alamat</td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1.5px 0;">{{ $alamatKaryawan }}</td>
    </tr>
    <tr>
      <td width="3%" style="width:3%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="16%" style="width:16%; vertical-align:top; white-space:nowrap; padding:1.5px 0;"><img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Kesalahan</td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; text-align:justify; padding:1.5px 0;">
        @if(count($itemsKesalahan) > 0)
          <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.25;">
            @foreach($itemsKesalahan as $idx => $itemText)
              <tr>
                <td width="3%" style="width:18px; vertical-align:top; text-align:left; padding:0 0 1px 0;">{{ $idx + 1 }}.</td>
                <td width="97%" style="vertical-align:top; text-align:justify; padding:0 0 1px 0;">{{ $itemText }}</td>
              </tr>
            @endforeach
          </table>
        @else
          -
        @endif
      </td>
    </tr>
    <tr>
      <td colspan="4" style="height:2px; line-height:2px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td width="3%" style="width:3%; vertical-align:top; padding:1.5px 0;">&nbsp;</td>
      <td width="16%" style="width:16%; vertical-align:top; white-space:nowrap; padding:1.5px 0;"><img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Jenis Sanksi</td>
      <td width="2%" style="width:2%; vertical-align:top; text-align:center; padding:1.5px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1.5px 0;">1. {{ $sanksiTitle }}</td>
    </tr>
  </table>

  <!-- PARAGRAF KETENTUAN MASA BERLAKU (LURUS PERSIS KIRI) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.28;">
    <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:justify; margin:0; padding:0;">{{ $spBerlakuTitle }} ini berlaku selama <b>{{ $masaBerlaku }} bulan</b> terhitung dari tanggal dikeluarkan, apabila yang bersangkutan masih melakukan pelanggaran lagi maka perusahaan dapat memberikan Surat Peringatan berikutnya atau sesuai dengan Undang - Undang yang berlaku.</td>
    </tr>
  </table>

  <!-- PARAGRAF PEMBINAAN & HARAPAN MANAJEMEN (LURUS PERSIS KIRI) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.28;">
    <tr><td style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:justify; margin:0; padding:0;">Dengan adanya {{ $spHarapanTitle }} yang diberikan kepada Saudara/i ini maka manajemen berharap agar Saudara/i dapat lebih baik lagi dalam hal kontrol, konsentrasi dan koordinasi tugas di lingkungan kerja Saudara/i sehari-hari. atas perhatiannya disampaikan terima kasih.</td>
    </tr>
  </table>

  <!-- TANDA TANGAN (LEFT-ALIGNED) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:11pt; line-height:1.25;">
    <tr><td colspan="2" style="height:18px; line-height:18px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="width:60%; vertical-align:top; text-align:left; margin:0; padding:0;">
        Hormat Kami,<br>
        {{ $companyName }}<br><br><br><br>
        @if($namaTtd !== '( ........................................ )')<u><b>{{ $namaTtd }}</b></u>@else<b>{{ $namaTtd }}</b>@endif<br>
        <b>{{ $jabatanTtd }}</b>
      </td>
      <td style="width:40%;">&nbsp;</td>
    </tr>
  </table>

  <!-- TEMBUSAN (LURUS PERSIS KIRI) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.22;">
    <tr><td style="height:12px; line-height:12px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:left; margin:0; padding:0;">Tembusan :<br>
        @if($tembusan && count($tembusan) > 0)
          @foreach($tembusan as $index => $t)
            @php
              $tVal = trim(is_array($t) ? ($t['value'] ?? '-') : ($t->value ?? '-'));
              $cleanTVal = preg_replace('/^\d+[\.\)]\s*/', '', $tVal);
            @endphp
            &nbsp;&nbsp;{{ $index + 1 }}. {{ $cleanTVal }}<br>
          @endforeach
        @else
          &nbsp;&nbsp;1. Direksi<br>
          &nbsp;&nbsp;2. Operational Manager<br>
          &nbsp;&nbsp;3. HRD
        @endif
      </td>
    </tr>
  </table>

  @if(request('footer'))
    @include('projects.web_sk_footer')
  @endif

</div>