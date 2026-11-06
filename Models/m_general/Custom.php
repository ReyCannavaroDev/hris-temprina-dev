<?php

namespace App\Models\CustomModels;

class m_general extends \App\Models\BasicModels\m_general
{    

    private $helper;
    public function __construct()
    {
        $this->helper = getCore('Helper');
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_massUpdate()
    {
        // SELECT m_general.group from m_general group by m_general.group
            \DB::beginTransaction();
        try{
            \DB::table('m_general')->update(['group' => \DB::raw('UPPER("group")')]);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return 'Terjadi kesalahan: ' . $e->getMessage();
        }


        return true;
    }

    public function transformRowData( array $row )
    {
        $kategori_value = $row['value'];
        $group = $row['group'] ?? '';
        
        if ($group === 'KATEGORI BERKAS') {
            $fullPath = [];
            $kategori = m_general::find($row['id']);
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
            "value" => $kategori_value,
        ] );
    }

    public function custom_seeder()
    {
        $provinsiData = m_general::where('group', 'KOTA')->orderBy('id')->skip(500)->take(250)->get();
        // SELECT * FROM m_general where m_general.group = 'KECAMATAN' and m_general.key = '196' order by m_general.id desc
        \DB::beginTransaction();

        try{
            $kota = [];
                foreach($provinsiData as $prov){
                    $apiUrl = 'https://backend.qqltech.com/kodepos/region/kecamatan?kota=' . urlencode($prov->value);
                    $ch = curl_init($apiUrl);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    $response = curl_exec($ch);
                    $data = json_decode($response, true);
                    foreach($data as $kot){
                        $kota[] = [
                            'm_comp_id' => 1,
                            'group' => 'KECAMATAN',
                            'key' => $prov->id,
                            'code' => null,
                            'value' => $kot,
                            'is_active' => true
                        ];
                    }
                    curl_close($ch);
                }

            m_general::insert($kota);
            // \DB::table('m_general')->where('group', 'KOTA')->delete();
            \DB::commit();

            return 'Proses selesai. Jumlah data yang ditambahkan: '. count($kota);
        } catch (\Exception $e) {
            \DB::rollBack();
            return 'Terjadi kesalahan: ' . $e->getMessage();
        }

    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $duplicateValue = ["grading","cost centre"];

        // $m_dir_id = auth()->user()->m_dir_id;
        
        if(in_array(strtolower($arrayData['group']), $duplicateValue)){
            $checkDuplicate = $this->isDuplicate($arrayData, $id=null);
            if($checkDuplicate['duplicate']) return['errors' =>['Nama '.$checkDuplicate['value'].' Sudah Ada']];
        }

        // if(!$m_dir_id)  
        //     return [
        //         "errors" => ['Maaf akun anda tidak memiliki akses untuk menambahkan data']
        //     ];
        return [
            "model"  => $model,
            "data"   => array_merge($arrayData,[
                'm_dir_id' =>  null
            ])
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $duplicateValue = ["grading","cost centre"];

        if(in_array(strtolower($arrayData['group']), $duplicateValue)){
            $checkDuplicate = $this->isDuplicate($arrayData, $id);
            if($checkDuplicate['duplicate']) return['errors' =>['Nama '.$checkDuplicate['value'].' Sudah Ada']];
        }

        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
    
    public function scopeGenProvinsi($model){
        $model->whereRaw("m_general.group = 'PROVINSI' and m_general.is_active = true");
        return $model;
    }

    public function scopeGenKota($model){
        $req = app()->request;
        $prov_id = $req->provinsi_id ?? 0;
        $model->where('group','KOTA')->where('key', $prov_id)->where('m_general.is_active', true);
        return $model;
    }

    public function scopeGenKecamatan($model){
        $req = app()->request;
        $kota_id = $req->kota_id ?? 0;
        $model->where('group','KECAMATAN')->where('key', $kota_id)->where('m_general.is_active', true);
        return $model;
    }

    public function scopeAlasanCuti($model){
        return $model->where('m_general.group','ALASAN CUTI')->where('m_general.is_active', true);
    }

    public function scopeTipeCuti($model){
        return $model->where('m_general.group','TIPE CUTI')->where('m_general.is_active', true);
    }

    public function scopeJenisSPD($model){
        return $model->where('m_general.group','JENIS SPD')->where('m_general.is_active', true);
    }

    public function scopeTipeLembur($model){
        return $model->where('group','TIPE LEMBUR');
    }

    public function scopeAlasanLembur($model){
        return $model->where('group','ALASAN LEMBUR');
    }

    public function scopeGrading($model){
        return $model->where('group','GRADING');
    }

    public function scopeCostcentre($model){
        return $model->where('group','COSTCENTRE');
    }

    public function scopeJenkel($model){
        return $model->where('group','JENIS KELAMIN');
    }

    public function scopeAgama($model){
        return $model->where('group','AGAMA');
    }

    public function scopeGolDarah($model){
        return $model->where('group','GOLONGAN DARAH');
    }

    public function scopeStatusNikah($model){
        return $model->where('group','STATUS NIKAH');
    }

    public function scopeTanggungan($model){
        return $model->where('group','TANGGUNGAN');
    }

    public function scopeTingkatPend($model){
        return $model->where('group','PENDIDIKAN');
    }

    // select g.group from m_general g group by g.group

    public function scopeFilterByGroup($model)
    {
        $req = app()->request;
        $model->whereRaw('upper(m_general.group) = upper(?)', [$req->group]);
    }

    public function scopeNotIncludeByGroup($model)
    {
        $notIn = ["grading"];
        $model->whereNotIn('m_general.group', array_map('strtoupper', $notIn));

    }

    public function scopeByCostCentre($model)
    {
        $req = app()->request;
        $m_branch_id = @$req->branch_id ?? 0;

        $model
            ->join('m_branch_d_cc', 'm_branch_d_cc.cost_centre_id', "$this->table.id")
            ->where('m_branch_d_cc.m_branch_id', $m_branch_id);
    }

    public function custom_getByGroup($req)
    {
        $data = $this->whereNotIn('group', ['GRADING', 'COSENTRE'])->select('group')->groupBy('group')->orderBy('group')->get();
        $return = [
            "data"  => $data
        ];
        return response()->json($return);
    }

    private function isDuplicate($data, $id){
    
        $duplicate = "";

        $query = $this->where('group', $data['group'])->whereRaw('LOWER(value) = ?', [strtolower($data['value'])]);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }

        if($data['group'] == "COST CENTRE"){
            $duplicateCode = $this->where('group', $data['group'])->where('code',$data['code']);

            if($id){
                $duplicateCode->where('id', '!=', $id);
            }

            $duplicateCode = $duplicateCode->first();

            if($duplicateCode){
                $duplicate = $duplicateCode['code'];
                return[
                    "duplicate" => true,
                    "value" => $duplicate,
                ];
            }
        }

        $checkData = $query->first();
        if($checkData){
            $duplicate = $checkData['value'];
            return[
                "duplicate" => true,
                "value" => $duplicate,
            ];
        }else{
            return[
                "duplicate" => false,
                "value" => $duplicate,
            ];

        } 
        
    }

    public function scopeSearchGrading($model){
    $nominal = is_numeric(request('nominal')) ? (float)request('nominal') : 0;
    return $model
            ->where('group', 'GRADING')
            ->whereRaw("? BETWEEN CAST(NULLIF(value_3, '') AS NUMERIC) AND CAST(NULLIF(value_4, '') AS NUMERIC)", [$nominal]);
    }

    // public function custom_raw_search_grade($req){
    //     $nominal = (float) $req->nominal;
    //     $results = \DB::select("
            // SELECT * 
            // FROM m_general 
            // WHERE \"group\" = 'GRADING' 
            // AND ? BETWEEN CAST(NULLIF(value_3, '') AS NUMERIC) 
            //   AND CAST(NULLIF(value_4, '') AS NUMERIC)", [$nominal]

    //     );
    //     $collect = collect($results)->first();
    //     return $this->helper->customResponse("OK", 200, $collect);
    // }
    
    public function custom_upload_grade_excel($req)
    {
        $validator = \Validator::make($req->all(), [
            "file" => "required|mimes:xls,xlsx"
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Wrong format'], 401);
        }

        $collection = \Excel::toCollection(null, $req->file('file'));

        foreach ($collection[0] as $index => $row) {
            if ($index === 0)
                continue;

            $row = $row->toArray();

            if (isset($row[0]) && in_array(strtolower(trim($row[0])), ['keterangan', 'key'])) {
                break;
            }

            if (count($row) < 5 || empty($row[1]))
                continue;

            m_general::updateOrCreate(
            [
                'code' =>$row[0]
            ],[
                'group' => 'GRADING',
                'key' => 'GRADE',
                'value' => $row[1],
                'value_2' => $row[2],
                'value_3' => $row[3],
                'value_4' => $row[4],
            ]);
        }
        return response()->json(['success' => 'File imported successfully'], 200);
    }

    //perlu respo dan path
    public function custom_access($req)
    {
        $path = $req->path;
        $respo = $req->respo_id;
        // dd(auth()->user());
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        
        if (strtolower($user->user_type) === 'admin') {
            return response()->json([
                'can_read'   => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
            ]);
        }

        // $roles = m_respo_d::where('m_respo_id', $respo)->pluck('m_role_id');
        // $menu = m_menu::where('path', $path)->where('is_active', true)->first();
        // $access = m_role_det::where('m_menu_id', $menu?->id)->whereIn('m_role_id', $roles)->first();
        // return response()->json([
        //     'can_read'   => $access->can_read,
        //     'can_create' => $access->can_create,
        //     'can_update' => $access->can_update,
        //     'can_delete' => $access->can_delete
        // ]);

        $roles = m_respo_d::where('m_respo_id', $respo)->pluck('m_role_id');
        $menu = m_menu::where('path', $path)->where('is_active', true)->first();
        $access = null;
        
        if ($menu) {
            $access = m_role_det::where('m_menu_id', $menu->id)
                ->whereIn('m_role_id', $roles)
                ->first();
        }

        return response()->json([
            'can_read'   => (bool) ($access->can_read ?? false),
            'can_create' => (bool) ($access->can_create ?? false),
            'can_update' => (bool) ($access->can_update ?? false),
            'can_delete' => (bool) ($access->can_delete ?? false)
        ]);
            
    }

    public function custom_destroy($req)
    {
        $id = $req->id;
        
        $delete = \DB::table('m_general')
            ->where('id', $id)
            ->delete();

        if ($delete) {
            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        }

        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
    }

}