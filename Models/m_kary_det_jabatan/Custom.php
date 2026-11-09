<?php

namespace App\Models\CustomModels;

class m_kary_det_jabatan extends \App\Models\BasicModels\m_kary_det_jabatan
{    
    public function __construct()
    {
        parent::__construct();
        $this->required = [];
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    private $allowedColumns = [
        "id", "m_kary_id", "m_comp_id", "m_subcomp_id", "m_branch_id", 
        "m_posisi_id", "start_time", "end_time", "desc", "is_primary", 
        "is_active", "creator_id", "last_editor_id", "created_at", 
        "updated_at", "m_divisi_id", "m_karyawan_id", "m_company_id"
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

        $is_primary = isset($arrayData['is_primary']) 
            ? filter_var($arrayData['is_primary'], FILTER_VALIDATE_BOOLEAN) 
            : (isset($arrayData['primary']) ? filter_var($arrayData['primary'], FILTER_VALIDATE_BOOLEAN) : false);

        $companyId = $arrayData['m_company_id'] ?? null;
        $subcompId = $arrayData['m_subcomp_id'] ?? null;

        if (!$companyId && $subcompId) {
            try {
                $subcomp = \DB::table('m_subcomp')->where('id', $subcompId)->first();
                $companyId = $subcomp ? ($subcomp->m_company_id ?? $subcomp->company_id ?? null) : null;
            } catch (\Throwable $e) {}
        }

        $newArrayData  = array_merge( $arrayData, [
            'm_kary_id'     => $karyId,
            'm_karyawan_id' => $karyId,
            'm_company_id'  => $companyId,
            'is_primary'    => $is_primary,
            'is_active'     => $is_active
        ] );

        if (is_object($model)) {
            unset($model->level_name, $model->sequence, $model->posisi_name);
            foreach (array_keys($model->getAttributes()) as $key) {
                if (!in_array($key, $this->allowedColumns)) {
                    unset($model->$key);
                }
            }
        }

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

        $companyId = $arrayData['m_company_id'] ?? null;
        $subcompId = $arrayData['m_subcomp_id'] ?? null;

        if (!$companyId && $subcompId) {
            try {
                $subcomp = \DB::table('m_subcomp')->where('id', $subcompId)->first();
                $companyId = $subcomp ? ($subcomp->m_company_id ?? $subcomp->company_id ?? null) : null;
                if ($companyId) {
                    $arrayData['m_company_id'] = $companyId;
                }
            } catch (\Throwable $e) {}
        }

        if (isset($arrayData['is_primary']) || isset($arrayData['primary'])) {
            $arrayData['is_primary'] = filter_var($arrayData['is_primary'] ?? $arrayData['primary'], FILTER_VALIDATE_BOOLEAN);
        }

        $detailId = $id ?? $arrayData['id'] ?? null;
        if (array_key_exists('is_active', $arrayData)) {
            $isActive = filter_var($arrayData['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            if ($detailId) {
                \DB::table('m_kary_det_jabatan')->where('id', $detailId)->update([
                    'is_active' => $isActive
                ]);
            }
            unset($arrayData['is_active']);
        }

        if (is_object($model)) {
            unset($model->level_name, $model->sequence, $model->posisi_name);
            foreach (array_keys($model->getAttributes()) as $key) {
                if (!in_array($key, $this->allowedColumns)) {
                    unset($model->$key);
                }
            }
        }

        $filteredData = array_intersect_key($arrayData, array_flip($this->allowedColumns));

        return [
            "model"  => $model,
            "data"   => $filteredData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $karyId = $model['m_karyawan_id'] ?? $model['m_kary_id'] ?? $arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null;
        if ($karyId) {
            try {
                \DB::table('m_kary')->where('id', $karyId)->update(['is_sync' => false]);
                \DB::table('default_users')->where('m_kary_id', $karyId)->update(['is_sync' => false]);
            } catch (\Throwable $e) {}
            $this->syncKeKaryawan($karyId);
        }
    }

    // Samakan jabatan/divisi/cabang di m_kary dengan jabatan utama terbaru.
    // Detail disimpan setelah header m_kary, jadi sinkron di m_kary::updateAfter saja membaca data lama.
    private function syncKeKaryawan($karyId)
    {
        try {
            (new \App\Models\CustomModels\m_kary)->syncKaryawanJabatan($karyId);
        } catch (\Throwable $e) {}
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $karyId = (is_object($model) ? ($model->m_karyawan_id ?? $model->m_kary_id ?? null) : null)
            ?? ($arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null);
        if ($karyId) {
            try {
                \DB::table('m_kary')->where('id', $karyId)->update(['is_sync' => false]);
                \DB::table('default_users')->where('m_kary_id', $karyId)->update(['is_sync' => false]);
            } catch (\Throwable $e) {}
            $this->syncKeKaryawan($karyId);
        }
    }

    public function deleteAfter($model, $arrayData, $metaData, $id = null)
    {
        $karyId = (is_object($model) ? ($model->m_karyawan_id ?? $model->m_kary_id ?? null) : null)
            ?? ($arrayData['m_karyawan_id'] ?? $arrayData['m_kary_id'] ?? null);
        if ($karyId) {
            try {
                \DB::table('m_kary')->where('id', $karyId)->update(['is_sync' => false]);
                \DB::table('default_users')->where('m_kary_id', $karyId)->update(['is_sync' => false]);
            } catch (\Throwable $e) {}
            $this->syncKeKaryawan($karyId);
        }
    }

    public function m_kary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CustomModels\m_kary::class, 'm_karyawan_id', 'id');
    }

    public function onRetrieved($model)
    {
        if (!empty($model->m_posisi_id)) {
            try {
                $lvl = \DB::table('m_level_posisi_d as lpd')
                    ->join('m_level_posisi as lp', 'lp.id', '=', 'lpd.m_level_posisi_id')
                    ->where('lpd.m_posisi_id', $model->m_posisi_id)
                    ->select('lp.sequence', 'lp.level_name')
                    ->first();
                if ($lvl) {
                    $model->level_name = $lvl->level_name;
                    $model->sequence = $lvl->sequence;
                }
                $pos = \DB::table('m_posisi')->where('id', $model->m_posisi_id)->value('name');
                if ($pos) {
                    $model->posisi_name = $pos;
                }
            } catch (\Throwable $e) {}
        }
    }
}
