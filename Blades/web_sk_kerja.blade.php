@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_kary_det_jabatan;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

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
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:90%; margin:auto; font-size:12px; line-height:1.5; color:#000;">

  <!-- KOP SURAT RESMI -->
  @include('projects.web_sk_kop')

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
  <div style="margin-top:24px; font-size:12px;">
    Yang bertanda tangan di bawah ini :
  </div>

  <!-- DATA PIHAK PENERANG -->
  <table style="width:100%; border-collapse:collapse; margin-top:10px; margin-left:30px; font-size:12px; line-height:1.5;">
    <tr>
      <td style="width:25px; vertical-align:top;">&#9658;</td>
      <td style="width:110px; vertical-align:top;">Nama</td>
      <td style="width:15px; vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;"><b>{{ $signatureNama }}</b></td>
    </tr>
    <tr>
      <td style="vertical-align:top;">&#9658;</td>
      <td style="vertical-align:top;">Bagian</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">
        {{ $signatureJabatan }}<br>
        {{ $signatureSubcomp }}<br>
        {{ $signatureAlamat }}
      </td>
    </tr>
  </table>

  <!-- KETERANGAN BAHWA -->
  <div style="margin-top:20px; font-size:12px;">
    Menerangkan bahwa :
  </div>

  <!-- DATA KARYAWAN -->
  <table style="width:100%; border-collapse:collapse; margin-top:10px; margin-left:30px; font-size:12px; line-height:1.5;">
    <tr>
      <td style="width:25px; vertical-align:top;">&#9658;</td>
      <td style="width:110px; vertical-align:top;">Nama</td>
      <td style="width:15px; vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;"><b>{{ $karyawanNama }}</b></td>
    </tr>
    <tr>
      <td style="vertical-align:top;">&#9658;</td>
      <td style="vertical-align:top;">Status</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">Yang bersangkutan adalah {{ $statusClean }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top;">&#9658;</td>
      <td style="vertical-align:top;">Bagian</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">
        {{ $karyawanJabatan }}<br>
        {{ $karyawanSubcomp }}<br>
        {{ $karyawanAlamat }}
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top;">&#9658;</td>
      <td style="vertical-align:top;">Awal Kerja</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $awalKerja }} s/d {{ $akhirKerja }}</td>
    </tr>
    <tr>
      <td style="vertical-align:top;">&#9658;</td>
      <td style="vertical-align:top;">Keperluan</td>
      <td style="vertical-align:top; text-align:center;">:</td>
      <td style="vertical-align:top;">{{ $keperluan }}</td>
    </tr>
  </table>

  <!-- PENUTUP -->
  <div style="margin-top:24px; font-size:12px; text-align:justify;">
    Demikian surat keterangan kerja ini dibuat agar dapat dipergunakan sebagaimana mestinya.
  </div>

  <!-- TANDA TANGAN -->
  <table style="width:100%; border-collapse:collapse; margin-top:28px; font-size:12px;">
    <tr>
      <td style="width:55%; vertical-align:top;">
        {{ $kotaTerbit }}, {{ $tanggalTerbit }}<br>
        <b>{{ $companyName }}</b>
        <div style="height:55px;"></div>
        <b><u>{{ $signatureNama }}</u></b><br>
        <i>{{ $signatureJabatan }}</i>
      </td>
      <td style="width:45%;"></td>
    </tr>
  </table>

  <!-- FOOTER CABANG -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <div style="position:fixed; bottom:0; left:5%; right:5%; width:90%; margin:auto;">
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