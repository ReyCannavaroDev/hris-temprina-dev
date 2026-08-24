<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;

class m_kary extends \App\Models\BasicModels\m_kary
{
    private $helper;
    private $respo;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
        $this->respo = getCore("Respo");
        $this->joins = array_values(array_filter($this->joins, function ($join) {
            return $join !== "m_jam_kerja.id=m_kary.m_jam_kerja_id";
        }));

        if (app()->request->skip_m_kary_details) {
            $this->details = [];
        }
    }

    public $fileColumns = [
        /*file_column*/
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function default_users()
    {
        return $this->belongsTo(
            "App\Models\BasicModels\default_users",
            "id",
            "m_kary_id"
        );
    }

    public function m_standart_gaji_n()
    {
        return $this->hasMany(
            "App\Models\BasicModels\m_standart_gaji",
            "m_kary_id",
            "id"
        );
    }

    public function m_standart_gaji_latest()
    {
        return $this->hasOne(
            "App\Models\BasicModels\m_standart_gaji",
            "m_kary_id",
            "id"
        )->latestOfMany(); 
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $kode = @$this->helper->generateNomor("KODE KARYAWAN");
        $nip = @$this->helper->generateNomor("KODE NIP KARYAWAN");
        @$arrayData['kode'] = null;
        $newArrayData = array_merge($arrayData, [
          "nip" => $arrayData["nip"] ?? '0' . $nip,
          "kode" => $kode,
          "no_registrasi" => $arrayData["no_registrasi"] ?? substr($kode, -10),
        ]);
        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        $arrayData['is_sync'] = false;

        return [
            "model" => $model,
            "data"  => $arrayData,
        ];
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        if (@$arrayData["m_dir_id"]) {
            default_users::where("m_kary_id", $id)->update([
                "m_dir_id" => $arrayData["m_dir_id"],
            ]);
        }

        if (@$arrayData["is_active"]) {
            default_users::where("m_kary_id", $id)->update([
                "is_active" => $arrayData["is_active"],
            ]);
        }

        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "is_sync" => false,
            ]),
        ];
    }

    public function transformRowData(array $row)
    {
        $object = [];
        if (app()->request->detail) {
            // $data = \DB::select("select public.employee_attendance(?,?)",[Date('Y-m-d'),$row['id'] ??0]);
            // $data = json_decode($data[0]->employee_attendance);
            $data = $this->infoCuti($row["id"]);
            $object["info_cuti"] = $data;

            $jadwal_kerja = \DB::table("t_jadwal_kerja as t")
                ->selectRaw("t.*")
                ->join("m_general as g", "g.id", "t.tipe_jam_kerja_id")
                ->where("t.tipe_jam_kerja_id", $row["tipe_jam_kerja_id"])
                ->where("status", "POSTED")
                ->first();
            $object["jadwal_kerja"] = $jadwal_kerja;
        }
        $object["nomor_ktp"] =
            \DB::table("m_kary_det_kartu")
                ->where("m_kary_id", $row["id"] ?? 0)
                ->value("ktp_no") ?? null;

        return array_merge($row, $object);
    }

    public function custom_infoCuti()
    {
        $kary_id = app()->request->m_kary_id;
        if(!$kary_id)
        {
            return $this->helper->customResponse(
                "Data karyawan tidak ditemukan",
                404
            );
        }
        $data = $this->infoCuti($kary_id);
        return $this->helper->customResponse("OK", 200, $data);

    }

    // public function infoCuti($id)
    // {
    //     $m_kary = m_kary::findOrFail($id);

    //     $jatah_cuti_reguler = $m_kary->cuti_jatah_reguler ?? 0;
    //     $sisa_cuti_tahun_lalu = $m_kary->cuti_jatah_tahun_lalu ?? 0;
    //     $sisa_cuti_reguler = $jatah_cuti_reguler ?? 0;

    //     $adj_cuti = t_cuti_adjustment::where('m_kary_id', $m_kary->id)->sum('value') ?? 0;

    //     $t_cuti = t_cuti::where('m_kary_id', $id)
    //     ->where('status', 'APPROVED')
    //     ->whereYear('date_from', Carbon::now()->year)
    //     ->whereHas('tipe_cuti', function($q){
    //         $q->where('key', '02');
    //     })->whereHas('alasan', function($q2){
    //         $q2->where('value_2', 'true');
    //     })->sum('interval') ?? 0;
    //     // dd($t_cuti);

    //     $sisa_cuti_reguler += $adj_cuti;

    //     $pakai_dari_tahun_lalu = min($t_cuti, $sisa_cuti_tahun_lalu);
    //     $sisa_cuti_tahun_lalu -= $pakai_dari_tahun_lalu;

    //     $sisa_pemakaian = $t_cuti - $pakai_dari_tahun_lalu;
    //     if ($sisa_pemakaian > 0) {
    //         $sisa_cuti_reguler -= $sisa_pemakaian;
    //     }

    //     $sisa_cuti_tahun_lalu = max($sisa_cuti_tahun_lalu, 0);
    //     $sisa_cuti_reguler = max($sisa_cuti_reguler, 0);

    //     return [
    //         'cuti_reguler' => $jatah_cuti_reguler,
    //         'sisa_cuti_tahun_lalu' => $sisa_cuti_tahun_lalu,
    //         'sisa_cuti_reguler' => $sisa_cuti_reguler,
    //     ];
    // }
    public function infoCuti($id)
    {
        $m_kary = m_kary::findOrFail($id);

        $cuti_reguler = $m_kary->cuti_jatah_reguler ?? 0;
        $cuti_tahun_lalu = $m_kary->cuti_jatah_tahun_lalu ?? 0;

        // $adj_cuti = t_cuti_adjustment::where('m_kary_id', $id)
        //     ->sum('value') ?? 0;
        $adj_cuti = 0;

        $cuti_reguler += $adj_cuti;

        $cuti_terpakai =
            t_cuti::where("m_kary_id", $id)
                ->where("status", "APPROVED")
                ->whereYear("date_from", Carbon::now()->year)
                ->whereHas("tipe_cuti", fn($q) => $q->where("key", "02"))
                ->whereHas("alasan", fn($q) => $q->where("value_2", "true"))
                ->sum("interval") ?? 0;

        $pakai_tahun_lalu = min($cuti_terpakai, $cuti_tahun_lalu);
        $sisa_cuti_tahun_lalu = $cuti_tahun_lalu - $pakai_tahun_lalu;

        $pakai_reguler = $cuti_terpakai - $pakai_tahun_lalu;
        $sisa_cuti_reguler = $cuti_reguler - $pakai_reguler;

        $sisa_cuti_tahun_lalu = max($sisa_cuti_tahun_lalu, 0);
        $sisa_cuti_reguler = max($sisa_cuti_reguler, 0);

        return [
            "cuti_reguler" => $cuti_reguler,
            "sisa_cuti_reguler" => $sisa_cuti_reguler,
            "cuti_tahun_lalu" => $cuti_tahun_lalu,
            "sisa_cuti_tahun_lalu" => $sisa_cuti_tahun_lalu,
        ];
    }

    public function resetCuti()
    {
        $all_kary = m_kary::all();
        foreach ($all_kary as $m_kary) {
            $m_kary->cuti_jatah_tahun_lalu = $this->infoCuti($m_kary->id)[
                "sisa_cuti_reguler"
            ];
            $m_kary->save();
        }
    }

    public function public_resetCuti()
    {
        return $this->resetCuti();
    }

    private function generateNik($compId, $dirId, $divisiId, $posisiId)
    {
        $currentDateTime = \Carbon\Carbon::now();

        $year = $currentDateTime->format("Y");
        $month = $currentDateTime->format("m");
        $day = $currentDateTime->format("d");

        $lastInsertedId = m_kary::max("id");

        $newId = $lastInsertedId + 1;

        // Create a formatted nik
        $formattedNik = sprintf(
            "%s%s%s%s%s%04d",
            $year,
            $month,
            $day,
            $compId,
            $dirId,
            $newId
        );

        return $formattedNik;
    }

    public function custom_resetCuti($request)
    {
        try {
            \DB::beginTransaction();
            $employees = m_kary::where("is_active", true)->get();

            foreach ($employees as $employee) {
                $employmentStartDate = Carbon::parse($employee->tgl_masuk);

                $yearsOfWork = $employmentStartDate->diffInYears(Carbon::now());

                $baseLeaveDays = 12;
                $additionalLeaveDays = [3, 5, 0, 3, 0, 0, 3, 0, 5];

                $employee->cuti_sisa_reguler =
                    $yearsOfWork >= 1
                        ? $baseLeaveDays +
                            $additionalLeaveDays[
                                ($yearsOfWork - 1) % count($additionalLeaveDays)
                            ]
                        : -4;

                $employee->cuti_sisa_panjang = 16;
                $employee->exp_date_cuti = Carbon::now()
                    ->addYear()
                    ->format("Y-m-d");

                $employee->save();
            }

            \DB::commit();
        } catch (\Exception $e) {
            return response()->json([
                "errors" => $e->getMessage(),
            ]);
        }
    }

    public function custom_postKaryawan($request)
    {
        try {
            \DB::beginTransaction();

            $idPelamar = $request->id;
            $status = $request->status;
            $data = t_pelamar::where("id", $idPelamar)->first();
            $dataBlacklist = m_blacklist::all();
            if ($status === "TOLAK") {
                $data->update([
                    "status" => "rejected",
                ]);

                return response()->json([
                    "message" => "Akun Ini Terblacklis",
                ]);
            }
            foreach ($dataBlacklist as $blacklist) {
                if ($blacklist->no_ktp === $data->ktp_no) {
                    $data->update([
                        "status" => "blacklist",
                    ]);

                    return response()->json([
                        "message" => "Akun Ini Terblacklis",
                    ]);
                }
            }

            $karyawan = m_kary::create([
                "ref_id" => $data["id"],
                "m_comp_id" => $data["m_comp_id"] ?? null,
                "m_dir_id" => $data["m_dir_id"] ?? null,
                "m_divisi_id" => $data["m_divisi_id"] ?? null,
                "m_dept_id" => $data["m_dept_id"] ?? null,
                "m_zona_id" => $data["m_zona_id"] ?? 0,
                "grading_id" => $data["grading_id"] ?? 0,
                "costcontre_id" => $data["costcontre_id"] ?? 0,
                "kode" => $this->helper->generateNomor("KODE KARYAWAN"),
                "m_posisi_id" => $data["m_posisi_id"] ?? null,
                "m_jam_kerja_id" => $data["m_jam_kerja_id"] ?? 0,
                "kode_presensi" => $data["kode_presensi"] ?? "",
                "nik" =>
                    $this->generateNik(
                        $data["m_comp_id"],
                        $data["m_dir_id"],
                        $data["m_divisi_id"],
                        $data["m_posisi_id"]
                    ) ?? null,
                "nama_depan" => $data["nama_depan"] ?? "",
                "nama_belakang" => $data["nama_belakang"] ?? "",
                "nama_lengkap" => $data["nama_pelamar"] ?? "",
                "nama_panggilan" => $data["nama_pelamar"] ?? "",
                "jk_id" => $data["jk_id"] ?? null,
                "tempat_lahir" => $data["tempat_lahir"] ?? null,
                "tgl_lahir" => $data["tgl_lahir"] ?? null,
                "provinsi_id" => $data["provinsi_id"] ?? 0,
                "kota_id" => $data["kota_id"] ?? 0,
                "kecamatan_id" => $data["kecamatan_id"] ?? 0,
                "kode_pos" => $data["kode_pos"] ?? 0,
                "alamat_asli" => $data["alamat_asli"] ?? "",
                "alamat_domisili" => $data["alamat_domisili"] ?? "",
                "no_tlp" => $data["telp"] ?? "",
                "no_tlp_lainnya" => $data["no_tlp_lainnya"] ?? "",
                "no_darurat" => $data["no_darurat"] ?? "",
                "nama_kontak_darurat" => $data["nama_kontak_darurat"] ?? "",
                "agama_id" => $data["agama_id"] ?? 0,
                "gol_darah_id" => $data["gol_darah_id"] ?? 0,
                "status_nikah_id" => $data["status_nikah_id"] ?? 0,
                "tanggungan_id" => $data["tanggungan_id"] ?? 0,
                "hub_dgn_karyawan" => $data["hub_dgn_karyawan"] ?? "",
                "cuti_jatah_reguler" => $data["cuti_jatah_reguler"] ?? 12,
                "cuti_sisa_reguler" => $data["cuti_sisa_reguler"] ?? 12,
                "cuti_panjang" => $data["cuti_panjang"] ?? 20,
                "cuti_sisa_panjang" => $data["cuti_sisa_panjang"] ?? 20,
                "status_kary_id" => $data["status_kary_id"] ?? null,
                "lama_kontrak_awal" => $data["lama_kontrak_awal"] ?? null,
                "lama_kontrak_akhir" => $data["lama_kontrak_akhir"] ?? null,
                "tgl_masuk" => $data["tgl_masuk"] ?? null,
                "tgl_berhenti" => $data["tgl_berhenti"] ?? null,
                "alasan_berhenti" => $data["alasan_berhenti"] ?? null,
                "uk_baju" => $data["uk_baju"] ?? "",
                "uk_celana" => $data["uk_celana"] ?? "",
                "uk_sepatu" => $data["uk_sepatu"] ?? "",
                "desc" => $data["desc"] ?? null,
                "is_active" => $data["is_active"] ?? true,
                "m_standart_gaji_id" => $data["m_standart_gaji_id"] ?? 0,
                "periode_gaji_id" => $data["periode_gaji_id"] ?? 0,
            ]);

            \DB::commit();

            return response()->json([
                "message" => "Registrasi Karyawan Berhasil",
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                "errors" => $e->getMessage(),
            ]);
        }
    }

    /**
     * collection data diri karyawan
     * for mobile profile
     */

    private function defaultDataDiri()
    {
        return [
            "id" => null,
            "m_comp_id" => null,
            "m_dir_id" => null,
            "m_divisi_id" => null,
            "m_dept_id" => null,
            "m_zona_id" => null,
            "grading_id" => null,
            "costcontre_id" => null,
            "kode" => null,
            "m_posisi_id" => null,
            "m_jam_kerja_id" => null,
            "kode_presensi" => null,
            "nik" => null,
            "nama_depan" => null,
            "nama_belakang" => null,
            "nama_lengkap" => null,
            "nama_panggilan" => null,
            "jk_id" => null,
            "tempat_lahir" => null,
            "tgl_lahir" => null,
            "provinsi_id" => null,
            "kota_id" => null,
            "kecamatan_id" => null,
            "kode_pos" => null,
            "alamat_asli" => null,
            "alamat_domisili" => null,
            "no_tlp" => null,
            "no_tlp_lainnya" => null,
            "no_darurat" => null,
            "nama_kontak_darurat" => null,
            "agama_id" => null,
            "gol_darah_id" => null,
            "status_nikah_id" => null,
            "tanggungan_id" => null,
            "hub_dgn_karyawan" => null,
            "cuti_jatah_reguler" => null,
            "cuti_sisa_reguler" => null,
            "cuti_panjang" => null,
            "cuti_sisa_panjang" => null,
            "status_kary_id" => null,
            "lama_kontrak_awal" => null,
            "lama_kontrak_akhir" => null,
            "tgl_masuk" => null,
            "tgl_berhenti" => null,
            "alasan_berhenti" => null,
            "uk_baju" => null,
            "uk_celana" => null,
            "uk_sepatu" => null,
            "desc" => null,
            "is_active" => null,
            "creator_id" => null,
            "last_editor_id" => null,
            "created_at" => null,
            "updated_at" => null,
            "m_standart_gaji_id" => null,
            "periode_gaji_id" => null,
            "ref_id" => null,
            "presensi_lokasi_default_id" => null,
            "exp_date_cuti" => null,
            "limit_potong" => null,
            "atasan_id" => null,
            "cuti_p24" => null,
            "cuti_sisa_p24" => null,
            "dir" => null,
            "div" => null,
            "dept" => null,
            "zona" => null,
            "grading" => null,
            "posisi" => null,
            "jam_kerja" => null,
            "jk" => null,
            "provinsi" => null,
            "kota" => null,
            "kecamatan" => null,
            "agama" => null,
            "gol_darah" => null,
            "tanggungan" => null,
            "costcontre" => null,
            "status_nikah" => null,
            "ktp_no" => null,
            "ktp_foto" => null,
            "pas_foto" => null,
            "kk_no" => null,
            "kk_foto" => null,
            "npwp_no" => null,
            "npwp_foto" => null,
            "npwp_tgl_berlaku" => null,
            "bpjs_tipe_id" => null,
            "bpjs_no" => null,
            "bpjs_no_kesehatan" => null,
            "bpjs_no_ketenagakerjaan" => null,
            "bpjs_foto" => null,
            "berkas_lain" => null,
            "desc_file" => null,
        ];
    }

    public function custom_data_diri($req)
    {
        $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
        $data = [];
        $data = m_kary::selectRaw(
            "
                m_kary.*,
                dir.nama dir,
                d.name div,
                dp.nama dept,
                z.nama zona,
                g.value grading,
                z.nama zona,
                p.name posisi,
                jk.value jk,
                prov.value provinsi,
                kota.value kota,
                kec.value kecamatan,
                agama.value agama,
                gol_darah.value gol_darah,
                tanggungan.value tanggungan,
                costcontre.value costcontre,
                status_nikah.value status_nikah,
                sbu.name nama_sbu,
                sub.name nama_sub,
                branch.name nama_branch,
                default_users.username,
                atasan.nama_lengkap nama_atasan
            "
        )
            ->leftJoin("m_dir as dir", "dir.id", "m_kary.m_dir_id")
            ->leftJoin("m_divisi as d", "d.id", "m_kary.m_divisi_id")
            ->leftJoin("m_dept as dp", "dp.id", "m_kary.m_dept_id")
            ->leftJoin("m_zona as z", "z.id", "m_kary.m_zona_id")
            ->leftJoin("m_general as g", "g.id", "m_kary.grading_id")
            ->leftJoin("m_general as c", "c.id", "m_kary.costcontre_id")
            ->leftJoin("m_posisi as p", "p.id", "m_kary.m_posisi_id")
            // ->leftJoin('m_jam_kerja as j','j.id','m_kary.m_jam_kerja_id')
            ->leftJoin("m_general as jk", "jk.id", "m_kary.jk_id")
            ->leftJoin("m_general as prov", "prov.id", "m_kary.provinsi_id")
            ->leftJoin("m_general as kota", "kota.id", "m_kary.kota_id")
            ->leftJoin("m_general as kec", "kec.id", "m_kary.kecamatan_id")
            ->leftJoin("m_general as agama", "agama.id", "m_kary.agama_id")
            ->leftJoin(
                "m_general as gol_darah",
                "gol_darah.id",
                "m_kary.gol_darah_id"
            )
            ->leftJoin(
                "m_general as status_nikah",
                "status_nikah.id",
                "m_kary.status_nikah_id"
            )
            ->leftJoin(
                "m_general as tanggungan",
                "tanggungan.id",
                "m_kary.tanggungan_id"
            )
            ->leftJoin(
                "m_general as costcontre",
                "costcontre.id",
                "m_kary.costcontre_id"
            )
            ->leftJoin("m_comp as sbu", "sbu.id", "m_kary.m_comp_id")
            ->leftJoin("m_subcomp as sub", "sub.id", "m_kary.m_subcomp_id")
            ->leftJoin("m_branch as branch", "branch.id", "m_kary.m_branch_id")
            ->leftJoin("default_users", "default_users.m_kary_id", "m_kary.id")
            ->leftJoin("m_kary as atasan", "m_kary.atasan_id", "atasan.id")
            ->where("m_kary.id", $id_kary)
            ->first();
        if (!$data) {
            $data = $this->defaultDataDiri();
        } else {
            $det_kartu = m_kary_det_kartu::with(["bpjs_tipe"])
                ->where("m_kary_id", @$id_kary ?? 0)
                ->first();
            $det_pemb = m_kary_det_pemb::with([
                "periode_gaji",
                "metode",
                "tipe",
                "bank",
            ])
                ->where("m_kary_id", @$id_kary ?? 0)
                ->first();
            if ($det_kartu) {
                $data["ktp_no"] = $det_kartu->ktp_no ?? null;
                $data["ktp_foto"] = $det_kartu->ktp_foto ?? null;
                $data["pas_foto"] = $det_kartu->pas_foto ?? null;
                $data["kk_no"] = $det_kartu->kk_no ?? null;
                $data["kk_foto"] = $det_kartu->kk_foto ?? null;
                $data["npwp_no"] = $det_kartu->npwp_no ?? null;
                $data["npwp_foto"] = $det_kartu->npwp_foto ?? null;
                $data["npwp_tgl_berlaku"] =
                    $det_kartu->npwp_tgl_berlaku ?? null;
                $data["bpjs_tipe"] = $det_kartu->bpjs_tipe->value ?? null;
                $data["bpjs_tipe_id"] = $det_kartu->bpjs_tipe_id ?? null;
                $data["bpjs_no"] = $det_kartu->bpjs_no ?? null;
                $data["bpjs_no_kesehatan"] =
                    $det_kartu->bpjs_no_kesehatan ?? null;
                $data["bpjs_no_ketenagakerjaan"] =
                    $det_kartu->bpjs_no_ketenagakerjaan ?? null;
                $data["bpjs_foto"] = $det_kartu->bpjs_foto ?? null;
                $data["berkas_lain"] = $det_kartu->berkas_lain ?? null;
                $data["desc_file"] = $det_kartu->desc_file ?? null;
                $data["periode_gaji_id"] = $det_pemb->periode_gaji->id ?? null;
                $data["periode_gaji"] = $det_pemb->periode_gaji->value ?? null;
                $data["metode"] = $det_pemb->metode->value ?? null;
                $data["metode_id"] = $det_pemb->metode->id ?? null;
                $data["tipe"] = $det_pemb->tipe->value ?? null;
                $data["tipe_id"] = $det_pemb->tipe->id ?? null;
                $data["bank"] = $det_pemb->bank->value ?? null;
                $data["bank_id"] = $det_pemb->bank->id ?? null;
                $data["no_rek"] = $det_pemb->no_rek ?? null;
                $data["atas_nama_rek"] = $det_pemb->atas_nama_rek ?? null;
            }
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    private function uploadFile($file)
    {
        if ($file) {
            $fileName =
                md5(time()) .
                ":::" .
                $file->getClientOriginalName() .
                "." .
                $file->getClientOriginalExtension();
            $file->move(public_path("uploads/m_kary_det_kartu"), $fileName);
            return $fileName;
        }
        return null;
    }

    public function custom_data_diri_update($req)
    {
        try {
            \DB::beginTransaction();
            $id_kary = default_users::where("id", auth()->user()->id)
                ->pluck("m_kary_id")
                ->first();
            $kar = m_kary::where("id", $id_kary)->first();
            if (!$kar) {
                // buat karyawan jika tidak ditemukan kary
                $createHeader = $this->create([
                    "m_comp_id" => $req->m_comp_id,
                    "m_dir_id" => $req->m_dir_id,
                    "m_divisi_id" => $req->m_divisi_id,
                    "m_dept_id" => $req->m_dept_id,
                    "m_zona_id" => $req->m_zona_id,
                    "grading_id" => $req->grading_id ?? null,
                    "costcontre_id" => $req->costcontre_id,
                    "kode" => $req->kode ?? null,
                    "m_posisi_id" => $req->m_posisi_id,
                    "m_jam_kerja_id" => $req->m_jam_kerja_id,
                    "kode_presensi" => $req->kode_presensi ?? null,
                    "nik" => $req->nik,
                    "nama_depan" => $req->nama_depan,
                    "nama_belakang" => $req->nama_belakang,
                    "nama_lengkap" => $req->nama_lengkap,
                    "nama_panggilan" => $req->nama_panggilan,
                    "jk_id" => $req->jk_id,
                    "tempat_lahir" => $req->tempat_lahir,
                    "tgl_lahir" => $req->tgl_lahir,
                    "provinsi_id" => $req->provinsi_id,
                    "kota_id" => $req->kota_id,
                    "kecamatan_id" => $req->kecamatan_id,
                    "kode_pos" => $req->kode_pos,
                    "alamat_asli" => $req->alamat_asli,
                    "alamat_domisili" => $req->alamat_domisili,
                    "no_tlp" => $req->no_tlp,
                    "no_tlp_lainnya" => $req->no_tlp_lainnya,
                    "no_darurat" => $req->no_darurat,
                    "nama_kontak_darurat" => $req->nama_kontak_darurat,
                    "agama_id" => $req->agama_id,
                    "gol_darah_id" => $req->gol_darah_id,
                    "status_nikah_id" => $req->status_nikah_id,
                    "tanggungan_id" => $req->tanggungan_id,
                    "hub_dgn_karyawan" => $req->hub_dgn_karyawan,
                    "cuti_jatah_reguler" => $req->cuti_jatah_reguler,
                    "cuti_sisa_reguler" => $req->cuti_sisa_reguler,
                    "cuti_panjang" => $req->cuti_panjang,
                    "cuti_sisa_panjang" => $req->cuti_sisa_panjang,
                    "status_kary_id" => $req->status_kary_id ?? null,
                    "lama_kontrak_awal" => $req->lama_kontrak_awal ?? null,
                    "lama_kontrak_akhir" => $req->lama_kontrak_akhir ?? null,
                    "tgl_masuk" => $req->tgl_masuk,
                    "tgl_berhenti" => $req->tgl_berhenti ?? null,
                    "alasan_berhenti" => $req->alasan_berhenti ?? null,
                    "uk_baju" => $req->uk_baju,
                    "uk_celana" => $req->uk_celana,
                    "uk_sepatu" => $req->uk_sepatu,
                    "desc" => $req->desc ?? null,
                ]);
                // update user -> isikan m_kary_id
                default_users::where("id", auth()->user()->id)->update([
                    "m_kary_id" => $createHeader->id,
                ]);
            } else {
                $createHeader = m_kary::where("id", $id_kary)->update([
                    "m_comp_id" => $req->m_comp_id ?? $kar->m_comp_id,
                    "m_dir_id" => $req->m_dir_id ?? $kar->m_dir_id,
                    "m_divisi_id" => $req->m_divisi_id ?? $kar->m_divisi_id,
                    "m_dept_id" => $req->m_dept_id ?? $kar->m_dept_id,
                    "m_zona_id" => $req->m_zona_id ?? $kar->m_zona_id,
                    "grading_id" =>
                        $req->grading_id ?? ($kar->grading_id ?? null),
                    "costcontre_id" =>
                        $req->costcontre_id ?? $kar->costcontre_id,
                    "kode" => $req->kode ?? ($kar->kode ?? null),
                    "m_posisi_id" => $req->m_posisi_id ?? $kar->m_posisi_id,
                    "m_jam_kerja_id" =>
                        $req->m_jam_kerja_id ?? $kar->m_jam_kerja_id,
                    "kode_presensi" =>
                        $req->kode_presensi ?? ($kar->kode_presensi ?? null),
                    "nik" => $req->nik ?? $kar->nik,
                    "nama_depan" => $req->nama_depan ?? $kar->nama_depan,
                    "nama_belakang" =>
                        $req->nama_belakang ?? $kar->nama_belakang,
                    "nama_lengkap" => $req->nama_lengkap ?? $kar->nama_lengkap,
                    "nama_panggilan" =>
                        $req->nama_panggilan ?? $kar->nama_panggilan,
                    "jk_id" => $req->jk_id ?? $kar->jk_id,
                    "tempat_lahir" => $req->tempat_lahir ?? $kar->tempat_lahir,
                    "tgl_lahir" => $req->tgl_lahir ?? $kar->tgl_lahir,
                    "provinsi_id" => $req->provinsi_id ?? $kar->provinsi_id,
                    "kota_id" => $req->kota_id ?? $kar->kota_id,
                    "kecamatan_id" => $req->kecamatan_id ?? $kar->kecamatan_id,
                    "kode_pos" => $req->kode_pos ?? $kar->kode_pos,
                    "alamat_asli" => $req->alamat_asli ?? $kar->alamat_asli,
                    "alamat_domisili" =>
                        $req->alamat_domisili ?? $kar->alamat_domisili,
                    "no_tlp" => $req->no_tlp ?? $kar->no_tlp,
                    "no_tlp_lainnya" =>
                        $req->no_tlp_lainnya ?? $kar->no_tlp_lainnya,
                    "no_darurat" => $req->no_darurat ?? $kar->no_darurat,
                    "nama_kontak_darurat" =>
                        $req->nama_kontak_darurat ?? $kar->nama_kontak_darurat,
                    "agama_id" => $req->agama_id ?? $kar->agama_id,
                    "gol_darah_id" => $req->gol_darah_id ?? $kar->gol_darah_id,
                    "status_nikah_id" =>
                        $req->status_nikah_id ?? $kar->status_nikah_id,
                    "tanggungan_id" =>
                        $req->tanggungan_id ?? $kar->tanggungan_id,
                    "hub_dgn_karyawan" =>
                        $req->hub_dgn_karyawan ?? $kar->hub_dgn_karyawan,
                    "cuti_jatah_reguler" =>
                        $req->cuti_jatah_reguler ?? $kar->cuti_jatah_reguler,
                    "cuti_sisa_reguler" =>
                        $req->cuti_sisa_reguler ?? $kar->cuti_sisa_reguler,
                    "cuti_panjang" => $req->cuti_panjang ?? $kar->cuti_panjang,
                    "cuti_sisa_panjang" =>
                        $req->cuti_sisa_panjang ?? $kar->cuti_sisa_panjang,
                    "status_kary_id" =>
                        $req->status_kary_id ?? ($kar->status_kary_id ?? null),
                    "lama_kontrak_awal" =>
                        $req->lama_kontrak_awal ??
                        ($kar->lama_kontrak_awal ?? null),
                    "lama_kontrak_akhir" =>
                        $req->lama_kontrak_akhir ??
                        ($kar->lama_kontrak_akhir ?? null),
                    "tgl_masuk" => $req->tgl_masuk ?? $kar->tgl_masuk,
                    "tgl_berhenti" =>
                        $req->tgl_berhenti ?? ($kar->tgl_berhenti ?? null),
                    "alasan_berhenti" =>
                        $req->alasan_berhenti ??
                        ($kar->alasan_berhenti ?? null),
                    "uk_baju" => $req->uk_baju ?? $kar->uk_baju,
                    "uk_celana" => $req->uk_celana ?? $kar->uk_celana,
                    "uk_sepatu" => $req->uk_sepatu ?? $kar->uk_sepatu,
                    "desc" => $req->desc ?? ($kar->desc ?? null),
                ]);
            }
            \DB::commit();

            if ($createHeader) {
                $check = m_kary_det_kartu::where(
                    "m_kary_id",
                    $id_kary
                )->first();
                $check_pemb = m_kary_det_pemb::where(
                    "m_kary_id",
                    $id_kary
                )->first();

                $file = $req->file("ktp_foto");
                $fileName_ktp = $this->uploadFile($file);
                // if(!$fileName_ktp) return $this->helper->customResponse('Foto KTP tidak valid, silahkan melakukan upload ulang file', 422);

                $file = $req->file("pas_foto");
                $fileName_pas = $this->uploadFile($file);
                // if(!$fileName_pas) return $this->helper->customResponse('Pas foto tidak valid, silahkan melakukan upload ulang file', 422);

                $file = $req->file("kk_foto");
                $fileName_kk = $this->uploadFile($file);
                // if(!$fileName_kk) return $this->helper->customResponse('Foto KK tidak valid, silahkan melakukan upload ulang file', 422);

                $file = $req->file("npwp_foto");
                $fileName_npwp = $this->uploadFile($file);
                // if(!$fileName_npwp) return $this->helper->customResponse('Foto NPWP tidak valid, silahkan melakukan upload ulang file', 422);

                $file = $req->file("bpjs_foto");
                $fileName_bpjs = $this->uploadFile($file);
                // if(!$fileName_bpjs) return $this->helper->customResponse('Foto BPJS tidak valid, silahkan melakukan upload ulang file', 422);

                $file = $req->file("berkas_lain");
                $fileName_berkas = $this->uploadFile($file);
                // if(!$fileName_berkas) return $this->helper->customResponse('Upload berkas lain tidak valid, silahkan melakukan upload ulang file', 422);
                \DB::beginTransaction();
                if ($check) {
                    \DB::table("m_kary_det_kartu")
                        ->where("m_kary_id", $id_kary)
                        ->update([
                            "m_kary_id" => $id_kary,
                            "ktp_no" => $req->ktp_no ?? @$check->ktp_no,
                            "ktp_foto" => $fileName_ktp ?? @$check->ktp_foto,
                            "pas_foto" => $fileName_pas ?? @$check->pas_foto,
                            "kk_no" => $req->kk_no ?? @$check->kk_no,
                            "kk_foto" => $fileName_kk ?? @$check->kk_foto,
                            "npwp_no" => $req->npwp_no ?? @$check->npwp_no,
                            "npwp_foto" => $fileName_npwp ?? @$check->npwp_foto,
                            "npwp_tgl_berlaku" =>
                                $req->npwp_tgl_berlaku ??
                                @$check->npwp_tgl_berlaku,
                            "bpjs_tipe_id" =>
                                $req->bpjs_tipe_id ?? @$check->bpjs_tipe_id,
                            "bpjs_no" => $req->bpjs_no ?? @$check->bpjs_no,
                            "bpjs_no_kesehatan" =>
                                $req->bpjs_no_kesehatan ??
                                @$check->bpjs_no_kesehatan,
                            "bpjs_no_ketenagakerjaan" =>
                                $req->bpjs_no_ketenagakerjaan ??
                                @$check->bpjs_no_ketenagakerjaan,
                            "bpjs_foto" => $fileName_bpjs ?? @$check->bpjs_foto,
                            "berkas_lain" =>
                                $fileName_berkas ?? @$check->berkas_lain,
                            "desc_file" =>
                                $req->desc_file ?? @$check->desc_file,
                        ]);
                } else {
                    \DB::table("m_kary_det_kartu")->insert([
                        "m_kary_id" => $id_kary,
                        "ktp_no" => @$req->ktp_no,
                        "ktp_foto" => @$fileName_ktp,
                        "pas_foto" => @$fileName_pas,
                        "kk_no" => @$req->kk_no,
                        "kk_foto" => @$fileName_kk,
                        "npwp_no" => @$req->npwp_no,
                        "npwp_foto" => @$fileName_npwp,
                        "npwp_tgl_berlaku" => @$req->npwp_tgl_berlaku,
                        "bpjs_tipe_id" => @$req->bpjs_tipe_id,
                        "bpjs_no" => @$req->bpjs_no,
                        "bpjs_no_kesehatan" => @$req->bpjs_no_kesehatan,
                        "bpjs_no_ketenagakerjaan" => @$req->bpjs_no_ketenagakerjaan,
                        "bpjs_foto" => @$fileName_bpjs,
                        "berkas_lain" => @$fileName_berkas,
                        "desc_file" => @$req->desc_file,
                    ]);
                }
                \DB::commit();

                \DB::beginTransaction();
                if ($check_pemb) {
                    \DB::table("m_kary_det_pemb")
                        ->where("m_kary_id", $id_kary)
                        ->update([
                            "bank_id" =>
                                @$req->bank_id ?? @$check_pemb->bank_id,
                            "no_rek" => @$req->no_rek ?? @$check_pemb->no_rek,
                            "atas_nama_rek" =>
                                @$req->atas_nama_rek ??
                                @$check_pemb->atas_nama_rek,
                        ]);
                } else {
                    \DB::table("m_kary_det_pemb")->insert([
                        "m_kary_id" => $id_kary,
                        "m_comp_id" => @$req->m_comp_id,
                        "m_dir_id" => @$req->m_dir_id,
                        "periode_gaji_id" => @$req->periode_gaji_id ?? 362,
                        "metode_id" => @$req->metode_id ?? 0,
                        "tipe_id" => @$req->tipe_id ?? 956,
                        "bank_id" => @$req->bank_id ?? 0,
                        "no_rek" => @$req->no_rek ?? 0,
                        "atas_nama_rek" => @$req->atas_nama_rek ?? 0,
                        "desc" => @$req->desc,
                    ]);
                }
                \DB::commit();
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
        return $this->helper->customResponse("Data diri berhasil diupdate");
    }

    /**
     * collection data pendidikan karyawan
     * for mobile profile
     */

    public function custom_pendidikan($req)
    {
        $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
        $tbl = "m_kary_det_pend";
        $data = m_kary_det_pend::query()
            ->selectRaw(
                "
                k.nama_lengkap karyawan,
                tingkat.value as tingkat,
                kota.value as kota,
                $tbl.*
            "
            )
            ->leftJoin("m_general as tingkat", "tingkat.id", "$tbl.tingkat_id")
            ->leftJoin("m_general as kota", "kota.id", "$tbl.kota_id")
            ->join("m_kary as k", "k.id", "$tbl.m_kary_id")
            ->orderBy("$tbl.created_at", "desc")
            ->where("$tbl.m_kary_id", $id_kary)
            ->get();
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_pendidikan_create($req)
    {
        \DB::beginTransaction();
        $fileName = null;

        if ($req->hasFile("ijazah_foto")) {
            $file = $req->file("ijazah_foto");
            $fileName =
                md5(time()) .
                ":::" .
                $file->getClientOriginalName() .
                "." .
                $file->getClientOriginalExtension();
            $file->move(public_path("uploads/m_kary_det_pend"), $fileName);
        }

        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_pend")->insert([
                "m_kary_id" => $id_kary,
                "tingkat_id" => $req->tingkat_id,
                "nama_sekolah" => $req->nama_sekolah,
                "thn_masuk" => $req->thn_masuk,
                "thn_lulus" => $req->thn_lulus,
                "kota_id" => $req->kota_id,
                "nilai" => $req->nilai,
                "jurusan" => $req->jurusan,
                "is_pend_terakhir" => $req->is_pend_terakhir,
                "ijazah_no" => $req->ijazah_no,
                "ijazah_foto" => $fileName,
                "desc" => $req->desc,
                "creator_id" => auth()->user()->id,
                "created_at" => date("Y-m-d H:i:s"),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pendidikan berhasil ditambahkan"
        );
    }

    public function custom_pendidikan_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $pendidikan = \DB::table("m_kary_det_pend")->find($id);

            if (!$pendidikan) {
                return $this->helper->customResponse(
                    "Data pendidikan tidak ditemukan",
                    404
                );
            }

            $fileName = $pendidikan->ijazah_foto;

            if ($req->hasFile("ijazah_foto")) {
                $oldFilePath = public_path(
                    "uploads/m_kary_det_pend/{$fileName}"
                );
                if ($fileName && file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $file = $req->file("ijazah_foto");
                $fileName =
                    md5(time()) .
                    ":::" .
                    $file->getClientOriginalName() .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/m_kary_det_pend"), $fileName);
            } else {
                $fileName = $pendidikan->ijazah_foto;
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_pend")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "tingkat_id" => $req->tingkat_id ?? $pendidikan->tingkat_id,
                    "nama_sekolah" =>
                        $req->nama_sekolah ?? $pendidikan->nama_sekolah,
                    "thn_masuk" => $req->thn_masuk ?? $pendidikan->thn_masuk,
                    "thn_lulus" => $req->thn_lulus ?? $pendidikan->thn_lulus,
                    "kota_id" => $req->kota_id ?? $pendidikan->kota_id,
                    "nilai" => $req->nilai ?? $pendidikan->nilai,
                    "jurusan" => $req->jurusan ?? $pendidikan->jurusan,
                    "is_pend_terakhir" =>
                        $req->is_pend_terakhir ?? $pendidikan->is_pend_terakhir,
                    "ijazah_no" => $req->ijazah_no ?? $pendidikan->ijazah_no,
                    "ijazah_foto" => $fileName,
                    "desc" => $req->desc ?? $pendidikan->desc,
                    "creator_id" => auth()->user()->id,
                    "updated_at" => date("Y-m-d H:i:s"),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pendidikan berhasil diperbarui"
        );
    }

    public function custom_pendidikan_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_pend::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pendidikan berhasil dihapus"
        );
    }

    public function custom_keluarga($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_kel::where("m_kary_id", $id_kary)
                ->select(
                    "m_kary_det_kel.*",
                    "keluarga.value AS keluarga",
                    "pend_terakhir.value AS pendidikan",
                    "jk.value AS jenis_kelamin",
                    "pekerjaan.value AS pekerjaan"
                )
                ->leftJoin(
                    "m_general AS keluarga",
                    "m_kary_det_kel.keluarga_id",
                    "=",
                    "keluarga.id"
                )
                ->leftJoin(
                    "m_general AS pend_terakhir",
                    "m_kary_det_kel.pend_terakhir_id",
                    "=",
                    "pend_terakhir.id"
                )
                ->leftJoin(
                    "m_general AS jk",
                    "m_kary_det_kel.jk_id",
                    "=",
                    "jk.id"
                )
                ->leftJoin(
                    "m_general AS pekerjaan",
                    "m_kary_det_kel.pekerjaan_id",
                    "=",
                    "pekerjaan.id"
                )
                ->orderBy("m_kary_det_kel.created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_keluarga_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_kel")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "keluarga_id" => $req->keluarga_id,
                "nama" => $req->nama,
                "pend_terakhir_id" => $req->pend_terakhir_id,
                "jk_id" => $req->jk_id,
                "pekerjaan_id" => $req->pekerjaan_id,
                "usia" => $req->usia,
                "desc" => $req->desc,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data keluarga berhasil ditambahkan"
        );
    }

    public function custom_keluarga_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $keluarga = \DB::table("m_kary_det_kel")->find($id);

            if (!$keluarga) {
                return $this->helper->customResponse(
                    "Data keluarga tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            \DB::table("m_kary_det_kel")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input(
                        "m_comp_id",
                        $keluarga->m_comp_id
                    ),
                    "m_dir_id" => $req->input("m_dir_id", $keluarga->m_dir_id),
                    "keluarga_id" => $req->input(
                        "keluarga_id",
                        $keluarga->keluarga_id
                    ),
                    "nama" => $req->input("nama", $keluarga->nama),
                    "pend_terakhir_id" => $req->input(
                        "pend_terakhir_id",
                        $keluarga->pend_terakhir_id
                    ),
                    "jk_id" => $req->input("jk_id", $keluarga->jk_id),
                    "pekerjaan_id" => $req->input(
                        "pekerjaan_id",
                        $keluarga->pekerjaan_id
                    ),
                    "usia" => $req->input("usia", $keluarga->usia),
                    "desc" => $req->input("desc", $keluarga->desc),
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data keluarga berhasil diperbarui"
        );
    }

    public function custom_keluarga_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_kel::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse("Data keluarga berhasil dihapus");
    }

    public function custom_pelatihan($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_pel::where("m_kary_id", $id_kary)
                ->select("m_kary_det_pel.*", "kota.value AS kota")
                ->leftJoin(
                    "m_general AS kota",
                    "m_kary_det_pel.kota_id",
                    "=",
                    "kota.id"
                )
                ->orderBy("m_kary_det_pel.created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_pelatihan_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_pel")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "nama_pel" => $req->nama_pel,
                "tahun" => $req->tahun,
                "nama_lem" => $req->nama_lem,
                "kota_id" => $req->kota_id ?? null,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pelatihan berhasil ditambahkan"
        );
    }

    public function custom_pelatihan_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $pelatihan = \DB::table("m_kary_det_pel")->find($id);

            if (!$pelatihan) {
                return $this->helper->customResponse(
                    "Data pelatihan tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            \DB::table("m_kary_det_pel")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input(
                        "m_comp_id",
                        $pelatihan->m_comp_id
                    ),
                    "m_dir_id" => $req->input("m_dir_id", $pelatihan->m_dir_id),
                    "nama_pel" => $req->input("nama_pel", $pelatihan->nama_pel),
                    "tahun" => $req->input("tahun", $pelatihan->tahun),
                    "nama_lem" => $req->input("nama_lem", $pelatihan->nama_lem),
                    "kota_id" => $req->input("kota_id", $pelatihan->kota_id),
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pelatihan berhasil diperbarui"
        );
    }

    public function custom_pelatihan_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_pel::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse("Data keluarga berhasil dihapus");
    }

    public function custom_prestasi($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_pres::where("m_kary_id", $id_kary)
                ->select(
                    "m_kary_det_pres.*",
                    "tingkat.value AS tingkat_prestasi"
                )
                ->leftJoin(
                    "m_general AS tingkat",
                    "m_kary_det_pres.tingkat_pres_id",
                    "=",
                    "tingkat.id"
                )
                ->orderBy("m_kary_det_pres.created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_prestasi_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_pres")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "nama_pres" => $req->nama_pres,
                "tahun" => $req->tahun,
                "tingkat_pres_id" => $req->tingkat_pres_id,
                "desc" => $req->desc,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data prestasi berhasil ditambahkan"
        );
    }

    public function custom_prestasi_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $prestasi = \DB::table("m_kary_det_pres")->find($id);

            if (!$prestasi) {
                return $this->helper->customResponse(
                    "Data prestasi tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            \DB::table("m_kary_det_pres")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input(
                        "m_comp_id",
                        $prestasi->m_comp_id
                    ),
                    "m_dir_id" => $req->input("m_dir_id", $prestasi->m_dir_id),
                    "nama_pres" => $req->input(
                        "nama_pres",
                        $prestasi->nama_pres
                    ),
                    "tahun" => $req->input("tahun", $prestasi->tahun),
                    "tingkat_pres_id" => $req->input(
                        "tingkat_pres_id",
                        $prestasi->tingkat_pres_id
                    ),
                    "desc" => $req->input("desc", $prestasi->desc),
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data prestasi berhasil diperbarui"
        );
    }

    public function custom_prestasi_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_pres::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse("Data prestasi berhasil dihapus");
    }

    public function custom_organisasi($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_org::where("m_kary_id", $id_kary)
                ->select(
                    "m_kary_det_org.*",
                    "jenis.value AS jenis_organisasi",
                    "kota.value AS kota"
                )
                ->leftJoin(
                    "m_general AS jenis",
                    "m_kary_det_org.jenis_org_id",
                    "=",
                    "jenis.id"
                )
                ->leftJoin(
                    "m_general AS kota",
                    "m_kary_det_org.kota_id",
                    "=",
                    "kota.id"
                )
                ->orderBy("m_kary_det_org.created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_organisasi_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_org")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "nama" => $req->nama,
                "tahun" => $req->tahun,
                "jenis_org_id" => $req->jenis_org_id,
                "kota_id" => $req->kota_id,
                "posisi" => $req->posisi,
                "desc" => $req->desc,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data organisasi berhasil ditambahkan"
        );
    }

    public function custom_organisasi_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $organisasi = \DB::table("m_kary_det_org")->find($id);

            if (!$organisasi) {
                return $this->helper->customResponse(
                    "Data organisasi tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            \DB::table("m_kary_det_org")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input(
                        "m_comp_id",
                        $organisasi->m_comp_id
                    ),
                    "m_dir_id" => $req->input(
                        "m_dir_id",
                        $organisasi->m_dir_id
                    ),
                    "nama" => $req->input("nama", $organisasi->nama),
                    "tahun" => $req->input("tahun", $organisasi->tahun),
                    "jenis_org_id" => $req->input(
                        "jenis_org_id",
                        $organisasi->jenis_org_id
                    ),
                    "kota_id" => $req->input("kota_id", $organisasi->kota_id),
                    "posisi" => $req->input("posisi", $organisasi->posisi),
                    "desc" => $req->input("desc", $organisasi->desc),
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data organisasi berhasil diperbarui"
        );
    }

    public function custom_organisasi_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_org::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data organisasi berhasil dihapus"
        );
    }

    public function custom_bahasa($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_bhs::where("m_kary_id", $id_kary)
                ->orderBy("created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_bahasa_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;
            \DB::table("m_kary_det_bhs")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "bhs_dikuasai" => $req->bhs_dikuasai,
                "nilai_lisan" => $req->nilai_lisan,
                "nilai_tertulis" => $req->nilai_tertulis,
                "level_lisan" => $req->level_lisan,
                "level_tertulis" => $req->level_tertulis,
                "desc" => $req->desc,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data bahasa berhasil ditambahkan"
        );
    }

    public function custom_bahasa_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $bahasa = \DB::table("m_kary_det_bhs")->find($id);

            if (!$bahasa) {
                return $this->helper->customResponse(
                    "Data bahasa tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            \DB::table("m_kary_det_bhs")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input("m_comp_id", $bahasa->m_comp_id),
                    "m_dir_id" => $req->input("m_dir_id", $bahasa->m_dir_id),
                    "bhs_dikuasai" => $req->input(
                        "bhs_dikuasai",
                        $bahasa->bhs_dikuasai
                    ),
                    "nilai_lisan" => $req->input(
                        "nilai_lisan",
                        $bahasa->nilai_lisan
                    ),
                    "nilai_tertulis" => $req->input(
                        "nilai_tertulis",
                        $bahasa->nilai_tertulis
                    ),
                    "level_lisan" => $req->input(
                        "level_lisan",
                        $bahasa->level_lisan
                    ),
                    "level_tertulis" => $req->input(
                        "level_tertulis",
                        $bahasa->level_tertulis
                    ),
                    "desc" => $req->input("desc", $bahasa->desc),
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse("Data bahasa berhasil diperbarui");
    }

    public function custom_bahasa_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_bhs::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse("Data bahasa berhasil dihapus");
    }

    public function custom_pk($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $data = m_kary_det_pk::where("m_kary_id", $id_kary)
                ->select("m_kary_det_pk.*", "kota.value AS kota")
                ->leftJoin(
                    "m_general AS kota",
                    "m_kary_det_pk.kota_id",
                    "=",
                    "kota.id"
                )
                ->orderBy("m_kary_det_pk.created_at", "desc")
                ->get();

            return $this->helper->customResponse("OK", 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_pk_create($req)
    {
        try {
            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $file = $req->file("surat_referensi");
            $fileName_berkas = $this->uploadFile($file);

            \DB::table("m_kary_det_pk")->insert([
                "m_kary_id" => $id_kary,
                "m_comp_id" => $req->m_comp_id ?? null,
                "m_dir_id" => $req->m_dir_id ?? null,
                "instansi" => $req->instansi,
                "bidang_usaha" => $req->bidang_usaha,
                "no_tlp" => $req->no_tlp,
                "posisi" => $req->posisi,
                "thn_masuk" => $req->thn_masuk,
                "thn_keluar" => $req->thn_keluar,
                "alamat_kantor" => $req->alamat_kantor,
                "kota_id" => $req->kota_id,
                "surat_referensi" => $fileName_berkas,
                "creator_id" => auth()->user()->id,
                "last_editor_id" => auth()->user()->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pengalaman kerja berhasil ditambahkan"
        );
    }

    public function custom_pk_update($req)
    {
        \DB::beginTransaction();
        $id = $req->id;
        try {
            $pengalaman_kerja = \DB::table("m_kary_det_pk")->find($id);

            if (!$pengalaman_kerja) {
                return $this->helper->customResponse(
                    "Data pengalaman kerja tidak ditemukan",
                    404
                );
            }

            $id_kary = default_users::find(auth()->user()->id)->m_kary_id;

            $file = $req->file("surat_referensi");
            $fileName_berkas = $pengalaman_kerja->surat_referensi;

            if ($file) {
                $oldFilePath = public_path(
                    "uploads/m_kary_det_pk/{$fileName_berkas}"
                );
                if ($fileName_berkas && file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
                $fileName_berkas = $this->uploadFile($file);
            } else {
                $fileName_berkas = $pengalaman_kerja->surat_referensi;
            }

            \DB::table("m_kary_det_pk")
                ->where("id", $id)
                ->update([
                    "m_kary_id" => $id_kary,
                    "m_comp_id" => $req->input(
                        "m_comp_id",
                        $pengalaman_kerja->m_comp_id
                    ),
                    "m_dir_id" => $req->input(
                        "m_dir_id",
                        $pengalaman_kerja->m_dir_id
                    ),
                    "instansi" => $req->input(
                        "instansi",
                        $pengalaman_kerja->instansi
                    ),
                    "bidang_usaha" => $req->input(
                        "bidang_usaha",
                        $pengalaman_kerja->bidang_usaha
                    ),
                    "no_tlp" => $req->input(
                        "no_tlp",
                        $pengalaman_kerja->no_tlp
                    ),
                    "posisi" => $req->input(
                        "posisi",
                        $pengalaman_kerja->posisi
                    ),
                    "thn_masuk" => $req->input(
                        "thn_masuk",
                        $pengalaman_kerja->thn_masuk
                    ),
                    "thn_keluar" => $req->input(
                        "thn_keluar",
                        $pengalaman_kerja->thn_keluar
                    ),
                    "alamat_kantor" => $req->input(
                        "alamat_kantor",
                        $pengalaman_kerja->alamat_kantor
                    ),
                    "kota_id" => $req->input(
                        "kota_id",
                        $pengalaman_kerja->kota_id
                    ),
                    "surat_referensi" => $fileName_berkas,
                    "last_editor_id" => auth()->user()->id,
                    "updated_at" => Carbon::now(),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pengalaman kerja berhasil diperbarui"
        );
    }

    public function custom_pk_delete($req)
    {
        \DB::beginTransaction();
        try {
            m_kary_det_pk::find($req->id)->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }

        return $this->helper->customResponse(
            "Data pengalaman kerja berhasil dihapus"
        );
    }

    public function scopeKaryawanOffice($model)
    {
        return $model
            ->join("m_general", "m_general.id", "m_kary.tipe_jam_kerja_id")
            ->whereRaw("lower(m_general.code) = 'office'");
    }

     public function scopelanding($model)
    {
        return $model
            ->leftJoin("m_branch", "m_branch.id", "m_kary.m_branch_id")
            ->leftJoin("m_posisi", 'm_posisi.id', 'm_kary.m_posisi_id')
            ->select("m_kary.*", "m_branch.name as m_branch.name", "m_branch.id as m_branch.id","m_posisi.name as m_posisi.name", "m_posisi.id as m_posisi.id");
            //->whereRaw("lower(m_general.code) = 'office'");
    }

    public function scopeKaryawanShift($model)
    {
        return $model
            ->join("m_general", "m_general.id", "m_kary.tipe_jam_kerja_id")
            ->whereRaw("lower(m_general.code) != 'office'")
            ->orWhere("m_kary.tipe_jam_kerja_id", null);
    }

    public function scopeNotInGenerate($model)
    {
        $t_jadwal_kerja_id = app()->request->t_jadwal_kerja_id;

        return $model->whereRaw(
            "
            m_kary.id not in(select d.m_kary_id from t_jadwal_kerja_det d 
            join t_jadwal_kerja t on t.id = d.t_jadwal_kerja_id where t.status = 'POSTED')
            or m_kary.id in(select d.m_kary_id from t_jadwal_kerja_det d 
            join t_jadwal_kerja t on t.id = d.t_jadwal_kerja_id where t.id = ?)
        ",
            [$t_jadwal_kerja_id ?? 0]
        );
    }

    public function scopeStructureOld($model)
    {
        $sbu = filter_var(@request("comp_id"), FILTER_VALIDATE_INT);
        $startLevelFilter = is_numeric(request("start_level"))
            ? (int) request("start_level")
            : 0;
        $endLevelFilter = is_numeric(request("end_level"))
            ? (int) request("end_level")
            : 1;
        if ($sbu) {
            $model = $model->whereHas("m_kary_det_jabatan", function (
                $query
            ) use ($sbu) {
                $query->where("m_comp_id", $sbu);
            });
        }
        $mkdj = "m_kary_det_jabatan";

        if (!is_null($startLevelFilter) && !is_null($endLevelFilter)) {
            $model = $model->whereHas($mkdj, function ($query) use (
                $mkdj,
                $startLevelFilter,
                $endLevelFilter
            ) {
                $query
                    ->where("is_primary", true)
                    ->leftJoin("m_divisi as md", "md.id", "{$mkdj}.m_divisi_id")
                    ->leftJoin("m_comp as mc", "mc.id", "{$mkdj}.m_comp_id")
                    ->leftJoin(
                        "m_subcomp as msc",
                        "msc.id",
                        "{$mkdj}.m_subcomp_id"
                    )
                    ->leftJoin("m_branch as mb", "mb.id", "{$mkdj}.m_branch_id")
                    ->leftJoin("m_posisi as mp", "mp.id", "{$mkdj}.m_posisi_id")
                    ->whereRaw(
                        "
                        CASE 
                            WHEN {$mkdj}.m_comp_id IS NULL THEN 1
                            WHEN {$mkdj}.m_subcomp_id IS NULL THEN 2
                            WHEN {$mkdj}.m_branch_id IS NULL THEN 3
                            WHEN {$mkdj}.m_divisi_id IS NULL THEN 4
                            ELSE (CAST(md.level AS INTEGER) + 5)
                        END Between ? AND ?
                    ",
                        [$startLevelFilter, $endLevelFilter]
                    );
            });
        }

        return $model
            ->where("m_kary.is_active", true)
            ->with([
                $mkdj => function ($query) use (
                    $mkdj,
                    $startLevelFilter,
                    $endLevelFilter
                ) {
                    $query
                        ->where("{$mkdj}.is_primary", true)
                        ->leftjoin(
                            "m_divisi as md",
                            "md.id",
                            "{$mkdj}.m_divisi_id"
                        )
                        ->leftjoin("m_comp as mc", "mc.id", "{$mkdj}.m_comp_id")
                        ->leftjoin(
                            "m_subcomp as msc",
                            "msc.id",
                            "{$mkdj}.m_subcomp_id"
                        )
                        ->leftjoin(
                            "m_branch as mb",
                            "mb.id",
                            "{$mkdj}.m_branch_id"
                        )
                        ->leftjoin(
                            "m_posisi as mp",
                            "mp.id",
                            "{$mkdj}.m_posisi_id"
                        )
                        ->leftJoin("m_kary as other_kary", function (
                            $join
                        ) use ($mkdj) {
                            $join
                                ->on(
                                    "other_kary.m_divisi_id",
                                    "=",
                                    "{$mkdj}.m_divisi_id"
                                )
                                ->on(
                                    "other_kary.id",
                                    "<>",
                                    "{$mkdj}.m_karyawan_id"
                                );
                        })
                        ->leftJoin("m_kary_det_jabatan as okdj", function (
                            $join
                        ) {
                            $join
                                ->on("okdj.m_karyawan_id", "=", "other_kary.id")
                                ->where("okdj.is_primary", true);
                        })
                        ->leftJoin(
                            "m_posisi as other_pos",
                            "other_pos.id",
                            "=",
                            "okdj.m_posisi_id"
                        )->selectRaw("{$mkdj}.m_karyawan_id, 
                CAST(md.level AS INT) AS level_divisi,
                {$mkdj}.m_comp_id, {$mkdj}.m_subcomp_id, {$mkdj}.m_branch_id, 
                {$mkdj}.m_divisi_id, md.name as nama_divisi, mc.name as nama_sbu, 
                msc.name as nama_sub, mb.name as nama_branch, mp.name as jabatan, md.parent_id,
                case 
                    when {$mkdj}.m_comp_id is null then '1'
                    when {$mkdj}.m_subcomp_id is null then '2'
                    when {$mkdj}.m_branch_id is null then '3'
                    when {$mkdj}.m_divisi_id is null then '4'
                    else (CAST(md.level AS INTEGER) + 5) -- Cast md.level to INTEGER
                end as level,
                CASE 
                    WHEN mp.is_same_level = TRUE 
                    THEN json_build_object('id', other_kary.id, 'jabatan', other_pos.name, 'nama_karyawan', other_kary.nama_lengkap) 
                    ELSE null
                END AS same_level_with
            ");
                },
            ])
            ->select("m_kary.id", "m_kary.nama_lengkap");
    }

    public function scopeStructure($model)
    {
        $sbu = filter_var(@request("comp_id"), FILTER_VALIDATE_INT);
        $startLevelFilter = is_numeric(request("start_level")) ? (int) request("start_level") : 0;
        $endLevelFilter = is_numeric(request("end_level")) ? (int) request("end_level") : 99; 

        if ($sbu) {
            $model = $model->whereHas("m_kary_det_jabatan", function ($query) use ($sbu) {
                $query->where("m_comp_id", $sbu);
            });
        }

        $mkdj = "m_kary_det_jabatan";

        // 1. Filter Karyawan berdasarkan range sequence level
        $model = $model->whereHas($mkdj, function ($query) use ($mkdj, $startLevelFilter, $endLevelFilter) {
            $query->where("{$mkdj}.is_primary", true)
                ->join("m_level_posisi_d as mlpd_filter", "mlpd_filter.m_posisi_id", "=", "{$mkdj}.m_posisi_id")
                ->join("m_level_posisi as mlp_filter", "mlp_filter.id", "=", "mlpd_filter.m_level_posisi_id")
                ->whereBetween("mlp_filter.sequence", [$startLevelFilter, $endLevelFilter]);
        });

        return $model
            ->where("m_kary.is_active", true)
            ->with([
                $mkdj => function ($query) use ($mkdj) {
                    $query->where("{$mkdj}.is_primary", true)
                        ->leftJoin("m_divisi as md", "md.id", "{$mkdj}.m_divisi_id")
                        ->leftJoin("m_comp as mc", "mc.id", "{$mkdj}.m_comp_id")
                        ->leftJoin("m_subcomp as msc", "msc.id", "{$mkdj}.m_subcomp_id")
                        ->leftJoin("m_branch as mb", "mb.id", "{$mkdj}.m_branch_id")
                        ->leftJoin("m_posisi as mp", "mp.id", "{$mkdj}.m_posisi_id")
                        // Join ke mapping level posisi user ini
                        ->leftJoin("m_level_posisi_d as mlpd", "mlpd.m_posisi_id", "=", "mp.id")
                        ->leftJoin("m_level_posisi as mlp", "mlp.id", "=", "mlpd.m_level_posisi_id")
                        
                        // Logic Peer (Same Level): Mencari karyawan lain yang m_level_posisi_id-nya sama
                        ->leftJoin("m_level_posisi_d as mlpd_peer", "mlpd_peer.m_level_posisi_id", "=", "mlpd.m_level_posisi_id")
                        ->leftJoin("m_kary_det_jabatan as okdj", function ($join) use ($mkdj) {
                            $join->on("okdj.m_posisi_id", "=", "mlpd_peer.m_posisi_id")
                                ->on("okdj.m_karyawan_id", "<>", "{$mkdj}.m_karyawan_id")
                                ->where("okdj.is_primary", true);
                        })
                        ->leftJoin("m_kary as other_kary", "other_kary.id", "=", "okdj.m_karyawan_id")
                        ->leftJoin("m_posisi as other_pos", "other_pos.id", "=", "okdj.m_posisi_id")
                        ->orderBy("mlp.sequence", "asc")
                        ->selectRaw("
                            {$mkdj}.m_karyawan_id, 
                            {$mkdj}.m_comp_id, 
                            {$mkdj}.m_subcomp_id, 
                            {$mkdj}.m_branch_id, 
                            {$mkdj}.m_divisi_id, 
                            md.name as nama_divisi, 
                            mc.name as nama_sbu, 
                            msc.name as nama_sub, 
                            mb.name as nama_branch, 
                            mp.name as jabatan, 
                            md.parent_id, 
                            mlp.sequence as level, 
                            mlp.level_name, 
                            -- Mengelompokkan rekan sejawat ke dalam JSON
                            CASE 
                                WHEN other_kary.id IS NOT NULL 
                                THEN json_build_object(
                                    'id', other_kary.id, 
                                    'jabatan', other_pos.name, 
                                    'nama_karyawan', other_kary.nama_lengkap
                                ) 
                                ELSE null
                            END AS same_level_with
                        ");
                },
            ])
            ->select("m_kary.id", "m_kary.nama_lengkap");
    }

    public function custom_structure($req)
    {
        $comp_id = $req->comp_id ?? 9;
        $startLevelFilter = is_numeric(request("start_level")) ? (int) request("start_level") : 0;
        $endLevelFilter = is_numeric(request("end_level")) ? (int) request("end_level") : 99; 

        $structure = m_subcomp::with([
            'm_branch' => function($q) {
                $q->select('id', 'm_subcomp_id', 'name'); 
                $q->with(['m_divisi' => function($q2) {
                    // Ambil yang paling atas dulu (Parent)
                    $q2->whereNull('parent_id') 
                        ->select('id', 'm_branch_id', 'name', 'parent_id')
                        ->with([
                            'general_name:id,value', 
                            // Ambil anaknya, dan ambil bapak dari anaknya (untuk label name)
                            'child_divisi' => function($q3) {
                                $q3->with(['name:id,value']);
                            }
                        ]);
                }]);
            }
        ])
        ->where('m_comp_id', $comp_id)
        ->get(['id', 'm_comp_id', 'name']);

        $structure->each(function ($subcomp) {
            $subcomp->m_branch?->each(function ($branch) {
                $branch->m_divisi?->transform(function ($divisi) {
                    return $this->formatDivisi($divisi);
                });
            });
        });

        return response()->json(['status' => 'success', 'data' => $structure], 200);
    }

    public function scopeStructureTest($model)
    {
        $sbu = filter_var(@request("comp_id"), FILTER_VALIDATE_INT);
        if ($sbu) {
            $model = $model->whereHas("m_kary_det_jabatan", function (
                $query
            ) use ($sbu) {
                $query->where("m_comp_id", $sbu);
            });
        }

        return $model
            ->join(
                "m_kary_det_jabatan as mkdj",
                "mkdj.m_karyawan_id",
                "=",
                "m_kary.id"
            )
            ->leftJoin("m_divisi as md", "md.id", "=", "mkdj.m_divisi_id")
            ->leftJoin("m_comp as mc", "mc.id", "=", "mkdj.m_comp_id")
            ->leftJoin("m_subcomp as msc", "msc.id", "=", "mkdj.m_subcomp_id")
            ->leftJoin("m_branch as mb", "mb.id", "=", "mkdj.m_branch_id")
            ->where("mkdj.is_primary", true)
            ->select("m_kary.nama_lengkap")->selectRaw("
                m_kary.nama_lengkap,
                m_posisi.desc_kerja,
                mkdj.m_karyawan_id,
                md.level as level_divisi,
                mkdj.m_comp_id,
                mkdj.m_subcomp_id,
                mkdj.m_branch_id,
                mkdj.m_divisi_id,
                md.name as nama_divisi,
                mc.name as nama_sbu,
                msc.name as nama_sub,
                mb.name as nama_branch,
                CASE 
                    WHEN mkdj.m_comp_id IS NULL THEN '1'
                    WHEN mkdj.m_subcomp_id IS NULL THEN '2'
                    WHEN mkdj.m_branch_id IS NULL THEN '3'
                    WHEN mkdj.m_divisi_id IS NULL THEN '4'
                    ELSE (CAST(md.level AS INTEGER) + 5)
                END as level
            ");
    }

    public function scopeSubordinate($model)
    {
        $atasanId = request("kary_id");
        $respo_id = request("respo_id") ?? 0;
        $respo_det = m_respo_d::where("m_respo_id", $respo_id)->get();
        if ($respo_det->count() > 0) {
            foreach ($respo_det as $r) {
                if ($r->m_role?->name === "HC") {
                    return $model;
                }
            }
        }

        return $model->where("m_kary.atasan_id", $atasanId);
    }

    public function scopeatasan($model)
    {
        $atasan_id =
            default_users::find(auth()->user()->id)?->m_kary_id ?? null;
        if ($atasan_id) {
            return $model->where("m_kary.atasan_id", $atasan_id);
        } else {
            return $model;
        }
    }

    //filte karyawan yang os
    public function scopeos($model)
    {
        $status_os = m_general::where(
            "group",
            "STATUS KARYAWAN OUTSOURCE"
        )->pluck("id");

        return $model->whereIn("m_kary.status_kary_id", $status_os);
    }

    //harian internal
    public function scopeharianinternal($model)
    {
        $status_os = m_general::where(
            "group",
            "STATUS KARYAWAN"
        )
        ->where("value", "HARIAN")
        ->pluck("id");

        return $model->whereIn("m_kary.status_kary_id", $status_os);
    }

    //filter karyawan yang bukan os
    public function scopenonos($model)
    {
        $status_os = m_general::where(
            "group",
            "STATUS KARYAWAN OUTSOURCE"
        )->pluck("id");

        return $model->whereNotIn("m_kary.status_kary_id", $status_os);
    }

    //filter karyawan by respo
    public function scoperespo($model)
    {
        $m_subcomp_id = request("m_subcomp_id") ?? null;
        $m_branch_id = request("m_branch_id") ?? null;

        if ($m_subcomp_id === "null") {
            $m_subcomp_id = null;
        }
        if ($m_branch_id === "null") {
            $m_branch_id = null;
        }

        return $model
            ->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                if (is_string($m_subcomp_id) && str_starts_with($m_subcomp_id, '[') && str_ends_with($m_subcomp_id, ']')) {
                    $m_subcomp_id = json_decode($m_subcomp_id, true);
                }
                if (is_array($m_subcomp_id)) {
                    $q->whereIn("m_kary.m_subcomp_id", $m_subcomp_id);
                } else if(is_string($m_subcomp_id) && str_contains($m_subcomp_id, ',')) {
                    $q->whereIn("m_kary.m_subcomp_id", explode(',', $m_subcomp_id));
                } else {
                    $q->where("m_kary.m_subcomp_id", $m_subcomp_id);
                }
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                if (is_string($m_branch_id) && str_starts_with($m_branch_id, '[') && str_ends_with($m_branch_id, ']')) {
                    $m_branch_id = json_decode($m_branch_id, true);
                }
                if (is_array($m_branch_id)) {
                    $q->whereIn("m_kary.m_branch_id", $m_branch_id);
                } else if(is_string($m_branch_id) && str_contains($m_branch_id, ',')) {
                    $q->whereIn("m_kary.m_branch_id", explode(',', $m_branch_id));
                } else {
                    $q->where("m_kary.m_branch_id", $m_branch_id);
                }
            });
    }

    public function scopejabatan($model)
    {
        $today = \Carbon\Carbon::now()->toDateString();

        $latestJadwal = \DB::table('t_jadwal_kerja_d_n')
            ->select('m_kary_id', \DB::raw('MAX(start_date) as latest_date'))
            ->where('status', 'AKTIF')
            ->where('start_date', '<=', $today)
            ->groupBy('m_kary_id');

        return $model
            ->leftJoin('m_comp', 'm_comp.id', '=', 'm_kary.m_comp_id')
            ->leftJoin('m_branch', 'm_branch.id', '=', 'm_kary.m_branch_id')
            ->leftJoin('m_divisi', 'm_divisi.id', '=', 'm_kary.m_divisi_id')
            ->leftJoin('m_general', 'm_general.id', '=', 'm_divisi.name')
            ->leftJoin('m_posisi', 'm_posisi.id', '=', 'm_kary.m_posisi_id')

            ->joinSub($latestJadwal, 'latest_j', function ($join) {
                $join->on('m_kary.id', '=', 'latest_j.m_kary_id');
            })
            ->join('t_jadwal_kerja_d_n as d', function ($join) {
                $join->on('d.m_kary_id', '=', 'latest_j.m_kary_id')
                    ->on('d.start_date', '=', 'latest_j.latest_date');
            })
            ->whereRaw('d.id = (SELECT MAX(id) FROM t_jadwal_kerja_d_n WHERE m_kary_id = d.m_kary_id AND start_date = d.start_date AND status = \'AKTIF\')')
            ->join('t_jadwal_kerja_n as n', 'n.id', '=', 'd.t_jadwal_kerja_n_id')
            

            ->select(
                'm_kary.*', 
                'm_comp.name as m_comp.name', 
                'm_branch.name as m_branch.name', 
                'm_general.value as m_general.value', 
                'm_posisi.name as m_posisi.name',
                'n.keterangan as t_jadwal_kerja_n.keterangan', 
                'n.id as t_jadwal_kerja_n.id'
            );
    }

    public function scopejadwal($model)
    {
        $today = \Carbon\Carbon::now()->toDateString();

        $latestJadwal = \DB::table('t_jadwal_kerja_d_n')
            ->select('m_kary_id', \DB::raw('MAX(start_date) as latest_date'))
            ->where('status', 'AKTIF')
            ->where('start_date', '<=', $today)
            ->groupBy('m_kary_id');

        return $model
            ->joinSub($latestJadwal, 'latest_j', function ($join) {
                $join->on('m_kary.id', '=', 'latest_j.m_kary_id');
            })
            ->join('t_jadwal_kerja_d_n as d', function ($join) {
                $join->on('d.m_kary_id', '=', 'latest_j.m_kary_id')
                    ->on('d.start_date', '=', 'latest_j.latest_date');
            })
            ->whereRaw('d.id = (SELECT MAX(id) FROM t_jadwal_kerja_d_n WHERE m_kary_id = d.m_kary_id AND start_date = d.start_date AND status = \'AKTIF\')')
            
            ->join('t_jadwal_kerja_n as n', 'n.id', '=', 'd.t_jadwal_kerja_n_id')
            
            ->select(
                'm_kary.*', 
                'n.keterangan as t_jadwal_kerja_n.keterangan', 
                'n.id as t_jadwal_kerja_n.id'
            );
    }

    public function scopehigherlevelOLD($model)
    {
        $m_kary = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->user()->id);
        })->first();

        $level = m_level_posisi::whereHas("m_level_posisi_d", function (
            $q
        ) use ($m_kary) {
            $q->where("m_posisi_id", $m_kary->m_posisi_id);
        })->first();

        // dd($level);

        $maxLevel = m_level_posisi::max("sequence");
        // dd($maxLevel);
        // dd($level->sequence);
        if ($level && $level?->sequence < $maxLevel) {
            return $model
                ->join(
                    "m_level_posisi_d as ld",
                    "ld.m_posisi_id",
                    "m_kary.m_posisi_id"
                )
                ->join("m_level_posisi as l", "l.id", "ld.m_level_posisi_id")
                ->where("l.sequence", ">", $level?->sequence);
        } else {
            return $model;
        }

        // return $model->whereHas('m_posisi.m_level_posisi_d.m_level_posisi', function($q) use ($level) {
        //     $q->where('sequence', '>', $level->sequence);
        // });
    }

    public function scopehigherlevel($query)
    {
        $m_kary = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->id());
        })->first();

        if(app()->request?->t_m_kary_id && is_numeric(app()->request->t_m_kary_id))
        {
           $m_kary = m_kary::find(app()->request->t_m_kary_id);
        }

        if (!$m_kary) {
            return $query->whereRaw("1 = 0");
        }

        $divisiIds = [];
        $currentDivisiId = $m_kary->m_divisi_id;

        while ($currentDivisiId) {
            $divisiIds[] = $currentDivisiId;

            $currentDivisiId = \DB::table("m_divisi")
                ->where("id", $currentDivisiId)
                ->value("parent_id");
        }

        $posisiId = (app()->request?->m_posisi_id && is_numeric(app()->request->m_posisi_id))
            ? app()->request->m_posisi_id
            : $m_kary->m_posisi_id;

        $level = null;
        if ($posisiId) {
            $level = m_level_posisi::whereHas("m_level_posisi_d", function ($q) use ($posisiId) {
                $q->where("m_posisi_id", $posisiId);
            })->first();
        }

        $maxLevel = m_level_posisi::max("sequence");

        return $query
            ->where("m_kary.id", "!=", $m_kary->id)
            ->when(!empty($divisiIds), function ($q) use ($divisiIds) {
                $q->where(function($subQ) use ($divisiIds) {
                    $subQ->whereIn("m_kary.m_divisi_id", $divisiIds)
                         ->orWhereNull("m_kary.m_divisi_id");
                });
            })
            ->when($level && $level->sequence < $maxLevel, function ($q) use ($level) {
                $q->where(function ($subQ) use ($level) {
                    $subQ->whereExists(function ($query) use ($level) {
                        $query->select(\DB::raw(1))
                              ->from('m_level_posisi_d as ld')
                              ->join('m_level_posisi as l', 'l.id', '=', 'ld.m_level_posisi_id')
                              ->whereColumn('ld.m_posisi_id', 'm_kary.m_posisi_id')
                              ->where('l.sequence', '>', $level->sequence);
                    })->orWhereExists(function ($query) use ($level) {
                        $query->select(\DB::raw(1))
                              ->from('m_kary_det_jabatan as mkdj')
                              ->join('m_level_posisi_d as ld', 'ld.m_posisi_id', '=', 'mkdj.m_posisi_id')
                              ->join('m_level_posisi as l', 'l.id', '=', 'ld.m_level_posisi_id')
                              ->where(function($joinKary) {
                                  $joinKary->whereColumn('mkdj.m_karyawan_id', 'm_kary.id')
                                           ->orWhereColumn('mkdj.m_kary_id', 'm_kary.id');
                              })
                              ->where('l.sequence', '>', $level->sequence);
                    });
                });
            });
    }

    public function scopeLowerLevelOLD($query)
    {
        $m_kary = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->user()->id);
        })->first();

        if (!$m_kary || !$m_kary->m_posisi_id) {
            return $query->whereRaw("1 = 0");
        }

        $level = m_level_posisi::whereHas("m_level_posisi_d", function (
            $q
        ) use ($m_kary) {
            $q->where("m_posisi_id", $m_kary->m_posisi_id);
        })->first();

        if (!$level) {
            return $query->whereRaw("1 = 0");
        }

        return $query
            ->join(
                "m_level_posisi_d as ld",
                "ld.m_posisi_id",
                "=",
                "m_kary.m_posisi_id"
            )
            ->join("m_level_posisi as l", "l.id", "=", "ld.m_level_posisi_id")
            ->where("l.sequence", "<", $level->sequence)
            ->select("m_kary.*");
    }

    public function scopeLowerLevel($query)
    {
        $m_kary = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->id());
        })->first();

        if (!$m_kary || !$m_kary->m_posisi_id) {
            return $query->whereRaw("1 = 0");
        }

        $divisiIds = [];
        $currentDivisiId = $m_kary->m_divisi_id;

        while ($currentDivisiId) {
            $divisiIds[] = $currentDivisiId;
            $currentDivisiId = \DB::table("m_divisi")
                ->where("id", $currentDivisiId)
                ->value("parent_id");
        }

        $level = m_level_posisi::whereHas("m_level_posisi_d", function (
            $q
        ) use ($m_kary) {
            $q->where("m_posisi_id", $m_kary->m_posisi_id);
        })->first();

        if (!$level) {
            return $query->whereRaw("1 = 0");
        }

        return $query
            ->select("m_kary.*")
            ->whereIn("m_kary.m_divisi_id", $divisiIds)
            ->join(
                "m_level_posisi_d as ld",
                "ld.m_posisi_id",
                "=",
                "m_kary.m_posisi_id"
            )
            ->join("m_level_posisi as l", "l.id", "=", "ld.m_level_posisi_id")
            ->where("l.sequence", "<", $level->sequence);
    }

    public function scopeBawahan($query)
    {
        $m_kary = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->id());
        })->first();

        if (!$m_kary) {
            return $query->whereRaw("1 = 0");
        }

        $subordinateIds = \DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id FROM m_kary WHERE atasan_id = ?
                UNION
                SELECT k.id FROM m_kary k
                INNER JOIN subordinates s ON k.atasan_id = s.id
            )
            SELECT id FROM subordinates
        ", [$m_kary->id]);

        $ids = array_column($subordinateIds, 'id');

        return $query->whereIn('m_kary.id', $ids);
    }

    public function public_getKary($req)
    {
        return $this->custom_getKary($req);
    }

    public function custom_getKary($req)
    {
        $branch_id = $req->branch_id ?? null;
        $subcode = $req->kode_sub ?? null;
        $subcomp_id = $req->subcomp_id ?? null;
        $os_id = $req->m_os_id ?? null;
        $kary_id = $req->kary_id ?? null;
        $search = $req->search ?? null;
        // dd($req->kode_sub);
        // dd(m_kary::get());

        $m_kary = m_kary::where(function ($query) use (
            $subcomp_id,
            $branch_id,
            $subcode
        ) {
            $query
                ->when(
                    $subcomp_id,
                    fn($q) => $q->where("m_subcomp_id", $subcomp_id)
                )
                ->when(
                    $branch_id,
                    fn($q) => $q->where("m_branch_id", $branch_id)
                )
                ->when($subcode, function ($q) use ($subcode) {
                    $q->whereHas(
                        "m_subcomp",
                        fn($q2) => $q2->where("code", $subcode)
                    );
                });
        })

            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where("kode", "ILIKE", "%{$search}%")
                        ->orWhere("nama_lengkap", "ILIKE", "%{$search}%");
                });
            })
            ->when($req->filter_kode, function ($q) use ($req) {
                $q->where("kode", "ILIKE", "%{$req->filter_kode}%");
            })
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
                $q->whereHas("m_branch", function ($q2) use ($req) {
                    $q2->where("name", "ILIKE", "%{$req->filter_branch}%");
                });
            })
            ->get()
            ->map(function ($kary) {
                return [
                    "id" => $kary->id,
                    "kode" => $kary->kode,
                    "nik" => $kary->nik,
                    "nama" => $kary->nama_depan,
                    "nama_lengkap" => $kary->nama_lengkap,
                    "tipe_karyawan" => $kary->status_kary?->value,
                    "status" => $kary->is_active,
                    "nama_atasan" => optional($kary->atasan)->nama_lengkap,
                    "subcomp" => optional(optional($kary)->m_subcomp)->name,
                    "branch" => optional(optional($kary)->m_branch)->name,
                    "divisi" => optional(optional($kary)->m_divisi)->name_old,
                ];
            });

        return $this->helper->customResponse("Data Karyawan", 200, $m_kary);
    }

    public function public_allKary()
    {
        return m_kary::all();
    }

    public function scopekary_os($model)
    {
        $os_id = request("os_id");

        $typeIds = m_general::where("group", "STATUS KARYAWAN OUTSOURCE")
            ->pluck("id")
            ->toArray();

        if ($os_id) {
            $model->where("m_company_outsourcing.id", $os_id);
        }

        $model->whereIn("status_kary.id", $typeIds);

        return $model;
    }

    public function custom_syncKary()
    {
        $kary_raw = $kary = m_kary::where(function($query) {
            $query->where('is_sync', false)
                ->orWhereNull('is_sync');
        });
        
        $updated_ids = $kary_raw->pluck('id')->toArray();
        $kary = $kary_raw->get()->map(function ($item) {
            return [
                "id" => $item->id ?? null,
                "kode" => $item->kode ?? null,
                "nik" => $item->nik ?? null,
                "no_registrasi" => $item->no_registrasi ?? null,
                "nip" => $item->nip ?? null,
                "nama_depan" => $item->nama_depan ?? null,
                "nama_belakang" => $item->nama_belakang ?? null,
                "nama_lengkap" => $item->nama_lengkap ?? null,
                "nama_panggilan" => $item->nama_panggilan ?? null,
                "atasan_id" => $item->atasan_id ?? null,
                "is_active" => $item->is_active ?? true,
                "m_comp_id" => $item->m_comp_id ?? null,
                "m_subcomp_id" => $item->m_subcomp_id ?? null,
                "m_branch_id" => $item->m_branch_id ?? null,
                "m_divisi_id" => $item->m_divisi_id ?? null,
                "m_posisi_id" => $item->m_posisi_id ?? null,
                "status_kary" => $item->status_kary?->value ?? null,
                "m_company_outsourcing_id" =>
                    $item->m_company_outsourcing_id ?? null,
                "created_at" => $item->created_at,
                "updated_at" => $item->updated_at,
            ];
        });

        // dd($kary);

        $comp = m_comp::all()->map(function ($item) {
            return [
                "id" => $item->id ?? null,
                "code" => $item->code ?? null,
                "name" => $item->name ?? null,
            ];
        });

        $subcomp = m_subcomp::all()->map(function ($item) {
            return [
                "id" => $item->id ?? null,
                "code" => $item->code ?? null,
                "name" => $item->name ?? null,
            ];
        });

        $branch = m_branch::all()->map(function ($item) {
            return [
                "id" => $item->id ?? null,
                "code" => $item->code ?? null,
                "name" => $item->name ?? null,
            ];
        });

        $company_outsourcing = m_company_outsourcing::all()->map(function (
            $item
        ) {
            return [
                "id" => $item->id ?? null,
                "code" => $item->code ?? null,
                "name" => $item->name ?? null,
            ];
        });

        $divisi = m_divisi::all()->map(function ($item) {
            $name = m_general::find($item->name)?->value ?? "";
            return [
                "id" => $item->id ?? null,
                "code" => $item->nomor ?? null,
                "m_branch_id" => $item->m_branch_id,
                "parent_id" => $item->parent_id,
                "is_parent" => $item->is_parent,
                "name" => $name,
            ];
        });

        $posisi = m_posisi::all()->map(function ($item) {
            return [
                "id" => $item->id ?? null,
                "code" => $item->nomor ?? null,
                "name" => $item->name ?? null,
            ];
        });

        // $default_users = default_users::all();
        $default_users_raw = default_users::whereNotNull("username")->where(
            "is_sync",
            false
        );
        $default_users = $default_users_raw->get()->map(function ($u) {
            return [
                "id" => $u->id,
                "m_company_id" => $u->m_comp_id,
                "m_employee_id" => $u->m_kary_id,
                "name" => $u->name,
                "email" => $u->email,
                "username" => $u->username,
                "email_verified_at" => $u->email_verified_at,
                // 'password' => $u->password,
                "remember_token" => $u->remember_token,
                "user_type" => $u->user_type,
                "note" => $u->note,
                "is_active" => $u->is_active,
                "created_at" => $u->created_at,
                "updated_at" => $u->updated_at,
            ];
        });

        //token ambil dari oauth_access_token di db erp, selama belum lewat expired at dan is_revoke false token akan berlaku
        $token = trim(env("ERP_TOKEN"));

        $response = Http::withHeaders([
            "Authorization" => "Bearer " . $token,
        ])->post(env("ERP_URL") . "/operation/hris_m_kary/syncKary", [
            "data" => $kary,
            "comp" => $comp,
            "subcomp" => $subcomp,
            "branch" => $branch,
            "company_outsourcing" => $company_outsourcing,
            "divisi" => $divisi,
            "posisi" => $posisi,
            "default_users" => $default_users,
        ]);

        if ($response->successful()) {
            default_users::whereIn(
                "id",
                $default_users_raw->pluck("id")
            )->update(["is_sync" => true]);

            m_kary::whereIn("id", $kary_raw->pluck("id"))->update([
                "is_sync" => true,
            ]);

            return response()->json([
                "status" => true,
                "message" => "Data karyawan berhasil dikirim ke ERP",
                "ids" => $updated_ids,
                "response" => $response->json(),
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Gagal mengirim data ke ERP",
                "error" => $response->body(),
            ]);
        }
    }

    public function scopeEfektifitas($model)
    {
        $req = app()->request;
        
        if (empty($req->t_realisasi_pelatihan_id) || $req->t_realisasi_pelatihan_id === 'null') {
            return $model->whereRaw('1 = 0');
        }

        return $model
            ->whereIn("m_kary.id", function ($query) use ($req) {
                $query->select("d.m_kary_id")
                    ->from("t_realisasi_pelatihan_d_kary as d")
                    ->where("d.t_realisasi_pelatihan_id", $req->t_realisasi_pelatihan_id);
            });
    }

    public function hitungRekap($kary_id, $start, $end): array
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $period = CarbonPeriod::create($start, $end);
        // dd($start, $end);

        $m_kary = m_kary::findOrFail($kary_id);

        $userId = default_users::where("m_kary_id", $m_kary->id)
            ->pluck("id")
            ->first();

        $presensi = presensi_absensi::with("t_jadwal_kerja_d_hari_n")
            ->where("default_user_id", $userId)
            ->whereBetween("tanggal", [$start, $end])
            ->get()
            ->keyBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $cuti = t_cuti::where("m_kary_id", $kary_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween("date_from", [$start, $end])
                    ->orWhereBetween("date_to", [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where("date_from", "<=", $start)->where(
                            "date_to",
                            ">=",
                            $end
                        );
                    });
            })
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get();

        $cutiDates = [];
        foreach ($cuti as $c) {
            foreach (CarbonPeriod::create($c->date_from, $c->date_to) as $tgl) {
                $cutiDates[$tgl->format("Y-m-d")] = $c->keterangan ?? "CUTI";
            }
        }

        // --- Hitung Rekap ---
        $hasil = [];
        $jumlah_hadir = 0;
        $total_menit_lembur_kerja = 0;
        $total_menit_lembur_libur = 0;
        $total_menit_terlambat = 0;
        $detail_menit_terlambat = [];
        $total_jam_terlambat = 0;
        $total_jam_tidak_hadir = 0;

        // ambil data lembur dalam periode
        $lembur = t_lembur::where("m_kary_id", $kary_id)
            ->whereBetween("tanggal", [$start, $end])
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get()
            ->groupBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $liburNasional = m_libur_nasional::whereBetween("tanggal", [
            $start,
            $end,
        ])->get();
        $liburDates = $liburNasional
            ->pluck("keterangan", "tanggal")
            ->mapWithKeys(
                fn($keterangan, $tgl) => [
                    Carbon::parse($tgl)->format("Y-m-d") =>
                        $keterangan ?? "LIBUR NASIONAL",
                ]
            );

        foreach ($period as $tanggal) {
            $key = $tanggal->format("Y-m-d");
            $data = $presensi[$key] ?? null;

            $status = $data?->status ?? "NOT ATTEND";
            if (isset($cutiDates[$key])) {
                $status = $cutiDates[$key];
            }

            // --- ambil tipe hari ---
            $tipe = null;
            if ($data && $data->t_jadwal_kerja_d_hari_n) {
                $tipe = $data->t_jadwal_kerja_d_hari_n->tipe_hari;
            } else {
                // fallback: cari dari jadwal karyawan
                $jadwal = t_jadwal_kerja_d_n::with(
                    "t_jadwal_kerja_n.t_jadwal_kerja_d_hari_n"
                )
                    ->where("m_kary_id", $m_kary->id)
                    ->whereDate("start_date", "<=", $tanggal)
                    ->orderByDesc("start_date")
                    ->first();

                if ($jadwal && $jadwal->t_jadwal_kerja_n) {
                    $hariName = $tanggal->translatedFormat("l");
                    $hari = $jadwal->t_jadwal_kerja_n->t_jadwal_kerja_d_hari_n->firstWhere(
                        "day",
                        $hariName
                    );
                    $tipe = $hari?->tipe_hari ?? "KERJA"; // default kerja
                } else {
                    $tipe = "KERJA"; // default jika jadwal pun tidak ada
                }
            }

            if (isset($liburDates[$key])) {
                $status = $liburDates[$key];
                $tipe = $liburDates[$key];
            }

            // --- hitung hadir ---
            if ($status === "ATTEND" && $tipe === "KERJA") {
                $jumlah_hadir++;
            }

            // --- hitung lembur ---
            if (isset($lembur[$key])) {
                foreach ($lembur[$key] as $l) {
                    $mulai = Carbon::parse($l->jam_mulai);
                    $selesai = Carbon::parse($l->jam_selesai);
                    $menit = $selesai->diffInMinutes($mulai);

                    if ($tipe === "KERJA") {
                        $total_menit_lembur_kerja += $menit;
                    } else {
                        $total_menit_lembur_libur += $menit;
                    }
                }
            }

            // --- hitung terlambat ---
            if (
                $data &&
                $data->checkin_time &&
                $data->t_jadwal_kerja_d_hari_n?->waktu_mulai
            ) {
                $checkin = Carbon::parse($data->checkin_time);
                $jadwalMulai = Carbon::parse(
                    $data->t_jadwal_kerja_d_hari_n->waktu_mulai
                );

                if ($checkin->greaterThan($jadwalMulai)) {
                    $menit_terlambat = $jadwalMulai->diffInMinutes($checkin);

                    $total_menit_terlambat += $jadwalMulai->diffInMinutes(
                        $checkin
                    );

                    $total_jam_terlambat += ceil($menit_terlambat / 60);
                    $detail_menit_terlambat[] = ceil($menit_terlambat / 60);
                }
            }

            // --- hitung tidak hadir ---
            if ($tipe === "KERJA" && $status === "NOT ATTEND") {
                if (
                    $data &&
                    $data->t_jadwal_kerja_d_hari_n?->waktu_mulai &&
                    $data->t_jadwal_kerja_d_hari_n?->waktu_selesai
                ) {
                    $mulai = Carbon::parse(
                        $data->t_jadwal_kerja_d_hari_n->waktu_mulai
                    );
                    $selesai = Carbon::parse(
                        $data->t_jadwal_kerja_d_hari_n->waktu_selesai
                    );
                    $total_jam_tidak_hadir += $selesai->diffInHours($mulai) - 1;
                } else {
                    $total_jam_tidak_hadir += 8; // fallback default 8 jam
                }
            }

            $hasil[] = [
                "tanggal" => $key,
                "tipe" => $tipe,
                "status" => $status,
            ];
        }

        return [
            "hari_kerja" => collect($hasil)
                ->where("tipe", "KERJA")
                ->count(),
            "jumlah_hadir" => $jumlah_hadir,
            "total_jam_tidak_hadir" => $total_jam_tidak_hadir,
            "jumlah_cuti" => count($cutiDates),
            "total_menit_lembur_kerja" => $total_menit_lembur_kerja,
            "total_menit_lembur_libur" => $total_menit_lembur_libur,
            "total_menit_terlambat" => $total_menit_terlambat,
            "detail_jam_terlambat" => $detail_menit_terlambat,
            "total_jam_terlambat" => $total_jam_terlambat,
        ];
    }

    public function custom_destroy($req)
    {
        \DB::beginTransaction();

        try {
            $kary_id = $req->id;

            $m_kary = m_kary::with([
                "m_kary_det_bhs",
                "m_kary_det_kartu",
                "m_kary_det_org",
                "m_kary_det_pel",
                "m_kary_det_pemb",
                "m_kary_det_pend",
                "m_kary_det_pk",
                "m_kary_det_pres",
                "m_kary_det_kel",
                "m_kary_det_jabatan",
                "m_kary_det_jobdesc",
                "m_kary_d_lokasi",
            ])->find($kary_id);

            if (!$m_kary) {
                return response()->json(
                    ["message" => "Karyawan tidak ditemukan"],
                    404
                );
            }

            $m_kary->m_kary_det_bhs()->delete();
            $m_kary->m_kary_det_kartu()->delete();
            $m_kary->m_kary_det_org()->delete();
            $m_kary->m_kary_det_pel()->delete();
            $m_kary->m_kary_det_pemb()->delete();
            $m_kary->m_kary_det_pend()->delete();
            $m_kary->m_kary_det_pk()->delete();
            $m_kary->m_kary_det_pres()->delete();
            $m_kary->m_kary_det_kel()->delete();
            $m_kary->m_kary_det_jabatan()->delete();
            $m_kary->m_kary_det_jobdesc()->delete();
            $m_kary->m_kary_d_lokasi()->delete();

            $m_kary->delete();

            \DB::commit();

            return response()->json(
                [
                    "message" =>
                        "Data karyawan dan seluruh relasinya berhasil dihapus.",
                ],
                200
            );
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(
                [
                    "message" => "Gagal menghapus data karyawan",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function scopedivisi($model)
    {
        return $model
            ->leftjoin("m_general", "m_divisi.name", "m_general.id")
            ->addSelect("m_general.value as nama_divisi");
    }

    public function scopedivisifilter($model)
    {
        return $model
            ->leftjoin("m_general", "m_divisi.name", "m_general.id")
            ->addSelect("m_general.value as m_divisi.value");
    }

    public function scopedivisirespo($query)
    {
        // $m_subcomp_id = request('m_subcomp_id') ?? null;
        // $m_branch_id = request('m_branch_id') ?? null;

        // if ($m_subcomp_id === 'null') $m_subcomp_id = null;
        // if ($m_branch_id === 'null')  $m_branch_id  = null;

        // if(!$m_branch_id && !$m_subcomp_id)
        // {

        // }

        $karyawan = m_kary::whereHas("default_users", function ($q) {
            $q->where("id", auth()->id());
        })
            ->select("m_divisi_id")
            ->first();

        if (!$karyawan || !$karyawan->m_divisi_id) {
            return $query->whereRaw("1 = 0");
        }

        return $query->where("m_kary.m_divisi_id", $karyawan->m_divisi_id);
    }

    public function latestJadwal()
    {
        return $this->hasOne(t_jadwal_kerja_d_n::class, 'm_kary_id', 'id')
            ->where('status', 'AKTIF')
            ->where('start_date', '<=', Carbon::now())
            ->latest('start_date')
            ->latest('id');
    }

    public function public_exportKary()
    {
        try {
            $req = app()->request;
            $status_os = m_general::where(
                "group",
                "STATUS KARYAWAN OUTSOURCE"
            )->pluck("id");        
            $m_branch_id = $req->m_branch_id ?? null;
            $m_divisi_id = $req->m_divisi_id ?? null;
            // $subcode = $req->kode_sub ?? null;
            $m_subcomp_id = $req->m_subcomp_id ?? null;
            $m_comp_id = $req->m_comp_id ?? null;
            $m_kary_id = $req->m_kary_id ?? null;
            $fileName =
                "data_karyawan_" . Carbon::now()->format("Ymd_His") . ".xlsx";
            $data = m_kary::with([
                "m_divisi",
                "m_kary_det_jabatan",
                "m_kary_det_pend",
                "m_posisi",
                "m_dept",
            ])->whereNotIn("m_kary.status_kary_id", $status_os)
                ->where(function ($query) use (
                    $m_comp_id,
                    $m_subcomp_id,
                    $m_branch_id,
                    $m_divisi_id,
                    $m_kary_id
                ) {
                    $query
                        ->when(
                            $m_comp_id,
                            fn($q) => $q->where("m_comp_id", $m_comp_id)
                        )
                        ->when($m_subcomp_id, function($q) use ($m_subcomp_id) {
                            if (is_string($m_subcomp_id) && str_starts_with($m_subcomp_id, '[') && str_ends_with($m_subcomp_id, ']')) {
                                $m_subcomp_id = json_decode($m_subcomp_id, true);
                            }
                            if (is_array($m_subcomp_id)) {
                                $q->whereIn("m_subcomp_id", $m_subcomp_id);
                            } else if (is_string($m_subcomp_id) && str_contains($m_subcomp_id, ',')) {
                                $q->whereIn("m_subcomp_id", explode(',', $m_subcomp_id));
                            } else {
                                $q->where("m_subcomp_id", $m_subcomp_id);
                            }
                        })
                        ->when($m_branch_id, function($q) use ($m_branch_id) {
                            if (is_string($m_branch_id) && str_starts_with($m_branch_id, '[') && str_ends_with($m_branch_id, ']')) {
                                $m_branch_id = json_decode($m_branch_id, true);
                            }
                            if (is_array($m_branch_id)) {
                                $q->whereIn("m_branch_id", $m_branch_id);
                            } else if (is_string($m_branch_id) && str_contains($m_branch_id, ',')) {
                                $q->whereIn("m_branch_id", explode(',', $m_branch_id));
                            } else {
                                $q->where("m_branch_id", $m_branch_id);
                            }
                        })
                        ->when(
                            $m_divisi_id,
                            fn($q) => $q->where("m_divisi_id", $m_divisi_id)
                        )
                        ->when(
                            $m_kary_id,
                            fn($q) => $q->where("id", $m_kary_id)
                        );
                })
                ->where("is_active", true)
                ->get()
                ->map(function ($kary) {
                    $format = function ($date) {
                        if (!$date || $date === "-" || $date === "0000-00-00") {
                            return "";
                        }

                        try {
                            return \Carbon\Carbon::parse($date)->format(
                                "d-M-Y"
                            );
                        } catch (\Exception $e) {
                            return "";
                        }
                    };

                    $level = m_level_posisi::whereHas(
                        "m_level_posisi_d",
                        function ($q) use ($kary) {
                            $q->where("m_posisi_id", $kary->m_posisi_id);
                        }
                    )->first()?->level_name;

                    return [
                        "NIP" => $kary->kode ?? "",
                        "NAMA PEGAWAI" => $kary->nama_lengkap ?? "",
                        "NO REGISTRASI" => $kary->no_registrasi ?? "",
                        "JABATAN" => $kary->m_posisi?->name ?? "",
                        "LEVEL" => $level ?? "",
                        "DEPARTEMEN" => $kary->m_dept->nama ?? "",
                        "COST CENTER" =>
                            m_costcentre::find($kary->costcontre_id)?->name ??
                            "",
                        "STATUS KARYAWAN" =>
                            m_general::find($kary->status_kary_id)?->value ??
                            "",
                        "AWAL MASUK" =>
                            $format(
                                $kary->m_kary_det_jabatan
                                    ->sortBy("start_time")
                                    ->first()?->start_time
                            ) ?? "",
                        "AWAL PERIODE" =>
                            $format(
                                $kary->m_kary_det_jabatan
                                    ->sortByDesc("start_time")
                                    ->first()?->start_time
                            ) ?? "",
                        "AKHIR /SD" =>
                            $format(
                                $kary->m_kary_det_jabatan
                                    ->sortByDesc("start_time")
                                    ->first()?->end_time
                            ) ?? "",
                        "TAHUN PENGANGKATAN" => "",
                        "AWAL DIANGKAT" => "",
                        "STATUS_TANGGUNGAN" =>
                            m_general::find($kary->tanggungan_id)?->value ?? "",
                        "PENDIDIKAN" =>
                            m_general::find(
                                $kary->m_kary_det_pend->first()?->tingkat_id
                            )?->value ?? "",
                        "JENIS KELAMIN" => match (
                        m_general::find($kary->jk_id)?->value
                        ) {
                            "Laki-Laki" => "L",
                            "Perempuan" => "P",
                            default => "",
                        },
                        "TGL_LAHIR" => $format($kary->tgl_lahir),
                    ];
                });

            // dd($data->toArray());

            // Buat export dinamis langsung tanpa class
            $export = new class ($data) implements FromCollection, WithHeadings
            {
                protected $data;
                public function __construct($data)
                {
                    $this->data = $data;
                }
                public function collection()
                {
                    return $this->data;
                }
                public function headings(): array
                {
                    return [
                        "NIP",
                        "NAMA PEGAWAI",
                        "NO REGISTRASI",
                        "JABATAN",
                        "LEVEL",
                        "DEPARTEMEN",
                        "COST CENTER",
                        "STATUS KARYAWAN",
                        "AWAL MASUK",
                        "AWAL PERIODE",
                        "AKHIR /SD",
                        "TAHUN PENGANGKATAN",
                        "AWAL DIANGKAT",
                        "STATUS TANGGUNGAN",
                        "PENDIDIKAN",
                        "JENIS KELAMIN",
                        "TGL LAHIR",
                    ];
                }
            };

            return Excel::download($export, $fileName, ExcelType::XLSX);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "error" =>
                        "Terjadi kesalahan saat export: " . $e->getMessage(),
                ],
                500
            );
        }
    }

    public function custom_canOutScope()
    {
        $data = $this->findOrFail(auth()->user()->m_kary_id);
        $now = Carbon::now();

        if($data->can_outscope === true){
            return response()->json([
                "can_outscope" => true
            ]);
        }else{
            $perdin = t_rencana_perdin::whereHas('t_perdin', function($q) use ($now){
                $q->whereDate('date_from', '<=', $now)
            ->whereDate('date_to', '>=', $now);
            })
            ->where('m_kary_id', $data->id)
            ->where('status', 'APPROVED')
            ->exists();

            if($perdin){
                return response()->json([
                    "can_outscope" => $perdin ?? false
                ]); 
            }

            $cuti = t_cuti::where('m_kary_id', $data->id)
                ->where('status', 'APPROVED')
                ->whereDate('date_from', '<=', $now)
                ->whereDate('date_to', '>=', $now)
                ->exists();

            return response()->json([
                "can_outscope" => $perdin || $ijin
            ]);
        }
    }

    // public function custom_getPlafond()
    // {
    //     $id = app()->request->m_kary_id;
    //     //$kary = m_kary::findOrFail($id);
    //     $gaji = m_standart_gaji::where('m_kary_id', $id)->first();
    //     dd($gaji->gaji_pokok);
    // }

    // public function scopegajipokok($model)
    // {
    //     return $model->join('m_standart_gaji', 'm_standart_gaji.m_kary_id', 'm_kary.id');
    // }

    public function custom_getplafond()
    {
        $requestId = app()->request?->id;

        //dd($requestId);

        if ($requestId) {
            $kary = m_kary::findOrFail($requestId);
            $gaji = $kary->m_standart_gaji_latest;
            $plafond = $this->calculatePlafond($gaji);

            return response()->json([
                "m_kary_id" => $kary->id,
                "plafond" => $plafond['plafond'],
                "sisa_plafond" => $plafond['sisa_plafond']
            ]);
        } 
    }

    private function calculatePlafond($m_standart_gaji)
    {
        $gaji = $m_standart_gaji;
        // dd($gaji);


        if (!$gaji) {
            return [
                "plafond" => 0,
                "sisa_plafond" => 0
            ];
        }

        $used = t_klaim_askes::where('m_kary_id', $gaji->m_kary_id)->whereYear('periode_akhir', Carbon::now()->year)->where('status', 'POSTED')->sum('total_nominal') ?? 0;
        // dd($used);

        // dd($gaji);
        $plafond = (float)($gaji?->gaji_pokok ?? 0) + 
            (float)($gaji?->tunjangan_produktifitas ?? 0) + 
            (float)($gaji?->tunjangan_posisi ?? 0) + 
            (float)($gaji?->tunjangan_transport ?? 0) + 
            (float)($gaji?->tunjangan_fungsional ?? 0);
        //dd($plafond);


        $sisa = $plafond - (float)$used;
        // dd($sisa);
        $data = [
            "plafond" => $plafond,
            "sisa_plafond" => $sisa
        ];

        return $data;
    }

    public function custom_getAskes()
    {
        $m_kary_id = app()->request->m_kary_id;

        $karyawan = DB::table('m_kary')
            ->select(
                'id as klaim_id',
                'nama_lengkap as klaim_nama',
                DB::raw("'m_kary' as klaim_type"),
                'id as m_kary_id'
            )
            ->where('is_active', true)
            ->where('id', $m_kary_id);

        $data = DB::table('m_kary_det_kel as dk')
            ->join('m_kary as mk', 'dk.m_kary_id', '=', 'mk.id')
            ->select(
                'dk.id as klaim_id',
                'dk.nama as klaim_nama',
                DB::raw("'m_kary_det_kel' as klaim_type"),
                'dk.m_kary_id'
            )
            ->where('mk.is_active', true)
            ->where('dk.m_kary_id', $m_kary_id)
            ->where('dk.include_askes', true)
            ->union($karyawan)
            ->get();

        return response()->json($data);
    }

    public function custom_getRanap()
    {
        $m_kary_id = app()->request->m_kary_id;

        $karyawan = DB::table('m_kary')
            ->select(
                'id as klaim_id',
                'nama_lengkap as klaim_nama',
                DB::raw("'m_kary' as klaim_type"),
                'id as m_kary_id'
            )
            ->where('is_active', true)
            ->where('id', $m_kary_id);

        $data = DB::table('m_kary_det_kel as dk')
            ->join('m_kary as mk', 'dk.m_kary_id', '=', 'mk.id')
            ->select(
                'dk.id as klaim_id',
                'dk.nama as klaim_nama',
                DB::raw("'m_kary_det_kel' as klaim_type"),
                'dk.m_kary_id'
            )
            ->where('mk.is_active', true)
            ->where('dk.m_kary_id', $m_kary_id)
            ->where('dk.include_askes', true)
            ->union($karyawan)
            ->get();

        return response()->json($data);
    }

    public function custom_getplafondranap()
    {
        $requestId = app()->request->id;
        if ($requestId) {
            $kary = m_kary::findOrFail($requestId);
            $tahun = \Carbon\Carbon::now()->year;
            $ranap = \App\Models\BasicModels\t_plafond_ranap::where('m_kary_id', $kary->id)->where('tahun', $tahun)->first();
            $plafond = (float)($ranap?->plafond ?? 0);
            $used = DB::table('t_klaim_ranap')->where('m_kary_id', $kary->id)->whereYear('periode_akhir', $tahun)->where('status', 'POSTED')->sum('total_nominal') ?? 0;
            $sisa = $plafond - $used;

            return response()->json([
                "m_kary_id" => $kary->id,
                "plafond" => $plafond,
                "sisa_plafond" => $sisa
            ]);
        } 
    }
}