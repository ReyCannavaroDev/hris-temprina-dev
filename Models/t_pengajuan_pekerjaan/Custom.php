<?php

namespace App\Models\CustomModels;
use App\Cores\Approval;
use App\Cores\Helper;
use App\Cores\Respo;


class t_pengajuan_pekerjaan extends \App\Models\BasicModels\t_pengajuan_pekerjaan
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();

    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];
    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
       if(!isset($arrayData['status'])){
            $status = 'DRAFT';
        }else{
            $status = $arrayData['status'];
        }

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $this->helper->generateNomor("KODE LAPORAN PEKERJAAN"),
            "status" => $status,
            "creator_id" => auth()->user()->id
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        if (!isset($arrayData['kode']))
        {
            $kode = $this->helper->generateNomor("KODE LAPORAN PEKERJAAN");
        }else{
            $kode = $arrayData['kode'];
        }

        $newArrayData  = array_merge( $arrayData,[
            "kode" => $kode
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
    

    public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{

            $data = $this->find($req->id);
            $data->status = 'POSTED';
            $data->save();

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

}