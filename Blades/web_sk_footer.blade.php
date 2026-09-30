@php
    $mediaFooterTMG = \DB::table('m_media')->where('is_active', true)->where('kode', 'FOOTER-TEMPRINA')->first();
    $mediaFooterJP = \DB::table('m_media')->where('is_active', true)->where('kode', 'FOOTER-JPBOOKS')->first();
    $mediaQR = \DB::table('m_media')->where('is_active', true)->where('kode', 'QR-TEMPRINA')->first();

    $resolveMedia = function($rawPath) {
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

    $footerTmgSrc = $mediaFooterTMG ? $resolveMedia($mediaFooterTMG->file_path) : '';
    $footerJpSrc = $mediaFooterJP ? $resolveMedia($mediaFooterJP->file_path) : '';
    $qrSrc = $mediaQR ? $resolveMedia($mediaQR->file_path) : '';

    $isJP = false;
    if (isset($t_mutasi)) {
        $cName = strtoupper(
            ($t_mutasi->m_sub_lama?->m_company?->name ?? '') . ' ' .
            ($t_mutasi->m_sub_lama?->m_comp?->name ?? '')
        );
        if (str_contains($cName, 'JEPE') || str_contains($cName, 'JP BOOKS') || str_contains($cName, 'JPMU')) {
            $isJP = true;
        }
    }
@endphp

@if($isJP)
    @if($footerJpSrc)
    <div style="margin-top:25px; text-align:center;">
        <img src="{{ $footerJpSrc }}" style="max-width:100%; max-height:42px;" alt="Footer JP Books">
    </div>
    @endif
@else
    <table style="width:100%; border-collapse:collapse; margin-top:20px; border-top:0.5px solid #ccc; padding-top:5px;">
        <tr>
            <td style="width:88%; vertical-align:middle;">
                @if($footerTmgSrc)
                    <img src="{{ $footerTmgSrc }}" width="450" alt="Cabang Temprina">
                @else
                    <div style="font-size:7px; line-height:1.2; color:#555;">
                        Bekasi: 021-8815222 | Cengkareng: 021-5553472 | Semarang: 024-7462136 | Solo: 0271-783001 | Malang: 0341-396700 | Nganjuk: 0358-773500 | Jember: 0331-320300 | Bali: 0361-421384
                    </div>
                @endif
            </td>
            <td style="width:12%; text-align:right; vertical-align:middle;">
                @if($qrSrc)
                    <img src="{{ $qrSrc }}" width="40" height="40" alt="QR Code">
                @endif
            </td>
        </tr>
    </table>
@endif
