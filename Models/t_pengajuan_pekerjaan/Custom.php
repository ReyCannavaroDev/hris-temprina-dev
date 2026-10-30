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

    public function transformRowData(array $row)
    {
        $branch = !empty($row['m_branch_id']) ? \DB::table('m_branch')->where('id', $row['m_branch_id'])->first() : null;
        $requestUser = !empty($row['request_id']) ? \DB::table('m_kary')->where('id', $row['request_id'])->first() : null;
        $picUser = !empty($row['pic_id']) ? \DB::table('m_kary')->where('id', $row['pic_id'])->first() : null;
        $jenisPekerjaan = !empty($row['jenis_pekerjaan_id']) ? \DB::table('m_general')->where('id', $row['jenis_pekerjaan_id'])->first() : null;

        $branchName = $branch->name ?? '-';
        $requestNama = $requestUser->nama_lengkap ?? '-';
        $picNama = $picUser->nama_lengkap ?? '-';
        $jenisPekerjaanNama = $jenisPekerjaan->value ?? '-';

        return array_merge($row, [
            // Cabang
            'm_branch' => $branch ? (array)$branch : null,
            'm_branch.name' => $branchName,
            'branch_name' => $branchName,

            // User / Pemohon
            'request' => $requestUser ? (array)$requestUser : null,
            'request.nama_lengkap' => $requestNama,
            'request_nama' => $requestNama,

            // PIC
            'pic' => $picUser ? (array)$picUser : null,
            'pic.nama_lengkap' => $picNama,
            'pic_nama' => $picNama,

            // Jenis Pekerjaan
            'jenis_pekerjaan' => $jenisPekerjaan ? (array)$jenisPekerjaan : null,
            'jenis_pekerjaan.value' => $jenisPekerjaanNama,
            'jenis_pekerjaan_nama' => $jenisPekerjaanNama,
        ]);
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
