@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\m_kary_det_jabatan;
use App\Models\CustomModels\m_standart_gaji;

Carbon::setLocale('id');
$req = app()->request;

$id = $req->id;
$t_mutasi = null;
if ($id) {
    try {
        $t_mutasi = t_mutasi::find($id);
    } catch (\Throwable $e) {}
}

$carbonDate = Carbon::parse($t_mutasi?->tgl ?? now());
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');
$tanggalNoDay = $carbonDate->translatedFormat('d F Y');
$tanggal = $carbonDate->format('d/m/Y');

// Nomor Dokumen
$nomorRaw = !empty($t_mutasi?->no_dokumen) ? $t_mutasi->no_dokumen : ($t_mutasi?->nomor ?? '001/Sl.311224/TMG/HLD/HRD/K');
$cleanNomor = preg_replace('/^no\.?\s*/i', '', trim($nomorRaw));
$nomor = !empty($cleanNomor) ? $cleanNomor : '001/Sl.311224/TMG/HLD/HRD/K';

// Karyawan (Pihak Kedua)
$karyawan = $t_mutasi?->m_kary;
if (!$karyawan && !empty($t_mutasi?->m_kary_id)) {
    try {
        $karyawan = \App\Models\CustomModels\m_kary::find($t_mutasi->m_kary_id) 
                 ?? \App\Models\BasicModels\m_kary::find($t_mutasi->m_kary_id);
    } catch (\Throwable $e) {}
}

// Perusahaan & Format Dokumen: PKWT Kop Surat & Footer Sesuai Template PDF
$isPkwt = true;
$isJpBooks = false;
$compRaw = $t_mutasi?->m_sub_lama?->m_company?->name ?? $karyawan?->m_company?->name ?? 'PT Temprina Media Grafika';
if (!str_starts_with(strtoupper(trim($compRaw)), 'PT')) {
    $compRaw = 'PT ' . $compRaw;
}
$companyName = $compRaw;

$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $karyawan?->m_branch?->kota 
            ?? $t_mutasi?->m_branch_baru?->name 
            ?? $t_mutasi?->m_branch_lama?->name 
            ?? $karyawan?->m_branch?->name 
            ?? 'Surabaya';
$kotaTerbit = preg_replace('/^(cabang|branch)\s+/i', '', trim($kotaTerbit));
$kotaTerbit = ucfirst(strtolower($kotaTerbit));

// Data Pihak Pertama (Pemberi Kerja)
$signature = $t_mutasi?->signature;
$sigId = $t_mutasi?->signature_id;
if (!$signature && !empty($sigId)) {
    try {
        $signature = \App\Models\CustomModels\m_kary::find($sigId) 
                  ?? \App\Models\BasicModels\m_kary::find($sigId);
    } catch (\Throwable $e) {}
}

$namaPihak1 = !empty($signature?->nama_lengkap) ? strtoupper($signature->nama_lengkap) : 'NAMA DIREKTUR';
$jabatanPihak1 = $signature?->m_posisi?->name ?? 'Direktur Utama';
$alamatPerusahaan = $signature?->m_subcomp?->m_company?->address 
                 ?? $karyawan?->m_branch?->alamat 
                 ?? 'Jl. Karah Agung 45 Surabaya';
$jenisUsaha = 'Trading & Percetakan';

// Data Pihak Kedua (Pekerja)
$namaPihak2 = strtoupper($karyawan?->nama_lengkap ?? 'NAMA KARYAWAN');
$nikPihak2 = $karyawan?->nik ?? $karyawan?->kode ?? '3525110101000003';
$jkRaw = strtoupper($karyawan?->jk?->value ?? ($karyawan?->jenis_kelamin ?? 'LAKI - LAKI'));
$jkPihak2 = str_contains($jkRaw, 'P') && !str_contains($jkRaw, 'LAKI') ? 'PEREMPUAN' : 'LAKI - LAKI';

$tglLahir = $karyawan?->tgl_lahir;
$umur = $tglLahir ? Carbon::parse($tglLahir)->diffInYears($carbonDate) . ' TAHUN' : '25 TAHUN';
$jkUsia = "{$jkPihak2} / {$umur}";

$alamatPihak2 = strtoupper($karyawan?->address ?? ($karyawan?->alamat ?? 'CERMELOR RT/RW 001/011 CERME KAB. GRESIK'));

// Pendidikan Terakhir
$lastEdu = null;
if ($karyawan && method_exists($karyawan, 'm_kary_det_pend')) {
    try {
        $lastEdu = $karyawan->m_kary_det_pend()->where('is_pend_terakhir', true)->first();
    } catch (\Throwable $e) {}
}
if (!$lastEdu && !empty($karyawan?->id)) {
    try {
        $lastEdu = \DB::table('m_kary_det_pend')->where('m_karyawan_id', $karyawan->id)->where('is_pend_terakhir', true)->first();
    } catch (\Throwable $e) {}
}
$pendidikanTerakhir = $lastEdu 
    ? trim(($lastEdu->jenjang ? $lastEdu->jenjang . ' – ' : '') . ($lastEdu->jurusan ?? $lastEdu->nama_sekolah ?? '')) 
    : 'S1 – Akuntansi';
if (empty($pendidikanTerakhir)) $pendidikanTerakhir = 'S1 – Akuntansi';

// Penempatan & Posisi
$posisiBaru = $t_mutasi?->m_posisi_baru?->name 
           ?? $t_mutasi?->m_posisi_lama?->name 
           ?? $karyawan?->m_posisi?->name 
           ?? 'Staff Accounting';
$alamatPenempatan = $t_mutasi?->m_branch_baru?->alamat 
                 ?? $t_mutasi?->m_branch_lama?->alamat 
                 ?? $karyawan?->m_branch?->alamat 
                 ?? 'Jl. Karah Agung 45 Surabaya';

// Durasi & Masa Berlaku Kontrak
$kontrak = null;
if ($karyawan && method_exists($karyawan, 'm_kary_det_jabatan')) {
    try {
        $kontrak = $karyawan->m_kary_det_jabatan()->where('is_active', true)->where('is_primary', true)->first();
    } catch (\Throwable $e) {}
}
if (!$kontrak && !empty($karyawan?->id)) {
    try {
        $kontrak = \DB::table('m_kary_det_jabatan')->where('m_karyawan_id', $karyawan->id)->orderBy('id', 'desc')->first();
    } catch (\Throwable $e) {}
}

$startDate = null;
$endDate = null;
if (!empty($kontrak?->start_time) && !empty($kontrak?->end_time)) {
    $startDate = Carbon::parse($kontrak->start_time);
    $endDate = Carbon::parse($kontrak->end_time);
} elseif (!empty($kontrak?->tgl_mulai) && !empty($kontrak?->tgl_selesai)) {
    $startDate = Carbon::parse($kontrak->tgl_mulai);
    $endDate = Carbon::parse($kontrak->tgl_selesai);
} else {
    $startDate = $carbonDate->copy();
    $endDate = $carbonDate->copy()->addMonths(6)->subDay();
}
$lamaKontrak = $startDate->diffInMonths($endDate->copy()->addDay());
if ($lamaKontrak <= 0) $lamaKontrak = 6;
$durasiKontrak = $startDate->translatedFormat('d F Y') . " sampai dengan " . $endDate->translatedFormat('d F Y');

// Upah Harian & Terbilang
$upahHarian = 0;
if (!empty($t_mutasi?->kompensasi) && (float)$t_mutasi->kompensasi > 0) {
    $upahHarian = (float)$t_mutasi->kompensasi;
} else {
    $salary = null;
    try {
        $salary = m_standart_gaji::where('m_kary_id', $t_mutasi?->m_kary_id)->first();
    } catch (\Throwable $e) {}
    if ($salary && (float)$salary->gaji_pokok > 0) {
        $upahHarian = (float)$salary->gaji_pokok;
    } else {
        $upahHarian = 219000;
    }
}
$upahHarianFormatted = number_format($upahHarian, 0, ',', '.');

if (!function_exists('penyebut_pkwt')) {
    function penyebut_pkwt($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = penyebut_pkwt($nilai - 10). " Belas";
        } else if ($nilai < 100) {
            $temp = penyebut_pkwt((int)($nilai / 10)). " Puluh". penyebut_pkwt($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . penyebut_pkwt($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebut_pkwt((int)($nilai / 100)) . " Ratus" . penyebut_pkwt($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . penyebut_pkwt($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebut_pkwt((int)($nilai / 1000)) . " Ribu" . penyebut_pkwt($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = penyebut_pkwt((int)($nilai / 1000000)) . " Juta" . penyebut_pkwt($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = penyebut_pkwt((int)($nilai / 1000000000)) . " Milyar" . penyebut_pkwt(fmod($nilai, 1000000000));
        }
        return $temp;
    }
}

if (!function_exists('terbilang_pkwt')) {
    function terbilang_pkwt($nilai) {
        if ($nilai < 0) {
            $hasil = "Minus ". trim(penyebut_pkwt($nilai));
        } else {
            $hasil = trim(penyebut_pkwt($nilai));
        }     		
        return !empty($hasil) ? $hasil . " Rupiah" : "Nol Rupiah";
    }
}
$upahHarianTerbilang = terbilang_pkwt($upahHarian);

$b64HeaderPkwt = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCACwBLADASIAAhEBAxEB/8QAHAABAAIDAQEBAAAAAAAAAAAAAAYHAwQFCAIB/8QAWBAAAQMDAgQDBAIMCAsGBQUAAQACAwQFEQYSBxMhMRRBUSIyYXEVgQgWIzU2QnN0kaGxshczUlRyk7PBNDc4VmJ1gpSi0dIYJkOSwsMlRFNj4SQnRWW0/8QAGgEBAAMBAQEAAAAAAAAAAAAAAAECAwUEBv/EAC8RAQACAgEDAgQGAgIDAAAAAAABAgMRBBIhMQUTQVFxoRQiMmHR8IGxBjTB4fH/2gAMAwEAAhEDEQA/AL/REQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBERAREQEREBcXVv4K3H8kf2hdpcXVv4K3H8if2hBvWn7zUP5vH+6FuLTtP3mofzeP90LcQFyajVGn6SofT1N8tkM0Z2vjkq42uafQgnIXWXmOj0zbdWfZA3213WJ8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';
$b64FooterPkwt = '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';
if (function_exists('base_path')) {
    $hTxt = base_path('media/b64_header.txt');
    if (file_exists($hTxt) && filesize($hTxt) > 50) {
        $b64HeaderPkwt = trim(file_get_contents($hTxt));
    }
    $fTxt = base_path('media/b64_footer.txt');
    if (file_exists($fTxt) && filesize($fTxt) > 50) {
        $b64FooterPkwt = trim(file_get_contents($fTxt));
    }
}

// Dynamic m_media lookup for PKWT Kop and Footer (mengutamakan upload terbaru dari m_media)
$kopMediaPkwt = null;
$footerMediaPkwt = null;
try {
    $kopMediaPkwt = \DB::table('m_media')
        ->select('m_media.*')
        ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
        ->where(function($q) {
            $q->where('m_media.is_active', true)
              ->orWhereNull('m_media.is_active');
        })
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
                    ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%FOOTER%'")
                    ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%FOOTER%'")
                    ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%BANNER%'")
                    ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%BANNER%'")
                    ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%BAWAH%'")
                    ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%BAWAH%'");
            })
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) IN ('KOP-PKWT', 'HEADER-PKWT', 'KOP_PKWT', 'HEADER_PKWT', 'KOP-PKTW', 'HEADER-PKTW', 'KOP-SURAT', 'KOP_SURAT', 'KOP SURAT', 'KOP', 'HEADER', 'LOGO-PKWT', 'LOGO-TMG', 'LOGO-TEMPRINA', 'LOGO_UTAMA')")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%KOP%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%HEADER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%ATAS%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%KOP%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%ATAS%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%KOP%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%HEADER%'");
        })
        ->orderByRaw("CASE 
            WHEN UPPER(COALESCE(m_media.kode,'')) IN ('KOP-PKWT', 'KOP_PKWT', 'KOP-PKTW', 'HEADER-PKWT') THEN 0
            WHEN (UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.kode,'')) LIKE '%KOP%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%') THEN 1
            WHEN (UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.nama,'')) LIKE '%KOP%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%') THEN 2
            WHEN (UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%') AND UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%FOOTER%' AND UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%BANNER%' THEN 3
            WHEN (UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%') AND UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%FOOTER%' AND UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%BANNER%' THEN 4
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%KOP%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%KOP%' THEN 5
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%HEADER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%' THEN 6
            ELSE 10
        END")
        ->orderBy('m_media.id', 'desc')
        ->first();

    // Fallback Kop: cari media terbaru bertema Kop / Header
    if (!$kopMediaPkwt) {
        $kopMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) {
                $q->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%KOP%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%KOP%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%HEADER%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%HEADER%')")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%FOOTER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%FOOTER%'");
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    // Fallback Kop 2: cari media terbaru yang bukan footer/banner
    if (!$kopMediaPkwt) {
        $kopMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) {
                $q->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%FOOTER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%FOOTER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%BANNER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%BANNER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%BAWAH%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%BAWAH%'");
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    $kopExcludeId = $kopMediaPkwt ? $kopMediaPkwt->id : 0;

    $footerMediaPkwt = \DB::table('m_media')
        ->select('m_media.*')
        ->leftJoin('m_general', 'm_general.id', '=', 'm_media.kategori_id')
        ->where(function($q) {
            $q->where('m_media.is_active', true)
              ->orWhereNull('m_media.is_active');
        })
        ->where(function($q) use ($kopExcludeId) {
            if ($kopExcludeId > 0) {
                $q->where('m_media.id', '!=', $kopExcludeId);
            }
        })
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
                    ->where(function($sub2) {
                        $sub2->whereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
                             ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'");
                    });
            })
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) IN ('FOOTER-PKWT', 'BANNER-PKWT', 'FOOTER_PKWT', 'BANNER_PKWT', 'FOOTER-PKTW', 'BANNER-PKTW', 'LOGO-PKWT-FOOTER', 'LOGO-FOOTER-PKWT', 'FOOTER', 'BANNER', 'FOOTER-SURAT', 'FOOTER-CABANG', 'FOOTER-TEMPRINA')")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
            ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%FOOTER%'")
            ->orWhereRaw("UPPER(COALESCE(m_general.value,'')) LIKE '%BANNER%'");
        })
        ->orderByRaw("CASE 
            WHEN UPPER(COALESCE(m_media.kode,'')) IN ('FOOTER-PKWT', 'FOOTER_PKWT', 'FOOTER-PKTW', 'BANNER-PKWT') THEN 0
            WHEN (UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%') THEN 1
            WHEN (UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%') AND (UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%') THEN 2
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%' THEN 3
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%' THEN 4
            WHEN UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%' THEN 5
            ELSE 10
        END")
        ->orderBy('m_media.id', 'desc')
        ->first();

    // Fallback: jika belum ketemu tapi ada media terbaru lain bertema PKWT (bukan Kop yang sudah dipilih)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopExcludeId) {
                if ($kopExcludeId > 0) {
                    $q->where('m_media.id', '!=', $kopExcludeId);
                }
                $q->whereRaw("(UPPER(COALESCE(m_media.kode,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKWT%' OR UPPER(COALESCE(m_media.kode,'')) LIKE '%PKTW%' OR UPPER(COALESCE(m_media.nama,'')) LIKE '%PKTW%')")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%HEADER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%HEADER%'")
                  ->whereRaw("UPPER(COALESCE(m_media.kode,'')) NOT LIKE '%KOP%'")
                  ->whereRaw("UPPER(COALESCE(m_media.nama,'')) NOT LIKE '%KOP%'");
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    // Fallback 2: cari media footer umum (bukan Kop)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopExcludeId) {
                if ($kopExcludeId > 0) {
                    $q->where('m_media.id', '!=', $kopExcludeId);
                }
                $q->where(function($sub) {
                    $sub->whereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%FOOTER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%FOOTER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BANNER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BANNER%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.kode,'')) LIKE '%BAWAH%'")
                        ->orWhereRaw("UPPER(COALESCE(m_media.nama,'')) LIKE '%BAWAH%'");
                });
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }

    // Fallback ultimate: jika masih belum ketemu, ambil media terbaru lain (selain Kop yang sudah terpilih)
    if (!$footerMediaPkwt) {
        $footerMediaPkwt = \DB::table('m_media')
            ->where(function($q) {
                $q->where('m_media.is_active', true)->orWhereNull('m_media.is_active');
            })
            ->where(function($q) use ($kopExcludeId) {
                if ($kopExcludeId > 0) {
                    $q->where('m_media.id', '!=', $kopExcludeId);
                }
            })
            ->orderBy('m_media.id', 'desc')
            ->first();
    }
} catch (\Throwable $e) {}

$resolvePkwtMedia = function($mediaObj, $defaultB64, $defaultType = 'jpg') {
    if ($mediaObj && !empty($mediaObj->file_path)) {
        $rawPath = trim($mediaObj->file_path);

        if (str_starts_with($rawPath, 'data:image')) {
            return $rawPath;
        }

        $clean = preg_replace('#^https?://[^/]+/#i', '', ltrim($rawPath, '/'));
        $clean = ltrim($clean, '/');
        $basename = basename($clean);

        $pathCandidates = array_unique(array_filter([
            $clean,
            $basename,
            str_contains($clean, ':::') ? explode(':::', $clean)[0] : null,
            str_contains($basename, ':::') ? explode(':::', $basename)[0] : null,
            str_contains($clean, ':::') ? (explode(':::', $clean)[1] ?? null) : null,
            str_contains($basename, ':::') ? (explode(':::', $basename)[1] ?? null) : null,
        ]));

        $subPaths = [];
        foreach ($pathCandidates as $cand) {
            $cand = trim($cand);
            if (empty($cand)) continue;
            $candBase = basename($cand);
            $subPaths[] = $cand;
            $subPaths[] = $candBase;
            $subPaths[] = 'uploads/m_media/' . $candBase;
            $subPaths[] = 'uploads/' . $candBase;
            $subPaths[] = 'storage/uploads/m_media/' . $candBase;
            $subPaths[] = 'storage/' . $candBase;
            $subPaths[] = 'images/' . $candBase;
            $subPaths[] = 'media/' . $candBase;
            $subPaths[] = 'uploads/m_media/' . $cand;
            $subPaths[] = 'storage/' . $cand;
        }
        $subPaths = array_unique(array_filter($subPaths));

        foreach ($subPaths as $sub) {
            $pathsToCheck = [];
            if (function_exists('public_path')) {
                $pathsToCheck[] = public_path($sub);
                $pathsToCheck[] = public_path('storage/' . $sub);
            }
            if (function_exists('storage_path')) {
                $pathsToCheck[] = storage_path('app/public/' . $sub);
                $pathsToCheck[] = storage_path('app/' . $sub);
                $pathsToCheck[] = storage_path($sub);
            }
            if (function_exists('base_path')) {
                $pathsToCheck[] = base_path('storage/app/public/' . $sub);
                $pathsToCheck[] = base_path('public/' . $sub);
                $pathsToCheck[] = base_path('public/storage/' . $sub);
                $pathsToCheck[] = base_path($sub);
            }
            $pathsToCheck[] = '/opt/www/hris/storage/app/public/' . $sub;
            $pathsToCheck[] = '/opt/www/hris/public/' . $sub;
            $pathsToCheck[] = '/var/www/html/storage/app/public/' . $sub;
            $pathsToCheck[] = '/var/www/html/public/' . $sub;
            $pathsToCheck[] = '/var/www/hris/storage/app/public/' . $sub;
            $pathsToCheck[] = '/var/www/hris/public/' . $sub;

            foreach ($pathsToCheck as $full) {
                if ($full && file_exists($full) && is_file($full) && filesize($full) > 50) {
                    $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        'svg' => 'image/svg+xml',
                        default => 'image/jpeg'
                    };
                    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
                }
            }
        }

        if (function_exists('file_get_contents')) {
            $streamCtx = stream_context_create([
                'http' => ['timeout' => 2, 'ignore_errors' => true],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $urlsToTry = [];
            if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                $urlsToTry[] = $rawPath;
            }
            foreach ($pathCandidates as $cand) {
                $candBase = basename($cand);
                $urlsToTry[] = 'https://hris.temprina.com/storage/uploads/m_media/' . $candBase;
                $urlsToTry[] = 'https://hris.temprina.com/storage/uploads/m_media/' . rawurlencode($candBase);
                $urlsToTry[] = 'https://hris.temprina.com/uploads/m_media/' . $candBase;
                $urlsToTry[] = 'https://hris.temprina.com/uploads/m_media/' . rawurlencode($candBase);
                $urlsToTry[] = 'https://hris.temprina.com/storage/' . $candBase;
                $urlsToTry[] = 'https://hris.temprina.com/' . ltrim($cand, '/');
                $urlsToTry[] = 'http://127.0.0.1/storage/uploads/m_media/' . $candBase;
                $urlsToTry[] = 'http://localhost/storage/uploads/m_media/' . $candBase;
            }

            foreach (array_unique(array_filter($urlsToTry)) as $u) {
                try {
                    $remoteData = @file_get_contents($u, false, $streamCtx);
                    if ($remoteData && strlen($remoteData) > 50) {
                        $mime = 'image/jpeg';
                        if (str_starts_with($remoteData, "\x89PNG")) {
                            $mime = 'image/png';
                        } elseif (str_starts_with($remoteData, "GIF")) {
                            $mime = 'image/gif';
                        }
                        return 'data:' . $mime . ';base64,' . base64_encode($remoteData);
                    }
                } catch (\Throwable $e) {}
            }
        }
    }

    if (function_exists('base_path')) {
        $localFiles = ($defaultType === 'png') ? [
            base_path('media/footer_pkwt.png'),
            base_path('media/footer_cabang_temprina.jpg'),
        ] : [
            base_path('media/header_pkwt_temprina.jpg'),
            base_path('media/header_pkwt_temprina.png'),
        ];
        foreach ($localFiles as $lf) {
            if ($lf && file_exists($lf) && is_file($lf) && filesize($lf) > 50) {
                $ext = strtolower(pathinfo($lf, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($lf));
            }
        }
    }

    $mime = ($defaultType === 'png') ? 'image/png' : 'image/jpeg';
    return 'data:' . $mime . ';base64,' . $defaultB64;
};

$headerPkwtImgSrc = $resolvePkwtMedia($kopMediaPkwt, $b64HeaderPkwt, 'jpg');
$footerPkwtImgSrc = $resolvePkwtMedia($footerMediaPkwt, $b64FooterPkwt, 'png');
@endphp

<!-- ==================== HALAMAN 1 ==================== -->
<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:0 auto; font-size:10pt; line-height:1.24; color:#000;">

    <!-- KOP SURAT TEMPRINA RESMI (FORMAT PDF ACUAN) -->
@if(view()->exists('projects.web_sk_kop_pkwt'))
    @include('projects.web_sk_kop_pkwt')
@elseif(view()->exists('web_sk_kop_pkwt'))
    @include('web_sk_kop_pkwt')
@else
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:12px; padding:0;">
  <tr>
    <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
      <img src="{{ $headerPkwtImgSrc }}" width="700" style="width:700px; max-width:100%;" alt="Kop Surat PKWT">
    </td>
  </tr>
</table>
@endif


  <!-- JUDUL & NOMOR -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0;">
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:13pt; letter-spacing:0.5px;">
        <u>PERJANJIAN KERJA WAKTU TERTENTU (HL)</u>
      </td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; font-size:10.5pt; padding-top:2px;">
        No. {{ $nomor }}
      </td>
    </tr>
    <tr><td style="height:8px; line-height:8px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- PEMBUKA -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <tr>
      <td style="text-align:left; margin:0; padding:0;">Kami yang bertanda tangan di bawah ini :</td>
    </tr>
    <tr><td style="height:4px; line-height:4px; font-size:1px;">&nbsp;</td></tr>
  </table>

  <!-- DATA PIHAK PERTAMA -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <tr>
      <td style="width:4%; vertical-align:top; padding:1px 0;">1</td>
      <td style="width:22%; vertical-align:top; padding:1px 0;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="width:71%; vertical-align:top; padding:1px 0;"><b>{{ $namaPihak1 }}</b></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Jabatan</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $jabatanPihak1 }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Alamat Perusahaan</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $alamatPerusahaan }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Jenis Usaha</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $jenisUsaha }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="3" style="vertical-align:top; text-align:justify; padding:3px 0 6px 0;">
        Bertindak atas nama {{ $jabatanPihak1 }} {{ $companyName }}, selanjutnya disebut sebagai Pemberi Kerja atau <b>PIHAK PERTAMA</b>.
      </td>
    </tr>
  </table>

  <!-- DATA PIHAK KEDUA -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <tr>
      <td style="width:4%; vertical-align:top; padding:1px 0;">2</td>
      <td style="width:22%; vertical-align:top; padding:1px 0;">Nama</td>
      <td style="width:3%; vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="width:71%; vertical-align:top; padding:1px 0;"><b>{{ $namaPihak2 }}</b></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Jenis Kelamin/Usia</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $jkUsia }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">NIK</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $nikPihak2 }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Alamat</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $alamatPihak2 }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="vertical-align:top; padding:1px 0;">Pendidikan</td>
      <td style="vertical-align:top; text-align:center; padding:1px 0;">:</td>
      <td style="vertical-align:top; padding:1px 0;">{{ $pendidikanTerakhir }}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="3" style="vertical-align:top; text-align:justify; padding:3px 0 6px 0;">
        Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai Pekerja atau <b>PIHAK KEDUA</b>.
      </td>
    </tr>
  </table>

  <!-- PEMBUKA PARA PIHAK -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <tr>
      <td style="text-align:justify; padding-bottom:4px;">
        PIHAK PERTAMA dan PIHAK KEDUA, bersama-sama dalam Perjanjian ini disebut sebagai <b>PARA PIHAK</b>.
      </td>
    </tr>
    <tr>
      <td style="text-align:justify; padding-bottom:6px;">
        PARA PIHAK, pada hari ini <b>{{ $tanggalIndo }}</b> telah bersepakat untuk membuat dan menandatangani Perjanjian Kerja Waktu Tertentu (PKWT) dengan sistem upah harian (harian lepas) yang dalam hal ini disebut dengan “Perjanjian Kerja”dengan syarat-syarat tersebut di bawah ini :
      </td>
    </tr>
  </table>

  <!-- PASAL 1 S/D 4 -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <!-- PASAL 1 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 1. MULAI DAN JANGKA WAKTU BERLAKUNYA PERJANJIAN KERJA :
      </td>
    </tr>
    <tr>
      <td style="width:5%; vertical-align:top; padding-bottom:2px;">1.1</td>
      <td style="width:95%; vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa Perjanjian Kerja ini berlaku <b>selama {{ $lamaKontrak }} bulan mulai tanggal {{ $durasiKontrak }}</b>.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:5px;">1.2</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:5px;">
        PIHAK PERTAMA berhak untuk memperpanjang maupun tidak memperpanjang Perjanjian Kerja ini berdasarkan kebutuhan volume pekerjaan, evaluasi dan hasil kerja PIHAK KEDUA yang pelaksanaannya didasarkan pada Keputusan Menteri Tenaga Kerja Nomor : 100/MEN/VI/2004 atau Peraturan perundang-undangan yang terkait.
      </td>
    </tr>

    <!-- PASAL 2 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 2. PENEMPATAN TUGAS, HARI dan JAM KERJA :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">2.1</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa PIHAK KEDUA akan dipekerjakan pada bagian <b>{{ $posisiBaru }} {{ $alamatPenempatan }}</b> atau dialihkan ke lain bagian sesuai dengan kebutuhan Perusahaan dan/atau demi perkembangan skill dan kompetensi PIHAK KEDUA.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">2.2</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PIHAK KEDUA wajib hadir kerja sesuai dengan aturan hari dan jam kerja yang ditetapkan oleh PIHAK PERTAMA atau wakil PIHAK PERTAMA sesuai dengan bidang dan jenis pekerjaan PIHAK PERTAMA.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:5px;">2.3</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:5px;">
        Apabila diperlukan, mengingat banyaknya permintaan / pesanan atau kebutuhan lain kepada Perusahaan, PIHAK KEDUA bersedia dan sepakat untuk kerja lembur yang tata cara pengaturannya baik waktu dan upahnya sesuai dengan kebijakan PIHAK PERTAMA dan Peraturan Perundang-undangan yang berlaku.
      </td>
    </tr>

    <!-- PASAL 3 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 3. TUGAS KERJA DAN TANGGUNG JAWAB :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:5px;">3.1</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:5px;">
        PARA PIHAK sepakat bahwa tugas dan tanggungjawab PIHAK KEDUA adalah sebagaimana yang tercantum dalam Job Description atau yang sudah ditentukan dalam Peraturan Perusahaan dan atau dokumen lain yang terkait.
      </td>
    </tr>

    <!-- PASAL 4 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 4. PEMUTUSAN HUBUNGAN KERJA :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">4.1</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Perjanjian Kerja ini berakhir demi hukum sesuai dengan berakhirnya waktu yang disepakati PARA PIHAK, kecuali diperpanjang atau diakhiri setelah disetujui oleh PARA PIHAK secara tertulis.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">4.2</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK SEPAKAT bahwa Perjanjian Kerja berakhir tanpa ada kewajiban pembayaran kompensasi apapun dari PIHAK PERTAMA kepada PIHAK KEDUA, jika PIHAK PERTAMA tertangkap tangan dan/atau melanggar peraturan perundang-undangan di bidang hukum pidana (misal : pemalsuan, pencurian, penggelapan, penipuan, penganiayaan, judi, asusila, dll) atau melakukan kesalahan berat sebagaimana Pasal 158 ayat 1 Undang-undang Nomor 13 Tahun 2003 tentang Ketenagakerjaan dan/atau sebagaimana pelanggaran-pelanggaran berat yang diatur dalam Peraturan Perusahaan.
      </td>
    </tr>
  </table>

    <!-- FOOTER PKWT TEMPLATE (STATIONERY / JPSTORE BANNER) -->
@if(view()->exists('projects.web_sk_footer_pkwt'))
    @include('projects.web_sk_footer_pkwt')
@elseif(view()->exists('web_sk_footer_pkwt'))
    @include('web_sk_footer_pkwt')
@else
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:14px; padding:0;">
  <tr>
    <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
      <img src="{{ $footerPkwtImgSrc }}" width="700" style="width:700px; max-width:100%;" alt="Footer PKWT">
    </td>
  </tr>
</table>
@endif

</div>

<!-- ==================== PAGE BREAK KE HALAMAN 2 ==================== -->
<div style="page-break-before:always;"></div>

<!-- ==================== HALAMAN 2 ==================== -->
<div style="font-family:'Times New Roman', Times, serif; width:92%; margin:0 auto; font-size:10pt; line-height:1.24; color:#000;">

    <!-- KOP SURAT TEMPRINA RESMI HALAMAN 2 (FORMAT PDF ACUAN) -->
@if(view()->exists('projects.web_sk_kop_pkwt'))
    @include('projects.web_sk_kop_pkwt')
@elseif(view()->exists('web_sk_kop_pkwt'))
    @include('web_sk_kop_pkwt')
@else
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:12px; padding:0;">
  <tr>
    <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
      <img src="{{ $headerPkwtImgSrc }}" width="700" style="width:700px; max-width:100%;" alt="Kop Surat PKWT">
    </td>
  </tr>
</table>
@endif


  <!-- PASAL 5 S/D 7 -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0; padding:0; font-size:10pt; line-height:1.24;">
    <!-- PASAL 5 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 5. UPAH / GAJI dan CARA PEMBAYARAN :
      </td>
    </tr>
    <tr>
      <td style="width:5%; vertical-align:top; padding-bottom:2px;">5.1.</td>
      <td style="width:95%; vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa PIHAK KEDUA diberikan upah yang dihitung harian berdasarkan jumlah kehadiran kerja PIHAK KEDUA yakni sejumlah <b>Rp {{ $upahHarianFormatted }} ({{ $upahHarianTerbilang }})</b> untuk setiap hari kehadiran PIHAK KEDUA.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">5.2.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa Pajak atas penghasilan (PPH Pasal 21) PIHAK KEDUA ditanggung oleh PIHAK PERTAMA.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">5.3.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Upah PIHAK KEDUA akan dibayarkan PIHAK PERTAMA melalui transfer ke rekening PIHAK KEDUA yakni pada awal bulan selanjutnya PIHAK KEDUA kepada PIHAK PERTAMA.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:6px;">5.4.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:6px;">
        PIHAK KEDUA menerima fasilitas BPJS Ketenagakerjaan dengan manfaat Jaminan Kecelakaan Kerja dan Jaminan Kematian yang dibebankan seluruhnya kepada PIHAK PERTAMA.
      </td>
    </tr>

    <!-- PASAL 6 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 6. LAIN – LAIN :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">6.1.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Pada saat ditandatanganinya Perjanjian Kerja ini, PIHAK PERTAMA telah memberikan 1 (satu) eksemplar dan menyampaikan Peraturan Perusahaan serta PIHAK KEDUA telah membaca, mengerti dan memahami Peraturan Perusahaan yang berlaku.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">6.2.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa hal-hal lain dalam Peraturan Perusahaan tersebut yang tidak tercantum pada Perjanjian Kerja ini tetap mengikat dan menjadi bagian yang tidak terpisahkan dalam Perjanjian Kerja ini.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">6.3.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Segala hal yang belum diatur dalam Perjanjian Kerja ini baik atas penambahan-penambahan maupun perubahan-perubahan yang disepakati PARA PIHAK akan diatur dalam addendum Perjanjian Kerja dan/atau dokumen lain yang menjadi bagian tidak terpisahkan dalam Perjanjian Kerja ini.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:6px;">6.4.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:6px;">
        PARA PIHAK menyatakan bahwa data dan keterangan yang disampaikan dalam Perjanjian ini adalah benar adanya dan dapat dipertanggungjawabkan, serta akan saling menginformasikan kepada PIHAK lainnya apabila dalam masa berlakunya Perjanjian Kerja ini ada perubahan terkait data dan keterangan paling lambat 7 (tujuh) hari sejak adanya perubahan data dan keterangan.
      </td>
    </tr>

    <!-- PASAL 7 -->
    <tr>
      <td colspan="2" style="font-weight:bold; padding-top:2px; padding-bottom:2px;">
        PASAL 7. PENUTUP :
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">7.1.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        PARA PIHAK sepakat bahwa perselisihan yang mungkin timbul selama pelaksanaan perjanjian ini akan diselesaikan secara musyawarah mufakat serta diselesaikan melalui Pegawai Mediator di Dinas Ketenagakerjaan Kabupaten Gresik dan apabila tidak tercapai mufakat maka PARA PIHAK memilih menyelesaikan melalui Kepaniteraan Pengadilan Hubungan Industrial pada Pengadilan Negeri Kabupaten Gresik
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:2px;">7.2.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:2px;">
        Demikian perjanjian kerja ini dibuat, disetujui, dan ditandatangani PARA PIHAK tanpa ada paksaan dari pihak manapun juga serta dibuat rangkap 2 (dua) yang masing-masing PIHAK memiliki 1 (satu) rangkap dengan bunyi yang sama dan memiliki kekuatan pembuktian yang sama juga.
      </td>
    </tr>
    <tr>
      <td style="vertical-align:top; padding-bottom:6px;">7.3.</td>
      <td style="vertical-align:top; text-align:justify; padding-bottom:6px;">
        Perjanjian Kerja ini berlaku sejak tanggal ditetapkan. Jika di kemudian hari terdapat kesalahan dalam perjanjian kerja ini, maka akan diadakan perbaikan seperlunya.
      </td>
    </tr>
  </table>

  <!-- TEMPAT & TANGGAL KESEPAKATAN -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:6px; font-size:10pt; line-height:1.24;">
    <tr>
      <td style="width:24%;">Dibuat dan disepakati di</td>
      <td style="width:2%;">:</td>
      <td style="width:74%;">{{ $kotaTerbit }}</td>
    </tr>
    <tr>
      <td>Tanggal</td>
      <td>:</td>
      <td>{{ $tanggal }}</td>
    </tr>
  </table>

  <!-- TANDA TANGAN PARA PIHAK -->
  <table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:12px; font-size:10pt;">
    <tr>
      <td style="width:50%; text-align:center; vertical-align:top;">
        <b>PIHAK PERTAMA</b><br>
        <b>{{ $companyName }}</b>
      </td>
      <td style="width:50%; text-align:center; vertical-align:top;">
        <b>PIHAK KEDUA</b>
      </td>
    </tr>
    <tr>
      <td style="height:48px;"></td>
      <td style="height:48px;"></td>
    </tr>
    <tr>
      <td style="text-align:center; vertical-align:top;">
        <b><u>{{ $namaPihak1 }}</u></b>
      </td>
      <td style="text-align:center; vertical-align:top;">
        <b><u>{{ $namaPihak2 }}</u></b>
      </td>
    </tr>
  </table>

    <!-- FOOTER PKWT TEMPLATE (STATIONERY / JPSTORE BANNER) HALAMAN 2 -->
@if(view()->exists('projects.web_sk_footer_pkwt'))
    @include('projects.web_sk_footer_pkwt')
@elseif(view()->exists('web_sk_footer_pkwt'))
    @include('web_sk_footer_pkwt')
@else
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:14px; padding:0;">
  <tr>
    <td align="center" style="width:100%; text-align:center; padding:0; vertical-align:middle;">
      <img src="{{ $footerPkwtImgSrc }}" width="700" style="width:700px; max-width:100%;" alt="Footer PKWT">
    </td>
  </tr>
</table>
@endif

</div>