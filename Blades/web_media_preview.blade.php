<?php
// Endpoint Web Stream Media Preview untuk HRIS Temprina
$req = function_exists('request') ? request() : app()->request;
$id = $req->get('id');
$kode = $req->get('kode');
$path = $req->get('path');

$rawPath = '';
if ($id) {
    try {
        $row = \DB::table('m_media')->where('id', $id)->first();
        if ($row) {
            $rawPath = $row->file_path ?? '';
            if (empty($kode)) $kode = $row->kode ?? '';
        }
    } catch (\Throwable $e) {}
}

if (empty($rawPath) && $kode) {
    try {
        $row = \DB::table('m_media')->where('kode', $kode)->first();
        if ($row) {
            $rawPath = $row->file_path ?? '';
        }
    } catch (\Throwable $e) {}
}

if (empty($rawPath)) {
    $rawPath = $path ?? '';
}

$rawPath = trim($rawPath);

// 1. Jika rawPath adalah data URI base64
if (str_starts_with($rawPath, 'data:image')) {
    $parts = explode(',', $rawPath, 2);
    if (count($parts) === 2) {
        preg_match('#data:(image/[^;]+)#', $parts[0], $m);
        $mime = $m[1] ?? 'image/jpeg';
        $binary = base64_decode($parts[1]);
        if ($binary) {
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . strlen($binary));
            header('Cache-Control: public, max-age=86400');
            echo $binary;
            exit;
        }
    }
}

// 2. Ekstrak bagian nama berkas dan hash jika ada pemisah ':::'
$partHash = $rawPath;
$partName = '';
if (str_contains($rawPath, ':::')) {
    $p = explode(':::', $rawPath);
    $partHash = trim($p[0]);
    $partName = trim($p[1] ?? '');
}

$cleanHash = ltrim(preg_replace('#^https?://[^/]+/#i', '', $partHash), '/');
$cleanName = ltrim(preg_replace('#^https?://[^/]+/#i', '', $partName), '/');
$baseHash = basename($cleanHash);
$baseName = basename($cleanName);

$cands = array_unique(array_filter([
    $baseName,
    $baseHash,
    $cleanName,
    $cleanHash,
    $rawPath,
    basename($rawPath),
    urldecode($baseName),
    urldecode($baseHash),
]));

// Fallback nama aset bawaan jika ada kode media
$upperKode = strtoupper(trim($kode ?? ''));
$assetFallbacks = [
    'KOP_DEMOSI' => ['media/header_pkwt_temprina.jpg', 'media/dim_logo.jpg', 'images/logo.png'],
    'KOP-DEMOSI' => ['media/header_pkwt_temprina.jpg', 'media/dim_logo.jpg', 'images/logo.png'],
    'FOOTER-PKWT' => ['media/footer_pkwt.png', 'media/footer_pkwt_template.jpg'],
    'KOP-PKWT' => ['media/header_pkwt_temprina.jpg'],
    'LOGO_ISO_FOOTER' => ['media/banner_iso_temprina.jpg', 'media/dim_logo.jpg'],
    'HEADER-JPBOOKS' => ['media/header_jpbooks.jpg'],
    'FOOTER-JPBOOKS' => ['media/footer_jpbooks.jpg'],
    'LOGO-JPBOOKS' => ['media/jpbooks_logo.png'],
    'QR-TEMPRINA' => ['media/qr_temprina.jpg'],
    'FOOTER-TEMPRINA' => ['media/footer_cabang_temprina.jpg'],
    'BANNER-ISO-TEMPRINA' => ['media/banner_iso_temprina.jpg'],
    'LOGO-TMG' => ['media/logo_temprina_pkwt.png', 'media/dim_logo.jpg'],
    'LOGO_UTAMA' => ['media/logo_temprina_pkwt.png', 'media/dim_logo.jpg'],
];

if (isset($assetFallbacks[$upperKode])) {
    foreach ($assetFallbacks[$upperKode] as $af) {
        $cands[] = $af;
        $cands[] = basename($af);
    }
}

$prefixes = [
    'storage/uploads/m_media',
    'uploads/m_media',
    'storage/uploads',
    'uploads',
    'media',
    'storage',
    'images',
    '',
];

$foundBinary = null;
$foundMime = 'image/jpeg';

// Cari berkas di sistem berkas server
foreach ($prefixes as $pfx) {
    foreach ($cands as $cand) {
        $candClean = ltrim(trim($cand), '/');
        if (empty($candClean)) continue;

        $sub = $pfx ? ($pfx . '/' . $candClean) : $candClean;
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
            $pathsToCheck[] = base_path($sub);
            $pathsToCheck[] = base_path('public/' . $sub);
            $pathsToCheck[] = base_path('storage/app/public/' . $sub);
        }
        $pathsToCheck[] = '/opt/www/hris/storage/app/public/' . $sub;
        $pathsToCheck[] = '/opt/www/hris/public/' . $sub;
        $pathsToCheck[] = '/opt/www/hris/' . $sub;
        $pathsToCheck[] = '/var/www/hris/storage/app/public/' . $sub;
        $pathsToCheck[] = '/var/www/hris/public/' . $sub;
        $pathsToCheck[] = '/var/www/hris/' . $sub;

        foreach ($pathsToCheck as $full) {
            if ($full && @file_exists($full) && @is_file($full) && @filesize($full) > 50) {
                $foundBinary = @file_get_contents($full);
                if ($foundBinary && strlen($foundBinary) > 50) {
                    $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                    $foundMime = match($ext) {
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        'svg' => 'image/svg+xml',
                        default => 'image/jpeg'
                    };
                    break 3;
                }
            }
        }
    }
}

// 3. Fallback kop demosi default jika file lokal tidak ditemukan
if (empty($foundBinary) && ($upperKode === 'KOP_DEMOSI' || $upperKode === 'KOP-DEMOSI' || str_contains(strtolower($rawPath), 'demosi'))) {
    if (function_exists('kopDemosiDefaultB64')) {
        $foundBinary = base64_decode(kopDemosiDefaultB64());
        $foundMime = 'image/jpeg';
    }
}

// 4. Fallback logo utama perusahaan
if (empty($foundBinary)) {
    $logoPaths = [
        function_exists('public_path') ? public_path('images/logo.png') : '',
        function_exists('base_path') ? base_path('images/logo.png') : '',
        '/opt/www/hris/public/images/logo.png',
        '/var/www/hris/public/images/logo.png',
    ];
    foreach ($logoPaths as $lp) {
        if ($lp && @file_exists($lp)) {
            $foundBinary = @file_get_contents($lp);
            $foundMime = 'image/png';
            break;
        }
    }
}

// 5. Fallback SVG jika berkas fisik belum ada sama sekali di server
if (empty($foundBinary)) {
    $label = htmlspecialchars($upperKode ?: 'MEDIA PREVIEW');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="150" viewBox="0 0 300 150">
        <rect width="100%" height="100%" fill="#f8fafc"/>
        <rect x="6" y="6" width="288" height="138" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-dasharray="6,4" rx="8"/>
        <circle cx="150" cy="55" r="22" fill="#e2e8f0"/>
        <path d="M142 55 L150 45 L158 55 Z" fill="#94a3b8"/>
        <text x="50%" y="95" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="13" font-weight="bold" fill="#334155">' . $label . '</text>
        <text x="50%" y="116" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#64748b">Aset Media Belum Tersedia di Disk</text>
    </svg>';
    $foundBinary = $svg;
    $foundMime = 'image/svg+xml';
}

header('Content-Type: ' . $foundMime);
header('Content-Length: ' . strlen($foundBinary));
header('Cache-Control: public, max-age=86400');
echo $foundBinary;
exit;
