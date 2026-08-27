<?php

namespace App\Models\CustomModels;

class m_kary_det_jobdesc extends \App\Models\BasicModels\m_kary_det_jobdesc
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $karyId = $arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null;
        $is_active = $arrayData['is_active'] ?? true;
        $newArrayData  = array_merge( $arrayData, [
            'm_kary_id'     => $karyId,
            'm_karyawan_id' => $karyId,
            'is_active'     => $is_active
        ] );
        
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $karyId = $arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null;
        if ($karyId) {
            $arrayData['m_kary_id'] = $karyId;
            $arrayData['m_karyawan_id'] = $karyId;
        }

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }
}