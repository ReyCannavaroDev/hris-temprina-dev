<?php

namespace App\Models\CustomModels;

class m_divisi extends \App\Models\BasicModels\m_divisi
{
    public function __construct()
    {
        parent::__construct();
    }

    public $fileColumns = [ /*file_column*/];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];


    public function transformRowData( array $row )
    {
        $data = [];

        $divisiVal = '';
        if (!empty($row['name'])) {
            $gen = \App\Models\BasicModels\m_general::find($row['name']);
            if ($gen && !empty($gen->value)) {
                $divisiVal = $gen->value;
            }
        }
        if (empty($divisiVal) && !empty($row['name_old'])) {
            $divisiVal = $row['name_old'];
        }

        $data['name.value'] = $divisiVal;
        $data['value'] = $divisiVal;

        if(app()->request->concat_branch){
            $getData = $this->where('m_divisi.id',$row['id'])
            ->leftjoin('m_branch','m_branch.id','m_divisi.m_branch_id')
            ->select('m_branch.name as branch_name','m_divisi.name as divisi_name')
            ->first();
            $concat = ($getData['divisi_name'] ?? '') . (($getData['branch_name'] ?? false) ? ' - ' : '') . ($getData['branch_name'] ?? '');
            $data['concat_divisi_branch'] = $concat;
        }
        return array_merge( $row, $data );
    }

    public function general_name() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'name', 'id');
    }

    public function child_divisi() 
    {
        return $this->hasMany(m_divisi::class, 'parent_id', 'id');
    }
        
    public function scopeName($model){
        return $model->leftjoin('m_general', 'm_divisi.name', 'm_general.id')
                ->select(
                    'm_divisi.id',
                    'm_divisi.m_branch_id',
                    \DB::raw("COALESCE(m_general.value, m_divisi.name_old, '') as \"name.value\""),
                    \DB::raw("COALESCE(m_general.value, m_divisi.name_old, '') as value"),
                    \DB::raw("COALESCE(m_general.value, m_divisi.name_old, '') as name_old"),
                    'm_divisi.nomor'
                );
    }

    public function scopeNames($model){
        return $model->leftjoin('m_general', 'm_divisi.name', 'm_general.id')
                ->select(
                    'm_divisi.id',
                    \DB::raw("COALESCE(m_general.value, m_divisi.name_old, '') as value"),
                    'm_divisi.nomor'
                );
    }

    public function custom_get_import_divisi()
    {
        return $this->checkImportDivisiMatch();
        return \DB::table("import.divisi")->get();
    }

    public function compareBranchNames()
    {
        $mainBranches = \DB::table('m_branch')->pluck('name')->map('strtoupper')->unique()->toArray();
        $importBranches = \DB::table('import.divisi')->pluck('branch')->map('strtoupper')->unique()->toArray();

        $result = [
            'in_main_not_in_import' => array_diff($mainBranches, $importBranches),
            'in_import_not_in_main' => array_diff($importBranches, $mainBranches),
            'same_in_both' => array_values(array_intersect($mainBranches, $importBranches)),
        ];

        return response()->json($result);
    }

    /**
     * Cek kecocokan data divisi antara import.divisi dan m_divisi berdasarkan nama divisi dan id m_branch.
     * Return JSON: cocok, belum ada di m_divisi, ada di m_divisi tapi tidak ada di import.
     */
    public function checkImportDivisiMatch()
    {
        // Ambil data import
        $importDivisi = \DB::table('import.divisi')
            ->select(['branch', 'divisi'])
            ->get()
            ->map(function($row) {
                return [
                    'branch' => strtoupper(trim($row->branch)),
                    'divisi' => strtoupper(trim($row->divisi)),
                ];
            })
            ->unique(function($item) {
                return $item['branch'].'|'.$item['divisi'];
            })
            ->values()
            ->toArray();

        // Ambil branch id mapping
        $branchMap = \DB::table('m_branch')
            ->select(['id', 'name'])
            ->get()
            ->mapWithKeys(function($row) {
                return [strtoupper(trim($row->name)) => $row->id];
            })
            ->toArray();

        // Ambil data m_divisi
        $mDivisi = \DB::table('m_divisi')
            ->select(['id', 'name', 'm_branch_id'])
            ->get()
            ->map(function($row) {
                return [
                    'm_branch_id' => $row->m_branch_id,
                    'divisi' => strtoupper(trim($row->name)),
                ];
            })
            ->unique(function($item) {
                return $item['m_branch_id'].'|'.$item['divisi'];
            })
            ->values()
            ->toArray();

        // Buat array untuk pencocokan
        $importKeyed = [];
        foreach ($importDivisi as $row) {
            $branchId = $branchMap[$row['branch']] ?? null;
            if ($branchId) {
                $importKeyed[$branchId.'|'.$row['divisi']] = [
                    'branch_id' => $branchId,
                    'branch' => $row['branch'],
                    'divisi' => $row['divisi'],
                ];
            }
        }

        $mDivisiKeyed = [];
        foreach ($mDivisi as $row) {
            $mDivisiKeyed[$row['m_branch_id'].'|'.$row['divisi']] = $row;
        }

        // Data cocok
        $matched = array_intersect_key($importKeyed, $mDivisiKeyed);

        // Data belum ada di m_divisi
        $not_in_m_divisi = array_diff_key($importKeyed, $mDivisiKeyed);

        // Data ada di m_divisi tapi tidak ada di import
        $not_in_import = array_diff_key($mDivisiKeyed, $importKeyed);

        $result = [
            'matched' => array_values($matched),
            'not_in_m_divisi' => array_values($not_in_m_divisi),
            'not_in_import' => array_values($not_in_import),
        ];

        return response()->json($result);
    }

    /**
     * Sinkronisasi data m_divisi dengan import.divisi:
     * - Tambah jika belum ada di m_divisi
     * - Hapus jika tidak ada di import
     * - Update jika sudah ada (tidak ada update field lain)
     * Field nomor dan level default 0, is_active true
     */
    public function syncImportDivisiToMdivisi()
    {
        // Ambil data import
        $importDivisi = \DB::table('import.divisi')
            ->select(['branch', 'divisi'])
            ->get()
            ->map(function($row) {
                return [
                    'branch' => strtoupper(trim($row->branch)),
                    'divisi' => strtoupper(trim($row->divisi)),
                ];
            })
            ->unique(function($item) {
                return $item['branch'].'|'.$item['divisi'];
            })
            ->values()
            ->toArray();

        // Ambil branch id mapping
        $branchMap = \DB::table('m_branch')
            ->select(['id', 'name'])
            ->get()
            ->mapWithKeys(function($row) {
                return [strtoupper(trim($row->name)) => $row->id];
            })
            ->toArray();

        // Ambil data m_divisi
        $mDivisi = \DB::table('m_divisi')
            ->select(['id', 'name', 'm_branch_id'])
            ->get()
            ->map(function($row) {
                return [
                    'id' => $row->id,
                    'm_branch_id' => $row->m_branch_id,
                    'divisi' => strtoupper(trim($row->name)),
                ];
            })
            ->unique(function($item) {
                return $item['m_branch_id'].'|'.$item['divisi'];
            })
            ->values()
            ->toArray();

        // Buat array untuk pencocokan
        $importKeyed = [];
        foreach ($importDivisi as $row) {
            $branchId = $branchMap[$row['branch']] ?? null;
            if ($branchId) {
                $importKeyed[$branchId.'|'.$row['divisi']] = [
                    'branch_id' => $branchId,
                    'divisi' => $row['divisi'],
                ];
            }
        }

        $mDivisiKeyed = [];
        foreach ($mDivisi as $row) {
            $mDivisiKeyed[$row['m_branch_id'].'|'.$row['divisi']] = $row;
        }

        // Data yang perlu dibuat
        $toCreate = array_diff_key($importKeyed, $mDivisiKeyed);

        // Data yang perlu dihapus
        $toDelete = array_diff_key($mDivisiKeyed, $importKeyed);

        // Data yang sudah ada (tidak perlu update field lain)
        $toUpdate = array_intersect_key($importKeyed, $mDivisiKeyed);

        // Proses create
        foreach ($toCreate as $key => $data) {
            \DB::table('m_divisi')->insert([
                'name' => $data['divisi'],
                'm_branch_id' => $data['branch_id'],
                'nomor' => 0,
                'level' => 0,
                'is_active' => true,
            ]);
        }

        // Proses delete
        foreach ($toDelete as $key => $data) {
            \DB::table('m_divisi')
                ->where('id', $data['id'])
                ->delete();
        }

        return response()->json([
            'created' => array_values($toCreate),
            'deleted' => array_values($toDelete),
            'matched' => array_values($toUpdate),
        ]);
    }

    //import divisi example
    /*{
        "sbu": "PUBLISHING",
        "sub": "Publishing DCP",
        "branch": "DCP Bandung",
        "divisi": "Administrasi Pemasaran",
        "parent_divisi": "Pemasaran"
    },
    */

    // Example of main branches in m_branch table
    //branch "name": "HOLDING MP",

    //Example divisi table
    /*
     "data": {
        "id": 34,
        "m_branch_id": 0,
        "parent_id": null,
        "nomor": "0",
        "name": "Bandung",
        "level": "0",
        "is_active": true,
        "creator_id": null,
        "last_editor_id": null,
        "created_at": null,
        "updated_at": null,
        "is_parent": null,
        "m_branch.id": null,
        "m_branch.m_comp_id": null,
        "m_branch.m_subcomp_id": null,
        "m_branch.code": null,
        "m_branch.name": null,
        "m_branch.province_id": null,
        "m_branch.city_id": null,
        "m_branch.district_id": null,
        "m_branch.address": null,
        "m_branch.npwp": null,
        "m_branch.phone1": null,
        "m_branch.phone2": null,
        "m_branch.email": null,
        "m_branch.is_active": null,
        "m_branch.creator_id": null,
        "m_branch.last_editor_id": null,
        "m_branch.created_at": null,
        "m_branch.updated_at": null,
        "m_branch.deletor_id": null,
        "m_branch.deleted_at": null,
        "parent.id": null,
        "parent.m_branch_id": null,
        "parent.parent_id": null,
        "parent.nomor": null,
        "parent.name": null,
        "parent.level": null,
        "parent.is_active": null,
        "parent.creator_id": null,
        "parent.last_editor_id": null,
        "parent.created_at": null,
        "parent.updated_at": null,
        "parent.is_parent": null,
        "creator.id": null,
        "creator.name": null,
        "creator.email": null,
        "creator.username": null,
        "creator.email_verified_at": null,
        "creator.password": null,
        "creator.m_comp_id": null,
        "creator.m_dir_id": null,
        "creator.is_active": null,
        "creator.creator_id": null,
        "creator.last_editor_id": null,
        "creator.remember_token": null,
        "creator.created_at": null,
        "creator.updated_at": null,
        "creator.profil_image": null,
        "creator.telp": null,
        "creator.m_kary_id": null,
        "creator.user_type": null,
        "creator.note": null,
        "last_editor.id": null,
        "last_editor.name": null,
        "last_editor.email": null,
        "last_editor.username": null,
        "last_editor.email_verified_at": null,
        "last_editor.password": null,
        "last_editor.m_comp_id": null,
        "last_editor.m_dir_id": null,
        "last_editor.is_active": null,
        "last_editor.creator_id": null,
        "last_editor.last_editor_id": null,
        "last_editor.remember_token": null,
        "last_editor.created_at": null,
        "last_editor.updated_at": null,
        "last_editor.profil_image": null,
        "last_editor.telp": null,
        "last_editor.m_kary_id": null,
        "last_editor.user_type": null,
        "last_editor.note": null
    },
    "processed_time": 0.05524
 }
     */

}