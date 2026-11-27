<?php

namespace App\Models\CustomModels;

class presensi_lokasi extends \App\Models\BasicModels\presensi_lokasi
{    
    public function __construct()
    {
        parent::__construct();
        // presensi_lokasi menyimpan referensi cabang di comp_id.
        // Hapus join m_comp default (yang berisi nama unit bisnis: PERKOM, PUBLISHING, dll)
        // dan arahkan relasi ke m_branch agar menampilkan nama cabang sebenarnya (Temprina Perkom Sumengko, dll).
        $this->joins = array_values(array_filter($this->joins, function ($j) {
            return $j !== "m_comp.id=presensi_lokasi.comp_id" && $j !== "m_branch.id=presensi_lokasi.branch_id";
        }));
        $this->joins[] = "m_branch.id=presensi_lokasi.comp_id";
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $fillable   = ["comp_id","default_user_id","nama","lat","long","is_active","creator_id","last_editor_id","branch_id","ip_address"];
    public $columns    = ["id","comp_id","default_user_id","nama","lat","long","is_active","creator_id","last_editor_id","created_at","updated_at","branch_id","ip_address"];
    public $columnsFull= ["id:bigint","comp_id:bigint","default_user_id:bigint","nama:string:191","lat:float","long:float","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","branch_id:bigint","ip_address:text"];
    public $createable = ["comp_id","default_user_id","nama","lat","long","is_active","creator_id","last_editor_id","branch_id","ip_address"];
    public $updateable = ["comp_id","default_user_id","nama","lat","long","is_active","creator_id","last_editor_id","branch_id","ip_address"];
    public $searchable = ["id","comp_id","default_user_id","nama","lat","long","is_active","creator_id","last_editor_id","created_at","updated_at","branch_id","ip_address"];
    
    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        if (!empty($arrayData['comp_id']) && empty($arrayData['branch_id'])) {
            $arrayData['branch_id'] = $arrayData['comp_id'];
        }
        if (!empty($arrayData['branch_id']) && empty($arrayData['comp_id'])) {
            $arrayData['comp_id'] = $arrayData['branch_id'];
        }
        return ["model" => $model, "data" => $arrayData];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        if (!empty($arrayData['comp_id']) && empty($arrayData['branch_id'])) {
            $arrayData['branch_id'] = $arrayData['comp_id'];
        }
        if (!empty($arrayData['branch_id']) && empty($arrayData['comp_id'])) {
            $arrayData['comp_id'] = $arrayData['branch_id'];
        }
        return ["model" => $model, "data" => $arrayData];
    }
    
}