<?php

namespace App\Models\CustomModels;

class m_level_posisi extends \App\Models\BasicModels\m_level_posisi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function m_level_posisi_d()
    {
        return $this->hasMany(\App\Models\CustomModels\m_level_posisi_d::class, 'm_level_posisi_id', 'id');
    }
}