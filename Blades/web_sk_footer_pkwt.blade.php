@php
/**
 * Master Footer Surat PKWT - HRIS Temprina
 * Dinamis mengambil dari data tabel m_media (mengutamakan upload terbaru Footer / Banner / PKWT)
 */
$pkwtFtrSrc = '';

try {
    $footerMediaPkwt = \DB::table('m_media')
        ->select('m_media.*')
        ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
        ->where(function($q) {
            $q->where('m_media.is_active', true)
              ->orWhereNull('m_media.is_active');
        })
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
                    ->where(function($sub2) {
                        $sub2->whereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'");
                    });
            })
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) IN ('FOOTER-PKWT', 'BANNER-PKWT', 'FOOTER_PKWT', 'BANNER_PKWT', 'FOOTER-PKTW', 'BANNER-PKTW', 'LOGO-PKWT-FOOTER', 'LOGO-FOOTER-PKWT', 'FOOTER', 'BANNER', 'FOOTER-SURAT', 'FOOTER-CABANG', 'FOOTER-TEMPRINA')")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%BANNER%'");
        })
        ->orderByRaw("CASE 
            WHEN UPPER(COALESCE(m_media.kode,'')) IN ('FOOTER-PKWT', 'FOOTER_PKWT', 'FOOTER-PKTW', 'BANNER-PKWT') THEN 0
            WHEN (UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%') THEN 1
            WHEN (UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%') THEN 2
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%' THEN 3
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%' THEN 4
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%' THEN 5
            ELSE 10
        END")
        ->orderBy('m_media.id', 'desc')
        ->first();

    // Cari Kop PKWT terlebih dahulu untuk mengetahui ID-nya (agar footer tidak mengambil media kop)
    $kopId = null;
    $kopCheck = \DB::table('m_media')
        ->where(function($q) {
            $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
        })
        ->where(function($q) {
            $q->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
              ->where(function($sub) {
                  $sub->whereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%KOP%'")
                      ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%KOP%'")
                      ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%'")
                      ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%HEADER%'");
              });
        })
        ->orderBy('m_media.id', 'desc')
        ->first();
    if ($kopCheck) {
        $kopId = $kopCheck->id;
    }

    // Jika belum ketemu tapi ada media terbaru lain bertema PKWT (bukan kop)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopId) {
                if ($kopId) {
                    $q->where('m_media.id', '!=', $kopId);
                }
                $q->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%HEADER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%HEADER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%KOP%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%KOP%'");
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    // Fallback 2: cari media footer umum (bukan Kop)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopId) {
                if ($kopId) {
                    $q->where('m_media.id', '!=', $kopId);
                }
                $q->where(function($sub) {
                    $sub->whereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'");
                });
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    // Fallback ultimate: jika masih belum ketemu, ambil media terbaru lain (selain Kop yang sudah terpilih)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopId) {
                if ($kopId) {
                    $q->where('m_media.id', '!=', $kopId);
                }
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    if ($footerMediaPkwt && !empty($footerMediaPkwt->file_path)) {
        $rawPath = trim($footerMediaPkwt->file_path);
        if (str_starts_with($rawPath, 'data:image')) {
            $pkwtFtrSrc = $rawPath;
        } else {
            $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
            $clean = ltrim($clean, '/');

            $partHash = $clean;
            $partName = '';
            if (str_contains($clean, ':::')) {
                $parts = explode(':::', $clean);
                $partHash = trim($parts[0]);
                $partName = trim($parts[1] ?? '');
            }

            $candidates = array_unique(array_filter([
                $clean,
                $partHash,
                $partName,
                basename($clean),
                basename($partHash),
                basename($partName),
            ]));

            $subPaths = [];
            foreach ($candidates as $c) {
                $c = ltrim(trim($c), '/');
                if (empty($c)) continue;
                $b = basename($c);
                $subPaths[] = $c;
                $subPaths[] = $b;
                $subPaths[] = 'uploads/m_media/' . $b;
                $subPaths[] = 'storage/uploads/m_media/' . $b;
                $subPaths[] = 'storage/' . $b;
                $subPaths[] = 'uploads/' . $b;
                $subPaths[] = 'storage/app/public/uploads/m_media/' . $b;
                $subPaths[] = 'storage/app/public/' . $b;
                $subPaths[] = 'uploads/m_media/' . $c;
                $subPaths[] = 'storage/uploads/m_media/' . $c;
                $subPaths[] = 'storage/' . $c;
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
                    $pathsToCheck[] = base_path('public/' . $sub);
                    $pathsToCheck[] = base_path('storage/app/public/' . $sub);
                    $pathsToCheck[] = base_path($sub);
                }
                if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                    $pathsToCheck[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $sub;
                    $pathsToCheck[] = dirname($_SERVER['DOCUMENT_ROOT']) . '/storage/app/public/' . $sub;
                }
                $pathsToCheck[] = '/var/www/html/public/' . $sub;
                $pathsToCheck[] = '/var/www/html/storage/app/public/' . $sub;
                $pathsToCheck[] = '/var/www/hris/public/' . $sub;
                $pathsToCheck[] = '/var/www/hris/storage/app/public/' . $sub;
                $pathsToCheck[] = '/opt/www/hris/public/' . $sub;
                $pathsToCheck[] = '/opt/www/hris/storage/app/public/' . $sub;

                foreach ($pathsToCheck as $fullPath) {
                    if ($fullPath && file_exists($fullPath) && is_file($fullPath) && filesize($fullPath) > 50) {
                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                        if (empty($ext) || !in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                            $h = @fopen($fullPath, 'rb');
                            $magic = $h ? @fread($h, 4) : '';
                            if ($h) @fclose($h);
                            if (str_starts_with($magic, "\x89PNG")) $ext = 'png';
                            elseif (str_starts_with($magic, "\xFF\xD8")) $ext = 'jpg';
                            elseif (str_starts_with($magic, "GIF")) $ext = 'gif';
                            else $ext = 'jpg';
                        }
                        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'gif') ? 'image/gif' : 'image/jpeg');
                        $pkwtFtrSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                        break 2;
                    }
                }
            }

            if (empty($pkwtFtrSrc) && function_exists('file_get_contents')) {
                $streamCtx = stream_context_create([
                    'http' => ['timeout' => 3, 'ignore_errors' => true],
                    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
                ]);
                $urls = [];
                if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                    $urls[] = $rawPath;
                }
                foreach ($candidates as $cand) {
                    $b = basename($cand);
                    $urls[] = 'https://hris.temprina.com/storage/uploads/m_media/' . $b;
                    $urls[] = 'https://hris.temprina.com/storage/uploads/m_media/' . rawurlencode($b);
                    $urls[] = 'https://hris.temprina.com/uploads/m_media/' . $b;
                    $urls[] = 'https://hris.temprina.com/uploads/m_media/' . rawurlencode($b);
                    $urls[] = 'https://hris.temprina.com/storage/' . $b;
                    $urls[] = 'https://hris.temprina.com/' . ltrim($cand, '/');
                    $urls[] = 'http://127.0.0.1/storage/uploads/m_media/' . $b;
                    $urls[] = 'http://127.0.0.1/uploads/m_media/' . $b;
                }
                foreach (array_unique(array_filter($urls)) as $u) {
                    try {
                        $remoteData = @file_get_contents($u, false, $streamCtx);
                        if ($remoteData && strlen($remoteData) > 50) {
                            $mime = str_starts_with($remoteData, "\x89PNG") ? 'image/png' : 'image/jpeg';
                            $pkwtFtrSrc = 'data:' . $mime . ';base64,' . base64_encode($remoteData);
                            break;
                        }
                    } catch (\Throwable $th) {}
                }
            }
        }
    }
} catch (\Throwable $e) {}

// Fallback jika belum tersimpan di DB atau file belum ditemukan di server
if (empty($pkwtFtrSrc) && function_exists('base_path')) {
    $localChecks = [
        base_path('media/footer_pkwt.png'),
        base_path('media/footer_pkwt_temprina_clean.png'),
        base_path('media/footer_opt.jpg'),
        base_path('media/footer_cabang_temprina.jpg'),
    ];
    foreach ($localChecks as $lf) {
        if ($lf && file_exists($lf) && is_file($lf) && filesize($lf) > 50) {
            $ext = strtolower(pathinfo($lf, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
            $pkwtFtrSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($lf));
            break;
        }
    }
}

if (empty($pkwtFtrSrc)) {
    $pkwtFtrSrc = 'data:image/jpeg;base64,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';
}
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:14px; padding:0;">
  <tr>
    <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
      <img src="{{ $pkwtFtrSrc }}" width="700" style="width:700px; max-width:100%;" alt="Footer PKWT">
    </td>
  </tr>
</table>
