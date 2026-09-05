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
      ->where('t.id', $id)
      ->select(
          't.*',
          'mp.tema_pelatihan as program_nama',
          'mt.nama_trainer',
          \DB::raw("COALESCE(mg_div.value, md.name_old, md.nomor, '-') as divisi_nama"),
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
      return in_array(strtoupper($l->action_type ?? ''), ['APPROVED', 'APPROVE', 'SUBMITTED', 'IN APPROVAL']);
  });

  $tglPengajuan = $data && !empty($data->created_at) ? date('d/m/Y', strtotime($data->created_at)) : date('d/m/Y');
  $tglMulai = $data && !empty($data->date_from) ? date('d/m/Y', strtotime($data->date_from)) : '-';
  $tglSelesai = $data && !empty($data->date_to) ? date('d/m/Y', strtotime($data->date_to)) : '-';
@endphp
@php
  $logoBase64 = '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';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Pengajuan Pelatihan - {{ $data->kode ?? '-' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 10mm 12mm 10mm 12mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #000;
      margin: 0;
      padding: 0;
      background-color: #fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .print-container {
      width: 100%;
      max-width: 190mm;
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
    
    /* Header Styles */
    .header-logo {
      width: 25%;
      padding: 8px 10px;
      text-align: center;
      border-right: 1px solid #000;
    }
    .logo-text-group {
      display: inline-block;
      text-align: left;
    }
    .logo-title {
      font-size: 18px;
      font-weight: 900;
      color: #0070ba;
      letter-spacing: -0.5px;
      line-height: 1;
      display: flex;
      align-items: center;
    }
    .logo-subtitle {
      font-size: 8px;
      font-weight: bold;
      color: #1a4480;
      display: flex;
      align-items: center;
      gap: 3px;
      margin-top: 2px;
    }
    .logo-square-red { width: 5px; height: 5px; background: #e31b23; display: inline-block; }
    .logo-square-yellow { width: 5px; height: 5px; background: #fdb813; display: inline-block; }
    .logo-square-blue { width: 5px; height: 5px; background: #0070ba; display: inline-block; }

    .header-title {
      width: 50%;
      text-align: center;
      font-size: 13px;
      font-weight: bold;
      letter-spacing: 0.5px;
      padding: 10px;
      text-transform: uppercase;
      border-right: 1px solid #000;
    }
    .header-meta {
      width: 25%;
      padding: 6px 8px;
      font-size: 9.5px;
      line-height: 1.5;
    }
    
    /* Sub Header */
    .sub-header-row td {
      padding: 5px 10px;
      font-size: 10px;
      border-bottom: 1px solid #000;
    }

    /* Content Form Body */
    .form-body {
      padding: 10px 14px;
    }
    .field-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
      margin-bottom: 8px;
    }
    .field-table td {
      border: none !important;
      padding: 4px 2px;
      font-size: 10.5px;
      vertical-align: top;
    }
    .field-label {
      width: 220px;
      font-weight: bold;
      color: #111;
    }
    .field-separator {
      width: 15px;
      text-align: center;
      font-weight: bold;
    }
    .field-value {
      color: #000;
    }

    /* Peserta Table */
    .peserta-section-title {
      font-weight: bold;
      font-size: 11px;
      margin-top: 10px;
      margin-bottom: 5px;
      text-transform: uppercase;
    }
    .peserta-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      margin-bottom: 8px;
    }
    .peserta-table th {
      border: 1px solid #000 !important;
      background-color: #f0f0f0;
      padding: 5px 4px;
      font-size: 9.5px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
    }
    .peserta-table td {
      border: 1px solid #000 !important;
      padding: 4.5px 6px;
      font-size: 9.5px;
    }

    /* Signature Box (Footer) */
    .footer-table {
      width: 100%;
      border-collapse: collapse;
      border-top: 1px solid #000;
    }
    .footer-table td {
      border: 1px solid #000;
      text-align: center;
      vertical-align: top;
      padding: 4px;
      font-size: 9.5px;
    }
    .sig-col-page { width: 18%; }
    .sig-col { width: 27.33%; }
    .sig-space {
      height: 55px;
    }
    .sig-name {
      font-weight: bold;
      text-decoration: underline;
    }
    .sig-date {
      font-size: 8.5px;
      color: #555;
    }

    /* Print Action Bar */
    .no-print {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #2d3748;
      color: #fff;
      padding: 10px 15px;
      margin-bottom: 15px;
      border-radius: 6px;
    }
    .btn-print {
      background: #3182ce;
      color: #fff;
      border: none;
      padding: 6px 14px;
      font-weight: bold;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
    }
    .btn-print:hover { background: #2b6cb0; }
    
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      .print-container { max-width: 100%; }
    }
  </style>
</head>
<body>

<div class="print-container">

  <!-- Action Bar (Hidden when printed) -->
  <div class="no-print">
    <div>
      <strong>Cetak Formulir Pengajuan Pelatihan</strong> | No: {{ $data->kode ?? '-' }}
    </div>
    <div>
      <button class="btn-print" onclick="window.print()">🖨️ Print / Simpan PDF</button>
    </div>
  </div>

  @if(!$data)
    <div style="border: 1px solid #e53e3e; background: #fff5f5; color: #c53030; padding: 20px; text-align: center; border-radius: 6px;">
      <h3>Data Pengajuan Pelatihan Tidak Ditemukan</h3>
      <p>Silakan periksa kembali parameter ID transaksi yang diberikan.</p>
    </div>
  @else

  <!-- KOTAK SURAT UTAMA TEMPRINA -->
  <table class="main-table">
    
    <!-- 1. HEADER 3 KOLOM -->
    <tr>
      <td class="header-logo">
        <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Logo Temprina" style="max-height: 44px; max-width: 140px; display: block; margin: 0 auto;">
      </td>
      <td class="header-title">
        FORM PENGAJUAN PELATIHAN
      </td>
      <td class="header-meta">
        <table style="width: 100%; border-collapse: collapse; border: none;">
          <tr>
            <td style="border: none; padding: 1px; width: 65px; font-weight: normal;">No. Form</td>
            <td style="border: none; padding: 1px; width: 5px;">:</td>
            <td style="border: none; padding: 1px; font-weight: bold;">FM-HRD-004</td>
          </tr>
          <tr>
            <td style="border: none; padding: 1px; font-weight: normal;">No. Urut</td>
            <td style="border: none; padding: 1px;">:</td>
            <td style="border: none; padding: 1px; font-weight: bold;">{{ $data->kode ?? '-' }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 2. SUB HEADER TANGGAL -->
    <tr class="sub-header-row">
      <td colspan="3">
        <table style="width: 100%; border-collapse: collapse; border: none;">
          <tr>
            <td style="border: none; padding: 0; width: 50%;">
              <strong>Tanggal</strong> : {{ $tglPengajuan }}
            </td>
            <td style="border: none; padding: 0; width: 50%; text-align: right;">
              <strong>Rev. / Tgl</strong> : 00 / -
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- 3. BODY PENGISIAN FORM -->
    <tr>
      <td colspan="3" style="padding: 12px 14px;">
        
        <table class="field-table">
          <tr>
            <td class="field-label">Nama Pemohon</td>
            <td class="field-separator">:</td>
            <td class="field-value">{{ $data->creator_name ?? '-' }}</td>
          </tr>
          <tr>
            <td class="field-label">Divisi</td>
            <td class="field-separator">:</td>
            <td class="field-value">{{ $data->divisi_nama ?? '-' }}</td>
          </tr>
          <tr>
            <td class="field-label">Unit / Perusahaan</td>
            <td class="field-separator">:</td>
            <td class="field-value">
              {{ $data->comp_nama ?? 'PT Temprina Media Grafika' }}
              @if(!empty($data->branch_nama)) ({{ $data->branch_nama }}) @endif
            </td>
          </tr>
          <tr>
            <td class="field-label">Tema / Program Pelatihan</td>
            <td class="field-separator">:</td>
            <td class="field-value"><strong>{{ $data->program_nama ?? '-' }}</strong></td>
          </tr>
          <tr>
            <td class="field-label">Instruktur / Trainer</td>
            <td class="field-separator">:</td>
            <td class="field-value">{{ $data->nama_trainer ?? '-' }}</td>
          </tr>
          <tr>
            <td class="field-label">Tanggal Pelaksanaan</td>
            <td class="field-separator">:</td>
            <td class="field-value">
              {{ $tglMulai }} s/d {{ $tglSelesai }}
            </td>
          </tr>
          <tr>
            <td class="field-label">Lokasi / Sarana Pelatihan</td>
            <td class="field-separator">:</td>
            <td class="field-value">{{ $data->sarana ?? '-' }}</td>
          </tr>
          <tr>
            <td class="field-label">Tujuan / Alasan Pelatihan</td>
            <td class="field-separator">:</td>
            <td class="field-value">{{ $data->desc ?? '-' }}</td>
          </tr>
          <tr>
            <td class="field-label">Status Pengajuan</td>
            <td class="field-separator">:</td>
            <td class="field-value">
              <span style="font-weight: bold; text-transform: uppercase;">{{ $data->status ?? 'DRAFT' }}</span>
            </td>
          </tr>
        </table>

        <!-- DAFTAR PESERTA PELATIHAN -->
        <div class="peserta-section-title">
          Daftar Peserta Pelatihan (Total: {{ count($peserta) }} Orang) :
        </div>
        
        <table class="peserta-table">
          <thead>
            <tr>
              <th style="width: 5%;">No</th>
              <th style="width: 20%;">NIK</th>
              <th style="width: 35%;">Nama Lengkap Karyawan</th>
              <th style="width: 20%;">Divisi</th>
              <th style="width: 20%;">Posisi / Jabatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($peserta as $idx => $p)
              <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td style="text-align: center;">{{ $p->nik ?? '-' }}</td>
                <td>{{ $p->nama_lengkap ?? '-' }}</td>
                <td>{{ $p->peserta_divisi ?? '-' }}</td>
                <td>{{ $p->peserta_posisi ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="text-align: center; font-style: italic; color: #666; padding: 10px;">
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
        <table class="footer-table">
          <tr>
            <td class="sig-col-page">
              <div style="font-weight: bold; margin-bottom: 25px;">Halaman</div>
              <div style="font-weight: bold; font-size: 11px;">1 / 1</div>
            </td>
            <td class="sig-col">
              <div style="font-weight: bold; margin-bottom: 5px;">Dibuat :</div>
              <div class="sig-space"></div>
              <div class="sig-name">({{ $data->creator_name ?? 'Pemohon' }})</div>
              <div class="sig-date">Tgl: {{ $tglPengajuan }}</div>
            </td>
            <td class="sig-col">
              <div style="font-weight: bold; margin-bottom: 5px;">Disetujui :</div>
              <div class="sig-space"></div>
              <div class="sig-name">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_user))
                  ({{ $appLogDisetujui->action_user }})
                @else
                  ( Atasan / Manager )
                @endif
              </div>
              <div class="sig-date">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_at))
                  Tgl: {{ date('d/m/Y', strtotime($appLogDisetujui->action_at)) }}
                @else
                  Tgl: ........................
                @endif
              </div>
            </td>
            <td class="sig-col">
              <div style="font-weight: bold; margin-bottom: 5px;">Diketahui :</div>
              <div class="sig-space"></div>
              <div class="sig-name">
                @if($appLogApproved && !empty($appLogApproved->action_user))
                  ({{ $appLogApproved->action_user }})
                @else
                  ( Human Capital / HRD )
                @endif
              </div>
              <div class="sig-date">
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