<?php

namespace App\Models\CustomModels;

class t_kbs extends \App\Models\BasicModels\t_kbs
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");

    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
      $newArrayData  = array_merge( $arrayData,[
        "nomor" => $this->helper->generateNomor("KODE KBS"),
      ] );
      
      return [
          "model"  => $model,
          "data"   => $newArrayData,
          // "errors" => ['error1']
      ];
    }
}