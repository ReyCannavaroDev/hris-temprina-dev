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

    if (!isset($footerPkwtSrc) || empty($footerPkwtSrc)) {
        $footerPkwtSrc = $resolveMediaSrc('footer_pkwt_template.jpg');
    }
@endphp

<!-- FOOTER PKWT TEMPLATE (STATIONERY / JPSTORE BANNER) -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:14px; padding:0;">
    <tr>
        <td style="width:100%; text-align:center; padding:0; line-height:0;">
            @if(!empty($footerPkwtSrc))
                <img src="{{ $footerPkwtSrc }}" style="width:100%; max-height:88px; display:block; margin:0 auto;" alt="Footer PKWT">
            @endif
        </td>
    </tr>
</table>
