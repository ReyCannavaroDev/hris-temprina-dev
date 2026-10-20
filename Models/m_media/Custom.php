<?php

namespace App\Models\CustomModels;

class m_media extends \App\Models\BasicModels\m_media
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = ['file_path'];

    public $joins = [
        "m_general.id=m_media.kategori_id",
        "m_comp.id=m_media.m_comp_id",
        "default_users.id=m_media.creator_id",
        "default_users.id=m_media.last_editor_id"
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public static function formatMediaUrl($path)
    {
        $root = function_exists('app') && app()->request ? rtrim(app()->request->root(), '/') : '';
        if (empty($path)) {
            return $root ? ($root . '/images/logo.png') : '/images/logo.png';
        }
        $raw = trim($path);
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://') || str_starts_with($raw, 'data:')) {
            return $raw;
        }

        $partHash = $raw;
        $partName = '';
        if (str_contains($raw, ':::')) {
            $parts = explode(':::', $raw);
            $partHash = trim($parts[0]);
            $partName = trim($parts[1] ?? '');
        }

        $cleanHash = ltrim($partHash, '/');
        $cleanName = ltrim($partName, '/');
        $baseHash = basename($cleanHash);
        $baseName = basename($cleanName);

        $candidates = array_unique(array_filter([
            $baseName,
            $baseHash,
            $cleanName,
            $cleanHash,
        ]));

        $subFolders = [
            'storage/uploads/m_media',
            'uploads/m_media',
            'media',
            'storage',
            'images',
            '',
        ];

        foreach ($subFolders as $folder) {
            foreach ($candidates as $cand) {
                $sub = $folder ? ($folder . '/' . $cand) : $cand;
                $full = function_exists('public_path') ? public_path($sub) : null;
                if ($full && file_exists($full) && is_file($full) && filesize($full) > 50) {
                    $enc = rawurlencode($cand);
                    $finalSub = $folder ? ($folder . '/' . $enc) : $enc;
                    return $root ? ($root . '/' . $finalSub) : ('/' . $finalSub);
                }
            }
        }

        // Fallback default
        $target = $baseName ?: $baseHash;
        $finalSub = 'storage/uploads/m_media/' . rawurlencode($target);
        return $root ? ($root . '/' . $finalSub) : ('/' . $finalSub);
    }

    /**
     * Ambil URL Logo aktif (bisa spesifik comp_id atau fallback ke LOGO_UTAMA / LOGO-IMG)
     */
    public static function getLogoUrl($compId = null)
    {
        $query = \DB::table('m_media')
            ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
            ->where('m_media.is_active', true)
            ->where(function($q) {
                $q->whereRaw("UPPER(m_general.value) = 'LOGO'")
                  ->orWhereRaw("UPPER(m_media.kode) LIKE '%LOGO%'");
            });

        if ($compId) {
            $compLogo = (clone $query)->where('m_media.m_comp_id', $compId)->first();
            if ($compLogo && !empty($compLogo->file_path)) {
                return self::formatMediaUrl($compLogo->file_path);
            }
        }

        $defaultLogo = $query->where(function($q) {
            $q->where('m_media.kode', 'LOGO_UTAMA')
              ->orWhere('m_media.kode', 'LOGO-IMG')
              ->orWhereNull('m_media.m_comp_id');
        })->orderByRaw("CASE WHEN m_media.kode = 'LOGO_UTAMA' THEN 0 WHEN m_media.kode LIKE '%LOGO%' THEN 1 ELSE 2 END")
          ->orderBy('m_media.id', 'asc')
          ->first();

        if ($defaultLogo && !empty($defaultLogo->file_path)) {
            return self::formatMediaUrl($defaultLogo->file_path);
        }

        return self::formatMediaUrl('images/logo.png');
    }
}