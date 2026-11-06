<?php

namespace App\Models\CustomModels;

class m_company extends \App\Models\BasicModels\m_company
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

        $isDuplicate = $this->isDuplicate($arrayData['name'],$id=null);
        if($isDuplicate){
            return ["errors" => ['Nama Company '.$arrayData['name']. ' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    private function isDuplicate($data, $id){
        $query = $this->whereRaw('LOWER(name) = ?', [strtolower($data)]);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        $checkData = $query->first();
        return $checkData ? true : false;
    }

     public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $isDuplicate = $this->isDuplicate($arrayData['name'],$id);
        if($isDuplicate){
            return ["errors" => ['Nama Company '.$arrayData['name']. ' Sudah Ada']];
        }
        
        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
    
    
}