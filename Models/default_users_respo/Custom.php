<?php

namespace App\Models\CustomModels;

class default_users_respo extends \App\Models\BasicModels\default_users_respo
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function scopeWithRespoName($model)
    {
        $id = request('id');
        $model
            ->join('m_comp', 'm_comp.id', 'm_respo.m_comp_id')
            ->join('m_subcomp', 'm_subcomp.id', 'm_respo.m_subcomp_id')
            ->join('m_branch', 'm_branch.id', 'm_respo.m_branch_id')
            ->select('default_users_respo.*', 'm_respo.m_comp_id as m_comp_id', 'm_respo.m_subcomp_id as m_subcomp_id', 'm_respo.m_branch_id as m_branch_id', 'm_comp.name as comp_name', 'm_subcomp.name as subcomp_name', 'm_branch.name as branch_name')
            ->where('default_users_respo.default_users_id', $id);
        
        return $model;
    }
}