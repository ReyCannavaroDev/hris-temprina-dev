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
    } catch (\Throwable $th) {
    }
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
    } catch (\Throwable $th) {
    }
  }
  if ($memperhatikan && count($memperhatikan) > 0) {
    $memperhatikan = collect($memperhatikan)->unique(function ($item) {
      return trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
    })->values();
  }

  // Query jam kerja asli dari tabel detail jadwal kerja di database
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

  // Format jam kerja riil dari database
  $formatJam = function ($jadwal) {
    if (!$jadwal || empty($jadwal->waktu_mulai)) {
      return '08:00 - 17:00 (Kebutuhan Setempat)';
    }
    $mulai = substr($jadwal->waktu_mulai, 0, 5);
    $akhir = !empty($jadwal->waktu_akhir) ? substr($jadwal->waktu_akhir, 0, 5) : (!empty($jadwal->waktu_selesai) ? substr($jadwal->waktu_selesai, 0, 5) : '17:00');
    return "{$mulai} - {$akhir} (Kebutuhan Setempat)";
  };

  $jamKerjaLama = $formatJam($jadwalLama);
  $jamKerjaBaru = $formatJam($jadwalBaru);

  // Deteksi entitas perusahaan baru & lama
  $dest = strtoupper(
    ($t_mutasi->m_sub_baru?->name ?? '') . ' ' .
    ($t_mutasi->m_sbu_baru?->name ?? '') . ' ' .
    ($t_mutasi->m_sub_baru?->kode ?? '') . ' ' .
    ($t_mutasi->m_sbu_baru?->kode ?? '')
  );
  $origin = strtoupper(
    ($t_mutasi->m_sub_lama?->name ?? '') . ' ' .
    ($t_mutasi->m_sbu_lama?->name ?? '') . ' ' .
    ($t_mutasi->m_sub_lama?->kode ?? '') . ' ' .
    ($t_mutasi->m_sbu_lama?->kode ?? '') . ' ' .
    ($t_mutasi->m_kary?->m_sbu?->name ?? '') . ' ' .
    ($t_mutasi->m_kary?->m_sbu?->kode ?? '')
  );

  $checkIsJp = function ($str) {
    return str_contains($str, 'JEPE') ||
      str_contains($str, 'JP BOOKS') ||
      str_contains($str, 'JPBOOKS') ||
      str_contains($str, 'JPMU') ||
      str_contains($str, 'SAHABAT EDUKASI') ||
      preg_match('/\bJP\b/', $str);
  };

  // Menggunakan template KOP resmi JP Books (PT. JePe Press Media Utama) sesuai acuan cetak
  $isJpBooks = true;
  $companyName = 'PT. JePe Press Media Utama';
  $kotaTerbit = $t_mutasi?->m_branch_baru?->kota
    ?? $t_mutasi?->m_branch_lama?->kota
    ?? $t_mutasi?->m_kary?->m_branch?->kota
    ?? 'Surabaya';

  $resolveMedia = function ($rawPath) {
    if (empty($rawPath))
      return '';
    $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
    $clean = ltrim($clean, '/');

    $candidates = [
      $clean,
      'uploads/m_media/' . $clean,
      'uploads/' . $clean,
      'storage/' . $clean,
      'images/' . $clean,
      'media/' . $clean
    ];

    foreach ($candidates as $cand) {
      $paths = [
        function_exists('public_path') ? public_path($cand) : null,
        function_exists('base_path') ? base_path('public/' . $cand) : null,
        function_exists('base_path') ? base_path($cand) : null,
        __DIR__ . '/../' . $cand,
        __DIR__ . '/../../' . $cand,
        '/opt/www/hris/public/' . $cand,
        '/opt/www/hris/' . $cand
      ];
      foreach ($paths as $full) {
        if ($full && file_exists($full) && is_file($full)) {
          $type = pathinfo($full, PATHINFO_EXTENSION);
          return 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
        }
      }
    }
    return $rawPath;
  };

  // 1. Ambil header JP Books dari database m_media atau file fisik media/header_jpbooks.jpg
  $mediaHeaderJP = null;
  try {
    $mediaHeaderJP = \DB::table('m_media')
      ->where('is_active', true)
      ->whereIn('kode', ['HEADER-JPBOOKS', 'LOGO-JPBOOKS'])
      ->orderBy('id', 'desc')
      ->first();
  } catch (\Throwable $th) {
    $mediaHeaderJP = null;
  }

  $logoJpHeader = $mediaHeaderJP ? $resolveMedia($mediaHeaderJP->file_path) : '';

  if (empty($logoJpHeader)) {
    $headerCandidates = [
      'media/header_jpbooks.jpg',
      'public/media/header_jpbooks.jpg',
      'images/header_jpbooks.jpg',
      'storage/header_jpbooks.jpg',
      'uploads/m_media/header_jpbooks.jpg'
    ];
    foreach ($headerCandidates as $cand) {
      $paths = [
        function_exists('public_path') ? public_path($cand) : null,
        function_exists('base_path') ? base_path('public/' . $cand) : null,
        function_exists('base_path') ? base_path($cand) : null,
        __DIR__ . '/../' . $cand,
        __DIR__ . '/../../' . $cand,
        '/opt/www/hris/public/' . $cand,
        '/opt/www/hris/' . $cand
      ];
      foreach ($paths as $full) {
        if ($full && file_exists($full) && is_file($full)) {
          $type = pathinfo($full, PATHINFO_EXTENSION);
          $logoJpHeader = 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
          break 2;
        }
      }
    }
  }

  // 2. Resolusi arrow bullet JP
  $arrowJp = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAJCAYAAAA/33wPAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAiklEQVQokaXSIQoCYRDF8d93AaPBaBAxGE1iEPGGHkCMHsKwJ/AEdptRDGNwFj4srrsPhnkT5s/wGNghIkLfQtOayDr2BO3r4YYJLjj9DatAWxzSr3FN6KoLqOSiUsoID9x9NM4uIopfqi56VllFQucd8pli1g7nL8CiV0YJeGE54AU02Az5o4jwBmbAZCpiE9QOAAAAAElFTkSuQmCC';
  $arrowCandidates = [
    'media/arrow_jp.png',
    'public/media/arrow_jp.png',
    'images/arrow_jp.png',
    'storage/arrow_jp.png',
    'uploads/m_media/arrow_jp.png'
  ];
  foreach ($arrowCandidates as $cand) {
    $paths = [
      function_exists('public_path') ? public_path($cand) : null,
      function_exists('base_path') ? base_path('public/' . $cand) : null,
      function_exists('base_path') ? base_path($cand) : null,
      __DIR__ . '/../' . $cand,
      __DIR__ . '/../../' . $cand,
      '/opt/www/hris/public/' . $cand,
      '/opt/www/hris/' . $cand
    ];
    foreach ($paths as $full) {
      if ($full && file_exists($full) && is_file($full)) {
        $type = pathinfo($full, PATHINFO_EXTENSION);
        $arrowJp = 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
        break 2;
      }
    }
  }

  // 3. Resolusi footer JP
  $footerJp = '';
  $footerCandidates = [
    'media/footer_jpbooks.jpg',
    'public/media/footer_jpbooks.jpg',
    'images/footer_jpbooks.jpg',
    'storage/footer_jpbooks.jpg',
    'uploads/m_media/footer_jpbooks.jpg'
  ];
  foreach ($footerCandidates as $cand) {
    $paths = [
      function_exists('public_path') ? public_path($cand) : null,
      function_exists('base_path') ? base_path('public/' . $cand) : null,
      function_exists('base_path') ? base_path($cand) : null,
      __DIR__ . '/../' . $cand,
      __DIR__ . '/../../' . $cand,
      '/opt/www/hris/public/' . $cand,
      '/opt/www/hris/' . $cand
    ];
    foreach ($paths as $full) {
      if ($full && file_exists($full) && is_file($full)) {
        $type = pathinfo($full, PATHINFO_EXTENSION);
        $footerJp = 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
        break 2;
      }
    }
  }
@endphp

<style>
  @page {
    size: A4 portrait;
    margin: 8mm 14mm 6mm 14mm;
  }
  body {
    margin: 0;
    padding: 0;
  }
  table, tr, td, div {
    page-break-inside: avoid;
  }
</style>

<div style="font-family:'Times New Roman',serif;width:100%;max-width:100%;margin:0 auto;font-size:10px;line-height:1.25;">

  <!-- KOP HEADER RESMI JP BOOKS -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:4px;">
    <tr>
      <td width="100%" style="width:100%; padding:0; text-align:left;">
        <img src="{{ $logoJpHeader }}" style="width:100%; max-height:75px; height:auto; display:block;" alt="JP Books - PT. JePe Press Media Utama">
      </td>
    </tr>
  </table>

  <!-- JUDUL & TENTANG -->
  <table style="width:100%; border-collapse:collapse; margin-top:4px; margin-bottom:4px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:12.5px; letter-spacing:0.5px;">
        <span style="display:inline-block; border-bottom:2px solid #000; padding-bottom:1px; line-height:1.15;">
          SURAT KEPUTUSAN
        </span>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:10px; padding-top:2px;">
        No. {{ strtoupper($t_mutasi?->nomor) }}
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:10px; padding-top:4px; line-height:1.2;">
        TENTANG<br>
        MUTASI TEMPAT TUGAS
      </td>
    </tr>
  </table>

  <!-- BAGIAN 2: PERTIMBANGAN & PENETAPAN -->
  <table style="width:100%; border-collapse:collapse; font-size:10px; margin-top:3px;">
    <!-- MEPERHATIKAN -->
    <tr>
      <td style="width:18%; vertical-align:top; white-space:nowrap;">MEPERHATIKAN</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:79%;"></td>
    </tr>

    @if($memperhatikan && count($memperhatikan) > 0)
      @foreach($memperhatikan as $item)
        <tr>
          <td style="width:18%;"></td>
          <td style="width:3%; vertical-align:top; text-align:center; font-size:10px; line-height:1.2;">&bull;</td>
          <td style="width:79%; vertical-align:top; line-height:1.25; padding-bottom:1px; padding-left:4px;">
            {{ is_array($item) ? ($item['value'] ?? '') : ($item->value ?? '') }}
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td style="width:18%;"></td>
        <td style="width:3%; vertical-align:top; text-align:center; font-size:10px; line-height:1.2;">&bull;</td>
        <td style="width:79%; vertical-align:top; line-height:1.25; padding-bottom:1px; padding-left:4px;">
          Keputusan hasil koordinasi manajemen {{ $companyName }}
        </td>
      </tr>
      <tr>
        <td style="width:18%;"></td>
        <td style="width:3%; vertical-align:top; text-align:center; font-size:10px; line-height:1.2;">&bull;</td>
        <td style="width:79%; vertical-align:top; line-height:1.25; padding-bottom:1px; padding-left:4px;">
          Upaya optimalisasi SDM di {{ $companyName }}
        </td>
      </tr>
    @endif

    <!-- SPASI ANTARA MEPERHATIKAN DAN MEMUTUSKAN -->
    <tr>
      <td colspan="3" style="height:3px;"></td>
    </tr>

    <!-- MEMUTUSKAN SERTA MENETAPKAN -->
    <tr>
      <td style="width:18%; vertical-align:bottom; line-height:1.2;">
        MEMUTUSKAN<br>
        <span style="display:inline-block; padding-left:14px;">SERTA</span><br>
        MENETAPKAN
      </td>
      <td style="width:3%; vertical-align:bottom; text-align:center;">:</td>
      <td style="width:79%; vertical-align:bottom;"></td>
    </tr>
  </table>

  <!-- BAGIAN 3: POIN KEPUTUSAN 1 & 2 -->
  <table style="width:100%; border-collapse:collapse; font-size:10px; margin-top:3px;">
    <!-- POIN 1 -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%; vertical-align:top; text-align:center;">1.</td>
      <td style="width:79%; vertical-align:top; line-height:1.25;">
        Terhitung efektif <b>{{$tanggalIndo}}</b>
      </td>
    </tr>

    <!-- NAMA -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%;"></td>
      <td style="width:79%; vertical-align:top; padding-top:1px;">
        <table style="width:100%; border-collapse:collapse; font-size:10px;">
          <tr>
            <td style="width:20px; vertical-align:middle;">
              <img src="{{ $arrowJp }}" width="15" height="8" style="vertical-align:middle; display:inline-block;" alt=">">
            </td>
            <td style="width:65px; vertical-align:middle; white-space:nowrap;">Nama</td>
            <td style="width:14px; vertical-align:middle; text-align:center;">:</td>
            <td style="vertical-align:middle; font-weight:bold;">
              <nobr>{{ strtoupper($t_mutasi->m_kary?->nama_lengkap ?? '-') }}</nobr>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- TEMPAT TUGAS LAMA -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%;"></td>
      <td style="width:79%; vertical-align:top; padding-top:3px; font-size:10px; text-decoration:underline;">
        Tempat Tugas Lama :
      </td>
    </tr>

    <!-- JABATAN & JAM KERJA LAMA -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%;"></td>
      <td style="width:79%; vertical-align:top; padding-top:1px;">
        <table style="width:100%; border-collapse:collapse; font-size:10px;">
          <tr>
            <td style="width:20px; vertical-align:top; padding:1px 0;">
              <img src="{{ $arrowJp }}" width="15" height="8" style="vertical-align:top; margin-top:2px; display:inline-block;" alt=">">
            </td>
            <td style="width:65px; vertical-align:top; padding:1px 0; white-space:nowrap;">Jabatan</td>
            <td style="width:14px; vertical-align:top; text-align:center; padding:1px 0;">:</td>
            <td style="vertical-align:top; line-height:1.25; padding:1px 0; white-space:nowrap;">
              <nobr>{{ $t_mutasi->m_posisi_lama?->name ?? '-' }}</nobr><br>
              <nobr>{{ $t_mutasi->m_sbu_lama?->name ?? ($t_mutasi->m_sub_lama?->name ?? '-') }}</nobr><br>
              <nobr>{{ $t_mutasi->m_sub_lama?->address ?? ($t_mutasi->m_branch_lama?->address ?? '') }}</nobr>
            </td>
          </tr>
          <tr>
            <td style="width:20px; vertical-align:top; padding:1px 0;">
              <img src="{{ $arrowJp }}" width="15" height="8" style="vertical-align:top; margin-top:2px; display:inline-block;" alt=">">
            </td>
            <td style="width:65px; vertical-align:top; padding:1px 0; white-space:nowrap;">Jam Kerja</td>
            <td style="width:14px; vertical-align:top; text-align:center; padding:1px 0;">:</td>
            <td style="vertical-align:top; padding:1px 0; white-space:nowrap;">
              <nobr>{{ $jamKerjaLama }}</nobr>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- TEMPAT TUGAS BARU -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%;"></td>
      <td style="width:79%; vertical-align:top; padding-top:3px; font-size:10px; text-decoration:underline;">
        Tempat Tugas Baru :
      </td>
    </tr>

    <!-- JABATAN & JAM KERJA BARU -->
    <tr>
      <td style="width:18%;"></td>
      <td style="width:3%;"></td>
      <td style="width:79%; vertical-align:top; padding-top:1px;">
        <table style="width:100%; border-collapse:collapse; font-size:10px;">
          <tr>
            <td style="width:20px; vertical-align:top; padding:1px 0;">
              <img src="{{ $arrowJp }}" width="15" height="8" style="vertical-align:top; margin-top:2px; display:inline-block;" alt=">">
            </td>
            <td style="width:65px; vertical-align:top; padding:1px 0; white-space:nowrap;">Jabatan</td>
            <td style="width:14px; vertical-align:top; text-align:center; padding:1px 0;">:</td>
            <td style="vertical-align:top; line-height:1.25; padding:1px 0; white-space:nowrap;">
              <nobr>{{ $t_mutasi->m_posisi_baru?->name ?? '-' }}</nobr><br>
              <nobr>{{ $t_mutasi->m_sbu_baru?->name ?? ($t_mutasi->m_sub_baru?->name ?? '-') }}</nobr><br>
              <nobr>{{ $t_mutasi->m_sub_baru?->address ?? ($t_mutasi->m_branch_baru?->address ?? '') }}</nobr>
            </td>
          </tr>
          <tr>
            <td style="width:20px; vertical-align:top; padding:1px 0;">
              <img src="{{ $arrowJp }}" width="15" height="8" style="vertical-align:top; margin-top:2px; display:inline-block;" alt=">">
            </td>
            <td style="width:65px; vertical-align:top; padding:1px 0; white-space:nowrap;">Jam Kerja</td>
            <td style="width:14px; vertical-align:top; text-align:center; padding:1px 0;">:</td>
            <td style="vertical-align:top; padding:1px 0; white-space:nowrap;">
              <nobr>{{ $jamKerjaBaru }}</nobr>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- POIN 2 -->
    <tr>
      <td style="width:18%; vertical-align:top; padding-top:4px;"></td>
      <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">2.</td>
      <td style="width:79%; vertical-align:top; line-height:1.25; padding-top:4px; text-align:justify;">
        Keputusan ini berlaku sejak {{ $kotaTerbit }} dan akan ditinjau kembali jika diperlukan.
      </td>
    </tr>
  </table>

  <!-- BAGIAN 4: PENUTUP & TANDA TANGAN (PERSIS GAMBAR 2) -->
  <table style="width:100%; margin-top:8px; font-size:10.5px; border-collapse:collapse;">
    <tr>
      <td style="width:55%;"></td>
      <td style="width:45%; vertical-align:top; line-height:1.35;">
        <table style="border-collapse:collapse; font-size:10.5px;">
          <tr>
            <td style="padding-bottom:1px; white-space:nowrap;">Dikeluarkan di</td>
            <td style="padding:0 6px 1px 14px; text-align:center;">:</td>
            <td style="padding-bottom:1px; white-space:nowrap;">{{ $kotaTerbit }}</td>
          </tr>
          <tr>
            <td style="padding-bottom:1px; white-space:nowrap;">Pada Tanggal</td>
            <td style="padding:0 6px 1px 14px; text-align:center;">:</td>
            <td style="padding-bottom:1px; white-space:nowrap;">{{ $tanggalTerbit }}</td>
          </tr>
        </table>
        <div style="margin-top:6px; white-space:nowrap;">
          {{ $companyName }}
        </div>
        <div style="height:32px;"></div>
        @if(!empty($t_mutasi->signature?->nama_lengkap))
          <div style="font-weight:bold; white-space:nowrap;">
            {{ strtoupper($t_mutasi->signature->nama_lengkap) }}
          </div>
          <div style="font-style:italic; font-weight:bold; white-space:nowrap;">
            {{ strtoupper($t_mutasi->signature->m_posisi?->name ?? 'HUMAN CAPITAL DEPT.') }}
          </div>
        @else
          <div style="font-weight:bold; white-space:nowrap;">
            NAMA YANG TTD
          </div>
          <div style="font-style:italic; font-weight:bold; white-space:nowrap;">
            JABATAN
          </div>
        @endif
      </td>
    </tr>
  </table>

  <!-- BAGIAN 5: TEMBUSAN (PERSIS GAMBAR 2) -->
  <table style="width:100%; margin-top:6px; font-size:9.5px; border-collapse:collapse;">
    <tr>
      <td style="width:100%; line-height:1.3;">
        Tembusan&nbsp;&nbsp;&nbsp;&nbsp;:<br>
        <div style="padding-left:14px; margin-top:1px;">
          @if($tembusan && count($tembusan) > 0)
            @foreach($tembusan as $index => $t)
              {{ $index + 1 }}. {{ is_array($t) ? ($t['value'] ?? '') : ($t->value ?? '') }}<br>
            @endforeach
          @elseif($isJpBooks)
            1. Direksi Holding<br>
            2. Direksi PT JPMU<br>
            3. HRD Cabang
          @else
            1. Direksi<br>
            2. Human Capital Dept.<br>
            3. Yang Bersangkutan<br>
            4. Arsip
          @endif
        </div>
      </td>
    </tr>
  </table>

  <!-- BAGIAN 6: FOOTER RESMI JP BOOKS -->
  @if($footerJp)
    <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:6px;">
      <tr>
        <td width="100%" style="width:100%; padding:0; text-align:left;">
          <img src="{{ $footerJp }}" style="width:100%; max-height:65px; height:auto; display:block;"
            alt="Footer JP Books">
        </td>
      </tr>
    </table>
  @else
    @include('projects.web_sk_footer')
  @endif

</div>