@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;
use App\Models\CustomModels\m_kary_det_jabatan;
Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
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
$kompensasiRaw = $t_mutasi?->kompensasi ?? 0;
$kompensasi = number_format($kompensasiRaw, 0, ',', '.');

@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:10.8px;line-height:1.5;">

  <table>
    <tr>
      <td></td>
    </tr>
  </table>

  <!-- JUDUL -->
  <table style="width:100%;margin-top:20px;">
    <tr>
      <td style="width:30%;"></td>
      <td
        style="width:40%;text-align:center;font-weight:bold;font-size:15px;border-bottom:2px solid black;height:28px;">
        PERJANJIAN BERSAMA
      </td>
      <td style="width:30%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:4px;">
        No. {{$t_mutasi?->nomor}}
      </td>
      <td></td>
    </tr>
  </table>

  <table>
    <tr>
      <td></td>
    </tr>
  </table>

  <table>
    <tr>
      <td>Perjanjian Bersama Pemutusan Hubungan Kerja ("PHK") dan Penerimaan Kompensasi atas PHK (selanjutnya disebut
        dengan "PB") dibuat dan disepakati oleh kami yang bertanda tangan dibawah ini:</td>
    </tr>
  </table>

  <table style="width:100%;margin-top:20px;">
    <tr>
      <td rowspan="2" style="width: 5%;">1.</td>
      <td style="width: 25%;">Nama</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->signature?->nama_lengkap ?? ''}}</td>
    </tr>
    <tr>
      <td style="width: 25%;">Jabatan</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->signature?->m_posisi?->name ?? ''}}</td>
    </tr>
  </table>

  <table>
    <tr>
      <td>Dalam jabatannya yang sah bertindak untuk dan atas nama PT. {{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}} yang beralamat dan
        berkedudukan hukum di {{$t_mutasi->m_sub_lama?->m_company?->alamat ?? '-'}} ; untuk selanjutnya dalam PB ini sebagai dan disebut dengan
        <strong>PEMBERI KERJA</strong>
      </td>
    </tr>
  </table>

  <table style="width:100%;margin-top:20px;">
    <tr>
      <td rowspan="4" style="width: 5%;">2.</td>
      <td style="width: 25%;">Nama</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
    </tr>
    <tr>
      <td style="width: 25%;">NIK</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->m_kary?->nik ?? '-'}}</td>
    </tr>
    <tr>
      <td style="width: 25%;">Alamat</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->m_kary?->alamat_domisili ?? '-'}}</td>
    </tr>
    <tr>
      <td style="width: 25%;">Jabatan</td>
      <td style="width: 3%;">:</td>
      <td>{{$t_mutasi->m_posisi_lama?->name}}</td>
    </tr>
  </table>

  <table>
    <tr>
      <td>Bertindak selaku pribadi, untuk dan atas nama diri sendiri yang dalam PB ini sebagai dan disebut dengan
        <strong>PENERIMA KERJA</strong>
      </td>
    </tr>
    <tr>
      <td><strong>PEMBERI KERJA</strong> dan <strong>PENERIMA KERJA</strong> bertindak secara bersama-sama disebut
        dengan <strong>PARA PIHAK</strong>. <strong>PARA
        PIHAK</strong> menerangkan bahwa telah memiliki hubungan kerja sebagaimana disepakati dalam Perjanjian Kerja
        Nomor 006/
        Sl.211221/TMG/SBY/HRD/TTP tanggal 21/12/2021 dan pada waktu serta tempat yang tercantum di bagian bawah PB ini
        telah menyepakati bahwa:</td>
    </tr>
  </table>

  <table style="width:100%;margin-top:20px; font-size:10px;">
    <tr>
      <td style="width: 5%;">1.</td>
      <td style="width:95%;">PARA PIHAK sepakat untuk mengakhiri hubungan kerja efektif sejak tanggal {{$tanggal}}.
        ("Tanggal Efektif")</td>
    </tr>
    <tr>
      <td style="width: 5%;">2.</td>
      <td>Sehubungan dengan PB ini dan menurut ketentuan hukum yang berlaku, maka PEMBERI KERJA akan memberikan
        uang kompensasi dan PENERIMA KERJA telah mengetahui dan sepakat atas jumlah uang kompensasi tersebut yakni
        sejumlah Rp.{{$kompensasi}},- ("Uang Kompensasi")</td>
    </tr>
    <tr>
      <td style="width: 5%;">3.</td>
      <td>PEMBERI KERJA menjamin bahwa akan tetap bekerja dan menyelesaikan tugas serta tanggungjawabnya hingga
        Tanggal Efektif termasuk namun tidak terbatas pada membuat laporan atau serah terima pekerjaan dan/atau membantu
        melakukan serah terima pekerjaan dengan karyawan Pengganti.</td>
    </tr>
    <tr>
      <td style="width: 5%;">4.</td>
      <td>PARA PIHAK sepakat bahwa Uang Kompensasi tersebut akan diberikan setelah PENERIMA KERJA menyelesaikan
        tugas dan tanggungjawabnya sebagaimana disepakati dalam poin 3 (tiga) diatas yang dibuktikan dengan laporan
        pekerjaan dan/atau laporan serah terima pekerjaan yang disetujui atasan langsung PENERIMA KERJA.</td>
    </tr>
    <tr>
      <td style="width: 5%;">5.</td>
      <td>PARA PIHAK sepakat untuk mengesampingkan ketentuan untuk mendaftarkan PB ini pada Pengadilan Hubungan
        Industrial maupun instansi terkait lainnya dan saling menyatakan bahwa PB ini sah dan berlaku cukup dengan
        ditandatanganinya PARA PIHAK.</td>
    </tr>
    <tr>
      <td style="width: 5%;"></td>
      <td></td>
    </tr>
  </table>

  <table>
    <tr>
      <td>Setelah PB ini disepakati dan dilaksanakan, maka PARA PIHAK baik saat ini maupun dikemudian hari sepakat untuk
        tidak
        akan saling melakukan tuntutan apapun juga sepanjang atas pelaksanaan ketentuan-ketentuan yang disepakati dalam
        PB ini.</td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
      <td>Demikainlah PB ini dibuat dan disepakati PARA PIHAK di PT {{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}} Jl. {{$t_mutasi->m_sub_lama?->m_company?->address ?? '-'}}
        {{$t_mutasi->m_sub_lama?->m_company?->city?->value ?? '-'}} pada tanggal {{$tanggal}} .</td>
    </tr>
    <tr>
      <td></td>
    </tr>
  </table>

  <table>
    <tr>
      <td style="width:33%">PEMBERI KERJA<br>PT {{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}}</td>
      <td style="width:33%"></td>
      <td style="width:33%">PENERIMA KERJA</td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr>
      <td></td>
    </tr>
    <tr style="font-size: 10px;">
      <td style="width:33%">{{$t_mutasi->signature?->nama_lengkap ?? '-'}}<br>{{$t_mutasi->signature?->m_posisi?->name}}</td>
      <td style="width:33%"></td>
      <td style="width:33%">{{$t_mutasi?->m_kary?->nama_lengkap ?? '-'}}</td>
    </tr>
  </table>

</div>