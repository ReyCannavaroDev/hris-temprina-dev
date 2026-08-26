<?php

namespace App\Models\CustomModels;

class t_rencana_perdin_det extends \App\Models\BasicModels\t_rencana_perdin_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function t_rencana_perdin()
    {
        return $this->belongsTo('App\Models\BasicModels\t_rencana_perdin', 't_rencana_perdin_id', 'id');
    }
}