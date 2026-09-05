@php
  $req = app()->request;
  $id = $req->id;

  $data = \DB::table('t_request_pelatihan as t')
      ->leftJoin('m_prog_pelatihan as mp', 'mp.id', '=', 't.m_prog_pelatihan_id')
      ->leftJoin('m_trainer as mt', 'mt.id', '=', 't.trainer_id')
      ->leftJoin('m_divisi as md', 'md.id', '=', 't.m_divisi_id')
      ->leftJoin('m_general as mg_div', 'mg_div.id', '=', 'md.name')
      ->leftJoin('m_dept as mdp', 'mdp.id', '=', 't.m_dept_id')
      ->leftJoin('m_comp as mc', 'mc.id', '=', 't.m_comp_id')
      ->leftJoin('m_subcomp as ms', 'ms.id', '=', 't.m_subcomp_id')
      ->leftJoin('m_branch as mb', 'mb.id', '=', 't.m_branch_id')
      ->leftJoin('default_users as u', 'u.id', '=', 't.creator_id')
      ->where('t.id', $id)
      ->select(
          't.*',
          'mp.tema_pelatihan as program_nama',
          'mt.nama_trainer',
          \DB::raw("COALESCE(mg_div.value, md.nama, md.kode, '-') as divisi_nama"),
          'mdp.nama as dept_nama',
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
              \DB::raw("COALESCE(kg_div.value, kd.nama, kd.kode, '-') as peserta_divisi"),
              'kp.desc_kerja as peserta_posisi'
          )
          ->orderBy('d.id', 'asc')
          ->get();
  }

  $logs = \DB::table('generate_approval_log')
      ->where(function($q) use ($id, $data) {
          $q->where('trx_id', $id);
          if ($data && !empty($data->kode)) {
              $q->orWhere('trx_nomor', $data->kode);
          }
      })
      ->where(function($q) {
          $q->where('form_name', 't_req_pelatihan')
            ->orWhere('form_name', 't_request_pelatihan')
            ->orWhere('trx_table', 't_request_pelatihan');
      })
      ->orderBy('id', 'desc')
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
        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo Temprina" style="max-height: 44px; max-width: 140px; display: block; margin: 0 auto;">
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
            <td class="field-label">Dept. / Divisi</td>
            <td class="field-separator">:</td>
            <td class="field-value">
              {{ $data->divisi_nama ?? '-' }} 
              @if(!empty($data->dept_nama) && $data->dept_nama !== '-') / {{ $data->dept_nama }} @endif
            </td>
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