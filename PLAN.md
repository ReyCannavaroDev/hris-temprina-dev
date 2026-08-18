# Analisis Error `t_perdin` Saat Memilih Atasan

## Ringkasan

Error pada lampiran adalah:

```text
Call to undefined method App\Models\CustomModels\m_level_posisi::m_level_posisi_d()
```

Error ini terjadi ketika form `t_perdin` mengisi pilihan **Atasan**. Akar masalah langsungnya adalah runtime mencoba menjalankan `whereHas('m_level_posisi_d')` pada model `App\Models\CustomModels\m_level_posisi`, tetapi method relasi tersebut tidak tersedia pada class yang sedang dimuat oleh server.

Source yang ada di workspace lokal sudah berisi relasi tersebut pada `Models/m_level_posisi/Custom.php`. Karena itu, penyebab paling mungkin adalah source server berbeda dari workspace ini, file `Custom.php` belum ikut ter-deploy, atau class/OPcache/autoload di server masih memakai versi lama. Belum ada bukti bahwa perubahan migration atau tipe kolom database diperlukan untuk error ini.

## Bukti alur request

1. Form `t_perdin` pada `Blades/t_perdin.blade.php:84-95` memakai `FieldSelect` untuk Atasan.
2. Field tersebut memanggil endpoint operasi `m_kary` dengan scope `higherlevel` dan parameter `t_m_kary_id` dari karyawan yang dipilih.
3. Scope tersebut didefinisikan di `Models/m_kary/Custom.php:2381-2428`.
4. Pada `Models/m_kary/Custom.php:2407-2411`, scope memanggil:

   ```php
   $level = m_level_posisi::whereHas("m_level_posisi_d", function ($q) use ($m_kary) {
       $q->where("m_posisi_id", $m_kary->m_posisi_id);
   })->first();
   ```

5. Pemanggilan `whereHas` membutuhkan method relasi `m_level_posisi_d` pada model `m_level_posisi` yang dipakai runtime. Pesan error menunjukkan class yang dipakai adalah `App\Models\CustomModels\m_level_posisi`.

## Temuan pada source lokal

### Relasi yang dibutuhkan sudah ada

`Models/m_level_posisi/Custom.php:17-20` memiliki implementasi:

```php
public function m_level_posisi_d()
{
    return $this->hasMany(\App\Models\CustomModels\m_level_posisi_d::class, 'm_level_posisi_id', 'id');
}
```

`Models/m_level_posisi_d/Basic.php:14-20` dan `Models/m_level_posisi_d/Migration.php:13-21` juga konsisten dengan relasi tersebut:

- tabel detail: `m_level_posisi_d`;
- foreign key: `m_level_posisi_d.m_level_posisi_id`;
- parent key: `m_level_posisi.id`;
- keduanya bertipe `bigInteger`.

Artinya, berdasarkan source lokal, `whereHas('m_level_posisi_d')` adalah relasi yang valid. Error pada lampiran tidak dapat dijelaskan oleh source lokal yang sedang ditempel apabila file server identik dan class sudah dimuat ulang.

### Class Basic tidak memiliki relasi tersebut

`Models/m_level_posisi/Basic.php:10-39` tidak mendefinisikan method `m_level_posisi_d`. Relasi hanya ada pada Custom model. Ini sesuai pola generator lama yang menaruh relasi tambahan di `Custom.php`, tetapi membuat deployment harus menyertakan file Basic dan Custom secara bersamaan.

Pesan error menyebut namespace `CustomModels`, jadi masalah yang paling masuk akal bukan sekadar scope memanggil class Basic. Yang perlu dibuktikan di server adalah isi class Custom yang benar-benar sudah dimuat, bukan hanya isi file di repository lokal.

## Pemeriksaan tipe kolom dan relasi

Relasi yang terlibat dalam pemilihan Atasan memiliki tipe yang konsisten:

| Relasi | Source | Tipe lokal | Status |
|---|---|---:|---|
| level ke detail | `m_level_posisi.id` → `m_level_posisi_d.m_level_posisi_id` | bigint → bigint | konsisten |
| detail ke jabatan | `m_level_posisi_d.m_posisi_id` → `m_posisi.id` | bigint → bigint | konsisten |
| karyawan ke jabatan | `m_kary.m_posisi_id` → `m_posisi.id` | bigint → bigint | konsisten |
| karyawan ke divisi | `m_kary.m_divisi_id` → `m_divisi.id` | bigint → bigint | konsisten |
| hierarki divisi | `m_divisi.parent_id` → `m_divisi.id` | bigint → bigint | konsisten |
| perdin ke atasan | `t_perdin.m_atasan_id` → `m_kary.id` | bigint → bigint | konsisten |

Dengan demikian, error pada lampiran bukan indikasi mismatch `bigint` versus `json` pada jalur pemilihan Atasan.

## Hal yang bukan akar masalah error lampiran

Workspace memang memiliki masalah tipe lain yang perlu dipisahkan dari kasus ini:

- `Models/m_kary/Migration.php:25` mendefinisikan `m_jam_kerja_id` sebagai JSON.
- `Models/m_kary/Basic.php:23-25` masih mencatat metadata/join `m_jam_kerja.id=m_kary.m_jam_kerja_id`.
- `Models/m_kary/Custom.php:23-25` secara eksplisit menghapus join tersebut.

Ini merupakan indikasi masalah lama `bigint = json` yang mungkin terkait listing karyawan, tetapi lampiran `t_perdin` berisi error `undefined method`, bukan error operator PostgreSQL. Jangan memperbaiki field `m_jam_kerja_id` sebagai solusi untuk error Atasan sebelum ada query/log yang membuktikan jalurnya.

## Diagnosis dan tingkat kepastian

### Pasti

- Request Atasan masuk ke scope `higherlevel` pada model `m_kary`.
- Scope tersebut memanggil relasi `m_level_posisi_d`.
- Runtime pada saat error tidak menemukan method relasi itu pada `App\Models\CustomModels\m_level_posisi`.

### Sangat mungkin

- Server menjalankan versi `Models/m_level_posisi/Custom.php` yang lebih lama atau tidak sama dengan source lokal.
- File Custom belum tersalin saat deployment, atau class yang sudah dimuat belum di-reload setelah file berubah.
- Cache class/OPcache/autoload membuat proses aplikasi masih menggunakan definisi class lama.

### Belum dapat dipastikan dari workspace

- Environment asal error: QL, development, atau production.
- Commit/salinan source yang sedang berjalan di environment tersebut.
- Apakah ada mekanisme generator/loader lain yang mengganti class Custom dengan class berbeda.
- Data karyawan tertentu yang dipilih, nilai `m_posisi_id`, serta isi aktual tabel `m_level_posisi_d`.

## Rencana perbaikan

### Tahap 1 — Verifikasi source yang berjalan

1. Bandingkan file server `Models/m_level_posisi/Custom.php` dengan source lokal.
2. Pastikan namespace dan class yang dimuat adalah `App\Models\CustomModels\m_level_posisi`.
3. Pastikan class tersebut memiliki method `m_level_posisi_d` dengan foreign key `m_level_posisi_id` dan local key `id`.
4. Periksa apakah file `Models/m_level_posisi_d/Custom.php` tersedia pada server, karena relasi lokal mengarah ke class Custom detail.
5. Setelah source disamakan, reload mekanisme cache/class yang digunakan oleh environment sesuai prosedur kantor. Jangan mengasumsikan route, service provider, Composer, Artisan, atau struktur Laravel standar dari workspace ini.

### Tahap 2 — Patch minimal bila server memang belum memiliki relasi

File yang perlu diubah:

- `Models/m_level_posisi/Custom.php`

Perubahan minimal:

- tambahkan method `m_level_posisi_d()` seperti yang sudah ada di source lokal pada baris 17-20;
- jangan mengubah migration atau tipe kolom;
- jangan mengganti nama relasi yang dipanggil scope `higherlevel`;
- jangan mengubah query level sebelum relasi runtime berhasil diverifikasi.

Jika source server ternyata memakai model Basic secara langsung, periksa loader model terlebih dahulu. Menyalin method ke Basic secara membabi buta dapat menyimpang dari pola generator dan dapat membuat class yang aktif berbeda dari yang diperkirakan.

### Tahap 3 — Validasi data dan perilaku bisnis

Setelah error method selesai:

1. Pilih karyawan pada form `t_perdin`.
2. Pastikan request Atasan mengirim `t_m_kary_id` yang valid, bukan string kosong atau nilai non-numerik.
3. Pastikan karyawan tersebut memiliki `m_posisi_id`.
4. Pastikan posisi tersebut memiliki baris pada `m_level_posisi_d`.
5. Pastikan level memiliki `sequence` dan terdapat kandidat dengan level lebih tinggi pada divisi yang sama atau divisi induknya.
6. Pastikan pilihan Atasan menampilkan `id` dan `nama_lengkap`.
7. Simpan `t_perdin` dan pastikan `m_atasan_id` tersimpan sebagai ID karyawan, bukan object JSON atau string kosong.

## Risiko dan dampak

- Dampak langsung perbaikan relasi terbatas pada query scope `higherlevel` dan endpoint pilihan Atasan.
- Jika relasi tidak ditemukan hanya karena deployment stale, perubahan database tidak diperlukan.
- Jika karyawan tidak memiliki posisi/level, daftar Atasan dapat kosong tanpa error; ini merupakan kondisi data atau aturan bisnis, bukan kegagalan relasi.
- `Models/t_perdin/Custom.php:13-16` menghapus join inherited `m_kary.id=t_perdin.m_atasan_id` lalu menambahkan join untuk `m_kary_id`. Ini tidak menjadi penyebab error saat lookup Atasan, tetapi perlu diuji terpisah karena dapat memengaruhi listing/detail `t_perdin` yang mengharapkan data atasan dari join otomatis.
- Scope `higherlevel` mencari rantai `m_divisi.parent_id` dengan loop. Data parent yang melingkar dapat menyebabkan loop tidak selesai; validasi data hierarki perlu dilakukan jika request tidak lagi gagal pada method tetapi tetap lambat.

## Rencana pengujian

Karena workspace ini hanya berisi source generator lama dan bukan aplikasi yang dapat dijalankan langsung, pengujian runtime harus dilakukan oleh programmer pada environment yang memiliki dependency dan database.

### Pemeriksaan source

- [ ] `Models/m_level_posisi/Custom.php` di environment target memiliki method `m_level_posisi_d()`.
- [ ] `Models/m_level_posisi_d/Custom.php` tersedia dan class-nya sesuai namespace.
- [ ] Source `Models/m_kary/Custom.php` pada environment target memuat scope `higherlevel` yang sama.
- [ ] Tidak ada credential, dump database, atau data pribadi yang disalin ke workspace.

### Smoke test UI/API

- [ ] Form `t_perdin` dapat dibuka.
- [ ] Setelah karyawan dipilih, request pilihan Jabatan berhasil.
- [ ] Request pilihan Atasan tidak lagi menghasilkan HTTP 500.
- [ ] Kandidat Atasan dikembalikan dengan field `id` dan `nama_lengkap`.
- [ ] Karyawan tanpa level ditangani sebagai daftar kosong atau perilaku bisnis yang telah disepakati, bukan fatal error.
- [ ] Penyimpanan `t_perdin` menghasilkan `m_atasan_id` bigint yang valid.
- [ ] Edit, detail, dan listing `t_perdin` tetap berjalan setelah perubahan.

### Regression test

- [ ] Listing `m_kary` tidak kembali terkena `bigint = json` pada join `m_jam_kerja`.
- [ ] Scope lain pada `m_kary` yang memakai level (`lowerlevel`, `Bawahan`, export) tetap dapat dipanggil.
- [ ] Pemilihan Atasan untuk divisi induk dan divisi tanpa parent berjalan sesuai aturan.
- [ ] `t_pengajuan_perdin` yang memakai tarif/level tidak terkena regresi.

## Kesimpulan

Prioritas pertama adalah menyamakan dan memuat ulang source `Models/m_level_posisi/Custom.php` pada environment yang menghasilkan error. Source lokal sudah menunjukkan patch relasi yang dibutuhkan, sehingga belum ada alasan berbasis bukti untuk mengubah migration, join, atau tipe kolom database. Setelah error method terselesaikan, baru lakukan validasi data level, kandidat atasan, dan efek samping join `t_perdin`.

