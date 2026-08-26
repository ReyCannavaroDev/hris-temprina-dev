<?php

namespace App\Models\CustomModels;

class t_penyelesaian_perdin_det extends \App\Models\BasicModels\t_penyelesaian_perdin_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function t_penyelesaian_perdin()
    {
        return $this->belongsTo('App\Models\BasicModels\t_penyelesaian_perdin', 't_penyelesaian_perdin_id', 'id');
    }
}