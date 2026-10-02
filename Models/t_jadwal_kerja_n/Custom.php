<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_n extends \App\Models\BasicModels\t_jadwal_kerja_n
{

    public function readAfter($model, $result)
    {
        if (isset($result['id'])) {
            $rawDetails = \DB::table('t_jadwal_kerja_d_hari_n')
                ->where('t_jadwal_kerja_n_id', $result['id'])
                ->orderBy('id', 'asc')
                ->get();
                
            $formattedDetails = [];
            foreach ($rawDetails as $det) {
                $det = (array) $det;
                
                // Fetch m_jam_kerja to populate the missing view fields
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
                
                $formattedDetails[] = $det;
            }
            $result['t_jadwal_kerja_d_hari_n'] = $formattedDetails;
        }
        return $result;
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $this->saveDetails($id, $arrayData);
        return $arrayData;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $this->saveDetails($id, $arrayData);
        return $arrayData;
    }

    private function saveDetails($id, $arrayData)
    {
        $payload = app('request')->input('t_jadwal_kerja_d_hari_n');
        $details = $arrayData['t_jadwal_kerja_d_hari_n'] ?? $payload ?? [];
        if (!empty($details)) {
            \DB::table('t_jadwal_kerja_d_hari_n')->where('t_jadwal_kerja_n_id', $id)->delete();
            
            $inserts = [];
            foreach ($details as $idx => $d) {
                $inserts[] = [
                    't_jadwal_kerja_n_id' => $id,
                    'm_jam_kerja_id' => $d['m_jam_kerja_id'] ?? null,
                    'tipe_hari' => $d['tipe_hari'] ?? 'KERJA',
                    'day' => $d['day'] ?? null,
                    'day_num' => $d['day_num'] ?? null,
                ];
            }
            \DB::table('t_jadwal_kerja_d_hari_n')->insert($inserts);
        }
    }
}
