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

    public function t_rencana_perdin() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_rencana_perdin', 't_perdin_id', 'id');
    }

    public function t_penyelesaian_perdin() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_penyelesaian_perdin', 't_perdin_id', 'id');
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
        $dateFrom = $this->normalizeDate($arrayData['date_from'] ?? null) ?? Carbon::now()->format('Y-m-d');

        $kary = !empty($arrayData['m_kary_id']) ? \App\Models\BasicModels\m_kary::find($arrayData['m_kary_id']) : null;
        $comp_id = $kary?->m_comp_id ?? auth()->user()?->m_comp_id ?? null;
        $branch_id = $kary?->m_branch_id ?? auth()->user()?->m_branch_id ?? null;

        $compCode = 'TMG';
        if ($comp_id) {
            $comp = \DB::table('m_comp')->where('id', $comp_id)->first();
            $compCode = $comp?->code ?? $comp?->singkatan ?? $comp?->name ?? 'TMG';
        }

        $branchCode = 'SBY';
        if ($branch_id) {
            $branch = \DB::table('m_branch')->where('id', $branch_id)->first();
            $branchCode = $branch?->code ?? $branch?->singkatan ?? $branch?->name ?? 'SBY';
        }

        $replacements = [
            'TMG' => $compCode,
            'SBY' => $branchCode,
            '{comp}' => $compCode,
            '{company}' => $compCode,
            '{branch}' => $branchCode,
            '{cabang}' => $branchCode,
        ];

        $nomor = $this->helper->generateNomor("PERDIN", true, null, $dateFrom, $replacements);

        $newArrayData = array_merge($arrayData, [
            "nomor" => $nomor,
            "tanggal_surat_tugas" => $dateFrom,
            "tanggal_rencana_biaya" => $dateFrom,
        ]);

        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function scopelistPerdin($model)
    {
        $user = auth()->user();
        $user_id = $user->id ?? 0;
        $m_kary_id = $user->m_kary_id ?? \App\Models\BasicModels\m_kary::whereHas('default_users', function($q) use ($user_id){
            $q->where('id', $user_id);
        })->first()?->id;

        if ($user->is_hc || strtolower($user->user_type ?? '') === 'admin') {
            return $model->whereDoesntHave('t_rencana_perdin', function($q){
                $q->whereRaw("upper(status) = 'APPROVED'");
            });
        }

        return $model->whereDoesntHave('t_rencana_perdin', function($q){
            $q->whereRaw("upper(status) = 'APPROVED'");
        })
        ->where('m_kary_id', $m_kary_id);
    }

    public function scopeusedPerdin($model)
    {
        $user = auth()->user();
        $user_id = $user->id ?? 0;
        $m_kary_id = $user->m_kary_id ?? \App\Models\BasicModels\m_kary::whereHas('default_users', function($q) use ($user_id){
            $q->where('id', $user_id);
        })->first()?->id;

        if ($user->is_hc || strtolower($user->user_type ?? '') === 'admin') {
            return $model->whereHas('t_rencana_perdin', function($q){
                $q->whereRaw("upper(status) = 'APPROVED'");
            });
        }

        return $model->whereHas('t_rencana_perdin', function($q){
            $q->whereRaw("upper(status) = 'APPROVED'");
        })->where('m_kary_id', $m_kary_id);
    }

    public function scopelanding($model)
    {
        return $model;
    }
    
    public function scoperincian($model)
    {
        return $model->with(['t_rencana_perdin', 't_penyelesaian_perdin']);
    }
}