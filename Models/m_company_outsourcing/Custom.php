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
            if ($authUser) {
                $osId = request('m_os_id') ?: ($authUser->m_os_id ?? null);
                if (!$osId && !empty($authUser->m_kary_id)) {
                    $osId = \DB::table('m_kary')->where('id', $authUser->m_kary_id)->value('m_company_outsourcing_id');
                }
                if (!empty($osId)) {
                    $builder->where('m_company_outsourcing.id', $osId);
                }
            }
        });
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function scoperespo($model)
    {
        $authUser = auth()->user();
        if ($authUser) {
            $osId = request('m_os_id') ?: ($authUser->m_os_id ?? null);
            if (!$osId && !empty($authUser->m_kary_id)) {
                $osId = \DB::table('m_kary')->where('id', $authUser->m_kary_id)->value('m_company_outsourcing_id');
            }
            if (!empty($osId)) {
                $model->where('m_company_outsourcing.id', $osId);
            }
        }
        return $model;
    }
}
