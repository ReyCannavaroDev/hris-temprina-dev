<?php

namespace App\Models\CustomModels;

class m_jobdesc extends \App\Models\BasicModels\m_jobdesc
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
        $checkDuplicate = $this->isDuplicate($arrayData);
        if($checkDuplicate['duplicate']){
            return['errors' =>['Jobdesc '.$checkDuplicate['value'].' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $checkDuplicate = $this->isDuplicate($arrayData, $id);
        if($checkDuplicate['duplicate']){
            return['errors' =>['Jobdesc '.$checkDuplicate['value'].' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    

    private function isDuplicate($data, $id = null){

        $duplicate = "";

        $query = $this->where('m_posisi_id', $data['m_posisi_id'])->join('m_posisi as mp', 'm_jobdesc.m_posisi_id', 'mp.id');

        if ($id) {
            $query->where('m_jobdesc.id', '!=', $id);
        }

        $checkData = $query->first();

        return[
            'duplicate' => (bool)@$checkData ?? false,
            'value' => @$checkData['desc_kerja'] ?? "",
        ];
    }

    public function scopegetbyposisi()
    {
        $m_posisi_id = app()->request->m_posisi_id;
        $data = m_jobdesc_d::whereHas('m_jobdesc', function($q) use ($m_posisi_id){
            $q->where('m_posisi_id', $m_posisi_id);
        });

        return $data;
    }
    
    
}