@php
/**
 * MASTER FOOTER SURAT - HRIS TEMPRINA
 * 
 * Tersedia Function Helper:
 * 1. {!! footerTugas() !!}     -> Footer khusus Surat Tugas Pelatihan (Daftar Cabang Lengkap: Telp, Fax, Email)
 * 2. {!! footerStandar() !!}   -> Footer Temprina Standar (Surat Promosi, Mutasi, Demosi, dll.)
 * 3. {!! footerJpBooks() !!}   -> Footer khusus PT JePe Press Media Utama (JP Books)
 * 4. {!! renderFooter('tugas'|'standar'|'jpbooks'|'auto') !!}
 * 
 * Atau via Include:
 * - @include('projects.web_sk_footer', ['footer' => 'tugas'])
 * - @include('projects.web_sk_footer', ['footer' => 'standar'])
 * - @include('projects.web_sk_footer')  <-- otomatis sesuai surat
 */

if (!function_exists('resolveFooterMediaData')) {
    function resolveFooterMediaData() {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $mediaFooterTMG = \DB::table('m_media')->where('is_active', true)->where('kode', 'FOOTER-TEMPRINA')->first();
        $mediaFooterJP  = \DB::table('m_media')->where('is_active', true)->where('kode', 'FOOTER-JPBOOKS')->first();
        $mediaQR        = \DB::table('m_media')->where('is_active', true)->where('kode', 'QR-TEMPRINA')->first();

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

        $cached = [
            'footerTmg' => $mediaFooterTMG ? $resolveMedia($mediaFooterTMG->file_path) : '',
            'footerJp'  => $mediaFooterJP  ? $resolveMedia($mediaFooterJP->file_path)  : '',
            'qr'        => $mediaQR        ? $resolveMedia($mediaQR->file_path)        : '',
        ];
        return $cached;
    }
}

/**
 * 1. FOOTER KHUSUS SURAT TUGAS PELATIHAN
 * Menampilkan daftar alamat cabang lengkap (Telp, Fax, Email) sesuai dokumen fisik acuan
 * Pilihan alignment: 'justify' (rata kanan-kiri), 'center' (rata tengah), 'left' (rata kiri), 'right' (rata kanan)
 */
if (!function_exists('footerTugas')) {
    function footerTugas($align = 'justify') {
        $validAlign = in_array($align, ['left', 'center', 'right', 'justify']) ? $align : 'justify';
        return '
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border-top:0.5px solid #bbb;">
            <tr>
                <td align="' . $validAlign . '" style="text-align:' . $validAlign . '; padding-top:4px; font-size:6.8px; line-height:1.35; color:#333;">
                    <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
                    <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
                    <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
                    <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
                </td>
            </tr>
        </table>';
    }
}

/**
 * 2. FOOTER TEMPRINA STANDAR
 * Menampilkan gambar footer / daftar cabang ringkas + QR Code untuk SK Promosi, Mutasi, Demosi, dll.
 */
if (!function_exists('footerStandar')) {
    function footerStandar() {
        $m = resolveFooterMediaData();
        $footerTmgSrc = $m['footerTmg'];
        $qrSrc        = $m['qr'];

        return '
        <table style="width:100%; border-collapse:collapse; margin-top:20px; border-top:0.5px solid #ccc; padding-top:5px;">
            <tr>
                <td style="width:88%; vertical-align:middle;">
                    ' . ($footerTmgSrc ? '<img src="' . $footerTmgSrc . '" width="450" alt="Cabang Temprina">' : '<div style="font-size:7px; line-height:1.2; color:#555;">Bekasi: 021-8815222 | Cengkareng: 021-5553472 | Semarang: 024-7462136 | Solo: 0271-783001 | Malang: 0341-396700 | Nganjuk: 0358-773500 | Jember: 0331-320300 | Bali: 0361-421384</div>') . '
                </td>
                <td style="width:12%; text-align:right; vertical-align:middle;">
                    ' . ($qrSrc ? '<img src="' . $qrSrc . '" width="40" height="40" alt="QR Code">' : '') . '
                </td>
            </tr>
        </table>';
    }
}

/**
 * 3. FOOTER JP BOOKS
 * Khusus PT JePe Press Media Utama
 */
if (!function_exists('footerJpBooks')) {
    function footerJpBooks() {
        $m = resolveFooterMediaData();
        $footerJpSrc = $m['footerJp'];

        if ($footerJpSrc) {
            return '
            <div style="margin-top:25px; text-align:center;">
                <img src="' . $footerJpSrc . '" style="max-width:100%; max-height:42px;" alt="Footer JP Books">
            </div>';
        }

        return '
        <table style="width:100%; border-collapse:collapse; margin-top:20px; border-top:1.5px solid #ff8a00; padding-top:6px;">
            <tr>
                <td style="font-size:8px; color:#555; vertical-align:middle; line-height:1.4;">
                    <strong style="color:#111;">Head Office:</strong> Jl. Karah Agung 45 Surabaya 60232 &bull; Telp. (031) 8289999 (Hunting) &bull; Fax. (031) 8286999
                </td>
            </tr>
        </table>';
    }
}

/**
 * 4. DISPATCHER FUNCTION
 */
if (!function_exists('renderFooter')) {
    function renderFooter($type = 'auto', $context = []) {
        if ($type === 'tugas' || $type === 'surat_tugas') {
            $align = $context['align'] ?? 'justify';
            return footerTugas($align);
        }
        if ($type === 'jpbooks' || $type === 'jp') {
            return footerJpBooks();
        }
        if ($type === 'standar' || $type === 'promosi') {
            return footerStandar();
        }

        // Auto-detect JP Books dari context
        $isJP = false;
        if (!empty($context['isJpBooks'])) {
            $isJP = true;
        } elseif (!empty($context['t_mutasi'])) {
            $t = $context['t_mutasi'];
            $dest = strtoupper(
                ($t->m_sub_baru?->name ?? '') . ' ' .
                ($t->m_sbu_baru?->name ?? '') . ' ' .
                ($t->m_sub_baru?->kode ?? '') . ' ' .
                ($t->m_sbu_baru?->kode ?? '')
            );
            $origin = strtoupper(
                ($t->m_sub_lama?->name ?? '') . ' ' .
                ($t->m_sbu_lama?->name ?? '') . ' ' .
                ($t->m_sub_lama?->kode ?? '') . ' ' .
                ($t->m_sbu_lama?->kode ?? '') . ' ' .
                ($t->m_kary?->m_sbu?->name ?? '') . ' ' .
                ($t->m_kary?->m_sbu?->kode ?? '')
            );

            $checkIsJp = function($str) {
                return str_contains($str, 'JEPE') || 
                       str_contains($str, 'JP BOOKS') || 
                       str_contains($str, 'JPBOOKS') || 
                       str_contains($str, 'JPMU') || 
                       str_contains($str, 'SAHABAT EDUKASI') ||
                       preg_match('/\bJP\b/', $str);
            };

            if ($checkIsJp($dest) || (empty(trim($dest)) && $checkIsJp($origin))) {
                $isJP = true;
            }
        }

        if ($isJP) {
            return footerJpBooks();
        }

        return footerStandar();
    }
}

// Menentukan tipe footer dan alignment yang direquest
$requestedFooter = $footer ?? ($type ?? ($isSuratTugas ?? false ? 'tugas' : 'auto'));
$requestedAlign  = $align ?? ($footerAlign ?? 'justify');
$validAlign      = in_array($requestedAlign, ['left', 'center', 'right', 'justify']) ? $requestedAlign : 'justify';
@endphp

@if($requestedFooter === 'tugas' || $requestedFooter === 'surat_tugas')
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; border-top:0.5px solid #bbb;">
    <tr>
        <td align="{{ $validAlign }}" style="text-align:{{ $validAlign }}; padding-top:4px; font-size:6.8px; line-height:1.35; color:#333;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
            <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
            <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
        </td>
    </tr>
</table>
@else
{!! renderFooter($requestedFooter, [
    'isJpBooks' => $isJpBooks ?? false,
    't_mutasi'  => $t_mutasi ?? null,
    'align'     => $validAlign
]) !!}
@endif
