<?php

namespace App\Models\CustomModels;

class m_standart_gaji_harian extends \App\Models\BasicModels\m_standart_gaji_harian
{    
    private $helper;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    // Paksa hilangkan filter otomatis dari core system agar data tampil
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('bypass_security', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $query = $builder->getQuery();
            if (isset($query->wheres)) {
                foreach ($query->wheres as $key => $where) {
                    $targetStr = isset($where['column']) ? $where['column'] : (isset($where['sql']) ? $where['sql'] : '');
                    if (strpos($targetStr, 'm_kary.m_subcomp_id') !== false || strpos($targetStr, 'm_kary.m_branch_id') !== false) {
                        unset($query->wheres[$key]);
                    }
                }
                $query->wheres = array_values($query->wheres);
            }
        });
    }

    // INI DIA KUNCI FIX-NYA: Join tabel m_kary agar scopeharian dan scopeos tidak error 500!
    public $joins = [
        "m_kary.id=m_standart_gaji_harian.m_kary_id"
    ];

    public $fileColumns = [
        /*file_column*/
    ];
    
    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $chosenKaryId = req('m_kary_id');
        $rawInput = json_decode(app()->request->getContent(), true);
        if (!empty($rawInput['m_kary_id'])) {
            $chosenKaryId = $rawInput['m_kary_id'];
        }
        if (!empty($chosenKaryId)) {
            $arrayData['m_kary_id'] = $chosenKaryId;
        }

        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "kode" => app()->request->kode ?? $this->helper->generateNomor("KODE STANDART GAJI"),
            ]),
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $chosenKaryId = req('m_kary_id');
        $rawInput = json_decode(app()->request->getContent(), true);
        if (!empty($rawInput['m_kary_id'])) {
            $chosenKaryId = $rawInput['m_kary_id'];
        }
        if (!empty($chosenKaryId)) {
            $arrayData['m_kary_id'] = $chosenKaryId;
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function transformRowData( array $row )
    {
        $newData = [
            "gaji_pokok_formatted" => "Rp " . number_format($row['gaji_pokok'], 0, ',', '.'),
        ];

        return array_merge( $row, $newData );
    }

    public function scopeharian($model)
    {
        $statusHarian = \DB::table('m_general')
            ->where('group', 'STATUS KARYAWAN')
            ->where(function ($q) {
                $q->where('value', 'HARIAN')
                  ->orWhereRaw("UPPER(value) LIKE '%HARIAN%'");
            })
            ->pluck('id')
            ->toArray();

        $statusOs = \DB::table('m_general')
            ->where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->orWhereRaw("UPPER(value) LIKE '%OUTSOURCE%'")
            ->pluck('id')
            ->toArray();

        return $model
            ->whereNull('m_kary.m_company_outsourcing_id')
            ->when(!empty($statusOs), function ($q) use ($statusOs) {
                $q->whereNotIn('m_kary.status_kary_id', $statusOs);
            })
            ->when(!empty($statusHarian), function ($q) use ($statusHarian) {
                $q->whereIn('m_kary.status_kary_id', $statusHarian);
            });
    }

    public function scopeos($model)
    {
        $statusOs = \DB::table('m_general')
            ->where(function ($q) {
                $q->where('group', 'STATUS KARYAWAN OUTSOURCE')
                  ->orWhereRaw("UPPER(value) LIKE '%OUTSOURCE%'");
            })
            ->pluck('id')
            ->toArray();

        $authUser = auth()->user();
        $osId = request('m_os_id') ?: ($authUser->m_os_id ?? null);
        if (!$osId && $authUser && !empty($authUser->m_kary_id)) {
            $osId = \DB::table('m_kary')->where('id', $authUser->m_kary_id)->value('m_company_outsourcing_id');
        }

        return $model
            ->where(function ($q) use ($statusOs) {
                $q->whereNotNull('m_kary.m_company_outsourcing_id');
                if (!empty($statusOs)) {
                    $q->orWhereIn('m_kary.status_kary_id', $statusOs);
                }
            })
            ->when(!empty($osId), function ($q) use ($osId) {
                $q->where('m_kary.m_company_outsourcing_id', $osId);
            });
    }
}
