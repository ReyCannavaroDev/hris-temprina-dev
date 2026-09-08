@php
  $req = app()->request;
  $id = $req->id;

  $data = \DB::table('t_hasil_tes as t')
      ->leftJoin('t_pelamar as tp', 'tp.id', '=', 't.t_pelamar_id')
      ->leftJoin('m_general as mg_jk', 'mg_jk.id', '=', 'tp.jk_id')
      ->leftJoin('t_loker as tl', 'tl.id', '=', 't.t_loker_id')
      ->leftJoin('m_comp as mc', 'mc.id', '=', 'tl.m_comp_id')
      ->leftJoin('m_subcomp as ms', 'ms.id', '=', 'tl.m_subcomp_id')
      ->leftJoin('m_branch as mb', 'mb.id', '=', 'tl.m_branch_id')
      ->leftJoin('m_divisi as md', 'md.id', '=', 'tl.m_divisi_id')
      ->leftJoin('m_general as mg_div', 'mg_div.id', '=', 'md.name')
      ->leftJoin('m_posisi as mp', 'mp.id', '=', 'tl.m_posisi_id')
      ->leftJoin('m_general as mg_thp', 'mg_thp.id', '=', 't.tahapan_id')
      ->leftJoin('default_users as u', 'u.id', '=', 't.creator_id')
      ->where('t.id', $id)
      ->select(
          't.*',
          \DB::raw("COALESCE(tp.nama_lengkap, CONCAT(COALESCE(tp.nama_depan, ''), ' ', COALESCE(tp.nama_belakang, ''))) as nama_pelamar"),
          'tp.ktp_no',
          'tp.telp',
          'tp.email',
          'tp.tempat_lahir',
          'tp.tgl_lahir',
          'mg_jk.value as jenis_kelamin',
          'tl.title as loker_title',
          'mc.name as comp_nama',
          'ms.name as subcomp_nama',
          'mb.name as branch_nama',
          \DB::raw("COALESCE(mg_div.value, md.name_old, md.nomor, '-') as divisi_nama"),
          'mp.name as posisi_nama',
          'mg_thp.value as tahapan_nama',
          'u.name as creator_name'
      )
      ->first();

  $details = [];
  $avgNilai = 0;
  if ($data) {
      $details = \DB::table('t_hasil_tes_det')
          ->where('t_hasil_tes_id', $id)
          ->orderBy('id', 'asc')
          ->get();

      if (count($details) > 0) {
          $validScores = $details->filter(function($d) { return !is_null($d->nilai_tes) && is_numeric($d->nilai_tes); });
          if ($validScores->count() > 0) {
              $avgNilai = round($validScores->avg('nilai_tes'), 2);
          }
      }
  }

  // Logo resolver dari m_media
  $mediaLogo = \DB::table('m_media')
      ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
      ->where('m_media.is_active', true)
      ->where(function($q) use ($data) {
          if ($data && !empty($data->m_comp_id)) {
              $q->where('m_media.m_comp_id', $data->m_comp_id);
          }
          $q->orWhere('m_media.kode', 'LOGO_UTAMA')
            ->orWhere('m_media.kode', 'LOGO-TMG')
            ->orWhere('m_media.kode', 'LOGO-IMG')
            ->orWhereRaw("UPPER(m_general.value) = 'LOGO'")
            ->orWhereRaw("UPPER(m_media.kode) LIKE '%LOGO%'");
      })
      ->orderByRaw("CASE WHEN m_media.kode = 'LOGO_UTAMA' THEN 0 WHEN m_media.kode LIKE '%LOGO%' THEN 1 ELSE 2 END")
      ->orderBy('m_media.id', 'asc')
      ->first();

  $resolveLogoFile = function($rawPath) {
      if (empty($rawPath)) return '';

      // Bersihkan protokol & domain jika tersimpan full URL
      $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
      $clean = ltrim($clean, '/');

      $candidates = [
          $clean,
          'uploads/m_media/' . $clean,
          'uploads/' . $clean,
          'storage/' . $clean,
          'images/' . $clean
      ];

      foreach ($candidates as $cand) {
          $full = function_exists('public_path') ? public_path($cand) : (function_exists('base_path') ? base_path('public/' . $cand) : ('/opt/www/hris/public/' . $cand));
          if ($full && file_exists($full) && is_file($full)) {
              return $full;
          }
      }

      $default = function_exists('public_path') ? public_path('images/logo.png') : (function_exists('base_path') ? base_path('public/images/logo.png') : '/opt/www/hris/public/images/logo.png');
      if ($default && file_exists($default) && is_file($default)) {
          return $default;
      }

      return '';
  };

  $logoPath = $mediaLogo ? $resolveLogoFile($mediaLogo->file_path) : $resolveLogoFile('images/logo.png');

  $tglCetak = date('d/m/Y');
  $tglDibuat = $data && !empty($data->created_at) ? date('d/m/Y', strtotime($data->created_at)) : date('d/m/Y');
  $ttl = ($data && !empty($data->tempat_lahir) ? $data->tempat_lahir : '') . ($data && !empty($data->tgl_lahir) ? ', ' . date('d/m/Y', strtotime($data->tgl_lahir)) : '-');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Hasil Test Seleksi Karyawan - {{ $data->nomor ?? '-' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm 14mm 12mm 14mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 10pt;
      line-height: 1.4;
      color: #000;
      margin: 0;
      padding: 0;
      background-color: #fff;
    }
    .print-container {
      width: 100%;
      margin: 0 auto;
    }
    .main-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
    }
    .main-table td, .main-table th {
      border: 1px solid #000;
      vertical-align: middle;
    }
  </style>
</head>
<body>

<div class="print-container">

  @if(!$data)
    <div style="border: 1px solid #e53e3e; background: #fff5f5; color: #c53030; padding: 24px; text-align: center; border-radius: 6px;">
      <h3 style="margin-bottom: 8px;">Data Hasil Test Lamaran Kerja Tidak Ditemukan</h3>
      <p style="margin: 0;">Silakan periksa kembali parameter ID transaksi yang diberikan.</p>
    </div>
  @else

  <!-- KOTAK SURAT UTAMA TEMPRINA (FM-HRD-REC-002) -->
  <table class="main-table" width="100%" cellpadding="0" cellspacing="0">
    
    <!-- 1. HEADER 3 KOLOM -->
    <tr>
      <td style="width: 22%; text-align: center; vertical-align: middle; padding: 6px 4px; border-right: 1px solid #000; border-bottom: 1px solid #000;">
        @if(!empty($logoPath) && file_exists($logoPath))
          <img src="{{ $logoPath }}" alt="Logo Temprina" height="30" border="0" style="height: 30px; vertical-align: middle;">
        @else
          <div style="font-size: 12pt; font-weight: bold; color: #1e3a8a; line-height: 1.1;">TEMPRINA<br><span style="font-size: 8pt; color: #374151; font-weight: normal;">MEDIA GRAFIKA</span></div>
        @endif
      </td>
      <td style="width: 42%; text-align: center; vertical-align: middle; padding: 12px 6px; font-size: 12pt; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; letter-spacing: 0.5px;">
        FORM EVALUASI & HASIL TEST SELEKSI KARYAWAN
      </td>
      <td style="width: 36%; vertical-align: middle; padding: 6px 12px; border-bottom: 1px solid #000;">
        <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width: 60px; border: none; padding: 2px 0; font-size: 9pt;">No. Form</td>
            <td style="width: 10px; border: none; padding: 2px 0; font-size: 9pt;">:</td>
            <td style="border: none; padding: 2px 0; font-weight: bold; font-size: 9pt; white-space: nowrap;">FM-HRD-REC-002</td>
          </tr>
          <tr>
            <td style="width: 60px; border: none; padding: 2px 0; font-size: 9pt;">No. Urut</td>
            <td style="width: 10px; border: none; padding: 2px 0; font-size: 9pt;">:</td>
            <td style="border: none; padding: 2px 0; font-weight: bold; font-size: 9pt; white-space: nowrap;">{{ $data->nomor ?? '-' }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 2. SUB HEADER TANGGAL (TANPA NESTED TABLE AGAR TIDAK KEPOTONG DI TCPDF) -->
    <tr>
      <td colspan="2" style="padding: 6px 14px; border-bottom: 1px solid #000; border-right: none; font-size: 9.5pt; background-color: #fafafa; line-height: 1.5; vertical-align: middle;">
        <strong>Tanggal Cetak :</strong> &nbsp;{{ $tglCetak }}
      </td>
      <td style="padding: 6px 14px; border-bottom: 1px solid #000; border-left: none; text-align: right; font-size: 9.5pt; background-color: #fafafa; line-height: 1.5; vertical-align: middle;">
        <strong>Rev. / Tgl :</strong> &nbsp;00 / -
      </td>
    </tr>

    <!-- 3. BODY IDENTITAS PELAMAR & POSISI -->
    <tr>
      <td colspan="3" style="padding: 16px 20px; border-bottom: 1px solid #000;">
        
        <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
          <tr>
            <!-- KOLOM KIRI: DATA PELAMAR -->
            <td style="width: 50%; vertical-align: top; border: none; padding-right: 16px;">
              <div style="font-weight: bold; font-size: 10pt; text-decoration: underline; margin-bottom: 10px; text-transform: uppercase;">
                A. Identitas Calon Karyawan :
              </div>
              <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Nama Lengkap</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt; font-weight: bold;">{{ $data->nama_pelamar ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">No. KTP / NIK</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->ktp_no ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Tempat / Tgl Lahir</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $ttl }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Jenis Kelamin</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->jenis_kelamin ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">No. Telepon / HP</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->telp ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Email</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->email ?? '-' }}</td>
                </tr>
              </table>
            </td>

            <!-- KOLOM KANAN: DATA LOWONGAN & SELEKSI -->
            <td style="width: 50%; vertical-align: top; border: none; padding-left: 16px; border-left: 1px dashed #ccc;">
              <div style="font-weight: bold; font-size: 10pt; text-decoration: underline; margin-bottom: 10px; text-transform: uppercase;">
                B. Data Seleksi & Posisi :
              </div>
              <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Posisi Dilamar</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt; font-weight: bold; color: #1e3a8a;">{{ $data->loker_title ?? $data->posisi_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Divisi</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->divisi_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Unit / Perusahaan</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">
                    {{ $data->comp_nama ?? 'PT Temprina Media Grafika' }}
                    @if(!empty($data->branch_nama)) ({{ $data->branch_nama }}) @endif
                  </td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Tahapan Terakhir</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt; font-weight: bold;">{{ $data->tahapan_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Status Akhir</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt; font-weight: bold;">
                    @php
                      $status = strtoupper($data->status ?? 'PENDING');
                    @endphp
                    @if($status == 'DITERIMA')
                      <span style="color: #047857;">DITERIMA (LULUS SELEKSI)</span>
                    @elseif($status == 'TIDAK DITERIMA')
                      <span style="color: #b91c1c;">TIDAK DITERIMA</span>
                    @else
                      <span>{{ $status }}</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 5px 0; font-weight: bold; font-size: 9.5pt;">Evaluator / Recruiter</td>
                  <td style="width: 10px; border: none; padding: 5px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 5px 0; font-size: 9.5pt;">{{ $data->creator_name ?? '-' }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- CATATAN / DESKRIPSI EVALUATOR -->
        @if(!empty($data->deskripsi))
        <div style="margin-top: 14px; padding: 10px 14px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px;">
          <strong style="font-size: 9.5pt;">Catatan / Rekomendasi Seleksi:</strong>
          <p style="margin: 4px 0 0 0; font-size: 9.5pt; color: #333; line-height: 1.45;">{{ $data->deskripsi }}</p>
        </div>
        @endif

        <!-- TABEL RINCIAN PENGUJIAN & SKOR -->
        <div style="font-weight: bold; font-size: 10pt; margin-top: 16px; margin-bottom: 8px; text-transform: uppercase;">
          C. Rincian Penilaian & Nilai Test :
        </div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 6px; margin-bottom: 8px;" cellpadding="6">
          <thead>
            <tr style="background-color: #f3f4f6;">
              <th style="width: 6%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 4px; text-transform: uppercase;">No</th>
              <th style="width: 22%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 6px; text-transform: uppercase;">Tanggal Ujian</th>
              <th style="width: 38%; border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 8px 8px; text-transform: uppercase;">Nama / Jenis Test</th>
              <th style="width: 18%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 4px; text-transform: uppercase;">Nilai / Skor Test</th>
              <th style="width: 16%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 4px; text-transform: uppercase;">Lampiran</th>
            </tr>
          </thead>
          <tbody>
            @forelse($details as $idx => $det)
              <tr>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; padding: 7px 4px;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; padding: 7px 4px;">
                  {{ !empty($det->tanggal) ? date('d/m/Y', strtotime($det->tanggal)) : '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 7px 8px;">
                  {{ $det->nama_tes ?? '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 7px 4px;">
                  {{ !is_null($det->nilai_tes) ? $det->nilai_tes : '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9pt; color: #555; padding: 7px 4px;">
                  {{ !empty($det->dokumen) ? 'Ada Berkas' : '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="border: 1px solid #000; text-align: center; font-style: italic; color: #666; font-size: 9.5pt; padding: 16px;">
                  Belum ada data pengujian nilai yang tercatat.
                </td>
              </tr>
            @endforelse
          </tbody>
          @if(count($details) > 0)
          <tfoot>
            <tr style="background-color: #fafafa; font-weight: bold;">
              <td colspan="3" style="border: 1px solid #000; text-align: right; font-size: 9.5pt; padding: 8px 10px;">
                RATA-RATA SKOR PENILAIAN :
              </td>
              <td style="border: 1px solid #000; text-align: center; font-size: 10pt; font-weight: bold; color: #1e3a8a; padding: 8px 4px;">
                {{ $avgNilai > 0 ? $avgNilai : '-' }}
              </td>
              <td style="border: 1px solid #000;"></td>
            </tr>
          </tfoot>
          @endif
        </table>

      </td>
    </tr>

    <!-- 4. FOOTER TANDA TANGAN (4 KOLOM) -->
    <tr>
      <td colspan="3" style="padding: 0;">
        <table style="width: 100%; border-collapse: collapse; border: none;" cellpadding="6">
          <tr>
            <td style="width: 18%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 6px 16px 6px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 36px;">Halaman</div>
              <div style="font-weight: bold; font-size: 10pt;">1 / 1</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Dibuat :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 42px;">(Recruiter / Penguji)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">({{ $data->creator_name ?? 'HC Officer' }})</div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">Tgl: {{ $tglDibuat }}</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Disetujui :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 42px;">(User / Manager Terkait)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">( ..................................... )</div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">Tgl: ........................</div>
            </td>
            <td style="width: 27.34%; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Diketahui :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 42px;">(Head of Human Capital)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">( ..................................... )</div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">Tgl: ........................</div>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
  @endif

</div>

</body>
</html>