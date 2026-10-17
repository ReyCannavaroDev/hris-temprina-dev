@php
/**
 * MASTER KOP SURAT - HRIS TEMPRINA
 * 
 * Tersedia Function Helper:
 * 1. {!! kopTugas() !!}     -> Kop Surat Tugas (Logo Temprina + Banner ISO | Garis Batas | Head Office)
 * 2. {!! kopStandar() !!}   -> Kop Temprina Standar (Surat Promosi, Mutasi, Demosi, dll.)
 * 3. {!! kopJpBooks() !!}   -> Kop khusus PT JePe Press Media Utama (JP Books)
 * 4. {!! renderKop('tugas'|'standar'|'jpbooks'|'auto') !!}
 * 
 * Atau via Include:
 * - @include('projects.web_sk_kop', ['kop' => 'tugas'])
 * - @include('projects.web_sk_kop', ['kop' => 'standar'])
 * - @include('projects.web_sk_kop')  <-- otomatis sesuai surat
 */

if (!function_exists('resolveKopMediaData')) {
    function resolveKopMediaData() {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $mediaTMG       = \DB::table('m_media')->where('is_active', true)->where('kode', 'LOGO-TMG')->first();
        $mediaISO       = \DB::table('m_media')->where('is_active', true)->where('kode', 'BANNER-ISO-TEMPRINA')->first();
        $mediaIsoFooter = \DB::table('m_media')->where('is_active', true)->where('kode', 'LOGO_ISO_FOOTER')->first();
        $mediaJP        = \DB::table('m_media')->where('is_active', true)->whereIn('kode', ['LOGO-JPBOOKS', 'HEADER-JPBOOKS'])->orderBy('id', 'desc')->first();

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

        $logoTmgSrc    = $mediaTMG      ? $resolveMediaPath($mediaTMG->file_path)      : '';
        $bannerIsoSrc  = $mediaISO      ? $resolveMediaPath($mediaISO->file_path)      : '';
        $logoIsoFooter = $mediaIsoFooter ? $resolveMediaPath($mediaIsoFooter->file_path) : '';
        if (empty($logoIsoFooter)) {
            $logoIsoFooter = $bannerIsoSrc;
        }
        $logoJpSrc     = $mediaJP       ? $resolveMediaPath($mediaJP->file_path)       : '';

        // Fallback local media files
        if (empty($bannerIsoSrc)) {
            $localBanner = function_exists('base_path') ? base_path('media/banner_iso_temprina.jpg') : '';
            if ($localBanner && file_exists($localBanner)) {
                $bannerIsoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($localBanner));
            }
        }
        if (empty($logoTmgSrc)) {
            $localLogo = function_exists('base_path') ? base_path('media/logo_temprina_pkwt.png') : '';
            if ($localLogo && file_exists($localLogo)) {
                $logoTmgSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($localLogo));
            }
        }

        $cached = [
            'logoTmg'       => $logoTmgSrc,
            'bannerIso'     => $bannerIsoSrc,
            'logoIsoFooter' => $logoIsoFooter,
            'logoJp'        => $logoJpSrc,
        ];
        return $cached;
    }
}

/**
 * 1. KOP SURAT TUGAS PELATIHAN
 * Susunan: Logo Temprina + Banner ISO | Garis Batas Vertikal | Head Office
 */
if (!function_exists('kopTugas')) {
    function kopTugas() {
        $m = resolveKopMediaData();
        $logoTmg   = $m['logoTmg'];
        $bannerIso = $m['bannerIso'];

        return '
        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;">
            <tr>
                <!-- GRUP 1: LOGO TEMPRINA -->
                <!-- Ubah padding-top jika ingin mengatur posisi naik/turun Logo Temprina -->
                <td width="26%" valign="top" style="vertical-align:top; padding-right:16px; padding-top:20px;">
                    ' . ($logoTmg ? '<img src="' . $logoTmg . '" height="48" alt="Temprina">' : '<b style="font-size:18px; color:#0055aa;">temprina</b>') . '
                </td>

                <!-- GRUP 2: BANNER ISO -->
                <!-- Ubah padding-top jika ingin mengatur posisi naik/turun Banner ISO -->
                <td width="36%" valign="top" style="vertical-align:top; padding-right:16px; padding-top:20px;">
                    ' . ($bannerIso ? '<img src="' . $bannerIso . '" height="40" alt="Banner ISO">' : '') . '
                </td>

                <!-- GRUP 3: GARIS ABU-ABU + HEAD OFFICE -->
                <!-- Ubah padding-top di <td> luar ini jika ingin menaikkan/menurunkan posisi blok Head Office -->
                <td width="38%" valign="top" style="vertical-align:top; padding-top:0px;">
                    <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <!-- GARIS ABU-ABU MENEMPEL PAS DI KIRI TEKS (Tingginya 100% pas dengan teks Head Office) -->
                            <!-- Ubah padding-left di bawah jika ingin garis lebih menempel/merapat (4px = sangat rapat) -->
                            <td style="border-left:2px solid #888; padding-left:4px; font-size:6.8px; color:#111; line-height:1.25; font-family:Arial, Helvetica, sans-serif;">
                                <span style="font-size:7.5px; font-weight:bold; color:#000;">Head Office :</span><br>
                                Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik<br>
                                Telp: 031 898 2999, Fax : Office 031-898 2065<br>
                                Marketing : 031-898 1777, Purchasing : 031-898 2066<br>
                                HRD : 031-898 3622<br>
                                www.temprina.com <i>E-mail : temprina@temprina.com</i>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';
    }
}

/**
 * 2. KOP TEMPRINA STANDAR
 * Susunan: Logo Temprina (Kiri) + Logo ISO & Alamat (Kanan Rata Kanan)
 * Digunakan untuk: Surat Promosi, Mutasi, Demosi, dll.
 */
if (!function_exists('kopStandar')) {
    function kopStandar() {
        $m = resolveKopMediaData();
        $logoTmg       = $m['logoTmg'];
        $logoIsoFooter = $m['logoIsoFooter'];

        return '
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <tr>
                <td width="35%" style="vertical-align:middle; padding-right:10px;">
                    ' . ($logoTmg ? '<img src="' . $logoTmg . '" width="140" alt="Temprina">' : '<b style="font-size:20px; color:#0055aa;">temprina</b>') . '
                </td>
                <td width="65%" style="vertical-align:top; padding-left:10px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        ' . ($logoIsoFooter ? '<tr><td align="right" style="padding-bottom:3px;"><img src="' . $logoIsoFooter . '" height="30" alt="ISO"></td></tr>' : '') . '
                        <tr>
                            <td align="right" style="font-size:8px; font-weight:bold; color:#000; line-height:1.5;">
                                Head Office :
                            </td>
                        </tr>
                        <tr>
                            <td align="right" style="font-size:7px; color:#222; line-height:1.5;">
                                Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik Telp : 0318982999 Far Office 031-8982065
                            </td>
                        </tr>
                        <tr>
                            <td align="right" style="font-size:7px; color:#222; line-height:1.5;">
                                Marketing : 031-0981777, Purchasing : 031-8982066 HRD : 031-8982622, Email : temprina@temprina.com
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';
    }
}

/**
 * 3. KOP JP BOOKS
 * Khusus PT JePe Press Media Utama
 */
if (!function_exists('kopJpBooks')) {
    function kopJpBooks() {
        $m = resolveKopMediaData();
        $logoJp = $m['logoJp'];

        return '
        <table style="width:100%; border-collapse:collapse; margin-bottom:15px; padding-bottom:4px;">
            <tr>
                <td style="width:35%; vertical-align:middle;">
                    ' . ($logoJp ? '<img src="' . $logoJp . '" style="max-height:52px; max-width:210px;" alt="JP Books">' : '<div style="display:inline-block; background-color:#ff8a00; padding:6px 14px; border-radius:4px; font-size:22px; font-weight:bold; color:#0055aa; letter-spacing:1px; border:2px solid #0055aa;">JP BOOKS</div>') . '
                </td>
                <td style="width:65%; text-align:left; vertical-align:middle;">
                    <div style="font-size:22px; font-weight:bold; color:#0055aa; font-family:Arial, Helvetica, sans-serif; letter-spacing:0.5px;">
                        PT. JePe Press Media Utama
                    </div>
                </td>
            </tr>
        </table>';
    }
}

/**
 * 4. DISPATCHER FUNCTION
 */
if (!function_exists('renderKop')) {
    function renderKop($type = 'auto', $context = []) {
        if ($type === 'tugas' || $type === 'surat_tugas') {
            return kopTugas();
        }
        if ($type === 'jpbooks' || $type === 'jp') {
            return kopJpBooks();
        }
        if ($type === 'standar' || $type === 'promosi') {
            return kopStandar();
        }

        // Auto-detect JP Books dari context
        $isJP = false;
        if (!empty($context['isJpBooks'])) {
            $isJP = true;
        } elseif (!empty($context['t_mutasi'])) {
            $t = $context['t_mutasi'];
            $dest = strtoupper(($t->m_sub_baru?->name ?? '') . ' ' . ($t->m_sbu_baru?->name ?? ''));
            $origin = strtoupper(($t->m_sub_lama?->name ?? '') . ' ' . ($t->m_sbu_lama?->name ?? ''));
            if (str_contains($dest, 'JP') || str_contains($origin, 'JP') || str_contains($dest, 'JEPE') || str_contains($origin, 'JEPE')) {
                $isJP = true;
            }
        }
        if ($isJP) {
            return kopJpBooks();
        }

        return kopStandar();
    }
}

// Menentukan tipe kop yang direquest
$requestedKop = $kop ?? ($type ?? ($isSuratTugas ?? false ? 'tugas' : 'auto'));
@endphp
{!! renderKop($requestedKop, [
    'isJpBooks' => $isJpBooks ?? false,
    't_mutasi'  => $t_mutasi ?? null
]) !!}

