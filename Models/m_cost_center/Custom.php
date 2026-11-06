<?php

namespace App\Models\CustomModels;

class m_cost_center extends \App\Models\BasicModels\m_cost_center
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        //dd($arrayData);
        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
}