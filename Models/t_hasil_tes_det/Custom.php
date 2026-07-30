<?php

namespace App\Models\CustomModels;

class t_hasil_tes_det extends \App\Models\BasicModels\t_hasil_tes_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ 'dokumen' ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}