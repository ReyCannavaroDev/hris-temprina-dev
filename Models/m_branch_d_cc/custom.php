<?php

namespace App\Models\CustomModels;

class m_branch_d_cc extends \App\Models\BasicModels\m_branch_d_cc
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    // public function createBefore( $model, $arrayData, $metaData, $id=null )
    // {

    //     // $isDuplicate = $this->isDuplicate($arrayData);
    //     // // $isDuplicate = true;
    //     // if($isDuplicate){
    //     //     return ["errors" => ['Cost Centre Tidak boleh sama dalam satu cabang'.$isDuplicate]];
    //     // }
    //     $newArrayData  = array_merge( $arrayData,[] );
    //     return [
    //         "model"  => $model,
    //         "data"   => $newArrayData,
    //         // "errors" => ['error1']
    //     ];
    // }

    // private function isDuplicate($data){
    //     $checkData = $this->whereRaw('LOWER(cost_centre_id) = ?', [strtolower($data)])->where('m_branch_id',$arrayData['m_branch_id'])->first();
    //     if($checkData) return true;
    //     return false;
    // }
    
    
}