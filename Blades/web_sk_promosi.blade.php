@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CustomModels\t_mutasi;
use App\Models\CustomModels\t_jadwal_kerja_n;
use App\Models\CustomModels\t_jadwal_kerja_d_hari_n;

Carbon::setLocale('id'); 
$req = app()->request;

$id = $req->id;
$t_mutasi = t_mutasi::find($id);

$carbonDate = Carbon::parse($t_mutasi->tgl);
//$namaHari = $carbonDate->translatedFormat('l');
$tanggalIndo = $carbonDate->translatedFormat('l, d F Y');

$terbitDate = Carbon::parse($t_mutasi?->updated_at ?? $t_mutasi?->created_at ?? now());
$tanggalTerbit = $terbitDate->translatedFormat('d F Y');

$tembusan = $t_mutasi?->t_mutasi_d_tembusan ?? null;
if ((!$tembusan || count($tembusan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $tembusan = \DB::table('t_mutasi_d_tembusan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}
if ($tembusan && count($tembusan) > 0) {
    $tembusan = collect($tembusan)->unique(function ($item) {
        return trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
    })->values();
}

$memperhatikan = $t_mutasi?->t_mutasi_d_memperhatikan ?? null;
if ((!$memperhatikan || count($memperhatikan) === 0) && !empty($t_mutasi?->id)) {
    try {
        $memperhatikan = \DB::table('t_mutasi_d_memperhatikan')->where('t_mutasi_id', $t_mutasi->id)->get();
    } catch (\Throwable $th) {}
}
if ($memperhatikan && count($memperhatikan) > 0) {
    $memperhatikan = collect($memperhatikan)->unique(function ($item) {
        return trim(is_array($item) ? ($item['value'] ?? '') : ($item->value ?? ''));
    })->values();
}

$jadwalLama = null;
if (!empty($t_mutasi?->jadwal_kerja_lama_id)) {
    try {
        $jadwalLama = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $t_mutasi->jadwal_kerja_lama_id)
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '')
            ->first();
    } catch (\Throwable $th) {
        $jadwalLama = null;
    }
}

$jadwalBaru = null;
if (!empty($t_mutasi?->jadwal_kerja_baru_id)) {
    try {
        $jadwalBaru = \DB::table('t_jadwal_kerja_d_hari_n')
            ->where('t_jadwal_kerja_n_id', $t_mutasi->jadwal_kerja_baru_id)
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '!=', '')
            ->first();
    } catch (\Throwable $th) {
        $jadwalBaru = null;
    }
}

$formatJam = function($jadwal) {
    if (!$jadwal || empty($jadwal->waktu_mulai)) {
        return '08:00 - 17:00 (Kebutuhan Setempat)';
    }
    $mulai = substr($jadwal->waktu_mulai, 0, 5);
    $akhir = !empty($jadwal->waktu_akhir) ? substr($jadwal->waktu_akhir, 0, 5) : (!empty($jadwal->waktu_selesai) ? substr($jadwal->waktu_selesai, 0, 5) : '17:00');
    return "{$mulai} - {$akhir} (Kebutuhan Setempat)";
};

$jamKerjaLama = $formatJam($jadwalLama);
$jamKerjaBaru = $formatJam($jadwalBaru);

$sbuCode = strtoupper($t_mutasi?->m_sbu_baru?->kode ?? $t_mutasi?->m_kary?->m_sbu?->kode ?? '');
$sbuName = strtoupper($t_mutasi?->m_sbu_baru?->name ?? $t_mutasi?->m_kary?->m_sbu?->name ?? '');
$isJpBooks = str_contains($sbuCode, 'JP') || str_contains($sbuName, 'JP') || str_contains($sbuName, 'SAHABAT EDUKASI');

$companyName = $isJpBooks ? 'PT Media Sahabat Edukasi' : 'PT Temprina Media Grafika';
$kotaTerbit = $t_mutasi?->m_branch_baru?->kota 
            ?? $t_mutasi?->m_branch_lama?->kota 
            ?? $t_mutasi?->m_kary?->m_branch?->kota 
            ?? 'Surabaya';

$subJudul = strtoupper($t_mutasi?->jenis_surat?->value ?? 'PROMOSI JABATAN');
if (!str_contains($subJudul, 'JABATAN')) {
    $subJudul .= ' JABATAN';
}
@endphp

<div style="font-family:'Times New Roman',serif;width:90%;margin:auto;font-size:12px;line-height:1.5;">

  @include('projects.web_sk_kop')

  <!-- JUDUL -->
  <table style="width:100%;margin-top:20px;">
    <tr>
      <td style="width:30%;"></td>
      <td
        style="width:40%;text-align:center;font-weight:bold;font-size:15px;border-bottom:2px solid black;height:28px;">
        SURAT KEPUTUSAN
      </td>
      <td style="width:30%;"></td>
    </tr>
    <tr>
      <td></td>
      <td style="text-align:center;font-weight:bold;font-size:11px;padding-top:4px;">
        No. {{ strtoupper($t_mutasi?->nomor ?? '-') }}
      </td>
      <td></td>
    </tr>
  </table>

  <div></div>

  <!-- SUB JUDUL -->
  <table style="width:100%;margin-top:16px;border-collapse:collapse;">
    <tr>
      <td style="width:25%;"></td>
      <td style="width:50%;text-align:center;font-size:11px;font-weight:bold;line-height:1.4;">
        TENTANG <br> {{$subJudul}}
      </td>
      <td style="width:25%;"></td>
    </tr>
  </table>

  <div></div>

  <!-- MEMPERHATIKAN -->
  <table style="width:100%; margin-top:16px; border-collapse:collapse; font-size:11px;">
    <tr>
      <td style="width:25%; vertical-align:top; font-weight:bold;">MEMPERHATIKAN</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:72%; vertical-align:top; text-align:justify;">
        @if($memperhatikan && count($memperhatikan) > 0)
          <table style="width:100%; border-collapse:collapse; font-size:11px;">
            @foreach($memperhatikan as $item)
              <tr>
                <td style="width:4%; vertical-align:top;">•</td>
                <td style="vertical-align:top; text-align:justify; padding-bottom:3px;">
                  {{ $item->value ?? $item['value'] }}
                </td>
              </tr>
            @endforeach
          </table>
        @else
          Kebutuhan operasional serta pembinaan sumber daya manusia di lingkungan perusahaan.
        @endif
      </td>
    </tr>
  </table>

  <!-- MEMUTUSKAN -->
  <table style="width:100%; margin-top:12px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:25%; vertical-align:top; font-weight:bold; line-height:1.4;">MEMUTUSKAN<br>SERTA MENETAPKAN</td>
      <td style="width:3%; vertical-align:top; text-align:center;">:</td>
      <td style="width:72%; vertical-align:top;">

        <table style="width:100%; border-collapse:collapse; font-size:11px;">

          <!-- POIN 1 -->
          <tr>
            <td style="width:4%; vertical-align:top;">1.</td>
            <td style="width:96%; vertical-align:top;">
              Terhitung efektif <b>{{$tanggalIndo}}</b>, kepada:
            </td>
          </tr>

          <tr>
            <td></td>
            <td style="padding-top:4px;">
              <table style="width:100%; border-collapse:collapse; font-size:11px;">
                <tr>
                  <td style="width:18%; vertical-align:top;">Nama</td>
                  <td style="width:3%; vertical-align:top;">:</td>
                  <td style="width:79%; vertical-align:top;"><b>{{$t_mutasi->m_kary?->nama_lengkap ?? '-'}}</b></td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- TEMPAT TUGAS LAMA -->
          <tr>
            <td></td>
            <td style="padding-top:8px; font-size:11px; font-weight:bold; text-decoration:underline;">
              Tempat Tugas Lama :
            </td>
          </tr>

          <tr>
            <td></td>
            <td>
              <table style="width:100%; margin-top:4px; font-size:11px; border-collapse:collapse;">
                <tr>
                  <td style="width:18%; vertical-align:top;">- Jabatan</td>
                  <td style="width:3%; vertical-align:top;">:</td>
                  <td style="width:79%; vertical-align:top;">
                    <strong>{{ $t_mutasi->m_posisi_lama?->name ?? '-' }}</strong><br>
                    {{ $t_mutasi->m_sub_lama?->name ?? '-' }}<br>
                    <span style="color:#555;">{{ $t_mutasi->m_sub_lama?->address ?? '' }}</span>
                  </td>
                </tr>
                <tr>
                  <td style="vertical-align:top;">- Jam Kerja</td>
                  <td style="vertical-align:top;">:</td>
                  <td style="vertical-align:top;">
                    {{ $jamKerjaLama }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- TEMPAT TUGAS BARU -->
          <tr>
            <td></td>
            <td style="padding-top:10px; font-size:11px; font-weight:bold; text-decoration:underline;">
              Tempat Tugas Baru :
            </td>
          </tr>

          <tr>
            <td></td>
            <td>
              <table style="width:100%; margin-top:4px; font-size:11px; border-collapse:collapse;">
                <tr>
                  <td style="width:18%; vertical-align:top;">- Jabatan</td>
                  <td style="width:3%; vertical-align:top;">:</td>
                  <td style="width:79%; vertical-align:top;">
                    <strong>{{ $t_mutasi->m_posisi_baru?->name ?? '-' }}</strong><br>
                    {{ $t_mutasi->m_sub_baru?->name ?? '-' }}<br>
                    <span style="color:#555;">{{ $t_mutasi->m_sub_baru?->address ?? '' }}</span>
                  </td>
                </tr>
                <tr>
                  <td style="vertical-align:top;">- Jam Kerja</td>
                  <td style="vertical-align:top;">:</td>
                  <td style="vertical-align:top;">
                    {{ $jamKerjaBaru }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- POIN 2 -->
          <tr>
            <td style="vertical-align:top; padding-top:10px;">2.</td>
            <td style="padding-top:10px; text-align:justify;">
              Keputusan ini berlaku sejak tanggal efektif dan akan ditinjau kembali
              apabila di kemudian hari terdapat kekeliruan.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

  <div></div>

  <!-- PENUTUP -->
  <table style="width:100%; margin-top:16px; font-size:11px; border-collapse:collapse;">
    <tr>
      <td style="width:55%;"></td>
      <td style="width:45%; vertical-align:top;">
        Dikeluarkan di&nbsp;&nbsp;: {{ $kotaTerbit }}<br>
        Pada Tanggal&nbsp;&nbsp;: {{ $tanggalTerbit }}
        <div style="margin-top:10px; font-weight:bold;">
          {{ $companyName }}
        </div>
        <div style="height:48px;"></div>
        <div style="font-weight:bold; text-decoration:underline;">
          {{ $t_mutasi->signature?->nama_lengkap ?? '( ........................................ )' }}
        </div>
        <div style="font-style:italic;">
          {{ $t_mutasi->signature?->m_posisi?->name ?? 'Human Capital Dept.' }}
        </div>
      </td>
    </tr>
  </table>

  <!-- TEMBUSAN -->
  <table style="width:100%; margin-top:10px; font-size:10px; border-collapse:collapse;">
    <tr>
      <td style="width:100%;">
        <b>Tembusan :</b><br>
        @if($tembusan && count($tembusan) > 0)
          @foreach($tembusan as $index => $t)
            &nbsp;&nbsp;{{ $index + 1 }}. {{ $t->value ?? $t['value'] }}<br>
          @endforeach
        @else
          &nbsp;&nbsp;1. Direksi<br>
          &nbsp;&nbsp;2. Human Capital Dept.<br>
          &nbsp;&nbsp;3. Yang Bersangkutan<br>
          &nbsp;&nbsp;4. Arsip
        @endif
      </td>
    </tr>
  </table>

  @if($isJpBooks)
    @include('projects.web_sk_footer')
  @else
    <!-- FOOTER CABANG TEMPRINA (SESUAI DOKUMEN RIIL TEMPRINA) -->
    <div style="margin-top:15px; border-top:0.5px solid #bbb; padding-top:4px; font-size:6.8px; line-height:1.35; color:#333; text-align:justify;">
      <b>Bekasi :</b> 021-8815222 Fax : 021-8817444, E-mail : Bekasi@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Cengkareng :</b> 021-5553472 Fax : 021-5553473, E-mail : Cengkareng@temprina.com<br>
      <b>Semarang :</b> 024-7462136 Fax : 024-7462135, E-mail : Semarang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Solo :</b> 0271-783001 Fax : 0271-782769, E-mail : Solo@temprina.com<br>
      <b>Malang :</b> 0341-396700 Fax : 0341-396800, E-mail : Malang@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Nganjuk :</b> 0358-773500,771199 Fax : 0358-773465, E-mail : Nganjuk@temprina.com<br>
      <b>Jember :</b> 0331-320300 Fax : 0331-320190, E-mail : Jember@temprina.com &nbsp;&nbsp;&nbsp;&nbsp;<b>Bali :</b> 0361-421384 Fax : 0361-417155, E-mail : Bali@temprina.com
    </div>
  @endif

</div>