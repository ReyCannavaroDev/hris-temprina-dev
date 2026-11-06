<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

Carbon::setLocale('id');

class presensi_absensi extends \App\Models\BasicModels\presensi_absensi
{
    private $helper;

    private $now;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
        $this->now = \Carbon::now();
    }

    public $fileColumns = [];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function onRetrieved($model)
    {
        $backendUrl = rtrim(env('BACKEND_URL'), '/');
        $fotoColumns = ['checkout_foto', 'checkin_foto'];

        foreach ($fotoColumns as $column) {
            $value = $model->{$column};

            if (!empty($value) && !str_starts_with(strtolower($value), 'http')) {
                $model->{$column} = $backendUrl . '/' . ltrim($value, '/');
            }
        }
    }

    public function default_users()
    {
        return $this->BelongsTo(
            "App\Models\BasicModels\default_users",
            "default_user_id",
            "id"
        );
    }

    public function custom_get_by_daily($req)
    {
        // $req->month = $req->month . '-01';
        // $data = \DB::select("
        //     SELECT json_agg(json_build_object(
        //         'all_days_of_month', all_days_of_month,
        //         'date_to_idn', date_to_idn,
        //         'day_name_idn', day_name_idn,
        //         'type', type,
        //         'presentase', presentase,
        //         'attend', attend,
        //         'cuti', cuti,
        //         'alpha', alpha,
        //         'total_kary', total_kary
        //     )) AS monthly_report
        //     FROM generate_monthly_report(?,?,?)", [$req->month, $req->divisi_id, $req->dept_id]);

        // if (count($data)) {
        //     return $this->helper->customResponse('OK', 200, json_decode($data[0]->monthly_report));
        // } else {
        //     return $this->helper->customResponse('OK', 200, []);
        // }

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $os_id = $req->m_os_id ?? null;
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
        ->pluck('id');

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $totalKary = default_users::whereHas("m_kary", function ($q) use (
            $req,
            $os_id,
            $status_os
        ) {
            $q->where("is_active", true)
           ->whereNotIn('status_kary_id', $status_os);

            if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {
                    // $q->whereHas('m_kary_det_jabatan', function($q2) use ($req) {
                    //     if (!empty($req->m_subcomp_id)) {
                    //         $q2->where('m_subcomp_id', $req->m_subcomp_id);
                    //     }
                    //     if (!empty($req->m_branch_id)) {
                    //         $q2->where('m_branch_id', $req->m_branch_id);
                    //     }
                    //     if (!empty($req->divisi_id)) {
                    //         $q2->where('m_divisi_id', $req->divisi_id);
                    //     }
                    // });
                    if (!empty($req->m_subcomp_id)) {
                        $q->where('m_subcomp_id', $req->m_subcomp_id);
                    }
                    if (!empty($req->m_branch_id)) {
                        $q->where('m_branch_id', $req->m_branch_id);
                    }
                    if (!empty($req->divisi_id)) {
                        $q->where('m_divisi_id', $req->divisi_id);
                    }
                }
        })
        ->count();

        // dd($totalKary->get());

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $status_os) {
                // $q1->whereHas('m_kary_det_jabatan', function($q2) use ($req) {
                //             if (!empty($req->m_subcomp_id)) {
                //                 $q2->where('m_subcomp_id', $req->m_subcomp_id);
                //             }
                //             if (!empty($req->m_branch_id)) {
                //                 $q2->where('m_branch_id', $req->m_branch_id);
                //             }
                //             if (!empty($req->m_divisi_id)) {
                //                 $q2->where('m_divisi_id', $req->m_divisi_id);
                //             }
                //         });
                $q1->whereNotIn('status_kary_id', $status_os);

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($req->divisi_id)) {
                    $q1->where('m_divisi_id', $req->divisi_id);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function($q) use ($req, $status_os){
                    if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {
                        // $q->whereHas('m_kary_det_jabatan', function($q2) use ($req) {
                        //     if (!empty($req->m_subcomp_id)) {
                        //         $q2->where('m_subcomp_id', $req->m_subcomp_id);
                        //     }
                        //     if (!empty($req->m_branch_id)) {
                        //         $q2->where('m_branch_id', $req->m_branch_id);
                        //     }
                        //     if (!empty($req->m_divisi_id)) {
                        //         $q2->where('m_divisi_id', $req->m_divisi_id);
                        //     }
                        // });
                        $q->whereNotIn('status_kary_id', $status_os);

                        if (!empty($req->m_subcomp_id)) {
                            $q->where('m_subcomp_id', $req->m_subcomp_id);
                        }
                        if (!empty($req->m_branch_id)) {
                            $q->where('m_branch_id', $req->m_branch_id);
                        }
                        if (!empty($req->divisi_id)) {
                            $q->where('m_divisi_id', $req->divisi_id);
                        }
                    }
                })
                // ->where("status", "POSTED")
                ->where("status", "APPROVED")
                ->whereDate("date_from", ">=", $date)
                ->whereDate("date_to", "<=", $date)
                ->count();

            $alpha = $totalKary - $hadir - $cuti;

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    public function custom_get_by_daily_os($req)
    {

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $os_id = $req->m_os_id ?? null;

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
        ->pluck('id');

        $totalKary = default_users::whereHas("m_kary", function ($q) use ($req, $os_id, $status_os) {
            $q->where("is_active", true)
            ->whereIn('status_kary_id', $status_os) // TAMBAHAN INI
            ->when($os_id, function($q) use ($os_id) {
                $q->where('m_company_outsourcing_id', $os_id);
            });

            // FILTER SUBCOMP / BRANCH / DIVISI
            if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {

                if (!empty($req->m_subcomp_id)) {
                    $q->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($req->m_divisi_id)) {
                    $q->where('m_divisi_id', $req->m_divisi_id);
                }
            }
        })
        ->count();

        // dd($totalKary->get());

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $status_os) {
                $q1->whereIn('status_kary_id', $status_os);

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($req->divisi_id)) {
                    $q1->where('m_divisi_id', $req->divisi_id);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function($q) use ($req, $status_os){
                    if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {
                        $q->whereIn('status_kary_id', $status_os);

                        if (!empty($req->m_subcomp_id)) {
                            $q->where('m_subcomp_id', $req->m_subcomp_id);
                        }
                        if (!empty($req->m_branch_id)) {
                            $q->where('m_branch_id', $req->m_branch_id);
                        }
                        if (!empty($req->divisi_id)) {
                            $q->where('m_divisi_id', $req->divisi_id);
                        }
                    }
                })
                // ->where("status", "POSTED")
                ->where("status", "APPROVED")
                ->whereDate("date_from", ">=", $date)
                ->whereDate("date_to", "<=", $date)
                ->count();

            $alpha = $totalKary - $hadir - $cuti;

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    public function custom_get_by_daily_harian($req)
    {

        if ($req->weeks) {
            [$startDate, $endDate] = explode("/", $req->weeks);
        } else {
            $startDate = Carbon::parse($req->month . "-01");
            $endDate = Carbon::parse($req->month . "-03");
        }

        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dates[] = $current->format("Y-m-d");
            $current->addDay();
        }

        $hariLibur = m_general::where("group", "HARI LIBUR")
            ->pluck("value")
            ->toArray();

        $cutiBersama = m_libur_nasional::pluck("tanggal")
            ->map(fn($t) => Carbon::parse($t)->format("Y-m-d"))
            ->toArray();

        $status_harian = m_general::where('group', 'STATUS KARYAWAN')->where('value', 'HARIAN')
        ->pluck('id');

        $totalKary = default_users::whereHas("m_kary", function ($q) use ($req, $status_harian) {
            $q->where("is_active", true)
            ->whereIn('status_kary_id', $status_harian);

            // FILTER SUBCOMP / BRANCH / DIVISI
            if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {

                if (!empty($req->m_subcomp_id)) {
                    $q->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($req->m_divisi_id)) {
                    $q->where('m_divisi_id', $req->m_divisi_id);
                }
            }
        })
        ->count();

        // dd($totalKary->get());

        $result = [];

        foreach ($dates as $index => $date) {
            $carbonDate = Carbon::parse($date);
            $dayNameIdn = $carbonDate->translatedFormat("l");
            $dayNameIdn = ucfirst($dayNameIdn);

            if (in_array($dayNameIdn, $hariLibur)) {
                $type = "Hari Libur";
            } elseif (in_array($carbonDate->format("Y-m-d"), $cutiBersama)) {
                $type = "Cuti Bersama";
            } else {
                $type = "Hari Kerja";
            }

            $hadir = default_users::whereHas("m_kary", function ($q1) use ($req, $status_harian) {
                $q1->whereIn('status_kary_id', $status_harian);

                if (!empty($req->m_subcomp_id)) {
                    $q1->where('m_subcomp_id', $req->m_subcomp_id);
                }
                if (!empty($req->m_branch_id)) {
                    $q1->where('m_branch_id', $req->m_branch_id);
                }
                if (!empty($req->divisi_id)) {
                    $q1->where('m_divisi_id', $req->divisi_id);
                }
            })
                ->whereHas("presensi_absensi", function ($q) use ($date) {
                    $q->where("tanggal", $date);
                })
                ->count();

            $cuti = t_cuti::whereHas('m_kary', function($q) use ($req, $status_harian){
                    if (!empty($req->m_subcomp_id) || !empty($req->m_branch_id) || !empty($req->m_divisi_id)) {
                        $q->whereIn('status_kary_id', $status_harian);

                        if (!empty($req->m_subcomp_id)) {
                            $q->where('m_subcomp_id', $req->m_subcomp_id);
                        }
                        if (!empty($req->m_branch_id)) {
                            $q->where('m_branch_id', $req->m_branch_id);
                        }
                        if (!empty($req->divisi_id)) {
                            $q->where('m_divisi_id', $req->divisi_id);
                        }
                    }
                })
                // ->where("status", "POSTED")
                ->where("status", "APPROVED")
                ->whereDate("date_from", ">=", $date)
                ->whereDate("date_to", "<=", $date)
                ->count();

            $alpha = $totalKary - $hadir - $cuti;

            $presentase = $totalKary > 0 ? ($hadir / $totalKary) * 100 : 0;

            $result[] = [
                "all_days_of_month" => $carbonDate->format("Y-m-d"),
                "date_to_idn" => $carbonDate->format("d-m-Y"),
                "day_name_idn" => $dayNameIdn,
                "type" => $type,
                "presentase" => $presentase,
                "attend" => $hadir,
                "cuti" => $cuti,
                "alpha" => $alpha,
                "total_kary" => $totalKary,
            ];
        }

        return $this->helper->customResponse("OK", 200, $result);

    }

    public function custom_get_by_date($req)
    {
        // $data = \DB::select("
        //     SELECT json_agg(json_build_object(
        //         'm_kary_id', m_kary_id,
        //         'default_user_id', default_user_id,
        //         'kode', kode,
        //         'nama_lengkap', nama_lengkap,
        //         'absensi', absensi
        //     )) AS att_report
        //     FROM get_employee_attendance_report(?,?)", [$req->date, $req->divisi_id]);


        // if (count($data)) {
        //     return $this->helper->customResponse('OK', 200, json_decode($data[0]->att_report));
        // } else {
        //     return $this->helper->customResponse('OK', 200, []);
        // }
        $req = app()->request;
        $os_id = $req->m_os_id ?? null;
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
        ->pluck('id');

        $kary = default_users::with(['presensi_absensi' => function($q) use ($req, $os_id){
           $q->where('tanggal', $req->date); 
        }])
        ->whereHas('m_kary', function($q) use ($req, $os_id, $status_os){
            $q->where('is_active', true)
            ->whereNotIn('status_kary_id', $status_os);

            if(!empty($req->m_os_id)){
                $q->where('m_company_outsourcing_id', $req->m_os_id);
            }
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($req->divisi_id)) {
                $q->where('m_divisi_id', $req->divisi_id);
            }
            // $q->whereHas('m_kary_det_jabatan', function($q1) use ($req){
            //     if(! empty($req->m_branch_id)){
            //         $q1->where('m_branch_id', $req->m_branch_id);
            //     }
            //     if(! empty($req->m_subcomp_id)){
            //         $q1->where('m_subcomp_id', $req->m_subcomp_id);
            //     }
            //     if(! empty($req->divisi_id)){
            //         $q1->where('m_divisi_id', $req->divisi_id);
            //     }
            // });
        })
        ->get()
        ->map(function($user) use ($req) {
            $absensi = $user->presensi_absensi->first();
            $jadwal = $absensi && $absensi->t_jadwal_kerja_d_hari_n ? $absensi->t_jadwal_kerja_d_hari_n : null ;

            if ($absensi) {
                $status = $absensi->status;
                $absensiData = $absensi;
            } else {
                $status = "NOT ATTEND";
                $absensiData = ['status' => 'NOT ATTEND'];
            }

            return [
                'm_kary_id'       => $user->m_kary_id,
                'default_user_id' => $user->id,
                "presensi_absensi_id" => $absensi?->id ?? 0,
                'kode'            => $user->kode,
                'nama_lengkap'    => $user->m_kary->nama_lengkap ?? null,
                'absensi'         => $absensiData,
                'status'          => $status,
                'jam_kerja'       => $jadwal ? $jadwal->waktu_mulai . ' - ' . $jadwal->waktu_akhir : '-',
                'desc'            => $jadwal && $jadwal->m_jam_kerja ? $jadwal->m_jam_kerja->desc : '-',
            ];
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap']);
        })
        ->values();
        ;

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_get_by_date_os($req)
    {
        $req = app()->request;
        $os_id = $req->m_os_id ?? null;
        $status_os = m_general::where('group', 'STATUS KARYAWAN OUTSOURCE')
        ->pluck('id');

        $kary = default_users::with(['presensi_absensi' => function($q) use ($req, $os_id){
           $q->where('tanggal', $req->date); 
        }])
        ->whereHas('m_kary', function($q) use ($req, $os_id, $status_os){
            $q->where('is_active', true)
            ->whereIn('status_kary_id', $status_os);
            if(!empty($req->m_os_id)){
                $q->where('m_company_outsourcing_id', $req->m_os_id);
            }
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($req->divisi_id)) {
                $q->where('m_divisi_id', $req->divisi_id);
            }
        })
        ->get()
        ->map(function($user) use ($req) {
            $absensi = $user->presensi_absensi->first();
            $jadwal = $absensi && $absensi->t_jadwal_kerja_d_hari_n ? $absensi->t_jadwal_kerja_d_hari_n : null ;

            if ($absensi) {
                $status = $absensi->status;
                $absensiData = $absensi;
            } else {
                $status = "NOT ATTEND";
                $absensiData = ['status' => 'NOT ATTEND'];
            }

            return [
                'm_kary_id'       => $user->m_kary_id,
                'default_user_id' => $user->id,
                "presensi_absensi_id" => $absensi?->id ?? 0,
                'kode'            => $user->kode,
                'nama_lengkap'    => $user->m_kary->nama_lengkap ?? null,
                'absensi'         => $absensiData,
                'status'          => $status,
                'jam_kerja'       => $jadwal ? $jadwal->waktu_mulai . ' - ' . $jadwal->waktu_akhir : '-',
                'desc'            => $jadwal && $jadwal->m_jam_kerja ? $jadwal->m_jam_kerja->desc : '-',
            ];
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap']);
        })
        ->values();

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_get_by_date_harian($req)
    {
        $req = app()->request;
        $status_harian = m_general::where('group', 'STATUS KARYAWAN')->where('value', 'HARIAN')
        ->pluck('id');

        $kary = default_users::with(['presensi_absensi' => function($q) use ($req){
           $q->where('tanggal', $req->date); 
        }])
        ->whereHas('m_kary', function($q) use ($req, $status_harian){
            $q->where('is_active', true)
            ->whereIn('status_kary_id', $status_harian);
            if (!empty($req->m_subcomp_id)) {
                $q->where('m_subcomp_id', $req->m_subcomp_id);
            }
            if (!empty($req->m_branch_id)) {
                $q->where('m_branch_id', $req->m_branch_id);
            }
            if (!empty($req->divisi_id)) {
                $q->where('m_divisi_id', $req->divisi_id);
            }
        })
        ->get()
        ->map(function($user) use ($req) {
            $absensi = $user->presensi_absensi->first();
            $jadwal = $absensi && $absensi->t_jadwal_kerja_d_hari_n ? $absensi->t_jadwal_kerja_d_hari_n : null ;

            if ($absensi) {
                $status = $absensi->status;
                $absensiData = $absensi;
            } else {
                $status = "NOT ATTEND";
                $absensiData = ['status' => 'NOT ATTEND'];
            }

            return [
                'm_kary_id'       => $user->m_kary_id,
                'default_user_id' => $user->id,
                "presensi_absensi_id" => $absensi?->id ?? 0,
                'kode'            => $user->kode,
                'nama_lengkap'    => $user->m_kary->nama_lengkap ?? null,
                'absensi'         => $absensiData,
                'status'          => $status,
                'jam_kerja'       => $jadwal ? $jadwal->waktu_mulai . ' - ' . $jadwal->waktu_akhir : '-',
                'desc'            => $jadwal && $jadwal->m_jam_kerja ? $jadwal->m_jam_kerja->desc : '-',
            ];
        })
        ->sortBy(function ($item) {
            return strtolower($item['nama_lengkap']);
        })
        ->values();

        return $this->helper->customResponse('OK', 200, $kary);
    }

    public function custom_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails())
            return $this->helper->responseValidate($validator);

        $workHour = $this->check_presensi_by_work_hour(auth()->user()->id);
        $kary_id = default_users::find(auth()->user()->id)?->m_kary_id;


        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long, $kary_id);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = $req->catatan_in ?? null;
            }

            $data["t_jadwal_kerja_d_n_id"] = null;
            $data["t_jadwal_kerja_n_id"] = null;
            $data["t_jadwal_kerja_d_hari_n_id"] = null;

            $kary = m_kary::whereHas('default_users', function($q){
                $q->where('id', auth()->user()->id);
            })->first();
            
            $day = Carbon::now()->translatedFormat('l');
            // dd($day);

            if($kary){
                $jadwal_kary = t_jadwal_kerja_d_n::with(['t_jadwal_kerja_n.t_jadwal_kerja_d_hari_n' => function($q) use ($day) {
                    $q->where('day', $day);
                }])
                ->where('m_kary_id', $kary->id)
                ->where('start_date', '<=', $this->now)
                ->whereHas('t_jadwal_kerja_n', function($q) use ($day){
                    $q->where('status', 'AKTIF');
                })
                ->first();
                // dd($jadwal_kary->t_jadwal_kerja_n->t_jadwal_kerja_d_hari_n);

                if($jadwal_kary){
                    $data["t_jadwal_kerja_d_n_id"] = $jadwal_kary->id;
                    $data["t_jadwal_kerja_n_id"] = $jadwal_kary->t_jadwal_kerja_n->id;
                    $data["t_jadwal_kerja_d_hari_n_id"] = $jadwal_kary->t_jadwal_kerja_n->t_jadwal_kerja_d_hari_n->first()->id;
                }

                // dd($data);
            }



            if ($this->now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
                $check_exists_absen = $this->where(
                    "created_at",
                    ">=",
                    $workHour["waktu_mulai_with_date"]
                )
                    ->where("created_at", "<=", $workHour['waktu_akhir_with_date'])
                    ->where("default_user_id", auth()->user()->id ?? 0)
                    ->exists();
                if ($check_exists_absen == true) {
                    return $this->helper->customResponse(
                        "Anda sudah checkin hari ini",
                        422
                    );
                }
            } else {
                $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                    ->where("default_user_id", auth()->user()->id)
                    ->exists();
                if ($check_exists_absen == true) {
                    return $this->helper->customResponse(
                        "Anda sudah checkin hari ini",
                        422
                    );
                }
            }

            // if ($req->hasFile("foto")) {
            //     $file = $req->file("foto");
            //     $fileName =
            //         auth()->user()->username .
            //         ":::" .
            //         md5(time()) .
            //         "." .
            //         $file->getClientOriginalExtension();
            //     $file->move(public_path("uploads/presensi"), $fileName);
            // } else {
            //     trigger_error("IMAGE NOT VALID");
            // }

            if ($req->hasFile('foto')) {
                $file = $req->file('foto');
                $ext = strtolower($file->getClientOriginalExtension());

                // Nama file .webp
                $fileName = auth()->user()->username.':::'.md5(time()).'.webp';

                // Folder berdasarkan tahun/bulan/tanggal
                $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
                $destination = public_path('uploads/presensi/' . $subfolder);

                // Buat folder kalau belum ada
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                // Buat image resource tergantung tipe
                switch ($ext) {
                    case 'jpeg':
                    case 'jpg':
                        $src = imagecreatefromjpeg($file->getRealPath());
                        break;
                    case 'png':
                        $src = imagecreatefrompng($file->getRealPath());
                        imagepalettetotruecolor($src);
                        imagealphablending($src, true);
                        imagesavealpha($src, true);
                        break;
                    default:
                        trigger_error('Format tidak didukung');
                        return;
                }

                // Simpan ke WebP dengan kualitas 80
                imagewebp($src, $destination.'/'.$fileName, 80);
                imagedestroy($src);

                // Path relatif untuk disimpan di DB
                $storedPath = 'uploads/presensi/'.$subfolder.'/'.$fileName;

            } else {
                trigger_error('IMAGE NOT VALID');
                return;
            }

            // Simpan ke DB
            $new = $this->create([
                'tanggal'                   => date('Y-m-d'),
                'checkin_time'              => date('H:i:s'),
                'checkin_foto'              => $storedPath, // <- pakai path yang baru
                'checkin_lat'               => $data['checkin_lat'],
                'checkin_long'              => $data['checkin_long'],
                'checkin_address'           => $data['checkin_address'],
                'checkin_region'            => $data['region'],
                'checkin_on_scope'          => $data['on_scope'],
                'catatan_in'                => $data['catatan_in'],
                'default_user_id'           => auth()->user()->id,
                'creator_id'                => auth()->user()->id,
                't_jadwal_kerja_n_id'       => $data['t_jadwal_kerja_n_id'],
                't_jadwal_kerja_d_n_id'     => $data['t_jadwal_kerja_d_n_id'],
                't_jadwal_kerja_d_hari_n_id'=> $data['t_jadwal_kerja_d_hari_n_id'],
            ]);


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkin gagal, coba kembali nanti - " . $e->getMessage(), 400);
        }
        return $this->helper->customResponse("Checkin berhasil", 200, $data);
    }

    // public function custom_checkout($req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         "foto" => "required",
    //         "lat" => "required",
    //         "long" => "required",
    //         "address" => "required",
    //     ]);
    //     if ($validator->fails())
    //         return $this->helper->responseValidate($validator);

    //     $workHour = $this->check_presensi_by_work_hour(auth()->user()->id);
    //     $kary_id = default_users::find(auth()->user()->id)?->m_kary_id;

    //     //cek apakah dia os / bukan
    //     $status_os_ids = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();
    //     $kary = m_kary::find($kary_id);
    //     $is_outsource = in_array($kary->status_kary_id, $status_os_ids);

    //     DB::beginTransaction();
    //     try {
    //         $distance = $this->distance($req->lat, $req->long, $kary_id);
    //         if ($distance) {
    //             $data["on_scope"] = true;
    //             $data["region"] = $distance->nama;
    //             $data["checkout_lat"] = $req->lat;
    //             $data["checkout_long"] = $req->long;
    //             $data["checkout_address"] = $req->address;
    //             $data["catatan_out"] = null;
    //         } else {
    //             $data["on_scope"] = false;
    //             $data["region"] = "Out Scope";
    //             $data["checkout_lat"] = $req->lat;
    //             $data["checkout_long"] = $req->long;
    //             $data["checkout_address"] = $req->address;
    //             $data["catatan_out"] = $req->catatan_out ?? null;
    //         }

    //         if ($this->now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
    //             $check_exists_absen = $this->where(
    //                 "created_at",
    //                 ">=",
    //                 $workHour["waktu_mulai_with_date"]
    //             )
    //                 ->where("created_at", "<=", $workHour['waktu_akhir_with_date'])
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->where("status", "ATTEND")
    //                 ->exists();
    //             if ($check_exists_absen) {
    //                 return $this->helper->customResponse(
    //                     "Anda sudah checkout hari ini",
    //                     422
    //                 );
    //             }
    //         } else {
    //             $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->where("status", "ATTEND")
    //                 ->exists();
    //             if ($check_exists_absen) {
    //                 return $this->helper->customResponse(
    //                     "Anda sudah checkout hari ini",
    //                     422
    //                 );
    //             }
    //         }

    //         if ($this->now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
    //             $check_not_exists_checkin = $this->where(
    //                 "created_at",
    //                 ">=",
    //                 $workHour["waktu_mulai_with_date"]
    //             )
    //                 ->where("created_at", "<=", $workHour['waktu_akhir_with_date'])
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->whereNot("status", "WORKING")->exists();
    //             if ($check_not_exists_checkin)
    //                 return $this->helper->customResponse("Anda belum checkin hari ini", 422);

    //         } else {
    //             $check_not_exists_checkin = $this->where("tanggal", date("Y-m-d"))
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->whereNot("status", "WORKING")->exists();
    //             if ($check_not_exists_checkin)
    //                 return $this->helper->customResponse("Anda belum checkin hari ini", 422);
    //         }


    //         // if ($req->hasFile("foto")) {
    //         //     $file = $req->file("foto");
    //         //     $fileName =
    //         //         auth()->user()->username .
    //         //         ":::" .
    //         //         md5(time()) .
    //         //         "." .
    //         //         $file->getClientOriginalExtension();
    //         //     $file->move(public_path("uploads/presensi"), $fileName);
    //         // } else {
    //         //     trigger_error("IMAGE NOT VALID");
    //         // }
    //         if ($req->hasFile('foto')) {
    //             $file = $req->file('foto');
    //             $ext = strtolower($file->getClientOriginalExtension());

    //             $fileName = auth()->user()->username.':::'.md5(time()).'.webp';

    //             $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
    //             $destination = public_path('uploads/presensi/' . $subfolder);

    //             if (!file_exists($destination)) {
    //                 mkdir($destination, 0755, true);
    //             }

    //             switch ($ext) {
    //                 case 'jpeg':
    //                 case 'jpg':
    //                     $src = imagecreatefromjpeg($file->getRealPath());
    //                     break;
    //                 case 'png':
    //                     $src = imagecreatefrompng($file->getRealPath());
    //                     imagepalettetotruecolor($src);
    //                     imagealphablending($src, true);
    //                     imagesavealpha($src, true);
    //                     break;
    //                 default:
    //                     trigger_error('Format tidak didukung');
    //                     return;
    //             }

    //             imagewebp($src, $destination.'/'.$fileName, 80);
    //             imagedestroy($src);

    //             $storedPath = 'uploads/presensi/'.$subfolder.'/'.$fileName;

    //         } else {
    //             trigger_error('IMAGE NOT VALID');
    //             return;
    //         }

           

    //         if ($this->now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
    //             $parseWorkHour = \Carbon::parse($workHour['waktu_mulai_with_date']);
    //             $dateWorkHour = $parseWorkHour->copy()->toDateString();
    //             $timeWorkHour = $parseWorkHour->copy()->subHours(2)->toTimeString();
    //             $getJamKerja = $this->get_jam_kerja(auth()->user()->id, $dateWorkHour, $timeWorkHour, date('H:i:s'));

    //             $data = $this->where(
    //                 "created_at",
    //                 ">=",
    //                 $workHour["waktu_mulai_with_date"]
    //             )
    //                 ->where("created_at", "<=", $workHour['waktu_akhir_with_date'])
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->where("status", "WORKING")
    //                 ->first();

    //             $jam_kerja_diakui = ($is_outsource && $data->checkin_time) 
    //                 ? min(round(Carbon::now()->diffInMinutes(Carbon::parse($data->checkin_time)) / 60, 2), 7) 
    //                 : null;

    //             $data->update([
    //                     "checkout_time" => date("H:i:s"),
    //                     "checkout_foto" => $storedPath,
    //                     "checkout_lat" => $data["checkout_lat"],
    //                     "checkout_long" => $data["checkout_long"],
    //                     "checkout_address" => $data["checkout_address"],
    //                     "checkout_region" => $data["region"],
    //                     "checkout_on_scope" => $data["on_scope"],
    //                     "catatan_out" => $data["catatan_out"],
    //                     "m_jam_kerja_id" => $getJamKerja['m_jam_kerja_id'] ?? null,
    //                     "jadwal_kerja_id" => $getJamKerja['t_jadwal_kerja_id'] ?? null,
    //                     "t_jadwal_kerja_det_hari_id" => $getJamKerja['t_jadwal_kerja_det_hari_id'] ?? null,
    //                     "shift" => $getJamKerja['nama_jam_kerja'] ?? null,
    //                     "status" => "ATTEND",
    //                     "jam_kerja_diakui" => $jam_kerja_diakui,
    //                 ]);

    //         } else {
    //             $getCheckin = $this->where("tanggal", date("Y-m-d"))
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->where("status", "WORKING")
    //                 ->first();
    //             $getJamKerja = $this->get_jam_kerja(auth()->user()->id, $getCheckin['tanggal'], $getCheckin['checkin_time'], date("H:i:s"));

    //             $data = $this->where("tanggal", date("Y-m-d"))
    //                 ->where("default_user_id", auth()->user()->id)
    //                 ->where("status", "WORKING")
    //                 ->first();

    //              $jam_kerja_diakui = ($is_outsource && $data->checkin_time) 
    //                 ? min(round(Carbon::now()->diffInMinutes(Carbon::parse($data->checkin_time)) / 60, 2), 7) 
    //                 : null;

    //             $data->update([
    //                     "checkout_time" => date("H:i:s"),
    //                     "checkout_foto" => $storedPath,
    //                     "checkout_lat" => $data["checkout_lat"],
    //                     "checkout_long" => $data["checkout_long"],
    //                     "checkout_address" => $data["checkout_address"],
    //                     "checkout_region" => $data["region"],
    //                     "checkout_on_scope" => $data["on_scope"],
    //                     "catatan_out" => $data["catatan_out"],
    //                     "m_jam_kerja_id" => $getJamKerja['m_jam_kerja_id'] ?? null,
    //                     "jadwal_kerja_id" => $getJamKerja['t_jadwal_kerja_id'] ?? null,
    //                     "t_jadwal_kerja_det_hari_id" => $getJamKerja['t_jadwal_kerja_det_hari_id'] ?? null,
    //                     "shift" => $getJamKerja['nama_jam_kerja'] ?? null,
    //                     "status" => "ATTEND",
    //                     "jam_kerja_diakui" => $jam_kerja_diakui,
    //                 ]);
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return $this->helper->customResponse("Checkout gagal, coba kembali nanti - ", 422);
    //     }
    //     return $this->helper->customResponse("Checkout berhasil", 200, $data);
    // }

    public function custom_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) {
            return $this->helper->responseValidate($validator);
        }

        $userId = auth()->user()->id;
        $kary_id = default_users::find($userId)?->m_kary_id;

        // Cek apakah dia OS / bukan
        $status_os_ids = m_general::where('group', "STATUS KARYAWAN OUTSOURCE")->pluck('id')->toArray();
        $kary = m_kary::find($kary_id);
        $is_outsource = $kary ? in_array($kary->status_kary_id, $status_os_ids) : false;

        $now = \Carbon\Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay();
        $yesterdayStr = $yesterday->format('Y-m-d');

        // ====================================================================
        // 1. CARI DATA PRESENSI AKTIF (Cek Hari Ini -> Lalu Cek Kemarin)
        // ====================================================================
        $presensiAktif = $this->where('default_user_id', $userId)
            ->where('tanggal', $todayStr)
            ->first();

        // Jika hari ini belum checkin atau statusnya sudah ATTEND, kita cek data kemarin
        if (!$presensiAktif || $presensiAktif->status == 'ATTEND') {
            $presensiKemarin = $this->where('default_user_id', $userId)
                ->where('tanggal', $yesterdayStr)
                ->first();

            // Jika ada data kemarin yang belum checkout (masih WORKING)
            if ($presensiKemarin && $presensiKemarin->status != 'ATTEND' && $presensiKemarin->checkin_time) {
                
                // Ambil jadwal kerja untuk HARI KEMARIN
                $jadwal = t_jadwal_kerja_d_n::where('m_kary_id', $kary_id)
                    ->whereDate('start_date', '<=', $yesterdayStr)
                    ->orderByDesc('start_date')
                    ->first();

                $isValidForYesterday = false;

                if ($jadwal) {
                    $hariKemarin = $yesterday->translatedFormat('l'); 
                    $jam_kerja_row = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
                        ->where('day', $hariKemarin)
                        ->first();

                    if ($jam_kerja_row) {
                        $waktuMulai = $jam_kerja_row->waktu_mulai;
                        $waktuAkhir = $jam_kerja_row->waktu_akhir;
                        $waktuAkhirShift = \Carbon\Carbon::parse($yesterdayStr . ' ' . $waktuAkhir);

                        // Logika Lintas Malam
                        if ($waktuAkhir < $waktuMulai) {
                            $waktuAkhirShift->addDay(); 
                        }

                        // Batas Toleransi Checkout (4 jam setelah jam akhir shift)
                        $batasCheckout = $waktuAkhirShift->copy()->addHours(4);

                        if ($now->lessThanOrEqualTo($batasCheckout)) {
                            $isValidForYesterday = true;
                        }
                    }
                }

                if ($isValidForYesterday) {
                    $presensiAktif = $presensiKemarin; // Gunakan presensi kemarin untuk di-checkout
                } else if (!$presensiAktif) {
                    return $this->helper->customResponse("Waktu checkout untuk shift kemarin sudah lewat. Anda juga belum checkin hari ini.", 422);
                }
            }
        }

        // ====================================================================
        // 2. VALIDASI STATUS AKHIR
        // ====================================================================
        if (!$presensiAktif) {
            return $this->helper->customResponse("Anda belum checkin hari ini", 422);
        }

        if ($presensiAktif->status == 'ATTEND') {
            return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
        }

        // ====================================================================
        // 3. PROSES UPLOAD GAMBAR
        // ====================================================================
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = auth()->user()->username.':::'.md5(time()).'.webp';

            $subfolder = date('Y') . '/' . date('m') . '/' . date('d');
            $destination = public_path('uploads/presensi/' . $subfolder);

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    $src = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'png':
                    $src = imagecreatefrompng($file->getRealPath());
                    imagepalettetotruecolor($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    break;
                default:
                    return $this->helper->customResponse("Format gambar tidak didukung", 422);
            }

            imagewebp($src, $destination.'/'.$fileName, 80);
            imagedestroy($src);

            $storedPath = 'uploads/presensi/'.$subfolder.'/'.$fileName;
        } else {
            return $this->helper->customResponse("IMAGE NOT VALID", 422);
        }

        // ====================================================================
        // 4. PROSES DATABASE CHECKOUT (Transaksi)
        // ====================================================================
        DB::beginTransaction();
        try {
            // Jarak Scope
            $distance = $this->distance($req->lat, $req->long, $kary_id);
            $onScope = $distance ? true : false;
            $regionName = $distance ? $distance->nama : "Out Scope";
            $catatanOut = $distance ? null : ($req->catatan_out ?? null);

            // Perhitungan Jam Kerja & Shift
            $getJamKerja = $this->get_jam_kerja($userId, $presensiAktif->tanggal, $presensiAktif->checkin_time, date('H:i:s'));

            // Jam Kerja Diakui (Outsource)
            $jam_kerja_diakui = null;
            if ($is_outsource && $presensiAktif->checkin_time) {
                // Perbaikan: Gabungkan dengan tanggal agar kalkulasi beda hari tidak minus/error
                $checkinCarbon = \Carbon\Carbon::parse($presensiAktif->tanggal . ' ' . $presensiAktif->checkin_time);
                $jam_kerja_diakui = min(round($now->diffInMinutes($checkinCarbon) / 60, 2), 7);
            }

            // Eksekusi Update ke Database
            $presensiAktif->update([
                "checkout_time" => date("H:i:s"),
                "checkout_foto" => $storedPath,
                "checkout_lat" => $req->lat,
                "checkout_long" => $req->long,
                "checkout_address" => $req->address,
                "checkout_region" => $regionName,
                "checkout_on_scope" => $onScope,
                "catatan_out" => $catatanOut,
                "m_jam_kerja_id" => $getJamKerja['m_jam_kerja_id'] ?? null,
                "jadwal_kerja_id" => $getJamKerja['t_jadwal_kerja_id'] ?? null,
                "t_jadwal_kerja_det_hari_id" => $getJamKerja['t_jadwal_kerja_det_hari_id'] ?? null,
                "shift" => $getJamKerja['nama_jam_kerja'] ?? null,
                "status" => "ATTEND",
                "jam_kerja_diakui" => $jam_kerja_diakui,
            ]);

            DB::commit();
            return $this->helper->customResponse("Checkout berhasil", 200, $presensiAktif);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkout gagal, coba kembali nanti - " . $e->getMessage(), 500);
        }
    }


    private function distance($lat, $long, $kary_id)
    {
        // dd($lat, $long, $kary_id);
        $distance = DB::select("select distance_location(?,?,?)", [$lat, $long, $kary_id]);
        if (count($distance)) {
            $location = json_decode($distance[0]->distance_location);
            return @$location[0] ?? false;
        } else {
            return false;
        }
    }

    public function custom_distance_check($req)
    {
        $kary_id = default_users::find(auth()->user()->id)?->m_kary_id ?? 0;
        //dd(default_users::find(auth()->user()->id), $kary_id);
        $distance = $this->distance($req->lat, $req->long, $kary_id);
        if ($distance) {
            $data["on_scope"] = true;
            $data["region"] = $distance->nama;
            $data["checkout_lat"] = $distance->lat;
            $data["checkout_long"] = $distance->long;
            $data["checkout_address"] = $req->address;
        } else {
            $data["on_scope"] = false;
            $data["region"] = "Out Scope";
            $data["checkout_lat"] = $req->lat;
            $data["checkout_long"] = $req->long;
            $data["checkout_address"] = $req->address;
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    // public function custom_status($model)
    // {
    //     // $now = app()->request->now ? Carbon::parse(app()->request->now) : $this->now;
    //     $now = $this->now;
    //     $workHour = $this->check_presensi_by_work_hour(auth()->user()->id);
    //     // dd($workHour);

    //     if ($now->between($workHour['waktu_mulai_with_date'], $workHour['waktu_akhir_with_date']) && $workHour['yesterday']) {
    //         $data = [
    //             'status' => $this->where(
    //                 "created_at",
    //                 ">=",
    //                 $workHour["waktu_mulai_with_date"]
    //             )->where("created_at", "<=", $workHour['waktu_akhir_with_date'])->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
    //         ];
    //     } else {
    //         $data = [
    //             'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
    //         ];
    //     }
    //     return $this->helper->customResponse("OK", 200, $data);
    // }

    public function custom_status($model)
    {
        $userId = auth()->user()->id ?? 0;
        
        $now = \Carbon\Carbon::now();
        // $now = Carbon::parse("2026-05-13 07:00:00");
        $todayStr = $now->format('Y-m-d');
        
        // Ambil objek hari kemarin
        $yesterday = $now->copy()->subDay();
        $yesterdayStr = $yesterday->format('Y-m-d');

        // 1. Cek Presensi Hari Ini Terlebih Dahulu
        $presensiHariIni = $this->where("tanggal", $todayStr)
            ->where("default_user_id", $userId)
            ->first();

        if ($presensiHariIni) {
            return $this->helper->customResponse("OK", 200, [
                "status" => $presensiHariIni->status ?? "NOT ATTEND"
            ]);
        }

        // 2. Jika tidak ada presensi hari ini, cek kemarin
        $presensiKemarin = $this->where("tanggal", $yesterdayStr)
            ->where("default_user_id", $userId)
            ->first();

        // Siapkan default data
        $data = [ "status" => 'NOT ATTEND' ];

        if ($presensiKemarin) {
            $status = $presensiKemarin->status ?? null;
            $checkin = $presensiKemarin->checkin_time ?? null;

            // Jika statusnya belum ATTEND (artinya dia sudah masuk tapi belum absen pulang)
            if ($status != 'ATTEND' && $checkin) {
                
                $m_kary = m_kary::whereHas('default_users', function($q) use ($userId) {
                    $q->where('id', $userId);
                })->first();

                // Cari jadwal kerja yang aktif sampai HARI KEMARIN
                $jadwal = t_jadwal_kerja_d_n::where('m_kary_id', $m_kary?->id)
                    ->whereDate('start_date', '<=', $yesterdayStr)
                    ->orderByDesc('start_date')
                    ->first();

                if ($jadwal) {
                    // PENTING: Ambil nama hari KEMARIN
                    // (Ganti translatedFormat('l') ke format('l') jika database Anda menyimpan 'Monday', bukan 'Senin')
                    $hariKemarin = $yesterday->translatedFormat('l'); 
                    
                    $jam_kerja_row = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
                        ->where('day', $hariKemarin)
                        ->first();

                    // dd($jam_kerja_row);

                    if ($jam_kerja_row) {
                        $waktuMulai = $jam_kerja_row->waktu_mulai; // cnth: "22:00:00"
                        $waktuAkhir = $jam_kerja_row->waktu_akhir; // cnth: "06:00:00"
                        // dd($waktuAkhir);

                        // Jadikan string jam akhir ke object Carbon sesuai TANGGAL KEMARIN
                        $waktuAkhirShift = \Carbon\Carbon::parse($yesterdayStr . ' ' . $waktuAkhir);
                        // dd($waktuAkhirShift);

                        // --- LOGIKA SHIFT MALAM LINTAS HARI ---
                        // Jika jam akhir (06:00) < jam mulai (22:00), berarti shift selesai di HARI BERIKUTNYA
                        if ($waktuAkhir < $waktuMulai) {
                            $waktuAkhirShift->addDay();
                        }

                        // --- TOLERANSI TELAT ABSEN PULANG ---
                        // Tambahkan batas maksimal karyawan boleh absen pulang dari jadwal akhirnya (Misal 4 jam)
                        // Tanpa ini, jika dia pulang jam 06:05, statusnya langsung jadi NOT ATTEND dan tidak bisa checkout
                        $batasCheckout = $waktuAkhirShift->copy()->addHours(4);
                        // dd($now);
                        // Cek apakah waktu saat ini sudah sangat telat / melewati batas akhir checkout shift kemarin
                        if ($now->greaterThan($batasCheckout)) {
                            $data = [ "status" => 'NOT ATTEND' ];
                        } else {
                            // Masih masuk dalam rentang waktu wajar shift / belum habis waktunya
                            $data = [ "status" => $status ];
                        }
                    }
                }
            }
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function scopeFilter($model)
    {
        if (req('date_from') && req('date_to')) {
            return $model->whereBetween('tanggal', [req('date_from'), req('date_to')])->where('default_user_id', auth()->user()->id ?? 0);
        }
    }

    public function custom_get_absen_old($req)
    {
        $periode = ($req->periode ?? date('Y-m')) . '-1';
        $m_kary_id = auth()->user()->m_kary_id;

        $data = \DB::select("
            select * from employee_attendance_detail(?, ?);
        ", [$periode, $m_kary_id ?? 0]);

        // transform object for mobile
        foreach ($data as $dt) {
            $dt->status = @json_decode($dt->absensi)->status ?? null;
            $dt->tanggal = @json_decode($dt->absensi)->tanggal ?? null;
            $dt->catatan_in = @json_decode($dt->absensi)->catatan_in ?? null;
            $dt->catatan_out = @json_decode($dt->absensi)->catatan_out ?? null;
            $dt->checkin_lat = @json_decode($dt->absensi)->checkin_lat ?? null;
            $dt->checkin_foto = @json_decode($dt->absensi)->checkin_foto ? url('') . '/' . @json_decode($dt->absensi)->checkin_foto : null;
            $dt->checkin_long = @json_decode($dt->absensi)->checkin_long ?? null;
            $dt->checkin_time = @json_decode($dt->absensi)->checkin_time ?? null;
            $dt->checkout_lat = @json_decode($dt->absensi)->checkout_lat ?? null;
            $dt->checkout_foto = @json_decode($dt->absensi)->checkout_foto ? url('') . '/' . @json_decode($dt->absensi)->checkout_foto : null;
            $dt->checkout_long = @json_decode($dt->absensi)->checkout_long ?? null;
            $dt->checkout_time = @json_decode($dt->absensi)->checkout_time ?? null;
            $dt->checkin_region = @json_decode($dt->absensi)->checkin_region ?? null;
            $dt->checkin_address = @json_decode($dt->absensi)->checkin_address ?? null;
            $dt->checkout_region = @json_decode($dt->absensi)->checkout_region ?? null;
            $dt->checkin_on_scope = @json_decode($dt->absensi)->checkin_on_scope ?? null;
            $dt->checkout_address = @json_decode($dt->absensi)->checkout_address ?? null;
            $dt->checkout_on_scope = @json_decode($dt->absensi)->checkout_on_scope ?? null;
            $dt->presensi_absensi_id = @json_decode($dt->absensi)->presensi_absensi_id ?? null;
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_get_absen($req)
    {
        $reqPeriode = $req->periode ?? date('Y-m');
        $periodeDate = Carbon::parse($reqPeriode . '-01');
        $m_kary_id = auth()->user()->m_kary_id;

        $kary = DB::table('m_kary')->where('id', $m_kary_id)->first();
        $karyJson = json_encode([
            'nik' => $kary->nik ?? '',
            'm_kary_id' => $m_kary_id,
            'nama_lengkap' => $kary->nama_lengkap ?? ''
        ]);

        $startDate = $periodeDate->copy()->startOfMonth();
        $endDate = $periodeDate->copy()->endOfMonth();

        $absensis = DB::table('presensi_absensi')
            ->where('default_user_id', auth()->user()->id)
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy('tanggal');

        $cutis = DB::table('t_cuti')
            ->where('m_kary_id', $m_kary_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_from', [$startDate, $endDate])
                    ->orWhereBetween('date_to', [$startDate, $endDate]);
            })
            ->get();

        $liburs = DB::table('m_libur_nasional')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy('tanggal');

        $jadwals = DB::table('t_jadwal_kerja_d_n')
            ->where('m_kary_id', $m_kary_id)
            ->whereDate('start_date', '<=', $endDate->format('Y-m-d'))
            ->orderByDesc('start_date')
            ->get();

        $jadwalIds = $jadwals->pluck('t_jadwal_kerja_n_id')->unique();
        $jadwalHaris = DB::table('t_jadwal_kerja_d_hari_n')
            ->whereIn('t_jadwal_kerja_n_id', $jadwalIds)
            ->get()
            ->groupBy('t_jadwal_kerja_n_id'); 
        
        // dd($jadwals);

        $daysInMonth = $periodeDate->daysInMonth;
        $collection = collect();

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = $periodeDate->copy()->day($i);
            $dateStr = $currentDate->format('Y-m-d');
            $hariIndo = $currentDate->translatedFormat('l');

            $type = 'Hari Kerja';
            $status = 'NOT ATTEND';

            $activeJadwal = $jadwals->firstWhere('start_date', '<=', $dateStr);
            $jamKerjaRow = null;
            $isHariLibur = false;

            if ($activeJadwal) {
                $daysSetup = $jadwalHaris->get($activeJadwal->t_jadwal_kerja_n_id);
                if ($daysSetup) {
                    $jamKerjaRow = $daysSetup->firstWhere('day', $hariIndo);
                }
            }

            // dd($jamKerjaRow);
            if ($jamKerjaRow && $jamKerjaRow->tipe_hari == 'LIBUR') {
                $isHariLibur = true;
                $type = 'Hari Libur';
                $status = 'LIBUR';
            }

            // dd($cutis);
            foreach ($cutis as $c) {
                if ($dateStr >= $c->date_from && $dateStr <= $c->date_to) {
                    $type = 'Cuti';
                    $status = 'CUTI';
                    break;
                }
            }

            if (isset($liburs[$dateStr])) {
                $type = 'Libur Nasional';
                $status = 'LIBUR';
            }

            $absen = $absensis[$dateStr] ?? null;
            if ($absen) {
                $status = $absen->status ?? 'ATTEND'; 
            }

            $item = [
                "kary" => $karyJson,
                "all_days_of_month" => $dateStr,
                "date_to_idn" => $currentDate->format('d-m-Y'),
                "day_name_idn" => $hariIndo,
                "type" => $type,
                "absensi" => json_encode(["status" => $status]),
                "status" => $status,
                "tanggal" => $absen ? $absen->tanggal : null,
                "catatan_in" => $absen->catatan_in ?? null,
                "catatan_out" => $absen->catatan_out ?? null,
                "checkin_lat" => $absen->checkin_lat ?? null,
                "checkin_foto" => $absen->checkin_foto ?? null,
                "checkin_long" => $absen->checkin_long ?? null,
                "checkin_time" => $absen->checkin_time ?? null,
                "checkout_lat" => $absen->checkout_lat ?? null,
                "checkout_foto" => $absen->checkout_foto ?? null,
                "checkout_long" => $absen->checkout_long ?? null,
                "checkout_time" => $absen->checkout_time ?? null,
                "checkin_region" => $absen->checkin_region ?? null,
                "checkin_address" => $absen->checkin_address ?? null,
                "checkout_region" => $absen->checkout_region ?? null,
                "checkin_on_scope" => $absen->checkin_on_scope ?? null,
                "checkout_address" => $absen->checkout_address ?? null,
                "checkout_on_scope" => $absen->checkout_on_scope ?? null,
                "presensi_absensi_id" => $absen->id ?? null,
            ];

            $collection->push($item);
        }

        return $this->helper->customResponse("OK", 200, $collection);
    }

    public function custom_get_absen_new($req)
    {
        $periode = ($req->periode ?? date('Y-m')) . '-1';
        $m_kary_id = auth()->user()->m_kary_id;

        //data presensi ada di tabel presensi_absensi
        //perhatikan di t_cuti untuk tampilkan status cuti
        //perhatikan juga di tabel t_jadwal_kerja_d_n & t_jadwal_kerja_d_hari_n untuk ambil jadwalnya yg berlaku pada hari itu
        //perhatikan di m_libur_nasional untuk status hari itu juga
        //main problem dari function lama adalah status/tipe hari yang belum sesuai per harinya (libur/cuti/tidak hadir)

        //response simpan dalam 1 collection, gunakan $this->helper->customResponse() unutk return agar formatnya sesuai standar
        //contoh parameter bisa dilihat di function lama di atas
        //expected response format:
        // {
        //     "timestamp": "26-05-2026 09:49:23",
        //     "code": 200,
        //     "message": "OK",
        //     "data": [
        //         {
        //             "kary": "{\"nik\": \"EMP99900001\", \"m_kary_id\": 1, \"nama_lengkap\": \"Trial 1 (Akun Trial Superadmin)\"}",
        //             "all_days_of_month": "2026-05-01",
        //             "date_to_idn": "01-05-2026",
        //             "day_name_idn": "Jumat",
        //             "type": "Hari Kerja",
        //             "absensi": "{\"status\": \"NOT ATTEND\"}",
        //             "status": "NOT ATTEND",
        //             "tanggal": null,
        //             "catatan_in": null,
        //             "catatan_out": null,
        //             "checkin_lat": null,
        //             "checkin_foto": null,
        //             "checkin_long": null,
        //             "checkin_time": null,
        //             "checkout_lat": null,
        //             "checkout_foto": null,
        //             "checkout_long": null,
        //             "checkout_time": null,
        //             "checkin_region": null,
        //             "checkin_address": null,
        //             "checkout_region": null,
        //             "checkin_on_scope": null,
        //             "checkout_address": null,
        //             "checkout_on_scope": null,
        //             "presensi_absensi_id": null
        //         }
        //     ]   
        // }

    }


    private function get_jam_kerja($userId, $date, $checkin, $checkout)
    {
        // $userId = request('user_id'); // untuk form-data, x-www-form-urlencoded, atau JSON
        // $date = request('date'); // untuk form-data, x-www-form-urlencoded, atau JSON
        // $checkin = request('checkin'); // untuk form-data, x-www-form-urlencoded, atau JSON
        // $checkout = request('checkout'); // untuk form-data
        $karyId = default_users::where('id', $userId)->first()->m_kary_id ?? 0;
        $kary = m_kary::where("id", $karyId)->first();
        if (!$kary) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        $matched_id = null;
        $toleranceMinutes = 30;

        $checkinCarbon = \Carbon::parse($date . '' . $checkin);
        $checkoutCarbon = \Carbon::parse($date . '' . $checkout);
        if ($checkoutCarbon < $checkinCarbon) {
            $checkoutCarbon->addDay();
        }

        $jam_kerja_ids = json_decode($kary['m_jam_kerja_id'], true);
        if ($jam_kerja_ids === null || !is_array($jam_kerja_ids)) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        foreach ($jam_kerja_ids as $jam_kerja_id) {
            $jamKerja = m_jam_kerja::find($jam_kerja_id);
            if (!$jamKerja)
                continue;

            $startWork = \Carbon::parse($date . ' ' . $jamKerja->waktu_mulai);
            $endWork = \Carbon::parse($date . ' ' . $jamKerja->waktu_akhir);

            if ($jamKerja->is_hari_berikutnya) {
                $endWork = $endWork->addDay();
            }

            $isCheckinNearStart = $checkinCarbon->between(
                $startWork->copy()->subMinutes($toleranceMinutes),
                $startWork->copy()->addMinutes($toleranceMinutes)
            );

            $isCheckoutNearEnd = $checkoutCarbon->between(
                $endWork->copy()->subMinutes($toleranceMinutes),
                $endWork->copy()->addMinutes($toleranceMinutes)
            );

            $isCheckinInRange = $checkinCarbon->between($startWork, $endWork);
            $isCheckoutInRange = $checkoutCarbon->between($startWork, $endWork);

            if ($isCheckinNearStart && $isCheckoutNearEnd) {
                $matched_id = $jamKerja;
                break;
            } elseif ($isCheckinInRange && $isCheckoutInRange) {
                $matched_id = $jamKerja;
                break;
            }
        }
        if (!$matched_id) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }

        $jadwalKerja = $this->get_jadwal_kerja($matched_id, $date);

        return $matched_id = [
            't_jadwal_kerja_id' => $jadwalKerja['t_jadwal_kerja_id'] ?? null,
            't_jadwal_kerja_det_hari_id' => $jadwalKerja['t_jadwal_kerja_det_hari_id'] ?? null,
            'm_jam_kerja_id' => $matched_id->id,
            'nama_jam_kerja' => $matched_id->desc,
        ];
    }

    private function get_jadwal_kerja($matched_id, $date)
    {
        $jadwalKerja = t_jadwal_kerja::with([
            't_jadwal_kerja_det_hari' => function ($q) use ($matched_id, $date) {
                $q->where('m_jam_kerja_id', $matched_id->id)->where('day_num', \Carbon::parse($date)->dayOfWeek + 1)
                    ->with([
                        't_jadwal_kerja_det' => function ($q2) {
                            // $q2->where('m_kary_id', $karyId);
                        }
                    ]);
            }
        ])
            // ->where('status', 'POSTED')
            ->whereHas('t_jadwal_kerja_det_hari', function ($q) use ($matched_id) {
                $q->where('m_jam_kerja_id', $matched_id->id);
            })
            ->first();
        return [
            't_jadwal_kerja_id' => $jadwalKerja->id ?? null,
            't_jadwal_kerja_det_hari_id' => $jadwalKerja->t_jadwal_kerja_det_hari[0]->id ?? null,
        ];
    }

    private function check_presensi_by_work_hour($id)
    {
        // $id = request('user_id'); // untuk form-data, x-www-form-urlencoded, atau JSON        
        // // $id = $req;
        // // $id = $req->user_id;

        //kalau hari sebelumnya shift malam (ganti hari) dan hari ini jam kerja normal, statusnya mendahulukan yang kemarin dulu kalau memang belum checkout

        $getKaryID = default_users::where('id', $id)->first();
        $karyID = $getKaryID->m_kary_id ?? 0;

        // $day = Carbon::parse('09-09-2025 05:51:17')->translatedFormat('l') ?? Carbon::now()->translatedFormat('l');
        // $yesterday = Carbon::parse('09-09-2025 05:51:17')->subDay()->translatedFormat('l') ?? Carbon::now()->subDay()->translatedFormat('l');
        $day = Carbon::now()->translatedFormat('l');
        $yesterday = Carbon::now()->subDay()->translatedFormat('l');

        //getworkhour
        // $getData = m_kary::where('m_kary.id', $karyID)
        //     ->select('m_kary.m_jam_kerja_id')->first();

        $jadwal_kary = t_jadwal_kerja_d_n::with(['t_jadwal_kerja_n.t_jadwal_kerja_d_hari_n' => function($q) use ($day) {
                    $q->where('day', $day);
                }])
                ->where('m_kary_id', $karyID)
                ->where('start_date', '<=', $this->now)
                ->whereHas('t_jadwal_kerja_n', function($q) use ($day){
                    $q->where('status', 'AKTIF');
                })
                ->first();
                
                $parseWorkHour = [];
                if(!$jadwal_kary){
                    return [
                        'yesterday' => false,
                        'waktu_mulai_with_date' => null,
                        'waktu_akhir_with_date' => null,
                    ];
                }
                $data = $jadwal_kary->t_jadwal_kerja_n->t_jadwal_kerja_d_hari_n->first();
                $checkinTime = Carbon::parse($data->waktu_mulai);
                $checkoutTime = Carbon::parse($data->waktu_akhir);

                // $parseWorkHour[$data->day] = [$data->waktu_mulai, $data->waktu_akhir];
                // dd($parseWorkHour);

        $jadwal_kary_yesterday = t_jadwal_kerja_d_n::with(['t_jadwal_kerja_n.t_jadwal_kerja_d_hari_n' => function($q) use ($yesterday) {
                    $q->where('day', $yesterday);
                }])
                ->where('m_kary_id', $karyID)
                ->where('start_date', '<=', $this->now)
                ->whereHas('t_jadwal_kerja_n', function($q){
                    $q->where('status', 'AKTIF');
                })
                ->first();

                $data_yesterday = $jadwal_kary_yesterday->t_jadwal_kerja_n->t_jadwal_kerja_d_hari_n->first();
                $parseWorkHourYesterday[$data_yesterday->day] = [$data_yesterday->waktu_mulai, $data_yesterday->waktu_akhir];
        // if (!$getData) {
        //     return [
        //         'yesterday' => false,
        //         'waktu_mulai_with_date' => null,
        //         'waktu_akhir_with_date' => null,
        //     ];
        // }
        // $jamKerjaRaw = $getData->m_jam_kerja_id ?? [];
        // if (!is_array($jamKerjaRaw) || empty($jamKerjaRaw)) {
        //     $jamKerjaRaw = [];
        // }
        // $getWorkHours = m_jam_kerja::whereIn('id', $jamKerjaRaw)->get();
        // $parseWorkHour = [];
        // foreach ($getWorkHours as $workHour) {
        //     $parseWorkHour[$workHour['desc']] = [$workHour['waktu_mulai'], $workHour['waktu_akhir']];
        // }

        //check yesterday
        // dd($parseWorkHourYesterday);
        $checkYesterdayPresence = $this->checkYesterdayPresence($parseWorkHourYesterday, $id);
        // dd($checkYesterdayPresence);

        if ($checkYesterdayPresence) {
            // $firstWorkHour = $getWorkHours->first();
            $firstWorkHour = $data_yesterday;
            $parseStart = \Carbon::parse($checkYesterdayPresence['tanggal'] . $firstWorkHour['waktu_mulai'])->subHours(2);
            $parseEnd = \Carbon::parse($checkYesterdayPresence['tanggal'] . $firstWorkHour['waktu_akhir'])->addHours(2);
            if ($parseStart > $parseEnd) {
                $parseEnd->addDay();
            }

            return [
                'yesterday' => true,
                'waktu_mulai_with_date' => $parseStart->toDateTimeString(),
                'waktu_akhir_with_date' => $parseEnd->toDateTimeString(),
            ];
        } else {

            //kalau kemarin masuk tapi hari ini jam kerja sesuai
            if($this->now >= $checkinTime->subHour() && $this->now <= $checkoutTime->addHour() ){
                 return [
                    'yesterday' => false,
                    'waktu_mulai_with_date' => null,
                    'waktu_akhir_with_date' => null,
                ];
            }

            return [
                'yesterday' => false,
                'waktu_mulai_with_date' => null,
                'waktu_akhir_with_date' => null,
            ];
        }
    }

    private function checkYesterdayPresence($shift, $id)
    {
        $checkPresence = null;
        $now = \Carbon::now();
        // $now = Carbon::parse('09-09-2025 05:51:17') ?? \Carbon::now();
        $yesterday = $now
            ->copy()
            ->subDay()
            ->toDateString();
        // dd($shift, $yesterday, $now);
        foreach ($shift as $index => $single) {
            // dd($single[0], $single[1]);
            $shiftParse = [];
            if ($single[0] > $single[1]) {
                $shiftParse[] = \Carbon::parse(
                    $yesterday . " " . $single[0]
                )->toDateTimeString();
                $shiftParse[] = \Carbon::parse(
                    $now->copy()->toDateString() . "" . $single[1]
                )->toDateTimeString();
            } else {
                foreach ($single as $time) {
                    $shiftParse[] = \Carbon::parse(
                        $yesterday . " " . $time
                    )->toDateTimeString();
                }
            }

            // dd($shiftParse);

            // $checkPresence =
            //     $this->whereBetween("created_at", $shiftParse)
            //         ->where("default_user_id", $id ?? 0)
            //         ->orderBy("tanggal", "desc")
            //         ->whereIn("status", ["WORKING", 'BREAK', 'RESUME WORKING', "ATTEND"])
            //         ->first() ?? null;
            $checkPresence = $this->select("*")
            ->where("default_user_id", $id ?? 0)
            ->whereRaw("make_timestamp(EXTRACT(YEAR FROM tanggal)::int, EXTRACT(MONTH FROM tanggal)::int, EXTRACT(DAY FROM tanggal)::int, EXTRACT(HOUR FROM checkin_time)::int, EXTRACT(MINUTE FROM checkin_time)::int, 0) between ? and ?", $shiftParse)
            ->orderBy("tanggal", "desc")
            ->whereIn("status", ["WORKING", "BREAK", "RESUME WORKING", "ATTEND"])
            ->first();

            // dd($checkPresence);
            if ($checkPresence) {
                // dd($checkPresence);
                return $checkPresence;
            }
        }
        if (!$checkPresence) {
            return null;
        }
    }

    public function custom_test_jam_kerja($req)
    {

        $result = $this->get_jadwal_kerja_n(
            $req->user_id,
            $req->date,
            $req->checkin,
            $req->checkout
        );
        return response()->json($result);
    }

    private function get_jadwal_kerja_n($userId, $date, $checkin, $checkout)
    {
        // return $date;
        $dayNum = \Carbon::parse($date)->dayOfWeekIso; // 1 (Monday) - 7 (Sunday)
        $karyId = default_users::where('id', $userId)->first()->m_kary_id ?? 0;
        $getJadwalKerjaDet = t_jadwal_kerja_d_n::where('m_kary_id', $karyId)->orderBy('start_date', 'desc')->first();
        $getJadwalKerja = t_jadwal_kerja_n::where('id', $getJadwalKerjaDet->t_jadwal_kerja_n_id ?? 0)->with('t_jadwal_kerja_d_hari_n')->first();
        if (!$getJadwalKerja) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        return $this->get_jam_kerja_n($getJadwalKerja, $dayNum,$checkin,$checkout);
    }

    private function get_jam_kerja_n($getJadwalKerja, $dayNum, $checkin, $checkout)
    {
        $collectJamKerja = collect($getJadwalKerja->t_jadwal_kerja_d_hari_n);
        $filtered = $collectJamKerja
            ->where('day_num', $dayNum)
            ->first();
        if(!$filtered) {
            return [
                't_jadwal_kerja_id' => null,
                't_jadwal_kerja_det_hari_id' => null,
                'm_jam_kerja_id' => null,
                'nama_jam_kerja' => null,
            ];
        }
        return [
            't_jadwal_kerja_id' => $getJadwalKerja->id ?? null,
            't_jadwal_kerja_det_hari_id' => $filtered->id ?? null,
            'm_jam_kerja_id' => $filtered->m_jam_kerja_id ?? null,
            'nama_jam_kerja' => $getJadwalKerja->keterangan ?? null,
        ];
    }

}
