@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

// Tembusan & Konsiderans Mengingat
$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? collect();
if ($tembusan instanceof \Illuminate\Support\Collection) {
    $tembusan = $tembusan->unique(fn($i) => trim($i->value ?? $i['value'] ?? ''))->values();
}

$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? collect();
if ($memperhatikan instanceof \Illuminate\Support\Collection) {
    $memperhatikan = $memperhatikan->unique(fn($i) => trim($i->value ?? $i['value'] ?? ''))->values();
}

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

// Status Karyawan Lama
$statusLama = $t_mutasi?->status_kary_lama?->value 
            ?? $t_mutasi?->m_kary?->status_karyawan?->value 
            ?? 'Karyawan Kontrak';

// Data Pengangkatan
$tglLahirRaw = $t_mutasi?->m_kary?->tgl_lahir;
$tglLahirDisplay = '-';
if ($tglLahirRaw) {
    try {
        $cLahir = Carbon::parse($tglLahirRaw);
        $tglLahirDisplay = $cLahir->translatedFormat('d F Y');
    } catch (\Throwable $e) {
        $tglLahirDisplay = $tglLahirRaw;
    }
}

$jabatan = $t_mutasi?->m_posisi_baru?->name 
         ?? $t_mutasi?->m_kary?->m_posisi?->name 
         ?? '-';

$penempatan = $t_mutasi?->m_branch_baru?->name 
            ?? $t_mutasi?->m_kary?->m_branch?->name 
            ?? $companyName;

// Kop Asset Resolution (Base64 data URIs)
$mediaTMG = \DB::table('m_media')->where('is_active', true)->where('kode', 'LOGO-TMG')->first();
$mediaISO = \DB::table('m_media')->where('is_active', true)->where('kode', 'BANNER-ISO-TEMPRINA')->first();

$resolveMediaPath = function($rawPath) {
    if (empty($rawPath)) return '';
    $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
    $clean = ltrim($clean, '/');

    $candidates = [
        $clean,
        'uploads/m_media/' . $clean,
        'uploads/' . $clean,
        'storage/' . $clean,
        'images/' . $clean
    ];

    foreach ($candidates as $cand) {
        $full = function_exists('public_path') ? public_path($cand) : (function_exists('base_path') ? base_path('public/' . $cand) : ('/opt/www/hris/public/' . $cand));
        if ($full && file_exists($full) && is_file($full)) {
            $type = pathinfo($full, PATHINFO_EXTENSION);
            return 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
        }
    }
    return $rawPath;
};

$logoTmgSrc = $mediaTMG ? $resolveMediaPath($mediaTMG->file_path) : '';
$bannerIsoSrc = $mediaISO ? $resolveMediaPath($mediaISO->file_path) : '';
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:100%; font-size:11px; line-height:1.35; color:#000;">

  <!-- KOP SURAT RESMI -->
  @if($isJpBooks)
    @include('projects.web_sk_kop')
  @else
    <table style="width:100%; border-collapse:collapse; margin-bottom:0;">
      <tr>
        <td style="width:38%; vertical-align:middle;">
          @if(!empty($logoTmgSrc))
            <img src="{{ $logoTmgSrc }}" style="width:165px; height:auto;" alt="Temprina Jawa Pos Group">
          @else
            <div style="font-weight:bold; font-size:20px; color:#005596; letter-spacing:1px;">temprina</div>
            <div style="font-size:9px; font-weight:bold; color:#f37023; letter-spacing:2px; margin-top:-2px;">Jawa Pos Group</div>
          @endif
        </td>
        <td style="width:25%; vertical-align:middle; text-align:center;">
          @if(!empty($bannerIsoSrc))
            <img src="{{ $bannerIsoSrc }}" style="width:145px; height:auto;" alt="ISO Certifications">
          @endif
        </td>
        <td style="width:37%; vertical-align:top; text-align:right; font-size:7.5px; line-height:1.2; color:#222;">
          <b style="font-size:8px;">Head Office:</b><br>
          Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik<br>
          Telp: 031 898 2999, Fax: Office 031-898 2065<br>
          Marketing: 031-898 1777, Purchasing: 031-898 2066<br>
          HRD: 031-898 3622<br>
          www.temprina.com E-mail: temprina@temprina.com
        </td>
      </tr>
    </table>
    <!-- DOUBLE LINE SEPARATOR -->
    <div style="border-top:1px solid #000; margin-top:5px;"></div>
    <div style="border-top:2px solid #000; margin-top:1.5px; margin-bottom:8px;"></div>
  @endif

  <!-- JUDUL SURAT -->
  <table style="width:100%; border-collapse:collapse; margin-top:4px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:14px; letter-spacing:2px; text-decoration:underline;">
        S U R A T &nbsp; K E P U T U S A N
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11px; padding-top:2px;">
        Direksi {{ $companyName }}
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:10px; padding-top:6px;">
        Tentang
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:11.5px; padding-top:2px;">
        PENGANGKATAN KARYAWAN TETAP Th. {{ $carbonDate->year }}
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:10.5px; font-weight:bold; padding-top:2px;">
        No. {{ $t_mutasi->nomor ?? '-' }}
      </td>
    </tr>
  </table>

  <!-- PREAMBLE DIREKSI -->
  <div style="margin-top:10px; font-size:11px; font-weight:bold;">
    Direksi {{ $companyName }}
  </div>

  <!-- KONSIDERANS MENIMBANG & MENGINGAT -->
  <table style="width:100%; border-collapse:collapse; margin-top:4px; font-size:11px;">
    <!-- MENIMBANG -->
    <tr>
      <td style="width:18%; vertical-align:top; font-weight:bold;">MENIMBANG</td>
      <td style="width:3%; vertical-align:top; text-align:center; font-weight:bold;">:</td>
      <td style="width:79%; vertical-align:top; text-align:justify;">
        Bahwa nama karyawan <b>{{ strtoupper($t_mutasi->m_kary?->nama_lengkap ?? '-') }}</b><br>
        {{ $statusLama }} - {{ $companyName }}
      </td>
    </tr>
    <tr><td colspan="3" style="height:6px;"></td></tr>
    <!-- MENGINGAT -->
    <tr>
      <td style="width:18%; vertical-align:top; font-weight:bold;">MENGINGAT</td>
      <td style="width:3%; vertical-align:top; text-align:center; font-weight:bold;">:</td>
      <td style="width:79%; vertical-align:top;">
        @if($memperhatikan && count($memperhatikan) > 0)
          <table style="width:100%; border-collapse:collapse; font-size:11px;">
            @foreach($memperhatikan as $index => $item)
              <tr>
                <td style="width:4%; vertical-align:top;">{{ $index + 1 }}.</td>
                <td style="width:96%; vertical-align:top; text-align:justify;">{{ $item->value ?? $item['value'] }}</td>
              </tr>
            @endforeach
          </table>
        @else
          <table style="width:100%; border-collapse:collapse; font-size:11px;">
            <tr>
              <td style="width:4%; vertical-align:top;">1.</td>
              <td style="width:96%; vertical-align:top;">Anggaran Dasar {{ $companyName }}</td>
            </tr>
            <tr>
              <td style="width:4%; vertical-align:top;">2.</td>
              <td style="width:96%; vertical-align:top;">Peraturan Perusahaan {{ $companyName }}</td>
            </tr>
          </table>
        @endif
      </td>
    </tr>
  </table>

  <!-- MEMUTUSKAN / MENETAPKAN -->
  <div style="text-align:center; font-weight:bold; font-size:11.5px; margin-top:10px; letter-spacing:1px;">
    MEMUTUSKAN
  </div>
  <div style="font-weight:bold; font-size:11px; margin-top:4px;">
    MENETAPKAN :
  </div>

  <!-- DIKTUM KEPUTUSAN -->
  <table style="width:100%; border-collapse:collapse; margin-top:4px; font-size:11px;">
    <!-- PERTAMA -->
    <tr>
      <td style="width:11%; vertical-align:top;">Pertama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:3%; vertical-align:top; text-align:center;">~</td>
      <td style="width:83%; vertical-align:top;">
        <table style="width:100%; border-collapse:collapse; font-size:11px;">
          <tr>
            <td style="width:32%; vertical-align:top;">Mengangkat Karyawan</td>
            <td style="width:3%; vertical-align:top; text-align:center;">:</td>
            <td style="width:65%; vertical-align:top; font-weight:bold;">{{ strtoupper($t_mutasi->m_kary?->nama_lengkap ?? '-') }}</td>
          </tr>
          <tr>
            <td style="width:32%; vertical-align:top;">Tanggal Lahir</td>
            <td style="width:3%; vertical-align:top; text-align:center;">:</td>
            <td style="width:65%; vertical-align:top;">{{ $tglLahirDisplay }}</td>
          </tr>
          <tr>
            <td style="width:32%; vertical-align:top;">Bagian dan Tempat</td>
            <td style="width:3%; vertical-align:top; text-align:center;">:</td>
            <td style="width:65%; vertical-align:top;"><b>{{ $jabatan }}</b> - {{ $penempatan }}</td>
          </tr>
          <tr>
            <td style="width:32%; vertical-align:top;">Karyawan Tetap</td>
            <td style="width:3%; vertical-align:top; text-align:center;">:</td>
            <td style="width:65%; vertical-align:top; font-weight:bold;">{{ $tanggalIndo }}</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr><td colspan="4" style="height:6px;"></td></tr>
    <!-- KEDUA -->
    <tr>
      <td style="width:11%; vertical-align:top;">Kedua</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:3%; vertical-align:top; text-align:center;">~</td>
      <td style="width:83%; vertical-align:top; text-align:justify;">
        Karyawan yang namanya tersebut di atas harus memenuhi semua kewajiban dan tanggung jawab yang ada serta mentaati segala peraturan atau hukum yang ada / berlaku dan tunduk terhadap keputusan Perusahaan.
      </td>
    </tr>
    <tr><td colspan="4" style="height:6px;"></td></tr>
    <!-- KETIGA -->
    <tr>
      <td style="width:11%; vertical-align:top;">Ketiga</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:3%; vertical-align:top; text-align:center;">~</td>
      <td style="width:83%; vertical-align:top; text-align:justify;">
        Surat keputusan ini berlaku sejak tanggal ditetapkan. Jika di kemudian hari terdapat kesalahan dalam surat keputusan ini, maka akan diadakan perbaikan seperlunya.
      </td>
    </tr>
  </table>

  <!-- TANDA TANGAN -->
  <table style="width:100%; margin-top:16px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:55%;"></td>
      <td style="width:45%; vertical-align:top;">
        Ditetapkan di&nbsp;&nbsp;: {{ $kotaTerbit }}<br>
        Pada Tanggal&nbsp;&nbsp;: {{ $tanggalTerbit }}
        <div style="margin-top:6px; font-weight:bold;">
          {{ $companyName }}
        </div>
        <div style="height:48px;"></div>
        <div style="font-weight:bold; text-decoration:underline;">
          {{ $t_mutasi->signature?->nama_lengkap ?? '( ........................................ )' }}
        </div>
        <div style="font-style:italic;">
          {{ $t_mutasi->signature?->m_posisi?->name ?? 'Direktur Utama' }}
        </div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN (OPSIONAL JIKA DIISI) -->
  @if($tembusan && count($tembusan) > 0)
    <table style="width:100%; margin-top:8px; font-size:10px; border-collapse:collapse;">
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

  <!-- FOOTER CABANG -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <div style="position:fixed; bottom:0; left:0; right:0; width:100%;">
      <div style="border-top:0.5px solid #777; margin-bottom:3px;"></div>
      <table style="width:100%; font-size:6.8px; line-height:1.2; color:#333; border-collapse:collapse;">
        <tr>
          <td style="width:50%; vertical-align:top;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com<br>
            <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com<br>
            <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com
          </td>
          <td style="width:50%; vertical-align:top;">
            <b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
            <b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
            <b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
            <b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
          </td>
        </tr>
      </table>
    </div>
  @endif

</div>