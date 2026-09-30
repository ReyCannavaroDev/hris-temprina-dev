@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
use App\Models\CustomModels\m_kary_det_jabatan;
use App\Models\CustomModels\m_standart_gaji;
Carbon::setLocale('id');
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
$tanggalNoDay = $carbonDate->translatedFormat('d F Y');
$tanggal = $carbonDate->translatedFormat('d/m/Y');


$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_mutasi->t_mutasi_d_tembusan ?? null;
$memperhatikan = $t_mutasi->t_mutasi_d_memperhatikan ?? null;

$karyawan_jabatan = m_kary_det_jabatan::where('m_karyawan_id', $t_mutasi->m_kary_id)
->orderBy('start_time', 'asc')
->first();

$awalKerja = $karyawan_jabatan ? Carbon::parse($karyawan_jabatan->tgl_mulai)->translatedFormat('d F Y') : '-';
$akhirKerja = $karyawan_jabatan && $karyawan_jabatan->tgl_selesai
? Carbon::parse($karyawan_jabatan->tgl_selesai)->translatedFormat('d F Y')
: 'Sekarang';

$tgl_lahir = $t_mutasi->m_kary->tgl_lahir;
$umur = Carbon::parse($tgl_lahir)->diffInYears($carbonDate);

$lastEdu = $t_mutasi->m_kary->m_kary_det_pend()->where('is_pend_terakhir', true)->first();
$pendidikanTerakhir = $lastEdu ? $lastEdu->jurusan . " " . $lastEdu->nama_sekolah : "-";

$kontrak = $t_mutasi->m_kary->m_kary_det_jabatan()->where('is_active', true)->where('is_primary', true)->first();
$durasiKontrak = $kontrak ? Carbon::parse($kontrak->start_time)->translatedFormat('d F Y') . " sampai dengan " .
Carbon::parse($kontrak->end_time)->translatedFormat('d F Y') : "-";
$lamaKontrak = Carbon::parse($kontrak->start_time)->diffInMonths($kontrak->end_time);


$salary = m_standart_gaji::where('m_kary_id', $t_mutasi->m_kary_id)->first();
$gapok = $salary ? $salary->gaji_pokok : 0;
$tprod = $salary ? $salary->tunjangan_produktifitas : 0;
$ttrans = $salary ? $salary->tunjangan_transport : 0;
$totalSalary = $gapok + $tprod + $ttrans;

$gapok = number_format($gapok, 0, ',', '.');
$tprod = number_format($tprod, 0, ',', '.');
$ttrans = number_format($ttrans, 0, ',', '.');
$totalSalary = number_format($totalSalary, 0, ',', '.');


$kompensasiRaw = $t_mutasi?->kompensasi ?? 0;
$kompensasi = number_format($kompensasiRaw, 0, ',', '.');

@endphp


<div></div>
<table style="width:100%; margin-top:20px; font-family:'Times New Roman', serif;">
  <tr>
    <td style="width:18%;"></td>

    <td style="
        width:64%;
        text-align:center;
        font-weight:bold;
        font-size:14px;
        border-bottom:2px solid black;
        padding-bottom:4px;
        letter-spacing:0.5px;">
      PERJANJIAN KERJA WAKTU TERTENTU (HL)
    </td>

    <td style="width:18%;"></td>
  </tr>

  <tr>
    <td></td>

    <td style="
        text-align:center;
        font-size:13px;
        padding-top:6px;">
      <span style="font-weight:bold;">No.</span> {{$t_mutasi?->nomor}}
    </td>

    <td></td>
  </tr>

    <tr>
    <td></td>
  </tr>
</table>

<table style="font-family:Times New Roman; font-size:11px;">
  <tr>
    <td>Kami yang bertanda tangan di bawah ini :</td>
  </tr>
</table>
<table cellspacing="0" style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:11px;">
  <tr>
    <td rowspan="4" style="width: 5%;">1.</td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->signature?->nama_lengkap ?? ''}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jabatan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_posisi?->name ?? ''}}</td>
  </tr>
   <tr>
    <td style="width: 25%;">Alamat Perusahaan</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_subcomp?->m_company?->address ?? ''}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jenis Usaha</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->signature?->m_subcomp?->m_comp?->name ?? ''}}</td>
  </tr>


  <tr>
    <td></td>
  </tr>

  <tr>
    <td></td>
    <td colspan="3">Bertindak atas nama {{$t_mutasi->signature?->m_posisi?->name ?? '-'}} {{$t_mutasi->signature?->m_subcomp?->m_company?->name ?? '-'}}, selanjutnya disebut sebagai Pemberi Kerja
      atau PIHAK PERTAMA.</td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <tr>
    <td rowspan="699" style="width: 5%;">2.</td>
    <td style="width: 25%;">Nama</td>
    <td style="width: 3%;">:</td>
    <td style="width: 50%;">{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Jenis Kelamin</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->m_kary?->jk?->value ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">NIK</td>
    <td style="width: 3%;">:</td>
    <td>{{$t_mutasi->m_kary?->nik ?? '-'}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Alamat</td>
    <td style="width: 3%;">:</td>
    <td>{{($t_mutasi->m_kary?->address ?? '-') . ' ' . ($t_mutasi->m_kary?->city?->value ?? '-')}}</td>
  </tr>
  <tr>
    <td style="width: 25%;">Pendidikan</td>
    <td style="width: 3%;">:</td>
    <td>{{$pendidikanTerakhir}}</td>
  </tr>

  <tr>
    <td></td>
  </tr>
</table>

<table style="width:100%;margin-top:20px; font-family:Times New Roman; font-size:11px;">
  <tr>
    <td colspan="3">PIHAK PERTAMA dan PIHAK KEDUA, bersama-sama dalam Perjanjian ini disebut sebagai PARA PIHAK.</td>
  </tr>


  <tr>
    <td></td>
  </tr>

  <tr>
    <td colspan="3">PARA PIHAK, pada hari ini {{$tanggalIndo}} telah bersepakat untuk membuat dan menandatangani
      Perjanjian Kerja
      Waktu Tertentu (PKWT) dengan sistem upah harian (harian lepas) yang dalam hal ini disebut dengan “Perjanjian
      Kerja”dengan
      syarat-syarat tersebut di bawah ini :</td>
  </tr>
</table>

<tr>
  <td></td>
</tr>

<table width="100%" cellpadding="4" cellspacing="0" style="font-family:Times New Roman; font-size:11px;">

  <!-- PASAL 1 -->
  <tr>
    <td colspan="2"><b>PASAL 1. MULAI DAN JANGKA WAKTU BERLAKUNYA PERJANJIAN KERJA :</b></td>
  </tr>
  <tr>
    <td width="5%">1.1</td>
    <td style="width: 95%">
      PARA PIHAK sepakat bahwa Perjanjian Kerja ini berlaku selama {{$lamaKontrak}} bulan mulai tanggal
      <b>{{$durasiKontrak}}</b>.
    </td>
  </tr>
  <tr>
    <td>1.2</td>
    <td>
      PIHAK PERTAMA berhak untuk memperpanjang maupun tidak memperpanjang Perjanjian Kerja ini
      berdasarkan kebutuhan volume pekerjaan, evaluasi dan hasil kerja PIHAK KEDUA yang
      pelaksanaannya didasarkan pada Keputusan Menteri Tenaga Kerja Nomor : 100/MEN/VI/2004
      atau Peraturan perundang-undangan yang terkait.
    </td>
  </tr>

  <!-- PASAL 2 -->
  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 2. PENEMPATAN TUGAS, HARI dan JAM KERJA :</b>
    </td>
  </tr>
  <tr>
    <td>2.1</td>
    <td>
      PARA PIHAK sepakat bahwa PIHAK KEDUA akan dipekerjakan pada bagian
      <b>{{$t_mutasi->m_posisi_lama?->name ?? '-'}}</b> {{$t_mutasi?->m_sub_lama?->m_company?->address ?? '-'}} atau dialihkan ke lain bagian
      sesuai dengan kebutuhan Perusahaan dan/atau demi perkembangan skill dan kompetensi PIHAK KEDUA.
    </td>
  </tr>
  <tr>
    <td>2.2</td>
    <td>
      PIHAK KEDUA wajib hadir kerja sesuai dengan aturan hari dan jam kerja yang ditetapkan
      oleh PIHAK PERTAMA atau wakil PIHAK PERTAMA sesuai dengan bidang dan jenis pekerjaan PIHAK PERTAMA.
    </td>
  </tr>
  <tr>
    <td>2.3</td>
    <td>
      Apabila diperlukan, mengingat banyaknya permintaan/pesanan atau kebutuhan lain kepada Perusahaan,
      PIHAK KEDUA bersedia dan sepakat untuk kerja lembur yang tata cara pengaturannya baik waktu dan upahnya
      sesuai dengan kebijakan PIHAK PERTAMA dan Peraturan Perundang-undangan yang berlaku.
    </td>
  </tr>

  <!-- PASAL 3 -->
  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 3. TUGAS KERJA DAN TANGGUNG JAWAB :</b>
    </td>
  </tr>
  <tr>
    <td>3.1</td>
    <td>
      PARA PIHAK sepakat bahwa tugas dan tanggungjawab PIHAK KEDUA adalah sebagaimana yang
      tercantum dalam Job Description atau yang sudah ditentukan dalam Peraturan Perusahaan
      dan atau dokumen lain yang terkait.
    </td>
  </tr>

  <!-- PASAL 4 -->
  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 4. PEMUTUSAN HUBUNGAN KERJA :</b>
    </td>
  </tr>
  <tr>
    <td>4.1</td>
    <td>
      Perjanjian Kerja ini berakhir demi hukum sesuai dengan berakhirnya waktu yang disepakati PARA PIHAK,
      kecuali diperpanjang atau diakhiri setelah disetujui oleh PARA PIHAK secara tertulis.
    </td>
  </tr>
  <tr>
    <td>4.2</td>
    <td>
      PARA PIHAK SEPAKAT bahwa Perjanjian Kerja berakhir tanpa ada kewajiban pembayaran kompensasi apapun
      dari PIHAK PERTAMA kepada PIHAK KEDUA, jika PIHAK KEDUA tertangkap tangan dan/atau melanggar
      peraturan perusahaan di bidang hukum pidana (misal: pemalsuan, pencurian, penggelapan, penipuan,
      penganiayaan, judi, asusila, dll) atau melakukan kesalahan berat sebagaimana Pasal 158 ayat 1
      Undang-undang Nomor 13 Tahun 2003 tentang Ketenagakerjaan dan/atau sebagaimana pelanggaran-pelanggaran
      berat yang diatur dalam Peraturan Perusahaan.
    </td>
  </tr>

  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 5. UPAH / GAJI dan CARA PEMBAYARAN :</b>
    </td>
  </tr>
  <tr>
    <td>5.1</td>
    <td>
      PARA PIHAK sepakat bahwa PIHAK KEDUA diberikan upah yang dihitung harian berdasarkan jumlah kehadiran kerja
      PIHAK KEDUA yakni sejumlah Rp 219.000 (Dua Ratu Sembilan Belas Ribu Rupiah) untuk setiap hari kehadiran
      PIHAK KEDUA.
    </td>
  </tr>
  <tr>
    <td>5.2</td>
    <td>
      PARA PIHAK sepakat bahwa Pajak atas penghasilan (PPH Pasal 21) PIHAK KEDUA ditanggung oleh PIHAK PERTAMA.
    </td>
  </tr>
  <tr>
    <td>5.3</td>
    <td>
      Upah PIHAK KEDUA akan dibayarkan PIHAK PERTAMA melalui transfer ke rekening PIHAK KEDUA yakni pada awal
      bulan selanjutnya PIHAK KEDUA kepada PIHAK PERTAMA.
    </td>
  </tr>
  <tr>
    <td>5.4</td>
    <td>
      PIHAK KEDUA menerima fasilitas BPJS Ketenagakerjaan dengan manfaat Jaminan Kecelakaan Kerja dan Jaminan
      Kematian yang dibebankan seluruhnya kepada PIHAK PERTAMA.
    </td>
  </tr>

  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 6. 6. LAIN – LAIN :</b>
    </td>
  </tr>
  <tr>
    <td>6.1</td>
    <td>
      Pada saat ditandatanganinya Perjanjian Kerja ini, PIHAK PERTAMA telah memberikan 1 (satu) eksemplar dan
      menyampaikan Peraturan Perusahaan serta PIHAK KEDUA telah membaca, mengerti dan memahami Peraturan Perusahaan
      yang berlaku.
    </td>
  </tr>
  <tr>
    <td>6.2</td>
    <td>
      PARA PIHAK sepakat bahwa hal-hal lain dalam Peraturan Perusahaan tersebut yang tidak tercantum pada Perjanjian
      Kerja
      ini tetap mengikat dan menjadi bagian yang tidak terpisahkan dalam Perjanjian Kerja ini.
    </td>
  </tr>
  <tr>
    <td>6.3</td>
    <td>
      Segala hal yang belum diatur dalam Perjanjian Kerja ini baik atas penambahan-penambahan maupun perubahan-perubahan
      yang disepakati PARA PIHAK akan diatur dalam addendum Perjanjian Kerja dan/atau dokumen lain yang menjadi bagian
      tidak terpisahkan dalam Perjanjian Kerja ini.
    </td>
  </tr>
  <tr>
    <td>6.4</td>
    <td>
      PARA PIHAK menyatakan bahwa data dan keterangan yang disampaikan dalam Perjanjian ini adalah benar adanya dan
      dapat dipertanggungjawabkan, serta akan saling menginformasikan kepada PIHAK lainnya apabila dalam masa berlakunya
      Perjanjian Kerja ini ada perubahan terkait data dan keterangan paling lambat 7 (tujuh) hari sejak adanya perubahan
      data dan
      keterangan.
    </td>
  </tr>

  <tr>
    <td colspan="2" style="padding-top:15px;">
      <b>PASAL 7.PENUTUP:</b>
    </td>
  </tr>
  <tr>
    <td>7.1</td>
    <td>
      PARA PIHAK sepakat bahwa perselisihan yang mungkin timbul selama pelaksanaan perjanjian ini akan diselesaikan
      secara
      musyawarah mufakat serta diselesaikan melalui Pegawai Mediator di Dinas Ketenagakerjaan Kabupaten Gresik dan
      apabila tidak
      tercapai mufakat maka PARA PIHAK memilih menyelesaikan melalui Kepaniteraan Pengadilan Hubungan Industrial pada
      Pengadilan Negeri Kabupaten Gresik
    </td>
  </tr>
  <tr>
    <td>7.2</td>
    <td>
      Demikian perjanjian kerja ini dibuat, disetujui, dan ditandatangani PARA PIHAK tanpa ada paksaan dari pihak
      manapun
      juga serta dibuat rangkap 2 (dua) yang masing-masing PIHAK memiliki 1 (satu) rangkap dengan bunyi yang sama dan
      memiliki
      kekuatan pembuktian yang sama juga.
    </td>
  </tr>
  <tr>
    <td>7.3</td>
    <td>
      Perjanjian Kerja ini berlaku sejak tanggal ditetapkan. Jika di kemudian hari terdapat kesalahan dalam perjanjian
      kerja ini, maka akan diadakan perbaikan seperlunya.
    </td>
  </tr>

  <tr style="font-size: 11px; " cellspacing="0">
    <td style="width:25%">Dibuat dan disepakati di</td>
    <td style="width:3%">:</td>
    <td style="width:15%">Surabaya</td>
  </tr>
  <tr style="font-size: 11px;">
    <td style="width:25%">Tanggal</td>
    <td style="width:3%">:</td>
    <td style="width:15%">{{$tanggal}}</td>
  </tr>
  <tr>
    <td></td>
  </tr>

  <table >
    <tr>
      <td style="width:33%">PEMBERI KERJA<br>{{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}}</td>
      <td style="width:33%"></td>
      <td style="width:33%">PENERIMA KERJA</td>
    </tr>
    <tr>
      <td style="height:20px"></td>
    </tr>
    <tr>
      <td style="height:20px"></td>
    </tr>
    <tr style="font-size: 10px;">
      <td style="width:33%">{{$t_mutasi->signature?->nama_lengkap ?? '-'}}<br>{{$t_mutasi->signature?->m_posisi?->name ?? '-'}}</td>
      <td style="width:33%"></td>
      <td style="width:33%">{{$t_mutasi?->m_kary?->nama_lengkap ?? '-'}}</td>
    </tr>
  </table>

</table>