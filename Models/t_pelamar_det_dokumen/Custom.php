<?php

namespace App\Models\CustomModels;

class t_pelamar_det_dokumen extends \App\Models\BasicModels\t_pelamar_det_dokumen
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ 'file' ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}