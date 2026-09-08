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
            ->orWhereRaw("UPPER(m_general.value) = 'LOGO'")
            ->orWhereRaw("UPPER(m_media.kode) LIKE '%LOGO%'");
      })
      ->orderByRaw("CASE WHEN m_media.kode = 'LOGO_UTAMA' THEN 0 ELSE 1 END")
      ->orderBy('m_media.id', 'asc')
      ->first();

  $logoSrc = ($mediaLogo && !empty($mediaLogo->file_path)) 
      ? (str_starts_with($mediaLogo->file_path, 'http') ? $mediaLogo->file_path : asset(ltrim($mediaLogo->file_path, '/')))
      : asset('images/logo.png');

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
      margin: 8mm 10mm 8mm 10mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 10pt;
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
      border: 1.5px solid #000;
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
    <div style="border: 1px solid #e53e3e; background: #fff5f5; color: #c53030; padding: 20px; text-align: center; border-radius: 6px;">
      <h3>Data Hasil Test Lamaran Kerja Tidak Ditemukan</h3>
      <p>Silakan periksa kembali parameter ID transaksi yang diberikan.</p>
    </div>
  @else

  <!-- KOTAK SURAT UTAMA TEMPRINA (FM-HRD-REC-002) -->
  <table class="main-table" width="100%" cellpadding="0" cellspacing="0">
    
    <!-- 1. HEADER 3 KOLOM -->
    <tr>
      <td style="width: 25%; text-align: center; vertical-align: middle; padding: 8px 10px; border-right: 1px solid #000; border-bottom: 1px solid #000;">
        <img src="{{ $logoSrc }}" alt="Logo Temprina" style="max-height: 48px; max-width: 140px; display: block; margin: 0 auto; object-fit: contain;">
      </td>
      <td style="width: 45%; text-align: center; vertical-align: middle; padding: 10px 5px; font-size: 12pt; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; letter-spacing: 0.5px;">
        FORM EVALUASI & HASIL TEST SELEKSI KARYAWAN
      </td>
      <td style="width: 30%; vertical-align: middle; padding: 6px 10px; border-bottom: 1px solid #000;">
        <table style="width: 100%; border: none; border-collapse: collapse;">
          <tr>
            <td style="width: 55px; border: none; padding: 1px 0; font-size: 9.5pt;">No. Form</td>
            <td style="width: 8px; border: none; padding: 1px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 1px 0; font-weight: bold; font-size: 9.5pt;">FM-HRD-REC-002</td>
          </tr>
          <tr>
            <td style="width: 55px; border: none; padding: 1px 0; font-size: 9.5pt;">No. Urut</td>
            <td style="width: 8px; border: none; padding: 1px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 1px 0; font-weight: bold; font-size: 9.5pt; white-space: nowrap;">{{ $data->nomor ?? '-' }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 2. SUB HEADER TANGGAL -->
    <tr>
      <td colspan="3" style="padding: 5px 12px; border-bottom: 1px solid #000; font-size: 9.5pt;">
        <table style="width: 100%; border: none; border-collapse: collapse;">
          <tr>
            <td style="width: 50%; border: none; padding: 0; font-size: 9.5pt;">
              <strong>Tanggal Cetak</strong> : {{ $tglCetak }}
            </td>
            <td style="width: 50%; border: none; padding: 0; text-align: right; font-size: 9.5pt;">
              <strong>Rev. / Tgl</strong> : 00 / -
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 3. BODY IDENTITAS PELAMAR & POSISI -->
    <tr>
      <td colspan="3" style="padding: 10px 14px; border-bottom: 1px solid #000;">
        
        <table style="width: 100%; border: none; border-collapse: collapse;">
          <tr>
            <!-- KOLOM KIRI: DATA PELAMAR -->
            <td style="width: 50%; vertical-align: top; border: none; padding-right: 15px;">
              <div style="font-weight: bold; font-size: 10pt; text-decoration: underline; margin-bottom: 6px; text-transform: uppercase;">
                A. Identitas Calon Karyawan :
              </div>
              <table style="width: 100%; border: none; border-collapse: collapse;">
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Nama Lengkap</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt; font-weight: bold;">{{ $data->nama_pelamar ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">No. KTP / NIK</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->ktp_no ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Tempat / Tgl Lahir</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $ttl }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Jenis Kelamin</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->jenis_kelamin ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">No. Telepon / HP</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->telp ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Email</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->email ?? '-' }}</td>
                </tr>
              </table>
            </td>

            <!-- KOLOM KANAN: DATA LOWONGAN & SELEKSI -->
            <td style="width: 50%; vertical-align: top; border: none; padding-left: 15px; border-left: 1px dashed #ccc;">
              <div style="font-weight: bold; font-size: 10pt; text-decoration: underline; margin-bottom: 6px; text-transform: uppercase;">
                B. Data Seleksi & Posisi :
              </div>
              <table style="width: 100%; border: none; border-collapse: collapse;">
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Posisi Dilamar</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt; font-weight: bold;">{{ $data->loker_title ?? $data->posisi_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Divisi</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->divisi_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Unit / Perusahaan</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">
                    {{ $data->comp_nama ?? 'PT Temprina Media Grafika' }}
                    @if(!empty($data->branch_nama)) ({{ $data->branch_nama }}) @endif
                  </td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Tahapan Terakhir</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt; font-weight: bold;">{{ $data->tahapan_nama ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Status Akhir</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt; font-weight: bold;">
                    @php
                      $status = strtoupper($data->status ?? 'PENDING');
                    @endphp
                    @if($status == 'DITERIMA')
                      <span style="color: #2b6cb0;">DITERIMA (LULUS SELEKSI)</span>
                    @elseif($status == 'TIDAK DITERIMA')
                      <span style="color: #c53030;">TIDAK DITERIMA</span>
                    @else
                      <span>{{ $status }}</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td style="width: 130px; border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">Evaluator / Recruiter</td>
                  <td style="width: 10px; border: none; padding: 3px 0; text-align: center; font-weight: bold; font-size: 9.5pt;">:</td>
                  <td style="border: none; padding: 3px 0; font-size: 9.5pt;">{{ $data->creator_name ?? '-' }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- CATATAN / DESKRIPSI EVALUATOR -->
        @if(!empty($data->deskripsi))
        <div style="margin-top: 10px; padding: 6px 10px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px;">
          <strong style="font-size: 9.5pt;">Catatan / Rekomendasi Seleksi:</strong>
          <p style="margin: 2px 0 0 0; font-size: 9.5pt; color: #333;">{{ $data->deskripsi }}</p>
        </div>
        @endif

        <!-- TABEL RINCIAN PENGUJIAN & SKOR -->
        <div style="font-weight: bold; font-size: 10pt; margin-top: 12px; margin-bottom: 4px; text-transform: uppercase;">
          C. Rincian Penilaian & Nilai Test :
        </div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 4px; margin-bottom: 6px;" cellpadding="5">
          <thead>
            <tr style="background-color: #f2f2f2;">
              <th style="width: 6%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">No</th>
              <th style="width: 22%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Tanggal Ujian</th>
              <th style="width: 38%; border: 1px solid #000; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Nama / Jenis Test</th>
              <th style="width: 18%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Nilai / Skor Test</th>
              <th style="width: 16%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Lampiran</th>
            </tr>
          </thead>
          <tbody>
            @forelse($details as $idx => $det)
              <tr>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt;">
                  {{ !empty($det->tanggal) ? date('d/m/Y', strtotime($det->tanggal)) : '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold;">
                  {{ $det->nama_tes ?? '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold;">
                  {{ !is_null($det->nilai_tes) ? $det->nilai_tes : '-' }}
                </td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9pt; color: #555;">
                  {{ !empty($det->dokumen) ? 'Ada Berkas' : '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="border: 1px solid #000; text-align: center; font-style: italic; color: #666; font-size: 9.5pt; padding: 10px;">
                  Belum ada data pengujian nilai yang tercatat.
                </td>
              </tr>
            @endforelse
          </tbody>
          @if(count($details) > 0)
          <tfoot>
            <tr style="background-color: #fafafa; font-weight: bold;">
              <td colspan="3" style="border: 1px solid #000; text-align: right; font-size: 9.5pt; padding-right: 10px;">
                RATA-RATA SKOR PENILAIAN :
              </td>
              <td style="border: 1px solid #000; text-align: center; font-size: 10pt; font-weight: bold; color: #1a365d;">
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
        <table style="width: 100%; border-collapse: collapse; border: none;" cellpadding="3">
          <tr>
            <td style="width: 18%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 24px;">Halaman</div>
              <div style="font-weight: bold; font-size: 10pt;">1 / 1</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Dibuat :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 40px;">(Recruiter / Penguji)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">({{ $data->creator_name ?? 'HC Officer' }})</div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">Tgl: {{ $tglDibuat }}</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Disetujui :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 40px;">(User / Manager Terkait)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">( ..................................... )</div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">Tgl: ........................</div>
            </td>
            <td style="width: 27.34%; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Diketahui :</div>
              <div style="font-size: 8.5pt; color: #666; margin-bottom: 40px;">(Head of Human Capital)</div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">( ..................................... )</div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">Tgl: ........................</div>
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