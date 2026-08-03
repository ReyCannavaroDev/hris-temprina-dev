<?php

namespace App\Models\CustomModels;
use DB;

class t_klaim_askes extends \App\Models\BasicModels\t_klaim_askes
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->details = array_values(array_unique(array_merge($this->details, [
            "t_klaim_askes_d",
        ])));
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $kode = @$this->helper->generateNomor("KODE KLAIM ASKES");
        $newArrayData = array_merge($arrayData, [
            "nomor" => @$arrayData["nomor"] ?? $kode,
        ]);
        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    

    public function custom_posted()
    {
        // Memulai transaksi database untuk menjaga integritas data
        DB::beginTransaction();

        try {
            //dd(app()->request);
            $id = app()->request->id;
            $data = $this->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            // Lakukan perubahan data
            $data->status = 'POSTED';
            $data->save();

            // Jika semua berhasil, simpan secara permanen ke database
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui menjadi POSTED.'
            ], 200);

        } catch (\Exception $e) {
            // Jika ada error (seperti disk full), kembalikan data ke kondisi semula
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
