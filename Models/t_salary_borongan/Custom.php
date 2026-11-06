<?php

namespace App\Models\CustomModels;

class t_salary_borongan extends \App\Models\BasicModels\t_salary_borongan
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}