<?php

namespace App\Models\CustomModels;

class m_berkas extends \App\Models\BasicModels\m_berkas
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = ['url'];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
    public function onRetrieved($model)
    {
        $backendUrl = env('BACKEND_URL');
        $model->url = $backendUrl . '/' . $model->url;
    }

    public function transformRowData( array $row )
    {
        $kategori_value = @$row['kategori.value'];
        if ($row['kategori_id']) {
            $fullPath = [];
            $kategori = m_general::find($row['kategori_id']);
            $currentKategori = $kategori;

            while ($currentKategori) {
                array_unshift($fullPath, $currentKategori->value);

                if ($currentKategori->value_2) {
                    $currentKategori = $currentKategori->where('id', $currentKategori->value_2)->first();
                } else {
                    $currentKategori = null; 
                }
            }

            $kategori_value = implode(' - ', $fullPath);
        }

        return array_merge( $row, [
            "kategori.value" => $kategori_value,
        ] );
    }
        
}