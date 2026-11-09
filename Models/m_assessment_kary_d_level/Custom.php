<?php

namespace App\Models\CustomModels;

class m_assessment_kary_d_level extends \App\Models\BasicModels\m_assessment_kary_d_level
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function m_level_posisi() :\Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\CustomModels\m_level_posisi', 'm_level_posisi_id', 'id');
    }
}