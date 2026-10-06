@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_kary;
use App\Models\CustomModels\m_kary_det_jabatan;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$nomor = $t_mutasi?->nomor ?? '-';
$tgl = $t_mutasi?->tgl ?? now();
$carbonDate = Carbon::parse($tgl);
$tanggalIndo = $carbonDate->translatedFormat('d F Y');
$tglEfektif = $carbonDate->format('d/m/Y');

// Resolusi Perusahaan
$sbuCode = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sub_lama?->m_sbu?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? $t_mutasi?->m_kary?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : $compRaw;
$companyNameUpper = strtoupper($companyName);

$alamatCompany = $t_mutasi?->m_sub_lama?->m_company?->alamat 
              ?? $t_mutasi?->m_sub_lama?->address 
              ?? $t_mutasi?->m_branch_lama?->address 
              ?? $t_mutasi?->m_kary?->m_branch?->address 
              ?? 'Jl. Karah Agung 45 Surabaya';

// Resolusi Pihak 1 (Signature / Direktur)
$signature = $t_mutasi?->signature;
$sigId = $t_mutasi?->signature_id;
if (!$signature && !empty($sigId)) {
    try {
        $signature = m_kary::find($sigId) ?? \App\Models\BasicModels\m_kary::find($sigId);
    } catch (\Throwable $e) {}
}
$namaDirektur = $signature?->nama_lengkap ?? 'NAMA DIREKTUR';
$jabatanDirektur = $signature?->m_posisi?->name ?? 'Direktur';

// Resolusi Pihak 2 (Karyawan)
$karyawan = $t_mutasi?->m_kary;
$namaKaryawan = strtoupper($karyawan?->nama_lengkap ?? '-');
$nikKaryawan = $karyawan?->nik ?: ($karyawan?->kode ?: '-');
$alamatKaryawan = $karyawan?->alamat_domisili ?: ($karyawan?->alamat_asli ?: ($karyawan?->address ?: '-'));

$posisiKaryawan = trim($t_mutasi?->m_posisi_lama?->name ?? $karyawan?->m_posisi?->name ?? '-');
$subcompKaryawan = trim($t_mutasi?->m_sub_lama?->name ?? $karyawan?->m_subcomp?->name ?? '');
if (!empty($subcompKaryawan) && !str_contains(strtoupper($posisiKaryawan), strtoupper($subcompKaryawan))) {
    $jabatanKaryawan = $posisiKaryawan . ' ' . $subcompKaryawan;
} else {
    $jabatanKaryawan = $posisiKaryawan;
}

// Kompensasi
$kompensasiRaw = $t_mutasi?->kompensasi ?? 0;
$kompensasiFormatted = number_format((float)$kompensasiRaw, 0, ',', '.');

// Perjanjian Kerja Sebelumnya
$prevMutasi = null;
if (!empty($t_mutasi?->m_kary_id)) {
    try {
        $prevMutasi = t_mutasi::where('m_kary_id', $t_mutasi->m_kary_id)
            ->where('id', '!=', $t_mutasi->id)
            ->whereNotNull('nomor')
            ->orderBy('tgl', 'desc')
            ->first();
    } catch (\Throwable $th) {}
}
$noPerjanjianKerja = $t_mutasi?->no_dokumen 
                   ?: ($prevMutasi?->nomor 
                   ?: '006/Sl.211221/TMG/SBY/HRD/TTP');
$tglPerjanjianKerja = $prevMutasi?->tgl 
                    ? Carbon::parse($prevMutasi->tgl)->format('d/m/Y') 
                    : '21/12/2021';
@endphp

<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:auto; font-size:10pt; line-height:1.26; color:#000;">

  <!-- KOP SURAT RESMI -->
  @include('projects.web_sk_kop')

  @if(!$isJpBooks)
    <!-- DOUBLE LINE SEPARATOR -->
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
      <tr><td style="border-bottom:1px solid #000; height:1px; line-height:1px; font-size:1px;">&nbsp;</td></tr>
      <tr><td style="border-bottom:2px solid #000; height:2px; line-height:2px; font-size:1px;">&nbsp;</td></tr>
      <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
    </table>
  @else
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
      <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
    </table>
  @endif

  <!-- JUDUL DOKUMEN -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
    <tr>
      <td style="text-align:center; margin:0; padding:0;">
        <span style="font-size:14pt; font-weight:bold; text-decoration:underline;">PERJANJIAN BERSAMA</span><br>
        <span style="font-size:10.5pt; font-weight:bold;">No. {{ $nomor }}</span>
      </td>
    </tr>
    <tr><td style="height:10px; line-height:10px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- PEMBUKA -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr>
      <td style="text-align:justify; margin:0; padding:0;">Perjanjian Bersama Pemutusan Hubungan Kerja ("PHK") dan Penerimaan Kompensasi atas PHK (selanjutnya disebut dengan "PB") dibuat dan disepakati oleh kami yang bertanda tangan dibawah ini:</td>
    </tr>
    <tr><td style="height:5px; line-height:5px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- PIHAK 1 (PEMBERI KERJA) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">1.</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">Nama</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;"><b>{{ $namaDirektur }}</b></td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">Jabatan</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;">{{ $jabatanDirektur }}</td>
    </tr>
    <tr>
      <td colspan="4" style="text-align:justify; padding:2px 0 5px 0;">Dalam jabatannya yang sah bertindak untuk dan atas nama {{ $companyName }} yang beralamat dan berkedudukan hukum di {{ $alamatCompany }} ; untuk selanjutnya dalam PB ini sebagai dan disebut dengan <b>PEMBERI KERJA</b></td>
    </tr>
  </table>

  <!-- PIHAK 2 (PENERIMA KERJA) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">2.</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">Nama</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;"><b>{{ $namaKaryawan }}</b></td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">NIK</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;">{{ $nikKaryawan }}</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">Alamat</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;">{{ $alamatKaryawan }}</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1px 0;">&nbsp;</td>
      <td width="15%" style="width:15%; vertical-align:top; padding:1px 0;">Jabatan</td>
      <td width="2.5%" style="width:2.5%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td width="79%" style="width:79%; vertical-align:top; padding:1px 0;">{{ $jabatanKaryawan }}</td>
    </tr>
    <tr>
      <td colspan="4" style="text-align:justify; padding:2px 0 5px 0;">Bertindak selaku pribadi, untuk dan atas nama diri sendiri yang dalam PB ini sebagai dan disebut dengan <b>PENERIMA KERJA</b></td>
    </tr>
  </table>

  <!-- KETENTUAN HUBUNGAN KERJA -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr><td style="height:4px; line-height:4px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:justify; margin:0; padding:0;"><b>PEMBERI KERJA</b> dan <b>PENERIMA KERJA</b> bertindak secara bersama-sama disebut dengan <b>PARA PIHAK</b>. <b>PARA PIHAK</b> menerangkan bahwa telah memiliki hubungan kerja sebagaimana disepakati dalam Perjanjian Kerja Nomor {{ $noPerjanjianKerja }} tanggal {{ $tglPerjanjianKerja }} dan pada waktu serta tempat yang tercantum di bagian bawah PB ini telah menyepakati bahwa:</td>
    </tr>
    <tr><td style="height:5px; line-height:5px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- 5 POIN KESEPAKATAN -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1.5px 0;">1.</td>
      <td width="96.5%" style="width:96.5%; vertical-align:top; text-align:justify; padding:1.5px 0;"><b>PARA PIHAK</b> sepakat untuk mengakhiri hubungan kerja efektif sejak tanggal {{ $tglEfektif }}. ("Tanggal Efektif")</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1.5px 0;">2.</td>
      <td width="96.5%" style="width:96.5%; vertical-align:top; text-align:justify; padding:1.5px 0;">Sehubungan dengan PB ini dan menurut ketentuan hukum yang berlaku, maka <b>PEMBERI KERJA</b> akan memberikan uang kompensasi dan <b>PENERIMA KERJA</b> telah mengetahui dan sepakat atas jumlah uang kompensasi tersebut yakni sejumlah&nbsp;&nbsp;&nbsp;&nbsp;<b>Rp.{{ $kompensasiFormatted }},-</b> ("Uang Kompensasi")</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1.5px 0;">3.</td>
      <td width="96.5%" style="width:96.5%; vertical-align:top; text-align:justify; padding:1.5px 0;"><b>PEMBERI KERJA</b> menjamin bahwa akan tetap bekerja dan menyelesaikan tugas serta tanggungjawabnya hingga Tanggal Efektif termasuk namun tidak terbatas pada membuat laporan atau serah terima pekerjaan dan/atau membantu melakukan serah terima pekerjaan dengan karyawan Pengganti.</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1.5px 0;">4.</td>
      <td width="96.5%" style="width:96.5%; vertical-align:top; text-align:justify; padding:1.5px 0;"><b>PARA PIHAK</b> sepakat bahwa Uang Kompensasi tersebut akan diberikan setelah <b>PENERIMA KERJA</b> menyelesaikan tugas dan tanggungjawabnya sebagaimana disepakati dalam poin 3 (tiga) diatas yang dibuktikan dengan laporan pekerjaan dan/atau laporan serah terima pekerjaan yang disetujui atasan langsung <b>PENERIMA KERJA</b>.</td>
    </tr>
    <tr>
      <td width="3.5%" style="width:3.5%; vertical-align:top; text-align:left; padding:1.5px 0;">5.</td>
      <td width="96.5%" style="width:96.5%; vertical-align:top; text-align:justify; padding:1.5px 0;"><b>PARA PIHAK</b> sepakat untuk mengesampingkan ketentuan untuk mendaftarkan PB ini pada Pengadilan Hubungan Industrial maupun instansi terkait lainnya dan saling menyatakan bahwa PB ini sah dan berlaku cukup dengan ditandatanganinya <b>PARA PIHAK</b>.</td>
    </tr>
  </table>

  <!-- PENUTUP -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr><td style="height:5px; line-height:5px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:justify; margin:0; padding:0;">Setelah PB ini disepakati dan dilaksanakan, maka <b>PARA PIHAK</b> baik saat ini maupun dikemudian hari sepakat untuk tidak akan saling melakukan tuntutan apapun juga sepanjang atas pelaksanaan ketentuan-ketentuan yang disepakati dalam PB ini.</td>
    </tr>
    <tr><td style="height:5px; line-height:5px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="text-align:justify; margin:0; padding:0;">Demikianlah PB ini dibuat dan disepakati <b>PARA PIHAK</b> di {{ $companyName }} {{ $alamatCompany }} pada tanggal {{ $tglEfektif }} .</td>
    </tr>
  </table>

  <!-- TANDA TANGAN (DUA PIHAK: PEMBERI KERJA & PENERIMA KERJA) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.26;">
    <tr><td colspan="2" style="height:14px; line-height:14px; font-size:1px;">&nbsp;</td></tr>
    <tr>
      <td style="width:58%; vertical-align:top; text-align:left; margin:0; padding:0;">
        PEMBERI KERJA<br>
        <b>{{ $companyNameUpper }}</b><br><br><br><br>
        @if($namaDirektur !== 'NAMA DIREKTUR' && $namaDirektur !== '( ........................................ )')<u><b>{{ $namaDirektur }}</b></u>@else<b>{{ $namaDirektur }}</b>@endif<br>
        {{ $jabatanDirektur }}
      </td>
      <td style="width:42%; vertical-align:top; text-align:left; margin:0; padding:0;">
        PENERIMA KERJA<br><br><br><br><br>
        <b><u>{{ $namaKaryawan }}</u></b>
      </td>
    </tr>
  </table>

  <!-- FOOTER CABANG RESMI -->
  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
      <tr><td style="height:14px; line-height:14px; font-size:1px;">&nbsp;</td></tr>
      <tr><td style="border-top:0.5px solid #bbb; height:1px; line-height:1px; font-size:1px;">&nbsp;</td></tr>
      <tr>
        <td style="padding-top:2px; font-size:6px; line-height:1.15; color:#333; text-align:justify;">
<b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
<b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
<b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
<b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
        </td>
      </tr>
    </table>
  @endif

</div>