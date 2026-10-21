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

// PENGATUR MARGIN KERTAS (Disamakan dengan SK Tetap)
$jarakAtas = '3cm';
// Untuk kertas A4 (lebar 21cm): Margin Kiri 4.3%, Konten 83.4%, Margin Kanan 12.3%
$lebarSpacerKiri = '4.3%';
$lebarKonten = '83.4%';
$lebarSpacerKanan = '12.3%';

// PENGATUR INDENT TANDA PANAH (Default '8%' agar menjorok ke dalam)
$indentPanah = '8%';

// PENGATUR JARAK TANDA PANAH KE LABEL (Ubah persentase ini untuk atur jarak: '4%', '5%', '6%', dll)
// Tanda panah rata kiri, sehingga sisa lebar kolom ini menjadi jarak kosong pasti ke teks label
$lebarKolomPanah = '5%';

$lebarKolomLabel = '16%';
$lebarKolomTitikDua = '3%';
$lebarKolomIsi = '68%';

// PENGATUR JARAK DARI NOMOR SURAT KE "Yang bertanda tangan di bawah ini :"
$jarakNomorKePembuka = '2cm';

// PENGATUR SPASI ANTAR BLOK SURAT (Bisa disesuaikan ukurannya, misal '14px', '16px', '20px', dll)
$jarakPihak1KePihak2 = '16px';     // Spasi antara Pihak 1 (Yang bertanda tangan) dengan "Menerangkan bahwa :"
$jarakPihak2KeParagraf = '16px';   // Spasi antara Pihak 2 (Menerangkan bahwa) dengan "Selama bekerja..."

// PENGATUR JUMLAH ENTER TANDA TANGAN (Antara PT dan Nama Penandatangan)
// Default 3 enter: '<br><br><br>' (jika ingin 2 enter: '<br><br>', 4 enter: '<br><br><br><br>')
$enterTtd = '<br><br><br>';

// Icon Pointer Panah 3D Wingdings Base64 (100% identik dengan PDF acuan resmi)
$arrowIcon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAF4SURBVFhHxZehb4NAGMVfTjQoUtVUkckqMjnVICcnJ5GTkzjmkJOd25/An4CcJFPIZWqZIpshE4TlNdC0X2gDtHf3S56ivPea47sDAFjCMisAbwDu5AWTRAAaALmtIo5S6r0tYa3INcMdx2lc161tFUkYvNlsmjiOrRThUnwuFou6LMuGslEkYFgYhk2HjSKvDMqybFfCdJG5Uurb87y6qqqDEiaL3NM8iiKZv8NEkZTGeZ7L7AN0FlkqpX583+9dComuIg80S5JE5h1FR5FsNpvVRVHIrJNcsshKKfW3Xq9rjuZYpWnaBEHQnTOTi3Qn5iXFIoOZA/jtMRmrAsALgKdWg9keVGcoa0/cSfC1reoxHSIG83w5i+2GNFJc31tpNIWbHvNTmvSEn4KGMqRPHwBCefO50FAGSX3pCCZOay4D94Mf299p4djYle2mpC2Y9I0dg7lxcEPSzv7YscizqWDSjV0XbPzbkWPHl9IrecEE/LdWgq3xD8U6LuwJEeE6AAAAAElFTkSuQmCC';
@endphp

<style>
  @page {
    size: A4 portrait;
    margin: 0;
  }
</style>

<!-- ========================================================================= -->
<!-- MARGIN TOP (3 cm): DARI ATAS KERTAS KE JUDUL SURAT                        -->
<!-- ========================================================================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
  <tr style="line-height:{{ $jarakAtas }};">
    <td style="height:{{ $jarakAtas }}; line-height:{{ $jarakAtas }}; font-size:1px;">&nbsp;</td>
  </tr>
</table>

<!-- ========================================================================= -->
<!-- JUDUL SURAT & NOMOR (100% LEBAR KERTAS A4, PERSIS DI TENGAH SURAT)        -->
<!-- ========================================================================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-family:'Times New Roman', Times, serif; color:#000;">
  <tr>
    <td width="100%" style="width:100%; text-align:center; font-weight:bold; font-size:14.5px;"><u>SURAT PENGALAMAN KERJA</u></td>
  </tr>
  <tr>
    <td width="100%" style="width:100%; text-align:center; font-size:11px; padding-top:2px;">No. {{ $t_mutasi?->nomor ?? '-' }}</td>
  </tr>
</table>

<!-- ========================================================================= -->
<!-- JARAK (2 cm): DARI NOMOR SURAT KE "Yang bertanda tangan di bawah ini :"   -->
<!-- ========================================================================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
  <tr style="line-height:{{ $jarakNomorKePembuka }};">
    <td style="height:{{ $jarakNomorKePembuka }}; line-height:{{ $jarakNomorKePembuka }}; font-size:1px;">&nbsp;</td>
  </tr>
</table>

<!-- ========================================================================= -->
<!-- TABEL MASTER LAYOUT (MARGIN KIRI & KANAN ISI SURAT TETAP SESUAI SETTINGAN)-->
<!-- ========================================================================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-family:'Times New Roman', Times, serif; font-size:11.5px; line-height:1.3; color:#000;">
  <tr>
    <!-- MARGIN KIRI -->
    <td width="{{ $lebarSpacerKiri }}" style="width:{{ $lebarSpacerKiri }}; vertical-align:top;">&nbsp;</td>

    <!-- KONTEN TENGAH SURAT -->
    <td width="{{ $lebarKonten }}" style="width:{{ $lebarKonten }}; vertical-align:top;">

  <!-- TABEL UTAMA ISI SURAT (SEMUA ELEMEN SEJAJAR SEMPURNA 100% DI GARIS KIRI YANG SAMA) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.3;">
    <!-- PEMBUKA PIHAK 1 -->
    <tr>
      <td colspan="5" style="text-align:left;">Yang bertanda tangan di bawah ini :</td>
    </tr>
    <tr style="line-height:6px;">
      <td colspan="5" style="height:6px; line-height:6px; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- PIHAK PENERANG -->
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Nama</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ strtoupper(trim($signatureNama)) }}</td>
    </tr>
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Bagian</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ trim($signatureJabatan) }}<br>{{ trim($signatureCompany) }}<br>{{ trim($signatureAlamat) }}</td>
    </tr>

    <!-- SPASI ANTARA YANG BERTANDA TANGAN DENGAN MENERANGKAN BAHWA -->
    <tr style="line-height:{{ $jarakPihak1KePihak2 }};">
      <td colspan="5" style="height:{{ $jarakPihak1KePihak2 }}; line-height:{{ $jarakPihak1KePihak2 }}; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- PEMBUKA PIHAK 2 -->
    <tr>
      <td colspan="5" style="text-align:left;">Menerangkan bahwa :</td>
    </tr>
    <tr style="line-height:6px;">
      <td colspan="5" style="height:6px; line-height:6px; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- PIHAK KARYAWAN -->
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Nama</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ strtoupper($karyawanNama) }}</td>
    </tr>
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Status</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ $statusDisplay }}</td>
    </tr>
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Bagian</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ $bagianKaryawanLine1 }}@if(!empty($alamatKaryawan))<br>{{ $alamatKaryawan }}@endif</td>
    </tr>
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Awal Kerja</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ $awalKerja }} s/d {{ $akhirKerja }}</td>
    </tr>
    <tr>
      <td width="{{ $indentPanah }}" style="width:{{ $indentPanah }}; vertical-align:top;">&nbsp;</td>
      <td width="{{ $lebarKolomPanah }}" style="width:{{ $lebarKolomPanah }}; vertical-align:top; text-align:left; padding-top:2px;"><img src="{{ $arrowIcon }}" width="10" height="10" alt="►"></td>
      <td width="{{ $lebarKolomLabel }}" style="width:{{ $lebarKolomLabel }}; vertical-align:top;">Keterangan</td>
      <td width="{{ $lebarKolomTitikDua }}" style="width:{{ $lebarKolomTitikDua }}; vertical-align:top; text-align:center;">:</td>
      <td width="{{ $lebarKolomIsi }}" style="width:{{ $lebarKolomIsi }}; vertical-align:top;">{{ $alasanBerakhir }}</td>
    </tr>

    <!-- SPASI ANTARA MENERANGKAN BAHWA DENGAN SELAMA BEKERJA YANG BERSANGKUTAN -->
    <tr style="line-height:{{ $jarakPihak2KeParagraf }};">
      <td colspan="5" style="height:{{ $jarakPihak2KeParagraf }}; line-height:{{ $jarakPihak2KeParagraf }}; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- PARAGRAF APRESIASI & PENUTUP -->
    <tr>
      <td colspan="5" style="text-align:justify; line-height:1.35;">Selama bekerja yang bersangkutan telah menunjukan dedikasi serta kinerja yang baik untuk perusahaan. Untuk itu atas nama manajemen {{ $companyName }} menyampaikan terima kasih atas kerjasamanya selama ini.</td>
    </tr>
    <tr style="line-height:10px;">
      <td colspan="5" style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="5" style="text-align:justify; line-height:1.35;">Demikian surat pengalaman kerja ini kami buat agar dapat dipergunakan sebagaimana mestinya.</td>
    </tr>
  </table>

  <!-- JARAK KE TANDA TANGAN (2 ENTER) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <tr style="line-height:16px;">
      <td style="height:16px; line-height:16px; font-size:1px;">&nbsp;</td>
    </tr>
  </table>

  <!-- TANDA TANGAN (LEFT-ALIGNED, NOBR AGAR TIDAK PERNAH TERPOTONG KE HALAMAN BERIKUTNYA) -->
  <table nobr="true" width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.3; page-break-inside:avoid;">
    <tr>
      <td style="width:60%; vertical-align:top; text-align:left;">{{ $kotaTerbit }}, {{ $tanggalTerbit }}<br>{{ $companyName }}{!! $enterTtd !!}<u><b>{{ strtoupper($signatureNama) }}</b></u><br><b><i>{{ strtoupper($signatureJabatan) }}</i></b></td>
      <td style="width:40%;">&nbsp;</td>
    </tr>
  </table>

    </td>

    <!-- MARGIN KANAN -->
    <td width="{{ $lebarSpacerKanan }}" style="width:{{ $lebarSpacerKanan }}; vertical-align:top;">&nbsp;</td>
  </tr>
</table>