# JOBLIST - HRIS Temprina
Spesifik Area Kerja: **Master Komponen Penilaian Karyawan (`m_penilaian` / `m_assessment_kary`)**
Diperbarui: 2026-08-26

---

## Legenda Status
- [ ] Belum dikerjakan
- [/] Sedang dikerjakan
- [x] Selesai
- [!] Error runtime
- [>] Dikerjakan oleh programmer / anak magang lain

---

## 🎯 Fokus Pekerjaan Aktif: `m_penilaian` (Komponen Penilaian Karyawan)

### Feedback Klien yang Dikerjakan:
1. **[x] Batasan: hanya bisa memilih 1 komponen per kategori**
   - **Analisa**: Dropdown kategori pada tabel detail indikator (`m_assessment_kary_d`) tidak boleh memiliki nilai kategori (`kategori`) yang sama di lebih dari 1 baris.
   - **Solusi**:
     - *Frontend*: Validasi pada `Javascript/m_penilaian.js` (fungsi `onSave`) mengecek duplikasi kategori dalam `detailArr` dan menampilkan alert peringatan jika ditemukan duplikat.
     - *Backend*: Validasi pada `Models/m_assessment_kary/Custom.php` (`createBefore` & `updateBefore`) untuk memastikan request tidak memuat kategori duplikat sebelum diinsert/update ke database.
   - **File**: `Javascript/m_penilaian.js`, `Models/m_assessment_kary/Custom.php`.

2. **[x] Level belum dapat ditampilkan datanya di landing**
   - **Analisa**: Kolom `LEVEL` di landing table mengambil field `level`, namun relasi level berada di tabel perantara `m_assessment_kary_d_level` join `m_level_posisi`. Jika tidak di-transform, nilainya kosong/null.
   - **Solusi**:
     - *Backend*: Pada `Models/m_assessment_kary/Custom.php`, fungsi `transformRowData(array $row)` mengambil ID assessment (menggunakan fallback `$row['this.id'] ?? $row['id']`) lalu query `m_assessment_kary_d_level` join `m_level_posisi` dan mengisi `$data['level'] = implode(', ', $levelNames)`.
     - *Frontend*: Parameter `landing.api.params.transform = true` aktif di `Javascript/m_penilaian.js`.
   - **File**: `Models/m_assessment_kary/Custom.php`, `Javascript/m_penilaian.js`.

3. **[x] Ketika data dilihat, level yang tadi sudah berhasil disimpan malah menampilkan NaN**
   - **Analisa**: Saat mode View/Edit (Read), data detail level dari API backend berupa array of object atau string non-numeric. Ketika di-binding ke `<FieldSelect multiple="true">` dengan `valueField="id"`, pemrosesan internal atau casting menghasilkan nilai `NaN`.
   - **Solusi**:
     - *Frontend*: Pada `Javascript/m_penilaian.js` di hook `onBeforeMount` (saat read), normalisasi `initialValues.m_assessment_kary_d_level` menjadi array integer ID murni.
     - *Frontend Template*: Di `Blades/m_penilaian.blade.php`, sesuaikan event `@input` pada `FieldSelect` level agar selalu menghasilkan array of integer ID yang valid.
     - *Payload onSave*: Di `Javascript/m_penilaian.js`, mapping `m_assessment_kary_d_level` saat kirim ke backend terstruktur rapi: `[{ m_assessment_kary_id, m_level_posisi_id, creator_id }]`.
   - **File**: `Blades/m_penilaian.blade.php`, `Javascript/m_penilaian.js`.

---

## 📁 File yang Perlu Diedit (Aman dari Konflik Server):
1. `Blades/m_penilaian.blade.php` (Binding FieldSelect Level & table detail)
2. `Javascript/m_penilaian.js` (Validasi kategori, normalisasi level read, payload save)
3. `Models/m_assessment_kary/Custom.php` (transformRowData level & validasi duplikat kategori)

---

## 👥 Modul Lain (Dikerjakan oleh Tim)
- [>] `t_perdin` (Error memilih atasan & notifikasi approval)
- [>] `t_rencana_perdin` (Error tarif perdin & notifikasi approval)
- [>] `t_penyelesaian_perdin` (Error kasbon)
- [>] `t_efektifitas_pelatihan` (Fix bigint null, filter atasan peserta)
- [>] `t_req_pelatihan` / `t_request_pelatihan` (Form & approval workflow)
- [>] `t_realisasi_pelatihan` (Auto divisi & karyawan)
- [>] `t_evaluasi_pelatihan` (Tema landing, peserta, notif)
- [>] `m_karyawan` (Dropdown jabatan & hide atasan)
- [>] `t_penilaian_kary` (Auto-fill level & divisi)
- [>] `t_kbs` (Nama karyawan di landing)
- [>] `m_prog_pelatihan` (Kolom sasaran landing)