<?php

namespace App\Models\CustomModels;

class t_request_pelatihan_d_kary extends \App\Models\BasicModels\t_request_pelatihan_d_kary
{    
    public function __construct()
    {
        parent::__construct();
        $this->columnsFull = array_merge($this->columnsFull, [
            "m_kary.nama_lengkap:string:optional",
            "m_kary.m_branch_id:bigint:optional",
            "m_kary.m_divisi_id:bigint:optional",
            "m_kary.m_posisi_id:bigint:optional",
            "nama_lengkap:string:optional",
            "m_branch_id:bigint:optional",
            "m_divisi_id:bigint:optional",
            "m_posisi_id:bigint:optional",
            "m_divisi.name:string:optional"
        ]);
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function transformRowData(array $row)
    {
        $kary = m_kary::find($row['m_kary_id'] ?? 0);

        return array_merge($row, [
            'm_kary' => $kary ? [
                'id' => $kary->id,
                'nama_lengkap' => $kary->nama_lengkap,
                'm_branch_id' => $kary->m_branch_id,
                'm_divisi_id' => $kary->m_divisi_id,
                'm_posisi_id' => $kary->m_posisi_id,
            ] : null,
            'm_kary.nama_lengkap' => $kary?->nama_lengkap,
            'm_kary.m_branch_id' => $kary?->m_branch_id,
            'm_kary.m_divisi_id' => $kary?->m_divisi_id,
            'm_kary.m_posisi_id' => $kary?->m_posisi_id,
        ]);
    }
}