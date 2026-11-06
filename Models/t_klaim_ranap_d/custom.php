<?php

namespace App\Models\CustomModels;
use DB;

class t_klaim_ranap_d extends \App\Models\BasicModels\t_klaim_ranap_d
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ 'bukti' ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
}
