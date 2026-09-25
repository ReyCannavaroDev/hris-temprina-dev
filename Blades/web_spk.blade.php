@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;
    use App\Models\CustomModels\t_perdin;
    Carbon::setLocale('id');
    $req = app()->request;

    $id = $req->id;
    $t_perdin = t_perdin::find($id);

    $carbonDate = Carbon::parse($t_perdin->tgl);
    $tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

    $terbitDate = Carbon::parse($t_perdin?->updated_at ?? $t_perdin?->created_at ?? now());
    $tanggalTerbit = $terbitDate->translatedFormat('d F Y');

    $datefrom = Carbon::parse($t_perdin->date_from);
    $datefrom = $datefrom->translatedFormat('d F Y');

    $dateto = Carbon::parse($t_perdin->date_to);
    $dateto = $dateto->translatedFormat('d F Y');

@endphp

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table style="width:100%;">
    <tr>
        <td style="width: 25%"></td>
        <td style="width: 50%;text-align: center; border-bottom: 1px solid black; font-weight:bold; font-size:16px;">
            SURAT
            PENUGASAN KARYAWAN
        </td>
        <td style="width: 25%"></td>
    </tr>
    <tr>
        <td></td>
        <td style="font-size:13px; text-align: center;">No. {{$t_perdin->nomor}}</td>
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
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size: 10px;">
    <tr>
        <td colspan="3" style="font-weight:bold;">A. Karyawan yang diberi tugas :</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight:bold;"></td>
    </tr>
    <tr>
        <td style="width:18%;">Nama</td>
        <td style="width:2%;">:</td>
        <td style="width: 80%">{{$t_perdin?->m_kary?->nama_lengkap ?? '-'}}</td>
    </tr>
    <tr>
        <td>Jabatan/Bagian</td>
        <td>:</td>
        <td>{{$t_perdin?->m_kary?->m_posisi?->name ?? '-'}}</td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size: 10px;">
    <tr>
        <td colspan="3" style="font-weight:bold;">B. Tempat yang dituju :</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight:bold;"></td>
    </tr>
    <tr>
        <td style="width:18%;">Perusahaan</td>
        <td style="width:2%;">:</td>
        <td style="width: 80%">{{$t_perdin?->tempat_tujuan ?? '-'}}<br>{{$t_perdin?->alamat_tujuan ?? '-'}}
        </td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size: 10px;">
    <tr>
        <td colspan="3" style="font-weight:bold;">C. Lama dan Sasaran Tugas :</td>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td style="width:18%;">Lama Tugas</td>
        <td style="width:2%;">:</td>
        <td style="width:80%;">{{$datefrom ?? '-'}} s/d {{$dateto ?? '-'}}</td>
    </tr>
    <tr>
        <td>Sasaran Tugas</td>
        <td>:</td>
        <td>{{$t_perdin?->tugas ?? '-'}}</td>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top:5px;">
            Apabila masa tugas berakhir sementara tugas belum selesai, karyawan diharapkan melakukan konfirmasi dengan
            pemberi
            tugas.
        </td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>


<table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size: 10px;">
    <tr>
        <td colspan="3" style="font-weight:bold;">D. Kewajiban Karyawan :</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight:bold;"></td>
    </tr>
    <tr>
        <td colspan="3">1. Dimohon segera kembali ke tempat asal jika sudah selesai tugas.</td>
    </tr>
    <tr>
        <td colspan="3">2. Membuat Laporan Tertulis, diketahui pihak yang bersangkutan.</td>
    </tr>
</table>


<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px; font-size: 10px;">
    <tr>
        <td colspan="3" style="font-weight:bold;">E. Fasilitas Karyawan :</td>
    </tr>
    <tr>
        <td style="width:18%; ">Uang Makan & Saku</td>
        <td style="width:2%;">:</td>
        <td style="width:80%;">Rp. _____________________ / hari</td>
    </tr>
    <tr>
        <td>Lain - lain</td>
        <td>:</td>
        <td>__________________________</td>
    </tr>
</table>

<table>
    <tr>
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
        <td></td>
    </tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-top:20px;">
    <tr>
        <td style="text-align:left; width:40%;">
            {{ trim(str_ireplace(['Kabupaten ', 'Kab. ', 'Kota '], '', $t_perdin->m_kary?->m_subcomp?->city?->value ?? '-')) }},
            {{$tanggalTerbit}}<br>
            Pemberi Tugas,<br><br><br><br>
            <b><u>{{$t_perdin?->m_atasan?->nama_lengkap ?? '-'}}</u></b><br>
            <span style="font-style:italic;">{{$t_perdin?->m_atasan?->m_posisi?->name ?? '-'}}</span>
        </td>
        <td style="width:60%;"></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>


<table style="width:100%; border-collapse:collapse; margin-top:20px; font-size: 10px;">
    <tr>
        <td style="width:100px; vertical-align:top;">Tembusan :</td>
        <td>
            1. Direksi
        </td>
    </tr>
    <tr>
        <td style="width:100px; vertical-align:top;"></td>
        <td>
            2. Bag. Keuangan
        </td>
    </tr>
</table>