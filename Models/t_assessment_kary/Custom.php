<?php

namespace App\Models\CustomModels;

class t_assessment_kary extends \App\Models\BasicModels\t_assessment_kary
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_postData($request)
    {
        $data = t_assessment_kary::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $update = $data->update([
                'status' => "POSTED"
            ]);

            if ($update) {
                return response()->json(['message' => 'Data berhasil diposting.']);
            } else {
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            // Handle exception, log error messages, etc.
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function scopeatasan($model)
    {
        $atasan_id = default_users::find(auth()->user()->id)?->m_kary_id ?? null;
        if($atasan_id){
            return $model->where('m_kary.atasan_id', $atasan_id);
        }else{
            return $model;
        }
    }

    public function scoperespo($model)
    {
        $m_subcomp_id = request('m_subcomp_id') ?? null;
        $m_branch_id = request('m_branch_id') ?? null;
        // dd($m_branch_id, $m_subcomp_id);

        if ($m_subcomp_id === 'null') $m_subcomp_id = null;
        if ($m_branch_id === 'null')  $m_branch_id  = null;

        return $model->when($m_subcomp_id, function($q) use ($m_subcomp_id){
            $q->where('m_kary.m_subcomp_id', $m_subcomp_id);
        })->when($m_branch_id, function($q) use ($m_branch_id){
            $q->where('m_kary.m_branch_id', $m_branch_id);
        });
    }
    
}