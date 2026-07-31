<?php

namespace App\Models\CustomModels;
use Carbon;

class t_perdin extends \App\Models\BasicModels\t_perdin
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->heirs = array_values(array_unique(array_merge($this->heirs, [
            "m_kary",
        ])));
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];

    public function t_rencana_perdin()
    {
        return $this->hasMany(t_rencana_perdin::class, 't_perdin_id', 'id');
    }

    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $formattedDate = isset($arrayData['date_from']) 
        ? Carbon::createFromFormat('d/m/Y', $arrayData['date_from'])->format('Y-m-d') 
        : null;
    
        $newArrayData = array_merge($arrayData, [
            "nomor" => !empty($arrayData['nomor']) 
                        ? $arrayData['nomor'] 
                        : $this->helper->generateNomor("KODE RINCIAN PERDIN", true, null, $formattedDate),
        ]);

        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function scopelistPerdin($model)
    {
        $user_id = auth()->user()->id;
        $m_kary_id = m_kary::whereHas('default_users', function($q) use ($user_id){
            $q->where('id', $user_id);
        })->first()?->id;

        return $model->whereDoesntHave('t_rencana_perdin', function($q){
            $q->where('status', 'APPROVED');
        })
        ->where('m_kary_id', $m_kary_id);
    }

    public function scopeusedPerdin($model)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $m_kary_id = m_kary::whereHas('default_users', function($q) use ($user_id){
            $q->where('id', $user_id);
        })->first()?->id;

        if($user->is_hc)
        {
            return $model->whereHas('t_rencana_perdin', function($q){
                $q->where('status', 'APPROVED');
            });
        }

        return $model->whereHas('t_rencana_perdin', function($q){
            $q->where('status', 'APPROVED');
        })->where('m_kary_id', $m_kary_id);
    }

    public function scopelanding($model)
    {
        $m_branch_id = request('m_branch_id');
        $m_subcomp_id = request('m_subcomp_id');

        return $model->whereHas('m_kary', function ($q) use ($m_branch_id, $m_subcomp_id) {
            $q->when($m_branch_id, function ($q) use ($m_branch_id) {
                $q->where('m_branch_id', $m_branch_id);
            })->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                $q->where('m_subcomp_id', $m_subcomp_id);
            });
        });
    }
    
    public function scoperincian($model)
    {
        return $model->with('t_rencana_perdin');
    }
}
