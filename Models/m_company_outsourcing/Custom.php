<?php

namespace App\Models\CustomModels;

class m_company_outsourcing extends \App\Models\BasicModels\m_company_outsourcing
{    
    public function __construct()
    {
        parent::__construct();
    }

    protected static function booted()
    {
        static::addGlobalScope('os_tenant', function ($builder) {
            $authUser = auth()->user();
            if ($authUser && !empty($authUser->m_os_id)) {
                $builder->where('m_company_outsourcing.id', $authUser->m_os_id);
            }
        });
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function scoperespo($model)
    {
        $authUser = auth()->user();
        if ($authUser && !empty($authUser->m_os_id)) {
            $model->where('m_company_outsourcing.id', $authUser->m_os_id);
        }
        return $model;
    }

    
}