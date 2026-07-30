<?php

namespace App\Models\CustomModels;

class m_assessment_kary extends \App\Models\BasicModels\m_assessment_kary
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function transformRowData( array $row)
    {
        $data = [];
        if(app()->request->group){
            $grouped = m_assessment_kary_d::with(['m_assessment_kary_sub_d' => function($query){
                $query->orderBy('nilai','asc');
            }])
            ->leftJoin('m_general', 'm_assessment_kary_d.kategori', '=', 'm_general.id')
            ->addSelect('m_assessment_kary_d.*', 'm_general.value as kategori_name')
            ->where('m_assessment_kary_id', $row['id'])
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

        $levelString = m_assessment_kary_d_level::where('m_assessment_kary_id', $row['id'])
                ->with('m_level_posisi')
                ->get()
                ->implode('m_level_posisi.level_name', ', ');
        
        $data['level'] = $levelString;
        
        if(app()->request->divisi_name){
            $divisi_general_id = m_divisi::find($row['m_divisi_id'])?->name;
            $data['m_divisi_name'] = $divisi_general_id ? ( m_general::find($divisi_general_id)?->value ?? '') : '';
        }

        return array_merge( $row, $data );
    }
    
    
}