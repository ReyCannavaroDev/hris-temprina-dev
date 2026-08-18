# PLAN — Backlog Analisis dan Perbaikan HRIS Temprina

Dokumen ini adalah backlog hasil pemeriksaan source yang tersedia di workspace. Repo ini hanya berisi potongan source generator lama dan bukan aplikasi Laravel lengkap, sehingga status “sudah ada di source” belum sama dengan “sudah terbukti berhasil di server”. Tidak ada perubahan source aplikasi pada analisis ini; file yang diubah hanya dokumen rencana ini.

## Status

- **BUG TERBUKTI DI SOURCE**: source saat ini memperlihatkan kondisi yang dapat menyebabkan feedback/error.
- **SUDAH ADA DI SOURCE — PERLU REGRESI**: pola perbaikannya sudah ada, tetapi belum dapat diuji tanpa runtime/API/database.
- **MITIGASI ADA — VALIDASI DEPLOYMENT**: source lokal menghindari masalah, tetapi perlu dipastikan source yang dijalankan server sama.
- **BELUM DAPAT DIBUKTIKAN**: file terkait kosong/placeholder atau dependensi penting tidak tersedia.
- **CLOSED CLIENT**: feedback dinyatakan selesai oleh client; tidak ada pekerjaan baru berdasarkan source saat ini.
- **BLOCKED**: membutuhkan source, schema, log, atau aturan bisnis tambahan.

## Prioritas backlog

### P0 — t_klaim_askes: detail dan kontrak payload

**Status: BUG TERBUKTI DI SOURCE**

Temuan yang perlu diperbaiki:

1. `Javascript/t_klaim_askes.js:636-641` mengisi `row.t_klaim_askes_id = row.id`. Data klaim dari API `getAskes` dibentuk sebagai data referensi dengan field `klaim_id`, `klaim_nama`, `klaim_type`, dan `m_kary_id` di `Models/m_kary/Custom.php` sekitar fungsi `getAskes`. Field `id` tidak terlihat dijamin ada. Akibatnya detail baru dapat tersimpan tanpa foreign key parent.
2. `Blades/t_klaim_askes.blade.php:335` memanggil `removeDetail(item)`, sedangkan `Javascript/t_klaim_askes.js:643-645` mengharapkan index numerik untuk `splice`. Ini adalah mismatch nyata antara template dan fungsi JavaScript.
3. UI menambahkan `santunanPct` ke object detail (`Javascript/t_klaim_askes.js:146-193`), sedangkan kolom detail di `Models/t_klaim_askes_d/Basic.php` hanya mendefinisikan `santunan` sebagai string. Saat save, `detailArr` dikirim utuh (`Javascript/t_klaim_askes.js:752-765`). Payload harus dinormalisasi agar field UI-only tidak ikut disimpan, atau kontraknya harus ditambahkan secara resmi pada model/database.
4. `apiAskes` memakai `.map()` setelah fallback response (`Javascript/t_klaim_askes.js:279-307`). Fallback belum menjamin nilai akhir adalah array; response object tanpa `data` masih dapat memunculkan error `map`.

Perbaikan yang direncanakan:

- Tetapkan parent ID dari claim yang sedang diedit/dibuat, bukan dari row referensi klaim askes.
- Samakan pemanggilan `removeDetail` dengan kontrak fungsi, lalu uji hapus detail pertama, tengah, dan terakhir.
- Pisahkan field tampilan seperti `santunanPct` dari payload database; pastikan `santunan` tetap mengikuti tipe string pada `t_klaim_askes_d`.
- Tambahkan guard `Array.isArray` pada hasil API sebelum `.map()`.

Yang sudah tersedia dan perlu diregresikan:

- Relasi detail sudah didaftarkan di `Models/t_klaim_askes/Custom.php:13-15,52-55`.
- Read form sudah mengambil endpoint detail dan mengisi `klaim.t_klaim_askes_d` di `Javascript/t_klaim_askes.js:359-407`.
- Landing sudah meminta `transform=true` dan menampilkan `m_kary.nama_lengkap` di `Javascript/t_klaim_askes.js:1069-1081,1110`; `transformRowData()` memasok field tersebut di `Models/t_klaim_askes/Custom.php:36-50`.

### P0 — t_perdin: pastikan source perbaikan benar-benar yang dideploy

**Status: SUDAH ADA DI SOURCE — PERLU REGRESI / MITIGASI DEPLOYMENT**

Temuan:

- Relasi `t_penyelesaian_perdin()` sudah ada di `Models/t_perdin/Custom.php:39-42`, dan `t_perdin/Basic.php` mencantumkan `t_penyelesaian_perdin` sebagai heir. Feedback `undefined method` untuk relasi ini seharusnya sudah tertangani di source lokal.
- Error lampiran awal menyebut `m_level_posisi_d()`, bukan `t_penyelesaian_perdin()`. Relasi `m_level_posisi_d()` sudah ada di `Models/m_level_posisi/Custom.php:17-20`, sementara `scopehigherlevel()` memakainya melalui `whereHas()` di `Models/m_kary/Custom.php:2381-2428`. Jika server masih menghasilkan undefined method, kandidat utama adalah source/cache/deployment tidak sama dengan workspace ini.
- Penomoran hari sudah dinormalisasi dengan `trim()` dan `strtolower()` di `Cores/Helper.php:70-85`, termasuk mapping `MG`, `SN`, `SL`, `RB`, `KM`, `JM`, `SB`.
- Kolom `tanggal_surat_tugas` dan `tanggal_rencana_biaya` sudah dibuat di `Models/t_perdin/Alter.php:12-19`, didaftarkan di `Models/t_perdin/Custom.php:21-27`, diisi dari `date_from` pada `createBefore()` (`Custom.php:85-97`), dan ditampilkan read-only di Blade serta tabel JavaScript.

Yang harus diverifikasi:

- Deploy ulang/clear cache sesuai mekanisme aplikasi kantor, lalu pastikan class `CustomModels` yang dipanggil runtime memuat dua relasi tersebut.
- Uji pemilihan atasan pada `t_perdin` dan tarif perdin, termasuk nilai kosong dan karyawan tanpa level posisi.
- Uji nomor untuk seluruh kode hari dan variasi spasi/huruf kapital.
- Uji create/edit ketika `date_from` kosong, berubah, atau dikirim sebagai format tanggal berbeda.
- Periksa regresi join: `t_perdin/Custom.php:13-16` mengganti join `m_atasan_id` menjadi join karyawan pengaju (`m_kary_id`). Pastikan landing/detail yang membutuhkan nama atasan tidak kehilangan datanya.

### P1 — menu karyawan dan relasi m_jam_kerja

**Status: BUG TERBUKTI DI SOURCE; MITIGASI ADA**

Temuan:

- `Models/m_kary/Migration.php:25` dan `Models/m_kary/Basic.php:23` mendefinisikan `m_jam_kerja_id` sebagai JSON.
- `Models/m_kary/Basic.php` masih memiliki join `m_jam_kerja.id=m_kary.m_jam_kerja_id` dan relasi `belongsTo` ke ID. Ini tidak konsisten dengan tipe JSON dan cocok dengan error PostgreSQL `operator does not exist: bigint = json`.
- `Models/m_kary/Custom.php:23-25` menghapus join tersebut dari constructor; salah satu query custom juga tidak lagi menjalankan join (`Custom.php:559`). Ini adalah mitigasi lokal, bukan bukti semua endpoint sudah aman.
- Source `Models/m_jam_kerja/Basic.php` dan `Migration.php` masih placeholder, sehingga tipe/schema tabel referensi tidak dapat dipastikan dari workspace.

Rencana:

- Inventaris semua endpoint/menu yang memakai `m_kary`, `joins`, atau scope terkait jam kerja.
- Tentukan kontrak data berdasarkan migration/schema resmi: apakah `m_jam_kerja_id` memang JSON, atau seharusnya bigint.
- Jangan mengubah tipe kolom atau join secara global sebelum schema resmi tersedia.
- Uji menu karyawan dengan data `m_jam_kerja_id` null, satu ID, dan JSON array setelah source server dipastikan sama.

### P1 — t_efektivitas_pelatihan: parameter atasan bernilai literal null

**Status: BUG TERBUKTI DI SOURCE**

Bukti:

- `Models/m_kary/Custom.php:2799-2818` pada `scopeEfektifitas()` membaca `request('kary_id')` lalu langsung menjalankan `where('m_kary.atasan_id', $req->kary_id)`.
- Log feedback yang ditempel menunjukkan query memakai `m_kary.atasan_id = null`, yang di PostgreSQL dapat menjadi invalid input syntax karena `null` diperlakukan sebagai string/parameter, bukan `IS NULL`.

Rencana:

- Tetapkan perilaku bisnis ketika `kary_id` kosong: kembalikan hasil kosong/validasi 422, atau gunakan `whereNull` hanya jika memang itu aturan bisnis.
- Normalisasi string `"null"`, string kosong, dan nilai null sebelum query.
- Uji karyawan yang mempunyai bawahan, tanpa bawahan, `kary_id` kosong, dan `kary_id` tidak valid.

### P1 — t_klaim_askes: read, nama karyawan, dan struktur santunan

**Status: SUDAH ADA DI SOURCE — PERLU REGRESI; sebagian masih tercakup P0**

- Relasi `t_klaim_askes_d` dan proses read sudah ada, sehingga keluhan detail tidak terbaca tidak dapat dinyatakan masih terjadi hanya dari source.
- `transformRowData()` sudah menambahkan nama karyawan untuk landing.
- Field `santunan` di detail memang bertipe string. Yang belum konsisten adalah field UI `santunanPct`, bukan tipe `santunan` itu sendiri.
- Kesimpulan: jangan mengulang perubahan relasi/read sebelum P0 diuji; fokus pada kontrak payload dan guard array.

### P2 — pengajuan pelatihan: dropdown divisi berulang

**Status: SUDAH ADA DI SOURCE — PERLU REGRESI**

Bukti:

- `Blades/t_request_pelatihan.blade.php:214-220` memfilter divisi aktif berdasarkan `m_branch_id` yang dipilih.
- Branch reset juga tersedia pada Blade terkait, dan `Models/t_request_pelatihan/Custom.php:270-279` memiliki filter `m_subcomp_id`/`m_branch_id`.

Rencana verifikasi:

- Uji kedua varian tampilan (`t_request_pelatihan` dan `_req_pelatihan`) bila keduanya masih digunakan.
- Ganti branch beberapa kali dan pastikan pilihan divisi lama dikosongkan.
- Pastikan API tidak mengembalikan duplikat untuk data divisi yang sama; jika source API memang duplikat, baru tentukan deduplikasi di layer yang benar.

### P2 — penilaian karyawan / assessment

**Status: BELUM DAPAT DIBUKTIKAN / BLOCKED**

Temuan:

- UI `Blades/t_penilaian_kary.blade.php` dan `Javascript/t_penilaian_kary.js` ada, tetapi endpoint yang dipanggil adalah `t_assessment_kary` (`Javascript/t_penilaian_kary.js:28`).
- `Models/t_assessment_kary/Basic.php`, `Custom.php`, `Migration.php`, serta model detailnya masih placeholder. Tidak ada `Models/t_penilaian_kary` yang dapat dijadikan kontrak transaksi.
- UI sudah memiliki filter divisi dan scope hierarchy karyawan, tetapi itu belum membuktikan query rekursif atau aturan bawahan berlapis berjalan di backend.
- `Javascript/t_penilaian_kary.js:316` memanggil `.map()` langsung pada `initialValues.t_assessment_kary_d`; guard array perlu dipertimbangkan setelah kontrak response model tersedia.
- Mapping `Level Jabatan` ke `Tipe Penilaian` tidak ditemukan sebagai aturan final di source. Karena itu rancangan query rekursif, filter divisi, dan sinkronisasi jabatan jangan diimplementasikan sebelum aturan mapping dikonfirmasi.

Rencana setelah blocker dibuka:

- Minta source model/migration/alter/custom transaksi assessment yang sebenarnya.
- Minta tabel mapping resmi level jabatan → tipe penilaian dan aturan atasan/bawahan.
- Petakan query existing sebelum menambah recursive query.
- Uji karyawan level pertama, bawahan langsung, bawahan berlapis, beda divisi, dan jabatan yang tidak punya mapping.

Feedback indikator penilaian staff dicatat sebagai **CLOSED CLIENT** sesuai laporan maintenance; tidak ada bukti source yang mengharuskan perubahan baru.

## Daftar pengujian minimum

- **t_perdin**: pilih atasan, pilih tarif, create/edit tanggal, nomor semua hari, read/detail, dan data tanpa level posisi.
- **m_kary**: menu landing, join jam kerja, nilai ID/JSON/null, serta endpoint yang memakai Basic dan Custom model.
- **t_efektivitas_pelatihan**: `kary_id` valid, kosong, literal `null`, null database, dan karyawan tanpa bawahan.
- **t_klaim_askes**: tambah detail, simpan, baca ulang, hapus per posisi, approval read, santunan, map response non-array, dan nama karyawan di landing.
- **t_request_pelatihan**: branch berubah, divisi reset, data tidak duplikat.
- **assessment**: hanya setelah model dan aturan mapping tersedia.

## Urutan pengerjaan yang disarankan

1. Perbaiki dan uji P0 `t_klaim_askes`.
2. Cocokkan source runtime/deployment untuk relasi `t_perdin` dan `m_level_posisi_d`.
3. Selesaikan kontrak schema `m_jam_kerja`, lalu uji menu karyawan.
4. Normalisasi parameter `kary_id` pada efektivitas pelatihan.
5. Jalankan regresi training request dan t_perdin.
6. Minta source/aturan assessment sebelum melanjutkan rollbacked customization.
6. Setelah item di atas tervalidasi, lakukan audit ulang untuk feedback Temprina lain yang belum memiliki bukti source.

## Batasan analisis

Belum ada akses runtime, database, schema deployment, route/controller/service provider, atau log server tambahan. Karena itu rencana ini tidak menyimpulkan bahwa semua feedback sudah selesai hanya karena pola perbaikannya terlihat di source lokal. Setiap item “sudah ada di source” tetap membutuhkan pengujian endpoint dan konfirmasi source yang dideploy.
