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
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public $details = ['m_assessment_kary_d', 'm_assessment_kary_d_level'];

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
        if(empty($req->m_assessment_kary_d_level)){
            $this->details = ['m_assessment_kary_d'];
        } else {
            $this->details = ['m_assessment_kary_d', 'm_assessment_kary_d_level'];
        }
        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $req = app()->request;
        if(empty($req->m_assessment_kary_d_level)){
            \App\Models\BasicModels\m_assessment_kary_d_level::where('m_assessment_kary_id', $id)->delete();
            $this->details = ['m_assessment_kary_d'];
        } else {
            $this->details = ['m_assessment_kary_d', 'm_assessment_kary_d_level'];
        }
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
            if (isset($row['m_assessment_kary_d']) && is_array($row['m_assessment_kary_d']) && count($row['m_assessment_kary_d']) > 0) {
                $assessmentId = $row['m_assessment_kary_d'][0]['m_assessment_kary_id'];
            }

            if(app()->request->group){
                $grouped = m_assessment_kary_d::with(['m_assessment_kary_sub_d' => function($query){
                    $query->orderBy('nilai','asc');
                }])
                ->leftJoin('m_general', 'm_assessment_kary_d.kategori', '=', 'm_general.id')
                ->addSelect('m_assessment_kary_d.*', 'm_general.value as kategori_name')
                ->where('m_assessment_kary_id', $assessmentId)
                ->get()
                ->groupBy('kategori_name');

                $data = [
                    'm_assessment_kary_d_group' => $grouped->map(function ($items, $kategoriName) {
                        return [
                            'name_kategori' => $kategoriName,
                            'data' => $items->values(), 
                        ];
                    })->values(),
                ];
            }

            $levelNames = '';
            if ($assessmentId) {
                $levelNamesResult = \DB::table('m_assessment_kary_d_level')
                    ->join('m_level_posisi', 'm_assessment_kary_d_level.m_level_posisi_id', '=', 'm_level_posisi.id')
                    ->where('m_assessment_kary_d_level.m_assessment_kary_id', $assessmentId)
                    ->pluck('m_level_posisi.level_name');
                
                if ($levelNamesResult instanceof \Illuminate\Support\Collection) {
                    $levelNames = $levelNamesResult->filter()->implode(', ');
                } else {
                    $levelNames = implode(', ', array_filter((array)$levelNamesResult));
                }
            }

            $data['level'] = $levelNames;
            
            if(app()->request->divisi_name){
                $divisi_general_id = m_divisi::find($row['m_divisi_id'])?->name;
                $data['m_divisi_name'] = $divisi_general_id ? ( m_general::find($divisi_general_id)?->value ?? '') : '';
            }

            return array_merge( $row, $data );
        } catch (\Exception $e) {
            $row['transform_error'] = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            return $row;
        }
    }
    
    
}