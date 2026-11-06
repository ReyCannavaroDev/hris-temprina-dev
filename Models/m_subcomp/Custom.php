<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;

class m_subcomp extends \App\Models\BasicModels\m_subcomp
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
            return ["errors" => ['Nama SUB '.$arrayData['name']. ' Sudah Ada']];
        }
        
        $newArrayData  = array_merge( $arrayData,[
            'code' => $this->helper->generateNomor('KODE SUB UNIT BISNIS')
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    private function isDuplicate($data, $id){
        $query = $this->whereRaw('LOWER(name) = ?', [strtolower($data)]);
        
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        $checkData = $query->first();
        return $checkData ? true : false;
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $isDuplicate = $this->isDuplicate($arrayData['name'],$id);
        if($isDuplicate){
            return ["errors" => ['Nama SUB '.$arrayData['name']. ' Sudah Ada']];
        }
        
        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    // public function m_branch() :\BelongsTo
    // {
    //     return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    // }

    public function m_branch()
    {
        return $this->hasMany(m_branch::class, 'm_subcomp_id', 'id');
    }

    public function custom_dashboard()
    {
        $sub_count = m_subcomp::where('is_active', true)->count();
        $branch_count = m_branch::where('is_active', true)->count();
        $total_kary = default_users::whereHas('m_kary', function($q){
            $q->where('is_active', true);
        })->count();
        $now = Carbon::now()->format('Y-m-d');
        $periode_in_date = Carbon::now()
            ->subMonth()
            ->day(20)
            ->format('Y-m-d');        

        $total_hadir = default_users::whereHas('presensi_absensi', function($q) use ($now){
            $q->where('tanggal', $now);
        })->count();
        $tidak_hadir = $total_kary - $total_hadir;

        $m_branch = m_branch::get();
        $salary_per_branch = []; // array penampung

        foreach ($m_branch as $branch) {
            $gaji = t_final_gaji_det::whereHas('m_kary', function($q) use ($branch){
                    $q->where('m_branch_id', $branch->id);
                })
                ->where('periode_in_date',  $periode_in_date)
                ->sum('total_gaji') ?? 0;

            // bisa langsung masukkan ke array
            $salary_per_branch[] = [
                'branch_id'   => $branch->id,
                'periode'     => Carbon::parse($periode_in_date)->format('Y-m'),
                'branch_name' => $branch->name ?? null, // kalau ada kolom nama
                'total_gaji'  => (int)$gaji
            ];
        }

        $m_subcomp = m_subcomp::get();
        $salary_per_subcomp = []; // array penampung

        foreach ($m_subcomp as $subcomp) {
            $gaji = t_final_gaji_det::whereHas('m_kary', function($q) use ($subcomp){
                    $q->where('m_subcomp_id', $subcomp->id);
                })
                ->where('periode_in_date',  $periode_in_date)
                ->sum('total_gaji') ?? 0;

            // bisa langsung masukkan ke array
            $salary_per_subcomp[] = [
                'subcomp_id'   => $subcomp->id,
                'periode'     => Carbon::parse($periode_in_date)->format('Y-m'),
                'subcomp_name' => $subcomp->name ?? null, // kalau ada kolom nama
                'total_gaji'  => (int)$gaji
            ];
        }

        $data = [
            "subcomp_count" => $sub_count,
            "branch_count" => $branch_count,
            "total_hadir" => $total_hadir,
            "total_absen" => $tidak_hadir,
            "branch_salary" => $salary_per_branch,
            "subcomp_salary" => $salary_per_subcomp,
        ];

        return $data;
    }
    
}