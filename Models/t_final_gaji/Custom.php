<?php

namespace App\Models\CustomModels;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
Carbon::setLocale("id");

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class t_final_gaji extends \App\Models\BasicModels\t_final_gaji
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

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $req = app()->request;
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE FINAL GAJI"),
        ]);

        // $model["periode_awal"] = Carbon::createFromFormat('d/m/Y',  $req["periode_awal"]);
        // $model["periode_akhir"] = Carbon::createFromFormat('d/m/Y',  $req["periode_akhir"]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_tes()
    {
        $det = t_final_gaji_det::with([
            "t_final_gaji_det_rincian" => function ($query) {
                $query->where("t_potongan_id", "!=", null);
            },
        ])
            ->where("t_final_gaji_id", 1)
            ->get();

        return response()->json($det);
    }

    public function custom_postData($request)
    {
        \DB::beginTransaction();
        $data = t_final_gaji::find($request->id);

        if (!$data) {
            return response()->json(["error" => "Data tidak ditemukan."], 404);
        }

        try {
            $update = $data->update([
                "status" => "POSTED",
            ]);
            $create = true;
            $det = t_final_gaji_det::with([
                "t_final_gaji_det_rincian" => function ($query) {
                    $query->where("t_potongan_id", "!=", null);
                },
            ])
                ->where("t_final_gaji_id", $request->id)
                ->get();
            foreach ($det as $dt) {
                foreach ($dt["t_final_gaji_det_rincian"] as $dt1) {
                    $potongan = t_potongan::where(
                        "id",
                        $dt1["t_potongan_id"]
                    )->first();
                    $create =
                        t_potongan_det_bayar::create([
                            "m_potongan_id" => $dt1["t_potongan_id"],
                            "t_final_gaji_id" => $request->id,
                            "percentage" => $potongan["percentage"],
                            "nilai" => $dt1["value"],
                            "paid_at" => \Carbon::now(),
                        ]) && $create;
                }
                // if($create){
                //     $cekAngsuran = t_potongan_det_bayar::where('t_potongan_id')
                // }
            }

            if ($update && $create) {
                \DB::commit();
                return response()->json([
                    "message" => "Data berhasil diposting.",
                ]);
            } else {
                \DB::rollback();
                return response()->json(
                    ["error" => "Gagal memperbarui status."],
                    500
                );
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            // Handle exception, log error messages, etc.
            return response()->json(
                ["error" => "Terjadi kesalahan: " . $e->getMessage()],
                500
            );
        }
    }

    //report excel

    public function custom_exportBNI($req)
    {
        try {
            $fileName =
                "export-gaji-bni-" . Carbon::now()->format("YmdHis") . ".csv";

            $t_final_gaji_id = $req->f_id ?? 0;
            $karyawan = t_final_gaji_det::where(
                "t_final_gaji_id",
                $t_final_gaji_id
            )->get();
            $branch = m_branch::findOrFail($req->m_branch_id);

            $jumlahKaryawan = $karyawan->count();
            $tanggalExport = Carbon::now()->format("Y/m/d_H:i:s");
            $tanggalGaji = Carbon::now()->format("Ymd");
            $noRekSumber = $branch->no_rek_cabang ?? "2911777557";
            $totalGaji = (int) $karyawan->sum(fn($k) => $k->total_gaji ?? 0);

            return response()->streamDownload(
                function () use (
                    $tanggalExport,
                    $jumlahKaryawan,
                    $tanggalGaji,
                    $noRekSumber,
                    $totalGaji,
                    $karyawan
                ) {
                    $file = fopen("php://output", "w");

                    // Baris 1
                    fputcsv($file, [$tanggalExport, $jumlahKaryawan]);

                    // Baris 2
                    fputcsv($file, [
                        "P",
                        $tanggalGaji,
                        $noRekSumber,
                        $karyawan->count(),
                        $totalGaji,
                    ]);

                    // Baris 3 dst
                    foreach ($karyawan as $row) {
                        fputcsv($file, [
                            $row->m_kary?->m_kary_det_pemb?->first()?->no_rek,
                            $row->m_kary?->m_kary_det_pemb?->first()
                                ?->atas_nama_rek,
                            (int) $row->total_gaji ?? 0,
                            \Carbon\Carbon::parse(
                                $row->periode
                            )->translatedFormat("F Y"),
                        ]);
                    }

                    fclose($file);
                },
                $fileName,
                [
                    "Content-Type" => "text/csv; charset=UTF-8",
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                ["error" => "Terjadi kesalahan: " . $e->getMessage()],
                500
            );
        }
    }

    public function public_exportPajakRekonsiliasi($req)
    {
        try {
            // 1. Handle Multi f_id (Support array JSON [33,34] atau string comma-separated 33,34)
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            $branch_id = $req->m_branch_id;
            // 2. Load Data dengan Eager Loading lengkap
            $karyawan = t_final_gaji_det::with([
                "m_kary.m_divisi",
                "m_kary.m_branch",
                "m_kary.m_subcomp.m_company",
                "m_kary.tanggungan",
                "t_final_gaji_det_rincian",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            // 3. Grouping berdasarkan Cabang
            $groupedByBranch = $karyawan->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];
            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = [
                    "BULAN" => '', "CABANG" => "CABANG: " . strtoupper($branchName), "NO" => '', "NIP" => '', "NAMA" => '', "STATUS" => '', 
                    "THP_FINAL" => '', "GAJI_POKOK" => '', "BONUS" => '', "LEMBUR" => '', "SANKSI" => '', "LAIN" => '', 
                    "ASKES" => '', "RAWAT_INAP" => '', "BPJS_KES" => '', "JKK" => '', "JKM" => '', "PERDIN" => '', 
                    "BPJS_PENSIUN" => '', "JHT_TK" => '', "POT_PROD" => '', "SANKSI_CETAK" => '', "T_JABATAN" => '',"T_FUNGSIONAL" => '', "T_PPH" => '',"PPH21" => ''
                ];

                // Akumulasi Total Cabang
                $bt = ["thp" => 0, "gp" => 0, "bonus" => 0, "lembur" => 0, "sanksi" => 0, "lain" => 0, "askes" => 0, "ri" => 0, "bkes" => 0, "jkk" => 0, "jkm" => 0, "perdin" => 0, "bp" => 0, "jht" => 0, "prod" => 0, "sc" => 0, "tunjab" => 0, "tunfung" => 0];

                foreach ($itemsInBranch as $row) {
                    $rincian = $row->t_final_gaji_det_rincian;
                    $periodeGaji = \Carbon\Carbon::parse($row->t_final_gaji->periode_akhir);
                    $bulan = $periodeGaji->month;
                    $tahun = $periodeGaji->year;
                    $karyawanId = $row->m_kary_id;
                    $m_company = $row->m_kary?->m_subcomp?->m_company;

                    // 1. Gaji Pokok & THP Final
                    $valGajiPokok = $rincian->where("name", "gaji_pokok")->sum("value");
                    $val_tunjab = $rincian->where("name", "tunjangan_posisi")->sum("value");
                    $val_tunfung = $rincian->where("name", "tunjangan_fungsional")->sum("value");

                    $valThpFinal = $rincian->filter(function($i) {
                        $name = strtolower($i->name);
                        return $i->factor === '+' 
                            && !str_contains($name, 'bonus') 
                            && !str_contains($name, 'lembur') 
                            && !str_contains($name, 'perdin') 
                            && !str_contains($name, 'askes');
                    })->sum("value");

                    // 2. Iuran Perusahaan (Hanya jika ada potongan karyawan terkait)
                    $hasBpjsKes = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->count() > 0;
                    $hasBpjsTk  = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'jht') || str_contains(strtolower($i->name), 'bpjs ketenagakerjaan'))->count() > 0;
                    $hasBpjsPen = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs pensiun'))->count() > 0;

                    $komponenBase = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
                    $baseUpah = (int) $rincian->whereIn("name", $komponenBase)->sum("value");

                    $valBpjsKes = $hasBpjsKes ? round($baseUpah * 0.04) : 0;
                    $valJkk     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkk ?? 0.24) / 100) * $baseUpah) : 0;
                    $valJkm     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkm ?? 0.3) / 100) * $baseUpah) : 0;
                    $valJht     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jht ?? 3.7) / 100) * $baseUpah) : 0;
                    $valBpjsPen = $hasBpjsPen ? round(((float) ($m_company?->iuran_jp ?? 2.0) / 100) * $baseUpah) : 0;

                    // 3. Rawat Inap & Komponen Lain
                    //$valRawatInap = ($row->m_kary->plafond_ranap ?? 0) / 12;
                    $tahunGaji = $periodeGaji->year;
                    $ranap = t_plafond_ranap::where('m_kary_id', $row->m_kary?->id)->where('tahun', $tahunGaji)->first();
                    $valKlaimRanap = \DB::table('t_klaim_ranap')
                        ->where('m_kary_id', $karyawanId)
                        ->where('status', 'POSTED')
                        ->whereMonth('periode_akhir', $bulan)
                        ->whereYear('periode_akhir', $tahun)
                        ->sum('total_nominal') ?? 0;
                    $valRawatInap = (($ranap?->plafond ?? 0) / 12) + $valKlaimRanap;

                    $valBonus  = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "bonus"))->sum("value");
                    $valLembur = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "lembur"))->sum("value");
                    $valSanksi = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "terlambat"))->sum("value");
                    $valAbsen  = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "tidak masuk kerja"))->sum("value");
                    
                    $valPotProd = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), "produktifitas"))->sum("value");
                    $valPotSalah = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), "kesalahan kerja"))->sum("value");

                    $valAskes  = t_klaim_askes::where("m_kary_id", $karyawanId)->where("status", "POSTED")->whereMonth("periode_akhir", $bulan)->whereYear("periode_akhir", $tahun)->sum("total_nominal");
                    $valPerdin = t_penyelesaian_perdin::join("t_perdin", "t_penyelesaian_perdin.t_perdin_id", "=", "t_perdin.id")
                                ->where("t_perdin.m_kary_id", $karyawanId)->where("t_penyelesaian_perdin.status", "APPROVED")->whereMonth("t_perdin.date_to", $bulan)->whereYear("t_perdin.date_to", $tahun)->sum("t_penyelesaian_perdin.total_biaya");

                    $rows[] = [
                        "BULAN" => $periodeGaji->format("Ym"),
                        "CABANG" => $branchName,
                        "NO" => $row->m_kary->no_registrasi ?? "-",
                        "NIP" => $row->m_kary->nip ?? "-",
                        "NAMA" => $row->m_kary->nama_lengkap ?? "-",
                        "STATUS" => $row->m_kary->tanggungan?->value ?? "-",
                        "THP_FINAL" => $valThpFinal,
                        "GAJI_POKOK" => $valGajiPokok,
                        "BONUS" => $valBonus,
                        "LEMBUR" => $valLembur,
                        "SANKSI" => $valSanksi,
                        "LAIN" => $valAbsen,
                        "ASKES" => $valAskes,
                        "RAWAT_INAP" => $valRawatInap,
                        "BPJS_KES" => $valBpjsKes,
                        "JKK" => $valJkk,
                        "JKM" => $valJkm,
                        "PERDIN" => $valPerdin,
                        "BPJS_PENSIUN" => $valBpjsPen,
                        "JHT_TK" => $valJht,
                        "POT_PROD" => $valPotProd,
                        "SANKSI_CETAK" => $valPotSalah,
                        "T_JABTAN" => $val_tunjab,
                        "T_FUNGSIONAL" => $val_tunfung,
                        "T_PPH" => 0,
                        "PPH21" => 0,
                    ];

                    // Update Akumulasi Branch
                    $bt['thp'] += $valThpFinal; $bt['gp'] += $valGajiPokok; $bt['bonus'] += $valBonus; $bt['lembur'] += $valLembur; $bt['sanksi'] += $valSanksi;
                    $bt['lain'] += $valAbsen; $bt['askes'] += $valAskes; $bt['ri'] += $valRawatInap; $bt['bkes'] += $valBpjsKes; $bt['jkk'] += $valJkk; $bt['jkm'] += $valJkm;
                    $bt['perdin'] += $valPerdin; $bt['bp'] += $valBpjsPen; $bt['jht'] += $valJht; $bt['prod'] += $valPotProd; $bt['sc'] += $valPotSalah;
                    $bt['tunjab'] += $val_tunjab;
                    $bt['tunfung'] += $val_tunfung;
                }

                // Row Total Cabang
                $rows[] = [
                    "BULAN" => '', "CABANG" => "TOTAL " . strtoupper($branchName), "NO" => '', "NIP" => '', "NAMA" => '', "STATUS" => '', 
                    "THP_FINAL" => $bt['thp'], "GAJI_POKOK" => $bt['gp'], "BONUS" => $bt['bonus'], "LEMBUR" => $bt['lembur'], "SANKSI" => $bt['sanksi'], "LAIN" => $bt['lain'], 
                    "ASKES" => $bt['askes'], "RAWAT_INAP" => $bt['ri'], "BPJS_KES" => $bt['bkes'], "JKK" => $bt['jkk'], "JKM" => $bt['jkm'], "PERDIN" => $bt['perdin'], 
                    "BPJS_PENSIUN" => $bt['bp'], "JHT_TK" => $bt['jht'], "POT_PROD" => $bt['prod'], "SANKSI_CETAK" => $bt['sc'], "T_JABATAN" => $bt['tunjab'], "T_FUNGSIONAL" => $bt['tunfung'], "T_PPH" => 0, "PPH21" => 0
                ];
                $rows[] = ["BULAN" => '']; // Spacer
            }

            // Penamaan File
            $firstId = reset($ids);
            $fileDesc = count($ids) > 1 ? "MULTIPLE-PERIODE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-pajak-rekonsiliasi-" . $fileDesc . "-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return \Maatwebsite\Excel\Facades\Excel::download(new class (collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithColumnFormatting, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["Bulan", "Cabang", "No", "NIP", "Nama", "Status", "THP Final", "Gaji Pokok", "Bonus", "Lembur", "Sanksi", "Lain", "Askes", "Rawat Inap", "BPJS Kesehatan", "JKK", "JKM", "Perdin", "BPJS Pensiun", "JHT TK", "Potongan Produktifitas", "Sanksi Kesalahan Kerja", 'T. Jabatan', 'T. Fungsional', 'T. PPH', 'PPH21'];
                }
                public function columnFormats(): array {
                    $fmt = "#,##0";
                    return ["G"=>$fmt, "H"=>$fmt, "I"=>$fmt, "J"=>$fmt, "K"=>$fmt, "L"=>$fmt, "M"=>$fmt, "N"=>$fmt, "O"=>$fmt, "P"=>$fmt, "Q"=>$fmt, "R"=>$fmt, "S"=>$fmt, "T"=>$fmt, "U"=>$fmt, "V"=>$fmt, "W"=>$fmt, "X"=>$fmt, "Y"=>$fmt, "Z"=>$fmt,];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }
    
    public function public_exportPajakCostCenter($req)
    {
        try {
            // 1. Handle Multi f_id
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            $branch_id = $req->m_branch_id;

            // 2. Load Data (Eager Loading lengkap)
            $karyawan = t_final_gaji_det::with([
                "m_kary.m_divisi",
                "m_kary.m_branch",
                "m_kary.status_kary",
                "m_kary.m_subcomp.m_company",
                "t_final_gaji_det_rincian.t_potongan.jenis_potongan",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            $rows = [];
            $groupedByBranch = $karyawan->groupBy(fn($item) => $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI');

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = [
                    "BULAN" => '', "LABEL" => "CABANG: " . strtoupper($branchName), "QTY" => '', 
                    "THP_FINAL" => '', "GAJI_POKOK" => '', "BONUS" => '', "LEMBUR" => '', "SANKSI" => '', "LAIN" => '', 
                    "ASKES" => '', "RAWAT_INAP" => '', "BPJS_KES" => '', "JKK" => '', "JKM" => '', "PERDIN" => '', 
                    "BPJS_PENSIUN" => '', "JHT_TK" => '', "POT_PROD" => '', "SANKSI_CETAK" => '', "T_JABATAN" => '', "T_FUNGSIONAL" => '',
                    "T_PPH" => '', "PPH21" => '' // Menambahkan kembali kolom PPH
                ];

                // Akumulator Cabang
                $bt = ["qty" => 0, "thp" => 0, "gp" => 0, "bonus" => 0, "lembur" => 0, "sanksi" => 0, "lain" => 0, "askes" => 0, "ri" => 0, "bkes" => 0, "jkk" => 0, "jkm" => 0, "perdin" => 0, "bp" => 0, "jht" => 0, "prod" => 0, "sc" => 0, "tunjab" => 0, "tunfung" => 0, "tpph" => 0, "pph21" => 0];

                $groupedByStatus = $itemsInBranch->groupBy(fn($item) => $item->m_kary?->status_kary?->value ?? 'TIDAK TERDEFINISI');

                foreach ($groupedByStatus as $statusName => $itemsByStatus) {
                    $rows[] = ["BULAN" => '', "LABEL" => "   STATUS: " . strtoupper($statusName)];
                    $st = ["qty" => 0, "thp" => 0, "gp" => 0, "bonus" => 0, "lembur" => 0, "sanksi" => 0, "lain" => 0, "askes" => 0, "ri" => 0, "bkes" => 0, "jkk" => 0, "jkm" => 0, "perdin" => 0, "bp" => 0, "jht" => 0, "prod" => 0, "sc" => 0, "tunjab" => 0, "tunfung" => 0, "tpph" => 0, "pph21" => 0];

                    // Grouping per Cost Center
                    $groupedByCC = $itemsByStatus->groupBy(function ($item) {
                        if (!$item->m_kary) return 'DATA TIDAK DITEMUKAN';
                        $divisiId = $item->m_kary->m_divisi->name ?? 0;
                        $posisiId = $item->m_kary->m_posisi_id ?? 0;
                        $level = m_level_posisi::whereHas('m_level_posisi_d', fn($q) => $q->where('m_posisi_id', $posisiId))->first();
                        $cc = m_cost_center::whereHas('m_cost_center_d', fn($q) => $q->where('m_divisi_id', $divisiId)->where('m_level_posisi_id', $level?->id ?? 0))->first();
                        return $cc ? $cc->cost_name : 'TANPA COST CENTER';
                    });

                    foreach ($groupedByCC as $ccName => $itemsInCC) {
                        $ccAccum = ["thp" => 0, "gp" => 0, "bonus" => 0, "lembur" => 0, "sanksi" => 0, "lain" => 0, "bkes" => 0, "jkk" => 0, "jkm" => 0, "jht" => 0, "bp" => 0, "prod" => 0, "sc" => 0, "tunjab" => 0, "tunfung" => 0, "tpph" => 0, "pph21" => 0];

                        foreach ($itemsInCC as $row) {
                            $rincian = $row->t_final_gaji_det_rincian;
                            $m_company = $row->m_kary?->m_subcomp?->m_company;
                            
                            // Logika Kalkulasi Pajak Rekonsiliasi
                            $valGajiPokok = $rincian->where("name", "gaji_pokok")->sum("value");
                            $val_tunjab = $rincian->where("name", "tunjangan_posisi")->sum("value");
                            $val_tunfung = $rincian->where("name", "tunjangan_fungsional")->sum("value");
                            $valThpFinal = $rincian->filter(fn($i) => $i->factor === '+' && !str_contains(strtolower($i->name), 'bonus') && !str_contains(strtolower($i->name), 'lembur'))->sum("value");
                            
                            $baseUpah = (int) $rincian->whereIn("name", ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"])->sum("value");

                            $hasBpjsTk = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'jht'))->count() > 0;
                            $hasBpjsKes = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->count() > 0;

                            $ccAccum['thp'] += $valThpFinal;
                            $ccAccum['gp'] += $valGajiPokok;
                            $ccAccum['tunjab'] += $val_tunjab;
                            $ccAccum['tunfung'] += $val_tunfung;
                            $ccAccum['bkes'] += $hasBpjsKes ? round($baseUpah * 0.04) : 0;
                            $ccAccum['jkk'] += $hasBpjsTk ? round(((float)($m_company?->iuran_jkk ?? 0.24)/100) * $baseUpah) : 0;
                            $ccAccum['jkm'] += $hasBpjsTk ? round(((float)($m_company?->iuran_jkm ?? 0.3)/100) * $baseUpah) : 0;
                            $ccAccum['jht'] += $hasBpjsTk ? round(((float)($m_company?->iuran_jht ?? 3.7)/100) * $baseUpah) : 0;
                            $ccAccum['tpph'] += 0; // Sesuai original (bisa diisi rincian pph jika ada)
                            $ccAccum['pph21'] += 0;
                        }

                        $rows[] = [
                            "BULAN" => \Carbon\Carbon::parse($itemsInCC->first()->t_final_gaji->periode_akhir)->format("Ym"),
                            "LABEL" => "      " . $ccName,
                            "QTY" => $itemsInCC->count(),
                            "THP_FINAL" => $ccAccum['thp'], "GAJI_POKOK" => $ccAccum['gp'], "BONUS" => 0, "LEMBUR" => 0, "SANKSI" => 0, "LAIN" => 0, "ASKES" => 0, "RAWAT_INAP" => 0,
                            "BPJS_KES" => $ccAccum['bkes'], "JKK" => $ccAccum['jkk'], "JKM" => $ccAccum['jkm'], "PERDIN" => 0, "BPJS_PENSIUN" => 0, "JHT_TK" => $ccAccum['jht'],
                            "POT_PROD" => 0, "SANKSI_CETAK" => 0, "T_JABATAN" => $ccAccum['tunjab'], "T_FUNGSIONAL" => $ccAccum['tunfung'],
                            "T_PPH" => $ccAccum['tpph'], "PPH21" => $ccAccum['pph21']
                        ];

                        foreach($ccAccum as $k => $v) { $st[$k] += $v; }
                        $st['qty'] += $itemsInCC->count();
                    }

                    // Baris Subtotal Status
                    $rows[] = [
                        "BULAN" => '', "LABEL" => "   SUB-TOTAL " . strtoupper($statusName), "QTY" => $st['qty'], 
                        "THP_FINAL" => $st['thp'], "GAJI_POKOK" => $st['gp'], "BONUS" => 0, "LEMBUR" => 0, "SANKSI" => 0, "LAIN" => 0, "ASKES" => 0, "RAWAT_INAP" => 0, 
                        "BPJS_KES" => $st['bkes'], "JKK" => $st['jkk'], "JKM" => $st['jkm'], "PERDIN" => 0, "BPJS_PENSIUN" => 0, "JHT_TK" => $st['jht'], 
                        "POT_PROD" => 0, "SANKSI_CETAK" => 0, "T_JABATAN" => $st['tunjab'], "T_FUNGSIONAL" => $st['tunfung'],
                        "T_PPH" => $st['tpph'], "PPH21" => $st['pph21']
                    ];
                    foreach($st as $k => $v) { $bt[$k] += $v; }
                }

                // Baris Total Cabang
                $rows[] = [
                    "BULAN" => '', "LABEL" => "TOTAL CABANG " . strtoupper($branchName), "QTY" => $bt['qty'], 
                    "THP_FINAL" => $bt['thp'], "GAJI_POKOK" => $bt['gp'], "BONUS" => 0, "LEMBUR" => 0, "SANKSI" => 0, "LAIN" => 0, "ASKES" => 0, "RAWAT_INAP" => 0, 
                    "BPJS_KES" => $bt['bkes'], "JKK" => $bt['jkk'], "JKM" => $bt['jkm'], "PERDIN" => 0, "BPJS_PENSIUN" => 0, "JHT_TK" => $bt['jht'], 
                    "POT_PROD" => 0, "SANKSI_CETAK" => 0, "T_JABATAN" => $bt['tunjab'], "T_FUNGSIONAL" => $bt['tunfung'],
                    "T_PPH" => $bt['tpph'], "PPH21" => $bt['pph21']
                ];
                $rows[] = ["BULAN" => ''];
            }

            $fileName = "export-pajak-cc-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";
            return \Maatwebsite\Excel\Facades\Excel::download(new class (collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["Bulan", "Cost Center", "Qty", "THP Final", "Gaji Pokok", "Bonus", "Lembur", "Sanksi", "Lain", "Askes", "Rawat Inap", "BPJS Kesehatan", "JKK", "JKM", "Perdin", "BPJS Pensiun", "JHT TK", "Pot. Produktifitas", "Sanksi Kesalahan", 'T. Jabatan', 'T. Fungsional', 'T. PPH', 'PPH21'];
                }
                public function columnFormats(): array {
                    $fmt = "#,##0";
                    return ["D"=>$fmt, "E"=>$fmt, "F"=>$fmt, "G"=>$fmt, "H"=>$fmt, "I"=>$fmt, "J"=>$fmt, "K"=>$fmt, "L"=>$fmt, "M"=>$fmt, "N"=>$fmt, "O"=>$fmt, "P"=>$fmt, "Q"=>$fmt, "R"=>$fmt, "S"=>$fmt, "T"=>$fmt, "U"=>$fmt, "V"=>$fmt, "W"=>$fmt];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    // public function public_exportPajakRekonsiliasi($req)
    // {
    //     try {
    //         $t_final_gaji_id = $req->f_id ?? 0;

    //         $karyawan = t_final_gaji_det::with([
    //             "m_kary.m_divisi",
    //             "m_kary.m_branch",
    //             "m_kary.m_subcomp.m_company",
    //             "m_kary.tanggungan",
    //             "t_final_gaji_det_rincian",
    //             "t_final_gaji"
    //         ])
    //         ->where("t_final_gaji_id", $t_final_gaji_id)
    //         ->get();

    //         // Grouping berdasarkan Cabang
    //         $groupedByBranch = $karyawan->groupBy(function ($item) {
    //             return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
    //         });

    //         $rows = [];
    //         foreach ($groupedByBranch as $branchName => $itemsInBranch) {
    //             // Header Cabang
    //             $rows[] = [
    //                 "BULAN" => '', "CABANG" => "CABANG: " . strtoupper($branchName), "NO" => '', "NIP" => '', "NAMA" => '', "STATUS" => '', 
    //                 "THP_FINAL" => '', "GAJI_POKOK" => '', "BONUS" => '', "LEMBUR" => '', "SANKSI" => '', "LAIN" => '', 
    //                 "ASKES" => '', "RAWAT_INAP" => '', "BPJS_KES" => '', "JKK" => '', "JKM" => '', "PERDIN" => '', 
    //                 "BPJS_PENSIUN" => '', "JHT_TK" => '', "POT_PROD" => '', "SANKSI_CETAK" => ''
    //             ];

    //             // Akumulasi Total Cabang (ri ditambahkan untuk akumulasi rawat inap)
    //             $bt = ["thp" => 0, "gp" => 0, "bonus" => 0, "lembur" => 0, "sanksi" => 0, "lain" => 0, "askes" => 0, "ri" => 0, "bkes" => 0, "jkk" => 0, "jkm" => 0, "perdin" => 0, "bp" => 0, "jht" => 0, "prod" => 0, "sc" => 0];

    //             foreach ($itemsInBranch as $row) {
    //                 $rincian = $row->t_final_gaji_det_rincian;
    //                 $periodeGaji = \Carbon\Carbon::parse($row->t_final_gaji->periode_akhir);
    //                 $bulan = $periodeGaji->month;
    //                 $tahun = $periodeGaji->year;
    //                 $karyawanId = $row->m_kary_id;
    //                 $m_company = $row->m_kary?->m_subcomp?->m_company;

    //                 // 1. Gaji Pokok: Name komponen tepat 'gaji_pokok'
    //                 $valGajiPokok = $rincian->where("name", "gaji_pokok")->sum("value");

    //                 // 2. THP Final: Semua factor '+' kecuali bonus, lembur, perdin, dan askes
    //                 $valThpFinal = $rincian->filter(function($i) {
    //                     $name = strtolower($i->name);
    //                     return $i->factor === '+' 
    //                         && !str_contains($name, 'bonus') 
    //                         && !str_contains($name, 'lembur') 
    //                         && !str_contains($name, 'perdin') 
    //                         && !str_contains($name, 'askes');
    //                 })->sum("value");

    //                 // 3. Iuran Perusahaan Kondisional
    //                 $hasBpjsKes = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->count() > 0;
    //                 $hasBpjsTk  = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'jht') || str_contains(strtolower($i->name), 'bpjs ketenagakerjaan'))->count() > 0;
    //                 $hasBpjsPen = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs pensiun'))->count() > 0;

    //                 // Hitung Base Upah untuk BPJS
    //                 $komponenBase = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
    //                 $baseUpah = (int) $rincian->whereIn("name", $komponenBase)->sum("value");

    //                 $valBpjsKes = $hasBpjsKes ? round($baseUpah * 0.04) : 0;
    //                 $valJkk     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkk ?? 0.24) / 100) * $baseUpah) : 0;
    //                 $valJkm     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkm ?? 0.3) / 100) * $baseUpah) : 0;
    //                 $valJht     = $hasBpjsTk ? round(((float) ($m_company?->iuran_jht ?? 3.7) / 100) * $baseUpah) : 0;
    //                 $valBpjsPen = $hasBpjsPen ? round(((float) ($m_company?->iuran_jp ?? 2.0) / 100) * $baseUpah) : 0;

    //                 // --- UPDATE RAWAT INAP ---
    //                 // Diambil dari plafond_ranap di m_kary dibagi 12
    //                 $valRawatInap = ($row->m_kary->plafond_ranap ?? 0) / 12;

    //                 // Komponen lainnya
    //                 $valBonus  = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "bonus"))->sum("value");
    //                 $valLembur = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "lembur"))->sum("value");
    //                 $valSanksi = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "terlambat"))->sum("value");
    //                 $valAbsen  = $rincian->filter(fn($i) => str_contains(strtolower($i->name), "tidak masuk kerja"))->sum("value");
                    
    //                 $valPotProd = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), "produktifitas"))->sum("value");
    //                 $valPotSalah = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), "kesalahan kerja"))->sum("value");

    //                 $valAskes  = t_klaim_askes::where("m_kary_id", $karyawanId)->where("status", "POSTED")->whereMonth("periode_akhir", $bulan)->whereYear("periode_akhir", $tahun)->sum("total_nominal");
    //                 $valPerdin = t_penyelesaian_perdin::join("t_perdin", "t_penyelesaian_perdin.t_perdin_id", "=", "t_perdin.id")
    //                             ->where("t_perdin.m_kary_id", $karyawanId)->where("t_penyelesaian_perdin.status", "APPROVED")->whereMonth("t_perdin.date_to", $bulan)->whereYear("t_perdin.date_to", $tahun)->sum("t_penyelesaian_perdin.total_biaya");

    //                 $dataRow = [
    //                     "BULAN" => $periodeGaji->format("Ym"),
    //                     "CABANG" => $branchName,
    //                     "NO" => $row->m_kary->no_registrasi ?? "-",
    //                     "NIP" => $row->m_kary->nip ?? "-",
    //                     "NAMA" => $row->m_kary->nama_lengkap ?? "-",
    //                     "STATUS" => $row->m_kary->tanggungan?->value ?? "-",
    //                     "THP_FINAL" => $valThpFinal,
    //                     "GAJI_POKOK" => $valGajiPokok,
    //                     "BONUS" => $valBonus,
    //                     "LEMBUR" => $valLembur,
    //                     "SANKSI" => $valSanksi,
    //                     "LAIN" => $valAbsen,
    //                     "ASKES" => $valAskes,
    //                     "RAWAT_INAP" => $valRawatInap,
    //                     "BPJS_KES" => $valBpjsKes,
    //                     "JKK" => $valJkk,
    //                     "JKM" => $valJkm,
    //                     "PERDIN" => $valPerdin,
    //                     "BPJS_PENSIUN" => $valBpjsPen,
    //                     "JHT_TK" => $valJht,
    //                     "POT_PROD" => $valPotProd,
    //                     "SANKSI_CETAK" => $valPotSalah,
    //                 ];

    //                 $rows[] = $dataRow;

    //                 // Akumulasi
    //                 $bt['thp'] += $valThpFinal; $bt['gp'] += $valGajiPokok; $bt['bonus'] += $valBonus; $bt['lembur'] += $valLembur; $bt['sanksi'] += $valSanksi;
    //                 $bt['lain'] += $valAbsen; $bt['askes'] += $valAskes; $bt['ri'] += $valRawatInap; $bt['bkes'] += $valBpjsKes; $bt['jkk'] += $valJkk; $bt['jkm'] += $valJkm;
    //                 $bt['perdin'] += $valPerdin; $bt['bp'] += $valBpjsPen; $bt['jht'] += $valJht; $bt['prod'] += $valPotProd; $bt['sc'] += $valPotSalah;
    //             }

    //             // Row Total Cabang
    //             $rows[] = [
    //                 "BULAN" => '', "CABANG" => "TOTAL " . strtoupper($branchName), "NO" => '', "NIP" => '', "NAMA" => '', "STATUS" => '', 
    //                 "THP_FINAL" => $bt['thp'], "GAJI_POKOK" => $bt['gp'], "BONUS" => $bt['bonus'], "LEMBUR" => $bt['lembur'], "SANKSI" => $bt['sanksi'], "LAIN" => $bt['lain'], 
    //                 "ASKES" => $bt['askes'], "RAWAT_INAP" => $bt['ri'], "BPJS_KES" => $bt['bkes'], "JKK" => $bt['jkk'], "JKM" => $bt['jkm'], "PERDIN" => $bt['perdin'], 
    //                 "BPJS_PENSIUN" => $bt['bp'], "JHT_TK" => $bt['jht'], "POT_PROD" => $bt['prod'], "SANKSI_CETAK" => $bt['sc']
    //             ];
    //             $rows[] = ["BULAN" => '']; // Spacer
    //         }

    //         $fileName = "export-pajak-rekonsiliasi-" . $this->find($t_final_gaji_id)->desc .'-' . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

    //         return \Maatwebsite\Excel\Facades\Excel::download(new class (collect($rows)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithColumnFormatting {
    //             protected $data;
    //             public function __construct($data) { $this->data = $data; }
    //             public function collection() { return $this->data; }
    //             public function headings(): array {
    //                 return ["Bulan", "Cabang", "No", "NIP", "Nama", "Status", "THP Final", "Gaji Pokok", "Bonus", "Lembur", "Sanksi", "Lain", "Askes", "Rawat Inap", "BPJS Kesehatan", "JKK", "JKM", "Perdin", "BPJS Pensiun", "JHT TK", "Potongan Produktifitas", "Sanksi Kesalahan Kerja"];
    //             }
    //             public function columnFormats(): array {
    //                 $fmt = "#,##0";
    //                 return ["G"=>$fmt, "H"=>$fmt, "I"=>$fmt, "J"=>$fmt, "K"=>$fmt, "L"=>$fmt, "M"=>$fmt, "N"=>$fmt, "O"=>$fmt, "P"=>$fmt, "Q"=>$fmt, "R"=>$fmt, "S"=>$fmt, "T"=>$fmt, "U"=>$fmt, "V"=>$fmt];
    //             }
    //         }, $fileName);

    //     } catch (\Exception $e) {
    //         return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
    //     }
    // }

    public function public_exportGajiCC($req)
    {
        try {
            $f_id_raw = $req->f_id;
            $m_branch_id = $req->m_branch_id;

            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }

            $ids = array_map('trim', array_filter((array) $ids));

            $details = t_final_gaji_det::with([
                'm_kary.m_divisi',
                'm_kary.status_kary',
                'm_kary.m_branch',
                'm_kary.m_subcomp.m_company',
                't_final_gaji_det_rincian.t_potongan.jenis_potongan',
                't_final_gaji'
            ])
            ->whereIn('t_final_gaji_id', $ids)
            ->when($m_branch_id, function ($query) use ($m_branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($m_branch_id) {
                    $q->where('m_branch_id', $m_branch_id);
                });
            })
            ->get();

            $filteredDetails = $details->filter(function ($item) {
                return $item->m_kary?->status_kary?->group === 'STATUS KARYAWAN';
            });

            $groupedByBranch = $filteredDetails->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                $bt = ['qty' => 0, 'thp' => 0, 'lembur' => 0, 'askes' => 0, 'jamsostek' => 0, 'bpjs' => 0, 'jp' => 0, 'terlambat' => 0, 'absen' => 0, 'prod' => 0, 'sanksi' => 0, 'koperasi' => 0, 'kantor' => 0, 'lainnya' => 0, 'netto' => 0, 'csv' => 0];

                $rows[] = [
                    'BULAN' => '', 'LABEL' => "CABANG: " . strtoupper($branchName), 'QTY' => '', 
                    'THP' => '', 'LEMBUR' => '', 'KLAIM_ASKES' => '', 'JAMSOSTEK' => '', 
                    'BPJS_KES' => '', 'BPJS_PENSIUN' => '', 'POT_TERLAMBAT' => '', 
                    'POT_ABSEN' => '', 'POT_PROD' => '', 'SANKSI_KERJA' => '', 
                    'PINJ_KOPERASI' => '', 'PINJ_KANTOR' => '', 'POT_LAINNYA' => '', 'NETTO' => '', 'ANGKA_CSV' => ''
                ];

                $groupedByStatus = $itemsInBranch->groupBy(function ($item) {
                    return $item->m_kary?->status_kary?->value ?? 'TIDAK TERDEFINISI';
                });

                foreach ($groupedByStatus as $statusName => $itemsByStatus) {
                    $rows[] = [
                        'BULAN' => '', 'LABEL' => "   STATUS: " . strtoupper($statusName), 'QTY' => '', 
                        'THP' => '', 'LEMBUR' => '', 'KLAIM_ASKES' => '', 'JAMSOSTEK' => '', 
                        'BPJS_KES' => '', 'BPJS_PENSIUN' => '', 'POT_TERLAMBAT' => '', 
                        'POT_ABSEN' => '', 'POT_PROD' => '', 'SANKSI_KERJA' => '', 
                        'PINJ_KOPERASI' => '', 'PINJ_KANTOR' => '', 'POT_LAINNYA' => '', 'NETTO' => '', 'ANGKA_CSV' => ''
                    ];

                    $st = ['qty' => 0, 'thp' => 0, 'lembur' => 0, 'askes' => 0, 'jamsostek' => 0, 'bpjs' => 0, 'jp' => 0, 'terlambat' => 0, 'absen' => 0, 'prod' => 0, 'sanksi' => 0, 'koperasi' => 0, 'kantor' => 0, 'lainnya' => 0, 'netto' => 0, 'csv' => 0];

                    $groupedByCC = $itemsByStatus->groupBy(function ($item) {
                        if (!$item->m_kary) return 'DATA TIDAK DITEMUKAN';
                        // $divisiId = $item->m_kary->m_divisi_id ?? 0;
                        $divisiId = $item->m_kary->m_divisi->name ?? 0;
                        $posisiId = $item->m_kary->m_posisi_id ?? 0;
                        $level = m_level_posisi::whereHas('m_level_posisi_d', function($q) use ($posisiId){
                            $q->where('m_posisi_id', $posisiId);
                        })->first();
                        $cc = m_cost_center::whereHas('m_cost_center_d', function($q) use ($divisiId, $level){
                            $q->where('m_divisi_id', $divisiId)->where('m_level_posisi_id', $level?->id ?? 0);
                        })->first();
                        return $cc ? $cc->cost_name : 'TANPA COST CENTER';
                    });

                    foreach ($groupedByCC as $ccName => $itemsInCC) {
                        $ccAccum = ['thp' => 0, 'lembur' => 0, 'askes' => 0, 'jamsostek' => 0, 'bpjs' => 0, 'jp' => 0, 'terlambat' => 0, 'absen' => 0, 'prod' => 0, 'sanksi' => 0, 'koperasi' => 0, 'kantor' => 0, 'lainnya' => 0, 'netto' => 0, 'csv' => 0];

                        foreach ($itemsInCC as $item) {
                            $rincian = $item->t_final_gaji_det_rincian;
                            
                            $valAskes = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), 'askes'))->sum('value');
                            $valThp = $rincian->filter(fn($i) => $i->factor === '+' && !str_contains(strtolower($i->name), 'lembur') && !str_contains(strtolower($i->name), 'askes'))->sum('value');
                            $valLembur = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'lembur'))->sum('value');

                            $valTerlambat = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'terlambat'))->sum('value');
                            $valAbsen     = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'tidak masuk kerja'))->sum('value');
                            $valProd      = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), 'produktifitas'))->sum('value');
                            $valSanksi    = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), 'kesalahan kerja'))->sum('value');
                            $valKoperasi  = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), 'pinjaman koperasi'))->sum('value');
                            $valKantor    = $rincian->filter(fn($i) => $i->t_potongan?->jenis_potongan && str_contains(strtolower($i->t_potongan->jenis_potongan->value), 'pinjaman kantor'))->sum('value');
                            $valJamsostek = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'jht') || str_contains(strtolower($i->name), 'bpjs ketenagakerjaan'))->sum('value');
                            $valBpjsKes   = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->sum('value');
                            $valBpjsPen   = $rincian->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs pensiun'))->sum('value');

                            $totalPotonganGaji = $rincian->where('factor', '-')->sum('value');
                            $valPotLainnya = $totalPotonganGaji - ($valTerlambat + $valAbsen + $valProd + $valSanksi + $valKoperasi + $valKantor + $valJamsostek + $valBpjsKes + $valBpjsPen + $valAskes);

                            // --- LOGIKA PERBAIKAN ---
                            // Netto = Original Netto (sudah dikurangi semua potongan)
                            $valNettoOriginal = ($valThp + $valLembur) - $totalPotonganGaji;
                            
                            // Angka CSV = Netto yang sudah ditambah kembali dengan pinjaman
                            $valAngkaCSV = $valNettoOriginal + $valKoperasi + $valKantor;

                            $ccAccum['thp'] += $valThp; 
                            $ccAccum['lembur'] += $valLembur; 
                            $ccAccum['askes'] += $valAskes;
                            $ccAccum['jamsostek'] += $valJamsostek; 
                            $ccAccum['bpjs'] += $valBpjsKes; 
                            $ccAccum['jp'] += $valBpjsPen;
                            $ccAccum['terlambat'] += $valTerlambat; 
                            $ccAccum['absen'] += $valAbsen; 
                            $ccAccum['prod'] += $valProd; 
                            $ccAccum['sanksi'] += $valSanksi;
                            $ccAccum['koperasi'] += $valKoperasi; 
                            $ccAccum['kantor'] += $valKantor;
                            $ccAccum['lainnya'] += max(0, $valPotLainnya);
                            $ccAccum['netto'] += $valNettoOriginal;
                            $ccAccum['csv'] += $valAngkaCSV;
                        }

                        $rows[] = [
                            'BULAN'         => \Carbon\Carbon::parse($itemsInCC->first()->t_final_gaji->periode_akhir)->format('Ym'),
                            'LABEL'         => "      ".$ccName,
                            'QTY'           => $itemsInCC->count(),
                            'THP'           => $ccAccum['thp'],
                            'LEMBUR'        => $ccAccum['lembur'],
                            'KLAIM_ASKES'   => $ccAccum['askes'],
                            'JAMSOSTEK'     => $ccAccum['jamsostek'],
                            'BPJS_KES'      => $ccAccum['bpjs'],
                            'BPJS_PENSIUN'  => $ccAccum['jp'],
                            'POT_TERLAMBAT' => $ccAccum['terlambat'],
                            'POT_ABSEN'     => $ccAccum['absen'],
                            'POT_PROD'      => $ccAccum['prod'],
                            'SANKSI_KERJA'  => $ccAccum['sanksi'],
                            'PINJ_KOPERASI' => $ccAccum['koperasi'],
                            'PINJ_KANTOR'   => $ccAccum['kantor'],
                            'POT_LAINNYA'   => $ccAccum['lainnya'],
                            'NETTO'         => $ccAccum['netto'],
                            'ANGKA_CSV'     => $ccAccum['csv'],
                        ];

                        foreach($ccAccum as $key => $val) { $st[$key] += $val; }
                        $st['qty'] += $itemsInCC->count();
                    }

                    $rows[] = [
                        'BULAN' => '', 'LABEL' => "   SUB-TOTAL " . strtoupper($statusName), 'QTY' => $st['qty'], 
                        'THP' => $st['thp'], 'LEMBUR' => $st['lembur'], 'KLAIM_ASKES' => $st['askes'], 
                        'JAMSOSTEK' => $st['jamsostek'], 'BPJS_KES' => $st['bpjs'], 'BPJS_PENSIUN' => $st['jp'], 
                        'POT_TERLAMBAT' => $st['terlambat'], 'POT_ABSEN' => $st['absen'], 
                        'POT_PROD' => $st['prod'], 'SANKSI_KERJA' => $st['sanksi'],
                        'PINJ_KOPERASI' => $st['koperasi'], 'PINJ_KANTOR' => $st['kantor'], 
                        'POT_LAINNYA' => $st['lainnya'], 'NETTO' => $st['netto'], 'ANGKA_CSV' => $st['csv']
                    ];

                    foreach($st as $key => $val) { $bt[$key] += $val; }
                }

                $rows[] = [
                    'BULAN' => '', 'LABEL' => "TOTAL CABANG " . strtoupper($branchName), 'QTY' => $bt['qty'], 
                    'THP' => $bt['thp'], 'LEMBUR' => $bt['lembur'], 'KLAIM_ASKES' => $bt['askes'], 
                    'JAMSOSTEK' => $bt['jamsostek'], 'BPJS_KES' => $bt['bpjs'], 'BPJS_PENSIUN' => $bt['jp'], 
                    'POT_TERLAMBAT' => $bt['terlambat'], 'POT_ABSEN' => $bt['absen'], 
                    'POT_PROD' => $bt['prod'], 'SANKSI_KERJA' => $bt['sanksi'],
                    'PINJ_KOPERASI' => $bt['koperasi'], 'PINJ_KANTOR' => $bt['kantor'], 
                    'POT_LAINNYA' => $bt['lainnya'], 'NETTO' => $bt['netto'], 'ANGKA_CSV' => $bt['csv']
                ];
                
                $rows[] = ['BULAN' => '']; 
            }

            $firstId = reset($ids); 
            $fileDesc = count($ids) > 1 ? "MULTIPLE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-gaji-cc-" . $fileDesc . '-' . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return Excel::download(new class(collect($rows)) implements FromCollection, WithHeadings, WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ['Bulan', 'Cost Center', 'Jumlah Karyawan', 'THP Final', 'Lembur', 'Pot. Askes', 'Jamsostek', 'BPJS Kesehatan', 'BPJS Pensiun', 'Pot. Terlambat', 'Pot. Absen', 'Pot. Produktifitas', 'Sanksi Kesalahan', 'Pinj. Koperasi', 'Pinj. Kantor', 'Pot. Lainnya', 'Netto', 'Angka CSV'];
                }
                public function columnFormats(): array {
                    return [
                        'D' => '#,##0', 'E' => '#,##0', 'F' => '#,##0', 'G' => '#,##0', 'H' => '#,##0', 
                        'I' => '#,##0', 'J' => '#,##0', 'K' => '#,##0', 'L' => '#,##0', 'M' => '#,##0', 
                        'N' => '#,##0', 'O' => '#,##0', 'P' => '#,##0', 'Q' => '#,##0', 'R' => '#,##0'
                    ];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function public_exportBPJSKesCC($req)
    {
        try {
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_map('trim', array_filter((array) $ids));

            $branch_id = $req->m_branch_id;

            $details = t_final_gaji_det::with([
                "m_kary.m_divisi",
                "m_kary.status_kary",
                "m_kary.m_branch",
                "t_final_gaji_det_rincian",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            $filteredDetails = $details->filter(function ($item) {
                return $item->m_kary?->status_kary?->group === "STATUS KARYAWAN";
            });

            $groupedByBranch = $filteredDetails->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];
            $no = 1;

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = ["NO" => "", "COST_CENTER" => "CABANG: " . strtoupper($branchName), "JUMLAH_KARYAWAN" => "", "BASE_UPAH" => "", "IURAN_1_PERSEN" => "", "IURAN_4_PERSEN" => "", "TOTAL_IURAN" => ""];

                // Akumulator untuk Total per Cabang
                $bt = ['qty' => 0, 'base' => 0, 'i1' => 0, 'i4' => 0];

                $groupedByStatus = $itemsInBranch->groupBy(function ($item) {
                    return $item->m_kary?->status_kary?->value ?? "TIDAK TERDEFINISI";
                });

                foreach ($groupedByStatus as $statusName => $itemsByStatus) {
                    $rows[] = ["NO" => "", "COST_CENTER" => "   STATUS: " . strtoupper($statusName), "JUMLAH_KARYAWAN" => "", "BASE_UPAH" => "", "IURAN_1_PERSEN" => "", "IURAN_4_PERSEN" => "", "TOTAL_IURAN" => ""];

                    $st = ['qty' => 0, 'base' => 0, 'i1' => 0, 'i4' => 0];

                    $groupedByCC = $itemsByStatus->groupBy(function ($item) {
                        $divisiName = $item->m_kary->m_divisi->name ?? 0;
                        $posisiId = $item->m_kary->m_posisi_id ?? 0;
                        $level = m_level_posisi::whereHas("m_level_posisi_d", fn($q) => $q->where("m_posisi_id", $posisiId))->first();
                        $cc = m_cost_center::whereHas("m_cost_center_d", fn($q) => $q->where("m_divisi_id", $divisiName)->where("m_level_posisi_id", $level?->id ?? 0))->first();
                        return $cc ? $cc->cost_name : "TANPA COST CENTER";
                    });

                    foreach ($groupedByCC as $ccName => $itemsInCC) {
                        $ccAcc = ['base' => 0, 'i1' => 0, 'i4' => 0];

                        foreach ($itemsInCC as $item) {
                            $rincian = $item->t_final_gaji_det_rincian;
                            if (!$rincian) continue;

                            $komponenBPJS = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
                            $baseUpah = (int) $rincian->whereIn("name", $komponenBPJS)->sum("value");

                            $hasPotongan = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->count() > 0;

                            $iuran1 = (int) ($rincian->where("nama_komponen", "BPJS Kesehatan")->first()?->nominal ?? ($hasPotongan ? round($baseUpah * 0.01) : 0));
                            $iuran4 = $hasPotongan ? round($baseUpah * 0.04) : 0;

                            $ccAcc['base'] += $baseUpah;
                            $ccAcc['i1'] += $iuran1;
                            $ccAcc['i4'] += $iuran4;
                        }

                        $rows[] = [
                            "NO" => $no++,
                            "COST_CENTER" => "      " . $ccName,
                            "JUMLAH_KARYAWAN" => $itemsInCC->count(),
                            "BASE_UPAH" => $ccAcc['base'],
                            "IURAN_1_PERSEN" => $ccAcc['i1'],
                            "IURAN_4_PERSEN" => $ccAcc['i4'],
                            "TOTAL_IURAN" => $ccAcc['i1'] + $ccAcc['i4'],
                        ];

                        $st['qty'] += $itemsInCC->count();
                        $st['base'] += $ccAcc['base'];
                        $st['i1'] += $ccAcc['i1'];
                        $st['i4'] += $ccAcc['i4'];
                    }

                    // Baris Sub-Total per Status
                    $rows[] = [
                        "NO" => "",
                        "COST_CENTER" => "   SUB-TOTAL " . strtoupper($statusName),
                        "JUMLAH_KARYAWAN" => $st['qty'],
                        "BASE_UPAH" => $st['base'],
                        "IURAN_1_PERSEN" => $st['i1'],
                        "IURAN_4_PERSEN" => $st['i4'],
                        "TOTAL_IURAN" => $st['i1'] + $st['i4'],
                    ];

                    // Tambahkan ke akumulator Cabang
                    $bt['qty'] += $st['qty'];
                    $bt['base'] += $st['base'];
                    $bt['i1'] += $st['i1'];
                    $bt['i4'] += $st['i4'];
                }

                // Baris TOTAL PER CABANG (Ditambahkan di sini)
                $rows[] = [
                    "NO" => "",
                    "COST_CENTER" => "TOTAL CABANG " . strtoupper($branchName),
                    "JUMLAH_KARYAWAN" => $bt['qty'],
                    "BASE_UPAH" => $bt['base'],
                    "IURAN_1_PERSEN" => $bt['i1'],
                    "IURAN_4_PERSEN" => $bt['i4'],
                    "TOTAL_IURAN" => $bt['i1'] + $bt['i4'],
                ];

                $rows[] = ["NO" => ""]; // Spacer
            }

            // Penamaan File
            $firstId = reset($ids);
            $fileDesc = count($ids) > 1 ? "MULTIPLE-PERIODE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-bpjs-kes-cc-" . $fileDesc . "-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return Excel::download(new class (collect($rows)) implements FromCollection, WithHeadings, WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["NO", "COST CENTER", "JUMLAH KARYAWAN", "TOTAL UPAH", "IURAN 1% (KARYAWAN)", "IURAN 4% (PERUSAHAAN)", "TOTAL IURAN"];
                }
                public function columnFormats(): array {
                    return ["D" => "#,##0", "E" => "#,##0", "F" => "#,##0", "G" => "#,##0"];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function public_exportBPJSKes($req)
    {
        try {
            // 1. Handle Multi f_id (Support string array [33,34] atau comma separated 33,34)
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            $branch_id = $req->m_branch_id;

            // 2. Ambil data dengan Eager Loading lengkap
            $karyawan = t_final_gaji_det::with([
                "m_kary.m_kary_det_kartu",
                "m_kary.m_branch",
                "m_kary.status_nikah",
                "t_final_gaji_det_rincian",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            // 3. Grouping per Branch (Cabang)
            $groupedByBranch = $karyawan->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];
            $globalNo = 1;

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = [
                    "NO" => "", "NIP" => "CABANG: " . strtoupper($branchName), 
                    "NO_BPJS" => "", "NAMA" => "", "STATUS" => "", "TGL_LAHIR" => "", 
                    "UPAH" => "", "IURAN1" => "", "IURAN4" => "", "TOTAL" => ""
                ];

                // Akumulasi Total Cabang
                $bt = ["upah" => 0, "i1" => 0, "i4" => 0, "total" => 0];

                foreach ($itemsInBranch as $row) {
                    $kary = $row->m_kary;
                    $rincian = $row->t_final_gaji_det_rincian;
                    
                    if (!$rincian) continue;

                    // --- Logika Perhitungan ---
                    $komponenBPJS = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
                    $baseUpah = (int) $rincian->whereIn("name", $komponenBPJS)->sum("value");

                    // Cek apakah karyawan memiliki potongan BPJS Kesehatan (Kondisional Iuran PT)
                    $hasPotongan = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs kesehatan'))->count() > 0;

                    $iuranKaryawan = (int) ($rincian->where("nama_komponen", "BPJS Kesehatan")->first()?->nominal ?? ($hasPotongan ? round($baseUpah * 0.01) : 0));
                    $iuranPerusahaan = $hasPotongan ? round($baseUpah * 0.04) : 0;
                    $totalIuran = $iuranKaryawan + $iuranPerusahaan;

                    $rows[] = [
                        "NO" => $globalNo++,
                        "NIP" => $kary->kode ?? "-",
                        "NO_BPJS" => $kary?->m_kary_det_kartu?->first()?->bpjs_no_kesehatan ?? "-",
                        "NAMA_LENGKAP" => $kary->nama_lengkap ?? "-",
                        "STATUS KAWIN" => $kary->status_nikah?->value ?? "BELUM KAWIN",
                        "TGL LAHIR" => $kary->tgl_lahir ? \Carbon\Carbon::parse($kary->tgl_lahir)->format("d-m-Y") : "-",
                        "UPAH TK" => $baseUpah,
                        "IURAN_1_PERSEN" => $iuranKaryawan,
                        "IURAN_4_PERSEN" => $iuranPerusahaan,
                        "TOTAL_IURAN" => $totalIuran,
                    ];

                    // Update Akumulasi Branch
                    $bt['upah'] += $baseUpah; $bt['i1'] += $iuranKaryawan; $bt['i4'] += $iuranPerusahaan; $bt['total'] += $totalIuran;
                }

                // Row Total Cabang
                $rows[] = [
                    "NO" => "", "NIP" => "TOTAL " . strtoupper($branchName), "NO_BPJS" => "", "NAMA" => "", "STATUS" => "", "TGL_LAHIR" => "", 
                    "UPAH" => $bt['upah'], "IURAN1" => $bt['i1'], "IURAN4" => $bt['i4'], "TOTAL" => $bt['total']
                ];
                $rows[] = ["NO" => ""]; // Spacer
            }

            // Penamaan File
            $firstId = reset($ids);
            $fileDesc = count($ids) > 1 ? "MULTIPLE-PERIODE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-bpjs-kes-" . $fileDesc . "-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return Excel::download(new class (collect($rows)) implements FromCollection, WithHeadings, WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["NO", "NIP", "NO BPJS KES", "NAMA KARYAWAN", "STATUS KAWIN", "TGL LAHIR", "UPAH TK", "IURAN 1% (KARY)", "IURAN 4% (PT)", "TOTAL IURAN"];
                }
                public function columnFormats(): array {
                    $fmt = "#,##0";
                    return ["G" => $fmt, "H" => $fmt, "I" => $fmt, "J" => $fmt];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => "Terjadi kesalahan: " . $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function public_exportBPJSTKCC($req)
    {
        try {
            // 1. Handle Multi f_id (Support string array [33,34] atau comma separated 33,34)
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            $branch_id = $req->m_branch_id;

            // 2. Load Data dengan Eager Loading lengkap
            $details = t_final_gaji_det::with([
                "m_kary.m_divisi",
                "m_kary.status_kary",
                "m_kary.m_branch",
                "m_kary.m_subcomp.m_company",
                "t_final_gaji_det_rincian",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            // 3. Filter Status Karyawan
            $filteredDetails = $details->filter(function ($item) {
                return $item->m_kary?->status_kary?->group === "STATUS KARYAWAN";
            });

            // 4. Grouping Utama: BRANCH (Cabang)
            $groupedByBranch = $filteredDetails->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];
            $no = 1;

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = [
                    "NO" => "", "COST_CENTER" => "CABANG: " . strtoupper($branchName), 
                    "JUMLAH_KARYAWAN" => "", "BASE_UPAH" => "", "IURAN_KARYAWAN_JAMSOSTEK" => "", 
                    "IURAN_JKK_CC" => "", "IURAN_JKM_CC" => "", "IURAN_JHT_CC" => "", 
                    "TOTAL_IURAN_JAMSOSTEK" => "", "IURAN_KARYAWAN_PENSIUN" => "", 
                    "IURAN_JP_CC" => "", "TOTAL_IURAN_PENSIUN" => ""
                ];

                // Akumulasi Total Cabang
                $bt = ["qty" => 0, "base" => 0, "k_jam" => 0, "jkk" => 0, "jkm" => 0, "jht" => 0, "k_pen" => 0, "jp" => 0];

                // Grouping Kedua: Status Karyawan
                $groupedByStatus = $itemsInBranch->groupBy(function ($item) {
                    return $item->m_kary?->status_kary?->value ?? "TIDAK TERDEFINISI";
                });

                foreach ($groupedByStatus as $statusName => $itemsByStatus) {
                    $rows[] = [
                        "NO" => "", "COST_CENTER" => "   STATUS: " . strtoupper($statusName), 
                        "JUMLAH_KARYAWAN" => "", "BASE_UPAH" => "", "IURAN_KARYAWAN_JAMSOSTEK" => "", 
                        "IURAN_JKK_CC" => "", "IURAN_JKM_CC" => "", "IURAN_JHT_CC" => "", 
                        "TOTAL_IURAN_JAMSOSTEK" => "", "IURAN_KARYAWAN_PENSIUN" => "", 
                        "IURAN_JP_CC" => "", "TOTAL_IURAN_PENSIUN" => ""
                    ];

                    $st = ["qty" => 0, "base" => 0, "k_jam" => 0, "jkk" => 0, "jkm" => 0, "jht" => 0, "k_pen" => 0, "jp" => 0];

                    // Grouping Ketiga: Cost Center
                    $groupedByCC = $itemsByStatus->groupBy(function ($item) {
                        //$divisiId = $item->m_kary->m_divisi_id ?? 0;
                        $divisiId = $item->m_kary->m_divisi->name ?? 0;
                        $posisiId = $item->m_kary->m_posisi_id ?? 0;
                        $level = m_level_posisi::whereHas("m_level_posisi_d", fn($q) => $q->where("m_posisi_id", $posisiId))->first();
                        $cc = m_cost_center::whereHas("m_cost_center_d", fn($q) => $q->where("m_divisi_id", $divisiId)->where("m_level_posisi_id", $level?->id ?? 0))->first();
                        return $cc ? $cc->cost_name : "TANPA COST CENTER";
                    });

                    foreach ($groupedByCC as $ccName => $itemsInCC) {
                        $ccAcc = ["base" => 0, "k_jam" => 0, "jkk" => 0, "jkm" => 0, "jht" => 0, "k_pen" => 0, "jp" => 0];

                        foreach ($itemsInCC as $item) {
                            $rincian = $item->t_final_gaji_det_rincian;
                            if (!$rincian) continue;
                            $m_company = $item->m_kary->m_subcomp->m_company ?? null;

                            $komponenBPJS = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
                            $baseUpah = (int) $rincian->whereIn("name", $komponenBPJS)->sum("value");

                            // Cek keberadaan potongan karyawan untuk iuran perusahaan kondisional
                            $hasBpjsTk = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'jht') || str_contains(strtolower($i->name), 'bpjs ketenagakerjaan'))->count() > 0;
                            $hasBpjsPen = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'bpjs pensiun'))->count() > 0;

                            $iuranKaryawan = (int) ($rincian->where("nama_komponen", "BPJS Ketenagakerjaan")->first()?->nominal ?? ($hasBpjsTk ? round($baseUpah * 0.02) : 0));
                            $iuranPensiunKaryawan = (int) ($rincian->where("nama_komponen", "BPJS Pensiun")->first()?->nominal ?? ($hasBpjsPen ? round($baseUpah * 0.01) : 0));

                            $jkk = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkk ?? 0.24) / 100) * $baseUpah) : 0;
                            $jkm = $hasBpjsTk ? round(((float) ($m_company?->iuran_jkm ?? 0.3) / 100) * $baseUpah) : 0;
                            $jht = $hasBpjsTk ? round(((float) ($m_company?->iuran_jht ?? 3.7) / 100) * $baseUpah) : 0;
                            $jp = $hasBpjsPen ? round(((float) ($m_company?->iuran_jp ?? 2.0) / 100) * $baseUpah) : 0;

                            $ccAcc['base'] += $baseUpah; $ccAcc['k_jam'] += $iuranKaryawan; $ccAcc['jkk'] += $jkk;
                            $ccAcc['jkm'] += $jkm; $ccAcc['jht'] += $jht; $ccAcc['k_pen'] += $iuranPensiunKaryawan; $ccAcc['jp'] += $jp;
                        }

                        $rows[] = [
                            "NO" => $no++,
                            "COST_CENTER" => "      " . $ccName,
                            "JUMLAH_KARYAWAN" => $itemsInCC->count(),
                            "BASE_UPAH" => $ccAcc['base'],
                            "IURAN_KARYAWAN_JAMSOSTEK" => $ccAcc['k_jam'],
                            "IURAN_JKK_CC" => $ccAcc['jkk'],
                            "IURAN_JKM_CC" => $ccAcc['jkm'],
                            "IURAN_JHT_CC" => $ccAcc['jht'],
                            "TOTAL_IURAN_JAMSOSTEK" => $ccAcc['k_jam'] + $ccAcc['jkk'] + $ccAcc['jkm'] + $ccAcc['jht'],
                            "IURAN_KARYAWAN_PENSIUN" => $ccAcc['k_pen'],
                            "IURAN_JP_CC" => $ccAcc['jp'],
                            "TOTAL_IURAN_PENSIUN" => $ccAcc['k_pen'] + $ccAcc['jp'],
                        ];

                        $st['qty'] += $itemsInCC->count(); $st['base'] += $ccAcc['base']; $st['k_jam'] += $ccAcc['k_jam']; 
                        $st['jkk'] += $ccAcc['jkk']; $st['jkm'] += $ccAcc['jkm']; $st['jht'] += $ccAcc['jht']; 
                        $st['k_pen'] += $ccAcc['k_pen']; $st['jp'] += $ccAcc['jp'];
                    }

                    $rows[] = [
                        "NO" => "", "COST_CENTER" => "   SUB-TOTAL " . strtoupper($statusName), 
                        "JUMLAH_KARYAWAN" => $st['qty'], "BASE_UPAH" => $st['base'], 
                        "IURAN_KARYAWAN_JAMSOSTEK" => $st['k_jam'], "IURAN_JKK_CC" => $st['jkk'], 
                        "IURAN_JKM_CC" => $st['jkm'], "IURAN_JHT_CC" => $st['jht'], 
                        "TOTAL_IURAN_JAMSOSTEK" => $st['k_jam'] + $st['jkk'] + $st['jkm'] + $st['jht'], 
                        "IURAN_KARYAWAN_PENSIUN" => $st['k_pen'], "IURAN_JP_CC" => $st['jp'], 
                        "TOTAL_IURAN_PENSIUN" => $st['k_pen'] + $st['jp'],
                    ];

                    foreach($st as $key => $val) { $bt[$key] += $val; }
                }

                $rows[] = [
                    "NO" => "", "COST_CENTER" => "TOTAL " . strtoupper($branchName), 
                    "JUMLAH_KARYAWAN" => $bt['qty'], "BASE_UPAH" => $bt['base'], 
                    "IURAN_KARYAWAN_JAMSOSTEK" => $bt['k_jam'], "IURAN_JKK_CC" => $bt['jkk'], 
                    "IURAN_JKM_CC" => $bt['jkm'], "IURAN_JHT_CC" => $bt['jht'], 
                    "TOTAL_IURAN_JAMSOSTEK" => $bt['k_jam'] + $bt['jkk'] + $bt['jkm'] + $bt['jht'], 
                    "IURAN_KARYAWAN_PENSIUN" => $bt['k_pen'], "IURAN_JP_CC" => $bt['jp'], 
                    "TOTAL_IURAN_PENSIUN" => $bt['k_pen'] + $bt['jp'],
                ];
                $rows[] = ["NO" => ""]; 
            }

            $firstId = reset($ids);
            $fileDesc = count($ids) > 1 ? "MULTIPLE-PERIODE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-tk-cc-" . $fileDesc . "-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return Excel::download(new class (collect($rows)) implements FromCollection, WithHeadings, WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["NO", "COST CENTER", "JUMLAH KARYAWAN", "TOTAL UPAH", "IURAN JAMSOSTEK KARYAWAN (2%)", "JKK PERUSAHAAN", "JKM PERUSAHAAN", "JHT PERUSAHAAN", "TOTAL JAMSOSTEK", "IURAN PENSIUN KARYAWAN (1%)", "JP PERUSAHAAN", "TOT PENSIUN"];
                }
                public function columnFormats(): array {
                    $fmt = "#,##0";
                    return ["D" => $fmt, "E" => $fmt, "F" => $fmt, "G" => $fmt, "H" => $fmt, "I" => $fmt, "J" => $fmt, "K" => $fmt, "L" => $fmt];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function public_exportBPJSTK($req)
    {
        try {
            // 1. Handle Multi f_id (Support string array [33,34] atau comma separated 33,34)
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            $branch_id = $req->m_branch_id;

            // 2. Ambil data dengan Eager Loading lengkap
            $karyawan = t_final_gaji_det::with([
                "m_kary.m_kary_det_kartu",
                "m_kary.m_branch",
                "m_kary.m_subcomp.m_company",
                "m_kary.status_nikah",
                "t_final_gaji_det_rincian",
                "t_final_gaji"
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->when($branch_id, function ($query) use ($branch_id) {
                return $query->whereHas('m_kary', function ($q) use ($branch_id) {
                    $q->where('m_branch_id', $branch_id);
                });
            })
            ->get();

            // 3. Grouping per Branch (Cabang)
            $groupedByBranch = $karyawan->groupBy(function ($item) {
                return $item->m_kary?->m_branch?->name ?? 'CABANG TIDAK TERDEFINISI';
            });

            $rows = [];
            $globalNo = 1;

            foreach ($groupedByBranch as $branchName => $itemsInBranch) {
                // Header Cabang
                $rows[] = [
                    "NO" => "", "NIP" => "CABANG: " . strtoupper($branchName), 
                    "NO_BPJS" => "", "NAMA" => "", "STATUS" => "", "TGL_LAHIR" => "", 
                    "UPAH" => "", "IURAN_KARY" => "", "IURAN_PT" => "", "TOTAL" => ""
                ];

                // Akumulasi Total Cabang
                $bt = ["upah" => 0, "i_kary" => 0, "i_pt" => 0, "total" => 0];

                foreach ($itemsInBranch as $row) {
                    $kary = $row->m_kary;
                    $rincian = $row->t_final_gaji_det_rincian;
                    
                    if (!$rincian) continue;

                    // --- Logika Perhitungan ---
                    $komponenBPJS = ["gaji_pokok", "tunjangan_produktifitas", "tunjangan_posisi", "tunjangan_transport", "tunjangan_fungsional"];
                    $baseUpah = (int) $rincian->whereIn("name", $komponenBPJS)->sum("value");

                    // Cek apakah karyawan memiliki potongan BPJS TK (Kondisional Iuran PT)
                    $hasPotongan = $rincian->where('factor', '-')->filter(fn($i) => str_contains(strtolower($i->name), 'jht') || str_contains(strtolower($i->name), 'bpjs ketenagakerjaan'))->count() > 0;

                    // Iuran Karyawan (2%)
                    $iuranKaryawan = (int) ($rincian->where("nama_komponen", "BPJS Ketenagakerjaan")->first()?->nominal ?? ($hasPotongan ? round($baseUpah * 0.02) : 0));
                    
                    // Iuran Perusahaan (JKK + JKM + JHT)
                    $m_company = $kary->m_subcomp->m_company ?? null;
                    $totalPresentasePT = $hasPotongan ? (
                        (float) ($m_company?->iuran_jkk ?? 0.24) +
                        (float) ($m_company?->iuran_jkm ?? 0.3) +
                        (float) ($m_company?->iuran_jht ?? 3.7)
                    ) : 0;

                    $iuranPerusahaan = round(($baseUpah * $totalPresentasePT) / 100);
                    $totalIuran = $iuranKaryawan + $iuranPerusahaan;

                    $rows[] = [
                        "NO" => $globalNo++,
                        "NIP" => $kary->kode ?? "-",
                        "NO_BPJS" => $kary?->m_kary_det_kartu?->first()?->bpjs_no_ketenagakerjaan ?? "-",
                        "NAMA_LENGKAP" => $kary->nama_lengkap ?? "-",
                        "STATUS KAWIN" => $kary->status_nikah?->value ?? "BELUM KAWIN",
                        "TGL LAHIR" => $kary->tgl_lahir ? \Carbon\Carbon::parse($kary->tgl_lahir)->format("d-m-Y") : "-",
                        "UPAH TK" => $baseUpah,
                        "IURAN_KARYAWAN" => $iuranKaryawan,
                        "IURAN_PERUSAHAAN" => $iuranPerusahaan,
                        "TOTAL_IURAN" => $totalIuran,
                    ];

                    // Update Akumulasi Branch
                    $bt['upah'] += $baseUpah; $bt['i_kary'] += $iuranKaryawan; $bt['i_pt'] += $iuranPerusahaan; $bt['total'] += $totalIuran;
                }

                // Row Total Cabang
                $rows[] = [
                    "NO" => "", "NIP" => "TOTAL " . strtoupper($branchName), "NO_BPJS" => "", "NAMA" => "", "STATUS" => "", "TGL_LAHIR" => "", 
                    "UPAH" => $bt['upah'], "IURAN_KARY" => $bt['i_kary'], "IURAN_PT" => $bt['i_pt'], "TOTAL" => $bt['total']
                ];
                $rows[] = ["NO" => ""]; // Spacer
            }

            // Penamaan File
            $firstId = reset($ids);
            $fileDesc = count($ids) > 1 ? "MULTIPLE-PERIODE" : ($this->find($firstId)->desc ?? "EXPORT");
            $fileName = "export-bpjs-tk-" . $fileDesc . "-" . \Carbon\Carbon::now()->format("YmdHis") . ".xlsx";

            return Excel::download(new class (collect($rows)) implements FromCollection, WithHeadings, WithColumnFormatting {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["NO", "NIP", "NO BPJS TK", "NAMA KARYAWAN", "STATUS KAWIN", "TGL LAHIR", "UPAH TK", "IURAN 2% (KARY)", "IURAN PT (JKK,JKM,JHT)", "TOTAL IURAN"];
                }
                public function columnFormats(): array {
                    $fmt = "#,##0";
                    return ["G" => $fmt, "H" => $fmt, "I" => $fmt, "J" => $fmt];
                }
            }, $fileName);

        } catch (\Exception $e) {
            return response()->json(["error" => "Terjadi kesalahan: " . $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function custom_exportMandiri($req)
    {
        try {
            // 1. Handle Multi f_id
            $f_id_raw = $req->f_id;
            if (is_string($f_id_raw)) {
                $decoded = json_decode($f_id_raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $f_id_raw);
            } else {
                $ids = is_array($f_id_raw) ? $f_id_raw : [$f_id_raw];
            }
            $ids = array_values(array_map('trim', array_filter((array) $ids)));

            // 2. Load data dengan relasi yang tepat
            $karyawan = t_final_gaji_det::with([
                'm_kary.m_kary_det_pemb', 
                't_final_gaji_det_rincian.t_potongan.jenis_potongan', 
                't_final_gaji'
            ])
            ->whereIn("t_final_gaji_id", $ids)
            ->get();
                
            $branch = m_branch::findOrFail($req->m_branch_id);

            $generalKoperasi = m_general::where('group', 'REK PINJAMAN KOPERASI')->where('key', '01')->first();
            $rekKoperasi = $generalKoperasi?->value ?? '987654321';
            $namaRekKoperasi = $generalKoperasi?->value_2 ?? 'KOPERASI';

            $generalKantor = m_general::where('group', 'REK PINJAMAN KANTOR')->where('key', '01')->first();
            $rekKantor = $generalKantor?->value ?? '123456789';
            $namaRekKantor = $generalKantor?->value_2 ?? 'KANTOR';
            $rows = [];
            $totalTransfer = 0;
            
            // Variabel penampung rekap total
            $grandTotalKoperasi = 0;
            $grandTotalKantor = 0;

            foreach ($karyawan as $row) {
                $rincian = $row->t_final_gaji_det_rincian;
                $periode = \Carbon\Carbon::parse($row->t_final_gaji->periode_akhir)->translatedFormat("F Y");

                // --- Hitung Potongan melalui relasi jenis_potongan ---
                $valKoperasi = $rincian->filter(function ($i) {
                    $jenis = $i->t_potongan?->jenis_potongan?->value ?? '';
                    return str_contains(strtolower($jenis), 'pinjaman koperasi');
                })->sum('value');

                $valKantor = $rincian->filter(function ($i) {
                    $jenis = $i->t_potongan?->jenis_potongan?->value ?? '';
                    return str_contains(strtolower($jenis), 'pinjaman kantor');
                })->sum('value');

                // Tambahkan ke akumulasi rekap
                $grandTotalKoperasi += $valKoperasi;
                $grandTotalKantor += $valKantor;

                // A. Row Transfer Gaji Netto ke Karyawan
                $valNetto = (int) ($row->total_gaji ?? 0);
                if ($valNetto > 0) {
                    $rows[] = [
                        $row->m_kary?->m_kary_det_pemb?->first()?->no_rek,
                        $row->m_kary?->m_kary_det_pemb?->first()?->atas_nama_rek,
                        "Surabaya", "", "", "IDR", $valNetto,
                        "GAJI " . strtoupper($periode), "", "", "IBU", "", "MANDIRI", "JAKARTA", 
                        "", "", "", "N", 
                        "","","","","","","","","","","","","","","","","","","","","","", "E" // Tepat 41 elemen
                    ];
                    $totalTransfer += $valNetto;
                }
            }

            // 4. Tambahkan Baris REKAP di paling bawah
            if ($grandTotalKoperasi > 0) {
                $rows[] = [
                    $rekKoperasi, $namaRekKoperasi, "Surabaya", "", "", "IDR", (int)$grandTotalKoperasi,
                    "GAJI " . strtoupper($periode), "", "", "IBU", "", "MANDIRI", "JAKARTA", 
                    "", "", "", "N", 
                    "","","","","","","","","","","","","","","","","","","","","","", "E" // Tepat 41 elemen
                ];
                $totalTransfer += $grandTotalKoperasi;
            }

            if ($grandTotalKantor > 0) {
                $rows[] = [
                    $rekKantor, $namaRekKantor, "Surabaya", "", "", "IDR", (int)$grandTotalKantor,
                    "GAJI " . strtoupper($periode), "", "", "IBU", "", "MANDIRI", "JAKARTA", 
                    "", "", "", "N", 
                    "","","","","","","","","","","","","","","","","","","","","","", "E" // Tepat 41 elemen
                ];
                $totalTransfer += $grandTotalKantor;
            }

            $fileName = "export-gaji-mandiri-rekap-" . \Carbon\Carbon::now()->format("YmdHis") . ".csv";
            $noRekSumber = $branch->no_rek_cabang ?? "2911777557";
            $tanggalGaji = \Carbon\Carbon::now()->format("Ymd");

            return response()->streamDownload(function () use ($rows, $totalTransfer, $noRekSumber, $tanggalGaji) {
                $file = fopen("php://output", "w");
                
                // Header Mandiri (P)
                // Berisi 5 elemen isi + 36 elemen kosong = Tepat 41 elemen
                fputcsv($file, [
                    "P", $tanggalGaji, $noRekSumber, count($rows), (int)$totalTransfer,
                    "","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","",""
                ]);
                
                // Detail Transfer
                foreach ($rows as $r) {
                    fputcsv($file, $r);
                }
                
                fclose($file);
            }, $fileName, ["Content-Type" => "text/csv; charset=UTF-8"]);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage() . " line " . $e->getLine()], 500);
        }
    }

    public function scopeowndata($model)
    {
        //dd(auth()->user());
        $kary_id = auth()->user()->m_kary_id;
        return $model->whereHas("t_final_gaji_det", function ($q) use (
            $kary_id
        ) {
            $q->where("m_kary_id", $kary_id);
        });
    }
}
