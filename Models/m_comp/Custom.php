<?php

namespace App\Models\CustomModels;

class m_comp extends \App\Models\BasicModels\m_comp
{    
    private $helper;
    public function __construct()
    {
        $this->helper = getCore('Helper');
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $isDuplicate = $this->isDuplicate($arrayData['name'],$id=null);
        if($isDuplicate){
            return ["errors" => ['Nama SBU '.$arrayData['name']. ' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[
            'code' => $this->helper->generateNomor('KODE UNIT BISNIS')
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
    private function isDuplicate($data, $id){
        $query = $this->whereRaw('LOWER(name) = ?', [strtolower($data)]);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        $checkData = $query->first();
        return $checkData ? true : false;
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $isDuplicate = $this->isDuplicate($arrayData['name'], $id);
        if($isDuplicate){
            return ["errors" => ['Nama SBU '.$arrayData['name']. ' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function subcomp() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_subcomp', 'm_comp_id', 'id');
    }

    public function custom_structure_company($req){
        $sbuId = $req->sbu_id ?? 9;
        $getData = $this->where('id',$sbuId)->with('subcomp')->get();
        $getData = $getData->transform(function ($item){
            $item['level'] = 1;
            if($item->relationLoaded('subcomp')){
                $item['subcomp'] = $item['subcomp']->map(function ($subcompItem){
                    $subcompItem['level'] = 2;
                    $subcompItem['branch'] = m_branch::where('m_subcomp_id',$subcompItem['id'])->get()->map(function ($branchItem){

                        $branchItem['level'] = 3;
                        $branchItem['divisi'] = m_divisi::where('m_branch_id', $branchItem['id'])->get()->map(function($divisiItem){
                            return [
                                'id' => $divisiItem['id'],
                                'level' => $divisiItem['level'] + 4,
                                'name' => $divisiItem['name']
                            ];
                        });
                        return [
                            'id' => $branchItem['id'],
                            'level' => 3,
                            'name' => $branchItem['name'],
                            'divisi' => $branchItem['divisi'],
                        ];
                    });
                    return [
                        'id' => $subcompItem['id'],
                        'level' => 2,
                        'name' => $subcompItem['name'],
                        'branch' => $subcompItem['branch'],
                    ];
                });
            }
            return [
                'id' => $item['id'],
                'level' => 1,
                'name' => $item['name'],
                'subcomp' => $item['subcomp'],
            ];
        });
        return $getData;
    }

    public function custom_structure($req)
    {
        $comp_id = $req->sbu_id ?? 9;

        $structure = m_subcomp::with([
            'm_branch' => function($q) {
                $q->select('id', 'm_subcomp_id', 'name'); 
                $q->with(['m_divisi' => function($q2) {
                    // Ambil yang paling atas dulu (Parent)
                    $q2->whereNull('parent_id') 
                        ->select('id', 'm_branch_id', 'name', 'parent_id')
                        ->with([
                            'general_name:id,value', 
                            // Ambil anaknya, dan ambil bapak dari anaknya (untuk label name)
                            'child_divisi' => function($q3) {
                                $q3->with(['name:id,value']);
                            }
                        ]);
                }]);
            }
        ])
        ->where('m_comp_id', $comp_id)
        ->get(['id', 'm_comp_id', 'name']);

        $structure->each(function ($subcomp) {
            $subcomp->m_branch?->each(function ($branch) {
                $branch->m_divisi?->transform(function ($divisi) {
                    return $this->formatDivisi($divisi);
                });
            });
        });

        return response()->json(['status' => 'success', 'data' => $structure], 200);
    }

    // Fungsi pembantu agar rekursif namanya juga berubah
    private function formatDivisi($divisi) {
        $gen = $divisi->general_name;
        $divisi->divisi_name = $gen->value ?? '-';
        
        // Jika punya anak (sub-divisi), proses juga namanya
        if ($divisi->child_divisi) {
            $divisi->child_divisi->transform(function ($child) {
                return $this->formatDivisi($child);
            });
        }
        
        unset($divisi->general_name); // Bersihkan agar tidak double di JSON
        return $divisi;
    }
    
}