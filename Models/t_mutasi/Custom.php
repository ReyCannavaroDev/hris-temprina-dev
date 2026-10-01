<?php

namespace App\Models\CustomModels;

class t_mutasi extends \App\Models\BasicModels\t_mutasi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->required = ["m_kary_id", "tgl", "status_kary_lama_id"];
    }

    public $fileColumns = [
        'file_dokumen'
    ];

    public $details = [
        't_mutasi_d_memperhatikan',
        't_mutasi_d_tembusan'
    ];

    public function t_mutasi_d_memperhatikan()
    {
        return $this->hasMany('App\Models\BasicModels\t_mutasi_d_memperhatikan', 't_mutasi_id', 'id');
    }

    public function t_mutasi_d_tembusan()
    {
        return $this->hasMany('App\Models\BasicModels\t_mutasi_d_tembusan', 't_mutasi_id', 'id');
    }

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function ensureGenerateNumSuratTugas()
    {
        try {
            $formatName = "SURAT TUGAS PELATIHAN";
            $existing = \App\Models\CustomModels\generate_num::where("nama", $formatName)->first();
            if ($existing) {
                return $existing;
            }

            // 1. Siapkan/ambil elemen di generate_num_type
            $getOrMakeType = function($nama, $refType, $value) {
                $type = \App\Models\CustomModels\generate_num_type::where('ref_type', $refType)
                    ->where('value', $value)
                    ->first();
                if (!$type) {
                    $type = \App\Models\CustomModels\generate_num_type::create([
                        'nama' => $nama,
                        'ref_type' => $refType,
                        'value' => $value,
                        'is_active' => true,
                    ]);
                }
                return $type;
            };

            $tSeq3     = $getOrMakeType('SEQUENCE xxx1 (3 Digit)', 'seq', '3');
            $tSlash    = $getOrMakeType('Separator (/)', 'text', '/');
            $tDayIndo  = $getOrMakeType('DAY INDO Title (Sn, Jm, dll)', 'day', 'hari_indo_title');
            $tDot      = $getOrMakeType('Separator (.)', 'text', '.');
            $tDateDmy  = $getOrMakeType('DATE dmy (6 Digit)', 'day', 'dmy');
            $tTmg      = $getOrMakeType('PREFIX /TMG/', 'text', '/TMG/');
            $tCabang   = $getOrMakeType('BRANCH [CABANG]/', 'text', '[CABANG]/');
            $tHrdTr    = $getOrMakeType('SUFFIX HRD/TR', 'text', 'HRD/TR');

            // 2. Buat header generate_num
            $genNum = \App\Models\CustomModels\generate_num::create([
                'nama' => $formatName,
                'comp_id' => 1,
                'is_active' => true,
            ]);

            // 3. Pasang susunan generate_num_det (seq 1..8)
            $details = [
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSeq3->id, 'seq' => 1],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSlash->id, 'seq' => 2],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDayIndo->id, 'seq' => 3],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDot->id, 'seq' => 4],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDateDmy->id, 'seq' => 5],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tTmg->id, 'seq' => 6],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tCabang->id, 'seq' => 7],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tHrdTr->id, 'seq' => 8],
            ];

            foreach ($details as $det) {
                \App\Models\CustomModels\generate_num_det::create($det);
            }

            return $genNum;
        } catch (\Throwable $th) {
            \Log::warning("Gagal auto-init generate_num: " . $th->getMessage());
            return null;
        }
    }

    public function ensureGenerateNumMutasi()
    {
        try {
            $formatName = "SK MUTASI";
            $existing = \App\Models\CustomModels\generate_num::where("nama", $formatName)->first();
            if ($existing) {
                return $existing;
            }

            // 1. Siapkan/ambil elemen di generate_num_type
            $getOrMakeType = function($nama, $refType, $value) {
                $type = \App\Models\CustomModels\generate_num_type::where('ref_type', $refType)
                    ->where('value', $value)
                    ->first();
                if (!$type) {
                    $type = \App\Models\CustomModels\generate_num_type::create([
                        'nama' => $nama,
                        'ref_type' => $refType,
                        'value' => $value,
                        'is_active' => true,
                    ]);
                }
                return $type;
            };

            $tSeq3     = $getOrMakeType('SEQUENCE xxx1 (3 Digit)', 'seq', '3');
            $tSlash    = $getOrMakeType('Separator (/)', 'text', '/');
            $tDayIndo  = $getOrMakeType('DAY INDO Title (Sn, Jm, dll)', 'day', 'hari_indo_title');
            $tDot      = $getOrMakeType('Separator (.)', 'text', '.');
            $tDateDmy  = $getOrMakeType('DATE dmy (6 Digit)', 'day', 'dmy');
            $tTmg      = $getOrMakeType('PREFIX /TMG/', 'text', '/TMG/');
            $tCabang   = $getOrMakeType('BRANCH [CABANG]/', 'text', '[CABANG]/');
            $tHrdMts   = $getOrMakeType('SUFFIX HRD/MTS', 'text', 'HRD/MTS');

            // 2. Buat header generate_num
            $genNum = \App\Models\CustomModels\generate_num::create([
                'nama' => $formatName,
                'comp_id' => 1,
                'is_active' => true,
            ]);

            // 3. Pasang susunan generate_num_det (seq 1..8)
            $details = [
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSeq3->id, 'seq' => 1],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSlash->id, 'seq' => 2],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDayIndo->id, 'seq' => 3],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDot->id, 'seq' => 4],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDateDmy->id, 'seq' => 5],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tTmg->id, 'seq' => 6],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tCabang->id, 'seq' => 7],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tHrdMts->id, 'seq' => 8],
            ];

            foreach ($details as $det) {
                \App\Models\CustomModels\generate_num_det::create($det);
            }

            return $genNum;
        } catch (\Throwable $th) {
            \Log::warning("Gagal auto-init generate_num SK MUTASI: " . $th->getMessage());
            return null;
        }
    }

    public function ensureGenerateNumPromosi()
    {
        try {
            $formatName = "SK PROMOSI";
            $existing = \App\Models\CustomModels\generate_num::where("nama", $formatName)->first();
            if ($existing) {
                return $existing;
            }

            // 1. Siapkan/ambil elemen di generate_num_type
            $getOrMakeType = function($nama, $refType, $value) {
                $type = \App\Models\CustomModels\generate_num_type::where('ref_type', $refType)
                    ->where('value', $value)
                    ->first();
                if (!$type) {
                    $type = \App\Models\CustomModels\generate_num_type::create([
                        'nama' => $nama,
                        'ref_type' => $refType,
                        'value' => $value,
                        'is_active' => true,
                    ]);
                }
                return $type;
            };

            $tSeq3     = $getOrMakeType('SEQUENCE xxx1 (3 Digit)', 'seq', '3');
            $tSlash    = $getOrMakeType('Separator (/)', 'text', '/');
            $tDayIndo  = $getOrMakeType('DAY INDO Title (Sn, Jm, dll)', 'day', 'hari_indo_title');
            $tDot      = $getOrMakeType('Separator (.)', 'text', '.');
            $tDateDmy  = $getOrMakeType('DATE dmy (6 Digit)', 'day', 'dmy');
            $tTmg      = $getOrMakeType('PREFIX /TMG/', 'text', '/TMG/');
            $tCabang   = $getOrMakeType('BRANCH [CABANG]/', 'text', '[CABANG]/');
            $tHrdPrm   = $getOrMakeType('SUFFIX HRD/PRM', 'text', 'HRD/PRM');

            // 2. Buat header generate_num
            $genNum = \App\Models\CustomModels\generate_num::create([
                'nama' => $formatName,
                'comp_id' => 1,
                'is_active' => true,
            ]);

            // 3. Pasang susunan generate_num_det (seq 1..8)
            $details = [
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSeq3->id, 'seq' => 1],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tSlash->id, 'seq' => 2],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDayIndo->id, 'seq' => 3],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDot->id, 'seq' => 4],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tDateDmy->id, 'seq' => 5],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tTmg->id, 'seq' => 6],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tCabang->id, 'seq' => 7],
                ['generate_num_id' => $genNum->id, 'generate_num_type_id' => $tHrdPrm->id, 'seq' => 8],
            ];

            foreach ($details as $det) {
                \App\Models\CustomModels\generate_num_det::create($det);
            }

            return $genNum;
        } catch (\Throwable $th) {
            \Log::warning("Gagal auto-init generate_num SK PROMOSI: " . $th->getMessage());
            return null;
        }
    }

    public function resolveBranchCode($mKaryId, $mBranchId = null)
    {
        $cityMap = [
            'SURABAYA'    => 'SBY',
            'GRESIK'      => 'SBY',
            'WRINGINANOM' => 'SBY',
            'BEKASI'      => 'BKS',
            'JAKARTA'     => 'CGK',
            'CENGKARENG'  => 'CGK',
            'TANGERANG'   => 'CGK',
            'SEMARANG'    => 'SMG',
            'SOLO'        => 'SLO',
            'SURAKARTA'   => 'SLO',
            'MALANG'      => 'MLG',
            'NGANJUK'     => 'NGN',
            'JEMBER'      => 'JBR',
            'BALI'        => 'DPS',
            'DENPASAR'    => 'DPS',
            'BAWEN'       => 'BWN',
            'SUMEDANG'    => 'SMD',
            'MAKASSAR'    => 'MKS',
            'PALEMBANG'   => 'PLB',
        ];

        $candidates = [];
        if ($mBranchId) {
            $branch = \DB::table('m_branch')->where('id', $mBranchId)->first();
            if ($branch) {
                if (!empty($branch->kode)) $candidates[] = $branch->kode;
                if (!empty($branch->name)) $candidates[] = $branch->name;
                if (!empty($branch->kota)) $candidates[] = $branch->kota;
            }
        }

        if ($mKaryId) {
            $kary = \DB::table('m_kary')->where('id', $mKaryId)->first();
            if ($kary && !empty($kary->m_branch_id)) {
                $branchKary = \DB::table('m_branch')->where('id', $kary->m_branch_id)->first();
                if ($branchKary) {
                    if (!empty($branchKary->kode)) $candidates[] = $branchKary->kode;
                    if (!empty($branchKary->name)) $candidates[] = $branchKary->name;
                    if (!empty($branchKary->kota)) $candidates[] = $branchKary->kota;
                }
            }
        }

        foreach ($candidates as $cand) {
            $clean = strtoupper(trim((string)$cand));
            $clean = preg_replace('/^(KOTA|KABUPATEN|KAB\.?)\s+/i', '', $clean);
            $clean = trim($clean);

            if (isset($cityMap[$clean])) {
                return $cityMap[$clean];
            }

            foreach ($cityMap as $cityName => $abbr) {
                if (str_contains($clean, $cityName)) {
                    return $abbr;
                }
            }

            if (strlen($clean) >= 2 && strlen($clean) <= 4 && !in_array($clean, ['HLD', 'HO', 'PST'])) {
                return $clean;
            }
        }

        return 'SBY';
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $tipeMutasi = $arrayData['tipe_mutasi'] ?? null;
        if (empty($tipeMutasi)) {
            $tipeMutasi = 'Non-Mutasi / Persuratan';
            if (!empty($arrayData['jenis_surat'])) {
                $js = m_general::find($arrayData['jenis_surat']);
                if ($js) {
                    $tipeMutasi = $js->value ?? 'Non-Mutasi / Persuratan';
                }
            }
        }

        $isSuratTugas = str_contains(strtoupper($tipeMutasi ?? ''), 'TUGAS') || 
                        str_contains(strtoupper($tipeMutasi ?? ''), 'PELATIHAN');
        $isPromosi = str_contains(strtoupper($tipeMutasi ?? ''), 'PROMOSI');
        $isMutasi = str_contains(strtoupper($tipeMutasi ?? ''), 'MUTASI') ||
                    in_array($tipeMutasi, ['Antar SBU', 'Antar SUB', 'Antar Branch / Cabang', 'Antar Divisi']);

        if (empty($arrayData['nomor'])) {
            $tglSurat = $arrayData['tgl'] ?? date('Y-m-d');
            $branchCode = $this->resolveBranchCode($arrayData['m_kary_id'] ?? null, $arrayData['m_branch_baru_id'] ?? $arrayData['m_branch_lama_id'] ?? null);

            if ($isSuratTugas) {
                $this->ensureGenerateNumSuratTugas();
                try {
                    $nomor = $this->helper->generateNomor(
                        "SURAT TUGAS PELATIHAN", 
                        true, 
                        null, 
                        $tglSurat, 
                        [
                            '[CABANG]' => $branchCode,
                            '{branch}' => $branchCode,
                            '{cabang}' => $branchCode,
                            'SBY' => $branchCode,
                        ]
                    );
                } catch (\Throwable $th) {
                    $hariMap = [0 => 'Mg', 1 => 'Sn', 2 => 'Sl', 3 => 'Rb', 4 => 'Km', 5 => 'Jm', 6 => 'Sb'];
                    $tStamp = strtotime(str_replace('/', '-', $tglSurat)) ?: time();
                    $hari = $hariMap[(int)date('w', $tStamp)] ?? 'Sn';
                    $dmy = date('dmy', $tStamp);
                    $seq = sprintf("%03d", \DB::table('t_mutasi')->count() + 1);
                    $nomor = "{$seq}/{$hari}.{$dmy}/TMG/{$branchCode}/HRD/TR";
                }
            } elseif ($isPromosi) {
                $this->ensureGenerateNumPromosi();
                try {
                    $nomor = $this->helper->generateNomor(
                        "SK PROMOSI", 
                        true, 
                        null, 
                        $tglSurat, 
                        [
                            '[CABANG]' => $branchCode,
                            '{branch}' => $branchCode,
                            '{cabang}' => $branchCode,
                            'SBY' => $branchCode,
                        ]
                    );
                } catch (\Throwable $th) {
                    $hariMap = [0 => 'Mg', 1 => 'Sn', 2 => 'Sl', 3 => 'Rb', 4 => 'Km', 5 => 'Jm', 6 => 'Sb'];
                    $tStamp = strtotime(str_replace('/', '-', $tglSurat)) ?: time();
                    $hari = $hariMap[(int)date('w', $tStamp)] ?? 'Sn';
                    $dmy = date('dmy', $tStamp);
                    $seq = sprintf("%03d", \DB::table('t_mutasi')->count() + 1);
                    $nomor = "{$seq}/{$hari}.{$dmy}/TMG/{$branchCode}/HRD/PRM";
                }
            } elseif ($isMutasi) {
                $this->ensureGenerateNumMutasi();
                try {
                    $nomor = $this->helper->generateNomor(
                        "SK MUTASI", 
                        true, 
                        null, 
                        $tglSurat, 
                        [
                            '[CABANG]' => $branchCode,
                            '{branch}' => $branchCode,
                            '{cabang}' => $branchCode,
                            'SBY' => $branchCode,
                        ]
                    );
                } catch (\Throwable $th) {
                    $hariMap = [0 => 'Mg', 1 => 'Sn', 2 => 'Sl', 3 => 'Rb', 4 => 'Km', 5 => 'Jm', 6 => 'Sb'];
                    $tStamp = strtotime(str_replace('/', '-', $tglSurat)) ?: time();
                    $hari = $hariMap[(int)date('w', $tStamp)] ?? 'Sn';
                    $dmy = date('dmy', $tStamp);
                    $seq = sprintf("%03d", \DB::table('t_mutasi')->count() + 1);
                    $nomor = "{$seq}/{$hari}.{$dmy}/TMG/{$branchCode}/HRD/MTS";
                }
            } else {
                $nomor = $this->helper->generateNomor("KODE MUTASI");
            }
        } else {
            $nomor = $arrayData['nomor'];
        }

        $nomor = strtoupper($nomor);

        $newArrayData = array_merge($arrayData, [
            "nomor" => $nomor,
            "tipe_mutasi" => $tipeMutasi,
            "no_dokumen" => $arrayData['no_dokumen'] ?? $nomor,
            "deskripsi" => $arrayData['deskripsi'] ?? '-',
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = $arrayData;
        if (empty($newArrayData['tipe_mutasi'])) {
            $newArrayData['tipe_mutasi'] = 'Non-Mutasi / Persuratan';
            if (!empty($newArrayData['jenis_surat'])) {
                $js = m_general::find($newArrayData['jenis_surat']);
                if ($js) {
                    $newArrayData['tipe_mutasi'] = $js->value ?? 'Non-Mutasi / Persuratan';
                }
            }
        }

        if (empty($newArrayData['no_dokumen']) && !empty($newArrayData['nomor'])) {
            $newArrayData['no_dokumen'] = $newArrayData['nomor'];
        }

        return [
            "model" => $model,
            "data" => $newArrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $mutasiId = $id ?? $model->id ?? null;
        if ($mutasiId) {
            $this->syncDetails($mutasiId);
        }
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        $mutasiId = $id ?? $model->id ?? null;
        if ($mutasiId) {
            $this->syncDetails($mutasiId);
        }
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        if ($id) {
            \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $id)->delete();
            \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $id)->delete();
        }
        return [
            "model" => $model,
            "data" => $arrayData
        ];
    }

    public function transformRowData(array $row)
    {
        $id = $row['this.id'] ?? $row['id'] ?? null;
        if ($id) {
            $row['t_mutasi_d_memperhatikan'] = \DB::table('t_mutasi_d_memperhatikan')
                ->where('t_mutasi_id', $id)
                ->select('id', 't_mutasi_id', 'value')
                ->get()
                ->toArray();

            $row['t_mutasi_d_tembusan'] = \DB::table('t_mutasi_d_tembusan')
                ->where('t_mutasi_id', $id)
                ->select('id', 't_mutasi_id', 'value')
                ->get()
                ->toArray();
        }
        return $row;
    }

    private function syncDetails($mutasiId)
    {
        $req = app()->request;

        // 1. Sinkronisasi Poin Memperhatikan
        if ($req->has('t_mutasi_d_memperhatikan')) {
            \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $mutasiId)->delete();
            $memperhatikan = $req->t_mutasi_d_memperhatikan;
            if (is_string($memperhatikan)) {
                $decoded = json_decode($memperhatikan, true);
                if (is_array($decoded)) $memperhatikan = $decoded;
            }
            if (is_array($memperhatikan)) {
                foreach ($memperhatikan as $row) {
                    $val = is_array($row) ? ($row['value'] ?? '') : (is_string($row) ? $row : ($row->value ?? ''));
                    $val = trim((string)$val);
                    if ($val !== '') {
                        \DB::table('t_mutasi_d_memperhatikan')->insert([
                            't_mutasi_id' => $mutasiId,
                            'value' => $val,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // 2. Sinkronisasi Tembusan
        if ($req->has('t_mutasi_d_tembusan')) {
            \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $mutasiId)->delete();
            $tembusan = $req->t_mutasi_d_tembusan;
            if (is_string($tembusan)) {
                $decoded = json_decode($tembusan, true);
                if (is_array($decoded)) $tembusan = $decoded;
            }
            if (is_array($tembusan)) {
                foreach ($tembusan as $row) {
                    $val = is_array($row) ? ($row['value'] ?? '') : (is_string($row) ? $row : ($row->value ?? ''));
                    $val = trim((string)$val);
                    if ($val !== '') {
                        \DB::table('t_mutasi_d_tembusan')->insert([
                            't_mutasi_id' => $mutasiId,
                            'value' => $val,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function scopelanding($model)
    {
        return $model->join('m_general', 'm_general.id', 't_mutasi.jenis_surat');
    }

    public function custom_post($request)
    {
        try {
            \DB::beginTransaction();

            $data = t_mutasi::find($request->id);
            if (!$data)
                return response()->json(["error" => "Data tidak ditemukan."], 404);

            $karyawan = m_kary::find($data["m_kary_id"]);
            if (!$karyawan)
                return response()->json(["error" => "Data karyawan tidak ditemukan."], 404);

            // Ambil Kode Tipe Surat dari m_general
            $tipeSurat = m_general::where('group', 'JENIS SURAT')->where('id', $data['jenis_surat'])->first();
            $kodeSurat = strtoupper(trim($tipeSurat ? ($tipeSurat->code ?? '') : ''));
            $namaSurat = strtoupper(trim($tipeSurat ? ($tipeSurat->value ?? '') : ''));

            // Pengelompokan Kategori Surat
            $isCareerMovement = in_array($kodeSurat, ['J02', 'J09', 'J12', 'J05', 'J07']) ||
                               str_contains($namaSurat, 'MUTASI') ||
                               str_contains($namaSurat, 'PROMOSI') ||
                               str_contains($namaSurat, 'DEMOSI') ||
                               str_contains($namaSurat, 'PENGANGKATAN') ||
                               str_contains($namaSurat, 'PENAMBAHAN TUGAS') ||
                               str_contains($namaSurat, 'TUNJANGAN JABATAN');

            $isTermination = str_contains($namaSurat, 'BERAKHIR') ||
                             str_contains($namaSurat, 'PEMBERHENTIAN') ||
                             (str_contains($namaSurat, 'PERJANJIAN BERSAMA') && !empty($data['kompensasi']));

            if ($isCareerMovement) {
                // 1. Logika Update Jabatan
                if (in_array($kodeSurat, ['J12', 'J09', 'J02']) ||
                    str_contains($namaSurat, 'MUTASI') ||
                    str_contains($namaSurat, 'PROMOSI') ||
                    str_contains($namaSurat, 'DEMOSI')) {
                    // DEMOSI (J12), PROMOSI (J09), MUTASI (J02) -> Non-aktifkan jabatan lama
                    m_kary_det_jabatan::where(function ($q) use ($karyawan) {
                        $q->where('m_karyawan_id', $karyawan->id)
                            ->orWhere('m_kary_id', $karyawan->id);
                    })
                        ->where('is_active', true)
                        ->where('is_primary', true)
                        ->update([
                            'end_time' => date('Y-m-d'),
                            'is_primary' => false,
                            'is_active' => false,
                        ]);
                }

                // 2. Tambah Jabatan Baru (Hanya jika posisi/SBU baru diisi)
                if (!empty($data["m_posisi_baru_id"]) || !empty($data["m_sbu_baru_id"])) {
                    $isPrimaryNew = ($kodeSurat === 'J05' || str_contains($namaSurat, 'PENAMBAHAN TUGAS')) ? false : true;
                    $newJabatan = m_kary_det_jabatan::create([
                        'm_kary_id' => $karyawan->id,
                        'm_karyawan_id' => $karyawan->id,
                        'm_comp_id' => $data["m_sbu_baru_id"],
                        'm_subcomp_id' => $data["m_sub_baru_id"],
                        'm_branch_id' => $data["m_branch_baru_id"],
                        'm_divisi_id' => $data["m_divisi_baru_id"],
                        'm_posisi_id' => $data["m_posisi_baru_id"],
                        'start_time' => $data['tgl'],
                        'is_primary' => $isPrimaryNew,
                        'is_active' => true,
                    ]);
                }

                // 3. Logika Update Data Utama Karyawan
                if ($kodeSurat !== 'J05' && !str_contains($namaSurat, 'PENAMBAHAN TUGAS')) {
                    $updateDataKary = [];
                    if (!empty($data["m_sbu_baru_id"])) $updateDataKary["m_comp_id"] = $data["m_sbu_baru_id"];
                    if (!empty($data["m_sub_baru_id"])) $updateDataKary["m_subcomp_id"] = $data["m_sub_baru_id"];
                    if (!empty($data["m_divisi_baru_id"])) $updateDataKary["m_divisi_id"] = $data["m_divisi_baru_id"];
                    if (!empty($data["m_posisi_baru_id"])) $updateDataKary["m_posisi_id"] = $data["m_posisi_baru_id"];
                    if (!empty($data["m_branch_baru_id"])) $updateDataKary["m_branch_baru_id"] = $data["m_branch_baru_id"];

                    // Khusus J07 (PENGANGKATAN) -> Update status karyawan
                    if ($kodeSurat === 'J07' || str_contains($namaSurat, 'PENGANGKATAN')) {
                        if (!empty($data['status_kary_baru_id'])) {
                            $updateDataKary["status_kary_id"] = $data['status_kary_baru_id'];
                        }
                        $updateDataKary["tgl_pengangkatan"] = $data['tgl'];
                    }

                    if (!empty($updateDataKary)) {
                        $karyawan->update($updateDataKary);
                    }
                }

                // 4. Update Jadwal Kerja (Biasanya mengikuti jadwal baru meskipun penambahan tugas)
                $jadwalBaruId = $data['jadwal_kerja_baru_id'] ?? $data['t_jadwal_kerja_baru_id'] ?? null;
                if (!empty($jadwalBaruId)) {
                    t_jadwal_kerja_d_n::where('m_kary_id', $karyawan->id)
                        ->where('status', 'AKTIF')
                        ->update(['status' => 'NON AKTIF']);

                    t_jadwal_kerja_d_n::create([
                        't_jadwal_kerja_n_id' => $jadwalBaruId,
                        'm_subcomp_id' => $data["m_sub_baru_id"],
                        'm_branch_id' => $data["m_branch_baru_id"],
                        'm_divisi_id' => $data["m_divisi_baru_id"],
                        'm_kary_id' => $data['m_kary_id'],
                        'start_date' => $data['tgl'],
                        'desc' => 'AUTO GENERATE FROM ' . ($tipeSurat->value ?? 'MUTASI'),
                        'status' => 'AKTIF'
                    ]);
                }
            } else if ($isTermination) {
                // Pengakhiran Hubungan Kerja: Non-aktifkan jabatan aktif
                m_kary_det_jabatan::where(function ($q) use ($karyawan) {
                    $q->where('m_karyawan_id', $karyawan->id)
                        ->orWhere('m_kary_id', $karyawan->id);
                })
                    ->where('is_active', true)
                    ->update([
                        'end_time' => $data['tgl'],
                        'is_primary' => false,
                        'is_active' => false,
                    ]);

                t_jadwal_kerja_d_n::where('m_kary_id', $karyawan->id)
                    ->where('status', 'AKTIF')
                    ->update(['status' => 'NON AKTIF']);

                try {
                    $karyawan->update(['is_active' => false]);
                } catch (\Throwable $th) {}
            }
            // Catatan: Untuk surat administrasi (Keterangan Kerja, Paklaring, Surat Tugas Pelatihan, PKWT),
            // posisi jabatan aktif karyawan tetap dipertahankan dan TIDAK diubah.

            // Finalize
            $data->update(["status" => "POSTED"]);

            \DB::commit();
            return response()->json(["message" => "Proses " . ($tipeSurat->value ?? 'Surat') . " berhasil diposting."]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(["error" => "Terjadi kesalahan: " . $e->getMessage()], 500);
        }
    }

    // public function custom_post($request)
    // {
    //     try {
    //         // Begin a database transaction
    //         \DB::beginTransaction();

    //         $data = t_mutasi::find($request->id);
    //         if (!$data) {
    //             return response()->json(
    //                 ["error" => "Data tidak ditemukan."],
    //                 404
    //             );
    //         }

    //         $karyawan = m_kary::find($data["m_kary_id"]);
    //         if (!$karyawan) {
    //             return response()->json(
    //                 ["error" => "Data karyawan tidak ditemukan."],
    //                 404
    //             );
    //         }
    //         $update = $data->update([
    //             "status" => "POSTED",
    //         ]);

    //         $updateKary = $karyawan->update([
    //             "m_comp_id" => $data["m_sbu_baru_id"],
    //             "m_subcomp_id" => $data["m_sub_baru_id"],
    //             "m_divisi_id" => $data["m_divisi_baru_id"],
    //             "m_posisi_id" => $data["m_posisi_baru_id"],
    //             "m_branch_id" => $data["m_branch_baru_id"],
    //             "status_kary_id" => $data['status_kary_baru_id']
    //         ]);

    //         $updateLastJabatan = m_kary_det_jabatan::orderBy('start_time', 'desc')->where('m_karyawan_id',$karyawan['id'])->first();
    //         if($updateLastJabatan){
    //             $updateLastJabatan->update([
    //                 'end_time' => date('Y-m-d'),
    //                 'is_primary' => false,
    //             ]);
    //         }

    //         $updateDetailJabatan = m_kary_det_jabatan::create([
    //             'm_karyawan_id' => $karyawan['id'],
    //             'm_comp_id' => $data["m_sbu_baru_id"],
    //             'm_subcomp_id' => $data["m_sub_baru_id"],
    //             'm_branch_id' => $data["m_branch_baru_id"],
    //             'm_divisi_id' => $data["m_divisi_baru_id"],
    //             'm_posisi_id' => $data["m_posisi_baru_id"],
    //             'start_time' => date('Y-m-d'),
    //             'end_time' => null,
    //             'is_primary' => true,
    //             'is_active' => true,
    //         ]);

    //         $updateLastJadwal = t_jadwal_kerja_d_n::orderBy('start_time', 'desc')->where('m_kary_id',$karyawan['id'])->first();
    //         if($updateLastJadwal){
    //             $updateLastJadwal->update([
    //                 // 'end_time' => date('Y-m-d'),
    //                 // 'is_primary' => false,
    //                 'status' => 'NON AKTIF'
    //             ]);
    //         }

    //         $updateJadwal = t_jadwal_kerja_d_n::create([
    //             't_jadwal_kerja_n_id' => $data['t_jadwal_kerja_baru_id'],
    //             'm_subcomp_id' => $data["m_sub_baru_id"],
    //             'm_branch_id' => $data["m_branch_baru_id"],
    //             'm_divisi_id' => $data["m_divisi_baru_id"],
    //             'm_kary_id' => $data['m_kary_id'],
    //             'start_date' => $data['tgl'],
    //             'desc' => 'AUTO GENERATE FROM MUTASI',
    //             'status' => 'AKTIF'
    //         ]);

    //         if ($update && $updateKary && $updateDetailJabatan && $updateLastJadwal && $updateJadwal) {
    //             // If both updates are successful, commit the transaction
    //             \DB::commit();

    //             return response()->json([
    //                 "message" => "Data berhasil diposting.",
    //             ]);
    //         } else {
    //             // If any update fails, rollback the transaction
    //             \DB::rollBack();

    //             return response()->json(
    //                 ["error" => "Gagal memperbarui status."],
    //                 500
    //             );
    //         }
    //     } catch (\Exception $e) {
    //         // Handle exception, log error messages, etc.

    //         // Rollback the transaction in case of any exception
    //         \DB::rollBack();

    //         return response()->json(
    //             ["error" => "Terjadi kesalahan: " . $e->getMessage()],
    //             500
    //         );
    //     }
    // }
}
