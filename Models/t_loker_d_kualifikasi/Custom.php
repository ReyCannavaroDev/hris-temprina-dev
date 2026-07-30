<?php

namespace App\Models\CustomModels;

class t_loker_d_kualifikasi extends \App\Models\BasicModels\t_loker_d_kualifikasi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}