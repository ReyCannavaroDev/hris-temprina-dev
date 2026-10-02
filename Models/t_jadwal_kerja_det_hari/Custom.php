<?php

/**
 * placeholder model: t_jadwal_kerja_det_hari
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_det_hari extends \App\Models\BasicModels\t_jadwal_kerja_det_hari
{

    public function transformRowData(array $row)
    {
        $m_jam_kerja = !empty($row['m_jam_kerja_id']) ? \DB::table('m_jam_kerja')->where('id', $row['m_jam_kerja_id'])->first() : null;
        
        return array_merge($row, [
            'm_jam_kerja' => $m_jam_kerja ? (array)$m_jam_kerja : null,
            'm_jam_kerja.kode' => $m_jam_kerja ? $m_jam_kerja->kode : '-',
            'm_jam_kerja.waktu_mulai' => $m_jam_kerja ? $m_jam_kerja->waktu_mulai : '-',
            'm_jam_kerja.waktu_akhir' => $m_jam_kerja ? $m_jam_kerja->waktu_akhir : '-',
            'jam_kerja' => $m_jam_kerja ? $m_jam_kerja->kode : '-',
            'start' => $m_jam_kerja ? $m_jam_kerja->waktu_mulai : ($row['waktu_mulai'] ?? '-'),
            'end' => $m_jam_kerja ? $m_jam_kerja->waktu_akhir : ($row['waktu_akhir'] ?? '-'),
        ]);
    }
}
