<?php

namespace App\Models\CustomModels;

class m_branch extends \App\Models\BasicModels\m_branch
{   
    private $helper;
    public function __construct()
    {
        $this->helper = getCore('Helper');
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];


    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {

        $isDuplicate = $this->isDuplicate($arrayData['name'],$id=null);
        if($isDuplicate){
            return ["errors" => ['Nama Cabang '.$arrayData['name']. ' Sudah Ada']];
        }

        
        $newArrayData  = array_merge( $arrayData,[
            'code' => $this->helper->generateNomor('KODE CABANG')
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function createAfterTransaction( $newdata, $olddata, $data, $meta )
    {
        $id = $newdata['id'];
        $saveLocation = $this->save_location(@$data['lokasi'], false , $id);
        if(!$saveLocation) return ["errors" => ['error save lokasi']];
    }

    public function m_divisi()
    {
        return $this->hasMany(m_divisi::class, 'm_branch_id', 'id');
    }


   private function isDuplicate($data, $id){
        $query = $this->whereRaw('LOWER(name) = ?', [strtolower($data)]);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        $checkData = $query->first();
        return $checkData ? true : false;
    }

    public function updateAfterTransaction( $newdata, $olddata, $data, $meta )
    {
        $id = $newdata['id'];
        $saveLocation = $this->save_location(@$data['lokasi'],true, $id);
        if(!$saveLocation) return ["errors" => ['error save lokasi']];
    }
    


    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $isDuplicate = $this->isDuplicate($arrayData['name'],$id);
        if($isDuplicate){
            return ["errors" => ['Nama Cabang '.$arrayData['name']. ' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    private function save_location($data, $isUpdate, $id){
        try {
            \DB::beginTransaction();
            presensi_lokasi::where('branch_id',$id)->delete();

            foreach($data as $single){
                presensi_lokasi::create([
                    'branch_id' => $id,
                    'nama' => $single['nama'],
                    'lat' => $single['lat'],
                    'long' => $single['long'],
                    'is_active' => $single['is_active'],
                ]);
            }
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
                        
            return false;

        }
    }

    public function lokasi() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\presensi_lokasi', 'branch_id', 'id');
    }
    
    
    public function scopeWithLokasi($model){
        return $model->with('lokasi');
    }
}