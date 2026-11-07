# DOKUMEN PERENCANAAN TEKNIS: PERBAIKAN ABSENSI & JADWAL KERJA HRIS TEMPRINA
**Target Sistem:** HRIS PT Temprina Media Grafika  
**Tanggal Rilis Dokumen:** 7 November 2026  
**Penulis:** Technical Lead / Core Developer HRIS Temprina  
**Tujuan Dokumen:** Panduan teknis komprehensif implementasi perbaikan modul Presensi Absensi, Laporan Absensi Karyawan, Jadwal Kerja, dan Lembur bagi AI Coding Agent dan Tim Pengembang.

---

## DAFTAR ISI
1. [Ringkasan Eksekutif & Aturan Kerja](#ringkasan-eksekutif--aturan-kerja)
2. [Fase 1: Perbaikan Bug Hari Libur Bergeser & Urutan Jadwal Kerja (Paling Mendesak)](#fase-1-perbaikan-bug-hari-libur-bergeser--urutan-jadwal-kerja-paling-mendesak)
3. [Fase 2: Snapshot Jam Kerja di Presensi & Proteksi Master Jadwal](#fase-2-snapshot-jam-kerja-di-presensi--proteksi-master-jadwal)
4. [Fase 3: Penanganan Shift Malam Lintas Hari (Overnight / Cross-Day Shift)](#fase-3-penanganan-shift-malam-lintas-hari-overnight--cross-day-shift)
5. [Fase 4: Penanganan Lembur Lintas Hari & Integrasi Laporan](#fase-4-penanganan-lembur-lintas-hari--integrasi-laporan)
6. [Matriks File yang Diubah](#matriks-file-yang-diubah)

---

## RINGKASAN EKSEKUTIF & ATURAN KERJA

### Konteks Bisnis
Berdasarkan evaluasi meeting Zoom bersama manajemen Temprina dan koordinasi dengan Pak Turikan Iqbal (HC/IT Temprina), sistem HRIS mengalami beberapa kendala operasional kritikal:
1. Hari libur karyawan (khususnya shift pabrik berotasi, misal libur hari Selasa) pada Laporan Absensi Karyawan tercetak acak-acakan dan bergeser ke hari Senin.
2. Tampilan urutan hari pada form Jadwal Kerja Karyawan terbalik di frontend.
3. Laporan absensi membaca master jadwal kerja secara dinamis tanpa mengunci data historis, sehingga perubahan jadwal kerja di masa depan berisiko merusak laporan absensi masa lalu.
4. Shift kerja malam lintas hari (contoh: Senin 20:00 s/d Selasa 08:00) menghasilkan durasi kerja negatif, status kehadiran keliru pada hari berikutnya, dan memicu error *"Anda sudah checkin hari ini"* saat karyawan masuk lagi pada malam harinya.

### Aturan Kerja Workspace (Wajib Dipatuhi)
1. **Dilarang Mengedit File `Basic.php`:** File `Models/<nama_model>/basic.php` digenerate otomatis oleh core generator. Seluruh penyesuaian query, hook, `$appends`, `$joins`, atau relasi **wajib diletakkan di `custom.php`**.
2. **Perubahan Skema Melalui `alter.php`:** Penambahan kolom database dilakukan pada file `Models/<nama_model>/alter.php` menggunakan kaidah Migration Laravel.
3. **Pola Generator:** Pertahankan konvensi hook generator (`transformRowData`, `createBefore`, `createAfter`, `updateBefore`, `updateAfter`, `deleteBefore`).
4. **Keamanan & Kredensial:** Dilarang mencantumkan credential, token, `.env`, atau data pribadi karyawan ke dalam kode.

---

## FASE 1: PERBAIKAN BUG HARI LIBUR BERGESER & URUTAN JADWAL KERJA (PALING MENDESAK)

### 1.1. Deskripsi Masalah & Penyebab
1. **Penyebab Libur Bergeser ke Hari Senin:**
   * Pada `Models/presensi_absensi/Custom.php` line 1740 dan template laporan `Blades/web_report_karyawan_absensi_detail.blade.php`:
     Pencocokan hari menggunakan formula:
     ```php
     $q->where('day_num', \Carbon::parse($date)->dayOfWeek + 1);
     ```
     Standar Carbon `dayOfWeek` bernilai: `0 = Minggu, 1 = Senin, 2 = Selasa, dst`.
     Ketika `$date` adalah hari Senin (nilai `1`), maka formula menghasilkan `1 + 1 = 2` (indeks hari Selasa). Akibatnya, setting hari Selasa (yang ditandai **LIBUR**) justru terpasang pada hari Senin.
   * Pencocokan berbasis nama teks hari `->where('day', $tanggal->translatedFormat('l'))` rentan perbedaan locale (`Senin` vs `Monday`) dan case sensitivity.
2. **Penyebab Urutan Hari Terbalik di UI:**
   * Pada `Javascript/t_jadwal_kerja_kary.js` line 189:
     ```javascript
     detailArr.value = Array.isArray(jadwalJson.data?.t_jadwal_kerja_det_hari)
       ? (jadwalJson.data.t_jadwal_kerja_det_hari || []).slice().reverse().map(...)
       : [];
     ```
     Pemanggilan `.slice().reverse()` sengaja membalik urutan hari sehingga Minggu tampil di atas dan Senin di bawah.
3. **Penyebab Data Detail Kosong / Tidak Sinkron:**
   * Pada `Models/t_jadwal_kerja_n/custom.php` method `saveDetails()`: data hanya di-insert ke tabel `t_jadwal_kerja_det_hari`, sedangkan query laporan membaca model `t_jadwal_kerja_d_hari_n`.

---

### 1.2. Solusi Teknis & Rincian Perubahan Kode

#### A. File: `Blades/web_report_karyawan_absensi_detail.blade.php` & `web_report_karyawan_absensi_rekap.blade.php`
* **Penyebab:** Pencocokan jadwal hari menggunakan nama hari bahasa lokal atau indeks non-ISO.
* **Perubahan Kode:**
  Ganti pencarian baris jadwal kerja hari menggunakan ISO Day-of-Week (`dayOfWeekIso`: 1 = Senin, 2 = Selasa, ..., 7 = Minggu):
  ```php
  // SEBELUM:
  $jam_kerja_row = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
      ->where('day', $tanggal->translatedFormat('l'))
      ->first();

  // SESUDAH:
  $dayNumIso = $tanggal->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
  $jam_kerja_row = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
      ->where(function($q) use ($dayNumIso, $tanggal) {
          $q->where('day_num', $dayNumIso)
            ->orWhere('day', $tanggal->translatedFormat('l'))
            ->orWhere('day', $tanggal->format('l'));
      })
      ->first();
  ```
  Dan pastikan jika `$jam_kerja_row?->tipe_hari === 'LIBUR'`, maka status kehadiran hari tersebut diakui sebagai **LIBUR** (bukan NOT ATTEND / Alpa) serta tidak dikenakan denda absen.

#### B. File: `Models/presensi_absensi/Custom.php`
* **Penyebab:** Fungsi `get_jadwal_kerja()` menggunakan `dayOfWeek + 1`.
* **Perubahan Kode:**
  ```php
  // SEBELUM:
  $q->where('m_jam_kerja_id', $matched_id->id)->where('day_num', \Carbon::parse($date)->dayOfWeek + 1)

  // SESUDAH:
  $q->where('m_jam_kerja_id', $matched_id->id)->where('day_num', \Carbon::parse($date)->dayOfWeekIso)
  ```

#### C. File: `Javascript/t_jadwal_kerja_kary.js` & `Javascript/jadwal_kerja_kary_os.js`
* **Penyebab:** Array hari dibalik dengan `.reverse()`.
* **Perubahan Kode:**
  ```javascript
  // SEBELUM:
  detailArr.value = Array.isArray(jadwalJson.data?.t_jadwal_kerja_det_hari)
    ? (jadwalJson.data.t_jadwal_kerja_det_hari || []).slice().reverse().map(item => ({ ...item }))
    : [];

  // SESUDAH:
  const rawList = jadwalJson.data?.t_jadwal_kerja_det_hari || [];
  // Urutkan berdasarkan day_num ascending (1 = Senin s/d 7 = Minggu)
  detailArr.value = rawList.slice().sort((a, b) => (Number(a.day_num || 0) - Number(b.day_num || 0))).map(item => ({ ...item }));
  ```

#### D. File: `Models/t_jadwal_kerja_n/custom.php`
* **Penyebab:** Method `saveDetails()` hanya menyimpan ke `t_jadwal_kerja_det_hari`.
* **Perubahan Kode:**
  Simpan serentak ke `t_jadwal_kerja_det_hari` dan `t_jadwal_kerja_d_hari_n` dengan nilai `day_num` yang seragam (1-7):
  ```php
  private function saveDetails($id, $arrayData)
  {
      $payload = app('request')->input('t_jadwal_kerja_det_hari');
      $details = $arrayData['t_jadwal_kerja_det_hari'] ?? $payload ?? [];

      \DB::table('t_jadwal_kerja_det_hari')->where('t_jadwal_kerja_id', $id)->delete();
      \DB::table('t_jadwal_kerja_d_hari_n')->where('t_jadwal_kerja_n_id', $id)->delete();
      
      $insertsLegacy = [];
      $insertsNew = [];
      foreach ($details as $idx => $d) {
          $dayNum = !empty($d['day_num']) ? (int)$d['day_num'] : ($idx + 1);
          $dayName = $d['hari'] ?? $d['day'] ?? null;
          $tipeHari = $d['tipe_hari'] ?? 'KERJA';
          $waktuMulai = $d['waktu_mulai'] ?? $d['start'] ?? '--:--';
          $waktuAkhir = $d['waktu_akhir'] ?? $d['end'] ?? '--:--';
          $jamKerjaId = $d['m_jam_kerja_id'] ?? null;

          $insertsLegacy[] = [
              't_jadwal_kerja_id' => $id,
              'm_jam_kerja_id'    => $jamKerjaId,
              'day'               => $dayName,
              'day_num'           => $dayNum,
              'tipe_hari'         => $tipeHari,
              'waktu_mulai'       => $waktuMulai,
              'waktu_akhir'       => $waktuAkhir,
          ];

          $insertsNew[] = [
              't_jadwal_kerja_n_id' => $id,
              'm_jam_kerja_id'      => $jamKerjaId,
              'day'                 => $dayName,
              'day_num'             => $dayNum,
              'tipe_hari'           => $tipeHari,
              'waktu_mulai'         => $waktuMulai,
              'waktu_akhir'         => $waktuAkhir,
              'created_at'          => now(),
              'updated_at'          => now(),
          ];
      }

      if (!empty($insertsLegacy)) {
          \DB::table('t_jadwal_kerja_det_hari')->insert($insertsLegacy);
      }
      if (!empty($insertsNew)) {
          \DB::table('t_jadwal_kerja_d_hari_n')->insert($insertsNew);
      }
  }
  ```

### 1.3. Dampak Perubahan
* Jadwal libur karyawan (contoh: hari Selasa) terpetakan 100% tepat pada hari Selasa di Laporan Absensi.
* Tidak terjadi pergeseran ke hari Senin.
* Tampilan urutan hari di form input jadwal kerja kembali normal dari Senin sampai Minggu.

### 1.4. Langkah Pengujian
1. Masuk ke menu **Jadwal Kerja Karyawan**, buka detail jadwal salah satu karyawan. Pastikan baris urut: Senin (No 1) s/d Minggu (No 7).
2. Set hari **Selasa** sebagai `tipe_hari = LIBUR` dan hari lainnya `KERJA`. Simpan data.
3. Buka menu **Laporan Absensi Karyawan** (`l_karyawan_absensi`), pilih rentang periode yang memuat hari Senin sampai Rabu.
4. Klik Export / Preview:
   * Hari Senin wajib bertipe `KERJA`.
   * Hari Selasa wajib bertipe `LIBUR` dengan status `LIBUR` (warna hijau, denda absen Rp 0).
   * Hari Rabu wajib kembali bertipe `KERJA`.

---

## FASE 2: SNAPSHOT JAM KERJA DI PRESENSI & PROTEKSI MASTER JADWAL

### 2.1. Deskripsi Masalah & Arahan Pak Iqbal
* Sesuai arahan Pak Iqbal: *"untuk reportnya tetap mengacu pada setting jadwal kerja ya, tapi untuk penambahan kolom jam kerja di presensi absensi tetap dilakukan. Kalau nanti sudah benar, nanti bisa buat report lain yg mengacu pada kolom jam kerja yg ditambahkan itu. Yang berdasarkan jadwal kerja, harus dipastikan jadwal kerja tidak boleh dirubah atau didelete, krn akan merubah laporan"*.
* **Masalah:** Jika master jadwal diubah di masa depan, laporan absensi masa lalu bisa ikut berubah karena tidak ada jejak snapshot pada tabel `presensi_absensi`.
* **Solusi:** 
  1. Melakukan **snapshot** (mengunci ID jam kerja, detail hari, dan nama shift) ke baris tabel `presensi_absensi` saat karyawan check-in.
  2. Memberikan validasi proteksi anti-delete pada master jadwal kerja yang sudah memiliki relasi absensi.

---

### 2.2. Solusi Teknis & Rincian Perubahan Kode

#### A. File: `Models/presensi_absensi/Custom.php` (Method `custom_checkin`)
* **Perubahan Kode:**
  Saat check-in berhasil diproses, lookup jadwal kerja karyawan hari itu dan langsung kunci ke kolom tabel `presensi_absensi`:
  ```php
  // Ambil jadwal aktif karyawan
  $karyId = default_users::find($userId)?->m_kary_id;
  $dayNumIso = $now->dayOfWeekIso; // 1 = Senin ... 7 = Minggu

  $jadwal = t_jadwal_kerja_d_n::where('m_kary_id', $karyId)
      ->whereDate('start_date', '<=', $todayStr)
      ->orderByDesc('start_date')
      ->first();

  $jamKerjaRow = null;
  if ($jadwal) {
      $jamKerjaRow = t_jadwal_kerja_d_hari_n::where('t_jadwal_kerja_n_id', $jadwal->t_jadwal_kerja_n_id)
          ->where('day_num', $dayNumIso)
          ->first();
  }

  // Isi atribut snapshot
  $model->m_jam_kerja_id = $jamKerjaRow?->m_jam_kerja_id;
  $model->t_jadwal_kerja_n_id = $jadwal?->t_jadwal_kerja_n_id;
  $model->t_jadwal_kerja_d_n_id = $jadwal?->id;
  $model->t_jadwal_kerja_d_hari_n_id = $jamKerjaRow?->id;
  $model->shift = ($jamKerjaRow && $jamKerjaRow->waktu_mulai) 
      ? ($jamKerjaRow->tipe_hari . ' (' . $jamKerjaRow->waktu_mulai . ' - ' . $jamKerjaRow->waktu_akhir . ')')
      : 'REGULER';
  ```

#### B. File: `Blades/presensi_absensi.blade.php`
* **Perubahan Kode:**
  Tampilkan data jam kerja dan shift pada baris tabel presensi:
  ```html
  <td class="text-left border-1 border-gray-500 px-3">{{ item.jam_kerja || (item.waktu_mulai ? (item.waktu_mulai + ' - ' + item.waktu_akhir) : '-') }}</td>
  <td class="text-left border-1 border-gray-500 px-3">{{ item.shift || item.desc || '-' }}</td>
  ```

#### C. File: `Models/t_jadwal_kerja_n/custom.php` & `Models/t_jadwal_kerja_kary/custom.php`
* **Perubahan Kode (Proteksi Delete):**
  Tambahkan validasi di hook `deleteBefore`:
  ```php
  public function deleteBefore($model, $id)
  {
      // Cek apakah jadwal ini sudah digunakan di transaksi presensi
      $isUsed = \DB::table('presensi_absensi')
          ->where('t_jadwal_kerja_n_id', $id)
          ->orWhere('jadwal_kerja_id', $id)
          ->exists();

      if ($isUsed) {
          throw new \Exception("Jadwal kerja tidak dapat dihapus karena sudah memiliki riwayat absensi karyawan. Untuk mengganti jadwal, buat jadwal baru dengan tanggal mulai (start date) baru.");
      }

      return $id;
  }
  ```

### 2.3. Dampak Perubahan
* Data historis kehadiran karyawan terkunci permanen.
* Master jadwal kerja terproteksi dari penghapusan tidak sengaja yang dapat merusak laporan keuangan/penggajian.

### 2.4. Langkah Pengujian
1. Lakukan check-in presensi untuk seorang karyawan.
2. Cek database tabel `presensi_absensi` baris terbaru: pastikan kolom `m_jam_kerja_id`, `t_jadwal_kerja_n_id`, `t_jadwal_kerja_d_hari_n_id`, dan `shift` terisi otomatis.
3. Buka master **Jadwal Kerja**, coba hapus jadwal kerja yang bersangkutan.
4. Sistem wajib menolak dan menampilkan pesan error bahwa jadwal kerja sedang digunakan dalam riwayat absensi.

---

## FASE 3: PENANGANAN SHIFT MALAM LINTAS HARI (OVERNIGHT / CROSS-DAY SHIFT)

### 3.1. Deskripsi Masalah & Kebutuhan Lapangan
Di unit percetakan dan pabrik Temprina yang beroperasi 24 jam, banyak karyawan bekerja pada shift malam:
* Contoh: Masuk **Senin jam 20:00** dan pulang **Selasa jam 08:00**.
* **Masalah Sistem Saat Ini:**
  1. Tabel `presensi_absensi` hanya memiliki satu kolom tanggal (`tanggal:date`). Jam check-in `20:00` dan check-out `08:00` dicatat pada `tanggal = 2026-11-02`.
  2. Durasi kerja: `08:00 - 20:00` dihitung sebagai **-12 jam**.
  3. Karyawan yang pulang Selasa jam 08:00 pagi dan masuk kembali Selasa malam jam 20:00 akan terblokir pesan *"Anda sudah checkin hari ini"* karena sistem belum mengenal pemisahan sesi kerja malam.

---

### 3.2. Solusi Teknis & Rincian Perubahan Kode

#### A. File: `Models/presensi_absensi/alter.php`
* **Penambahan Kolom Database:**
  Tambahkan kolom `checkout_date` bertipe `date` (nullable):
  ```php
  <?php

  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;

  class AlterPresensiAbsensiCheckoutDate extends Migration
  {
      public function up()
      {
          if (Schema::hasTable('presensi_absensi')) {
              Schema::table('presensi_absensi', function (Blueprint $table) {
                  if (!Schema::hasColumn('presensi_absensi', 'checkout_date')) {
                      $table->date('checkout_date')->nullable()->after('checkin_on_scope');
                  }
              });
          }
      }

      public function down()
      {
          if (Schema::hasTable('presensi_absensi')) {
              Schema::table('presensi_absensi', function (Blueprint $table) {
                  if (Schema::hasColumn('presensi_absensi', 'checkout_date')) {
                      $table->dropColumn('checkout_date');
                  }
              });
          }
      }
  }
  ```

#### B. File: `Models/presensi_absensi/Custom.php` (Method `custom_checkout`)
* **Perubahan Kode:**
  Saat checkout shift kemarin yang lintas hari berhasil, isi `checkout_date`:
  ```php
  // Pada proses update presensi aktif kemarin:
  $presensiAktif->checkout_time = $now->format('H:i:s');
  $presensiAktif->checkout_date = $todayStr; // Tanggal hari ini saat checkout
  $presensiAktif->status = 'ATTEND';
  $presensiAktif->checkout_foto = $fotoPath;
  $presensiAktif->checkout_lat = $req->lat;
  $presensiAktif->checkout_long = $req->long;
  $presensiAktif->checkout_address = $req->address;
  $presensiAktif->save();
  ```

#### C. File: `Blades/web_report_karyawan_absensi_detail.blade.php` & Logika Hitung Lembur/Telat
* **Perubahan Kode:**
  Ganti kalkulasi selisih waktu menjadi berbasis Datetime penuh:
  ```php
  // Hitung Durasi & Telat menggunakan Full Datetime
  $checkinDt = Carbon::parse($data->tanggal . ' ' . $data->checkin_time);
  $checkoutDate = $data->checkout_date ?? $data->tanggal;
  $checkoutDt = $data->checkout_time ? Carbon::parse($checkoutDate . ' ' . $data->checkout_time) : null;

  // Jika checkout lebih kecil dari checkin dan belum ada checkout_date, asumsikan lintas hari
  if ($checkoutDt && $checkoutDt->lessThan($checkinDt)) {
      $checkoutDt->addDay();
  }

  $durasiKerjaMenit = $checkoutDt ? $checkinDt->diffInMinutes($checkoutDt) : 0;
  ```

### 3.3. Dampak Perubahan
* Karyawan shift malam dapat checkout di hari berikutnya tanpa memicu status anomali.
* Karyawan dapat melakukan check-in kembali pada shift malam hari berikutnya tanpa terblokir.
* Durasi kerja dan kalkulasi lembur/denda tidak bernilai minus.

### 3.4. Langkah Pengujian
1. Buat jadwal kerja shift malam: `20:00 - 08:00`.
2. Simulasikan check-in pada hari Senin pukul 20:05. Verifikasi status `WORKING`.
3. Simulasikan checkout pada hari Selasa pukul 08:02. 
4. Verifikasi di database: `tanggal = Senin`, `checkout_date = Selasa`, `status = ATTEND`.
5. Simulasikan check-in kembali pada hari Selasa pukul 20:00. Sistem wajib mengizinkan check-in baru untuk shift hari Selasa malam.

---

## FASE 4: PENANGANAN LEMBUR LINTAS HARI & INTEGRASI LAPORAN

### 4.1. Deskripsi Masalah
* Karyawan lembur dari jam **22:00 malam sampai 04:00 subuh**.
* Tabel `t_lembur` saat ini hanya mencatat satu kolom `tanggal`.
* Selisih jam `04:00 - 22:00` menghasilkan angka negatif (-18 jam) dan merusak rekapan jam lembur pada slip gaji/laporan absensi.

---

### 4.2. Solusi Teknis & Rincian Perubahan Kode

#### A. File: `Models/t_lembur/alter.php`
* **Penambahan Kolom Database:**
  Tambahkan `tanggal_selesai` (date, nullable):
  ```php
  <?php

  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;

  class AlterTLemburTanggalSelesai extends Migration
  {
      public function up()
      {
          if (Schema::hasTable('t_lembur')) {
              Schema::table('t_lembur', function (Blueprint $table) {
                  if (!Schema::hasColumn('t_lembur', 'tanggal_selesai')) {
                      $table->date('tanggal_selesai')->nullable()->after('tanggal');
                  }
              });
          }
      }

      public function down()
      {
          if (Schema::hasTable('t_lembur')) {
              Schema::table('t_lembur', function (Blueprint $table) {
                  if (Schema::hasColumn('t_lembur', 'tanggal_selesai')) {
                      $table->dropColumn('tanggal_selesai');
                  }
              });
          }
      }
  }
  ```

#### B. File: `Models/t_lembur/custom.php` & `Blades/web_report_karyawan_absensi_detail.blade.php`
* **Perubahan Kode:**
  Hitung durasi menit lembur berbasis Datetime:
  ```php
  $tglMulai = $t_lembur->tanggal;
  $tglSelesai = $t_lembur->tanggal_selesai ?? $t_lembur->tanggal;

  $startDt = Carbon::parse($tglMulai . ' ' . $t_lembur->jam_mulai);
  $endDt   = Carbon::parse($tglSelesai . ' ' . $t_lembur->jam_selesai);

  // Jika jam selesai lebih kecil dari jam mulai dan tanggal selesai sama, otomatis geser 1 hari
  if ($endDt->lessThan($startDt) && $tglSelesai === $tglMulai) {
      $endDt->addDay();
  }

  $durasiLemburMenit = $startDt->diffInMinutes($endDt);
  ```

### 4.3. Dampak Perubahan
* Pengajuan lembur lintas malam (misal 22:00 s/d 04:00) terhitung akurat sebesar 360 menit (6 jam).
* Laporan absensi menampilkan akumulasi jam lembur yang valid untuk diteruskan ke bagian Payroll.

### 4.4. Langkah Pengujian
1. Input data pengajuan lembur: `tanggal = 2026-11-06`, `jam_mulai = 21:00`, `tanggal_selesai = 2026-11-07`, `jam_selesai = 03:00`.
2. Approve pengajuan lembur tersebut.
3. Cetak Laporan Absensi Detail karyawan terkait.
4. Pastikan kolom Lembur (Min) menampilkan angka `360` dan kolom ringkasan total lembur di bawah tabel bertambah 1.

---

## MATRIKS FILE YANG DIUBAH

| File Path | Fase | Jenis Perubahan | Catatan Kritis |
|---|:---:|---|---|
| `Blades/web_report_karyawan_absensi_detail.blade.php` | 1 & 3 | Blade View / Reporting | Ganti formula matching hari ke `dayOfWeekIso`, hitung durasi via Datetime |
| `Blades/web_report_karyawan_absensi_rekap.blade.php` | 1 | Blade View / Reporting | Sinkronisasi logika tipe hari KERJA vs LIBUR |
| `Models/presensi_absensi/Custom.php` | 1, 2, 3 | Core Model Custom | ISO day of week, snapshot jam kerja, checkout lintas hari |
| `Javascript/t_jadwal_kerja_kary.js` | 1 | Frontend JS | Hapus `.slice().reverse()`, sort `day_num` asc |
| `Javascript/jadwal_kerja_kary_os.js` | 1 | Frontend JS | Hapus pembalikan array hari |
| `Models/t_jadwal_kerja_n/custom.php` | 1 & 2 | Core Model Custom | Sinkronisasi `saveDetails()` ke 2 tabel detail, proteksi `deleteBefore` |
| `Blades/presensi_absensi.blade.php` | 2 & 3 | Blade View / Landing | Bind kolom Jam Kerja dan Jadwal, tampilkan tanggal checkout |
| `Models/presensi_absensi/alter.php` | 3 | Database Migration | Tambah kolom `checkout_date` |
| `Models/t_lembur/alter.php` | 4 | Database Migration | Tambah kolom `tanggal_selesai` |
| `Models/t_lembur/custom.php` | 4 | Core Model Custom | Hitung durasi lembur lintas hari |

