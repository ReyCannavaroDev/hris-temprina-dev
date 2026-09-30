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

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $nomor = $arrayData['nomor'] ?? $this->helper->generateNomor("KODE MUTASI");
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

        $newArrayData = array_merge($arrayData, [
            "nomor" => $nomor,
            "tipe_mutasi" => $tipeMutasi,
            "no_dokumen" => $arrayData['no_dokumen'] ?? $nomor,
            "deskripsi" => $arrayData['deskripsi'] ?? '-',
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
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
