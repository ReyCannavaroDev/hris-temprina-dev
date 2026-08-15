<?php

namespace App\Models\CustomModels;

class m_tarif_perdin extends \App\Models\BasicModels\m_tarif_perdin
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        /*file_column*/
    ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "kode" => $this->helper->generateNomor("KODE TARIF PERDIN"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    // public function scopedetail()
    // {
    //     $tarif_id = app()->request->tarif_id;
    //     return m_tarif_perdin_det::where("m_tarif_perdin_id", $tarif_id);
    // }

    public function scopeWithDetail($model)
    {
        $model->with(["m_tarif_perdin_det"]);
    }

    public function m_tarif_perdin_det()
    {
        return $this->hasMany(\App\Models\CustomModels\m_tarif_perdin_det::class, 'm_tarif_perdin_id', 'id');
    }

    public function scopelevel($model)
    {
        $m_posisi_id = app()->request->t_m_posisi_id;
       
        if (!$m_posisi_id) {
            return $model; 
        }

        $level_id = m_level_posisi::whereHas('m_level_posisi_d', function($q) use ($m_posisi_id){
            $q->where('m_posisi_id', $m_posisi_id);
        })->first()->id;

        return $model->where('m_level_posisi_id', $level_id);
    }
}