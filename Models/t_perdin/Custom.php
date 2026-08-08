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
        $this->joins = array_values(array_filter($this->joins, function ($join) {
            return $join !== "m_kary.id=t_perdin.m_atasan_id";
        }));
        $this->joins[] = "m_kary.id=t_perdin.m_kary_id";
        $this->heirs = array_values(array_unique(array_merge($this->heirs, [
            "m_kary",
        ])));
        
        $newCols = ["tanggal_surat_tugas", "tanggal_rencana_biaya"];
        $this->fillable = array_merge($this->fillable, $newCols);
        $this->columns = array_merge($this->columns, $newCols);
        $this->columnsFull = array_merge($this->columnsFull, ["tanggal_surat_tugas:date", "tanggal_rencana_biaya:date"]);
        $this->createable = array_merge($this->createable, $newCols);
        $this->updateable = array_merge($this->updateable, $newCols);
        $this->searchable = array_merge($this->searchable, $newCols);
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];

    public function t_rencana_perdin()
    {
        return $this->hasMany(t_rencana_perdin::class, 't_perdin_id', 'id');
    }

    public function t_penyelesaian_perdin()
    {
        return $this->hasMany(t_penyelesaian_perdin::class, 't_perdin_id', 'id');
    }

    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }

    private function normalizeDate(?string $value) : ?string
    {
        if (!$value) {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getHariCode(string $date) : string
    {
        $map = [
            0 => 'MG',
            1 => 'SN',
            2 => 'SL',
            3 => 'RB',
            4 => 'KM',
            5 => 'JM',
            6 => 'SB',
        ];

        return $map[(int) Carbon::parse($date)->dayOfWeek] ?? 'SN';
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("PERDIN", true, null, $arrayData['date_from'] ?? null),
            "tanggal_surat_tugas" => $arrayData['date_from'] ?? null,
            "tanggal_rencana_biaya" => $arrayData['date_from'] ?? null,
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
        return $model->with(['t_rencana_perdin', 't_penyelesaian_perdin']);
    }
}