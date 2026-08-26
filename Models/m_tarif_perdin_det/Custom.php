<?php

namespace App\Models\CustomModels;

class m_tarif_perdin_det extends \App\Models\BasicModels\m_tarif_perdin_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function m_tarif_perdin()
    {
        return $this->belongsTo('App\Models\BasicModels\m_tarif_perdin', 'm_tarif_perdin_id', 'id');
    }
}