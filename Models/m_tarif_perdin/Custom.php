<?php

namespace App\Models\CustomModels;

class m_tarif_perdin extends \App\Models\BasicModels\m_tarif_perdin
{
    private $helper;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->helper = getCore("Helper");
        if (app()->request->isMethod('GET')) {
            $this->details = [];
        }
    }

    public $fileColumns = [
        /*file_column*/
    ];
    public $details = ["m_tarif_perdin_det"];

    public function m_tarif_perdin_det()
    {
        return $this->hasMany(\App\Models\CustomModels\m_tarif_perdin_det::class, 'm_tarif_perdin_id', 'id');
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "kode" => $this->helper->generateNomor("KODE TARIF PERDIN"),
        ]);

        $req = app()->request;
        $details = [];
        if (!empty($req->m_tarif_perdin_det) && is_array($req->m_tarif_perdin_det)) {
            $details[] = 'm_tarif_perdin_det';
        }
        $this->details = $details;

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $req = app()->request;
        $details = [];
        if (!empty($req->m_tarif_perdin_det) && is_array($req->m_tarif_perdin_det)) {
            $details[] = 'm_tarif_perdin_det';
        } else {
            \DB::table('m_tarif_perdin_det')->where('m_tarif_perdin_id', $id)->delete();
        }
        $this->details = $details;

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if ($id && (!isset($row['m_tarif_perdin_det']) || empty($row['m_tarif_perdin_det']))) {
            $details = \DB::table('m_tarif_perdin_det')
                ->where('m_tarif_perdin_id', $id)
                ->get();
            $row['m_tarif_perdin_det'] = json_decode(json_encode($details), true) ?? [];
        }
        return $row;
    }

    public function scopeWithDetail($model)
    {
        return $model->with(["m_tarif_perdin_det"]);
    }

    public function scopelevel($model)
    {
        $m_posisi_id = app()->request->t_m_posisi_id;
       
        if (!$m_posisi_id) {
            return $model; 
        }

        $level_id = m_level_posisi::whereHas('m_level_posisi_d', function($q) use ($m_posisi_id){
            $q->where('m_posisi_id', $m_posisi_id);
        })->first()?->id;

        if (!$level_id) {
            return $model;
        }

        return $model->where('m_level_posisi_id', $level_id);
    }
}