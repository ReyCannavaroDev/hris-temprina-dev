<?php

namespace App\Models\CustomModels;

class m_prog_pelatihan_d_divisi extends \App\Models\BasicModels\m_prog_pelatihan_d_divisi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}