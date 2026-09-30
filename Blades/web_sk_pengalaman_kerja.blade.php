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

@endphp

  <div style="font-family:'Times New Roman',serif;margin:auto;font-size:12px;line-height:1.2;">
    
    <div style="text-align:center; margin-bottom:40px;">
      <h3 style="text-decoration:underline; margin:0; font-size:18px;">SURAT PENGALAMAN KERJA</h3>
      <p style="margin:0;">No. {{$t_mutasi?->nomor}}</p>
    </div>

    <p style="margin-bottom:10px;">Yang bertanda tangan di bawah ini :</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; margin-left:20px;">
      <tr>
        <td style="width:25px; vertical-align:top;">-</td>
        <td style="width:100px; vertical-align:top;">Nama</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->signature?->nama_lengkap}}</td>
      </tr>
      <tr>
        <td style="vertical-align:top;">-</td>
        <td style="vertical-align:top;">Bagian</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->signature?->m_posisi->name}}<br>&nbsp;&nbsp;{{$t_mutasi->signature?->m_subcomp?->name}}<br>&nbsp;&nbsp;{{$t_mutasi->signature?->m_subcomp?->address}}</td>
      </tr>
    </table>

    <p style="margin-bottom:10px;">Menerangkan bahwa :</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:25px; margin-left:20px;">
      <tr>
        <td style="width:25px; vertical-align:top;">-</td>
        <td style="width:100px; vertical-align:top;">Nama</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</td>
      </tr>
      <tr>
        <td style="vertical-align:top;">-</td>
        <td style="width:100px; vertical-align:top;">Status</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->status_kary_lama?->value ?? '-'}} {{ ' ' . $t_mutasi->m_sub_lama?->name ?? '-'}}</td>
      </tr>
      <tr>
        <td style="vertical-align:top;">-</td>
        <td style="vertical-align:top;">Bagian</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->m_posisi_lama?->name}} {{ ' ' . $t_mutasi->m_sub_lama?->name}}<br>&nbsp;&nbsp;{{ ' ' . $t_mutasi->m_sub_lama?->address}}
        </td>
      </tr>
      <tr>
        <td style="vertical-align:top;">-</td>
        <td style="vertical-align:top;">Awal Kerja</td>
        <td style="vertical-align:top; width:100%">: {{$awalKerja}} s/d {{$akhirKerja}}</td>
      </tr>
      <tr>
        <td style="vertical-align:top;">-</td>
        <td style="vertical-align:top;">Keterangan</td>
        <td style="vertical-align:top; width:100%">: {{$t_mutasi->Keterangan ?? '-'}}</td>
      </tr>
    </table>

    <p style="text-align:justify; margin-bottom:20px;">
      Selama bekerja yang bersangkutan telah menunjukkan dedikasi serta kinerja yang baik untuk perusahaan. Untuk itu
      atas nama manajemen PT Temprina Media Grafika menyampaikan terima kasih atas kerjasamanya selama ini.
    </p>

    <p style="text-align:justify; margin-bottom:40px;">
      Demikian surat pengalaman kerja ini kami buat agar dapat dipergunakan sebagaimana mestinya.
    </p>

   <!-- <div>
      <div >
        <p>Surabaya, 31 Desember 2025</p>
        <p style="font-weight: bold;">PT TEMPRINA MEDIA GRAFIKA</p>
      </div>
      
      <div>
        <p style="font-weight: bold; text-decoration:underline">NAMA YANG TTD</p>
        <p style="font-weight: bold;">JABATAN TTD</p>
      </div>
    </div> -->
    <table style="width:100%;margin-top:26px; font-size: 11px;">
    <tr>
      <td style="width:60%;"></td>
      <td style="width:40%;">
        Dikeluarkan di&nbsp;&nbsp;: Surabaya<br>
        Pada Tanggal&nbsp;&nbsp;: {{$tanggalTerbit}}
        <div style="margin-top:18px;font-weight:bold;">
          {{$t_mutasi->m_sub_lama?->m_company?->name ?? '-'}}
        </div>
        <div></div>
        <div style="margin-top:50px;font-weight:bold;text-decoration:underline;">
          {{$t_mutasi->signature?->nama_lengkap ?? ''}}
        </div>
        <div>
          {{$t_mutasi->signature?->m_posisi?->name ?? ''}}
        </div>
      </td>
    </tr>
  </table>

  </div>