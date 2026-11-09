<?php

namespace App\Models\CustomModels;

class m_assessment_kary_d extends \App\Models\BasicModels\m_assessment_kary_d
{    
    public function __construct()
    {
        parent::__construct();
        $this->joins = array_values(array_filter($this->joins, function ($join) {
            return $join !== "m_general.id=m_assessment_kary_d.kategori";
        }));
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}