# JOBLIST - HRIS Temprina
Berdasarkan feedback user 13-08-2026 dan spreadsheet maintenance Trial HRIS Temprina.
Diperbarui: 2026-08-18

---

## Legenda Status
- [ ] Belum dikerjakan
- [/] Sedang dikerjakan
- [x] Selesai
- [!] Error runtime - prioritas tinggi
- [-] Manual / setup data, bukan perubahan source

---

## P0 - Error Runtime (Harus Segera)

### t_perdin - Penugasan Dinas Karyawan
- [!] Error ketika memilih atasan
  - Error: Call to undefined method App\Models\CustomModels\m_level_posisi::m_level_posisi_d()
  - Relasi sudah ada di source lokal Models/m_level_posisi/Custom.php
  - Kemungkinan besar: source di server belum diperbarui, perlu deploy ulang / clear cache
  - File: Models/m_level_posisi/Custom.php, Models/t_perdin/Custom.php

### t_rencana_perdin - Rencana Perjalanan Dinas
- [!] Error ketika memilih tarif perdin
  - Error: Call to undefined method App\Models\CustomModels\m_level_posisi::m_level_posisi_d()
  - Akar masalah sama dengan t_perdin di atas
  - File: Models/m_level_posisi/Custom.php, Models/t_rencana_perdin/Custom.php

### t_penyelesaian_perdin - Penyesaian Perjalanan Dinas
- [x] Error ketika memilih kasbon - 500 internal server error
  - Log tidak cukup detail untuk investigasi
  - PERLU TEMPEL: Models/t_penyelesaian_perdin/Custom.php dari generator

### t_efektifitas_pelatihan - Efektifitas Pelatihan
- [x] Error: invalid input syntax for type bigint: null
  - Penyebab: scopeEfektifitas() di Models/m_kary/Custom.php melakukan
    where(atasan_id, kary_id) tanpa sanitasi nilai null
  - Fix: normalisasi kary_id sebelum masuk query, gunakan whereNull atau return empty jika null
  - File: Models/m_kary/Custom.php (fungsi scopeEfektifitas)

---

## P1 - Bug Fungsional / Fitur Penting

### t_perdin - Penugasan Dinas Karyawan
- [ ] Approval dikirim ke atasan berupa notifikasi untuk approve
  - Mekanisme: via generate_approval yang sudah ada + trigger FCM/push setelah ticket dibuat
  - File: Models/t_perdin/Custom.php, Cores/Helper.php

### t_rencana_perdin - Rencana Perjalanan Dinas
- [ ] Approval dikirim ke atasan berupa notifikasi untuk approve
  - Mekanisme sama dengan t_perdin

### t_kbs - Kas Bon Sementara
- [ ] Nama karyawan belum muncul di landing
  - Kemungkinan: transformRowData() belum menyertakan nama_lengkap dari join m_kary
  - PERLU TEMPEL: Models/t_kbs/Custom.php dan Javascript/t_kbs.js

### m_prog_pelatihan - Program Pelatihan
- [x] Kolom sasaran belum ditampilkan datanya di landing
  - PERLU TEMPEL: Models/m_prog_pelatihan/Custom.php dan Javascript/m_prog_pelatihan.js

### t_req_pelatihan - Pengajuan Pelatihan
- [x] Status seharusnya auto active saat create
- [x] Tambah validasi status di frontend sebelum submit
- [x] Saat view detail, nama karyawan tidak muncul
  - PERLU TEMPEL: Models/t_req_pelatihan/Custom.php

### m_penilaian - Komponen Penilaian Karyawan
- [ ] Batasan: hanya bisa memilih 1 komponen per kategori
- [ ] Level belum tampil datanya di landing
- [ ] Saat data dilihat, level yang tersimpan menampilkan NaN
  - Kemungkinan: nilai level tidak konsisten tipe string vs number saat transform
  - PERLU TEMPEL: Models/m_penilaian/Custom.php dan Javascript/m_penilaian.js

---

## P2 - Enhancement / Fitur Baru

### t_realisasi_pelatihan - Realisasi Pelatihan
- [ ] Divisi otomatis mengikut dari data request pelatihan saat create
- [ ] Status otomatis auto active saat create
- [ ] Detail karyawan default mengikuti pengajuan pelatihan, namun bisa ditambah/dikurangi
  - PERLU TEMPEL: Models/t_realisasi_pelatihan/Custom.php

### t_evaluasi_pelatihan - Evaluasi Pelatihan
- [ ] Nama pelatihan belum ditampilkan di landing
- [ ] Tambah tombol save di form
- [ ] Form diisi oleh peserta pelatihan (filter by login user)
- [ ] Evaluasi dimunculkan sebagai notifikasi ke peserta yang mengikuti pelatihan
  - PERLU TEMPEL: Models/t_evaluasi_pelatihan/Custom.php dan Javascript/t_evaluasi_pelatihan.js

### t_efektifitas_pelatihan - Efektifitas Pelatihan
- [ ] Form hanya bisa diisi oleh atasan peserta (filter by login user = atasan)
- [ ] Data karyawan yang muncul mengikuti atasan dan data dari realisasi pelatihan
  - PERLU TEMPEL: Models/t_efektifitas_pelatihan/Custom.php (kerjakan setelah P0 selesai)

### m_karyawan - Master Karyawan
- [ ] Kolom atasan tidak perlu ditampilkan di landing dan form
- [ ] Saat input jabatan: memilih sub otomatis mengisi field company
- [ ] Dropdown jabatan menampilkan level jabatan juga
  - File: Blades/m_karyawan.blade.php, Javascript/m_karyawan.js

### t_penilaian_kary - Penilaian Karyawan
- [ ] Level karyawan otomatis terisi saat memilih karyawan
- [ ] Divisi karyawan dimunculkan di form
  - PERLU TEMPEL: Models/t_penilaian_kary/Custom.php dan Javascript/t_penilaian_kary.js

---

## Setup Manual (Bukan Perubahan Source)

### Menu
- [-] Setup data SubModul di tabel m_menu via generator / database langsung

---

## Source yang Masih Perlu Ditempel dari Generator

| Model                  | File                  | Untuk Isu                          |
|------------------------|-----------------------|------------------------------------|
| t_penyelesaian_perdin  | Custom.php            | Error kasbon                       |
| t_kbs                  | Custom.php, Basic.php | Nama karyawan di landing           |
| m_prog_pelatihan       | Custom.php            | Sasaran di landing                 |
| t_req_pelatihan        | Custom.php            | Nama karyawan di detail            |
| m_penilaian            | Custom.php, Basic.php | Level NaN, level di landing        |
| t_realisasi_pelatihan  | Custom.php            | Auto divisi & karyawan             |
| t_evaluasi_pelatihan   | Custom.php            | Nama pelatihan, peserta, notif     |
| t_efektifitas_pelatihan| Custom.php            | Filter atasan, data dari realisasi |
| t_penilaian_kary       | Custom.php            | Level & divisi auto-fill           |

---

## Urutan Pengerjaan yang Disarankan

1. Deploy / clear cache server
   cek apakah error t_perdin & t_rencana_perdin (m_level_posisi_d) sudah hilang

2. Fix scopeEfektifitas() di Models/m_kary/Custom.php
   sanitasi null pada kary_id sebelum masuk query

3. Tempel source t_penyelesaian_perdin, investigasi error kasbon

4. Tempel source t_kbs, fix nama karyawan di landing

5. [x] Tempel source m_prog_pelatihan, fix sasaran di landing

6. [x] Tempel source t_req_pelatihan, fix nama karyawan di detail + auto status

7. Tempel source m_penilaian, fix level NaN + level di landing

8. P2 lanjut setelah P0 dan P1 selesai:
   t_realisasi_pelatihan, t_evaluasi_pelatihan, t_efektifitas_pelatihan, m_karyawan, t_penilaian_kary

9. Setup SubModul Menu (manual, bisa kapan saja paralel)
