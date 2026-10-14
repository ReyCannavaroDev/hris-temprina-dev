@php
    $resolveMediaSrc = function($filename, $fallbackB64 = '') {
        $candidates = [
            $filename,
            'media/' . $filename,
            'uploads/' . $filename,
            'storage/' . $filename,
            'images/' . $filename
        ];
        foreach ($candidates as $cand) {
            $paths = [
                function_exists('public_path') ? public_path($cand) : null,
                function_exists('base_path') ? base_path('public/' . $cand) : null,
                function_exists('base_path') ? base_path($cand) : null,
                '/opt/www/hris/public/' . $cand,
                '/opt/www/hris/' . $cand,
                'c:/Users/IP3_R3ID/Desktop/PKL/hris-temprina-dev/' . $cand
            ];
            foreach ($paths as $full) {
                if ($full && file_exists($full) && is_file($full)) {
                    $type = pathinfo($full, PATHINFO_EXTENSION);
                    return 'data:image/' . ($type === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($full));
                }
            }
        }
        return $fallbackB64;
    };

    if (!isset($headerPkwtSrc) || empty($headerPkwtSrc)) {
        $headerPkwtSrc = $resolveMediaSrc('header_pkwt_temprina.jpg');
    }
@endphp

<!-- KOP SURAT PKWT TEMPRINA (FORMAT PDF ACUAN) -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:10px; padding:0;">
    <tr>
        <td style="width:100%; text-align:center; padding:0; line-height:0;">
            @if(!empty($headerPkwtSrc))
                <img src="{{ $headerPkwtSrc }}" style="width:100%; max-height:88px; display:block; margin:0 auto;" alt="Kop Surat PKWT">
            @else
                <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif;">
                    <tr>
                        <td style="width:30%; vertical-align:middle; text-align:left;">
                            <div style="font-size:20pt; font-weight:bold; color:#0055aa; letter-spacing:0.5px;">temprina</div>
                            <div style="font-size:9pt; color:#444; margin-top:-3px;">Jawa Pos Group</div>
                        </td>
                        <td style="width:2%; border-left:1px solid #777;">&nbsp;</td>
                        <td style="width:68%; vertical-align:middle; text-align:left; font-size:8pt; line-height:1.25; color:#222;">
                            <b>PT TEMPRINA MEDIA GRAFIKA</b><br>
                            Jl. Raya Sumengko KM 30-31 Wringinanom, Gresik<br>
                            Telp: (031) 8986222, Fax: (031) 8986333<br>
                            Email: temprina@temprina.com &bull; www.temprina.com
                        </td>
                    </tr>
                </table>
            @endif
        </td>
    </tr>
</table>
