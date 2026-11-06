<?php

namespace App\Models\CustomModels;

class m_jobdesc_d extends \App\Models\BasicModels\m_jobdesc_d
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function scopeWithJabatan($model){
        $posisi = request('posisi_id',0);
        $base = $model->join('m_posisi as mp', 'm_jobdesc.m_posisi_id','mp.id')->addSelect('jobdesc');
        
        if($posisi != 0){
            return $base->where('m_jobdesc.m_posisi_id',$posisi);
        }
        return $base;
    }
}