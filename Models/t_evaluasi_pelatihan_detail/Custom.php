<?php

namespace App\Models\CustomModels;

class t_evaluasi_pelatihan_detail extends \App\Models\BasicModels\t_evaluasi_pelatihan_detail
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}