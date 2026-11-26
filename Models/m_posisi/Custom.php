<?php

namespace App\Models\CustomModels;

class m_posisi extends \App\Models\BasicModels\m_posisi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->joins = [
            "default_users.id=m_posisi.creator_id",
            "default_users.id=m_posisi.last_editor_id",
            "m_divisi.id=m_posisi.m_divisi_id"
        ];
    }

    public $fileColumns = [
        /*file_column*/
    ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $name = trim($arrayData['name'] ?? '');
        $divisiId = $arrayData['m_divisi_id'] ?? null;

        if (!empty($name)) {
            $exists = \DB::table('m_posisi')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->when($divisiId, function($q) use ($divisiId) {
                    $q->where('m_divisi_id', $divisiId);
                })
                ->exists();

            if ($exists) {
                return [
                    "model" => $model,
                    "data" => $arrayData,
                    "errors" => ["Nama Jabatan '{$name}' sudah terdaftar pada divisi ini!"]
                ];
            }
        }

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

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $name = trim($arrayData['name'] ?? '');
        $divisiId = $arrayData['m_divisi_id'] ?? null;

        if (!empty($name)) {
            $exists = \DB::table('m_posisi')
                ->where('id', '!=', $id)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->when($divisiId, function($q) use ($divisiId) {
                    $q->where('m_divisi_id', $divisiId);
                })
                ->exists();

            if ($exists) {
                return [
                    "model" => $model,
                    "data" => $arrayData,
                    "errors" => ["Nama Jabatan '{$name}' sudah terdaftar pada divisi ini!"]
                ];
            }
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function m_divisi() :\Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('\App\Models\BasicModels\m_divisi', 'm_divisi_id', 'id');
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
        $req = app()->request;

        $query = $model
            ->leftJoin("m_divisi as d", "d.id", "=", "m_posisi.m_divisi_id")
            ->leftJoin("m_general as m_gen", function ($join) {
                $join->on(\DB::raw('CAST(d.name AS text)'), '=', \DB::raw('CAST(m_gen.id AS text)'))
                     ->where(function($w) {
                         $w->where('m_gen.group', 'MASTER_DIVISI')
                           ->orWhere('m_gen.group', 'DIVISI');
                     });
            })
            ->leftJoin("m_general as gen_direct", function ($join) {
                $join->on("gen_direct.id", "=", "m_posisi.m_divisi_id")
                     ->where(function($w) {
                         $w->where('gen_direct.group', 'MASTER_DIVISI')
                           ->orWhere('gen_direct.group', 'DIVISI');
                     });
            })
            ->leftJoin("m_level_posisi_d as lpd", "lpd.m_posisi_id", "=", "m_posisi.id")
            ->leftJoin("m_level_posisi as lp", "lpd.m_level_posisi_id", "=", "lp.id")
            ->select(
                "m_posisi.*",
                "lp.level_name",
                "lp.sequence",
                \DB::raw("COALESCE(m_gen.value, d.name_old, gen_direct.value, CAST(d.name AS text), '') as value"),
                \DB::raw("COALESCE(m_gen.value, d.name_old, gen_direct.value, CAST(d.name AS text), '') as divisi_name")
            );

        // Filter Kolom Nama Jabatan (mendukung filter_name, name, filter_nama, nama)
        $filterName = $req->filter_name ?? $req->name ?? $req->filter_nama ?? $req->nama ?? null;
        if (!empty($filterName)) {
            $query->where("m_posisi.name", "ILIKE", "%" . trim($filterName) . "%");
        }

        // Filter Kolom Divisi
        $filterDivisi = $req->filter_value ?? $req->filter_divisi ?? $req->divisi ?? null;
        if (!empty($filterDivisi)) {
            $keywordDiv = trim($filterDivisi);
            $query->where(function($q) use ($keywordDiv) {
                $q->where("m_gen.value", "ILIKE", "%{$keywordDiv}%")
                  ->orWhere("d.name_old", "ILIKE", "%{$keywordDiv}%")
                  ->orWhere("gen_direct.value", "ILIKE", "%{$keywordDiv}%");
            });
        }

        // Filter Kolom Level Jabatan
        $filterLevel = $req->filter_level_name ?? $req->level_name ?? null;
        if (!empty($filterLevel)) {
            $query->where("lp.level_name", "ILIKE", "%" . trim($filterLevel) . "%");
        }

        // Global Search (Ctrl+F)
        if ($req->filled('search')) {
            $keyword = trim($req->search);
            $query->where(function ($q) use ($keyword) {
                $q->where("m_posisi.name", "ILIKE", "%{$keyword}%")
                  ->orWhere("m_gen.value", "ILIKE", "%{$keyword}%")
                  ->orWhere("lp.level_name", "ILIKE", "%{$keyword}%")
                  ->orWhere("d.name_old", "ILIKE", "%{$keyword}%");
            });
        }

        return $query;
    }

    public function transformRowData(array $row)
    {
        if (!empty($row['m_divisi_id'])) {
            $div = \DB::table('m_divisi')->where('id', $row['m_divisi_id'])->first();
            if ($div) {
                $divName = null;
                $gen = \DB::table('m_general')
                    ->whereRaw('CAST(id AS text) = ?', [strval($div->name)])
                    ->where(function($w) {
                        $w->where('group', 'MASTER_DIVISI')
                          ->orWhere('group', 'DIVISI');
                    })
                    ->first();
                if ($gen && !empty($gen->value)) {
                    $divName = $gen->value;
                } elseif (!empty($div->name_old)) {
                    $divName = $div->name_old;
                } else {
                    $divName = $div->name;
                }
                $row['divisi_name'] = $divName;
                $row['m_divisi.name'] = $divName;
                $row['value'] = $divName;
            }
        }
        return $row;
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

        if (!$m_kary_id || !is_numeric($m_kary_id)) {
            return m_posisi::whereRaw("1 = 0")->select('m_posisi.id', 'm_posisi.name');
        }

        $kary = \DB::table('m_kary')->where('id', $m_kary_id)->first();
        $posisiIds = [];
        if ($kary && $kary->m_posisi_id) {
            $posisiIds[] = $kary->m_posisi_id;
        }

        $detPosisiIds = \DB::table('m_kary_det_jabatan')
            ->where(function ($q) use ($m_kary_id) {
                $q->where('m_karyawan_id', $m_kary_id)
                  ->orWhere('m_kary_id', $m_kary_id);
            })
            ->pluck('m_posisi_id')
            ->toArray();

        $allPosisiIds = array_unique(array_filter(array_merge($posisiIds, $detPosisiIds)));

        if (empty($allPosisiIds)) {
            return m_posisi::whereRaw("1 = 0")->select('m_posisi.id', 'm_posisi.name');
        }

        return m_posisi::whereIn('id', $allPosisiIds)->select('id', 'name');
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
