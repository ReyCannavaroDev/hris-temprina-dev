<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_n extends \App\Models\BasicModels\t_jadwal_kerja_n
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends[] = 't_jadwal_kerja_det_hari';
    }

    public function getTJadwalKerjaDetHariAttribute()
    {
        return $this->attributes['t_jadwal_kerja_det_hari'] ?? [];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if (!$id) return $row;

        $result = $row;
        $rawDetails = \DB::table('t_jadwal_kerja_det_hari')
            ->where('t_jadwal_kerja_id', $id)
            ->orderBy('id', 'asc')
            ->get();
            
        $formattedDetails = [];
        foreach ($rawDetails as $det) {
            $det = (array) $det;
            
            if (!empty($det['m_jam_kerja_id'])) {
                $m_jam_kerja = \DB::table('m_jam_kerja')->where('id', $det['m_jam_kerja_id'])->first();
                if ($m_jam_kerja) {
                    $det['m_jam_kerja'] = (array) $m_jam_kerja;
                    $det['m_jam_kerja.kode'] = $m_jam_kerja->kode;
                    $det['m_jam_kerja.waktu_mulai'] = $m_jam_kerja->waktu_mulai;
                    $det['m_jam_kerja.waktu_akhir'] = $m_jam_kerja->waktu_akhir;
                    $det['start_jam_kerja'] = $m_jam_kerja->waktu_mulai;
                    $det['end_jam_kerja'] = $m_jam_kerja->waktu_akhir;
                    $det['start'] = $m_jam_kerja->waktu_mulai;
                    $det['end'] = $m_jam_kerja->waktu_akhir;
                    $det['jam_kerja'] = $m_jam_kerja->id; // set model for select
                }
            } else {
                $det['start'] = $det['waktu_mulai'] ?? '--:--';
                $det['end'] = $det['waktu_akhir'] ?? '--:--';
                $det['start_jam_kerja'] = $det['waktu_mulai'] ?? '--:--';
                $det['end_jam_kerja'] = $det['waktu_akhir'] ?? '--:--';
            }
            
            if (!isset($det['day']) && isset($det['hari'])) $det['day'] = $det['hari'];
            if (!isset($det['tipe_hari'])) $det['tipe_hari'] = 'KERJA';
            
            $formattedDetails[] = $det;
        }
        
        $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
        
        return $result;
    }

    public function readAfter($model, $result)
    {
        if (isset($result['id'])) {
            $rawDetails = \DB::table('t_jadwal_kerja_det_hari')
                ->where('t_jadwal_kerja_id', $result['id'])
                ->orderBy('id', 'asc')
                ->get();
                
            $formattedDetails = [];
            foreach ($rawDetails as $det) {
                $det = (array) $det;
                
                if (!empty($det['m_jam_kerja_id'])) {
                    $m_jam_kerja = \DB::table('m_jam_kerja')->where('id', $det['m_jam_kerja_id'])->first();
                    if ($m_jam_kerja) {
                        $det['m_jam_kerja'] = (array) $m_jam_kerja;
                        $det['m_jam_kerja.kode'] = $m_jam_kerja->kode;
                        $det['m_jam_kerja.waktu_mulai'] = $m_jam_kerja->waktu_mulai;
                        $det['m_jam_kerja.waktu_akhir'] = $m_jam_kerja->waktu_akhir;
                        $det['start_jam_kerja'] = $m_jam_kerja->waktu_mulai;
                        $det['end_jam_kerja'] = $m_jam_kerja->waktu_akhir;
                        $det['start'] = $m_jam_kerja->waktu_mulai;
                        $det['end'] = $m_jam_kerja->waktu_akhir;
                        $det['jam_kerja'] = $m_jam_kerja->id; // set model for select
                    }
                } else {
                    $det['start'] = $det['waktu_mulai'] ?? '--:--';
                    $det['end'] = $det['waktu_akhir'] ?? '--:--';
                    $det['start_jam_kerja'] = $det['waktu_mulai'] ?? '--:--';
                    $det['end_jam_kerja'] = $det['waktu_akhir'] ?? '--:--';
                }
                
                if (!isset($det['day']) && isset($det['hari'])) $det['day'] = $det['hari'];
                if (!isset($det['tipe_hari'])) $det['tipe_hari'] = 'KERJA';
                
                $formattedDetails[] = $det;
            }
            
            if (is_array($result)) {
                $result['t_jadwal_kerja_det_hari'] = $formattedDetails;
            } elseif (is_object($result)) {
                $result->t_jadwal_kerja_det_hari = $formattedDetails;
                if (method_exists($result, 'setAttribute')) {
                    $result->setAttribute('t_jadwal_kerja_det_hari', $formattedDetails);
                }
            }
        }
        return $result;
    }
    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        // Pindahkan proses simpan detail ke Before agar pasti jalan!
        if ($id) {
            $this->saveDetails($id, $arrayData);
        }
        unset($arrayData['t_jadwal_kerja_det_hari']);
        return ["model" => $model, "data" => $arrayData];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        if ($id) {
            $this->saveDetails($id, $arrayData);
        }
        unset($arrayData['t_jadwal_kerja_det_hari']);
        return ["model" => $model, "data" => $arrayData];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        if ($id) {
            $this->saveDetails($id, $arrayData);
        }
        return $arrayData;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        // Kosongkan karena sudah di-handle di Before
        return $arrayData;
    }

    private function saveDetails($id, $arrayData)
    {
        $payload = app('request')->input('t_jadwal_kerja_det_hari');
        $details = $arrayData['t_jadwal_kerja_det_hari'] ?? $payload ?? [];

        \DB::table('t_jadwal_kerja_det_hari')->where('t_jadwal_kerja_id', $id)->delete();
        
        $inserts = [];
        foreach ($details as $idx => $d) {
            $inserts[] = [
                't_jadwal_kerja_id' => $id,
                'm_jam_kerja_id' => $d['m_jam_kerja_id'] ?? null,
                'day' => $d['hari'] ?? $d['day'] ?? null,
                'day_num' => $d['day_num'] ?? ($idx + 1),
                'tipe_hari' => $d['tipe_hari'] ?? 'KERJA',
                'waktu_mulai' => $d['waktu_mulai'] ?? $d['start'] ?? '--:--',
                'waktu_akhir' => $d['waktu_akhir'] ?? $d['end'] ?? '--:--',
            ];
        }
        if (!empty($inserts)) {
            \DB::table('t_jadwal_kerja_det_hari')->insert($inserts);
        }
    }
}
