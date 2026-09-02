<?php

namespace App\Models\CustomModels;

class m_assessment_kary extends \App\Models\BasicModels\m_assessment_kary
{    
    public function __construct()
    {
        parent::__construct();
        $this->joins = array_values(array_filter($this->joins, function ($join) {
            return !in_array($join, [
                "m_general.id=m_assessment_kary.type",
                "m_divisi.id=m_assessment_kary.m_divisi_id"
            ]);
        }));
        // Bypass default GlobalHelper auto details fetching to prevent "Undefined array key 0" on empty relations
        $this->details = [];
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function m_assessment_kary_d_level() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\CustomModels\m_assessment_kary_d_level', 'm_assessment_kary_id', 'id');
    }

    public function m_assessment_kary_d() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\CustomModels\m_assessment_kary_d', 'm_assessment_kary_id', 'id');
    }

    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        $req = app()->request;

        if (!empty($req->m_assessment_kary_d) && is_array($req->m_assessment_kary_d)) {
            $categories = array_filter(array_column($req->m_assessment_kary_d, 'kategori'));
            if (count($categories) !== count(array_unique($categories))) {
                trigger_error("Hanya bisa memilih 1 komponen per kategori");
            }
        }

        $this->details = [];

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $req = app()->request;

        if (!empty($req->m_assessment_kary_d) && is_array($req->m_assessment_kary_d)) {
            $categories = array_filter(array_column($req->m_assessment_kary_d, 'kategori'));
            if (count($categories) !== count(array_unique($categories))) {
                trigger_error("Hanya bisa memilih 1 komponen per kategori");
            }
        }

        $this->details = [];

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id=null)
    {
        $realId = is_object($model) ? $model->id : ($id ?? ($arrayData['id'] ?? null));
        if ($realId) {
            $this->syncLevelData($realId, $arrayData);
            $this->syncDetailData($realId, $arrayData);
        }
        return true;
    }

    public function updateAfter($model, $arrayData, $metaData, $id=null)
    {
        $realId = is_object($model) ? $model->id : ($id ?? ($arrayData['id'] ?? null));
        if ($realId) {
            $this->syncLevelData($realId, $arrayData);
            $this->syncDetailData($realId, $arrayData);
        }
        return true;
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        $delId = is_object($model) ? $model->id : ($id ?? null);
        if ($delId) {
            \DB::table('m_assessment_kary_d_level')->where('m_assessment_kary_id', $delId)->delete();
            $detailIds = \DB::table('m_assessment_kary_d')->where('m_assessment_kary_id', $delId)->pluck('id')->toArray();
            if (!empty($detailIds)) {
                \DB::table('m_assessment_kary_sub_d')->whereIn('m_assessment_kary_d_id', $detailIds)->delete();
                \DB::table('m_assessment_kary_d')->whereIn('id', $detailIds)->delete();
            }
        }
        return true;
    }

    private function syncDetailData($assessmentId, $arrayData)
    {
        $req = app()->request;
        $details = $req->input('m_assessment_kary_d', $req->m_assessment_kary_d ?? ($arrayData['m_assessment_kary_d'] ?? []));

        if (!is_array($details)) {
            $details = [];
        }

        $existingDetailIds = \DB::table('m_assessment_kary_d')
            ->where('m_assessment_kary_id', $assessmentId)
            ->pluck('id')
            ->toArray();

        $processedDetailIds = [];

        foreach ($details as $item) {
            $item = is_array($item) ? $item : (array)$item;
            $nama = $item['nama_assessment'] ?? null;
            $kategori = $item['kategori'] ?? null;
            $bobot = $item['bobot'] ?? 0;

            if (empty($nama) && empty($kategori)) {
                continue;
            }

            $detailId = !empty($item['id']) && in_array($item['id'], $existingDetailIds) ? (int)$item['id'] : null;

            $detailData = [
                'm_assessment_kary_id' => $assessmentId,
                'nama_assessment' => $nama,
                'kategori' => $kategori ? (int)$kategori : null,
                'bobot' => (int)$bobot,
                'last_editor_id' => auth()->user() ? auth()->user()->id : 1,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            if ($detailId) {
                \DB::table('m_assessment_kary_d')->where('id', $detailId)->update($detailData);
            } else {
                $detailData['creator_id'] = auth()->user() ? auth()->user()->id : 1;
                $detailData['created_at'] = \Carbon\Carbon::now();
                $detailId = \DB::table('m_assessment_kary_d')->insertGetId($detailData);
            }

            $processedDetailIds[] = $detailId;

            // Simpan / Sinkronkan Sub-Detail (m_assessment_kary_sub_d)
            $subDetails = $item['m_assessment_kary_sub_d'] ?? [];
            if (!is_array($subDetails)) {
                $subDetails = [];
            }

            $existingSubIds = \DB::table('m_assessment_kary_sub_d')
                ->where('m_assessment_kary_d_id', $detailId)
                ->pluck('id')
                ->toArray();

            $processedSubIds = [];

            foreach ($subDetails as $subItem) {
                $subItem = is_array($subItem) ? $subItem : (array)$subItem;
                $keterangan = $subItem['keterangan'] ?? null;
                $nilai = $subItem['nilai'] ?? 0;

                if (empty($keterangan) && $nilai === null) {
                    continue;
                }

                $subId = !empty($subItem['id']) && in_array($subItem['id'], $existingSubIds) ? (int)$subItem['id'] : null;

                $subData = [
                    'm_assessment_kary_d_id' => $detailId,
                    'keterangan' => $keterangan ?: '',
                    'nilai' => (int)$nilai,
                    'last_editor_id' => auth()->user() ? auth()->user()->id : 1,
                    'updated_at' => \Carbon\Carbon::now(),
                ];

                if ($subId) {
                    \DB::table('m_assessment_kary_sub_d')->where('id', $subId)->update($subData);
                } else {
                    $subData['creator_id'] = auth()->user() ? auth()->user()->id : 1;
                    $subData['created_at'] = \Carbon\Carbon::now();
                    $subId = \DB::table('m_assessment_kary_sub_d')->insertGetId($subData);
                }

                $processedSubIds[] = $subId;
            }

            // Hapus sub-detail yang sudah tidak ada
            $deleteSubIds = array_diff($existingSubIds, $processedSubIds);
            if (!empty($deleteSubIds)) {
                \DB::table('m_assessment_kary_sub_d')->whereIn('id', $deleteSubIds)->delete();
            }
        }

        // Hapus detail dan sub-detail yang sudah tidak ada
        $deleteDetailIds = array_diff($existingDetailIds, $processedDetailIds);
        if (!empty($deleteDetailIds)) {
            \DB::table('m_assessment_kary_sub_d')->whereIn('m_assessment_kary_d_id', $deleteDetailIds)->delete();
            \DB::table('m_assessment_kary_d')->whereIn('id', $deleteDetailIds)->delete();
        }
    }

    private function syncLevelData($assessmentId, $arrayData)
    {
        $req = app()->request;
        $levelData = $req->input('m_assessment_kary_d_level', $req->m_assessment_kary_d_level ?? ($arrayData['m_assessment_kary_d_level'] ?? []));
        $singleLevelId = $req->input('m_level_posisi_id', $arrayData['m_level_posisi_id'] ?? null);

        if (empty($levelData) && !empty($singleLevelId)) {
            $levelData = [['m_level_posisi_id' => $singleLevelId]];
        }

        \DB::table('m_assessment_kary_d_level')->where('m_assessment_kary_id', $assessmentId)->delete();

        if (!empty($levelData) && is_array($levelData)) {
            $insertRows = [];
            foreach ($levelData as $item) {
                $lvlId = is_array($item) ? ($item['m_level_posisi_id'] ?? $item['id'] ?? null) : $item;
                $numLvl = (int)$lvlId;
                if ($numLvl > 0) {
                    $insertRows[] = [
                        'm_assessment_kary_id' => $assessmentId,
                        'm_level_posisi_id' => $numLvl,
                        'creator_id' => auth()->user() ? auth()->user()->id : 1,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                }
            }
            if (!empty($insertRows)) {
                \DB::table('m_assessment_kary_d_level')->insert($insertRows);
            }
        }
    }

    public function scopeForKaryawan($query)
    {
        $karyawan_id = request('karyawan_id');
        if (!$karyawan_id) return $query;
        
        $karyawan = \App\Models\BasicModels\m_kary::find($karyawan_id);
        if (!$karyawan || !$karyawan->m_posisi_id) return $query;

        // Get level_posisi_id of the karyawan
        $level_id = \DB::table('m_level_posisi_d')
            ->where('m_posisi_id', $karyawan->m_posisi_id)
            ->value('m_level_posisi_id');

        if (!$level_id) {
            return $query->whereRaw("1 = 0");
        }

        // Filter m_assessment_kary based on level and divisi
        $query->whereHas('m_assessment_kary_d_level', function($q) use ($level_id) {
            $q->where('m_level_posisi_id', $level_id);
        });

        $query->where(function($q) use ($karyawan) {
            $q->whereNull('m_divisi_id')
              ->orWhere('m_divisi_id', 0)
              ->orWhere('m_divisi_id', $karyawan->m_divisi_id);
        });

        return $query;
    }

    public function transformRowData( array $row)
    {
        try {
            $data = [];
            // Gunakan 'this.id' bila tersedia (generator join bisa menimpa 'id' dengan m_general.id)
            $assessmentId = $row['this.id'] ?? $row['id'] ?? null;
            if (!$assessmentId && isset($row['m_assessment_kary_d']) && is_array($row['m_assessment_kary_d']) && count($row['m_assessment_kary_d']) > 0) {
                $assessmentId = $row['m_assessment_kary_d'][0]['m_assessment_kary_id'] ?? null;
            }

            if ($assessmentId) {
                // 1. Ambil Level (aman jika data kosong / tidak memiliki level)
                $levelRecords = \DB::table('m_assessment_kary_d_level')
                    ->leftJoin('m_level_posisi', 'm_assessment_kary_d_level.m_level_posisi_id', '=', 'm_level_posisi.id')
                    ->where('m_assessment_kary_d_level.m_assessment_kary_id', $assessmentId)
                    ->select('m_assessment_kary_d_level.*', 'm_level_posisi.level_name as level_name')
                    ->get();

                $levelNames = $levelRecords->pluck('level_name')->filter()->implode(', ');
                $data['level'] = $levelNames ?: '-';
                $data['m_assessment_kary_d_level'] = json_decode(json_encode($levelRecords), true) ?? [];

                // 2. Ambil Detail & Sub-Detail selalu lengkap dari database
                $details = \DB::table('m_assessment_kary_d')
                    ->where('m_assessment_kary_id', $assessmentId)
                    ->orderBy('id', 'asc')
                    ->get();

                $detailList = [];
                foreach ($details as $det) {
                    $subDetails = \DB::table('m_assessment_kary_sub_d')
                        ->where('m_assessment_kary_d_id', $det->id)
                        ->orderBy('nilai', 'asc')
                        ->get();

                    $detArr = (array) $det;
                    $detArr['m_assessment_kary_sub_d'] = json_decode(json_encode($subDetails), true) ?? [];
                    $detailList[] = $detArr;
                }
                $data['m_assessment_kary_d'] = $detailList;

                // 3. Grouping logic untuk kebutuhan frontend
                if (app()->request->group) {
                    $grouped = m_assessment_kary_d::with(['m_assessment_kary_sub_d' => function($query){
                        $query->orderBy('nilai','asc');
                    }])
                    ->leftJoin('m_general', 'm_assessment_kary_d.kategori', '=', 'm_general.id')
                    ->addSelect('m_assessment_kary_d.*', 'm_general.value as kategori_name')
                    ->where('m_assessment_kary_id', $assessmentId)
                    ->get()
                    ->groupBy('kategori_name');

                    $data['m_assessment_kary_d_group'] = $grouped->map(function ($items, $kategoriName) {
                        return [
                            'name_kategori' => $kategoriName,
                            'data' => $items->values(), 
                        ];
                    })->values();
                }
            } else {
                $data['level'] = '-';
                $data['m_assessment_kary_d_level'] = [];
                $data['m_assessment_kary_d'] = [];
            }

            // Pastikan m_divisi_id dan m_divisi_name selalu tersedia di response
            $divisiId = $row['m_divisi_id'] ?? $row['this.m_divisi_id'] ?? null;
            $data['m_divisi_id'] = $divisiId ? (int)$divisiId : null;

            $divisiName = '';
            if (!empty($divisiId)) {
                $divisi = \App\Models\BasicModels\m_divisi::find($divisiId);
                if ($divisi) {
                    if ($divisi->name) {
                        $genDiv = \App\Models\BasicModels\m_general::find($divisi->name);
                        $divisiName = $genDiv ? ($genDiv->value ?? '') : '';
                    }
                    if (empty($divisiName)) {
                        $divisiName = $divisi->name_old ?? '';
                    }
                }
            }
            $data['m_divisi_name'] = $divisiName ?: '-';

            // Tipe Penilaian (type) untuk landing table & detail
            $typeId = $row['type'] ?? $row['this.type'] ?? null;
            $data['type'] = $typeId ? (int)$typeId : null;
            $typeName = '';
            if ($typeId) {
                $genType = \App\Models\BasicModels\m_general::find($typeId);
                $typeName = $genType ? ($genType->value ?? '') : '';
            }
            $data['type.value'] = $typeName ?: '-';
            $data['type_name'] = $typeName ?: '-';

            return array_merge( $row, $data );
        } catch (\Exception $e) {
            $row['transform_error'] = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            return $row;
        }
    }
}