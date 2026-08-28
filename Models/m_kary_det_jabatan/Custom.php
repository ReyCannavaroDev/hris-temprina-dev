<?php

namespace App\Models\CustomModels;

class m_kary_det_jabatan extends \App\Models\BasicModels\m_kary_det_jabatan
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
        $karyId = $arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null;
        $is_active = $arrayData['is_active'] ?? true;
        $companyId = $arrayData['m_company_id'] ?? null;
        $subcompId = $arrayData['m_subcomp_id'] ?? null;

        if (!$companyId && $subcompId) {
            try {
                $subcomp = \DB::table('m_subcomp')->where('id', $subcompId)->first();
                $companyId = $subcomp?->m_company_id ?? $subcomp?->company_id ?? null;
            } catch (\Throwable $e) {}
        }

        $newArrayData  = array_merge( $arrayData, [
            'm_kary_id'     => $karyId,
            'm_karyawan_id' => $karyId,
            'm_company_id'  => $companyId,
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

        $companyId = $arrayData['m_company_id'] ?? null;
        $subcompId = $arrayData['m_subcomp_id'] ?? null;

        if (!$companyId && $subcompId) {
            try {
                $subcomp = \DB::table('m_subcomp')->where('id', $subcompId)->first();
                $companyId = $subcomp?->m_company_id ?? $subcomp?->company_id ?? null;
                if ($companyId) {
                    $arrayData['m_company_id'] = $companyId;
                }
            } catch (\Throwable $e) {}
        }

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function m_kary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CustomModels\m_kary::class, 'm_karyawan_id', 'id');
    }
}