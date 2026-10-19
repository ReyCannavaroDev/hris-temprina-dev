@php
/**
 * MASTER KOP SURAT - HRIS TEMPRINA
 * 
 * Tersedia Function Helper:
 * 1. {!! kopTugas() !!}     -> Kop Surat Tugas (Logo Temprina + Banner ISO | Garis Batas | Head Office)
 * 2. {!! kopStandar() !!}   -> Kop Temprina Standar (Surat Promosi, Mutasi, Demosi, dll.)
 * 3. {!! kopJpBooks() !!}   -> Kop khusus PT JePe Press Media Utama (JP Books)
 * 4. {!! kopPkwt() !!}      -> Kop khusus PKWT / Freelance (Format Resmi PDF Acuan)
 * 5. {!! renderKop('tugas'|'standar'|'jpbooks'|'pkwt'|'auto') !!}
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
        $mediaPkwt = \DB::table('m_media')
            ->select('m_media.*')
            ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) {
                $q->whereIn(\DB::raw("UPPER(TRIM(COALESCE(m_media.kode, '')))"), [
                    'KOP-PKWT', 'HEADER-PKWT', 'KOP_PKWT', 'HEADER_PKWT',
                    'KOP-SURAT', 'KOP_SURAT', 'KOP SURAT', 'KOP', 'HEADER',
                    'LOGO-PKWT-HEADER', 'LOGO-HEADER-PKWT', 'LOGO-KOP-PKWT',
                    'LOGO-PKWT', 'LOGO-TMG', 'LOGO-TEMPRINA', 'LOGO_UTAMA'
                ])
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%KOP%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%HEADER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%ATAS%'")
                ->orWhereRaw("(UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%PKWT%' AND UPPER(TRIM(COALESCE(m_media.kode, ''))) NOT LIKE '%FOOTER%' AND UPPER(TRIM(COALESCE(m_media.kode, ''))) NOT LIKE '%BANNER%')")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%KOP%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%HEADER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%ATAS%'")
                ->orWhereRaw("(UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%PKWT%' AND UPPER(TRIM(COALESCE(m_media.nama, ''))) NOT LIKE '%FOOTER%' AND UPPER(TRIM(COALESCE(m_media.nama, ''))) NOT LIKE '%BANNER%')")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%KOP%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%HEADER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%ATAS%'");
            })
            ->orderByRaw("CASE 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) = 'KOP-PKWT' THEN 0 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) = 'HEADER-PKWT' THEN 1 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%PKWT%' AND UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%KOP%' THEN 2 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%PKWT%' THEN 3 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%PKWT%' THEN 4 
                WHEN UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%PKWT%' THEN 5
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) IN ('KOP-SURAT', 'KOP_SURAT', 'KOP SURAT') THEN 6 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%KOP%SURAT%' THEN 7 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%KOP%' THEN 8 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%KOP%' THEN 9 
                WHEN UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%KOP%' THEN 10
                ELSE 11 
            END")
            ->orderBy('m_media.id', 'desc')
            ->first();

        $resolveMediaPath = function($rawPath) {
            if (empty($rawPath)) return '';
            $rawPath = trim($rawPath);
            if (str_starts_with($rawPath, 'data:image')) {
                return $rawPath;
            }

            $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
            $clean = ltrim($clean, '/');
            $basename = basename($clean);

            $pathCandidates = array_unique(array_filter([
                $clean,
                $basename,
                str_contains($clean, ':::') ? explode(':::', $clean)[0] : null,
                str_contains($basename, ':::') ? explode(':::', $basename)[0] : null,
                str_contains($clean, ':::') ? (explode(':::', $clean)[1] ?? null) : null,
                str_contains($basename, ':::') ? (explode(':::', $basename)[1] ?? null) : null,
            ]));

            $subPaths = [];
            foreach ($pathCandidates as $cand) {
                $cand = trim($cand);
                if (empty($cand)) continue;
                $candBase = basename($cand);
                $subPaths[] = $cand;
                $subPaths[] = $candBase;
                $subPaths[] = 'uploads/m_media/' . $candBase;
                $subPaths[] = 'uploads/' . $candBase;
                $subPaths[] = 'storage/uploads/m_media/' . $candBase;
                $subPaths[] = 'storage/' . $candBase;
                $subPaths[] = 'images/' . $candBase;
                $subPaths[] = 'media/' . $candBase;
                $subPaths[] = 'uploads/m_media/' . $cand;
                $subPaths[] = 'storage/' . $cand;
            }
            $subPaths = array_unique(array_filter($subPaths));

            foreach ($subPaths as $sub) {
                $pathsToCheck = [];
                if (function_exists('public_path')) {
                    $pathsToCheck[] = public_path($sub);
                    $pathsToCheck[] = public_path('storage/' . $sub);
                }
                if (function_exists('storage_path')) {
                    $pathsToCheck[] = storage_path('app/public/' . $sub);
                    $pathsToCheck[] = storage_path('app/' . $sub);
                    $pathsToCheck[] = storage_path($sub);
                }
                if (function_exists('base_path')) {
                    $pathsToCheck[] = base_path('storage/app/public/' . $sub);
                    $pathsToCheck[] = base_path('public/' . $sub);
                    $pathsToCheck[] = base_path('public/storage/' . $sub);
                    $pathsToCheck[] = base_path($sub);
                }
                $pathsToCheck[] = '/opt/www/hris/storage/app/public/' . $sub;
                $pathsToCheck[] = '/opt/www/hris/public/' . $sub;
                $pathsToCheck[] = '/var/www/html/storage/app/public/' . $sub;
                $pathsToCheck[] = '/var/www/html/public/' . $sub;
                $pathsToCheck[] = '/var/www/hris/storage/app/public/' . $sub;
                $pathsToCheck[] = '/var/www/hris/public/' . $sub;

                foreach ($pathsToCheck as $full) {
                    if ($full && file_exists($full) && is_file($full) && filesize($full) > 50) {
                        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                        $mime = match($ext) {
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp',
                            'svg' => 'image/svg+xml',
                            default => 'image/jpeg'
                        };
                        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
                    }
                }
            }

            if (function_exists('file_get_contents')) {
                $streamCtx = stream_context_create([
                    'http' => ['timeout' => 2, 'ignore_errors' => true],
                    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
                ]);
                $urlsToTry = [];
                if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                    $urlsToTry[] = $rawPath;
                }
                foreach ($pathCandidates as $cand) {
                    $candBase = basename($cand);
                    $urlsToTry[] = 'https://hris.temprina.com/storage/uploads/m_media/' . $candBase;
                    $urlsToTry[] = 'https://hris.temprina.com/storage/uploads/m_media/' . rawurlencode($candBase);
                    $urlsToTry[] = 'https://hris.temprina.com/uploads/m_media/' . $candBase;
                    $urlsToTry[] = 'https://hris.temprina.com/uploads/m_media/' . rawurlencode($candBase);
                    $urlsToTry[] = 'https://hris.temprina.com/storage/' . $candBase;
                    $urlsToTry[] = 'https://hris.temprina.com/' . ltrim($cand, '/');
                    $urlsToTry[] = 'http://127.0.0.1/storage/uploads/m_media/' . $candBase;
                    $urlsToTry[] = 'http://localhost/storage/uploads/m_media/' . $candBase;
                }

                foreach (array_unique(array_filter($urlsToTry)) as $u) {
                    try {
                        $remoteData = @file_get_contents($u, false, $streamCtx);
                        if ($remoteData && strlen($remoteData) > 50) {
                            $mime = 'image/jpeg';
                            if (str_starts_with($remoteData, "\x89PNG")) {
                                $mime = 'image/png';
                            } elseif (str_starts_with($remoteData, "GIF")) {
                                $mime = 'image/gif';
                            }
                            return 'data:' . $mime . ';base64,' . base64_encode($remoteData);
                        }
                    } catch (\Throwable $e) {}
                }
            }

            if (str_starts_with($rawPath, 'data:image') || str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                return $rawPath;
            }
            return '';
        };

        $logoTmgSrc     = $mediaTMG      ? $resolveMediaPath($mediaTMG->file_path)      : '';
        $bannerIsoSrc   = $mediaISO      ? $resolveMediaPath($mediaISO->file_path)      : '';
        $logoIsoFooter  = $mediaIsoFooter ? $resolveMediaPath($mediaIsoFooter->file_path) : '';
        if (empty($logoIsoFooter)) {
            $logoIsoFooter = $bannerIsoSrc;
        }
        $logoJpSrc      = $mediaJP       ? $resolveMediaPath($mediaJP->file_path)       : '';
        $headerPkwtSrc  = $mediaPkwt     ? $resolveMediaPath($mediaPkwt->file_path)     : '';

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
            'headerPkwt'    => $headerPkwtSrc,
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
                <td width="26%" valign="top" style="vertical-align:top; padding-right:16px; padding-top:20px;">
                    ' . ($logoTmg ? '<img src="' . $logoTmg . '" height="48" alt="Temprina">' : '<b style="font-size:18px; color:#0055aa;">temprina</b>') . '
                </td>

                <!-- GRUP 2: BANNER ISO -->
                <td width="36%" valign="top" style="vertical-align:top; padding-right:16px; padding-top:20px;">
                    ' . ($bannerIso ? '<img src="' . $bannerIso . '" height="40" alt="Banner ISO">' : '') . '
                </td>

                <!-- GRUP 3: GARIS ABU-ABU + HEAD OFFICE -->
                <td width="38%" valign="top" style="vertical-align:top; padding-top:0px;">
                    <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
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
 * 4. KOP PKWT / FREELANCE (FORMAT RESMI PDF ACUAN)
 */
if (!function_exists('kopPkwt')) {
    function kopPkwt() {
        if (function_exists('view')) {
            if (view()->exists('projects.web_sk_kop_pkwt')) {
                return view('projects.web_sk_kop_pkwt')->render();
            }
            if (view()->exists('web_sk_kop_pkwt')) {
                return view('web_sk_kop_pkwt')->render();
            }
        }
        $m = resolveKopMediaData();
        $src = !empty($m['headerPkwt']) ? $m['headerPkwt'] : '';
        if (!$src) {
            $b64Path = function_exists('base_path') ? base_path('media/real_header_b64.txt') : '';
            if ($b64Path && file_exists($b64Path)) {
                $src = 'data:image/jpeg;base64,' . trim(file_get_contents($b64Path));
            }
        }
        if (!$src) {
            $src = function_exists('kopPkwtDefaultB64') ? 'data:image/jpeg;base64,' . kopPkwtDefaultB64() : kopStandar();
            if (!str_starts_with($src, 'data:image')) {
                return $src;
            }
        }
        return '
        <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:12px; padding:0;">
          <tr>
            <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
              <img src="' . $src . '" width="700" style="width:700px; max-width:100%;" alt="Kop Surat PKWT">
            </td>
          </tr>
        </table>';
    }
}

/**
 * 5. DISPATCHER FUNCTION
 */
if (!function_exists('renderKop')) {
    function renderKop($type = 'auto', $context = []) {
        if ($type === 'tugas' || $type === 'surat_tugas') {
            return kopTugas();
        }
        if ($type === 'pkwt' || $type === 'freelance') {
            return kopPkwt();
        }
        if ($type === 'jpbooks' || $type === 'jp') {
            return kopJpBooks();
        }
        if ($type === 'standar' || $type === 'promosi') {
            return kopStandar();
        }

        // Auto-detect PKWT / Freelance dari context
        if (!empty($context['isPkwt']) || !empty($context['isPKWT'])) {
            return kopPkwt();
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

// Deteksi apakah dokumen adalah PKWT / Freelance
$isPKWT = false;
if (!empty($isPkwt) || !empty($isPKWT)) {
    $isPKWT = true;
} elseif (function_exists('request') && request()) {
    $uri = request()->getRequestUri() ?? '';
    $path = request()->path() ?? '';
    $fullUrl = request()->fullUrl() ?? '';
    $targetStr = strtolower($uri . ' ' . $path . ' ' . $fullUrl);
    if (str_contains($targetStr, 'sk_pkwt') || str_contains($targetStr, 'pkwt') || str_contains($targetStr, 'freelance')) {
        $isPKWT = true;
    }
}
if (!$isPKWT && isset($t_mutasi)) {
    $nomorDoc = strtoupper(($t_mutasi->no_dokumen ?? '') . ' ' . ($t_mutasi->nomor ?? ''));
    $jenisMut = strtoupper(($t_mutasi->m_jenis_mutasi?->name ?? '') . ' ' . ($t_mutasi->m_jenis_mutasi?->kode ?? ''));
    if (str_contains($nomorDoc, 'PKWT') || str_contains($nomorDoc, 'HLD') || str_contains($nomorDoc, '/HL/') || str_contains($nomorDoc, 'HL') ||
        str_contains($jenisMut, 'PKWT') || str_contains($jenisMut, 'HARIAN LEPAS') || str_contains($jenisMut, 'HL')) {
        $isPKWT = true;
    }
}

// Deteksi JP Books
$isJP = false;
if (isset($isJpBooks)) {
    $isJP = (bool)$isJpBooks;
} elseif (isset($t_mutasi)) {
    $dest = strtoupper(($t_mutasi->m_sub_baru?->name ?? '') . ' ' . ($t_mutasi->m_sbu_baru?->name ?? ''));
    $origin = strtoupper(($t_mutasi->m_sub_lama?->name ?? '') . ' ' . ($t_mutasi->m_sbu_lama?->name ?? ''));
    if (str_contains($dest, 'JP') || str_contains($origin, 'JP') || str_contains($dest, 'JEPE') || str_contains($origin, 'JEPE')) {
        $isJP = true;
    }
}

// Menentukan tipe kop yang direquest
$requestedKop = $kop ?? ($type ?? ($isSuratTugas ?? false ? 'tugas' : ($isPKWT ? 'pkwt' : ($isJP ? 'jpbooks' : 'auto'))));
@endphp

@if($isPKWT || $requestedKop === 'pkwt' || $requestedKop === 'freelance')
    @if(view()->exists('projects.web_sk_kop_pkwt'))
        @include('projects.web_sk_kop_pkwt')
    @else
        {!! kopPkwt() !!}
    @endif
@elseif($requestedKop === 'tugas' || $requestedKop === 'surat_tugas')
    {!! kopTugas() !!}
@elseif($isJP || $requestedKop === 'jpbooks' || $requestedKop === 'jp')
    {!! kopJpBooks() !!}
@else
    {!! kopStandar() !!}
@endif
