<?php

namespace App\Models\CustomModels;
use DB;

class t_klaim_askes_d extends \App\Models\BasicModels\t_klaim_askes_d
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }
    
    public $fileColumns = [];


    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
    
    // Override bug empty string required from Basic model
    public $required = [];

}