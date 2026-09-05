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
      <h3>Data Pengajuan Pelatihan Tidak Ditemukan</h3>
      <p>Silakan periksa kembali parameter ID transaksi yang diberikan.</p>
    </div>
  @else

  <!-- KOTAK SURAT UTAMA TEMPRINA (FM-HRD-004) -->
  <table class="main-table" width="100%" cellpadding="0" cellspacing="0">
    
    <!-- 1. HEADER 3 KOLOM -->
    <tr>
      <td style="width: 25%; text-align: center; vertical-align: middle; padding: 8px 10px; border-right: 1px solid #000; border-bottom: 1px solid #000;">
        <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Logo Temprina" style="max-height: 46px; max-width: 140px; display: block; margin: 0 auto;">
      </td>
      <td style="width: 45%; text-align: center; vertical-align: middle; padding: 10px 5px; font-size: 13pt; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; letter-spacing: 0.5px;">
        FORM PENGAJUAN PELATIHAN
      </td>
      <td style="width: 30%; vertical-align: middle; padding: 6px 10px; border-bottom: 1px solid #000;">
        <table style="width: 100%; border: none; border-collapse: collapse;">
          <tr>
            <td style="width: 55px; border: none; padding: 1px 0; font-size: 9.5pt;">No. Form</td>
            <td style="width: 8px; border: none; padding: 1px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 1px 0; font-weight: bold; font-size: 9.5pt;">FM-HRD-004</td>
          </tr>
          <tr>
            <td style="width: 55px; border: none; padding: 1px 0; font-size: 9.5pt;">No. Urut</td>
            <td style="width: 8px; border: none; padding: 1px 0; font-size: 9.5pt;">:</td>
            <td style="border: none; padding: 1px 0; font-weight: bold; font-size: 9.5pt; white-space: nowrap;">{{ $data->kode ?? '-' }}</td>
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
      <td colspan="3" style="padding: 10px 14px; border-bottom: 1px solid #000;">
        
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px;">
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Nama Pemohon</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $data->creator_name ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Divisi</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $data->divisi_nama ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Unit / Perusahaan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">
              {{ $data->comp_nama ?? 'PT Temprina Media Grafika' }}
              @if(!empty($data->branch_nama)) ({{ $data->branch_nama }}) @endif
            </td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Tema / Program Pelatihan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt; font-weight: bold;">{{ $data->program_nama ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Instruktur / Trainer</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $data->nama_trainer ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Tanggal Pelaksanaan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $tglMulai }} s/d {{ $tglSelesai }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Lokasi / Sarana Pelatihan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $data->sarana ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Tujuan / Alasan Pelatihan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt;">{{ $data->desc ?? '-' }}</td>
          </tr>
          <tr>
            <td style="width: 200px; border: none; padding: 3.5px 0; font-weight: bold; font-size: 10pt;">Status Pengajuan</td>
            <td style="width: 15px; border: none; padding: 3.5px 0; text-align: center; font-weight: bold; font-size: 10pt;">:</td>
            <td style="border: none; padding: 3.5px 0; font-size: 10pt; font-weight: bold;">{{ strtoupper($data->status ?? 'APPROVED') }}</td>
          </tr>
        </table>

        <!-- TABEL PESERTA PELATIHAN -->
        <div style="font-weight: bold; font-size: 10pt; margin-top: 8px; margin-bottom: 4px; text-transform: uppercase;">
          DAFTAR PESERTA PELATIHAN (TOTAL: {{ count($peserta) }} ORANG) :
        </div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 4px; margin-bottom: 6px;" cellpadding="4">
          <thead>
            <tr style="background-color: #f2f2f2;">
              <th style="width: 6%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">No</th>
              <th style="width: 20%; border: 1px solid #000; text-align: center; font-size: 9pt; font-weight: bold; text-transform: uppercase;">NIK</th>
              <th style="width: 34%; border: 1px solid #000; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Nama Lengkap Karyawan</th>
              <th style="width: 20%; border: 1px solid #000; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Divisi</th>
              <th style="width: 20%; border: 1px solid #000; text-align: left; font-size: 9pt; font-weight: bold; text-transform: uppercase;">Posisi / Jabatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($peserta as $idx => $p)
              <tr>
                <td style="border: 1px solid #000; text-align: center; font-size: 9pt;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000; text-align: center; font-size: 9pt;">{{ $p->nik ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9pt;">{{ $p->nama_lengkap ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9pt;">{{ $p->peserta_divisi ?? '-' }}</td>
                <td style="border: 1px solid #000; text-align: left; font-size: 9pt;">{{ $p->peserta_posisi ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="border: 1px solid #000; text-align: center; font-style: italic; color: #666; font-size: 9pt; padding: 8px;">
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
        <table style="width: 100%; border-collapse: collapse; border: none;" cellpadding="3">
          <tr>
            <td style="width: 18%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 24px;">Halaman</div>
              <div style="font-weight: bold; font-size: 10pt;">1 / 1</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Dibuat :</div>
              <div style="height: 48px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">({{ $data->creator_name ?? 'Pemohon' }})</div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">Tgl: {{ $tglPengajuan }}</div>
            </td>
            <td style="width: 27.33%; border-right: 1px solid #000; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Disetujui :</div>
              <div style="height: 48px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_user))
                  ({{ $appLogDisetujui->action_user }})
                @else
                  ( Atasan / Manager )
                @endif
              </div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">
                @if($appLogDisetujui && !empty($appLogDisetujui->action_at))
                  Tgl: {{ date('d/m/Y', strtotime($appLogDisetujui->action_at)) }}
                @else
                  Tgl: ........................
                @endif
              </div>
            </td>
            <td style="width: 27.34%; text-align: center; vertical-align: top; padding: 4px;">
              <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 5px;">Diketahui :</div>
              <div style="height: 48px;"></div>
              <div style="font-weight: bold; text-decoration: underline; font-size: 9.5pt;">
                @if($appLogApproved && !empty($appLogApproved->action_user))
                  ({{ $appLogApproved->action_user }})
                @else
                  ( Human Capital / HRD )
                @endif
              </div>
              <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">
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