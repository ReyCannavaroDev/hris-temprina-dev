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

        $details = [];
        if (!empty($req->m_assessment_kary_d)) {
            $details[] = 'm_assessment_kary_d';
        }
        if (!empty($req->m_assessment_kary_d_level)) {
            $details[] = 'm_assessment_kary_d_level';
        }
        $this->details = $details;

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

        $details = [];
        if (!empty($req->m_assessment_kary_d)) {
            $details[] = 'm_assessment_kary_d';
        }
        if (empty($req->m_assessment_kary_d_level)) {
            \App\Models\BasicModels\m_assessment_kary_d_level::where('m_assessment_kary_id', $id)->delete();
        } else {
            $details[] = 'm_assessment_kary_d_level';
        }
        $this->details = $details;

        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
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

                // 2. Ambil Detail & Sub-Detail jika belum ada di $row
                if (!isset($row['m_assessment_kary_d']) || empty($row['m_assessment_kary_d'])) {
                    $details = \DB::table('m_assessment_kary_d')
                        ->where('m_assessment_kary_id', $assessmentId)
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
                }

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
                        $divisiName = \App\Models\BasicModels\m_general::find($divisi->name)?->value ?? '';
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
                $typeName = \App\Models\BasicModels\m_general::find($typeId)?->value ?? '';
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