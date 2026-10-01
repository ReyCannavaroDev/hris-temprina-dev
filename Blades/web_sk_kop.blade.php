@php
    // Query media items from m_media
    $mediaTMG = \DB::table('m_media')->where('is_active', true)->where('kode', 'LOGO-TMG')->first();
    $mediaISO = \DB::table('m_media')->where('is_active', true)->where('kode', 'BANNER-ISO-TEMPRINA')->first();
    $mediaJP = \DB::table('m_media')->where('is_active', true)->whereIn('kode', ['LOGO-JPBOOKS', 'HEADER-JPBOOKS'])->orderBy('id', 'desc')->first();

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
        return $rawPath; // fallback to URL
    };

    $logoTmgSrc = $mediaTMG ? $resolveMediaPath($mediaTMG->file_path) : '';
    $bannerIsoSrc = $mediaISO ? $resolveMediaPath($mediaISO->file_path) : '';
    $logoJpSrc = $mediaJP ? $resolveMediaPath($mediaJP->file_path) : '';

    // Deteksi apakah perusahaan adalah JP Books
    $isJPBooks = false;
    if (isset($isJpBooks)) {
        $isJPBooks = (bool)$isJpBooks;
    } elseif (isset($t_mutasi)) {
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

        $checkIsJp = function($str) {
            return str_contains($str, 'JEPE') || 
                   str_contains($str, 'JP BOOKS') || 
                   str_contains($str, 'JPBOOKS') || 
                   str_contains($str, 'JPMU') || 
                   str_contains($str, 'SAHABAT EDUKASI') ||
                   preg_match('/\bJP\b/', $str);
        };

        if ($checkIsJp($dest)) {
            $isJPBooks = true;
        } elseif (empty(trim($dest)) && $checkIsJp($origin)) {
            $isJPBooks = true;
        }
    }
@endphp

@if($isJPBooks)
    <!-- KOP JP BOOKS -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:15px; padding-bottom:4px;">
        <tr>
            <td style="width:35%; vertical-align:middle;">
                @if($logoJpSrc)
                    <img src="{{ $logoJpSrc }}" style="max-height:52px; max-width:210px;" alt="JP Books">
                @else
                    <div style="display:inline-block; background-color:#ff8a00; padding:6px 14px; border-radius:4px; font-size:22px; font-weight:bold; color:#0055aa; letter-spacing:1px; border:2px solid #0055aa;">JP BOOKS</div>
                @endif
            </td>
            <td style="width:65%; text-align:left; vertical-align:middle;">
                <div style="font-size:22px; font-weight:bold; color:#0055aa; font-family:Arial, Helvetica, sans-serif; letter-spacing:0.5px;">
                    PT. JePe Press Media Utama
                </div>
            </td>
        </tr>
    </table>
@else
    <!-- KOP TEMPRINA -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="width:62%; vertical-align:middle;">
                <table style="border-collapse:collapse;">
                    <tr>
                        <td style="vertical-align:middle; padding-right:8px;">
                            @if($logoTmgSrc)
                                <img src="{{ $logoTmgSrc }}" width="145" alt="Temprina">
                            @else
                                <div style="font-size:22px; font-weight:bold; color:#0055aa; letter-spacing:0.5px;">temprina</div>
                                <div style="font-size:9px; color:#555; margin-top:-2px;">Jawa Pos Group</div>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">
                            @if($bannerIsoSrc)
                                <img src="{{ $bannerIsoSrc }}" width="185" alt="ISO Certifications">
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:38%; text-align:right; vertical-align:top;">
                <div style="font-size:7px; line-height:1.2; color:#222;">
                    <b>Head Office:</b><br>
                    Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik<br>
                    Telp: 031 898 2999, Fax: Office 031-898 2065<br>
                    Marketing: 031-898 1777, Purchasing: 031-898 2066<br>
                    HRD: 031-898 3622<br>
                    www.temprina.com E-mail: temprina@temprina.com
                </div>
            </td>
        </tr>
    </table>
@endif
