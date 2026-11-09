<?php

namespace App\Models\CustomModels;

class m_level_posisi_d extends \App\Models\BasicModels\m_level_posisi_d
{    
    public function __construct()
    {
        parent::__construct();
        $this->joins = [
            "m_posisi.id=m_level_posisi_d.m_posisi_id",
            "m_level_posisi.id=m_level_posisi_d.m_level_posisi_id",
        ];
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function m_posisi()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_posisi::class, 'm_posisi_id', 'id');
    }

    public function m_level_posisi()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_level_posisi::class, 'm_level_posisi_id', 'id');
    }
}
