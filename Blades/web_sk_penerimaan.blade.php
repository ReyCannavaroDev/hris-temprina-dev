@php
use Carbon\Carbon;
use App\Models\CustomModels\t_mutasi;

Carbon::setLocale('id');
$req = app()->request;
$id = $req->id;

$t_mutasi = $id ? t_mutasi::find($id) : null;

$nomor = $t_mutasi?->nomor ?? $req->nomor ?? '001/TMG/HLD/VII/2022';
$tgl = $t_mutasi?->tgl ?? now();
$carbonDate = Carbon::parse($tgl);
$tanggalSurat = $carbonDate->translatedFormat('d F Y');

$city = $t_mutasi?->m_branch_baru?->kota 
      ?? $t_mutasi?->m_branch_lama?->kota 
      ?? $t_mutasi?->m_kary?->m_branch?->kota 
      ?? 'Gresik';

$signature = $t_mutasi?->signature;
$namaTtd = $signature?->nama_lengkap ?? 'Dissy Ardjani';
$jabatanTtd = $signature?->m_posisi?->name ?? 'Human Capital';

$perihal = $t_mutasi?->deskripsi ?? 'Konfirmasi Penerimaan siswa Praktek Kerja Lapangan';
$tujuanNama = $req->tujuan_nama ?? 'Dr. Ir. Novarina Hendrasarie, MT.';
$tujuanJabatan = $req->tujuan_jabatan ?? 'Dekan Fakultas Ilmu Komputer';
$tujuanInstansi = $req->tujuan_instansi ?? 'UPN Jawa Timur';

$suratBalasanNo = $req->surat_balasan_no ?? '876/UN63.7/PJ/2024';
$suratBalasanTgl = $req->surat_balasan_tgl ?? '01 Juli 2024';

$periodeMulai = $t_mutasi?->date_from ? Carbon::parse($t_mutasi->date_from)->translatedFormat('d F Y') : '08 Juli 2024';
$periodeSelesai = $t_mutasi?->date_to ? Carbon::parse($t_mutasi->date_to)->translatedFormat('d F Y') : '31 Agustus 2024';
$pembimbing = $t_mutasi?->keterangan ?? 'Bpk Turikan';
@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:11px;line-height:1.6;color:black;">

  @include('projects.web_sk_kop')

  <div style="margin-top:20px; margin-bottom:5px;">
    Nomor : {{$nomor}}
  </div>

  <div style="margin-bottom:20px;">
    Perihal : <b>{{$perihal}}</b>
  </div>

  <div style="margin-bottom:20px;">
    Kepada Yth.<br>
    <b>{{$tujuanNama}}</b><br>
    <b>{{$tujuanJabatan}}</b><br>
    <b>{{$tujuanInstansi}}</b><br>
    Di Tempat
  </div>

  <div style="margin-bottom:15px;">
    Dengan hormat,
  </div>

  <div style="margin-bottom:15px; text-align:justify;">
    Menanggapi surat Nomor : <b>{{$suratBalasanNo}}</b> tanggal {{$suratBalasanTgl}} perihal
    Praktek Kerja Lapangan mahasiswa :
  </div>

  <div style="margin-left:30px; margin-bottom:15px;">
    @if($t_mutasi?->m_kary)
      1. &nbsp;{{$t_mutasi->m_kary->nama_lengkap}} (NIP/NIM: {{$t_mutasi->m_kary->kode}})<br>
    @else
      1. &nbsp;Nadia Dita Salsabila NPM. 21081010181<br>
      2. &nbsp;Yuani Pranajelita NPM. 21081010204
    @endif
  </div>

  <div style="margin-bottom:15px; text-align:justify;">
    Dengan ini disampaikan bahwa permohonan Praktek Kerja Lapangan kami
    <b>terima</b> dengan waktu pelaksanaan Praktek Kerja Lapangan terhitung mulai
    tanggal <b>{{$periodeMulai}}</b> s.d. <b>{{$periodeSelesai}}</b> dengan Pembimbing dari perusahaan
    atas nama <b>{{$pembimbing}}</b>.
  </div>

  <div style="margin-bottom:30px; text-align:justify;">
    Demikian surat persetujuan ini kami buat untuk digunakan sebagaimana mestinya.
    Terimakasih.
  </div>

  <!-- TANDA TANGAN -->
  <table cellspacing="0" cellpadding="0" style="width:100%; margin-top:20px; font-size:11px; line-height:1.3;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        {{$city}}, {{$tanggalSurat}}<br>
        Hormat kami,<br>
        <div style="height:55px;"></div>
        <b><u>{{$namaTtd}}</u></b><br>
        {{$jabatanTtd}}
      </td>
    </tr>
  </table>

  @include('projects.web_sk_footer')

</div>