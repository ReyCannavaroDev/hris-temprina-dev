<?php

namespace App\Models\CustomModels;

class m_level_posisi extends \App\Models\BasicModels\m_level_posisi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public $details = ['m_level_posisi_d'];

    public function m_level_posisi_d()
    {
        return $this->hasMany(\App\Models\CustomModels\m_level_posisi_d::class, 'm_level_posisi_id', 'id');
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->details = [];
        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $this->details = [];
        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $realId = is_object($model) ? $model->id : $id;
        $req = app()->request;
        $details = $req->input('m_level_posisi_d', $req->m_level_posisi_d ?? ($arrayData['m_level_posisi_d'] ?? []));
        $this->syncDetails($realId, $details);
        return true;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $realId = is_object($model) ? $model->id : $id;
        $req = app()->request;
        $details = $req->input('m_level_posisi_d', $req->m_level_posisi_d ?? ($arrayData['m_level_posisi_d'] ?? []));
        $this->syncDetails($realId, $details);
        return true;
    }

    private function syncDetails($levelId, $details)
    {
        if (!is_array($details)) {
            $details = [];
        }

        $posisiIds = [];
        foreach ($details as $row) {
            $posId = is_array($row) ? ($row['m_posisi_id'] ?? null) : ($row->m_posisi_id ?? null);
            if ($posId) {
                $posisiIds[] = (int)$posId;
            }
        }
        $posisiIds = array_unique(array_filter($posisiIds));

        // Hapus posisi yang tidak ada di detail lagi untuk level ini
        \DB::table('m_level_posisi_d')
            ->where('m_level_posisi_id', $levelId)
            ->whereNotIn('m_posisi_id', $posisiIds)
            ->delete();

        // Ambil posisi yang sudah tersimpan di level ini
        $existing = \DB::table('m_level_posisi_d')
            ->where('m_level_posisi_id', $levelId)
            ->pluck('m_posisi_id')
            ->toArray();

        $userId = auth()->user()?->id ?? 1;
        $now = \Carbon\Carbon::now();
        foreach ($posisiIds as $posId) {
            if (!in_array($posId, $existing)) {
                // Hapus dari level lain jika sebelumnya sudah terdaftar (1 posisi hanya 1 level)
                \DB::table('m_level_posisi_d')->where('m_posisi_id', $posId)->delete();

                \DB::table('m_level_posisi_d')->insert([
                    'm_level_posisi_id' => $levelId,
                    'm_posisi_id'       => $posId,
                    'creator_id'        => $userId,
                    'last_editor_id'    => $userId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if ($id) {
            $details = \DB::table('m_level_posisi_d as lpd')
                ->leftJoin('m_posisi as p', 'p.id', '=', 'lpd.m_posisi_id')
                ->where('lpd.m_level_posisi_id', $id)
                ->select(
                    'lpd.id',
                    'lpd.m_level_posisi_id',
                    'lpd.m_posisi_id',
                    'p.name as name',
                    'p.name as posisi_name'
                )
                ->orderBy('p.name', 'asc')
                ->get();
            $row['m_level_posisi_d'] = json_decode(json_encode($details), true) ?? [];
        }
        return $row;
    }
}
