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

    public $details = ['m_prog_pelatihan_d_level'];

    public function m_prog_pelatihan_d_divisi() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_prog_pelatihan_d_divisi', 'm_prog_pelatihan_id', 'id');
    }

    public function m_prog_pelatihan_d_level() :\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\m_prog_pelatihan_d_level', 'm_prog_pelatihan_id', 'id');
    }

    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        $levelData = $arrayData['m_prog_pelatihan_d_level'] ?? (app()->request->input('m_prog_pelatihan_d_level') ?? []);
        if(empty($levelData)){
            $this->details = [];
        }
        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $levelData = $arrayData['m_prog_pelatihan_d_level'] ?? (app()->request->input('m_prog_pelatihan_d_level') ?? []);
        if(empty($levelData)){
            \App\Models\BasicModels\m_prog_pelatihan_d_level::where('m_prog_pelatihan_id', $id)->delete();
            $this->details = [];
        }
        return [
            "model"  => $model,
            "data"   => $arrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id=null)
    {
        $realId = is_object($model) ? $model->id : $id;
        $req = app()->request;

        $levelData = $req->input('m_prog_pelatihan_d_level', $req->m_prog_pelatihan_d_level ?? []);
        if (empty($levelData) && isset($arrayData['m_prog_pelatihan_d_level'])) {
            $levelData = $arrayData['m_prog_pelatihan_d_level'];
        }
        
        $levelIds = collect($levelData)
            ->pluck('m_level_posisi_id')
            ->filter()
            ->toArray();

        if (!empty($levelIds)) {
            $prog = $this->find($realId);
            if ($prog) {
                $levels = \App\Models\BasicModels\m_level_posisi::whereIn('id', $levelIds)
                    ->pluck('level_name')
                    ->toArray();
                $prog->sasaran = implode(', ', $levels);
                $prog->save();
            }
        } else {
            // Fallback: coba dari tabel detail (untuk kasus lain)
            $this->updateSasaran($realId);
        }

        return true;
    }

    public function updateAfter($model, $arrayData, $metaData, $id=null)
    {
        $realId = is_object($model) ? $model->id : $id;
        $req = app()->request;
        
        $levelData = $req->input('m_prog_pelatihan_d_level', $req->m_prog_pelatihan_d_level ?? []);
        if (empty($levelData) && isset($arrayData['m_prog_pelatihan_d_level'])) {
            $levelData = $arrayData['m_prog_pelatihan_d_level'];
        }

        $levelIds = collect($levelData)
            ->pluck('m_level_posisi_id')
            ->filter()
            ->toArray();

        if (!empty($levelIds)) {
            $prog = $this->find($realId);
            if ($prog) {
                $levels = \App\Models\BasicModels\m_level_posisi::whereIn('id', $levelIds)
                    ->pluck('level_name')
                    ->toArray();
                $prog->sasaran = implode(', ', $levels);
                $prog->save();
            }
        } else {
            $this->updateSasaran($realId);
        }
        
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