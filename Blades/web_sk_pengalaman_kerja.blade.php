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

$rawSigComp = trim($t_mutasi?->signature?->m_subcomp?->name ?? '');
if (empty($rawSigComp) || strtoupper($rawSigComp) === 'HOLDING' || str_contains(strtoupper($signatureJabatan), strtoupper($rawSigComp))) {
    $signatureCompany = $companyName;
} else {
    if (!str_starts_with(strtoupper($rawSigComp), 'PT')) {
        $rawSigComp = 'PT ' . $rawSigComp;
    }
    $signatureCompany = $rawSigComp;
}

$signatureAlamat = $t_mutasi?->signature?->m_subcomp?->address 
                ?? $t_mutasi?->signature?->m_branch?->address 
                ?? 'Jl. Raya Sumengko KM 30-31 Wringin Anom Gresik';

// Data Pihak 2 (Karyawan)
$karyawanNama = strtoupper($t_mutasi?->m_kary?->nama_lengkap ?? '-');

$rawStatus = $t_mutasi?->status_kary_lama?->value 
          ?? $t_mutasi?->m_kary?->status_karyawan?->value 
          ?? 'Karyawan Tetap';
$statusClean = trim($rawStatus);
$statusDisplay = $statusClean . ' ' . $companyName;

$posisiName = trim($t_mutasi?->m_posisi_lama?->name ?? $t_mutasi?->m_kary?->m_posisi?->name ?? '-');
$subcompName = trim($t_mutasi?->m_sub_lama?->name ?? $t_mutasi?->m_kary?->m_subcomp?->name ?? '');
if (!empty($subcompName) && !str_contains(strtoupper($posisiName), strtoupper($subcompName))) {
    $bagianKaryawanLine1 = $posisiName . ' ' . $subcompName;
} else {
    $bagianKaryawanLine1 = $posisiName;
}

$alamatKaryawan = $t_mutasi?->m_sub_lama?->address 
               ?? $t_mutasi?->m_kary?->m_subcomp?->address 
               ?? $t_mutasi?->m_branch_lama?->address 
               ?? $t_mutasi?->m_kary?->m_branch?->address 
               ?? 'Jl. Karah Agung 45 Surabaya';

// Masa Kerja (Awal Kerja s/d Akhir Kerja)
$karyawan_jabatan = m_kary_det_jabatan::where('m_karyawan_id', $t_mutasi?->m_kary_id)
                    ->orderBy('start_time', 'asc')
                    ->first();

$tglAwal = $karyawan_jabatan?->tgl_mulai ?? $t_mutasi?->m_kary?->tgl_masuk;
$awalKerja = $tglAwal ? Carbon::parse($tglAwal)->translatedFormat('d F Y') : '-';

$tglAkhir = $karyawan_jabatan?->tgl_selesai 
          ?? $t_mutasi?->m_kary?->tgl_berhenti 
          ?? $t_mutasi?->tgl 
          ?? now();
$akhirKerja = $tglAkhir ? Carbon::parse($tglAkhir)->translatedFormat('d F Y') : '-';

// Alasan Berakhir Kerja / Keterangan (Pensiun Dini / Resign / Habis Masa Kontrak)
$alasanBerakhir = $t_mutasi?->deskripsi 
               ?? $t_mutasi?->keterangan 
               ?? $t_mutasi?->catatan 
               ?? $t_mutasi?->m_kary?->alasan_berhenti 
               ?? 'Pensiun Dini';

// Icon Wedge Arrow Hitam Base64 (Identik acuan resmi dan anti tanda tanya '?' di font PDF)
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAXklEQVR4nJ2S0Q4AEAwDV/H/v1zxQIqFWZ93rrGBpGVSUpQJCOBLDa06YJIIG5fXAL4aLEa1arwGoc+B0+AwjkG7pDeoPxCl8gG+APPA3XZbi2uM7HGC3RYB5nz2yBvH5jIZAPwxNAAAAABJRU5ErkJggg==';
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:auto; font-size:11px; line-height:1.25; color:#000;">

  <!-- KOP SURAT RESMI -->
  @include('projects.web_sk_kop')

  @if(!$isJpBooks)
    <!-- DOUBLE LINE SEPARATOR (TABEL AMAN TCPDF TANPA BALOK HITAM) -->
    <table style="width:100%; border-collapse:collapse; margin-top:2px; margin-bottom:6px;">
      <tr><td style="border-bottom:1px solid #000; height:1px; line-height:1px; font-size:1px;"></td></tr>
      <tr><td style="border-bottom:2px solid #000; height:2px; line-height:2px; font-size:1px;"></td></tr>
    </table>
  @endif

  <!-- JUDUL SURAT -->
  <table style="width:100%; border-collapse:collapse; margin-top:3px;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:15px; letter-spacing:1px; text-decoration:underline;">
        SURAT PENGALAMAN KERJA
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11px; padding-top:2px;">
        No. {{ $t_mutasi?->nomor ?? '-' }}
      </td>
    </tr>
  </table>

  <!-- PEMBUKA -->
  <div style="margin-top:8px; font-size:11px;">
    Yang bertanda tangan di bawah ini :
  </div>

  <!-- TABEL PIHAK PENERANG (KOLOM PERSENTASE IDENTIK DENGAN TABEL KARYAWAN) -->
  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:2px; font-size:11px; line-height:1.25;">
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Nama
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;"><b>{{ $signatureNama }}</b></td>
    </tr>
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Bagian&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">
        {{ $signatureJabatan }}<br>
        {{ $signatureCompany }}<br>
        {{ $signatureAlamat }}
      </td>
    </tr>
  </table>

  <!-- PEMBATAS: MENERANGKAN BAHWA -->
  <div style="margin-top:6px; margin-bottom:2px; font-size:11px;">
    Menerangkan bahwa :
  </div>

  <!-- TABEL PIHAK KARYAWAN (KOLOM PERSENTASE IDENTIK DENGAN TABEL PENERANG) -->
  <table width="100%" style="width:100%; border-collapse:collapse; margin-top:2px; font-size:11px; line-height:1.25;">
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Nama
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;"><b>{{ $karyawanNama }}</b></td>
    </tr>
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Status
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">{{ $statusDisplay }}</td>
    </tr>
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Bagian
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">{{ $bagianKaryawanLine1 }}</td>
    </tr>
    @if(!empty($alamatKaryawan))
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top;">&nbsp;</td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">{{ $alamatKaryawan }}</td>
    </tr>
    @endif
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Awal Kerja
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">{{ $awalKerja }} s/d {{ $akhirKerja }}</td>
    </tr>
    <tr>
      <td width="8%" style="width:8%; vertical-align:top;">&nbsp;</td>
      <td width="22%" style="width:22%; vertical-align:top; white-space:nowrap;">
        <img src="{{ $arrowIcon }}" width="8" height="8" style="vertical-align:middle; margin-right:4px;">&nbsp;Keterangan
      </td>
      <td width="4%" style="width:4%; vertical-align:top; text-align:center;">:</td>
      <td width="66%" style="width:66%; vertical-align:top;">{{ $alasanBerakhir }}</td>
    </tr>
  </table>

  <!-- PARAGRAF APRESIASI -->
  <div style="margin-top:6px; font-size:11px; line-height:1.25; text-align:justify;">
    Selama bekerja yang bersangkutan telah menunjukan dedikasi serta kinerja yang baik untuk perusahaan. Untuk itu atas nama manajemen {{ $companyName }} menyampaikan terima kasih atas kerjasamanya selama ini.
  </div>

  <!-- PENUTUP -->
  <div style="margin-top:5px; font-size:11px; line-height:1.25; text-align:justify;">
    Demikian surat pengalaman kerja ini kami buat agar dapat dipergunakan sebagaimana mestinya.
  </div>

  <!-- TANDA TANGAN (LEFT-ALIGNED, NOBR AGAR TIDAK PERNAH TERPOTONG KE HALAMAN 2) -->
  <table nobr="true" style="width:100%; border-collapse:collapse; margin-top:6px; font-size:11px; page-break-inside:avoid;">
    <tr>
      <td style="width:55%; vertical-align:top; text-align:left;">
        {{ $kotaTerbit }}, {{ $tanggalTerbit }}<br>
        {{ $companyName }}
        <div style="height:28px; line-height:28px; font-size:1px;">&nbsp;</div>
        <u><b>{{ $signatureNama }}</b></u><br>
        <b><i>{{ $signatureJabatan }}</i></b>
      </td>
      <td style="width:45%;">&nbsp;</td>
    </tr>
  </table>

  <!-- FOOTER CABANG RESMI (FLOW BIASA RINGKAS, PASTI MUAT DI HALAMAN 1) -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <div style="margin-top:6px; border-top:0.5px solid #bbb; padding-top:2px; font-size:6.2px; line-height:1.15; color:#333; text-align:justify;">
      <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
      <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
      <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
      <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
    </div>
  @endif

</div>