<?php

namespace App\Models\CustomModels;

class m_assessment_kary_sub_d extends \App\Models\BasicModels\m_assessment_kary_sub_d
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}