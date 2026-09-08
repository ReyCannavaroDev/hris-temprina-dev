@php
  $req = app()->request;
  $id = $req->id;

  $data = \DB::table('t_request_pelatihan as t')
      ->leftJoin('m_prog_pelatihan as mp', 'mp.id', '=', 't.m_prog_pelatihan_id')
      ->leftJoin('m_trainer as mt', 'mt.id', '=', 't.trainer_id')
      ->leftJoin('m_divisi as md', 'md.id', '=', 't.m_divisi_id')
      ->leftJoin('m_general as mg_div', 'mg_div.id', '=', 'md.name')
      ->leftJoin('m_comp as mc', 'mc.id', '=', 't.m_comp_id')
      ->leftJoin('m_subcomp as ms', 'ms.id', '=', 't.m_subcomp_id')
      ->leftJoin('m_branch as mb', 'mb.id', '=', 't.m_branch_id')
      ->leftJoin('default_users as u', 'u.id', '=', 't.creator_id')
      ->leftJoin('m_kary as uk', 'uk.id', '=', 'u.m_kary_id')
      ->leftJoin('m_divisi as ukd', 'ukd.id', '=', 'uk.m_divisi_id')
      ->leftJoin('m_general as ukg_div', 'ukg_div.id', '=', 'ukd.name')
      ->where('t.id', $id)
      ->select(
          't.*',
          'mp.tema_pelatihan as program_nama',
          'mt.nama_trainer',
          \DB::raw("COALESCE(mg_div.value, md.name_old, md.nomor, ukg_div.value, ukd.name_old, ukd.nomor, '-') as divisi_nama"),
          'mc.name as comp_nama',
          'ms.name as subcomp_nama',
          'mb.name as branch_nama',
          'u.name as creator_name'
      )
      ->first();

  $peserta = [];
  if ($data) {
      $peserta = \DB::table('t_request_pelatihan_d_kary as d')
          ->leftJoin('m_kary as k', 'k.id', '=', 'd.m_kary_id')
          ->leftJoin('m_divisi as kd', 'kd.id', '=', 'k.m_divisi_id')
          ->leftJoin('m_general as kg_div', 'kg_div.id', '=', 'kd.name')
          ->leftJoin('m_posisi as kp', 'kp.id', '=', 'k.m_posisi_id')
          ->where('d.t_request_pelatihan_id', $id)
          ->select(
              'd.*',
              'k.nik',
              'k.nama_lengkap',
              \DB::raw("COALESCE(kg_div.value, kd.name_old, kd.nomor, '-') as peserta_divisi"),
              'kp.name as peserta_posisi'
          )
          ->orderBy('d.id', 'asc')
          ->get();
  }

  $logs = \DB::table('generate_approval_log as l')
      ->leftJoin('default_users as u', 'u.id', '=', 'l.action_user_id')
      ->where(function($q) use ($id, $data) {
          $q->where('l.trx_id', $id);
          if ($data && !empty($data->kode)) {
              $q->orWhere('l.trx_nomor', $data->kode);
          }
      })
      ->where(function($q) {
          $q->where('l.trx_table', 't_request_pelatihan')
            ->orWhere('l.trx_name', 'like', '%Pelatihan%');
      })
      ->select('l.*', 'u.name as action_user')
      ->orderBy('l.id', 'desc')
      ->get();

  $appLogApproved = $logs->first(function($l) {
      return in_array(strtoupper($l->action_type ?? ''), ['APPROVED', 'APPROVE', 'APPROVE HC']);
  });
  
  $appLogDisetujui = $logs->first(function($l) {
      return in_array(strtoupper($l->action_type ?? ''), ['APPROVED', 'APPROVE', 'SUBMITTED', 'IN APPROVAL', 'PROGRESS']);
  });

  $tglPengajuan = $data && !empty($data->created_at) ? date('d/m/Y', strtotime($data->created_at)) : date('d/m/Y');
  $tglMulai = $data && !empty($data->date_from) ? date('d/m/Y', strtotime($data->date_from)) : '-';
  $tglSelesai = $data && !empty($data->date_to) ? date('d/m/Y', strtotime($data->date_to)) : '-';

  // Ambil Logo dari Master Media (m_media)
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
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Pengajuan Pelatihan - {{ $data->kode ?? '-' }}</title>
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
      <h3 style="margin-bottom: 8px;">Data Pengajuan Pelatihan Tidak Ditemukan</h3>
      <p style="margin: 0;">Silakan periksa kembali parameter ID transaksi yang diberikan.</p>
    </div>
  @else

  <!-- KOTAK SURAT UTAMA TEMPRINA (FM-HRD-004) -->
  <table class="main-table" width="100%" cellpadding="0" cellspacing="0">
    
    <!-- 1. HEADER 3 KOLOM -->
    <tr>
      <td style="width: 26%; text-align: center; vertical-align: middle; padding: 10px 8px; border-right: 1px solid #000; border-bottom: 1px solid #000;">
        @if(!empty($logoPath) && file_exists($logoPath))
          <div style="text-align: center; padding: 4px 0;">
            <img src="{{ $logoPath }}" alt="Logo Temprina" height="36" border="0" style="height: 36px; max-width: 130px;">
          </div>
        @else
          <div style="font-size: 13pt; font-weight: bold; color: #1e3a8a; line-height: 1.1;">TEMPRINA<br><span style="font-size: 8pt; color: #374151; font-weight: normal;">MEDIA GRAFIKA</span></div>
        @endif
      </td>
      <td style="width: 44%; text-align: center; vertical-align: middle; padding: 14px 8px; font-size: 13pt; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; letter-spacing: 0.5px;">
        FORM PENGAJUAN PELATIHAN
      </td>
      <td style="width: 30%; vertical-align: middle; padding: 10px 14px; border-bottom: 1px solid #000;">
        <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width: 65px; border: none; padding: 3px 0; font-size: 9.5pt;">No. Form</td>
            <td style="width: 10px; border: none; padding: 3px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt;">FM-HRD-004</td>
          </tr>
          <tr>
            <td style="width: 65px; border: none; padding: 3px 0; font-size: 9.5pt;">No. Urut</td>
            <td style="width: 10px; border: none; padding: 3px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 3px 0; font-weight: bold; font-size: 9.5pt; white-space: nowrap;">{{ $data->kode ?? '-' }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 2. SUB HEADER TANGGAL -->
    <tr>
      <td colspan="3" style="padding: 8px 18px; border-bottom: 1px solid #000; font-size: 9.5pt; background-color: #fafafa;">
        <table style="width: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width: 50%; border: none; padding: 0; font-size: 9.5pt;">
              <strong>Tanggal</strong> : {{ $tglPengajuan }}
            </td>
            <td style="width: 50%; border: none; padding: 0; text-align: right; font-size: 9.5pt;">
              <strong>Rev. / Tgl</strong> : 00 / -
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 3. BODY PENGISIAN FORM -->
    <tr>
      <td colspan="3" style="padding: 18px 22px; border-bottom: 1px solid #000;">
        
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px;" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Nama Pemohon</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">{{ $data->creator_name ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Divisi</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">{{ $data->divisi_nama ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Unit / Perusahaan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">
              {{ $data->comp_nama ?? 'PT Temprina Media Grafika' }}
              @if(!empty($data->branch_nama)) ({{ $data->branch_nama }}) @endif
            </td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Tema / Program Pelatihan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt; font-weight: bold; color: #1e3a8a;">{{ $data->program_nama ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Instruktur / Trainer</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">{{ $data->nama_trainer ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Tanggal Pelaksanaan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">{{ $tglMulai }} s/d {{ $tglSelesai }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Lokasi / Sarana Pelatihan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt;">{{ $data->sarana ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Tujuan / Alasan Pelatihan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt; line-height: 1.45;">{{ $data->desc ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 220px; border: none; padding: 6px 0; font-weight: bold; font-size: 10pt;">Status Pengajuan</td>
            <td style="width: 15px; border: none; padding: 6px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 6px 0; font-size: 10pt; font-weight: bold;">
              <span style="color: #047857;">{{ strtoupper($data->status ?? 'APPROVED') }}</span>
            </td>
          </tr>
        </table>

        <!-- TABEL PESERTA PELATIHAN -->
        <div style="font-weight: bold; font-size: 10pt; margin-top: 16px; margin-bottom: 8px; text-transform: uppercase;">
          DAFTAR PESERTA PELATIHAN (TOTAL: {{ count($peserta) }} ORANG) :
        </div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 6px; margin-bottom: 8px;" cellpadding="6">
          <thead>
            <tr style="background-color: #f3f4f6;">
              <th style="width: 6%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 4px; text-transform: uppercase;">No</th>
              <th style="width: 20%; border: 1px solid #000; text-align: center; font-size: 9.5pt; font-weight: bold; padding: 8px 6px; text-transform: uppercase;">NIK</th>
              <th style="width: 34%; border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 8px 8px; text-transform: uppercase;">Nama Lengkap Karyawan</th>
              <th style="width: 20%; border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 8px 8px; text-transform: uppercase;">Divisi</th>
              <th style="width: 20%; border: 1px solid #000; text-align: left; font-size: 9.5pt; font-weight: bold; padding: 8px 8px; text-transform: uppercase;">Posisi / Jabatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($peserta as $idx => $p)
              <tr>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; padding: 7px 4px;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9.5pt; padding: 7px 6px;">{{ $p->nik ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9.5pt; padding: 7px 8px;">{{ $p->nama_lengkap ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9.5pt; padding: 7px 8px;">{{ $p->peserta_divisi ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9.5pt; padding: 7px 8px;">{{ $p->peserta_posisi ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="border: 1px solid #000; text-align: center; font-style: italic; color: #666; font-size: 9.5pt; padding: 16px;">
                  Belum ada peserta pelatihan yang ditambahkan.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

      </td>
    </tr>

    <!-- 4. FOOTER APPROVAL (4 KOLOM) -->
    <tr>
      <td colspan="3" style="padding: 0;">
        <table style="width: 100%; border-collapse: collapse; border: none;" cellpadding="6">
          <tr>
            <td style="width: 18%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 6px 16px 6px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 36px;">Halaman</div>
              <div style="font-weight: bold; font-size: 10pt;">1 / 1</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 6px;">Dibuat :</div>
              <div style="height: 60px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">({{ $data->creator_name ?? 'Pemohon' }})</div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">Tgl: {{ $tglPengajuan }}</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 6px;">Disetujui :</div>
              <div style="height: 60px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_user))
                  ({{ $appLogDisetujui->action_user }})
                @else
                  ( Atasan / Manager )
                @endif
              </div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_at))
                  Tgl: {{ date('d/m/Y', strtotime($appLogDisetujui->action_at)) }}
                @else
                  Tgl: ........................
                @endif
              </div>
            </td>
            <td style="width: 27.34%; text-align: center; vertical-align: top; padding: 12px 8px 16px 8px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 6px;">Diketahui :</div>
              <div style="height: 60px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">
                @if($appLogApproved && !empty($appLogApproved->action_user))
                  ({{ $appLogApproved->action_user }})
                @else
                  ( Human Capital / HRD )
                @endif
              </div>
              <div style="font-size: 8.5pt; color: #555; margin-top: 4px;">
                @if($appLogApproved && !empty($appLogApproved->action_at))
                  Tgl: {{ date('d/m/Y', strtotime($appLogApproved->action_at)) }}
                @else
                  Tgl: ........................
                @endif
              </div>
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