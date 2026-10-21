@php
/**
 * MASTER FOOTER SURAT - HRIS TEMPRINA
 * 
 * Tersedia Function Helper:
 * 1. {!! footerTugas($align) !!} -> Footer khusus Surat Tugas Pelatihan (Daftar Cabang Lengkap: Telp, Fax, Email)
 * 2. {!! footerStandar() !!}     -> Footer Temprina Standar (Surat Promosi, Mutasi, Demosi, dll.)
 * 3. {!! footerJpBooks() !!}     -> Footer khusus PT JePe Press Media Utama (JP Books)
 * 4. {!! footerPkwt() !!}        -> Footer khusus PKWT / Freelance (Format Resmi PDF Acuan)
 * 5. {!! renderFooter('tugas'|'standar'|'jpbooks'|'pkwt'|'auto') !!}
 * 
 * Atau via Include:
 * - @include('projects.web_sk_footer', ['footer' => 'tugas', 'align' => 'center'])
 * - @include('projects.web_sk_footer', ['footer' => 'standar'])
 * - @include('projects.web_sk_footer')  <-- otomatis sesuai surat
 */

if (!function_exists('resolveFooterMediaData')) {
    function resolveFooterMediaData() {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $mediaFooterTMG  = \DB::table('m_media')->where('is_active', true)->whereIn('kode', ['FOOTER-CABANG-TEMPRINA', 'FOOTER-TEMPRINA'])->first();
        $mediaFooterJP   = \DB::table('m_media')->where('is_active', true)->whereIn('kode', ['FOOTER-JPBOOKS', 'BANNER-JPBOOKS'])->orderBy('id', 'desc')->first();
        $mediaQR         = \DB::table('m_media')->where('is_active', true)->where('kode', 'QR-TEMPRINA')->first();
        $mediaFooterPkwt = \DB::table('m_media')
            ->select('m_media.*')
            ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) {
                $q->whereIn(\DB::raw("UPPER(TRIM(COALESCE(m_media.kode, '')))"), [
                    'FOOTER-PKWT', 'BANNER-PKWT', 'FOOTER_PKWT', 'BANNER_PKWT',
                    'LOGO-PKWT-FOOTER', 'LOGO-FOOTER-PKWT', 'FOOTER', 'BANNER',
                    'FOOTER-TEMPRINA', 'FOOTER-CABANG', 'FOOTER-CABANG-TEMPRINA', 'FOOTER-SURAT'
                ])
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%FOOTER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%BANNER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%BAWAH%'")
                ->orWhereRaw("(UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%PKWT%' AND UPPER(TRIM(COALESCE(m_media.kode, ''))) NOT LIKE '%KOP%' AND UPPER(TRIM(COALESCE(m_media.kode, ''))) NOT LIKE '%HEADER%')")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%FOOTER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%BANNER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%BAWAH%'")
                ->orWhereRaw("(UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%PKWT%' AND UPPER(TRIM(COALESCE(m_media.nama, ''))) NOT LIKE '%KOP%' AND UPPER(TRIM(COALESCE(m_media.nama, ''))) NOT LIKE '%HEADER%')")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%FOOTER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%BANNER%'")
                ->orWhereRaw("UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%BAWAH%'");
            })
            ->orderByRaw("CASE 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) = 'FOOTER-PKWT' THEN 0 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) = 'BANNER-PKWT' THEN 1 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%PKWT%' THEN 2 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%PKWT%' THEN 3 
                WHEN UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%PKWT%' THEN 4
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) IN ('FOOTER-TEMPRINA', 'FOOTER-CABANG', 'FOOTER-CABANG-TEMPRINA') THEN 5 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%CABANG%' THEN 6 
                WHEN UPPER(TRIM(COALESCE(m_media.kode, ''))) LIKE '%FOOTER%' THEN 7 
                WHEN UPPER(TRIM(COALESCE(m_media.nama, ''))) LIKE '%FOOTER%' THEN 8 
                WHEN UPPER(TRIM(COALESCE(m_general.value, ''))) LIKE '%FOOTER%' THEN 9
                ELSE 10 
            END")
            ->orderBy('m_media.id', 'desc')
            ->first();

        $resolveMedia = function($rawPath) {
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

        $cached = [
            'footerTmg'  => $mediaFooterTMG  ? $resolveMedia($mediaFooterTMG->file_path)  : '',
            'footerJp'   => $mediaFooterJP   ? $resolveMedia($mediaFooterJP->file_path)   : '',
            'qr'         => $mediaQR         ? $resolveMedia($mediaQR->file_path)         : '',
            'footerPkwt' => $mediaFooterPkwt ? $resolveMedia($mediaFooterPkwt->file_path) : '',
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
        $qrSrc = $m['qr'] ?? '';

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

        return '
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
                    ' . ($qrSrc ? '<img src="' . $qrSrc . '" width="42" height="42" alt="QR Code">' : '') . '
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
 * 4. FOOTER PKWT / FREELANCE (FORMAT RESMI PDF ACUAN)
 */
if (!function_exists('footerPkwt')) {
    function footerPkwt() {
        if (function_exists('view')) {
            if (view()->exists('projects.web_sk_footer_pkwt')) {
                return view('projects.web_sk_footer_pkwt')->render();
            }
            if (view()->exists('web_sk_footer_pkwt')) {
                return view('web_sk_footer_pkwt')->render();
            }
        }
        $m = resolveFooterMediaData();
        $src = !empty($m['footerPkwt']) ? $m['footerPkwt'] : '';
        if (!$src) {
            $b64Path = function_exists('base_path') ? base_path('media/real_footer_b64.txt') : '';
            if ($b64Path && file_exists($b64Path)) {
                $src = 'data:image/jpeg;base64,' . trim(file_get_contents($b64Path));
            }
        }
        if (!$src) {
            return footerStandar();
        }
        return '
        <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:14px; padding:0;">
          <tr>
            <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
              <img src="' . $src . '" width="700" style="width:700px; max-width:100%;" alt="Footer PKWT">
            </td>
          </tr>
        </table>';
    }
}

/**
 * 5. DISPATCHER FUNCTION
 */
if (!function_exists('renderFooter')) {
    function renderFooter($type = 'auto', $context = []) {
        $align = $context['align'] ?? 'justify';

        if ($type === 'tugas' || $type === 'surat_tugas') {
            return footerTugas($align);
        }
        if ($type === 'pkwt' || $type === 'freelance') {
            return footerPkwt();
        }
        if ($type === 'jpbooks' || $type === 'jp') {
            return footerJpBooks();
        }
        if ($type === 'standar' || $type === 'promosi') {
            return footerStandar();
        }

        // Auto-detect PKWT / Freelance dari context
        if (!empty($context['isPkwt']) || !empty($context['isPKWT'])) {
            return footerPkwt();
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
            if (str_contains($dest, 'JP') || str_contains($origin, 'JP') || str_contains($dest, 'JEPE') || str_contains($origin, 'JEPE')) {
                $isJP = true;
            }
        }
        if ($isJP) {
            return footerJpBooks();
        }

        return footerStandar();
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
$isSkKerja = false;
if (function_exists('request') && request()) {
    $uri = request()->getRequestUri() ?? '';
    $path = request()->path() ?? '';
    $fullUrl = request()->fullUrl() ?? '';
    $targetStr = strtolower($uri . ' ' . $path . ' ' . $fullUrl);
    if (str_contains($targetStr, 'sk_kerja') || str_contains($targetStr, 'keterangan_kerja')) {
        $isSkKerja = true;
    }
}

if (!$isPKWT && !$isSkKerja && isset($t_mutasi) && ($footer ?? '') !== 'standar') {
    $nomorDoc = strtoupper(($t_mutasi->no_dokumen ?? '') . ' ' . ($t_mutasi->nomor ?? ''));
    $jenisMut = strtoupper(($t_mutasi->m_jenis_mutasi?->name ?? '') . ' ' . ($t_mutasi->m_jenis_mutasi?->kode ?? ''));
    if (str_contains($nomorDoc, 'PKWT') || str_contains($nomorDoc, 'HLD') || str_contains($nomorDoc, '/HL/') || str_contains($nomorDoc, 'HL') ||
        str_contains($jenisMut, 'PKWT') || str_contains($jenisMut, 'HARIAN LEPAS') || str_contains($jenisMut, 'HL')) {
        $isPKWT = true;
    }
}

if ($isSkKerja || ($footer ?? '') === 'standar') {
    $isPKWT = false;
}

// Deteksi JP Books
$isJP = false;
if (isset($isJpBooks)) {
    $isJP = (bool)$isJpBooks;
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
    if (str_contains($dest, 'JP') || str_contains($origin, 'JP') || str_contains($dest, 'JEPE') || str_contains($origin, 'JEPE')) {
        $isJP = true;
    }
}

// Menentukan tipe footer yang direquest
$requestedFooter = $footer ?? ($type ?? ($isSuratTugas ?? false ? 'tugas' : ($isPKWT ? 'pkwt' : ($isJP ? 'jpbooks' : 'standar'))));
$requestedAlign  = $align ?? ($footerAlign ?? 'justify');
$validAlign      = in_array($requestedAlign, ['left', 'center', 'right', 'justify']) ? $requestedAlign : 'justify';
@endphp

@if(($requestedFooter === 'pkwt' || $requestedFooter === 'freelance' || $isPKWT) && !$isSkKerja && ($footer ?? '') !== 'standar')
    @if(view()->exists('projects.web_sk_footer_pkwt'))
        @include('projects.web_sk_footer_pkwt')
    @else
        {!! footerPkwt() !!}
    @endif
@elseif($requestedFooter === 'tugas' || $requestedFooter === 'surat_tugas')
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; border-top:0.5px solid #bbb;">
    <tr>
        <td align="{{ $validAlign }}" style="text-align:{{ $validAlign }}; padding-top:4px; font-size:6.8px; line-height:1.35; color:#333;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
            <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
            <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, Email : Nganjuk@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, Email : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, Email : Bali@temprina.com
        </td>
    </tr>
</table>
@elseif($isJP || $requestedFooter === 'jpbooks' || $requestedFooter === 'jp')
    @php
        $m = resolveFooterMediaData();
        $footerJpSrc = $m['footerJp'];
    @endphp
    @if($footerJpSrc)
    <div style="margin-top:25px; text-align:center;">
        <img src="{{ $footerJpSrc }}" style="max-width:100%; max-height:42px;" alt="Footer JP Books">
    </div>
    @else
    <table style="width:100%; border-collapse:collapse; margin-top:20px; border-top:1.5px solid #ff8a00; padding-top:6px;">
        <tr>
            <td style="font-size:8px; color:#555; vertical-align:middle; line-height:1.4;">
                <strong style="color:#111;">Head Office:</strong> Jl. Karah Agung 45 Surabaya 60232 &bull; Telp. (031) 8289999 (Hunting) &bull; Fax. (031) 8286999
            </td>
        </tr>
    </table>
    @endif
@else
    @php
        $m = resolveFooterMediaData();
        $qrSrc = $m['qr'] ?? '';
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
    @endphp
    <!-- FOOTER CABANG TEMPRINA (FORMAT RESMI GAMBAR 2) -->
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
                @if($qrSrc)
                    <img src="{{ $qrSrc }}" width="42" height="42" alt="QR Code">
                @endif
            </td>
        </tr>
    </table>
@endif
