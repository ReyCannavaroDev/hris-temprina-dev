<?php

namespace App\Models\CustomModels;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;

class t_perhitungan_gaji extends \App\Models\BasicModels\t_perhitungan_gaji
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

    protected $factorAdded = [];

    private function factorSalary($standart_gaji, $kary = null, $periode_awal, $periode_akhir)
    {
        $firstDayOfMonth = Carbon::parse($periode_awal);
        //$date = new \DateTime($firstDayOfMonth);

        // Set the date to the last day of the month
        // $date->modify("last day of this month");

        // Get the last day as a string in 'Y-m-d' format
        $lastDayOfMonth = Carbon::parse($periode_akhir);

        $thp = 0;
        $arrayThp = [
            "gaji_pokok",
            "tunjangan_produktifitas",
            "tunjangan_transport",
        ];

        $defaultColumns = [
            [
                "name" => "gaji_pokok",
                "type" => "gaji_pokok_periode",
            ],
            [
                "name" => "tunjangan_produktifitas",
                "type" => "tunjangan_produktifitas_periode",
            ],
            [
                "name" => "tunjangan_posisi",
                "type" => "tunjangan_posisi_periode",
            ],
            [
                "name" => "tunjangan_kemahalan",
                // "table" => "m_tunj_kemahalan",
                "type" => "tunjangan_kemahalan_periode",
            ],
            [
                "name" => "tunjangan_transport",
                "type" => "tunjangan_transport_periode",
            ],
            [
                "name" => "tunjangan_fungsional",
                "type" => "tunjangan_fungsional_periode",
            ],
        ];

        foreach ($defaultColumns as $idx => $key) {
            $name = $key['name'];
            if ($name === 'tunjangan_posisi') {
                $name = 'tunjangan_jabatan';
            }
            $defaultColumns[$idx]['label'] = $this->helper->snakeCaseToCapitalize($name);

            $defaultColumns[$idx]["factor"] = "+"; // pasti tambah karena default kolom tunjangan

            // dynamic
            if (@!$key["table"]) {
                $defaultColumns[$idx]["value"] =
                    (float) $standart_gaji[$key["name"]] ?? 0;
                $defaultColumns[$idx]["type"] =
                    $standart_gaji[$key["type"]] ?? "BULANAN";
            } else {
                if ($key["table"] == "m_tunj_kemahalan") {
                    $defaultColumns[$idx]["label"] = "Tunjangan Kemahalan";
                    $defaultColumns[$idx]["value"] =
                        (float) \DB::table($key["table"])
                            ->where("id", $standart_gaji[$key["name"]])
                            ->pluck("besaran")
                            ->first() ?? 0;
                    $defaultColumns[$idx]["type"] =
                        $standart_gaji[$key["type"]] ?? "BULANAN";
                }
            }
            $defaultColumns[$idx]["can_adjust"] = 1;
            if ($defaultColumns[$idx]["value"] == 0) {
                unset($defaultColumns[$idx]);
            }
        }

        foreach ($defaultColumns as $column) {
            if (in_array($column["name"], $arrayThp)) {
                $thp += $column["value"];
            }
        }

        // faktor lain dari table m_standart_gaji_det
        $standart_gaji_det = m_standart_gaji_det::where(
            "m_standart_gaji_id",
            $standart_gaji->id ?? 0
        )->get();
        
        //presentase
        $getTipeKomponen = m_general::where("group", "TIPE KOMPONEN")
            ->where("code", "TPK01")
            ->first();

        //rekap absensi
        $rekap = $this->hitungRekap(@$kary->id, $firstDayOfMonth, $lastDayOfMonth);
        // dd($rekap);


        foreach ($standart_gaji_det as $d) {
            $isPercentage = $d->komponen_tipe_id == ($getTipeKomponen->id ?? 0);
            $isBpjs = stripos($d->komponen->value ?? '', 'bpjs') !== false;

            if ($isPercentage) {
                if ($isBpjs) {
                    $base_calc = $thp + ($standart_gaji['tunjangan_posisi'] ?? 0) + ($standart_gaji['tunjangan_fungsional'] ?? 0);
                } else {
                    $base_calc = $thp;
                }
                $calculatedValue = $d->nilai * ($base_calc / 100);
            } else {
                $calculatedValue = $d->nilai;
            }

            if (strtoupper($d->periode ?? '') == 'HARIAN') {
                $finalValue = (int)($calculatedValue * ($rekap['jumlah_hadir'] ?? 0));
            } else {
                $finalValue = (int)$calculatedValue;
            }

            $defaultColumns[] = [
                "label" => $d->komponen->value,
                "factor" => $d->faktor,
                "value" => $finalValue,
                "type" => $d->periode,
                "can_adjust" => 1,
            ];
        }
        // dd($standart_gaji);
        // foreach ($standart_gaji_det as $d) {
        //     if ($d->komponen_tipe_id == ($getTipeKomponen->id ?? 0)) {
        //         $isBpjs = stripos($d->komponen->value ?? '', 'bpjs') !== false;
        //         if ($isBpjs) {
        //             $value_bpjs = $thp +  $standart_gaji['tunjangan_posisi'] + $standart_gaji['tunjangan_fungsional'];
        //         }
        //         $defaultColumns[] = [
        //             "label" => $d->komponen->value,
        //             "factor" => $d->faktor,
        //             "value" => $isBpjs ? $d->nilai * ($value_bpjs / 100) : $d->nilai * ($thp / 100),
        //             "type" => $d->periode,
        //             "can_adjust" => 1,
        //         ];
        //     } else {
        //         $defaultColumns[] = [
        //             "label" => $d->komponen->value,
        //             "factor" => $d->faktor,
        //             "value" => $d->nilai,
        //             "type" => $d->periode,
        //             "can_adjust" => 1,
        //         ];
        //     }
        // }

        if (!$kary) {
            return $defaultColumns;
        }

        // tunjangan masa kerja
        // $general_masa_kerja = m_general::where("group", "TUNJANGAN MASA KERJA")
        //     ->where("key", "01")
        //     ->pluck("value")
        //     ->first();
        // if ($general_masa_kerja && $kary->tgl_masuk) {
        //     $general_masa_kerja = (float) $general_masa_kerja;
        //     $date_from = \DateTime::createFromFormat("Y-m-d", $kary->tgl_masuk);
        //     $date_to = \DateTime::createFromFormat("Y-m-d", date("Y-m-d"));
        //     $interval = @$date_from->diff($date_to) ?? 0;
        //     $jumlah_tahun = floor($interval->days / 365);

        //     $total_tunjangan = $general_masa_kerja * pow(2, $jumlah_tahun);
        //     if ($total_tunjangan > 0) {
        //         $defaultColumns[] = [
        //             "label" => "Tunjangan Masa Kerja ($jumlah_tahun)",
        //             "factor" => "+",
        //             "value" => $total_tunjangan,
        //             "type" => "Bulanan",
        //             "can_adjust" => 1,
        //         ];
        //     }
        // }

        // faktor lain :Potongan
         $t_potongan = t_potongan::where(function ($query) use ($kary) {
            $query->where("m_kary_id", @$kary->id ?? 0)
                ->orWhere("is_all_kary", true);
                })
                ->whereRaw("date_from >= ? and date_to <= ?", [
                    $firstDayOfMonth,
                    $lastDayOfMonth,
                ])
                ->where('status', 'POSTED')
                ->get();
        if (count($t_potongan)) {
            foreach ($t_potongan as $d) {
                $nilai_netto = ((float) $d->nilai * (float) $d->percentage) / 100;
                $defaultColumns[] = [
                    "label" => "Potongan - $d->nomor",
                    "factor" => "-",
                    "value" => $nilai_netto,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                    "t_potongan_id" => $d->id,
                ];
            }
        }

          // faktor lain :Bonus
        $t_bonus = t_bonus::where("m_kary_id", @$kary->id ?? 0)
            //->orWhere("is_all_kary", true)
            ->where(function($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                $query->whereRaw("date_from <= ?", [$lastDayOfMonth])
                ->whereRaw("date_to >= ?", [$firstDayOfMonth]);
            })
            ->where('status', 'POSTED')
            ->get();
        
        // dd($t_bonus);

        if (count($t_bonus)) {
            foreach ($t_bonus as $d) {
                $nilai_netto = (float) $d->nilai;
                $defaultColumns[] = [
                    "label" => "Bonus - $d->keterangan",
                    "factor" => "+",
                    "value" => $nilai_netto,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                    "t_bonus_id" => $d->id,
                ];
            }
        }

        // check kehadiran karyawan
        $attendance = \DB::select("select public.employee_attendance(?,?)", [
            $firstDayOfMonth,
            @$kary->id ?? 0,
        ]);

        // $start = Carbon::parse($periode)->startOfMonth();
        // $end = Carbon::parse($periode)->endOfMonth();
        // dd($periode_awal, $periode_akhir);

        // dd($rekap);
        // dd($this->hitungRekap(27, $start, $end));

        if (count($rekap)) {
            // $att = $attendance[0]->employee_attendance;
            // $att = json_decode($att);

            // $rekap = $this->hitungRekap(@$kary->id, $start, $end);
            // $hari_kerja = $rekap['hari_kerja'];
            $not_attend = $rekap["hari_kerja"] - ($rekap["jumlah_hadir"] + $rekap["jumlah_cuti"]);
            $not_attend_hours = $rekap["total_jam_tidak_hadir"];
            $total_lembur_hari_biasa = ceil(
                $rekap["total_menit_lembur_kerja"] / 60
            );
            $total_lembur_hari_libur = ceil(
                $rekap["total_menit_lembur_libur"] / 60
            );
            // $total_terlambat = ceil($rekap["total_menit_terlambat"] / 60);
            $total_terlambat = $rekap["total_jam_terlambat"];


            // $jml_hari_sebulan = $att->work_days_in_month;
            // $tidak_masuk_kerja = $att->work_not_present;
            // $cuti_reguler = $att->cuti_reguler;
            // $sisa_cuti_reguler = $att->sisa_cuti_reguler;
            // $sisa_cuti_masa_kerja = $att->sisa_cuti_masa_kerja;
            // $potongan_cuti = $att->potongan_cuti;
            // $sisa_cuti = $sisa_cuti_reguler + $sisa_cuti_masa_kerja;

            // gaji perhari
            // $gaji_per_hari = 0;
            // $makan_per_hari = 0;
            $gaji_pokok = @$standart_gaji["gaji_pokok"] ?? 0;
            // if ($gaji_pokok) {
            //     $gaji_per_hari = $gaji_pokok / $jml_hari_sebulan;
            // }
            // $uang_makan = @$standart_gaji["uang_makan"];
            // if ($uang_makan) {
            //     $makan_per_hari = $standart_gaji / $jml_hari_sebulan;
            // }

            // potongan tidak hadir dan jatah semua cuti sudah habis
            // if(($sisa_cuti-$tidak_masuk_kerja) < 0){
            
            if (@$standart_gaji['check_absence'] && $not_attend > 0) {
                // $sisa_cuti -= $not_attend;
                // $value = $gaji_per_hari*$tidak_masuk_kerja;
                // $value = $gaji_per_hari*$not_attend;
                $value = (@$standart_gaji["gaji_pokok"] + @$standart_gaji["tunjangan_produktifitas"] + @$standart_gaji["tunjangan_transport"]) / 173 * 7 * $not_attend;

                // $value =
                //     (@$standart_gaji["tunjangan_transport"] / 173) *
                //     $not_attend_hours;

                $defaultColumns[] = [
                    "label" => "Potongan Tidak Masuk Kerja ($not_attend Hari)",
                    "factor" => "-",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }

            if (@$standart_gaji['check_late'] && $total_terlambat > 0) {
                $value =
                    ( (@$standart_gaji["tunjangan_transport"] + @$standart_gaji["tunjangan_produktifitas"]) / 173) *
                    $total_terlambat;
                $defaultColumns[] = [
                    "label" => "Potongan Terlambat ($total_terlambat Jam)",
                    "factor" => "-",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }

            if ($total_lembur_hari_biasa > 0) {
                $upahPerJam = (@$standart_gaji["gaji_pokok"] + @$standart_gaji["tunjangan_produktifitas"] + @$standart_gaji["tunjangan_transport"]) / 173;

                $jamPertama = min(1, $total_lembur_hari_biasa);
                $jamBerikutnya = max(0, $total_lembur_hari_biasa - 1);

                $value =
                    $jamPertama * 1.5 * $upahPerJam +
                    $jamBerikutnya * 2 * $upahPerJam;

                $defaultColumns[] = [
                    "label" => "Upah Lembur Hari Kerja ($total_lembur_hari_biasa Jam)",
                    "factor" => "+",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }

            if ($total_lembur_hari_libur > 0) {
                $upahPerJam =
                    (@$standart_gaji["gaji_pokok"] +
                        @$standart_gaji["tunjangan_jabatan"]) /
                    173;

                $jamPertama = min(7, $total_lembur_hari_libur);
                $jamKedelapan = $total_lembur_hari_libur > 7 ? 1 : 0;
                $jamSisanya = max(0, $total_lembur_hari_libur - 8);

                $value =
                    $jamPertama * 2 * $upahPerJam +
                    $jamKedelapan * 3 * $upahPerJam +
                    $jamSisanya * 4 * $upahPerJam;

                $defaultColumns[] = [
                    "label" => "Biaya Lembur Hari Libur ($total_lembur_hari_libur Jam)",
                    "factor" => "+",
                    "value" => $value,
                    "type" => "Bulanan",
                    "can_adjust" => 1,
                ];
            }

            // // ketika jatah cuti reguler masih ada -> potong uang makan
            // if($sisa_cuti > 0 && $potongan_cuti > 0){
            //     $value = $makan_per_hari*$potongan_cuti;
            //     $defaultColumns[] = [
            //         'label'    => "Potongan Uang Makan Cuti ($potongan_cuti)",
            //         'factor'   => '-',
            //         'value'    => $value,
            //         'type'     => 'Bulanan',
            //         'can_adjust' => 1
            //     ];
            // }

            // // ketika jatah cuti reguler tidak ada -> potong gaji
            // if($sisa_cuti <= 0 && $potongan_cuti > 0){
            //     $value = $gaji_per_hari*$potongan_cuti;
            //     $defaultColumns[] = [
            //         'label'    => "Potongan Cuti ($potongan_cuti)",
            //         'factor'   => '-',
            //         'value'    => $value,
            //         'type'     => 'Bulanan',
            //         'can_adjust' => 1
            //     ];
            // }
        }

        // faktor lain :Cuti
        // $t_cuti = t_cuti::where('m_kary_id', @$kary->id ?? 0)->whereRaw("status = 'APPROVED' and date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->get();
        // if(count($t_cuti)) {
        //     $sisa_cuti = m_kary::where('id', @$kary->id ?? 0)->pluck('cuti_sisa_reguler')->first() ?? 0;
        //     $count = t_cuti::where('m_kary_id', @$kary->id ?? 0)->whereRaw("attachment is not null and status = 'APPROVED' and date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->count();
        //     foreach($t_cuti as $d){
        //         $date_from = \DateTime::createFromFormat('Y-m-d', $d->date_from);
        //         $date_to = \DateTime::createFromFormat('Y-m-d', $d->date_to);
        //         $interval = @$date_from->diff($date_to) ?? 0;
        //         $jumlah_hari = $interval->days;
        //         if($sisa_cuti > 0){
        //             $jumlah_hari = $jumlah_hari - $sisa_cuti;
        //         }

        //         $gaji_per_hari = 0;
        //         $makan_per_hari = 0;
        //         $gaji_pokok = @$standart_gaji['gaji_pokok'] ?? 0;
        //         if($gaji_pokok){
        //             $gaji_per_hari = $gaji_pokok / (int)date('t');
        //         }
        //         $makan_per_hari = @$standart_gaji['uang_makan'] / (int)date('t');
        //         $potongan_cuti = $gaji_per_hari*$jumlah_hari;
        //         $potongan_makan = $makan_per_hari * $jumlah_hari;

        //         if($count > 7){
        //             $defaultColumns[] = [
        //                 'label'    => "Potongan Cuti ($jumlah_hari)",
        //                 'factor'   => '-',
        //                 'value'    => $potongan_cuti,
        //                 'type'     => 'Bulanan',
        //                 'can_adjust' => 1,
        //                 't_cuti_id' => $d->id
        //             ];

        //         }else{
        //             $defaultColumns[] = [
        //                 'label'    => "Potongan Cuti (Uang Makan) ($jumlah_hari)",
        //                 'factor'   => '-',
        //                 'value'    => $potongan_cuti,
        //                 'type'     => 'Bulanan',
        //                 'can_adjust' => 1,
        //                 't_cuti_id' => $d->id
        //             ];
        //         }

        //     }
        // }

        return $defaultColumns;
    }

    // private function factorSalaryHarian($standart_gaji, $kary = null, $periode_awal, $periode_akhir)
    // {
    //     $firstDayOfMonth = Carbon::parse($periode_awal);
        
    //     $lastDayOfMonth = Carbon::parse($periode_akhir);

    //     $defaultColumns = [];
    
    //     if (!$kary) {
    //         return $defaultColumns;
    //     }

    //     //rekap absensi
    //     $rekapData = $this->hitungRekapHarian(@$kary->id, $firstDayOfMonth, $lastDayOfMonth);
    //     // dd($rekapData);

    //     if (isset($rekapData['daily_details'])) {
    //         $gaji_pokok_harian = @$standart_gaji["gaji_pokok"] ?? 0;
    //         $total_jam_kerja = @$standart_gaji["total_jam_kerja"] ?? 7;

    //         foreach ($rekapData['daily_details'] as $day) {
    //             if ($day['status'] === 'ATTEND') {
                    
    //                 // $jam_diakui = min($day['jam_kerja'], 7);
    //                 $jam_diakui = $day['jam_kerja'];
                            
    //                 $value = $jam_diakui * ($gaji_pokok_harian / $total_jam_kerja);                    
    //                 // Jika gaji flat per kehadiran:
    //                 //$value = $gaji_pokok_harian;

    //                 if($day['jam_kerja'] > 0)
    //                 {
    //                     $defaultColumns[] = [
    //                         "label"      => "Gaji Harian - " . $day['tanggal'] . " (" . $day['hari'] . ") - " . $jam_diakui . " Jam",
    //                         "hours"      => $jam_diakui,
    //                         "daily_wage" => $gaji_pokok_harian,
    //                         "hourly_wage" => ($gaji_pokok_harian / $total_jam_kerja),
    //                         "factor"     => "+",
    //                         "value"      => $value,
    //                         "type"       => "Harian",
    //                         "can_adjust" => 1,
    //                     ];
    //                 }

    //                 if ($day['jam_lembur'] > 0) {
    //                     $multiplier = $day['is_libur_nasional'] ? 2 : 1;
    //                     $upah_per_jam = $gaji_pokok_harian / $total_jam_kerja;
    //                     $valueLembur = $upah_per_jam * $day['jam_lembur'] * $multiplier;

    //                     $labelLibur = $day['is_libur_nasional'] ? " [Libur Nasional x2]" : "";

    //                     $defaultColumns[] = [
    //                         "label"      => "Lembur" . $labelLibur . " - " . $day['tanggal'] . " (" . $day['jam_lembur'] . " Jam)",
    //                         "hours"      => $day['jam_lembur'],
    //                         "daily_wage" => $gaji_pokok_harian,
    //                         "hourly_wage" => ($gaji_pokok_harian / $total_jam_kerja),
    //                         "factor"     => "+",
    //                         "value"      => $valueLembur,
    //                         "type"       => "Lembur",
    //                         "can_adjust" => 1,
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     // faktor lain :Potongan
    //     $t_potongan = t_potongan::where(function ($query) use ($kary) {
    //     $query->where("m_kary_id", @$kary->id ?? 0)
    //           ->orWhere("is_all_kary", true);
    //         })
    //         ->whereRaw("date_from >= ? and date_to <= ?", [
    //             $firstDayOfMonth,
    //             $lastDayOfMonth,
    //         ])
    //         ->where('status', 'POSTED')
    //         ->get();
    //         // dd($t_potongan);

    //     if (count($t_potongan)) {
    //         foreach ($t_potongan as $d) {
    //             $nilai_netto = ((float) $d->nilai * (float) $d->percentage) / 100;
    //             $defaultColumns[] = [
    //                 "label" => "Potongan - $d->nomor",
    //                 "factor" => "-",
    //                 "value" => $nilai_netto,
    //                 "type" => "Bulanan",
    //                 "can_adjust" => 1,
    //                 "t_potongan_id" => $d->id,
    //             ];
    //         }
    //     }

    //     // faktor lain :Bonus
    //     $t_bonus = t_bonus::where("m_kary_id", @$kary->id ?? 0)
    //         ->where(function($query) use ($firstDayOfMonth, $lastDayOfMonth) {
    //             $query->whereRaw("date_from <= ?", [$lastDayOfMonth])
    //             ->whereRaw("date_to >= ?", [$firstDayOfMonth]);
    //         })
    //         ->where('status', 'POSTED')
    //         ->get();
        

    //     if (count($t_bonus)) {
    //         foreach ($t_bonus as $d) {
    //             $nilai_netto = (float) $d->nilai;
    //             $defaultColumns[] = [
    //                 "label" => "Bonus - $d->keterangan",
    //                 "factor" => "+",
    //                 "value" => $nilai_netto,
    //                 "type" => "Bulanan",
    //                 "can_adjust" => 1,
    //                 "t_bonus_id" => $d->id,
    //             ];
    //         }
    //     }

    //     return $defaultColumns;
    // }

    private function factorSalaryHarian($standart_gaji, $kary = null, $periode_awal, $periode_akhir)
    {
        $firstDayOfMonth = Carbon::parse($periode_awal);

        $lastDayOfMonth = Carbon::parse($periode_akhir);

        $defaultColumns = [];

        if (!$kary) {
          return $defaultColumns;
        }

        $rekapData = $this->hitungRekapHarian(@$kary->id, $firstDayOfMonth, $lastDayOfMonth);
        // dd($rekapData);

        if (isset($rekapData['daily_details'])) {
          $gaji_pokok_harian = @$standart_gaji["gaji_pokok"] ?? 0;
          $total_jam_kerja = @$standart_gaji["total_jam_kerja"] ?? 7;

          // dd($standart_gaji->toArray());

          $check_absen = filter_var($standart_gaji->check_absence ?? false, FILTER_VALIDATE_BOOLEAN);
          $check_telat = filter_var($standart_gaji->check_late ?? false, FILTER_VALIDATE_BOOLEAN);

        //   dd([
        //   '1. Nilai Check Absen Terbaca' => $check_absen,
        //   '2. Nilai Check Telat Terbaca' => $check_telat,
        //   '3. Data Master Gaji Asli'     => $standart_gaji->toArray(),
        //   '4. Rekap Hari Tgl 2 Juni'     => collect($rekapData['daily_details'])->where('tanggal', '2026-06-02')->first(),
        //   '5. Rekap Hari Tgl 10 Juni'    => collect($rekapData['daily_details'])->where('tanggal', '2026-06-10')->first(),
        // ]);
        foreach ($rekapData['daily_details'] as $day) {

          $is_attend = ($day['status'] === 'ATTEND');

          if ($day['tipe'] === 'KERJA') {

            // 1. JIKA DIA ABSEN (TIDAK MASUK)
            if (!$is_attend && $check_absen == true) {
              $defaultColumns[] = [
              "label"          => "Absen / Tidak Masuk - " . $day['tanggal'] . " (" . $day['hari'] . ")",
              "hours"          => 0,
              "late_hours"     => 0, // Kolom baru: Jam telat
              "late_deduction" => 0, // Kolom baru: Nominal denda telat
              "daily_wage"     => floatval($gaji_pokok_harian),
              "hourly_wage"    => 0,
              "factor"         => "+",
              "value"          => 0,
              "type"           => "Harian",
              "can_adjust"     => 1,
            ];
          }
          // 2. JIKA DIA HADIR
          else {
            $jam_aktual = floatval($day['jam_kerja']);
            $jam_telat = 0;
            $potongan_telat = 0;
            $jam_diakui = floatval($total_jam_kerja); // Default full

            // Jika ada aturan telat dan jam aktual kurang dari target
            if ($check_telat == true && $jam_aktual < floatval($total_jam_kerja)) {
              $jam_telat = floatval($total_jam_kerja) - $jam_aktual;
              $potongan_telat = $jam_telat * ($gaji_pokok_harian / $total_jam_kerja);
              $jam_diakui = $jam_aktual; // Jam diakui menyesuaikan mesin (misal: 6)
            }

            // Hitung total bersih hari itu (Gaji full - denda telat)
            $gaji_bersih_hari_ini = floatval($gaji_pokok_harian) - $potongan_telat;

            $defaultColumns[] = [
            "label"          => "Gaji Harian - " . $day['tanggal'] . " (" . $day['hari'] . ")",
            "hours"          => floatval($jam_diakui), // Akan tampil jam aslinya (misal 6)
            "late_hours"     => floatval($jam_telat),  // Akan tampil jam telatnya (misal 1)
            "late_deduction" => floatval($potongan_telat), // Tampil nominal dendanya
            "daily_wage"     => floatval($gaji_pokok_harian),
            "hourly_wage"    => floatval($gaji_pokok_harian / $total_jam_kerja),
            "factor"         => "+",
            "value"          => floatval($gaji_bersih_hari_ini), // Hasil bersih
            "type"           => "Harian",
            "can_adjust"     => 1,
          ];
        }
        }

        // 3. HITUNGAN LEMBUR
        if (floatval($day['jam_lembur']) > 0) {
          $multiplier = $day['is_libur_nasional'] ? 2 : 1;
          $upah_per_jam = floatval($gaji_pokok_harian) / floatval($total_jam_kerja);
          $valueLembur = $upah_per_jam * floatval($day['jam_lembur']) * $multiplier;

          $labelLibur = $day['is_libur_nasional'] ? " [Libur Nasional x2]" : "";

          $defaultColumns[] = [
          "label"          => "Lembur" . $labelLibur . " - " . $day['tanggal'],
          "hours"          => floatval($day['jam_lembur']),
          "late_hours"     => 0,
          "late_deduction" => 0,
          "daily_wage"     => floatval($gaji_pokok_harian),
          "hourly_wage"    => floatval($upah_per_jam),
          "factor"         => "+",
          "value"          => floatval($valueLembur),
          "type"           => "Lembur",
          "can_adjust"     => 1,
        ];
        }
        }
      }

      $t_potongan = t_potongan::where(function ($query) use ($kary) {
        $query->where("m_kary_id", @$kary->id ?? 0)
        ->orWhere("is_all_kary", true);
      })
      ->whereRaw("date_from >= ? and date_to <= ?", [
        $firstDayOfMonth,
        $lastDayOfMonth,
      ])
      ->where('status', 'POSTED')
      ->get();

      if (count($t_potongan)) {
        foreach ($t_potongan as $d) {
          $nilai_netto = ((float) $d->nilai * (float) $d->percentage) / 100;
          $defaultColumns[] = [
          "label" => "Potongan - $d->nomor",
          "factor" => "-",
          "value" => $nilai_netto,
          "type" => "Bulanan",
          "can_adjust" => 1,
          "t_potongan_id" => $d->id,
        ];
      }
      }

      $t_bonus = t_bonus::where("m_kary_id", @$kary->id ?? 0)
      ->where(function($query) use ($firstDayOfMonth, $lastDayOfMonth) {
        $query->whereRaw("date_from <= ?", [$lastDayOfMonth])
        ->whereRaw("date_to >= ?", [$firstDayOfMonth]);
      })
      ->where('status', 'POSTED')
      ->get();

      if (count($t_bonus)) {
        foreach ($t_bonus as $d) {
          $nilai_netto = (float) $d->nilai;
          $defaultColumns[] = [
          "label" => "Bonus - $d->keterangan",
          "factor" => "+",
          "value" => $nilai_netto,
          "type" => "Bulanan",
          "can_adjust" => 1,
          "t_bonus_id" => $d->id,
        ];
      }
      }

      return $defaultColumns;
    }

    private function matchDayRowGaji($detailsCollection, Carbon $dateObj)
    {
        if (!$detailsCollection || $detailsCollection->isEmpty()) {
            return null;
        }

        $dayMap = [
            'senin' => 1, 'monday' => 1,
            'selasa' => 2, 'tuesday' => 2,
            'rabu' => 3, 'wednesday' => 3,
            'kamis' => 4, 'thursday' => 4,
            'jumat' => 5, "jum'at" => 5, 'friday' => 5,
            'sabtu' => 6, 'saturday' => 6,
            'minggu' => 7, 'ahad' => 7, 'sunday' => 7,
        ];

        $dayNumIso = (int)$dateObj->dayOfWeekIso;
        $namaHariId = strtolower(trim($dateObj->translatedFormat('l')));
        $namaHariEn = strtolower(trim($dateObj->format('l')));

        return $detailsCollection->first(function ($row) use ($dayNumIso, $namaHariId, $namaHariEn, $dayMap) {
            $rDayNum = !empty($row->day_num) ? (int)$row->day_num : 0;
            $rDayLower = strtolower(trim((string)($row->day ?? $row->hari ?? '')));
            $calcDayNum = $dayMap[$rDayLower] ?? 0;

            if ($rDayNum > 0 && $rDayNum === $dayNumIso) return true;
            if ($calcDayNum > 0 && $calcDayNum === $dayNumIso) return true;
            if ($rDayLower === $namaHariId || $rDayLower === $namaHariEn) return true;
            return false;
        });
    }

    private function preloadJadwalGaji($karyId, Carbon $end): array
    {
        $jadwals = \DB::table("t_jadwal_kerja_d_n as d")
            ->leftJoin("t_jadwal_kerja_n as j", "j.id", "=", "d.t_jadwal_kerja_n_id")
            ->select("d.*", "j.status as master_status")
            ->where("d.m_kary_id", $karyId)
            ->where(function ($q) use ($end) {
                $q->whereNull("d.start_date")
                  ->orWhereDate("d.start_date", "<=", $end->format("Y-m-d"));
            })
            ->orderByRaw("CASE WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' AND UPPER(COALESCE(j.status, 'AKTIF')) = 'AKTIF' THEN 0 WHEN UPPER(COALESCE(d.status, '')) = 'AKTIF' THEN 1 ELSE 2 END ASC")
            ->orderByDesc("d.start_date")
            ->orderByDesc("d.id")
            ->get();

        $jadwalIds = $jadwals->pluck("t_jadwal_kerja_n_id")->filter()->unique()->values();
        $jadwalHarisMap = $jadwalIds->isNotEmpty()
            ? \DB::table("t_jadwal_kerja_d_hari_n")
                ->whereIn("t_jadwal_kerja_n_id", $jadwalIds)
                ->get()
                ->groupBy("t_jadwal_kerja_n_id")
            : collect();
        $legacyHarisMap = $jadwalIds->isNotEmpty()
            ? \DB::table("t_jadwal_kerja_det_hari")
                ->whereIn("t_jadwal_kerja_id", $jadwalIds)
                ->get()
                ->groupBy("t_jadwal_kerja_id")
            : collect();
        $legacyDet = $jadwals->isEmpty()
            ? \DB::table("t_jadwal_kerja_det")->where("m_kary_id", $karyId)->orderByDesc("id")->first()
            : null;
        $legacyFallbackRows = ($legacyDet && !empty($legacyDet->t_jadwal_kerja_id))
            ? \DB::table("t_jadwal_kerja_det_hari")->where("t_jadwal_kerja_id", $legacyDet->t_jadwal_kerja_id)->get()
            : collect();

        return compact("jadwals", "jadwalHarisMap", "legacyHarisMap", "legacyFallbackRows");
    }

    private function resolveDetailHariGaji(array $preloaded, Carbon $tanggal, $presensiRow = null)
    {
        if ($presensiRow && $presensiRow->t_jadwal_kerja_d_hari_n) {
            return $presensiRow->t_jadwal_kerja_d_hari_n;
        }

        $key = $tanggal->format("Y-m-d");
        $activeJadwal = $preloaded["jadwals"]->first(function ($j) use ($key) {
            return empty($j->start_date) || Carbon::parse($j->start_date)->format("Y-m-d") <= $key;
        });

        if ($activeJadwal) {
            $rows = $preloaded["jadwalHarisMap"]->get($activeJadwal->t_jadwal_kerja_n_id);
            if ($rows && $rows->isNotEmpty()) {
                return $this->matchDayRowGaji($rows, $tanggal);
            }
            $legRows = $preloaded["legacyHarisMap"]->get($activeJadwal->t_jadwal_kerja_n_id);
            if ($legRows && $legRows->isNotEmpty()) {
                return $this->matchDayRowGaji($legRows, $tanggal);
            }
        } elseif ($preloaded["legacyFallbackRows"]->isNotEmpty()) {
            return $this->matchDayRowGaji($preloaded["legacyFallbackRows"], $tanggal);
        }

        return null;
    }

    private function hitungRekap($kary_id, $start, $end): array
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();
        $period = CarbonPeriod::create($start, $end);
        // dd($start, $end);

        $m_kary = m_kary::findOrFail($kary_id);

        $userId = default_users::where("m_kary_id", $m_kary->id)
            ->pluck("id")
            ->first();

        $presensi = presensi_absensi::with(["t_jadwal_kerja_d_hari_n.m_jam_kerja", "m_jam_kerja"])
            ->where("default_user_id", $userId)
            ->whereBetween("tanggal", [$start->format("Y-m-d"), $end->format("Y-m-d")])
            ->get()
            ->keyBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $cuti = t_cuti::where("m_kary_id", $kary_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween("date_from", [$start->format("Y-m-d"), $end->format("Y-m-d")])
                    ->orWhereBetween("date_to", [$start->format("Y-m-d"), $end->format("Y-m-d")])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where("date_from", "<=", $start->format("Y-m-d"))->where(
                            "date_to",
                            ">=",
                            $end->format("Y-m-d")
                        );
                    });
            })
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get();

        $cutiDates = [];
        foreach ($cuti as $c) {
            $cutiStart = Carbon::parse($c->date_from)->startOfDay();
            $cutiEnd = Carbon::parse($c->date_to)->startOfDay();
            if ($cutiStart->lessThan($start)) $cutiStart = $start->copy();
            if ($cutiEnd->greaterThan($end)) $cutiEnd = $end->copy();
            if ($cutiStart->lte($cutiEnd)) {
                foreach (CarbonPeriod::create($cutiStart, $cutiEnd) as $tgl) {
                    $cutiDates[$tgl->format("Y-m-d")] = $c->keterangan ?? "CUTI";
                }
            }
        }

        $preloadedJadwal = $this->preloadJadwalGaji($m_kary->id, $end);

        // --- Hitung Rekap ---
        $hasil = [];
        $jumlah_hadir = 0;
        $total_menit_lembur_kerja = 0;
        $total_menit_lembur_libur = 0;
        $total_menit_terlambat = 0;
        $detail_menit_terlambat = [];
        $total_jam_terlambat = 0 ;
        $total_jam_tidak_hadir = 0;

        // ambil data lembur dalam periode
        $lembur = t_lembur::where("m_kary_id", $kary_id)
            ->whereBetween("tanggal", [$start->format("Y-m-d"), $end->format("Y-m-d")])
            // ->where("status", "POSTED")
            ->where("status", "APPROVED")
            ->get()
            ->groupBy(
                fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d")
            );

        $liburNasional = m_libur_nasional::whereBetween("tanggal", [
            $start->format("Y-m-d"),
            $end->format("Y-m-d"),
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
            $tipe = "KERJA";

            $status = $data?->status ?? "NOT ATTEND";
            if (isset($cutiDates[$key])) {
                $status = $cutiDates[$key];
            }

            $detail_hari = $this->resolveDetailHariGaji($preloadedJadwal, $tanggal, $data);
            $jadwalMulai = null;
            $jadwalSelesai = null;

            if ($detail_hari) {
                $tipe = strtoupper(trim((string)($detail_hari->tipe_hari ?? "KERJA")));
                if (empty($tipe)) $tipe = "KERJA";
            }

            $wMulaiStr = $detail_hari?->waktu_mulai ?? ($data?->m_jam_kerja?->waktu_mulai ?? null);
            $wAkhirStr = $detail_hari?->waktu_akhir ?? ($data?->m_jam_kerja?->waktu_akhir ?? null);
            $isValidJamKerja = !empty($wMulaiStr)
                && !empty($wAkhirStr)
                && !($wMulaiStr === "00:00:00" && $wAkhirStr === "00:00:00")
                && !($wMulaiStr === "00:00" && $wAkhirStr === "00:00");

            if (!empty($wMulaiStr) && $wMulaiStr !== "00:00:00") {
                $jadwalMulai = Carbon::parse($key . " " . $wMulaiStr);
            }
            if (!empty($wAkhirStr) && $wAkhirStr !== "00:00:00") {
                $jadwalSelesai = Carbon::parse($key . " " . $wAkhirStr);
                if ($jadwalMulai && $jadwalSelesai->lessThan($jadwalMulai)) {
                    $jadwalSelesai->addDay();
                }
            }

            if (isset($liburDates[$key])) {
                $status = $liburDates[$key];
                $tipe = $liburDates[$key];
            }

            // --- hitung hadir ---
            if ($status === "ATTEND" && $tipe === 'KERJA') {
                $jumlah_hadir++;
            }

            // --- hitung lembur ---
            if (isset($lembur[$key])) {
                foreach ($lembur[$key] as $l) {
                    $tglMulaiL = Carbon::parse($l->tanggal)->format("Y-m-d");
                    $tglSelesaiL = !empty($l->tanggal_selesai)
                        ? Carbon::parse($l->tanggal_selesai)->format("Y-m-d")
                        : $tglMulaiL;
                    $mulai = Carbon::parse($tglMulaiL . " " . $l->jam_mulai);
                    $selesai = Carbon::parse($tglSelesaiL . " " . $l->jam_selesai);
                    if ($tglSelesaiL === $tglMulaiL && $selesai->lessThan($mulai)) {
                        $selesai->addDay();
                    }
                    $menit = $selesai->greaterThan($mulai) ? $selesai->diffInMinutes($mulai) : 0;

                    if ($tipe === "KERJA") {
                        $total_menit_lembur_kerja += $menit;
                    } else {
                        $total_menit_lembur_libur += $menit;
                    }
                }
            }

            // --- hitung terlambat ---
            if ($tipe === "KERJA" && !isset($cutiDates[$key]) && !isset($liburDates[$key]) && $data && $data->checkin_time && $jadwalMulai) {
                $checkin = Carbon::parse($key . " " . $data->checkin_time);

                // Tetap cek apakah checkin lebih besar dari jadwal mulai
                if ($checkin->greaterThan($jadwalMulai)) {
                    
                    // Hitung selisih menit (ini mengabaikan sisa detik)
                    // Contoh: selisih 59 detik -> hasilnya 0 menit
                    // Contoh: selisih 1 menit 1 detik -> hasilnya 1 menit
                    $menit_terlambat = $jadwalMulai->diffInMinutes($checkin);
                    
                    // HANYA MASUK LOGIKA TELAT JIKA SUDAH LEBIH DARI ATAU SAMA DENGAN 1 MENIT
                    if ($menit_terlambat > 0) {
                        
                        $total_menit_terlambat += $menit_terlambat;

                        /**
                        * Logika Anda: 
                        * 1 menit s/d 59 menit = (floor 0) + 1 = 1 jam
                        * 60 menit = (floor 1) + 1 = 2 jam (Sesuai catatan penalti Anda di atas)
                        */
                        $jam_terlambat_dihitung = floor($menit_terlambat / 60) + 1;

                        $total_jam_terlambat += $jam_terlambat_dihitung;
                        $detail_menit_terlambat[] = $jam_terlambat_dihitung;
                    }
                }
            }

            // --- hitung tidak hadir ---
            if ($tipe === "KERJA" && $status === "NOT ATTEND") {
                if ($isValidJamKerja && $jadwalMulai && $jadwalSelesai) {
                    // Gunakan max(0, ...) untuk menghindari angka negatif jika selisih < 1 jam
                    $selisih = $jadwalSelesai->diffInHours($jadwalMulai);
                    $total_jam_tidak_hadir += ($selisih > 0) ? $selisih - 1 : 0;
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

    public function hitungRekapHarian($kary_id, $start, $end): array
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();
        $period = CarbonPeriod::create($start, $end);

        $m_kary = m_kary::findOrFail($kary_id);

        $userId = default_users::where("m_kary_id", $m_kary->id)
            ->pluck("id")
            ->first();

        // 1. Ambil data Presensi
        $presensi = presensi_absensi::with("t_jadwal_kerja_d_hari_n")
        ->where("default_user_id", $userId)
        ->whereBetween("tanggal", [$start->format("Y-m-d"), $end->format("Y-m-d")])
        ->get()
        ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d"));

        // 2. Ambil data Lembur (Hanya yang APPROVED)
        $lembur = t_lembur::where("m_kary_id", $kary_id)
            ->whereBetween("tanggal", [$start->format("Y-m-d"), $end->format("Y-m-d")])
            ->where("status", "APPROVED")
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->tanggal)->format("Y-m-d"));

        // 3. Ambil data Cuti (filter periode + clamp agar cuti lintas periode terhitung akurat)
        $cuti = t_cuti::where("m_kary_id", $kary_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween("date_from", [$start->format("Y-m-d"), $end->format("Y-m-d")])
                    ->orWhereBetween("date_to", [$start->format("Y-m-d"), $end->format("Y-m-d")])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where("date_from", "<=", $start->format("Y-m-d"))
                           ->where("date_to", ">=", $end->format("Y-m-d"));
                    });
            })
            ->where("status", "APPROVED")
            ->get();

        $cutiDates = [];
        foreach ($cuti as $c) {
            $cutiStart = Carbon::parse($c->date_from)->startOfDay();
            $cutiEnd = Carbon::parse($c->date_to)->startOfDay();
            if ($cutiStart->lessThan($start)) $cutiStart = $start->copy();
            if ($cutiEnd->greaterThan($end)) $cutiEnd = $end->copy();
            if ($cutiStart->lte($cutiEnd)) {
                foreach (CarbonPeriod::create($cutiStart, $cutiEnd) as $tgl) {
                    $cutiDates[$tgl->format("Y-m-d")] = $c->keterangan ?? "CUTI";
                }
            }
        }

        // 4. Ambil data Libur Nasional
        $liburDates = m_libur_nasional::whereBetween("tanggal", [$start->format("Y-m-d"), $end->format("Y-m-d")])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format("Y-m-d") => $item->keterangan ?? "LIBUR NASIONAL"];
            });

        $preloadedJadwal = $this->preloadJadwalGaji($m_kary->id, $end);

        $dailyDetails = [];
        
        $jumlah_hadir = 0;
        $hari_kerja = 0;
        $total_menit_lembur_kerja = 0;
        $total_menit_lembur_libur = 0;

        foreach ($period as $tanggal) {
            $key = $tanggal->format("Y-m-d");
            $data = $presensi[$key] ?? null;
            
            // Cek apakah tanggal ini ada di tabel libur nasional
            $is_libur_nasional = isset($liburDates[$key]);

            // Tentukan Tipe Hari dari snapshot atau jadwal karyawan
            $detail_hari = $this->resolveDetailHariGaji($preloadedJadwal, $tanggal, $data);
            $tipe = strtoupper(trim((string)($detail_hari?->tipe_hari ?? "KERJA")));
            if (empty($tipe)) $tipe = "KERJA";

            // Overwrite status/tipe jika libur nasional
            $status = $data?->status ?? "NOT ATTEND";
            if (isset($cutiDates[$key])) $status = $cutiDates[$key];
            
            if ($is_libur_nasional) {
                $status = $liburDates[$key];
                $tipe = "LIBUR"; // Dianggap hari libur
            }

            // --- HITUNG DURASI LEMBUR HARIAN ---
            $menit_lembur_hari_ini = 0;
            if (isset($lembur[$key])) {
                foreach ($lembur[$key] as $l) {
                    $tglMulaiL = Carbon::parse($l->tanggal)->format("Y-m-d");
                    $tglSelesaiL = !empty($l->tanggal_selesai)
                        ? Carbon::parse($l->tanggal_selesai)->format("Y-m-d")
                        : $tglMulaiL;
                    $mulaiL = Carbon::parse($tglMulaiL . " " . $l->jam_mulai);
                    $selesaiL = Carbon::parse($tglSelesaiL . " " . $l->jam_selesai);
                    if ($tglSelesaiL === $tglMulaiL && $selesaiL->lessThan($mulaiL)) {
                        $selesaiL->addDay();
                    }
                    $menit_lembur_hari_ini += $selesaiL->greaterThan($mulaiL) ? $selesaiL->diffInMinutes($mulaiL) : 0;
                }
            }

            // Klasifikasi rekap
            if ($tipe === "KERJA") {
                $hari_kerja++;
                $total_menit_lembur_kerja += $menit_lembur_hari_ini;
                if ($status === "ATTEND") $jumlah_hadir++;
            } else {
                // Termasuk Libur Nasional atau Sabtu/Minggu (tergantung jadwal)
                $total_menit_lembur_libur += $menit_lembur_hari_ini;
            }

            $jamKerjaHarian = 0;
            if (!empty($data?->jam_kerja_diakui)) {
                $jamKerjaHarian = (float) $data->jam_kerja_diakui;
            } elseif ($data?->checkin_time && $data?->checkout_time) {
                $inDate = Carbon::parse($data->tanggal ?? $key)->format("Y-m-d");
                $outDate = !empty($data->checkout_date) ? Carbon::parse($data->checkout_date)->format("Y-m-d") : $inDate;
                $inDt = Carbon::parse($inDate . " " . $data->checkin_time);
                $outDt = Carbon::parse($outDate . " " . $data->checkout_time);
                if ($outDate === $inDate && $outDt->lessThan($inDt)) {
                    $outDt->addDay();
                }
                $jamKerjaHarian = $outDt->greaterThan($inDt) ? (float) round($inDt->diffInMinutes($outDt) / 60, 2) : 0;
            }

            $dailyDetails[] = [
                "tanggal"           => $key,
                "hari"              => $tanggal->translatedFormat("l"),
                "tipe"              => $tipe,
                "status"            => $status,
                "is_libur_nasional" => $is_libur_nasional, // Flagging khusus pesananmu
                "jam_masuk"         => $data?->checkin_time ? Carbon::parse($data->checkin_time)->format('H:i') : '-',
                "jam_pulang"        => $data?->checkout_time ? Carbon::parse($data->checkout_time)->format('H:i') : '-',
                "menit_lembur"      => $menit_lembur_hari_ini,
                "jam_lembur"        => round($menit_lembur_hari_ini / 60, 2),
                "jam_kerja"         => $jamKerjaHarian,
            ];
        }

        return [
            "rekap" => [
                "hari_kerja"               => $hari_kerja,
                "jumlah_hadir"             => $jumlah_hadir,
                "total_menit_lembur_kerja" => $total_menit_lembur_kerja,
                "total_menit_lembur_libur" => $total_menit_lembur_libur,
            ],
            "daily_details" => $dailyDetails
        ];
    }

    // private function countPPH21($kary, $netto = 0)
    // {
    //     $getBasicSalary = [];
    //     // pengurangan dari perhitungan pph21
    //     // ------------------------- contoh perhitungan ---------------------------
    //     // Penghasilan Neto dalam setahun Rp9.400.000 x 12	    = Rp112.800.000
    //     // PTKP Status Lajang	                                = Rp54.000.000 (-)
    //     // Pendapatan Kena Pajak (PKP):
    //     // PKP setahun Rp112.800.000 – Rp54.000.000	            = Rp58.800.000

    //     $tanggungan = m_general::find($kary->tanggungan_id);
    //     if ($tanggungan) {
    //         // persentase pajak <= Rp50.000.000                 = 5%
    //         // persentase pajak > Rp50.000.000  – Rp250.000.000 = 15%
    //         // persentase pajak > Rp250.000.000 – Rp500.000.000 = 25%
    //         // persentase pajak > Rp250.000.000 – Rp500.000.000 = 30%

    //         $nilaiTanggungan = @$tanggungan->value_2 ?? 0;
    //         $nettoYear = $netto * 12;
    //         $nettoPTKP = $nettoYear - $nilaiTanggungan;

    //         // hentikan fungsi ketika gaji masih dibawah jumlah tanggungan
    //         if ($nettoPTKP <= 0) {
    //             return $getBasicSalary;
    //         }

    //         $percent = 0;
    //         if ($nettoPTKP <= 50000000) {
    //             $before_value = 0;
    //             $before_percent = $percent;
    //             $percent = 5;
    //         } elseif ($nettoPTKP > 50000000 && $nettoPTKP <= 250000000) {
    //             $before_value = 50000000;
    //             $before_percent = $percent;
    //             $percent = 15;
    //         } elseif ($nettoPTKP > 250000000 && $nettoPTKP <= 500000000) {
    //             $before_value = 250000000;
    //             $before_percent = $percent;
    //             $percent = 25;
    //         } elseif ($nettoPTKP > 500000000) {
    //             $before_value = 500000000;
    //             $before_percent = $percent;
    //             $percent = 30;
    //         }
    //         $getBasicSalary = $this->countTaxDetail(
    //             $tanggungan,
    //             $nettoPTKP,
    //             $before_value,
    //             $before_percent,
    //             $percent,
    //             $getBasicSalary
    //         );
    //     }
    //     return $getBasicSalary;
    // }

    private function countTaxDetail(
        $tanggungan,
        $nettoPTKP,
        $before_value,
        $before_percent,
        $percent,
        $mergingArr
    ) {
        $outstanding = $nettoPTKP - $before_value;
        $tax1 = ($before_percent * $before_value) / 100;
        $tax2 = ($percent * $outstanding) / 100;
        $total_tax = $tax1 + $tax2;
        // insert dari kondisi gaji sebelumnya sebelumnya
        // ex: 5% x 50.000.000
        // ex: 15% x 800.0000
        $detail = [];
        if ($before_percent != 0) {
            // jika netto / before value memiliki sisa
            $detail = [
                [
                    "label" => "$before_percent% x $before_value",
                    "factor" => "+",
                    "value" => $tax1,
                    "type" => "Tahunan",
                ],
                [
                    "label" => "$percent% x $outstanding",
                    "factor" => "+",
                    "value" => $tax2,
                    "type" => "Tahunan",
                ],
            ];
        } else {
            // jika netto / before value tidak memiliki sisa (konidisi pertama)
            $detail = [
                [
                    "label" => "$percent% x $nettoPTKP",
                    "factor" => "+",
                    "value" => $tax2,
                    "type" => "Tahunan",
                ],
            ];
        }

        $mergingArr[] = [
            "label" => "PTKP $tanggungan->value (perbulan)",
            "factor" => "-",
            "value" => $total_tax / 12,
            "type" => "Bulanan",
            "can_adjust" => 0,
            "detail" => $detail,
        ];
        return $mergingArr;
    }

    private function countPPH21($kary, $bruto = 0)
    {
        //PERHITUNGAN BERDASARKAN TER (PP 58/2023)
        // 1. Dapatkan Kategori (A/B/C)
        $statusPTKP = $kary->tanggungan?->value;
        if (!$statusPTKP) {
            return []; 
        }

        $statusKaryawan = strtoupper($kary->status_kary?->value ?? '');
        $allowedStatus = ['TETAP', 'TIDAK TETAP', 'KONTRAK'];

        $isAllowed = false;
        foreach ($allowedStatus as $status) {
            if (str_contains($statusKaryawan, $status)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            return [];
        }

        $kategori = $this->decideCategory($statusPTKP);

        // 2. Ambil List Tarif berdasarkan Kategori
        $listTarif = $this->getTarifTER($kategori);

        // 3. Cari Tarif Persen berdasarkan Bruto
        $tarifPersen = 0;
        foreach ($listTarif as $item) {
            $min = $item['range'][0];
            $max = $item['range'][1];

            if ($max === null) { // Untuk range terakhir (di atas 1,4 Miliar)
                if ($bruto >= $min) {
                    $tarifPersen = $item['ter'];
                    break;
                }
            } else {
                if ($bruto >= $min && $bruto < $max) {
                    $tarifPersen = $item['ter'];
                    break;
                }
            }
        }

        // 4. Hitung Nominal
        $pph21Nominal = round($bruto * ($tarifPersen / 100));

        // return [
        //     'kategori' => $kategori,
        //     'tarif'    => $tarifPersen,
        //     'nominal'  => $pph21Nominal,
        //     'bruto'    => $bruto
        // ];
        $mergingArr[] = [
            // Label dinamis agar user tahu kategori dan tarif yang dikenakan
            "label"      => "PPH21 TER " . $kategori . " (" . $tarifPersen . "% x " . (int)$bruto . ")",
            "factor"     => "-",
            "value"      => $pph21Nominal, // Nilai nominal pajak TER bulanan
            "type"       => "Bulanan",
            "can_adjust" => 0,
            //"detail"     => $detail, // Anda bisa memasukkan rincian bruto ke sini jika perlu
        ];

        return $mergingArr;
    }

    public function decideCategory($status)
    {
        $status = strtoupper(trim($status));

        return match (true) {
            in_array($status, ['TK/0', 'TK/1', 'K/0']) => 'A',
            in_array($status, ['TK/2', 'K/1', 'TK/3', 'K/2']) => 'B',
            default => 'C',
        };
    }

    public function getTarifTER($kategori)
    {
        return match ($kategori) {
            'A' => $this->tarifKategoriA(),
            'B' => $this->tarifKategoriB(),
            'C' => $this->tarifKategoriC(),
            default => [],
        };
    }

    public function tarifKategoriA()
    {
        return [
            ['range' => [0, 5400000], 'ter' => 0],
            ['range' => [5400000, 5650000], 'ter' => 0.25],
            ['range' => [5650000, 5950000], 'ter' => 0.5],
            ['range' => [5950000, 6300000], 'ter' => 0.75],
            ['range' => [6300000, 6750000], 'ter' => 1],
            ['range' => [6750000, 7500000], 'ter' => 1.25],
            ['range' => [7500000, 8550000], 'ter' => 1.5],
            ['range' => [8550000, 9650000], 'ter' => 1.75],
            ['range' => [9650000, 10050000], 'ter' => 2],
            ['range' => [10050000, 10350000], 'ter' => 2.25],
            ['range' => [10350000, 10700000], 'ter' => 2.5],
            ['range' => [10700000, 11050000], 'ter' => 3],
            ['range' => [11050000, 11600000], 'ter' => 3.5],
            ['range' => [11600000, 12500000], 'ter' => 4],
            ['range' => [12500000, 13750000], 'ter' => 5],
            ['range' => [13750000, 15100000], 'ter' => 6],
            ['range' => [15100000, 16950000], 'ter' => 7],
            ['range' => [16950000, 19750000], 'ter' => 8],
            ['range' => [19750000, 24150000], 'ter' => 9],
            ['range' => [24150000, 26450000], 'ter' => 10],
            ['range' => [26450000, 28000000], 'ter' => 11],
            ['range' => [28000000, 30050000], 'ter' => 12],
            ['range' => [30050000, 32400000], 'ter' => 13],
            ['range' => [32400000, 35400000], 'ter' => 14],
            ['range' => [35400000, 39100000], 'ter' => 15],
            ['range' => [39100000, 43850000], 'ter' => 16],
            ['range' => [43850000, 47800000], 'ter' => 17],
            ['range' => [47800000, 51400000], 'ter' => 18],
            ['range' => [51400000, 56300000], 'ter' => 19],
            ['range' => [56300000, 62200000], 'ter' => 20],
            ['range' => [62200000, 68600000], 'ter' => 21],
            ['range' => [68600000, 77500000], 'ter' => 22],
            ['range' => [77500000, 89000000], 'ter' => 23],
            ['range' => [89000000, 103000000], 'ter' => 24],
            ['range' => [103000000, 125000000], 'ter' => 25],
            ['range' => [125000000, 157000000], 'ter' => 26],
            ['range' => [157000000, 206000000], 'ter' => 27],
            ['range' => [206000000, 337000000], 'ter' => 28],
            ['range' => [337000000, 454000000], 'ter' => 29],
            ['range' => [454000000, 550000000], 'ter' => 30],
            ['range' => [550000000, 695000000], 'ter' => 31],
            ['range' => [695000000, 910000000], 'ter' => 32],
            ['range' => [910000000, 1400000000], 'ter' => 33],
            ['range' => [1400000000, null], 'ter' => 34],
        ];
    }

    public function tarifKategoriB()
    {
        return [
            ['range' => [0, 6200000], 'ter' => 0],
            ['range' => [6200000, 6500000], 'ter' => 0.25],
            ['range' => [6500000, 6850000], 'ter' => 0.5],
            ['range' => [6850000, 7300000], 'ter' => 0.75],
            ['range' => [7300000, 9200000], 'ter' => 1],
            ['range' => [9200000, 10750000], 'ter' => 1.5],
            ['range' => [10750000, 11250000], 'ter' => 2],
            ['range' => [11250000, 11600000], 'ter' => 2.5],
            ['range' => [11600000, 12600000], 'ter' => 3],
            ['range' => [12600000, 13600000], 'ter' => 4],
            ['range' => [13600000, 14950000], 'ter' => 5],
            ['range' => [14950000, 16400000], 'ter' => 6],
            ['range' => [16400000, 18450000], 'ter' => 7],
            ['range' => [18450000, 21850000], 'ter' => 8],
            ['range' => [21850000, 26000000], 'ter' => 9],
            ['range' => [26000000, 27700000], 'ter' => 10],
            ['range' => [27700000, 29350000], 'ter' => 11],
            ['range' => [29350000, 31450000], 'ter' => 12],
            ['range' => [31450000, 33950000], 'ter' => 13],
            ['range' => [33950000, 37100000], 'ter' => 14],
            ['range' => [37100000, 41100000], 'ter' => 15],
            ['range' => [41100000, 45800000], 'ter' => 16],
            ['range' => [45800000, 49500000], 'ter' => 17],
            ['range' => [49500000, 53800000], 'ter' => 18],
            ['range' => [53800000, 58500000], 'ter' => 19],
            ['range' => [58500000, 64000000], 'ter' => 20],
            ['range' => [64000000, 71000000], 'ter' => 21],
            ['range' => [71000000, 80000000], 'ter' => 22],
            ['range' => [80000000, 93000000], 'ter' => 23],
            ['range' => [93000000, 109000000], 'ter' => 24],
            ['range' => [109000000, 129000000], 'ter' => 25],
            ['range' => [129000000, 163000000], 'ter' => 26],
            ['range' => [163000000, 211000000], 'ter' => 27],
            ['range' => [211000000, 374000000], 'ter' => 28],
            ['range' => [374000000, 459000000], 'ter' => 29],
            ['range' => [459000000, 555000000], 'ter' => 30],
            ['range' => [555000000, 704000000], 'ter' => 31],
            ['range' => [704000000, 957000000], 'ter' => 32],
            ['range' => [957000000, 1405000000], 'ter' => 33],
            ['range' => [1405000000, null], 'ter' => 34],
        ];
    }

    public function tarifKategoriC()
    {
        return [
            ['range' => [0, 6600000], 'ter' => 0],
            ['range' => [6600000, 6950000], 'ter' => 0.25],
            ['range' => [6950000, 7350000], 'ter' => 0.5],
            ['range' => [7350000, 7800000], 'ter' => 0.75],
            ['range' => [7800000, 8850000], 'ter' => 1],
            ['range' => [8850000, 9800000], 'ter' => 1.25],
            ['range' => [9800000, 10950000], 'ter' => 1.5],
            ['range' => [10950000, 11200000], 'ter' => 1.75],
            ['range' => [11200000, 12050000], 'ter' => 2],
            ['range' => [12050000, 12950000], 'ter' => 3],
            ['range' => [12950000, 14150000], 'ter' => 4],
            ['range' => [14150000, 15550000], 'ter' => 5],
            ['range' => [15550000, 17050000], 'ter' => 6],
            ['range' => [17050000, 19500000], 'ter' => 7],
            ['range' => [19500000, 22700000], 'ter' => 8],
            ['range' => [22700000, 26600000], 'ter' => 9],
            ['range' => [26600000, 28100000], 'ter' => 10],
            ['range' => [28100000, 30100000], 'ter' => 11],
            ['range' => [30100000, 32600000], 'ter' => 12],
            ['range' => [32600000, 35400000], 'ter' => 13],
            ['range' => [35400000, 38900000], 'ter' => 14],
            ['range' => [38900000, 43000000], 'ter' => 15],
            ['range' => [43000000, 47400000], 'ter' => 16],
            ['range' => [47400000, 51200000], 'ter' => 17],
            ['range' => [51200000, 55800000], 'ter' => 18],
            ['range' => [55800000, 60400000], 'ter' => 19],
            ['range' => [60400000, 66700000], 'ter' => 20],
            ['range' => [66700000, 74500000], 'ter' => 21],
            ['range' => [74500000, 83200000], 'ter' => 22],
            ['range' => [83200000, 95000000], 'ter' => 23],
            ['range' => [95000000, 110000000], 'ter' => 24],
            ['range' => [110000000, 134000000], 'ter' => 25],
            ['range' => [134000000, 169000000], 'ter' => 26],
            ['range' => [169000000, 221000000], 'ter' => 27],
            ['range' => [221000000, 390000000], 'ter' => 28],
            ['range' => [390000000, 463000000], 'ter' => 29],
            ['range' => [463000000, 561000000], 'ter' => 30],
            ['range' => [561000000, 709000000], 'ter' => 31],
            ['range' => [709000000, 965000000], 'ter' => 32],
            ['range' => [965000000, 1419000000], 'ter' => 33],
            ['range' => [1419000000, null], 'ter' => 34],
        ];
    }

    private function summarySubSalary($arrConfig)
    {
        return array_reduce(
            $arrConfig,
            function ($carry, $item) {
                if (is_numeric($item["value"])) {
                    $value = (float) $item["value"];
                    if ($value != 0) {
                        if ($item["factor"] == "+") {
                            $carry = $carry + $item["value"];
                        } elseif ($item["factor"] == "-") {
                            $carry = $carry - $item["value"];
                        }
                    }
                }
                return $carry;
            },
            0
        );
    }

    // public function salaryOfKary($id, $periode_awal, $periode_akhir)
    // {
    //     $m_kary_id = $id;
    //     $kary = m_kary::find($m_kary_id);
    //     $checkExists = m_standart_gaji::where(
    //         "m_kary_id",
    //         $kary["id"]
    //     )->exists();
        
    //     if ($checkExists) {
    //         // $m_standart_gaji = m_standart_gaji::where(
    //         //     "m_kary_id",
    //         //     $kary["id"]
    //         // )->first();

    //         //logic baru ambil by start date periode
    //         $m_standart_gaji = m_standart_gaji::where("m_kary_id", $m_kary_id)
    //         ->whereDate("start_date", "<=", $periode_awal)
    //         ->orderByDesc("start_date")
    //         ->first();
    //     } 
    //     // else {
    //     //     $m_standart_gaji = m_standart_gaji::find($kary->m_standart_gaji_id);
    //     // }
    //     // trigger_error(json_encode($m_standart_gaji));

    //     // default summary salary
    //     // dd($periode_akhir, $periode_awal);

    //     // dd($m_standart_gaji);
    //     $getBasicSalary = $this->factorSalary(
    //         $m_standart_gaji,
    //         $kary,
    //         $periode_awal,
    //         $periode_akhir
    //     );
    //     $netto = $this->summarySubSalary($getBasicSalary);
    //     $getBasicSalary = array_merge($getBasicSalary, [
    //         [
    //             "label" => "Total Gaji",
    //             "factor" => "=",
    //             "value" => $netto,
    //             "type" => "-",
    //         ],
    //     ]);

    //     $nettoFinish = $this->summarySubSalary($getBasicSalary);

    //     // default summary tax
    //     $brutoPajak = $this->calculateBrutoPajak($getBasicSalary, $kary, $m_standart_gaji);
    //     $totalTax = 0;

    //     // $arrPPH   = $this->countPPH21($kary, $brutoPajak);
    //     // $totalTax = @$arrPPH[0]['value'];
    //     // if(count($arrPPH)){
    //     //     $getBasicSalary = array_merge($getBasicSalary, $arrPPH);
    //     //     $nettoFinish    = $this->summarySubSalary($getBasicSalary);
    //     //     $getBasicSalary    = array_merge($getBasicSalary, [
    //     //         [
    //     //             'label'    => 'Total Gaji (Setelah PPH 21)',
    //     //             'factor'   => '=',
    //     //             'value'    => $nettoFinish,
    //     //             'type'     => '-'
    //     //         ]
    //     //     ]);
    //     // }

    //     return [
    //         "m_kary_id" => $m_kary_id,
    //         "total_gaji" => $netto,
    //         "total_tax" => $totalTax,
    //         "netto" => $nettoFinish,
    //         "detail" => $getBasicSalary,
    //     ];
    // }

    public function salaryOfKary($id, $periode_awal, $periode_akhir)
    {
        $m_kary_id = $id;
        $kary = m_kary::find($m_kary_id);
        
        // Cari standar gaji yang berlaku pada periode ini.
        // Ambil yang start_date-nya <= periode_akhir, urutkan dari yang terbaru, ambil yang teratas.
        $m_standart_gaji = m_standart_gaji::where("m_kary_id", $m_kary_id)
            ->whereDate("start_date", "<=", $periode_akhir)
            ->orderByDesc("start_date")
            ->first();

        // Fallback: Jika tidak ada standar gaji yang masuk di range tanggal tersebut, 
        // ambil standar gaji default/paling awal milik karyawan tersebut agar perhitungan tidak error.
        if (!$m_standart_gaji) {
            $m_standart_gaji = m_standart_gaji::where("m_kary_id", $m_kary_id)->first();
        }

        // default summary salary
        $getBasicSalary = $this->factorSalary(
            $m_standart_gaji,
            $kary,
            $periode_awal,
            $periode_akhir
        );
        
        $netto = $this->summarySubSalary($getBasicSalary);
        
        $getBasicSalary = array_merge($getBasicSalary, [
            [
                "label" => "Total Gaji",
                "factor" => "=",
                "value" => $netto,
                "type" => "-",
            ],
        ]);

        $nettoFinish = $this->summarySubSalary($getBasicSalary);

        // default summary tax
        $brutoPajak = $this->calculateBrutoPajak($getBasicSalary, $kary, $m_standart_gaji, $periode_akhir);
        $totalTax = 0;

        return [
            "m_kary_id" => $m_kary_id,
            "total_gaji" => $netto,
            "total_tax" => $totalTax,
            "netto" => $nettoFinish,
            "detail" => $getBasicSalary,
        ];
    }

    public function salaryOfKaryHarian($id, $periode_awal, $periode_akhir)
    {
        $m_kary_id = $id;
        $kary = m_kary::find($m_kary_id);
        $checkExists = m_standart_gaji_harian::where(
            "m_kary_id",
            $kary["id"]
        )->exists();
        if ($checkExists) {
            // $m_standart_gaji_harian = m_standart_gaji_harian::where(
            //     "m_kary_id",
            //     $kary["id"]
            // )->first();

            //logic baru ambil by periode
             $m_standart_gaji_harian = m_standart_gaji_harian::where("m_kary_id", $m_kary_id)
            ->whereDate("start_date", "<=", $periode_awal)
            ->orderByDesc("start_date")
            ->first();
        } 
       
        $getBasicSalary = $this->factorSalaryHarian(
            $m_standart_gaji_harian,
            $kary,
            $periode_awal,
            $periode_akhir
        );

        $netto = $this->summarySubSalary($getBasicSalary);
        // $getBasicSalary = array_merge($getBasicSalary, [
        //     [
        //         "label" => "Total Gaji",
        //         "factor" => "=",
        //         "value" => $netto,
        //         "type" => "-",
        //     ],
        // ]);

        $nettoFinish = $this->summarySubSalary($getBasicSalary);

        $brutoPajak = $this->calculateBrutoPajak($getBasicSalary, $kary, $m_standart_gaji_harian, $periode_akhir);
        $totalTax = 0;

        return [
            "m_kary_id" => $m_kary_id,
            "total_gaji" => $netto,
            "total_tax" => $totalTax,
            "netto" => $nettoFinish,
            "detail" => $getBasicSalary,
        ];
    }

    public function salaryOfKaryBorongan($id, $periode_awal, $periode_akhir)
    {
        $payload = [
            "start_date" => $periode_awal,
            "end_date"   => $periode_akhir,
            "pic_id"     => $id,
        ];

        // dd($payload);

        $response = Http::get(env("ERP_URL") . "/public/t_pres/hasilborongan", $payload);

        // dd($response);

        if ($response->successful()) {
            //$detailBorongan = $response->json();
            $dataErp = $response->json();

            $detailBorongan = collect($dataErp)->map(function ($item) {
                $namaMesin = $item['machine_name'] ?? '-';
                return [
                    "label"      => "Borongan - " . $item['no_pres'] . " - " . $item['date'],
                    "factor"     => "+",
                    "value"      => (float) $item['total_nominal'], 
                    "type"       => "Borongan",
                    "can_adjust" => 1,
                    "qty"     => (float)$item['qty'], 
                    "tarif"       => $item['total_dl'],
                    "machine_name" => $namaMesin
                ];
            })->toArray();
            // dd($detailBorongan);

            $totalGaji = collect($dataErp)->sum('total_nominal');
            $totalTax = 0; 
            $nettoFinish = $totalGaji - $totalTax;

            return [
                "m_kary_id"  => $id,
                "total_gaji" => $totalGaji,
                "total_tax"  => $totalTax,
                "netto"      => $nettoFinish,
                "detail"     => $detailBorongan,
            ];
        } else {
            return [
                "m_kary_id"  => $id,
                "total_gaji" => 0,
                "total_tax"  => 0,
                "netto"      => 0,
                "detail"     => [],
                "error"      => "Gagal mengambil data dari ERP: " . $response->body()
            ];
        }
    }

    private function calculateBrutoPajak($detailSalary, $kary, $m_standart_gaji, $periode_akhir = null)
    {
        $brutoPajak = 0;
        foreach ($detailSalary as $item) {
            if ($item['factor'] === '+') {
                $brutoPajak += $item['value'];
            }
        }

        // 2. Ambil Upah Dasar untuk BPJS (biasanya Gaji Pokok + Tunjangan Tetap)
        // Sesuaikan dengan field di database Anda
        $baseUpahBPJS = $m_standart_gaji->gaji_pokok ?? 0; 
        
        // 3. Hitung Premi BPJS yang dibayar Perusahaan (Penambah Bruto Pajak)
        $m_company = $kary->m_subcomp?->m_company;
        
        $jkkPerusahaan = round(((float)($m_company?->iuran_jkk ?? 0.24) / 100) * $baseUpahBPJS);
        $jkmPerusahaan = round(((float)($m_company?->iuran_jkm ?? 0.30) / 100) * $baseUpahBPJS);
        
        // BPJS Kesehatan Perusahaan (4%)
        // Perhatikan: BPJS Kes ada batas atas (Cap) upah, biasanya Rp12.000.000
        $limitUpahKes = 12000000;
        $upahBasisKes = min($baseUpahBPJS, $limitUpahKes);
        $bpjsKesPerusahaan = round(0.04 * $upahBasisKes);

        // 4. Tambahkan ke Bruto Pajak
        $brutoPajak += ($jkkPerusahaan + $jkmPerusahaan + $bpjsKesPerusahaan);

        // 5. Tambahkan Klaim Rawat Inap pada bulan berjalan
        if ($periode_akhir) {
            $carbonPeriod = \Carbon\Carbon::parse($periode_akhir);
            $month = $carbonPeriod->month;
            $year = $carbonPeriod->year;
            $totalRanap = \DB::table('t_klaim_ranap')
                ->where('m_kary_id', $kary->id)
                ->whereMonth('periode_akhir', $month)
                ->whereYear('periode_akhir', $year)
                ->where('status', 'POSTED')
                ->sum('total_nominal') ?? 0;
            $brutoPajak += $totalRanap;
        }

        return $brutoPajak;
    }

    // public function custom_test_generate(){
    //     return m_kary::selectRaw("m_kary.*,m_general.value periode_text, m_divisi.name divisi")
    //         // ->leftJoin('m_dir','m_dir.id','m_kary.m_dir_id')
    //         ->leftJoin('m_divisi','m_divisi.id','m_kary.m_divisi_id')
    //         ->join('m_general','m_general.id','m_kary.periode_gaji_id')
    //         ->whereRaw('m_kary.m_standart_gaji_id in(select s.id from m_standart_gaji s where s.is_active = true)')
    //         ->get();
    // }

    public function generateSalary()
    {
        $req = app()->request;
        $kary = m_kary::selectRaw(
            "m_kary.*,m_general.value periode_text, m_divisi.name divisi, divisi_name.value as divisi_name"
        )
            // ->leftJoin('m_dir','m_dir.id','m_kary.m_dir_id')
            ->where("m_kary.is_active", true)
            ->leftJoin("m_divisi", "m_divisi.id", "m_kary.m_divisi_id")
            ->leftJoin("m_general as divisi_name", "divisi_name.id", "m_divisi.name")
            ->leftJoin("m_posisi", "m_posisi.id", "m_kary.m_posisi_id")
            // ->leftJoin(
            //     "m_kary_det_jabatan",
            //     "m_kary_det_jabatan.m_karyawan_id",
            //     "m_kary.id"
            // )
            // ->leftJoin("m_kary_det_jabatan", function ($join) {
            // $join->on("m_kary_det_jabatan.m_karyawan_id", "=", "m_kary.id")
            //     ->where("m_kary_det_jabatan.is_active", true)
            //     ->where("m_kary_det_jabatan.is_primary", true);
            // })
            ->join("m_general", "m_general.id", "m_kary.periode_gaji_id")
            ->whereRaw(
                "m_kary.m_standart_gaji_id in(select s.id from m_standart_gaji s where s.is_active = true)"
            )
            ->orWhereRaw(
                "EXISTS (SELECT 1 FROM m_standart_gaji sg WHERE sg.m_kary_id = m_kary.id AND sg.is_active = true)"
            );

        // if($req->m_dept_id) $kary = $kary->where('m_kary.m_dept_id', $req->m_dept_id);
        // if ($req->sbu) {
        //     $kary = $kary->where("m_kary_det_jabatan.m_subcomp_id", $req->sbu);
        // }
        // if ($req->m_branch_id) {
        //     $kary = $kary->where(
        //         "m_kary_det_jabatan.m_branch_id",
        //         $req->m_branch_id
        //     );
        // }
        // if ($req->m_divisi_id) {
        //     $kary = $kary->where(
        //         "m_kary_det_jabatan.m_divisi_id",
        //         $req->m_divisi_id
        //     );
        // }
        if ($req->sbu) {
            $kary = $kary->where("m_kary.m_subcomp_id", $req->sbu);
        }
        if ($req->m_branch_id) {
            $kary = $kary->where("m_kary.m_branch_id", $req->m_branch_id);
        }
        if ($req->m_divisi_id) {
            $kary = $kary->where("m_kary.m_divisi_id",$req->m_divisi_id);
        }
        if ($req->m_kary_id) {
            $kary = $kary->where("m_kary.id",$req->m_kary_id);
        }
        // if($req->m_divisi_id) $kary = $kary->where('m_kary.m_divisi_id', $req->m_divisi_id);

        $kary = $kary->orderBy('nama_lengkap')->get();
        //dd($kary);

        $date_from = \DateTime::createFromFormat("d/m/Y", trim($req->periode_awal));
        $date_to   = \DateTime::createFromFormat("d/m/Y", trim($req->periode_akhir));

        // dd($req->periode_awal, $req->periode_akhir, $date_from, $date_to);

        // Menghitung jumlah bulan antara tanggal_awal dan tanggal_akhir
        $interval = $date_from->diff($date_to);
        //$jumlah_bulan = $interval->y * 12 + $interval->m;
        $jumlah_bulan = 0;

        $data = [];
        for ($i = 0; $i <= $jumlah_bulan; $i++) {
            $date = $date_from->format("Y-m-d");
            foreach ($kary as $key) {
                $gaji = $this->salaryOfKary(
                    $key->id,
                    $date_from,
                    $date_to
                );

                // dd($gaji);
                // dd($kary->divisi_name);
                $data[] = [
                    "m_kary_id" => $key->id,
                    "m_kary.nik" => $key->nik,
                    "m_kary_dir_id" => $key->m_dir_id,
                    "m_kary_dir.nama" => $key->dir,
                    "m_kary_divisi_id" => $key->m_divisi_id,
                    "m_kary_divisi.name" => $key?->divisi_name ?? '',
                    "m_kary_dept_id" => $key->m_dept_id,
                    "m_kary_dept.nama" => $key->dept,
                    "m_kary_posisi_id" => $key->m_posisi_id,
                    "m_kary_posisi.name" => $key?->m_posisi?->name ?? '',
                    "nik" => $key->nik,
                    "nama_lengkap" => $key->nama_lengkap,
                    "periode" => $date_from->format("d-m-Y"),
                    "periode_in_date" => $date,
                    "periode_id" => $key->periode_gaji_id,
                    "periode_text" => $key->periode_text,
                    "total_tax" => $gaji["total_tax"] ?? 0,
                    "total_gaji" => $gaji["total_gaji"],
                    "netto" => $gaji["netto"],
                    "detail_gaji" => $gaji["detail"],
                ];
            }

            // dd($data);

            // Menambahkan satu bulan untuk iterasi berikutnya
            $date_from->add(new \DateInterval("P1M"));
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function generateSalaryHarian()
    {
        $req = app()->request;
        $status_harian = m_general::where('group', "STATUS KARYAWAN")->where("value", 'HARIAN')->pluck('id')->toArray();

        $kary = m_kary::selectRaw(
                "m_kary.*, m_general.value periode_text, m_divisi.name divisi, divisi_name.value as divisi_name"
            )
            ->leftJoin("m_divisi", "m_divisi.id", "m_kary.m_divisi_id")
            ->leftJoin("m_general as divisi_name", "divisi_name.id", "m_divisi.name")
            ->leftJoin("m_posisi", "m_posisi.id", "m_kary.m_posisi_id")
            ->join("m_general", "m_general.id", "m_kary.periode_gaji_id")
            ->where("m_kary.is_active", true)
            ->whereIn("m_kary.status_kary_id", $status_harian)
            ->where(function($query) {
                $query->WhereRaw("EXISTS (SELECT 1 FROM m_standart_gaji_harian sg WHERE sg.m_kary_id = m_kary.id AND sg.is_active = true)");
            })
            ;

        if ($req->sbu) {
            $kary->where("m_kary.m_subcomp_id", $req->sbu);
        }
        if ($req->m_branch_id) {
            $kary->where("m_kary.m_branch_id", $req->m_branch_id);
        }
        if ($req->m_divisi_id) {
            $kary->where("m_kary.m_divisi_id", $req->m_divisi_id);
        }
        if ($req->m_kary_id) {
            $kary->where("m_kary.id", $req->m_kary_id);
        }

        $kary = $kary->get();
        // dd($kary);

        $date_from = \DateTime::createFromFormat("d/m/Y", trim($req->periode_awal));
        $date_to   = \DateTime::createFromFormat("d/m/Y", trim($req->periode_akhir));

        $interval = $date_from->diff($date_to);
        $jumlah_bulan = 0;

        $data = [];
        for ($i = 0; $i <= $jumlah_bulan; $i++) {
            $date = $date_from->format("Y-m-d");
            foreach ($kary as $key) {
                $gaji = $this->salaryOfKaryHarian(
                    $key->id,
                    $date_from,
                    $date_to
                );

                $data[] = [
                    "m_kary_id" => $key->id,
                    "m_kary.nik" => $key->nik,
                    "m_kary_dir_id" => $key->m_dir_id,
                    "m_kary_dir.nama" => $key->dir,
                    "m_kary_divisi_id" => $key->m_divisi_id,
                    "m_kary_divisi.name" => $key?->divisi_name ?? '',
                    "m_kary_dept_id" => $key->m_dept_id,
                    "m_kary_dept.nama" => $key->dept,
                    "m_kary_posisi_id" => $key->m_posisi_id,
                    "m_kary_posisi.name" => $key?->m_posisi?->name ?? '',
                    "nik" => $key->nik,
                    "nama_lengkap" => $key->nama_lengkap,
                    "periode" => $date_from->format("d-m-Y"),
                    "periode_in_date" => $date,
                    "periode_id" => $key->periode_gaji_id,
                    "periode_text" => $key->periode_text,
                    "total_tax" => $gaji["total_tax"] ?? 0,
                    "total_gaji" => $gaji["total_gaji"],
                    "netto" => $gaji["netto"],
                    "detail_gaji" => $gaji["detail"],
                ];
            }

            $date_from->add(new \DateInterval("P1M"));
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function generateSalaryBoronganOS()
    {
        $req = app()->request;
        // $status_borongan = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->where("value", 'BORONGAN')->pluck('id')->toArray();
        $status_borongan = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        // dd($status_borongan);
        $kary = m_kary::selectRaw(
                "m_kary.*, m_divisi.name divisi, divisi_name.value as divisi_name"
            )
            ->leftJoin("m_divisi", "m_divisi.id", "m_kary.m_divisi_id")
            ->leftJoin("m_general as divisi_name", "divisi_name.id", "m_divisi.name")
            ->leftJoin("m_posisi", "m_posisi.id", "m_kary.m_posisi_id")
            //->join("m_general", "m_general.id", "m_kary.periode_gaji_id")
            ->where("m_kary.is_active", true)
            ->whereIn("m_kary.status_kary_id", $status_borongan)
            ;

        // dd($kary->get());

        if ($req->sbu) {
            $kary->where("m_kary.m_subcomp_id", $req->sbu);
        }
        if ($req->m_branch_id) {
            $kary->where("m_kary.m_branch_id", $req->m_branch_id);
        }
        if ($req->m_divisi_id) {
            $kary->where("m_kary.m_divisi_id", $req->m_divisi_id);
        }
        if ($req->m_kary_id) {
            $kary->where("m_kary.id", $req->m_kary_id);
        }

        $kary = $kary->get();
        // dd($kary);

        $date_from = \DateTime::createFromFormat("d/m/Y", trim($req->periode_awal));
        $date_to   = \DateTime::createFromFormat("d/m/Y", trim($req->periode_akhir));

        $interval = $date_from->diff($date_to);
        $jumlah_bulan = 0;

        $data = [];
        for ($i = 0; $i <= $jumlah_bulan; $i++) {
            $date = $date_from->format("Y-m-d");
            foreach ($kary as $key) {
                $gaji = $this->salaryOfKaryBorongan(
                    $key->id,
                    $date_from ? $date_from->format("Y-m-d") : "", 
                    $date_to ? $date_to->format("Y-m-d") : ""
                    // $date_from,
                    // $date_to
                );

                if (empty($gaji["total_gaji"])) {
                    continue;
                }

                $data[] = [
                    "m_kary_id" => $key->id,
                    "m_kary.nik" => $key->nik,
                    "m_kary_dir_id" => $key->m_dir_id,
                    "m_kary_dir.nama" => $key->dir,
                    "m_kary_divisi_id" => $key->m_divisi_id,
                    "m_kary_divisi.name" => $key?->divisi_name ?? '',
                    "m_kary_dept_id" => $key->m_dept_id,
                    "m_kary_dept.nama" => $key->dept,
                    "m_kary_posisi_id" => $key->m_posisi_id,
                    "m_kary_posisi.name" => $key?->m_posisi?->name ?? '',
                    "nik" => $key->nik,
                    "nama_lengkap" => $key->nama_lengkap,
                    "periode" => $date_from->format("d-m-Y"),
                    "periode_in_date" => $date,
                    "periode_id" => $key->periode_gaji_id,
                    "periode_text" => $key->periode_text,
                    "total_tax" => $gaji["total_tax"] ?? 0,
                    "total_gaji" => $gaji["total_gaji"],
                    "netto" => $gaji["netto"],
                    "detail_gaji" => $gaji["detail"],
                ];
            }

            $date_from->add(new \DateInterval("P1M"));
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function generateSalaryHarianOS()
    {
        $req = app()->request;
        $status_harian = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        $kary = m_kary::selectRaw(
                "m_kary.*, m_general.value periode_text, m_divisi.name divisi, divisi_name.value as divisi_name"
            )
            ->leftJoin("m_divisi", "m_divisi.id", "m_kary.m_divisi_id")
            ->leftJoin("m_general as divisi_name", "divisi_name.id", "m_divisi.name")
            ->leftJoin("m_posisi", "m_posisi.id", "m_kary.m_posisi_id")
            ->join("m_general", "m_general.id", "m_kary.periode_gaji_id")
            ->where("m_kary.is_active", true)
            ->whereIn("m_kary.status_kary_id", $status_harian)
            ->where(function($query) {
                $query->WhereRaw("EXISTS (SELECT 1 FROM m_standart_gaji_harian sg WHERE sg.m_kary_id = m_kary.id AND sg.is_active = true)");
            })
            ;

        if ($req->sbu) {
            $kary->where("m_kary.m_subcomp_id", $req->sbu);
        }
        if ($req->m_branch_id) {
            $kary->where("m_kary.m_branch_id", $req->m_branch_id);
        }
        if ($req->m_divisi_id) {
            $kary->where("m_kary.m_divisi_id", $req->m_divisi_id);
        }
        if ($req->m_kary_id) {
            $kary->where("m_kary.id", $req->m_kary_id);
        }

        $kary = $kary->get();
        // dd($kary);

        $date_from = \DateTime::createFromFormat("d/m/Y", trim($req->periode_awal));
        $date_to   = \DateTime::createFromFormat("d/m/Y", trim($req->periode_akhir));

        $interval = $date_from->diff($date_to);
        $jumlah_bulan = 0;

        $data = [];
        for ($i = 0; $i <= $jumlah_bulan; $i++) {
            $date = $date_from->format("Y-m-d");
            foreach ($kary as $key) {
                $gaji = $this->salaryOfKaryHarian(
                    $key->id,
                    $date_from,
                    $date_to
                );

                $data[] = [
                    "m_kary_id" => $key->id,
                    "m_kary.nik" => $key->nik,
                    "m_kary_dir_id" => $key->m_dir_id,
                    "m_kary_dir.nama" => $key->dir,
                    "m_kary_divisi_id" => $key->m_divisi_id,
                    "m_kary_divisi.name" => $key?->divisi_name ?? '',
                    "m_kary_dept_id" => $key->m_dept_id,
                    "m_kary_dept.nama" => $key->dept,
                    "m_kary_posisi_id" => $key->m_posisi_id,
                    "m_kary_posisi.name" => $key?->m_posisi?->name ?? '',
                    "nik" => $key->nik,
                    "nama_lengkap" => $key->nama_lengkap,
                    "periode" => $date_from->format("d-m-Y"),
                    "periode_in_date" => $date,
                    "periode_id" => $key->periode_gaji_id,
                    "periode_text" => $key->periode_text,
                    "total_tax" => $gaji["total_tax"] ?? 0,
                    "total_gaji" => $gaji["total_gaji"],
                    "netto" => $gaji["netto"],
                    "detail_gaji" => $gaji["detail"],
                ];
            }

            $date_from->add(new \DateInterval("P1M"));
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function public_generate()
    {
        $data = $this->salaryOfKary(8);

        return response(["msg" => $data]);
    }

    public function custom_generate()
    {
        return $this->generateSalary();
    }

    public function custom_generateHarian()
    {
        return $this->generateSalaryHarian();
    }

    public function custom_generateHarianOS()
    {
        return $this->generateSalaryHarianOS();
    }

     public function custom_generateBoronganOS()
    {
        return $this->generateSalaryBoronganOS();
    }


    public function custom_generatePPH($req)
    {
        // $netto = $req->netto;
        // $kary = m_kary::find($req->m_kary_id);
        // return $this->countPPH21($kary, $netto);
        return [];
    }

    // public function custom_save($req)
    // {
    //     // dd($req);
    //     $counter = count($req->detail);
    //     if ($counter) {
    //         $nomor = $this->helper->generateNomor("KODE PERHITUNGAN GAJI");
    //         foreach ($req->detail as $key) {
    //             $periode = @$key["periode_in_date"];
    //             // dd(@$key["periode"]);
    //             $checkAndDelete = $this->where("m_kary_id", @$key["m_kary_id"])
    //                 ->where("periode_in_date", $periode)
    //                 ->delete();
    //             $key["nomor"] = $nomor;
    //             $key["detail_gaji"] = json_encode($key["detail_gaji"]);
    //             $key["periode"] = $req["periode_awal"] . " - " . $req["periode_akhir"];
    //             $key["deskripsi"] = $req["deskripsi"] ?? null;
    //             $hdr = $this->create($key);
    //         }
    //     }
    //     return $this->helper->customResponse("$counter Data berhasil disimpan");
    // }

    public function custom_save($req)
    {
        $counter = count($req->detail);
        if ($counter) {
            $nomor = $this->helper->generateNomor("KODE PERHITUNGAN GAJI");
            
            foreach ($req->detail as $key) {
                $periode = @$key["periode_in_date"];
                $deskripsiReq = $req["deskripsi"] ?? null;

                $existingData = $this->where("m_kary_id", @$key["m_kary_id"])
                    ->where("periode_in_date", $periode)
                    ->where("deskripsi", $deskripsiReq) 
                    ->first();

                if ($existingData) {
                    $existingData->delete();
                }

                $key["nomor"] = $nomor;
                $key["detail_gaji"] = json_encode($key["detail_gaji"]);
                $key["periode"] = $req["periode_awal"] . " - " . $req["periode_akhir"];
                $key["deskripsi"] = $deskripsiReq;
                
                $hdr = $this->create($key);
            }
        }
        
        return $this->helper->customResponse("$counter Data berhasil disimpan");
    }

    public function scopeGenerateForFinal($model)
    {
        $req = app()->request;
        // $date_from =
        //     \DateTime::createFromFormat("Y-m-d", $req->periode_awal . "-01") ??
        //     null;
        // $date_to =
        //     \DateTime::createFromFormat("Y-m-d", $req->periode_akhir . "-30") ??
        //     null;

        // $date_from = Carbon::createFromFormat('d/m/Y', $req->periode_awal);
        // $date_to   = Carbon::createFromFormat('d/m/Y', $req->periode_akhir);

        $date_from =
            \DateTime::createFromFormat("Y-m-d", $req->periode_awal) ??
            null;
        $date_to =
            \DateTime::createFromFormat("Y-m-d", $req->periode_akhir) ??
            null;

        // dd($date_to, $date_from, $req->periode_awal);

        $model = $model->whereBetween("periode_in_date", [
            $date_from,
            $date_to,
        ]);
        if ($req->m_divisi_id) {
            $model = $model->where(
                "t_perhitungan_gaji.m_kary_divisi_id",
                $req->m_divisi_id
            );
        }
        if ($req->sub) {
            $model = $model->where(
                "m_kary.m_subcomp_id",
                $req->sub
            );
        }
        if ($req->m_branch_id) {
            $model = $model->where(
                "m_kary.m_branch_id",
                $req->m_branch_id
            );
        }

        return $model;
    }

    public function scopegetos($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        return $model->whereIn('m_kary.status_kary_id', $status_harian)
                    ->where(function ($q) {
                        $q->where('deskripsi', '!=', 'BORONGAN')
                        ->orWhereNull('deskripsi');
                    })
                    ->leftJoin('m_divisi', 't_perhitungan_gaji.m_kary_divisi_id', 'm_divisi.id')
                    ->leftJoin('m_general', 'm_divisi.name', 'm_general.id')
                    ->leftJoin('m_branch', 'm_branch.id', 'm_divisi.m_branch_id')
                    ->addSelect('m_general.value as m_general.value', 'm_branch.name as m_branch.name')
                    ;
    }

    public function scopegetboronganos($model)
    {
        $status_os = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        return $model->whereIn('m_kary.status_kary_id', $status_os)
                    ->where('deskripsi', 'BORONGAN')
                    ->leftJoin('m_divisi', 't_perhitungan_gaji.m_kary_divisi_id', 'm_divisi.id')
                    ->leftJoin('m_general', 'm_divisi.name', 'm_general.id')
                    ->leftJoin('m_branch', 'm_branch.id', 'm_divisi.m_branch_id')
                    ->addSelect('m_general.value as m_general.value', 'm_branch.name as m_branch.name')
                    ;
    }

     public function scopegetharianinternal($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN")->where("value", "HARIAN")->pluck('id')->toArray();

        return $model->whereIn('m_kary.status_kary_id', $status_harian)
                    ->where(function ($q) {
                        $q->where('deskripsi', '!=', 'BORONGAN')
                        ->orWhereNull('deskripsi');
                    })
                    ->leftJoin('m_divisi', 't_perhitungan_gaji.m_kary_divisi_id', 'm_divisi.id')
                    ->leftJoin('m_general', 'm_divisi.name', 'm_general.id')
                    ->leftJoin('m_branch', 'm_branch.id', 'm_divisi.m_branch_id')
                    ->addSelect('m_general.value as m_general.value', 'm_branch.name as m_branch.name')
                    ;

    }

    public function scopegetnonos($model)
    {
        $status_harian = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();

        return $model->whereNotIn('m_kary.status_kary_id', $status_harian) 
                ->where(function ($q) {
                    $q->where('deskripsi', 'STANDART')
                    ->orWhere('deskripsi', '!=', 'BORONGAN')
                    ->orWhereNull('deskripsi');
                })
                ->leftJoin('m_divisi', 't_perhitungan_gaji.m_kary_divisi_id', 'm_divisi.id')
                ->leftJoin('m_general', 'm_divisi.name', 'm_general.id')
                ->leftJoin('m_branch', 'm_branch.id', 'm_divisi.m_branch_id')
                ->addSelect('m_general.value as m_general.value', 'm_branch.name as m_branch.name')
                ;

    }
}
