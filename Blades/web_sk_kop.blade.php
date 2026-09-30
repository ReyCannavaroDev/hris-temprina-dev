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
    if (isset($t_mutasi)) {
        $compName = strtoupper(
            ($t_mutasi->m_sub_lama?->m_company?->name ?? '') . ' ' .
            ($t_mutasi->m_sub_lama?->m_comp?->name ?? '') . ' ' .
            ($t_mutasi->m_sub_baru?->m_comp?->name ?? '')
        );
        if (str_contains($compName, 'JEPE') || str_contains($compName, 'JP BOOKS') || str_contains($compName, 'JPMU')) {
            $isJPBooks = true;
        }
    }
@endphp

@if($isJPBooks)
    <!-- KOP JP BOOKS -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:15px; border-bottom:2px solid #000; padding-bottom:8px;">
        <tr>
            <td style="width:40%; vertical-align:middle;">
                @if($logoJpSrc)
                    <img src="{{ $logoJpSrc }}" style="max-height:55px; max-width:220px;" alt="JP Books">
                @else
                    <div style="font-size:22px; font-weight:bold; color:#005FBF;">JP BOOKS</div>
                    <div style="font-size:11px; font-weight:bold;">PT. JePe Press Media Utama</div>
                @endif
            </td>
            <td style="width:60%; text-align:right; vertical-align:middle; font-size:9px; line-height:1.3; color:#333;">
                <b style="font-size:11px; color:#000;">PT. JePe Press Media Utama</b><br>
                Office: Jl. Karah Agung 45 Surabaya<br>
                Telp. : 031-8289999 ext. 145/156/157/303/208 | Fax. 031 8281004<br>
                Website : www.jpbooks.co.id, email : jpbooks.surabaya@gmail.com
            </td>
        </tr>
    </table>
@else
    <!-- KOP TEMPRINA -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:12px; border-bottom:1px solid #777; padding-bottom:6px;">
        <tr>
            <td style="width:38%; vertical-align:top;">
                @if($logoTmgSrc)
                    <img src="{{ $logoTmgSrc }}" style="max-height:50px; max-width:190px;" alt="Temprina">
                @else
                    <div style="font-size:24px; font-weight:bold; color:#0055aa; letter-spacing:0.5px;">temprina</div>
                    <div style="font-size:10px; color:#555; margin-top:-2px;">Jawa Pos Group</div>
                @endif
            </td>
            <td style="width:62%; text-align:right; vertical-align:top;">
                @if($bannerIsoSrc)
                    <img src="{{ $bannerIsoSrc }}" style="max-height:36px; max-width:270px;" alt="ISO Certifications"><br>
                @endif
                <div style="font-size:7.5px; line-height:1.2; color:#333; margin-top:3px;">
                    <b>Head Office:</b> Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik Telp: 031 898 2999, Fax: Office 031-898 2065<br>
                    Marketing: 031-898 1777, Purchasing: 031-898 2066 HRD: 031-898 3622, Email: temprina@temprina.com
                </div>
            </td>
        </tr>
    </table>
@endif
