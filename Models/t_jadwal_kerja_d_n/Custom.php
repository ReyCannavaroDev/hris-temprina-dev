<?php

namespace App\Models\CustomModels;

/**
 * placeholder model: t_jadwal_kerja_d_n
 * bagian: Custom
 * tempel source code dari generator lama di file ini.
 */
class t_jadwal_kerja_d_n extends \App\Models\BasicModels\t_jadwal_kerja_d_n
{
    public function __construct()
    {
        parent::__construct();

        $this->joins = array_values(array_unique(array_merge($this->joins ?? [], [
            "m_kary.id=t_jadwal_kerja_d_n.m_kary_id",
            "m_branch.id=m_kary.m_branch_id",
            "m_divisi.id=m_kary.m_divisi_id",
            "m_posisi.id=m_kary.m_posisi_id",
            "t_jadwal_kerja_n.id=t_jadwal_kerja_d_n.t_jadwal_kerja_n_id",
        ])));
    }

    public function createBefore($model, $arrayData, $metaData, $id=null)
    {
        $payload = app('request')->all();
        $karyawanIds = $payload['m_kary_id'] ?? $arrayData['m_kary_id'] ?? [];
        
        if (is_string($karyawanIds)) {
            $decoded = json_decode($karyawanIds, true);
            $karyawanIds = json_last_error() === JSON_ERROR_NONE ? $decoded : explode(',', $karyawanIds);
        }

        if (!empty($karyawanIds) && is_array($karyawanIds)) {
            // Ambil ID pertama untuk di-insert oleh generator native
            $firstKaryId = array_shift($karyawanIds);
            $targetFirstId = is_array($firstKaryId) ? ($firstKaryId['id'] ?? $firstKaryId['m_kary_id']) : $firstKaryId;
            $arrayData['m_kary_id'] = $targetFirstId;

            $kProfile = \DB::table('m_kary')->where('id', $targetFirstId)->first();
            if ($kProfile) {
                $arrayData['m_divisi_id'] = $kProfile->m_divisi_id ?? $arrayData['m_divisi_id'] ?? null;
                $arrayData['m_branch_id'] = $kProfile->m_branch_id ?? $arrayData['m_branch_id'] ?? null;
                $arrayData['m_subcomp_id'] = $kProfile->m_subcomp_id ?? $arrayData['m_subcomp_id'] ?? null;
            }

            // Jika masih ada sisa karyawan yang dipilih, insert manual sisanya
            if (count($karyawanIds) > 0) {
                $table = $model->getTable();
                $baseData = $arrayData;
                $baseData['created_at'] = \Carbon\Carbon::now();
                $baseData['updated_at'] = \Carbon\Carbon::now();

                unset($baseData['t_jadwal_kerja_d_hari_n']);
                unset($baseData['t_jadwal_kerja_det_hari']);
                unset($baseData['detailArr']);

                foreach ($karyawanIds as $kId) {
                    if (empty($kId)) continue;
                    $insertData = $baseData;
                    $targetKId = is_array($kId) ? ($kId['id'] ?? $kId['m_kary_id']) : $kId;
                    $insertData['m_kary_id'] = $targetKId;

                    $kProfile = \DB::table('m_kary')->where('id', $targetKId)->first();
                    if ($kProfile) {
                        $insertData['m_divisi_id'] = $kProfile->m_divisi_id ?? $insertData['m_divisi_id'] ?? null;
                        $insertData['m_branch_id'] = $kProfile->m_branch_id ?? $insertData['m_branch_id'] ?? null;
                        $insertData['m_subcomp_id'] = $kProfile->m_subcomp_id ?? $insertData['m_subcomp_id'] ?? null;
                    }

                    \DB::table($table)->insert($insertData);
                }
            }
        }

        // Hapus payload detail read-only agar tidak memodifikasi master jadwal kerja bersama
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['detailArr']);

        return [
            "model" => $model,
            "data"  => $arrayData
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id=null)
    {
        $targetId = $id ?: ($model->id ?? null);
        if ($targetId) {
            $isUsedInPresensi = \DB::table('presensi_absensi')
                ->where('t_jadwal_kerja_d_n_id', $targetId)
                ->exists();

            if ($isUsedInPresensi) {
                $existing = \DB::table('t_jadwal_kerja_d_n')->where('id', $targetId)->first();
                if ($existing) {
                    $oldStartDate = !empty($existing->start_date) ? \Carbon\Carbon::parse($existing->start_date)->format('Y-m-d') : null;
                    $newStartDate = array_key_exists('start_date', $arrayData) && !empty($arrayData['start_date'])
                        ? \Carbon\Carbon::parse($arrayData['start_date'])->format('Y-m-d')
                        : $oldStartDate;

                    $karyChanged = array_key_exists('m_kary_id', $arrayData)
                        && (string)$arrayData['m_kary_id'] !== (string)$existing->m_kary_id;
                    $masterChanged = array_key_exists('t_jadwal_kerja_n_id', $arrayData)
                        && (string)$arrayData['t_jadwal_kerja_n_id'] !== (string)$existing->t_jadwal_kerja_n_id;
                    $dateChanged = array_key_exists('start_date', $arrayData)
                        && (string)$newStartDate !== (string)$oldStartDate;

                    if ($karyChanged || $masterChanged || $dateChanged) {
                        return [
                            "model" => $model,
                            "data" => $arrayData,
                            "errors" => ["Penugasan jadwal kerja tidak dapat diubah (karyawan, master jadwal, atau start date) karena sudah memiliki riwayat absensi. Silakan buat penugasan jadwal baru dengan start date baru."],
                        ];
                    }
                }
            }
        }

        // Hapus payload detail read-only agar tidak memodifikasi master jadwal kerja bersama
        unset($arrayData['t_jadwal_kerja_d_hari_n']);
        unset($arrayData['t_jadwal_kerja_det_hari']);
        unset($arrayData['detailArr']);

        return [
            "model" => $model,
            "data"  => $arrayData
        ];
    }

    public function deleteBefore($model, $arrayData, $metaData, $id = null)
    {
        $targetId = $id ?: ($model->id ?? null);
        if ($targetId) {
            $isUsed = \DB::table('presensi_absensi')
                ->where('t_jadwal_kerja_d_n_id', $targetId)
                ->exists();
            if ($isUsed) {
                throw new \Exception("Jadwal kerja karyawan tidak dapat dihapus karena sudah memiliki riwayat absensi. Silakan nonaktifkan jadwal ini dan buat jadwal baru dengan start date baru.");
            }
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        return $arrayData;
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)
    {
        return $arrayData;
    }
    
    public function transformRowData(array $row)
    {
        $id = $row['id'] ?? null;
        if (!$id) return $row;

        // Karyawan & Posisi
        if (!empty($row['m_kary_id'])) {
            $kary = \DB::table('m_kary')->where('id', $row['m_kary_id'])->first();
            if ($kary) {
                $row['m_kary'] = (array)$kary;
                $row['nama_lengkap'] = $kary->nama_lengkap ?? null;
                $row['m_kary.nama_lengkap'] = $kary->nama_lengkap ?? null;
                
                $posisiId = $kary->m_posisi_id ?? null;
                if (!empty($posisiId)) {
                    $posisi = \DB::table('m_posisi')->where('id', $posisiId)->first();
                    if ($posisi) {
                        $row['m_kary']['m_posisi'] = (array)$posisi;
                        $row['m_posisi'] = (array)$posisi;
                        $row['m_posisi.name'] = $posisi->name ?? null;
                        $row['posisi_name'] = $posisi->name ?? null;
                    }
                }

                // Jika branch belum ada di row, ambil dari karyawan
                if (empty($row['m_branch_id']) && !empty($kary->m_branch_id)) {
                    $row['m_branch_id'] = $kary->m_branch_id;
                }
            }
        }

        // Cabang
        if (!empty($row['m_branch_id'])) {
            $branch = \DB::table('m_branch')->where('id', $row['m_branch_id'])->first();
            if ($branch) {
                $row['m_branch'] = (array)$branch;
                $row['m_branch.name'] = $branch->name ?? null;
                $row['branch_name'] = $branch->name ?? null;
            }
        }

        // Subcomp
        if (!empty($row['m_subcomp_id'])) {
            $subcomp = \DB::table('m_subcomp')->where('id', $row['m_subcomp_id'])->first();
            if ($subcomp) {
                $row['m_subcomp'] = (array)$subcomp;
                $row['m_subcomp.name'] = $subcomp->name ?? null;
            }
        }

        // Divisi
        $divisiId = $row['m_divisi_id'] ?? ($kary->m_divisi_id ?? null);
        if (!empty($divisiId)) {
            $divisi = \DB::table('m_divisi')->where('id', $divisiId)->first();
            if ($divisi) {
                $divName = null;
                if (!empty($divisi->name) && is_numeric($divisi->name)) {
                    $gen = \DB::table('m_general')->where('id', $divisi->name)->first();
                    $divName = $gen?->value;
                }
                if (empty($divName)) {
                    $divName = $divisi->name_old ?? $divisi->name ?? null;
                }
                $row['m_divisi'] = (array)$divisi;
                $row['m_divisi.name'] = $divName;
                $row['divisi_name'] = $divName;
                $row['nama_divisi'] = $divName;
            }
        }

        // Jadwal Kerja
        if (!empty($row['t_jadwal_kerja_n_id'])) {
            $jadwal = \DB::table('t_jadwal_kerja_n')->where('id', $row['t_jadwal_kerja_n_id'])->first();
            if ($jadwal) {
                $row['t_jadwal_kerja_n'] = (array)$jadwal;
                $row['t_jadwal_kerja_n.keterangan'] = $jadwal->keterangan ?? null;
                $row['jadwal_kerja'] = $jadwal->keterangan ?? null;
            }
        }

        return $row;
    }

    public function t_jadwal_kerja_n()
    {
        return $this->belongsTo(\App\Models\CustomModels\t_jadwal_kerja_n::class, 't_jadwal_kerja_n_id', 'id');
    }

    public function m_kary()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_kary::class, 'm_kary_id', 'id');
    }

    public function m_divisi()
    {
        return $this->belongsTo(\App\Models\CustomModels\m_divisi::class, 'm_divisi_id', 'id');
    }

    public function scoperespo($model)
    {
        $m_subcomp_id = request("m_subcomp_id");
        $m_branch_id = request("m_branch_id");
        $m_divisi_id = request("m_divisi_id");

        if (empty($m_subcomp_id) || $m_subcomp_id === "null" || $m_subcomp_id === "undefined" || $m_subcomp_id === "[object Object]") {
            $m_subcomp_id = null;
        }
        if (empty($m_branch_id) || $m_branch_id === "null" || $m_branch_id === "undefined" || $m_branch_id === "[object Object]") {
            $m_branch_id = null;
        }
        if (empty($m_divisi_id) || $m_divisi_id === "null" || $m_divisi_id === "undefined" || $m_divisi_id === "[object Object]") {
            $m_divisi_id = null;
        }

        // Decode JSON array strings if sent as "[1]" or "[1,2]"
        if (is_string($m_subcomp_id) && str_starts_with(trim($m_subcomp_id), '[')) {
            $m_subcomp_id = json_decode($m_subcomp_id, true);
        }
        if (is_string($m_branch_id) && str_starts_with(trim($m_branch_id), '[')) {
            $m_branch_id = json_decode($m_branch_id, true);
        }
        if (is_string($m_divisi_id) && str_starts_with(trim($m_divisi_id), '[')) {
            $m_divisi_id = json_decode($m_divisi_id, true);
        }

        $authUser = auth()->user();
        $isHc = false;
        if ($authUser) {
            $userType = strtolower($authUser->user_type ?? '');
            $isHc = !empty($authUser->is_hc) || $userType === 'admin' || $userType === 'superadmin';

            if (!$isHc && !empty($authUser->id)) {
                try {
                    $user_respo = \DB::table('default_users_respo')
                        ->where('default_users_id', $authUser->id)
                        ->where('is_primary', true)
                        ->first();
                    if ($user_respo && !empty($user_respo->m_respo_id)) {
                        $roles = \DB::table('m_respo_d')
                            ->join('m_role', 'm_role.id', '=', 'm_respo_d.m_role_id')
                            ->where('m_respo_d.m_respo_id', $user_respo->m_respo_id)
                            ->pluck('m_role.name');
                        foreach ($roles as $rName) {
                            $rLower = strtolower($rName);
                            if (str_contains($rLower, 'admin') || str_contains($rLower, 'hc') || str_contains($rLower, 'hrd') || str_contains($rLower, 'super') || str_contains($rLower, 'personalia')) {
                                $isHc = true;
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) {}
            }

            // Jika bukan HC (Kadiv / Karu): kunci ketat ke divisi dan cabangnya
            if (!$isHc && !empty($authUser->m_kary_id)) {
                $userKary = \DB::table('m_kary')->where('id', $authUser->m_kary_id)->first();
                if ($userKary && !empty($userKary->m_divisi_id)) {
                    $m_divisi_id = $userKary->m_divisi_id;
                }
                if ($userKary && !empty($userKary->m_branch_id)) {
                    $m_branch_id = $userKary->m_branch_id;
                }
            }
        }

        return $model
            ->when($m_subcomp_id, function ($q) use ($m_subcomp_id) {
                if (is_numeric($m_subcomp_id)) {
                    $q->where("m_kary.m_subcomp_id", $m_subcomp_id);
                } else if (is_array($m_subcomp_id)) {
                    $q->whereIn("m_kary.m_subcomp_id", $m_subcomp_id);
                }
            })
            ->when($m_branch_id, function ($q) use ($m_branch_id) {
                if (is_numeric($m_branch_id)) {
                    $q->where("m_kary.m_branch_id", $m_branch_id);
                } else if (is_array($m_branch_id)) {
                    $q->whereIn("m_kary.m_branch_id", $m_branch_id);
                }
            })
            ->when($m_divisi_id, function ($q) use ($m_divisi_id) {
                if (is_numeric($m_divisi_id)) {
                    $q->where("m_kary.m_divisi_id", $m_divisi_id);
                } else if (is_array($m_divisi_id)) {
                    $q->whereIn("m_kary.m_divisi_id", $m_divisi_id);
                }
            });
    }
}
