<?php

namespace App\Models\CustomModels;

class m_prog_pelatihan extends \App\Models\BasicModels\m_prog_pelatihan
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public $details = ['m_prog_pelatihan_d_divisi', 'm_prog_pelatihan_d_level'];

    public function m_prog_pelatihan_d_divisi() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_prog_pelatihan_d_divisi', 'm_prog_pelatihan_id', 'id');
    }

    public function m_prog_pelatihan_d_level() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_prog_pelatihan_d_level', 'm_prog_pelatihan_id', 'id');
    }

    public function createAfter($model, $arrayData, $metaData, $id=null)
    {
        $this->updateSasaran($model->id);
        return true;
    }

    public function updateAfter($model, $arrayData, $metaData, $id=null)
    {
        $this->updateSasaran($model->id);
        return true;
    }

    private function updateSasaran($id)
    {
        $model = $this->find($id);
        if($model){
            $levels = \App\Models\BasicModels\m_prog_pelatihan_d_level::where('m_prog_pelatihan_id', $id)
                ->join('m_level_posisi', 'm_level_posisi.id', '=', 'm_prog_pelatihan_d_level.m_level_posisi_id')
                ->pluck('m_level_posisi.level_name')
                ->toArray();
            $model->sasaran = implode(', ', $levels);
            $model->save();
        }
    }
}