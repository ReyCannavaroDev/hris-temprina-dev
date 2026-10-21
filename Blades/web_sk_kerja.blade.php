@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_kary_det_jabatan;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
<<<<<<< HEAD
$tanggalIndo = $carbonDate->translatedFormat('d F Y');
=======
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
>>>>>>> c1c740a (feat: add work schedule javascript logic and blade templates for SK documents)

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

<<<<<<< HEAD
=======
$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? null;
$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;

$karyawan_jabatan = null;
if (!empty($t_mutasi?->m_kary_id)) {
    try {
        $karyawan_jabatan = m_kary_det_jabatan::where(function ($q) use ($t_mutasi) {
                $q->where('m_karyawan_id', $t_mutasi->m_kary_id)
                  ->orWhere('m_kary_id', $t_mutasi->m_kary_id);
            })
            ->orderBy('start_time', 'asc')
            ->first();
    } catch (\Throwable $th) {}
}

$awalKerja = $karyawan_jabatan && !empty($karyawan_jabatan->start_time ?? $karyawan_jabatan->tgl_mulai) 
    ? Carbon::parse($karyawan_jabatan->start_time ?? $karyawan_jabatan->tgl_mulai)->translatedFormat('d F Y') 
    : ($t_mutasi?->m_kary?->tgl_masuk ? Carbon::parse($t_mutasi->m_kary->tgl_masuk)->translatedFormat('d F Y') : '-');

$akhirKerja = $karyawan_jabatan && !empty($karyawan_jabatan->end_time ?? $karyawan_jabatan->tgl_selesai)
    ? Carbon::parse($karyawan_jabatan->end_time ?? $karyawan_jabatan->tgl_selesai)->translatedFormat('d F Y') 
    : 'Sekarang';

>>>>>>> c1c740a (feat: add work schedule javascript logic and blade templates for SK documents)
$sbuCode = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : $compRaw;

$kotaTerbit = $t_mutasi?->m_sub_lama?->m_branch?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

<<<<<<< HEAD
// Data Pihak 1 (Signature / Yang Bertanda Tangan)
$signatureNama = $t_mutasi?->signature?->nama_lengkap ?? '( ........................................ )';
$signatureJabatan = $t_mutasi?->signature?->m_posisi?->name ?? 'Kadiv. Human Capital Holding';
$signatureSubcomp = $t_mutasi?->signature?->m_subcomp?->name ?? $companyName;
$signatureAlamat = $t_mutasi?->signature?->m_subcomp?->address 
                ?? $t_mutasi?->signature?->m_branch?->address 
                ?? 'Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik';

// Data Pihak 2 (Karyawan yang Diterangkan)
$karyawanNama = strtoupper($t_mutasi?->m_kary?->nama_lengkap ?? '-');

$rawStatus = $t_mutasi?->status_kary_lama?->value 
          ?? $t_mutasi?->m_kary?->status_karyawan?->value 
          ?? 'Karyawan Tetap';
$statusClean = preg_replace('/^yang\s+bersangkutan\s+adalah\s+/i', '', trim($rawStatus));

$karyawanJabatan = $t_mutasi?->m_posisi_lama?->name 
                ?? $t_mutasi?->m_kary?->m_posisi?->name 
                ?? '-';
$karyawanSubcomp = $t_mutasi?->m_sub_lama?->name ?? $companyName;
$karyawanAlamat = $t_mutasi?->m_sub_lama?->address 
               ?? $t_mutasi?->m_kary?->m_subcomp?->address 
               ?? $t_mutasi?->m_branch_lama?->address 
               ?? $t_mutasi?->m_kary?->m_branch?->address 
               ?? 'Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik';

// Masa Kerja (Awal Kerja s/d Sekarang / Akhir)
$karyawan_jabatan = m_kary_det_jabatan::where('m_karyawan_id', $t_mutasi?->m_kary_id)
                    ->orderBy('start_time', 'asc')
                    ->first();

$tglAwal = $karyawan_jabatan?->tgl_mulai ?? $t_mutasi?->m_kary?->tgl_masuk;
$awalKerja = $tglAwal ? Carbon::parse($tglAwal)->translatedFormat('d F Y') : '-';

$tglAkhir = $karyawan_jabatan?->tgl_selesai ?? $t_mutasi?->m_kary?->tgl_berhenti;
$akhirKerja = $tglAkhir ? Carbon::parse($tglAkhir)->translatedFormat('d F Y') : 'sekarang';

// Keperluan
$keperluan = $t_mutasi?->deskripsi 
          ?? $t_mutasi?->keterangan 
          ?? $t_mutasi?->catatan 
          ?? '-';

// Icon Pointer Segitiga Hitam Base64 (100% kompatibel dengan TCPDF tanpa resiko jadi '?')
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAASElEQVR4nJWNQQ4AIAjD3OL/vzxPJEZhKCcCbQpJ42cYCwB9CSF1IrOjk1g9qhpdPquxE8ZRmy+CJDwXdtgWTtAWKvgqODBmAXo9GyO93OPeAAAAAElFTkSuQmCC';
=======
$rawStatus = $t_mutasi?->status_kary_lama?->value ?? $t_mutasi?->m_kary?->status_kary?->value ?? '-';
if ($rawStatus !== '-' && !str_contains(strtolower($rawStatus), 'yang bersangkutan')) {
    $statusText = 'Yang bersangkutan adalah ' . strtoupper($rawStatus);
} else {
    $statusText = $rawStatus;
}
>>>>>>> c1c740a (feat: add work schedule javascript logic and blade templates for SK documents)
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:auto; font-size:12px; line-height:1.5; color:#000;">

  <!-- KOP SURAT RESMI -->
  @include('projects.web_sk_kop')

<<<<<<< HEAD
  @if(!$isJpBooks)
    <!-- DOUBLE LINE SEPARATOR -->
    <div style="border-top:1px solid #000; margin-top:4px;"></div>
    <div style="border-top:2px solid #000; margin-top:1.5px; margin-bottom:12px;"></div>
  @endif

  <!-- JUDUL SURAT -->
  <table style="width:100%; border-collapse:collapse; margin-top:10px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:16px; letter-spacing:1px; text-decoration:underline;">
        SURAT KETERANGAN KERJA
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11.5px; font-weight:bold; padding-top:4px;">
        No. {{ $t_mutasi?->nomor ?? '-' }}
      </td>
    </tr>
  </table>

  <!-- PEMBUKA -->
  <div style="margin-top:22px; font-size:12px;">
    Yang bertanda tangan di bawah ini :
  </div>

  <!-- DATA PIHAK PENERANG -->
  <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:12px; line-height:1.5;">
    <tr>
      <td style="width:4%;"></td>
      <td style="width:3%; vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="width:16%; vertical-align:top;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:74%; vertical-align:top;"><b>{{ $signatureNama }}</b></td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top;">Bagian</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">
        {{ $signatureJabatan }}<br>
        {{ $signatureSubcomp }}<br>
        {{ $signatureAlamat }}
      </td>
    </tr>
  </table>
=======
<table style="width:100%; margin-top:20px; font-family:'Times New Roman', serif;">
  <tr>
    <td style="text-align:center; font-weight:bold; font-size:16px;">
      <span style="border-bottom:2px solid black; padding-bottom:2px;">SURAT KETERANGAN KERJA</span>
    </td>
  </tr>
  <tr>
    <td style="text-align:center; font-size:12px; font-weight:bold; padding-top:6px;">
      No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
    </td>
  </tr>
</table>

<p style="margin-top:24px; margin-bottom:8px; font-size:13px;">Yang bertanda tangan di bawah ini :</p>

<table style="width:100%; border-collapse:collapse; margin-bottom:15px; font-size:13px; line-height:1.5;">
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top;">-&gt;&nbsp;&nbsp;Nama</td>
    <td style="width:3%; vertical-align:top; text-align:center;">:</td>
    <td style="width:72%; vertical-align:top;">
      {{ $t_mutasi->signature?->nama_lengkap ?? '( ........................................ )' }}
    </td>
  </tr>
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top; padding-top:4px;">-&gt;&nbsp;&nbsp;Bagian</td>
    <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">:</td>
    <td style="width:72%; vertical-align:top; padding-top:4px;">
      {{ $t_mutasi->signature?->m_posisi?->name ?? 'Kadiv. Human Capital Holding' }}<br>
      {{ $t_mutasi->signature?->m_subcomp?->name ?? $companyName }}<br>
      {{ $t_mutasi->signature?->m_subcomp?->address ?? 'Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik' }}
    </td>
  </tr>
</table>

<p style="margin-top:16px; margin-bottom:8px; font-size:13px;">Menerangkan bahwa :</p>

<table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px; line-height:1.5;">
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top;">-&gt;&nbsp;&nbsp;Nama</td>
    <td style="width:3%; vertical-align:top; text-align:center;">:</td>
    <td style="width:72%; vertical-align:top; font-weight:bold;">
      {{ $t_mutasi->m_kary?->nama_lengkap ?? '-' }}
    </td>
  </tr>
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top; padding-top:4px;">-&gt;&nbsp;&nbsp;Status</td>
    <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">:</td>
    <td style="width:72%; vertical-align:top; padding-top:4px;">
      {{ $statusText }}
    </td>
  </tr>
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top; padding-top:4px;">-&gt;&nbsp;&nbsp;Bagian</td>
    <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">:</td>
    <td style="width:72%; vertical-align:top; padding-top:4px;">
      {{ $t_mutasi->m_posisi_lama?->name ?? '-' }}<br>
      {{ $t_mutasi->m_sub_lama?->name ?? $companyName }}<br>
      {{ $t_mutasi->m_sub_lama?->address ?? '' }}
    </td>
  </tr>
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top; padding-top:4px;">-&gt;&nbsp;&nbsp;Awal Kerja</td>
    <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">:</td>
    <td style="width:72%; vertical-align:top; padding-top:4px;">
      {{ $awalKerja }} s/d {{ $akhirKerja }}
    </td>
  </tr>
  <tr>
    <td style="width:3%;"></td>
    <td style="width:22%; vertical-align:top; padding-top:4px;">-&gt;&nbsp;&nbsp;Keperluan</td>
    <td style="width:3%; vertical-align:top; text-align:center; padding-top:4px;">:</td>
    <td style="width:72%; vertical-align:top; padding-top:4px;">
      {{ (!empty($t_mutasi->deskripsi) && $t_mutasi->deskripsi !== '-') ? $t_mutasi->deskripsi : ((!empty($t_mutasi->keterangan) && $t_mutasi->keterangan !== '-') ? $t_mutasi->keterangan : '-') }}
    </td>
  </tr>
</table>

<p style="margin-top:20px; font-size:13px; text-align:justify;">
  Demikian surat keterangan kerja ini dibuat agar dapat dipergunakan sebagaimana mestinya.
</p>

<table style="width:100%; margin-top:25px; font-family:'Times New Roman', serif; font-size:13px;">
  <tr>
    <td style="width:58%;"></td>
    <td style="width:42%; vertical-align:top;">
      <div>{{ $kotaTerbit }}, {{ $tanggalTerbit }}</div>
      <div style="font-weight:bold; margin-top:4px;">{{ $companyName }}</div>
      <div style="height:55px;"></div>
      @if($t_mutasi->signature?->nama_lengkap)
        <div style="font-weight:bold; text-decoration:underline;">
          {{ $t_mutasi->signature->nama_lengkap }}
        </div>
        <div>
          {{ $t_mutasi->signature->m_posisi?->name ?? 'Human Capital Dept.' }}
        </div>
      @else
        <div style="font-weight:bold;">
          ( ........................................ )
        </div>
        <div>
          Human Capital Dept.
        </div>
      @endif
    </td>
  </tr>
</table>
>>>>>>> c1c740a (feat: add work schedule javascript logic and blade templates for SK documents)

  <!-- KETERANGAN BAHWA -->
  <div style="margin-top:18px; font-size:12px;">
    Menerangkan bahwa :
  </div>

  <!-- DATA KARYAWAN -->
  <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:12px; line-height:1.5;">
    <tr>
      <td style="width:4%;"></td>
      <td style="width:3%; vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="width:16%; vertical-align:top;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:74%; vertical-align:top;"><b>{{ $karyawanNama }}</b></td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top;">Status</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">Yang bersangkutan adalah {{ $statusClean }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top;">Bagian</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">
        {{ $karyawanJabatan }}<br>
        {{ $karyawanSubcomp }}<br>
        {{ $karyawanAlamat }}
      </td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top;">Awal Kerja</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $awalKerja }} s/d {{ $akhirKerja }}</td>
    </tr>
    <tr>
      <td></td>
      <td style="vertical-align:top; padding-top:3px;">
        <img src="{{ $arrowIcon }}" width="8" height="8" alt="►">
      </td>
      <td style="vertical-align:top;">Keperluan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $keperluan }}</td>
    </tr>
  </table>

  <!-- PENUTUP -->
  <div style="margin-top:22px; font-size:12px; text-align:justify;">
    Demikian surat keterangan kerja ini dibuat agar dapat dipergunakan sebagaimana mestinya.
  </div>

  <!-- TANDA TANGAN -->
  <table style="width:100%; border-collapse:collapse; margin-top:26px; font-size:12px;">
    <tr>
      <td style="width:55%; vertical-align:top;">
        {{ $kotaTerbit }}, {{ $tanggalTerbit }}<br>
        <b>{{ $companyName }}</b>
        <div style="height:55px; line-height:55px;">&nbsp;</div>
        <u><b>{{ $signatureNama }}</b></u><br>
        <i>{{ $signatureJabatan }}</i>
      </td>
      <td style="width:45%;"></td>
    </tr>
  </table>

  <!-- FOOTER CABANG -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <div style="position:fixed; bottom:0; left:4%; right:4%; width:92%; margin:auto;">
      <div style="border-top:0.5px solid #777; margin-bottom:3px;"></div>
      <table style="width:100%; font-size:6.8px; line-height:1.2; color:#333; border-collapse:collapse;">
        <tr>
          <td style="width:50%; vertical-align:top;">
            <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com<br>
            <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com<br>
            <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com<br>
            <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com
          </td>
          <td style="width:50%; vertical-align:top;">
            <b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
            <b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
            <b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
            <b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
          </td>
        </tr>
      </table>
    </div>
  @endif

</div>