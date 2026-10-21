@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

// Konsiderans Mengingat dari DB jika ada
$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? collect();
if ($memperhatikan instanceof \Illuminate\Support\Collection) {
    $memperhatikan = $memperhatikan->unique(fn($i) => trim($i->value ?? $i['value'] ?? ''))->values();
}

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

// Poin Mengingat (1. ..., 2. ...) tanpa spasi awal
$mengingatLines = [];
if ($memperhatikan && count($memperhatikan) > 0) {
    foreach ($memperhatikan as $index => $item) {
        $val = trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
        if ($val !== '') {
            $mengingatLines[] = ($index + 1) . '. ' . $val;
        }
    }
}
if (empty($mengingatLines)) {
    $mengingatLines[] = '1. Anggaran Dasar ' . $companyName;
    $mengingatLines[] = '2. Peraturan Perusahaan ' . $companyName;
}
$mengingatDisplay = implode('<br>', $mengingatLines);

// Status Karyawan Lama
$statusLama = $t_mutasi?->status_kary_lama?->value 
            ?? $t_mutasi?->m_kary?->status_karyawan?->value 
            ?? 'Karyawan Kontrak';

// Data Pengangkatan
$tglLahirRaw = $t_mutasi?->m_kary?->tgl_lahir;
$tglLahirDisplay = '-';
if ($tglLahirRaw) {
    try {
        $cLahir = Carbon::parse($tglLahirRaw);
        $tglLahirDisplay = $cLahir->format('d/m/Y');
    } catch (\Throwable $e) {
        $tglLahirDisplay = $tglLahirRaw;
    }
}

$jabatan = $t_mutasi?->m_posisi_baru?->name 
         ?? $t_mutasi?->m_kary?->m_posisi?->name 
         ?? '-';

$penempatan = $t_mutasi?->m_branch_baru?->name 
            ?? $t_mutasi?->m_kary?->m_branch?->name 
            ?? $companyName;

// Data Penandatangan (Direktur)
// Jika tidak diinput, dikosongkan dan langsung jabatan (Direktur Utama)
$signatureNama = trim($t_mutasi?->signature?->nama_lengkap ?? '');
$signatureJabatan = $t_mutasi?->signature?->m_posisi?->name ?? 'Direktur Utama';

// PENGATUR MARGIN KERTAS (Semua 3 cm: Atas 3cm, Kiri 3cm, Kanan 3cm, Bawah 3cm)
$jarakAtas = '3cm';
// Untuk kertas A4 (lebar 21cm): Margin Kiri 3cm (14.3%), Konten 15cm (71.4%), Margin Kanan 3cm (14.3%)
$lebarSpacerKiri = '14.3%';
$lebarKonten = '71.4%';
$lebarSpacerKanan = '14.3%';

// PENGATUR JUMLAH ENTER TANDA TANGAN (Antara PT Temprina dan Nama/Jabatan Direktur)
// Default: '<br><br>' (2 enter) atau '<br><br><br>' (3 enter)
$enterTtd = '<br><br>';

// PENGATUR JARAK ANTARA "PADA TANGGAL" DENGAN NAMA PERUSAHAAN (Bisa diubah manual: '2px', '4px', dll)
$jarakTanggalKePt = '2px';
@endphp

<style>
  @page {
    size: A4 portrait;
    margin: 0;
  }
</style>

<!-- ========================================================================= -->
<!-- TABEL MASTER LAYOUT (MEMASTIKAN MARGIN KIRI & KANAN 3 CM AKTIF DI PDF)    -->
<!-- ========================================================================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-family:'Times New Roman', Times, serif; font-size:11.5px; line-height:1.35; color:#000;">
  <tr>
    <!-- MARGIN KIRI (3 cm) -->
    <td width="{{ $lebarSpacerKiri }}" style="width:{{ $lebarSpacerKiri }}; vertical-align:top;">&nbsp;</td>

    <!-- KONTEN TENGAH SURAT (15 cm) -->
    <td width="{{ $lebarKonten }}" style="width:{{ $lebarKonten }}; vertical-align:top;">

      <!-- MARGIN ATAS (3 cm): DARI ATAS KERTAS KE JUDUL SURAT -->
      <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr style="line-height:{{ $jarakAtas }};">
          <td style="height:{{ $jarakAtas }}; line-height:{{ $jarakAtas }}; font-size:1px;">&nbsp;</td>
        </tr>
      </table>

  <!-- JUDUL SURAT -->
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:15px; letter-spacing:3px;">
        <u>SURAT KEPUTUSAN</u>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11.5px; padding-top:4px;">
        Direksi {{ $companyName }}
      </td>
    </tr>
    <!-- ENTER SETELAH NAMA COMPANY -->
    <tr style="line-height:12px;">
      <td style="height:12px; line-height:12px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11px;">
        Tentang
      </td>
    </tr>
    <!-- ENTER SETELAH TENTANG -->
    <tr style="line-height:12px;">
      <td style="height:12px; line-height:12px; font-size:1px;">&nbsp;</td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:12px;">
        PENGANGKATAN KARYAWAN TETAP Th.{{ $carbonDate->year }}
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-size:11px; padding-top:2px;">
        No. {{ $t_mutasi?->nomor ?? '-' }}
      </td>
    </tr>
    <!-- ENTER SETELAH NOMOR KE DIREKSI -->
    <tr style="line-height:12px;">
      <td style="height:12px; line-height:12px; font-size:1px;">&nbsp;</td>
    </tr>
  </table>

  <!-- KONSIDERANS MENIMBANG & MENGINGAT -->
  <table width="100%" style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.35;">
    <!-- PREAMBLE DIREKSI -->
    <tr>
      <td colspan="3" width="100%" style="width:100%; vertical-align:top;">Direksi {{ $companyName }}</td>
    </tr>
    <tr>
      <td colspan="3" style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td>
    </tr>
    <!-- MENIMBANG -->
    <tr>
      <td width="16%" style="width:16%; vertical-align:top;">MENIMBANG</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="81%" style="width:81%; vertical-align:top;">Bahwa nama karyawan {{ strtoupper(trim($t_mutasi?->m_kary?->nama_lengkap ?? '-')) }}<br>{{ $statusLama }} - {{ $companyName }}</td>
    </tr>
    <tr>
      <td colspan="3" style="height:6px; line-height:6px; font-size:1px;">&nbsp;</td>
    </tr>
    <!-- MENGINGAT -->
    <tr>
      <td width="16%" style="width:16%; vertical-align:top;">MENGINGAT</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="81%" style="width:81%; vertical-align:top;">{!! $mengingatDisplay !!}</td>
    </tr>
    <!-- ENTER SETELAH MENGINGAT KE MEMUTUSKAN -->
    <tr style="line-height:12px;">
      <td colspan="3" style="height:12px; line-height:12px; font-size:1px;">&nbsp;</td>
    </tr>
  </table>

  <!-- MEMUTUSKAN -->
  <div style="text-align:center; font-weight:bold; font-size:12px; margin-top:0px; margin-bottom:6px; letter-spacing:1px;">
    MEMUTUSKAN
  </div>

  <!-- DIKTUM KEPUTUSAN (SATU TABEL TUNGGAL AGAR JARAK ~ KE TEKS DEKAT DAN SEJAJAR SEMPURNA) -->
  <table width="100%" style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.35;">
    <!-- MENETAPKAN -->
    <tr>
      <td colspan="5" width="100%" style="width:100%; font-weight:bold; vertical-align:top;">MENETAPKAN :</td>
    </tr>
    <tr>
      <td colspan="5" style="height:6px; line-height:6px; font-size:1px;">&nbsp;</td>
    </tr>
    <!-- PERTAMA -->
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">Pertama</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:left;">~</td>
      <td width="26%" style="width:26%; vertical-align:top;">Mengangkat Karyawan</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="56%" style="width:56%; vertical-align:top;"><b>{{ strtoupper(trim($t_mutasi?->m_kary?->nama_lengkap ?? '-')) }}</b></td>
    </tr>
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">&nbsp;</td>
      <td width="3%" style="width:3%; vertical-align:top;">&nbsp;</td>
      <td width="26%" style="width:26%; vertical-align:top;">Tanggal Lahir</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="56%" style="width:56%; vertical-align:top;">{{ $tglLahirDisplay }}</td>
    </tr>
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">&nbsp;</td>
      <td width="3%" style="width:3%; vertical-align:top;">&nbsp;</td>
      <td width="26%" style="width:26%; vertical-align:top;">Bagian dan Tempat</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="56%" style="width:56%; vertical-align:top;">{{ $jabatan }} - {{ $penempatan }}</td>
    </tr>
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">&nbsp;</td>
      <td width="3%" style="width:3%; vertical-align:top;">&nbsp;</td>
      <td width="26%" style="width:26%; vertical-align:top;">Karyawan Tetap</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td width="56%" style="width:56%; vertical-align:top;">{{ $tanggalIndo }}</td>
    </tr>

    <!-- JARAK PERTAMA KE KEDUA -->
    <tr>
      <td colspan="5" style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- KEDUA -->
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">Kedua</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:left;">~</td>
      <td colspan="3" width="85%" style="width:85%; vertical-align:top; text-align:justify;">Karyawan yang namanya tersebut di atas harus memenuhi semua kewajiban dan tanggung jawab yang ada serta mentaati segala peraturan atau hukum yang ada / berlaku dan tunduk terhadap keputusan Perusahaan.</td>
    </tr>

    <!-- JARAK KEDUA KE KETIGA -->
    <tr>
      <td colspan="5" style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td>
    </tr>

    <!-- KETIGA -->
    <tr>
      <td width="12%" style="width:12%; vertical-align:top;">Ketiga</td>
      <td width="3%" style="width:3%; vertical-align:top; text-align:left;">~</td>
      <td colspan="3" width="85%" style="width:85%; vertical-align:top; text-align:justify;">Surat keputusan ini berlaku sejak tanggal ditetapkan. Jika di kemudian hari terdapat kesalahan dalam surat keputusan ini, maka akan diadakan perbaikan seperlunya.</td>
    </tr>
    <!-- ENTER SETELAH KETIGA KE TANDA TANGAN -->
    <tr style="line-height:10px;">
      <td colspan="5" style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td>
    </tr>
  </table>

  <!-- TANDA TANGAN (RIGHT-OFFSET, NOBR AGAR TIDAK PERNAH TERPOTONG) -->
  <table nobr="true" style="width:100%; border-collapse:collapse; font-size:11.5px; line-height:1.35; page-break-inside:avoid;">
    <tr>
      <td width="55%" style="width:55%;">&nbsp;</td>
      <td width="45%" style="width:45%; vertical-align:top; text-align:left;">
        Ditetapkan di : {{ $kotaTerbit }}<br>
        Pada Tanggal : {{ $tanggalTerbit }}
        <div style="height:{{ $jarakTanggalKePt }}; line-height:{{ $jarakTanggalKePt }}; font-size:1px;">&nbsp;</div>
        {{ $companyName }}{!! $enterTtd !!}
        @if(!empty($signatureNama))
          <br><u><b>{{ strtoupper($signatureNama) }}</b></u><br>
        @else
          <br>
        @endif
        <i>{{ $signatureJabatan }}</i>
      </td>
    </tr>
  </table>

    </td>

    <!-- MARGIN KANAN (3 cm) -->
    <td width="{{ $lebarSpacerKanan }}" style="width:{{ $lebarSpacerKanan }}; vertical-align:top;">&nbsp;</td>
  </tr>
</table>