@php
  use Carbon\Carbon;
  use Carbon\CarbonPeriod;
  use App\Models\CustomModels\t_mutasi;
  use App\Models\CustomModels\t_jadwal_kerja_n;
  use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
  use App\Models\CustomModels\m_kary_det_jabatan;

  Carbon::setLocale('id');
  $req = app()->request;

  $id = $req->id;
  $t_mutasi = t_mutasi::find($id);

  $carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
  $tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

  $terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
  $tanggalTerbit = $terbitDate->translatedFormat('d F Y');

  $tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? null;
  $memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;

  $karyawan_jabatan = null;
  if (!empty($t_mutasi?->m_kary_id)) {
    try {
      $karyawan_jabatan = m_kary_det_jabatan::where(function ($q) use ($t_mutasi) {
        $q->where('m_karyawan_id', $t_mutasi->m_kary_id)
          ->orWhere('m_kary_id', $t_mutasi->m_kary_id);
      })
        ->orderBy('start_time', 'asc')
        ->first();
    } catch (\Throwable $th) {
    }
  }

  $awalKerja = $karyawan_jabatan && !empty($karyawan_jabatan->start_time ?? $karyawan_jabatan->tgl_mulai)
    ? Carbon::parse($karyawan_jabatan->start_time ?? $karyawan_jabatan->tgl_mulai)->translatedFormat('d F Y')
    : ($t_mutasi?->m_kary?->tgl_masuk ? Carbon::parse($t_mutasi->m_kary->tgl_masuk)->translatedFormat('d F Y') : '-');

  $akhirKerja = $karyawan_jabatan && !empty($karyawan_jabatan->end_time ?? $karyawan_jabatan->tgl_selesai)
    ? Carbon::parse($karyawan_jabatan->end_time ?? $karyawan_jabatan->tgl_selesai)->translatedFormat('d F Y')
    : 'Sekarang';

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

  $rawStatus = $t_mutasi?->status_kary_lama?->value ?? $t_mutasi?->m_kary?->status_kary?->value ?? '-';
  if ($rawStatus !== '-' && !str_contains(strtolower($rawStatus), 'yang bersangkutan')) {
    $statusText = 'Yang bersangkutan adalah ' . strtoupper($rawStatus);
  } else {
    $statusText = $rawStatus;
  }

  $resolveMedia = function ($rawPath) {
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
  // Icon Pointer Segitiga Hitam Base64 (100% kompatibel dengan PDF tanpa resiko jadi '?')
  $arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAASElEQVR4nJWNQQ4AIAjD3OL/vzxPJEZhKCcCbQpJ42cYCwB9CSF1IrOjk1g9qhpdPquxE8ZRmy+CJDwXdtgWTtAWKvgqODBmAXo9GyO93OPeAAAAAElFTkSuQmCC';
@endphp

<div style="font-family:'Times New Roman',serif; width:92%; margin:auto; font-size:11.5px; line-height:1.45; color:#000;">

  @include('projects.web_sk_kop')

  <table style="width:100%; border-collapse:collapse; margin-top:14px; font-family:'Times New Roman', serif;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:15px;">
        <span style="border-bottom:1.5px solid black; padding-bottom:1px; letter-spacing:0.5px;">SURAT KETERANGAN KERJA</span>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11.5px; font-weight:bold; padding-top:4px;">
        No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
      </td>
    </tr>
  </table>

  <p style="margin-top:18px; margin-bottom:6px; font-size:11.5px;">Yang bertanda tangan di bawah ini :</p>

  <table style="width:100%; border-collapse:collapse; margin-bottom:12px; font-size:11.5px; line-height:1.45;">
    <tr>
      <td style="width:4%;"></td>
      <td style="width:3%; vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="width:16%; vertical-align:top;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:74%; vertical-align:top;">
        {{ $t_mutasi->signature?->nama_lengkap ?? '( ........................................ )' }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top; padding-top:3px;">Bagian</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">
        {{ $t_mutasi->signature?->m_posisi?->name ?? 'Kadiv. Human Capital Holding' }}<br>
        {{ $t_mutasi->signature?->m_subcomp?->name ?? $companyName }}<br>
        {{ $t_mutasi->signature?->m_subcomp?->address ?? 'Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik' }}
      </td>
    </tr>
  </table>

  <p style="margin-top:14px; margin-bottom:6px; font-size:11.5px;">Menerangkan bahwa :</p>

  <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:11.5px; line-height:1.45;">
    <tr>
      <td style="width:4%;"></td>
      <td style="width:3%; vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="width:16%; vertical-align:top;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:74%; vertical-align:top; font-weight:bold;">
        {{ strtoupper($t_mutasi->m_kary?->nama_lengkap ?? '-') }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top; padding-top:3px;">Status</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">
        {{ $statusText }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top; padding-top:3px;">Bagian</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">
        {{ $t_mutasi->m_posisi_lama?->name ?? '-' }}<br>
        {{ $t_mutasi->m_sub_lama?->name ?? $companyName }}<br>
        {{ $t_mutasi->m_sub_lama?->address ?? '' }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top; padding-top:3px;">Awal Kerja</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">
        {{ $awalKerja }} s/d {{ $akhirKerja }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:2px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top; padding-top:3px;">Keperluan</td>
      <td style="vertical-align:top; text-align:center; padding-top:3px;">:</td>
      <td style="vertical-align:top; padding-top:3px;">
        {{ (!empty($t_mutasi->deskripsi) && $t_mutasi->deskripsi !== '-') ? $t_mutasi->deskripsi : ((!empty($t_mutasi->keterangan) && $t_mutasi->keterangan !== '-') ? $t_mutasi->keterangan : '-') }}
      </td>
    </tr>
  </table>

  <p style="margin-top:16px; margin-bottom:12px; font-size:11.5px; text-align:justify; line-height:1.45;">
    Demikian surat keterangan kerja ini dibuat agar dapat dipergunakan sebagaimana mestinya.
  </p>

  <table style="width:100%; margin-top:20px; font-family:'Times New Roman', serif; font-size:11.5px; border-collapse:collapse;">
    <tr>
      <td style="width:55%; vertical-align:top;">
        <div>{{ $kotaTerbit }}, {{ $tanggalTerbit }}</div>
        <div style="font-weight:bold; margin-top:2px;">{{ $companyName }}</div>
        <div style="height:52px;"></div>
        @if($t_mutasi->signature?->nama_lengkap)
          <div style="font-weight:bold; text-decoration:underline;">
            {{ $t_mutasi->signature->nama_lengkap }}
          </div>
          <div style="font-style:italic;">
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
      <td style="width:45%;"></td>
    </tr>
  </table>

  @if($isJpBooks)
    @if(view()->exists('projects.web_sk_footer'))
      @include('projects.web_sk_footer', ['footer' => 'jpbooks'])
    @elseif(view()->exists('web_sk_footer'))
      @include('web_sk_footer', ['footer' => 'jpbooks'])
    @endif
  @else
    <!-- FOOTER CABANG TEMPRINA (SESUAI FORMAT GAMBAR 2) -->
    <table style="width:100%; border-collapse:collapse; margin-top:16px; border-top:0.5px solid #aaa; padding-top:4px;">
      <tr>
        <td style="width:87%; vertical-align:middle;">
          <div style="font-size:6.8px; line-height:1.35; color:#222; text-align:justify;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, Email : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, Email : Cengkareng@temprina.com<br>
            <b>Semarang :</b> 0298-5200940 Fax : 024-7462135, Email : tmgsmg@gmail.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, Email : Nganjuk@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, Email : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, Email : Bali@temprina.com
          </div>
        </td>
        <td style="width:13%; text-align:right; vertical-align:middle;">
          @if(!empty($qrSrc))
            <img src="{{ $qrSrc }}" width="42" height="42" alt="QR Code">
          @endif
        </td>
      </tr>
    </table>
  @endif

</div>