<?php

namespace App\Models\CustomModels;
use Illuminate\Database\Eloquent\Builder;

class m_menu extends \App\Models\BasicModels\m_menu
{       
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function scopeapproval($model)
    {
        $menu_approval = [
            'Realisasi Pelatihan',
            'Pengajuan Pelatihan',
            'Efektifitas Pelatihan',
            'Evaluasi Pelatihan',
            'Cuti',
            'Lembur',
            'Laporan Pekerjaan',
            'Rencana Perjalanan Dinas',
            'Penyelesaian Perjalanan Dinas',
            'Lowongan Pekerjaan'
            ];

        return $model->whereIn('m_menu.menu', $menu_approval);
    }

}