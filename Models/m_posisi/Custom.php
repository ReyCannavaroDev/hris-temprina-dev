<?php

namespace App\Models\CustomModels;

class m_posisi extends \App\Models\BasicModels\m_posisi
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
        $newArrayData = array_merge($arrayData, [
            "kode" => $this->helper->generateNomor("KODE POSISI"),
            "comp_id" => auth()->user()->comp_id ?? 0,
        ]);
        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function m_level_posisi_d()
    {
        return $this->hasMany(m_level_posisi_d::class, "m_posisi_id", "id");
    }

    public function m_kary_det_jabatan()
    {
        return $this->hasMany(m_kary_det_jabatan::class, "m_posisi_id", "id");
    }

    public function scopelevel($model)
    {
        return $model->whereDoesntHave("m_level_posisi_d");
    }

    // public function scopeJoinWithDetail($model){
    //     return $model->leftjoin('m_posisi_d as pd', 'pd.m_posisi_id','m_posisi.id')
    //     ->select('m_posisi.id','pd.id as id_detail','desc_kerja','pd.sub_jabatan');
    // }
    public function scopeJoinChild($model)
    {
        return $model
            ->selectRaw("m_posisi.id, m_posisi.desc_kerja, NULL as detail")
            ->where("m_posisi.is_active", true)
            ->unionAll(
                \DB::table("m_posisi")
                    ->selectRaw(
                        "m_posisi.id, m_posisi.desc_kerja, COALESCE(pd.sub_jabatan, NULL) as detail"
                    )
                    ->leftJoin(
                        "m_posisi_d as pd",
                        "m_posisi.id",
                        "=",
                        "pd.m_posisi_id"
                    )
                    ->where("m_posisi.is_active", true)
            )
            ->orderBy("id")
            ->orderByRaw("detail IS NULL DESC, detail");
    }

    public function scopeGetValueGenOld($query)
    {

        return $query
            ->from("m_posisi")
            ->select("m_posisi.*", "m_gen.value as value")
            ->leftJoin("m_general as m_gen","m_gen.id","=","m_posisi.m_divisi_id")
            ->join('m_level_posisi_d as lpd', 'lpd.m_posisi_id', 'm_posisi.id')
            ->join('m_level_posisi as lp', 'lpd.m_level_posisi_id', 'lp.id')
            ->select('m_posisi.*', 'lp.*', 'm_gen.*');
    }

    public function scopeGetValueGen($model)
    {

        return $model
            ->leftJoin("m_general as m_gen","m_gen.id","=","m_posisi.m_divisi_id")
            ->leftjoin('m_level_posisi_d as lpd', 'lpd.m_posisi_id', 'm_posisi.id')
            ->leftjoin('m_level_posisi as lp', 'lpd.m_level_posisi_id', 'lp.id')
            ->select('m_posisi.*', 'lp.level_name', 'm_gen.value');
    }

    public function custom_join_child()
    {
        $data = \DB::table(
            \DB::raw("
            (
                -- Parent Row (Original `desc_kerja`)
                SELECT m_posisi.id, m_posisi.desc_kerja, NULL AS child_id, 'parent' AS row_type
                FROM m_posisi
                WHERE m_posisi.is_active = true

                UNION ALL

                -- Child Rows (Use `detail` as `desc_kerja`, Include `pd.id` as `child_id`)
                SELECT m_posisi.id, pd.sub_jabatan AS desc_kerja, pd.id AS child_id, 'child' AS row_type
                FROM m_posisi
                LEFT JOIN m_posisi_d AS pd ON m_posisi.id = pd.m_posisi_id
                WHERE m_posisi.is_active = true
                AND pd.sub_jabatan IS NOT NULL  -- ✅ Exclude NULL child rows
            ) AS subquery
        ")
        )
            ->orderBy("id")
            ->orderByRaw("CASE WHEN row_type = 'parent' THEN 0 ELSE 1 END")
            ->get();

        return response()->json($data);
    }

    public function custom_import_excel($req)
    {
        $validator = \Validator::make($req->all(), [
            "file" => "required|mimes:xls,xlsx",
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => "Wrong format"], 401);
        }

        $collection = \Excel::toCollection(null, $req->file("file"));

        $rows = $collection[0];

        $headers = $rows[0];

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $data[] = $headers->combine($row)->toArray();
        }
        $data = array_map(function ($item) {
            unset($item[""]);
            return $item;
        }, $data);

        $collect = collect($data);
        $groupByDivisi = $collect->groupBy("divisi");
        return $groupByDivisi;
        return response()->json(
            ["success" => "File imported successfully"],
            200
        );
    }

    public function custom_get_import_jabatan()
    {
        return $this->syncImportJabatanToMposisi();
    }

    //contoh data
    /*
     {
        "divisi": "INFORMATION TECHNOLOGY",
        "jabatan": "KADIV IT"
    },
     */

    public function getFileImportJabatan()
    {
        return $importDivisi = \DB::table("import.jabatan")
            ->select(["divisi", "jabatan"])
            ->get()
            ->map(function ($row) {
                return [
                    "divisi" => strtoupper(trim($row->divisi)),
                    "jabatan" => strtoupper(trim($row->jabatan)),
                ];
            })
            ->unique(function ($item) {
                return $item["divisi"] . "|" . $item["jabatan"];
            })
            ->values()
            ->toArray();
    }

    public function checkImportJabatanMatch()
    {
        // Ambil data import jabatan
        $importJabatan = \DB::table("import.jabatan")
            ->select(["divisi", "jabatan"])
            ->get()
            ->map(function ($row) {
                return [
                    "divisi" => strtoupper(trim($row->divisi)),
                    "jabatan" => strtoupper(trim($row->jabatan)),
                ];
            })
            ->groupBy("divisi");

        // Ambil mapping divisi ke id
        $divisiMap = \DB::table("m_divisi")
            ->select(["id", "name"])
            ->get()
            ->mapWithKeys(function ($row) {
                return [strtoupper(trim($row->name)) => $row->id];
            })
            ->toArray();

        // Ambil data m_posisi
        $mPosisi = \DB::table("m_posisi")
            ->select(["id", "name", "m_divisi_id"])
            ->get()
            ->map(function ($row) use ($divisiMap) {
                // Cari nama divisi dari id
                $divisiName = array_search($row->m_divisi_id, $divisiMap);
                return [
                    "divisi" => $divisiName ? $divisiName : null,
                    "jabatan" => strtoupper(trim($row->name)),
                ];
            })
            ->filter(function ($row) {
                return $row["divisi"] !== null;
            })
            ->groupBy("divisi");

        $result = [];
        $allDivisi = collect($importJabatan->keys())
            ->merge($mPosisi->keys())
            ->unique();

        foreach ($allDivisi as $divisi) {
            $importJabs = $importJabatan
                ->get($divisi, collect())
                ->pluck("jabatan")
                ->unique()
                ->toArray();
            $mPosisiJabs = $mPosisi
                ->get($divisi, collect())
                ->pluck("jabatan")
                ->unique()
                ->toArray();

            $matched = array_values(array_intersect($importJabs, $mPosisiJabs));
            $only_in_import = array_values(
                array_diff($importJabs, $mPosisiJabs)
            );
            $only_in_m_posisi = array_values(
                array_diff($mPosisiJabs, $importJabs)
            );

            $result[] = [
                "divisi" => $divisi,
                "matched" => $matched,
                "only_in_import" => $only_in_import,
                "only_in_m_posisi" => $only_in_m_posisi,
            ];
        }

        return response()->json($result);
    }

    /**
     * Cek apakah semua divisi di m_divisi sudah ada di import.jabatan
     * Return: divisi yang ada di m_divisi tapi tidak ada di import.jabatan
     */
    public function checkDivisiInImportJabatan()
    {
        // Ambil semua nama divisi dari m_divisi
        $divisiDb = \DB::table("m_divisi")
            ->select("name")
            ->get()
            ->map(function ($row) {
                return strtoupper(trim($row->name));
            })
            ->unique()
            ->toArray();

        // Ambil semua nama divisi dari import.jabatan
        $divisiImport = \DB::table("import.jabatan")
            ->select("divisi")
            ->get()
            ->map(function ($row) {
                return strtoupper(trim($row->divisi));
            })
            ->unique()
            ->toArray();

        $not_in_import = array_values(array_diff($divisiDb, $divisiImport));
        $in_both = array_values(array_intersect($divisiDb, $divisiImport));

        return response()->json([
            "not_in_import" => $not_in_import,
            "in_both" => $in_both,
        ]);
    }

    /**
     * Bandingkan divisi dari import.jabatan dengan m_divisi.
     * Return: divisi hanya di import, hanya di m_divisi, dan yang sama.
     */
    public function compareImportJabatanWithMdivisi()
    {
        // Ambil semua nama divisi dari import.jabatan
        $importDivisi = \DB::table("import.jabatan")
            ->select("divisi")
            ->get()
            ->map(function ($row) {
                return strtoupper(trim($row->divisi));
            })
            ->unique()
            ->toArray();

        // Ambil semua nama divisi dari m_divisi
        $mDivisi = \DB::table("m_divisi")
            ->select("name")
            ->get()
            ->map(function ($row) {
                return strtoupper(trim($row->name));
            })
            ->unique()
            ->toArray();

        $only_in_import = array_values(array_diff($importDivisi, $mDivisi));
        $only_in_m_divisi = array_values(array_diff($mDivisi, $importDivisi));
        $same_in_both = array_values(array_intersect($importDivisi, $mDivisi));

        return response()->json([
            "only_in_import" => $only_in_import,
            "only_in_m_divisi" => $only_in_m_divisi,
            "same_in_both" => $same_in_both,
        ]);
    }

    /**
     * Sinkronisasi data m_posisi dengan import.jabatan:
     * - Create/update berdasarkan name (jabatan) dan divisi (case-insensitive)
     * - Field: name, m_divisi_id, is_active=true, level=0, nomor=0
     * - Tidak ada proses delete
     */
    public function syncImportJabatanToMposisi()
    {
        // Ambil mapping divisi ke id (case-insensitive)
        $divisiMap = \DB::table("m_divisi")
            ->select(["id", "name"])
            ->get()
            ->mapWithKeys(function ($row) {
                return [strtoupper(trim($row->name)) => $row->id];
            })
            ->toArray();

        // Ambil data import jabatan
        $importJabatan = \DB::table("import.jabatan")
            ->select(["divisi", "jabatan"])
            ->get()
            ->map(function ($row) {
                return [
                    "divisi" => strtoupper(trim($row->divisi)),
                    "jabatan" => strtoupper(trim($row->jabatan)),
                ];
            })
            ->unique(function ($item) {
                return $item["divisi"] . "|" . $item["jabatan"];
            })
            ->values()
            ->toArray();

        $created = [];
        $updated = [];
        foreach ($importJabatan as $row) {
            $divisiId = $divisiMap[$row["divisi"]] ?? null;
            if (!$divisiId) {
                continue; // skip jika divisi tidak ditemukan di m_divisi
            }

            // Cek apakah sudah ada di m_posisi (case-insensitive pada name dan m_divisi_id)
            $existing = \DB::table("m_posisi")
                ->where("m_divisi_id", $divisiId)
                ->whereRaw("UPPER(name) = ?", [$row["jabatan"]])
                ->first();

            $data = [
                "name" => $row["jabatan"],
                "m_divisi_id" => $divisiId,
                "is_active" => true,
                "level" => 0,
                "nomor" => 0,
            ];

            if ($existing) {
                \DB::table("m_posisi")
                    ->where("id", $existing->id)
                    ->update($data);
                $updated[] = $data;
            } else {
                \DB::table("m_posisi")->insert($data);
                $created[] = $data;
            }
        }

        return response()->json([
            "created" => $created,
            "updated" => $updated,
        ]);
    }

    public function scopegetbykary()
    {
        $m_kary_id = app()->request->m_kary_id;

        $data = m_posisi::whereHas('m_kary_det_jabatan', function($q) use ($m_kary_id){
            $q->where('m_karyawan_id', $m_kary_id);
        })->select('id', 'name');

        return $data;
    }

    public function scopegetbylevel($model)
    {
        $m_level_posisi_id = app()->request->m_level_posisi_id;

         $data = $model->whereHas('m_level_posisi_d', function($q) use ($m_level_posisi_id){
            $q->where('m_level_posisi_id', $m_level_posisi_id);
        });

        return $data;
    }

    public function custom_destroy($req)
    {
        $id = $req->id;
        
        $delete = $this
            ->where('id', $id)
            ->delete();

        if ($delete) {
            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        }

        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
    }

    
}
