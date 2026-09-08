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

    /**
     * Ambil URL Logo aktif (bisa spesifik comp_id atau fallback ke LOGO_UTAMA)
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
                $path = $compLogo->file_path;
                return (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) ? $path : asset(ltrim($path, '/'));
            }
        }

        $defaultLogo = $query->where(function($q) {
            $q->where('m_media.kode', 'LOGO_UTAMA')
              ->orWhereNull('m_media.m_comp_id');
        })->orderBy('m_media.id', 'asc')->first();

        if ($defaultLogo && !empty($defaultLogo->file_path)) {
            $path = $defaultLogo->file_path;
            return (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) ? $path : asset(ltrim($path, '/'));
        }

        return asset('images/logo.png');
    }
}