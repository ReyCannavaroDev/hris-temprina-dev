<?php

namespace App\Models\CustomModels;

class m_kary_det_jabatan extends \App\Models\BasicModels\m_kary_det_jabatan
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $is_active = $arrayData['is_active'] ?? false ;
        $newArrayData  = array_merge( $arrayData,[
            'is_active' => $is_active
        ] );
        
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
     
}