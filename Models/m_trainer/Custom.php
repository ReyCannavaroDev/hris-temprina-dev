<?php

namespace App\Models\CustomModels;

class m_trainer extends \App\Models\BasicModels\m_trainer
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function transformRowData( array $row )
    {
        $row['jenis_training'] = m_general::find($row['jenis_training_id'] ?? 0)?->value ?? '-';
        return $row;
    }
    
    
}