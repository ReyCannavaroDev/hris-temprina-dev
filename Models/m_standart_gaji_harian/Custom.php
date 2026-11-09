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
        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "kode" => app()->request->kode ?? $this->helper->generateNomor("KODE STANDART GAJI"),
            ]),
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
        // Bypass filter agar data testing bisa tampil
        // $id = m_general::where('group', 'STATUS KARYAWAN')->where('value', 'HARIAN')->pluck('id')->toArray();
        // return $model->whereIn('m_kary.status_kary_id', $id);
        return $model;
    }

    public function scopeos($model)
    {
        // Bypass filter agar data testing bisa tampil
        // $id = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')->pluck('id')->toArray();
        // return $model->whereIn('m_kary.status_kary_id', $id);
        return $model;
    }
}
