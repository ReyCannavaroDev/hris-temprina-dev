<?php

namespace App\Models\CustomModels;

class m_kary_det_jobdesc extends \App\Models\BasicModels\m_kary_det_jobdesc
{    
    public function __construct()
    {
        parent::__construct();
        $this->required = [];
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    private $allowedColumns = [
        "id", "m_kary_id", "m_posisi_id", "jobdesc", "is_active", 
        "creator_id", "last_editor_id", "created_at", "updated_at", "m_karyawan_id"
    ];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $karyId = $arrayData['m_karyawan_id'] 
            ?? $arrayData['m_kary_id'] 
            ?? ($metaData['parent_id'] ?? null) 
            ?? app()->request->id 
            ?? app()->request->route('id') 
            ?? null;

        $is_active = isset($arrayData['is_active']) 
            ? filter_var($arrayData['is_active'], FILTER_VALIDATE_BOOLEAN) 
            : true;

        $newArrayData  = array_merge( $arrayData, [
            'm_kary_id'     => $karyId,
            'm_karyawan_id' => $karyId,
            'is_active'     => $is_active
        ] );

        $filteredData = array_intersect_key($newArrayData, array_flip($this->allowedColumns));
        
        return [
            "model"  => $model,
            "data"   => $filteredData,
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $karyId = $arrayData['m_karyawan_id'] 
            ?? $arrayData['m_kary_id'] 
            ?? ($metaData['parent_id'] ?? null) 
            ?? app()->request->id 
            ?? app()->request->route('id') 
            ?? null;

        if ($karyId) {
            $arrayData['m_kary_id'] = $karyId;
            $arrayData['m_karyawan_id'] = $karyId;
        }

        $detailId = $id ?? $arrayData['id'] ?? null;
        if (array_key_exists('is_active', $arrayData)) {
            $isActive = filter_var($arrayData['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            if ($detailId) {
                \DB::table('m_kary_det_jobdesc')->where('id', $detailId)->update([
                    'is_active' => $isActive
                ]);
            }
            unset($arrayData['is_active']);
        }

        $filteredData = array_intersect_key($arrayData, array_flip($this->allowedColumns));

        return [
            "model"  => $model,
            "data"   => $filteredData,
        ];
    }

    public function m_kary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CustomModels\m_kary::class, 'm_karyawan_id', 'id');
    }
}
