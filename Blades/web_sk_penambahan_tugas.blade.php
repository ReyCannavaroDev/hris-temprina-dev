@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('d F Y');
$tanggalIndoLengkap = $namaHari . ', ' . $tanggalIndo;

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

$dest = strtoupper(
    ($t_mutasi?->m_sub_baru?->name ?? '') . ' ' .
    ($t_mutasi?->m_sbu_baru?->name ?? '') . ' ' .
    ($t_mutasi?->m_sub_baru?->kode ?? '') . ' ' .
    ($t_mutasi?->m_sbu_baru?->kode ?? '')
);
$origin = strtoupper(
    ($t_mutasi?->m_sub_lama?->name ?? '') . ' ' .
    ($t_mutasi?->m_sbu_lama?->name ?? '') . ' ' .
    ($t_mutasi?->m_sub_lama?->kode ?? '') . ' ' .
    ($t_mutasi?->m_sbu_lama?->kode ?? '') . ' ' .
    ($t_mutasi?->m_kary?->m_sbu?->name ?? '') . ' ' .
    ($t_mutasi?->m_kary?->m_sbu?->kode ?? '')
);

$checkIsJp = function($str) {
    return str_contains($str, 'JEPE') || 
           str_contains($str, 'JP BOOKS') || 
           str_contains($str, 'JPBOOKS') || 
           str_contains($str, 'JPMU') || 
           str_contains($str, 'SAHABAT EDUKASI') ||
           preg_match('/\bJP\b/', $str);
};

$isJpBooks = $checkIsJp($dest) || (empty(trim($dest)) && $checkIsJp($origin));
$companyName = $isJpBooks ? 'PT. JePe Press Media Utama' : 'PT Temprina Media Grafika';

$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

$tugasTambahan = $t_mutasi?->m_posisi_baru?->name;
if (empty($tugasTambahan) || $tugasTambahan === '-') {
    $tugasTambahan = (!empty($t_mutasi?->deskripsi) && $t_mutasi?->deskripsi !== '-') 
        ? $t_mutasi?->deskripsi 
        : ((!empty($t_mutasi?->keterangan) && $t_mutasi?->keterangan !== '-') ? $t_mutasi?->keterangan : '-');
}
if ($tugasTambahan && $tugasTambahan !== '-' && !str_ends_with(trim($tugasTambahan), '.')) {
    $tugasTambahan = trim($tugasTambahan) . '.';
}

$alamatLama = $t_mutasi?->m_sub_lama?->address 
            ?? $t_mutasi?->m_branch_lama?->address 
            ?? $t_mutasi?->m_kary?->m_branch?->address 
            ?? $t_mutasi?->m_kary?->m_sub?->address
            ?? 'Jl. Karah Agung 45 Surabaya';

// Helper function to resolve media to base64
$resolveMedia = function($rawPath) {
    if (empty($rawPath)) return '';
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
    return '';
};

$mediaQR = null;
try {
    $mediaQR = \DB::table('m_media')->where('is_active', true)->where('kode', 'QR-TEMPRINA')->first();
} catch (\Throwable $th) {}

$qrSrc = $mediaQR ? $resolveMedia($mediaQR->file_path) : '';
if (empty($qrSrc)) {
    $qrCandidates = [
        'media/qr_temprina.jpg',
        'public/media/qr_temprina.jpg',
        'images/qr_temprina.jpg',
        'storage/qr_temprina.jpg',
        'uploads/m_media/qr_temprina.jpg'
    ];
    foreach ($qrCandidates as $cand) {
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
                $qrSrc = 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
                break 2;
            }
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

<div style="font-family:'Times New Roman',serif; width:90%; margin:auto; font-size:11.5px; line-height:1.45; color:#000;">

  @if(view()->exists('projects.web_sk_kop'))
    @include('projects.web_sk_kop')
  @elseif(view()->exists('web_sk_kop'))
    @include('web_sk_kop')
  @endif

  <!-- JUDUL -->
  <table style="width:100%; margin-top:16px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:15px;">
        <span style="border-bottom:1.5px solid black; padding-bottom:1px;">SURAT KEPUTUSAN</span>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:11px; padding-top:4px;">
        No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
      </td>
    </tr>
  </table>

  <!-- TENTANG -->
  <table style="width:100%; margin-top:14px; border-collapse:collapse;">
    <tr>
      <td style="text-align:center; font-size:11px; font-weight:bold; line-height:1.4;">
        TENTANG <br>
        PENAMBAHAN TUGAS
      </td>
    </tr>
  </table>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:14px; border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:25%; vertical-align:top;">MEMPERHATIKAN</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:72%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          @if($memperhatikan && count($memperhatikan) > 0)
            @foreach($memperhatikan as $item)
            <tr>
              <td style="width:4%; vertical-align:top;">•</td>
              <td style="width:96%; vertical-align:top; text-align:justify; padding-bottom:3px;">
                {{ is_array($item) ? ($item['value'] ?? '') : ($item->value ?? '') }}
              </td>
            </tr>
            @endforeach
          @else
            <tr>
              <td style="width:4%; vertical-align:top;">•</td>
              <td style="width:96%; vertical-align:top; text-align:justify; padding-bottom:3px;">
                Keputusan hasil koordinasi manajemen
              </td>
            </tr>
            <tr>
              <td style="width:4%; vertical-align:top;">•</td>
              <td style="width:96%; vertical-align:top; text-align:justify; padding-bottom:3px;">
                Upaya optimalisasi SDM
              </td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  <!-- SPACE PEMISAH -->
  <div style="height:14px; line-height:14px;">&nbsp;</div>

  <!-- MEMUTUSKAN SERTA MENETAPKAN -->
  <table style="width:100%; border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:25%; vertical-align:top; line-height:1.35;">
        MEMUTUSKAN<br>
        &nbsp;&nbsp;&nbsp;&nbsp;SERTA<br>
        MENETAPKAN
      </td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:72%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse; font-size:11px; line-height:1.45;">
          <!-- POINT 1 -->
          <tr>
            <td style="width:4%; vertical-align:top;">1.</td>
            <td style="width:96%; vertical-align:top;">
              Terhitung efektif {{ $tanggalIndoLengkap }}
            </td>
          </tr>
          <tr>
            <td style="width:4%;"></td>
            <td style="width:96%; vertical-align:top; padding-top:2px;">
              <table style="width:100%; border-collapse:collapse; font-size:11px; line-height:1.45;">
                <tr>
                  <td style="width:5%; vertical-align:top;">•</td>
                  <td style="width:15%; vertical-align:top;">Nama</td>
                  <td style="width:3%; vertical-align:top; text-align:center;">:</td>
                  <td style="width:77%; vertical-align:top; font-weight:bold;">
                    {{ $t_mutasi->m_kary?->nama_lengkap ?? '-' }}
                  </td>
                </tr>
                <tr>
                  <td style="width:5%; vertical-align:top; padding-top:2px;">•</td>
                  <td style="width:15%; vertical-align:top; padding-top:2px;">Bagian</td>
                  <td style="width:3%; vertical-align:top; text-align:center; padding-top:2px;">:</td>
                  <td style="width:77%; vertical-align:top; padding-top:2px;">
                    {{ $t_mutasi->m_posisi_lama?->name ?? '-' }}<br>
                    {{ $companyName }}<br>
                    {{ $alamatLama }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- POINT 2 -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:8px;">2.</td>
            <td style="width:96%; vertical-align:top; padding-top:8px;">
              Tambahan tugas :
            </td>
          </tr>
          <tr>
            <td style="width:4%;"></td>
            <td style="width:96%; vertical-align:top; padding-top:2px; padding-left:14px;">
              {{ $tugasTambahan }}
            </td>
          </tr>

          <!-- POINT 3 -->
          <tr>
            <td style="width:4%; vertical-align:top; padding-top:8px;">3.</td>
            <td style="width:96%; vertical-align:top; padding-top:8px; text-align:justify;">
              Keputusan ini berlaku sejak tanggal efektif dan akan ditinjau kembali jika diperlukan.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- PENUTUP / TANDA TANGAN -->
  <table style="width:100%; margin-top:22px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:55%;"></td>
      <td style="width:45%; vertical-align:top;">
        <table style="border-collapse:collapse; font-size:11px;">
          <tr>
            <td style="padding-bottom:2px; white-space:nowrap;">Dikeluarkan di</td>
            <td style="padding-left:4px; padding-right:6px; padding-bottom:2px; text-align:center;">:</td>
            <td style="padding-bottom:2px; white-space:nowrap;">{{ $kotaTerbit }}</td>
          </tr>
          <tr>
            <td style="padding-bottom:4px; white-space:nowrap;">Pada Tanggal</td>
            <td style="padding-left:4px; padding-right:6px; padding-bottom:4px; text-align:center;">:</td>
            <td style="padding-bottom:4px; white-space:nowrap;">{{ $tanggalTerbit }}</td>
          </tr>
        </table>
        <div style="font-weight:bold; margin-top:6px;">
          {{ $companyName }}
        </div>
        <div style="height:55px;"></div>
        @if($t_mutasi->signature?->nama_lengkap)
          <div style="font-weight:bold;">
            {{ $t_mutasi->signature->nama_lengkap }}
          </div>
          <div>
            {{ $t_mutasi->signature->m_posisi?->name ?? 'Human Capital Dept.' }}
          </div>
        @else
          <div style="font-weight:bold;">
            ( ........................................ )
          </div>
          <div>
            Human Capital Dept.
          </div>
        @endif
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN -->
  <div style="margin-top:14px; font-size:10px; line-height:1.4;">
    <div><b>Tembusan</b></div>
    <div style="padding-left:14px; margin-top:2px;">
      @if($tembusan && count($tembusan) > 0)
        @foreach($tembusan as $index => $t)
          {{ $index + 1 }}. {{ is_array($t) ? ($t['value'] ?? '') : ($t->value ?? '') }}<br>
        @endforeach
      @else
        1. Direksi<br>
        2. GM/Vice GM Business Service
      @endif
    </div>
  </div>

  @if($isJpBooks)
    @if(view()->exists('projects.web_sk_footer'))
      @include('projects.web_sk_footer')
    @elseif(view()->exists('web_sk_footer'))
      @include('web_sk_footer')
    @endif
  @else
    <!-- FOOTER CABANG TEMPRINA (PERSIS GAMBAR 2 LINGKARAN BIRU) -->
    <table style="width:100%; border-collapse:collapse; margin-top:16px; border-top:0.5px solid #aaa; padding-top:4px;">
      <tr>
        <td style="width:87%; vertical-align:middle;">
          <div style="font-size:6.8px; line-height:1.35; color:#222; text-align:justify;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, Email : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, Email : Cengkareng@temprina.com<br>
            <b>Semarang :</b> 0298-5200940, Email : tmgsrng@gmail.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, Email : Nganjuk@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, Email : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, Email : Bali@temprina.com
          </div>
        </td>
        <td style="width:13%; text-align:right; vertical-align:middle;">
          @if($qrSrc)
            <img src="{{ $qrSrc }}" width="42" height="42" alt="QR Code">
          @endif
        </td>
      </tr>
    </table>
  @endif

</div>