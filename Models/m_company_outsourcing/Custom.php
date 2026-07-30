<?php

namespace App\Models\CustomModels;

class m_company_outsourcing extends \App\Models\BasicModels\m_company_outsourcing
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}