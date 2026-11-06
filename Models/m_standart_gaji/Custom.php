<?php

namespace App\Models\CustomModels;

class m_standart_gaji extends \App\Models\BasicModels\m_standart_gaji
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        /*file_column*/
    ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        // $data = m_posisi::where('id', $arrayData['m_posisi_id'])->first();
        // if($arrayData['gaji_pokok'] < $data['min_gaji_pokok'] && $arrayData['gaji_pokok'] > $data['max_gaji_pokok'] ){
        //     return response()->json(['errors' => 'Data Gaji Pokok tidak boleh kurang dari '.$data['min_gaji_pokok'].' dan tidak boleh lebih dari '.$data['max_gaji_pokok'].'']);
        // }

        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "kode" =>
                    app()->request->kode ??
                    $this->helper->generateNomor("KODE STANDART GAJI"),
                //'m_dir_id' =>  $m_dir_id
            ]),
        ];
    }

    // public function createAfterTransaction( $newdata, $olddata, $data, $meta )
    // {
    //     $this->where('m_kary_id',$olddata)->whereNot('id',$newdata['id'])->update([
    //         'is_active' => false,
    //     ]);
    // }

    public function custom_getKary($req)
    {
        $branch_id = $req->branch_id ?? null;
        $subcomp_id = $req->subcomp_id ?? null;
        $kary_id = $req->kary_id ?? null;
        $search = $req->search ?? null;

        $m_kary = m_kary::whereHas("m_standart_gaji_n")
            ->whereHas("m_kary_det_jabatan", function ($q) use (
                $subcomp_id,
                $branch_id
            ) {
                $q
                ->where('is_primary', true)
                ->when($subcomp_id, function ($query, $subcomp_id) {
                    if ($subcomp_id != "null") {
                        $query->where("m_subcomp_id", $subcomp_id);
                    }
                })->when($branch_id, function ($query, $branch_id) {
                    if ($branch_id != "null") {
                        $query->where("m_branch_id", $branch_id);
                    }
                });
            })
            // ->where('atasan_id', $kary_id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where("kode", "ILIKE", "%{$search}%")
                        ->orWhere("nama_lengkap", "ILIKE", "%{$search}%");
                });
            })
            ->when($req->filter_kode, function ($q) use ($req) {
                $q->whereHas("m_standart_gaji_n.grading", function($q2) use ($req){
                    $q2->where("code", "ILIKE", "%{$req->filter_kode}%");
                });
            })

             ->when($req->filter_m_zona_nama, function ($q) use ($req) {
                $q->whereHas("m_standart_gaji_n.m_zona", function($q2) use ($req){
                    $q2->where("nama", "ILIKE", "%{$req->filter_m_zona_nama}%");
                });
            })
            // ->when($req->filter_kode, function ($q) use ($req) {
            //     $q->where("kode", "ILIKE", "%{$req->filter_kode}%");
            // })
            ->when($req->filter_nik, function ($q) use ($req) {
                $q->where("nik", "ILIKE", "%{$req->filter_nik}%");
            })
            ->when($req->filter_nama_lengkap, function ($q) use ($req) {
                $q->where(
                    "nama_lengkap",
                    "ILIKE",
                    "%{$req->filter_nama_lengkap}%"
                );
            })
            ->when($req->filter_nama_atasan, function ($q) use ($req) {
                $q->whereHas("atasan", function ($q2) use ($req) {
                    $q2->where(
                        "nama_lengkap",
                        "ILIKE",
                        "%{$req->filter_nama_atasan}%"
                    );
                });
            })
            ->when($req->filter_branch, function ($q) use ($req) {
                $q->whereHas("m_kary_det_jabatan.m_branch", function ($q2) use (
                    $req
                ) {
                    $q2->where("name", "ILIKE", "%{$req->filter_branch}%");
                });
            })
            ->get()
            ->map(function ($kary) {
                return [
                    "id"        => $kary->m_standart_gaji_n[0]?->id,
                    "m_kary.id" => $kary->id,
                    "m_comp_id" => $kary->m_kary_det_jabatan[0]->m_comp_id,
                    "branch_id" => $kary->m_kary_det_jabatan[0]->m_branch_id,
                    "m_subcomp_id" => $kary->m_kary_det_jabatan[0]->m_subcomp_id,
                    "nama_lengkap" => $kary->nama_lengkap,
                    "m_kary.nik" => $kary->nik,

                    "m_zona.nama" =>
                        $kary->m_standart_gaji_n[0]?->m_zona?->nama ?? null,

                    'kode' => $kary->m_standart_gaji_n[0]?->grading?->code,

                ];
            });

        // ->map(function ($kary) {
        //     $detJabatan = $kary->m_kary_det_jabatan->first();
        //     return [
        //         'id'         => $kary->id,
        //         // 'kode'       => $kary->kode,
        //         'nik'        => $kary->nik,
        //         'nama'       => $kary->nama_depan,
        //         'm_kary.nama_lengkap' => $kary->nama_lengkap,
        //         'status'     => $kary->is_active,
        //         'nama_atasan'=> $kary->atasan->nama_lengkap ?? null,
        //         'subcomp'    => $kary->m_kary_det_jabatan[0]->m_subcomp->name ?? null,
        //         'branch'     => $kary->m_kary_det_jabatan[0]->m_branch->name ?? null,
        //         'divisi'     => $kary->m_kary_det_jabatan[0]->m_divisi->name_old ?? null,
        //         // 'gaji'       => $kary->m_standart_gaji_n,
        //         'm_zona.nama' => $kary->m_standart_gaji_n[0]?->m_zona?->nama,
        //         'grading.code' => $kary->m_standart_gaji_n[0]?->grading?->code,
        //         'kode' => $kary->m_standart_gaji_n[0]?->grading?->code,
        //     ];
        // });

        return $this->helper->customResponse("Data Karyawan", 200, $m_kary);
    }
}
