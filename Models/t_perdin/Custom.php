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

    private function getCompanyAndKotaCode($karyId) : array
    {
        $kary = !empty($karyId) ? \DB::table('m_kary')->where('id', $karyId)->first() : null;

        // 1. Ambil Company dari tabel m_company
        $company = null;
        $companyId = null;

        if ($kary && !empty($kary->m_company_id)) {
            $companyId = $kary->m_company_id;
        }

        if (!$companyId && $kary) {
            $jabatan = \DB::table('m_kary_det_jabatan')
                ->where('m_kary_id', $kary->id)
                ->where('is_primary', true)
                ->first();
            $companyId = $jabatan?->m_company_id ?? null;
        }

        if (!$companyId && $kary && !empty($kary->m_subcomp_id)) {
            $subcomp = \DB::table('m_subcomp')->where('id', $kary->m_subcomp_id)->first();
            $companyId = $subcomp?->m_company_id ?? $subcomp?->company_id ?? null;
        }

        if ($companyId) {
            $company = \DB::table('m_company')->where('id', $companyId)->first();
        }

        if (!$company && $kary && !empty($kary->m_comp_id)) {
            $company = \DB::table('m_company')->where('id', $kary->m_comp_id)->first();
        }

        if (!$company && auth()->check()) {
            $user = auth()->user();
            if (!empty($user->m_company_id)) {
                $company = \DB::table('m_company')->where('id', $user->m_company_id)->first();
            }
        }

        // Kode Company (contoh: TMG, TSM, TWL, ALNG, JPM, DCI, NGMS, DCP, DIM, IPBOOK)
        $compCode = $company?->kode ?? $company?->code ?? 'TMG';

        // 2. Ambil Singkatan Kota (Prioritas Opsi A: Mengikuti Kota dari m_company)
        $candidates = [];

        // a. Prioritas 1: Ambil dari tabel m_company (kolom kota / city / city_id)
        if ($company) {
            if (!empty($company->kota)) $candidates[] = $company->kota;
            if (!empty($company->city)) $candidates[] = $company->city;
            if (!empty($company->city_id)) {
                $c = \DB::table('m_general')->where('id', $company->city_id)->value('value');
                if ($c) $candidates[] = $c;
            }
            if (!empty($company->kota_id)) {
                $c = \DB::table('m_general')->where('id', $company->kota_id)->value('value');
                if ($c) $candidates[] = $c;
            }
        }

        // b. Prioritas 2 (Fallback jika m_company tidak punya kota): Ambil dari m_branch / m_kary
        if (empty($candidates)) {
            $branch = null;
            if ($kary && !empty($kary->m_branch_id)) {
                $branch = \DB::table('m_branch')->where('id', $kary->m_branch_id)->first();
            }

            if ($branch) {
                if (!empty($branch->city_id)) {
                    $c = \DB::table('m_general')->where('id', $branch->city_id)->value('value');
                    if ($c) $candidates[] = $c;
                }
                if (!empty($branch->kota_id)) {
                    $c = \DB::table('m_general')->where('id', $branch->kota_id)->value('value');
                    if ($c) $candidates[] = $c;
                }
                if (!empty($branch->city)) $candidates[] = $branch->city;
                if (!empty($branch->kota)) $candidates[] = $branch->kota;
                if (!empty($branch->name) && !in_array(strtoupper($branch->name), ['HOLDING MP', 'HOLDING', 'PUSAT'])) {
                    $candidates[] = $branch->name;
                }
                if (!empty($branch->code) && !in_array(strtoupper($branch->code), ['HLD', 'HO', 'PST'])) {
                    $candidates[] = $branch->code;
                }
            }

            if ($kary && !empty($kary->kota_id)) {
                $c = \DB::table('m_general')->where('id', $kary->kota_id)->value('value');
                if ($c) $candidates[] = $c;
            }
        }

        // c. Cek apakah ada record di m_general yang memiliki kolom `code`
        foreach ($candidates as $cand) {
            $cleanName = strtoupper(trim((string)$cand));
            $cleanName = preg_replace('/^(KOTA|KABUPATEN|KAB\.?)\s+/i', '', $cleanName);
            $cleanName = trim($cleanName);

            $genFound = \DB::table('m_general')
                ->where(function($q) use ($cleanName, $cand){
                    $q->whereRaw('upper(value) = ?', [$cleanName])
                      ->orWhereRaw('upper(value) = ?', [strtoupper(trim($cand))]);
                })
                ->whereNotNull('code')
                ->where('code', '!=', '')
                ->first();

            if ($genFound && !empty($genFound->code) && strlen(trim($genFound->code)) <= 5) {
                return [
                    'company' => $compCode,
                    'kota'    => strtoupper(trim($genFound->code)),
                ];
            }
        }

        // d. Kamus singkatan kota standar Indonesia
        $cityMap = [
            'SURABAYA'    => 'SBY',
            'SIDOARJO'    => 'SDA',
            'MALANG'      => 'MLG',
            'JEMBER'      => 'JBR',
            'TANGERANG'   => 'TNG',
            'SURAKARTA'   => 'SKT',
            'SOLO'        => 'SLO',
            'NGANJUK'     => 'NGK',
            'GRESIK'      => 'GSK',
            'SEMARANG'    => 'SMG',
            'DENPASAR'    => 'DPS',
            'BALI'        => 'DPS',
            'JAKARTA'     => 'JKT',
            'BEKASI'      => 'BKS',
            'BANDUNG'     => 'BDG',
            'BANYUWANGI'  => 'BWI',
            'PROBOLINGGO' => 'PBG',
            'PASURUAN'    => 'PSR',
            'KEDIRI'      => 'KDR',
            'MADIUN'      => 'MDN',
            'YOGYAKARTA'  => 'YOG',
            'JOGJA'       => 'JOG',
            'KLATEN'      => 'KLT',
            'BOGOR'       => 'BGR',
            'DEPOK'       => 'DPK',
        ];

        $kotaCode = null;
        foreach ($candidates as $cand) {
            $clean = strtoupper(trim((string)$cand));
            $clean = preg_replace('/^(KOTA|KABUPATEN|KAB\.?)\s+/i', '', $clean);
            $clean = trim($clean);

            if (isset($cityMap[$clean])) {
                $kotaCode = $cityMap[$clean];
                break;
            }

            foreach ($cityMap as $cityName => $abbr) {
                if (str_contains($clean, $cityName)) {
                    $kotaCode = $abbr;
                    break 2;
                }
            }

            if (strlen($clean) >= 2 && strlen($clean) <= 4 && !in_array($clean, ['HLD', 'HO', 'PST'])) {
                $kotaCode = $clean;
                break;
            }
        }

        // e. Fallback jika kota baru di luar kamus
        if (!$kotaCode && !empty($candidates)) {
            $firstCand = strtoupper(trim($candidates[0]));
            $firstCand = preg_replace('/^(KOTA|KABUPATEN|KAB\.?)\s+/i', '', $firstCand);
            $firstCand = preg_replace('/[^A-Z]/', '', $firstCand);
            $kotaCode = substr($firstCand, 0, 3) ?: 'SBY';
        }

        return [
            'company' => $compCode,
            'kota'    => $kotaCode ?: 'SBY',
        ];
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $dateFrom = $this->normalizeDate($arrayData['date_from'] ?? null) ?? Carbon::now()->format('Y-m-d');

        $resolved = $this->getCompanyAndKotaCode($arrayData['m_kary_id'] ?? null);
        $compCode = $resolved['company'];
        $kotaCode = $resolved['kota'];

        $replacements = [
            'TMG' => $compCode,
            'SBY' => $kotaCode,
            '{comp}' => $compCode,
            '{company}' => $compCode,
            '{kota}' => $kotaCode,
            '{city}' => $kotaCode,
            '{branch}' => $kotaCode,
            '{cabang}' => $kotaCode,
        ];

        $nomor = $this->helper->generateNomor("PERDIN", true, null, $dateFrom, $replacements, 'daily');

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

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $existing = $id ? \DB::table('t_perdin')->where('id', $id)->first() : null;

        $dateFrom = $this->normalizeDate($arrayData['date_from'] ?? null) 
            ?? $this->normalizeDate($existing?->date_from ?? null) 
            ?? Carbon::now()->format('Y-m-d');

        $karyId = $arrayData['m_kary_id'] ?? $existing?->m_kary_id ?? null;

        $resolved = $this->getCompanyAndKotaCode($karyId);
        $compCode = $resolved['company'];
        $kotaCode = $resolved['kota'];

        $replacements = [
            'TMG' => $compCode,
            'SBY' => $kotaCode,
            '{comp}' => $compCode,
            '{company}' => $compCode,
            '{kota}' => $kotaCode,
            '{city}' => $kotaCode,
            '{branch}' => $kotaCode,
            '{cabang}' => $kotaCode,
        ];

        // Jika nomor sudah terbit dan tanggal surat tugas tidak diubah, pertahankan nomor yang sudah ada
        $existingDate = $this->normalizeDate($existing?->date_from ?? null);
        if ($existing && !empty($existing->nomor) && ($existingDate === $dateFrom || empty($arrayData['date_from']))) {
            $nomor = $existing->nomor;
        } else {
            // Generate nomor yang merefleksikan tanggal dan company/kota baru dengan reset harian
            $nomor = $this->helper->generateNomor("PERDIN", false, null, $dateFrom, $replacements, 'daily');
        }

        $newArrayData = array_merge($arrayData, [
            "nomor" => $nomor,
            "tanggal_surat_tugas" => $dateFrom,
            "tanggal_rencana_biaya" => $dateFrom,
        ]);

        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateAfter( $model, $arrayData, $metaData, $id=null )
    {
        $targetId = $id ?? $model->id ?? null;
        if ($targetId) {
            $current = \DB::table('t_perdin')->where('id', $targetId)->first();
            if ($current && !empty($current->nomor)) {
                \DB::table('t_rencana_perdin')
                    ->where('t_perdin_id', $targetId)
                    ->update(['nomor' => $current->nomor]);

                \DB::table('t_penyelesaian_perdin')
                    ->where('t_perdin_id', $targetId)
                    ->update(['nomor' => $current->nomor]);
            }
        }

        return [
            "model" => $model,
            "data"  => $arrayData,
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

        $query = $model->whereHas('t_rencana_perdin', function($q){
            $q->whereRaw("upper(status) NOT IN ('DRAFT','REJECTED')");
        })->whereDoesntHave('t_penyelesaian_perdin', function($q){
            $q->whereRaw("upper(status) != 'REJECTED'");
        });

        if ($user->is_hc || strtolower($user->user_type ?? '') === 'admin') {
            return $query;
        }

        return $query->where('m_kary_id', $m_kary_id);
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