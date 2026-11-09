@verbatim
<div class="hd-page">
  <div v-if="errorMsg" class="hd-alert">{{ errorMsg }}</div>
  <div v-if="!dash.akses && loading" class="hd-loading">Memuat dashboard...</div>

  <!-- ================= KARYAWAN SELAIN HC: sambutan + absensi sendiri ================= -->
  <template v-if="dash.akses === 'pribadi'">
    <div class="hd-hero">
      <div>
        <div class="hd-hero-hi">{{ sapaan }},</div>
        <h1 class="hd-hero-name">{{ p.karyawan.nama || p.karyawan.panggilan || 'Karyawan' }}</h1>
        <div class="hd-hero-meta">
          <span v-if="p.karyawan.jabatan">{{ p.karyawan.jabatan }}</span>
          <span v-if="p.karyawan.divisi">{{ p.karyawan.divisi }}</span>
          <span v-if="p.karyawan.cabang">{{ p.karyawan.cabang }}</span>
        </div>
        <div v-if="p.karyawan.ulang_tahun_hari_ini" class="hd-hero-bday">Selamat ulang tahun! 🎉</div>
      </div>
      <div class="hd-hero-date">
        <div class="hd-hero-day">{{ p.hari }}</div>
        <div>{{ tanggalPanjang(p.tanggal) }}</div>
      </div>
    </div>

    <div class="hd-grid hd-row2">
      <div class="hd-card">
        <div class="hd-h3">Absensi hari ini
          <span v-if="p.hari_ini && p.hari_ini.tipe_hari">{{ p.hari_ini.keterangan_libur || p.hari_ini.tipe_hari }}</span>
        </div>
        <div class="hd-today">
          <div class="hd-today-box">
            <div class="hd-lbl">Masuk</div>
            <div class="hd-today-time" :class="p.hari_ini && p.hari_ini.telat ? 'hd-c-amber' : 'hd-c-green'">{{ jam(p.hari_ini && p.hari_ini.masuk) }}</div>
            <div class="hd-sub">jadwal {{ jam(p.hari_ini && p.hari_ini.jadwal_masuk) }}<span v-if="p.hari_ini && p.hari_ini.telat"> · terlambat</span></div>
          </div>
          <div class="hd-today-box">
            <div class="hd-lbl">Pulang</div>
            <div class="hd-today-time hd-c-blue">{{ jam(p.hari_ini && p.hari_ini.pulang) }}</div>
            <div class="hd-sub">jadwal {{ jam(p.hari_ini && p.hari_ini.jadwal_pulang) }}</div>
          </div>
        </div>
        <div class="hd-actions">
          <button class="hd-action hd-action-main" @click="buka('/absensi_online')">Absensi Online</button>
          <button class="hd-action" @click="buka('/notifikasi')">Notification</button>
        </div>
      </div>

      <div class="hd-card">
        <div class="hd-h3">Periode ini <span>{{ tanggalPendek(p.periode.awal) }} – {{ tanggalPendek(p.periode.akhir) }}</span></div>
        <div class="hd-mini">
          <div><div class="hd-lbl">Hadir</div><div class="hd-mini-val hd-c-green">{{ p.ringkasan.hadir }}</div><div class="hd-sub">hari</div></div>
          <div><div class="hd-lbl">Terlambat</div><div class="hd-mini-val hd-c-amber">{{ p.ringkasan.telat }}</div><div class="hd-sub">kali</div></div>
          <div><div class="hd-lbl">Tanpa jam pulang</div><div class="hd-mini-val hd-c-red">{{ p.ringkasan.tanpa_pulang }}</div><div class="hd-sub">hari</div></div>
          <div><div class="hd-lbl">Cuti</div><div class="hd-mini-val hd-c-violet">{{ p.ringkasan.cuti }}</div><div class="hd-sub">hari</div></div>
        </div>
        <div v-if="p.karyawan.sisa_cuti !== null && p.karyawan.sisa_cuti !== undefined" class="hd-note">Sisa cuti tahunan: <b>{{ p.karyawan.sisa_cuti }}</b> hari</div>
      </div>
    </div>

    <div class="hd-section">Riwayat absensi terakhir</div>
    <div class="hd-card">
      <div class="hd-table-wrap">
        <table class="hd-table">
          <thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Jadwal</th><th>Keterangan</th></tr></thead>
          <tbody>
            <tr v-for="r in p.riwayat" :key="r.tanggal">
              <td>{{ tanggalPendek(r.tanggal, true) }}</td>
              <td :class="r.telat ? 'hd-c-amber' : ''">{{ jam(r.masuk) }}</td>
              <td>{{ jam(r.pulang) }}</td>
              <td>{{ r.jadwal_masuk ? jam(r.jadwal_masuk) + ' – ' + jam(r.jadwal_pulang) : '-' }}</td>
              <td>
                <span v-if="r.telat" class="hd-pill hd-p-amber">Terlambat</span>
                <span v-if="r.status === 'WORKING' && r.tanggal !== p.tanggal" class="hd-pill hd-p-red">Belum absen pulang</span>
                <span v-if="!r.telat && !(r.status === 'WORKING' && r.tanggal !== p.tanggal)" class="hd-pill hd-p-green">Sesuai</span>
              </td>
            </tr>
            <tr v-if="!p.riwayat.length"><td colspan="5" class="hd-empty-cell">Belum ada absensi di periode ini</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>

  <!-- ================= HC / HOLDING ================= -->
  <template v-if="dash.akses === 'hc'">
  <div class="hd-top">
    <div>
      <h1 class="hd-title">Dashboard</h1>
      <p class="hd-subtitle">Data mengikuti respo aktif: <b>{{ dash.lingkup.nama || '-' }}</b></p>
    </div>
    <div class="hd-chips">
      <label class="hd-chip">Tanggal
        <input type="date" class="hd-date" :value="tanggal" @change="ev => gantiTanggal(ev.target.value)" />
      </label>
      <button class="hd-chip hd-btn" :disabled="loading" @click="muat">{{ loading ? 'Memuat...' : 'Muat ulang' }}</button>
    </div>
  </div>

  <!-- RINGKASAN -->
  <div class="hd-grid hd-kpis">
    <div class="hd-card hd-kpi">
      <div class="hd-lbl">Karyawan aktif</div>
      <div class="hd-val hd-c-blue">{{ angka(r.aktif) }}</div>
      <div class="hd-sub">{{ teksStatus }}</div>
    </div>
    <div class="hd-card hd-kpi">
      <div class="hd-lbl">Hadir {{ labelHari }}</div>
      <div class="hd-val hd-c-green">{{ angka(r.hadir) }} <small class="hd-of">/ {{ angka(r.aktif) }}</small></div>
      <div class="hd-sub">{{ persenHadir }}% kehadiran</div>
      <div class="hd-bar"><i :style="{ width: persenHadir + '%' }"></i></div>
    </div>
    <div class="hd-card hd-kpi">
      <div class="hd-lbl">Terlambat</div>
      <div class="hd-val hd-c-amber">{{ angka(r.telat) }}</div>
      <div class="hd-sub">masuk lewat jam jadwal</div>
    </div>
    <div class="hd-card hd-kpi">
      <div class="hd-lbl">Belum absen / tidak masuk</div>
      <div class="hd-val hd-c-red">{{ angka(r.belum_absen) }}</div>
      <div class="hd-sub">cuti hari ini: {{ angka(r.cuti_hari_ini) }}</div>
    </div>
    <div class="hd-card hd-kpi">
      <div class="hd-lbl">Menunggu persetujuan</div>
      <div class="hd-val hd-c-violet">{{ angka(r.approval_tertunda) }}</div>
      <div class="hd-sub">cuti, lembur, perdin, laporan</div>
    </div>
  </div>

  <!-- KEHADIRAN -->
  <div class="hd-section">Kehadiran</div>
  <div class="hd-grid hd-row2">
    <div class="hd-card">
      <div class="hd-h3">Kehadiran 7 hari terakhir <span>jumlah karyawan per hari</span></div>
      <column-chart :data="chartKehadiran" :stacked="true" :colors="['#1d4ed8', '#dbe3f1']" height="250px" :legend="'bottom'" />
    </div>
    <div class="hd-card">
      <div class="hd-h3">Status {{ labelHari }}</div>
      <pie-chart :data="chartStatus" :donut="true" :colors="['#12915a', '#3b82f6', '#cbd5e1']" height="220px" :legend="'bottom'" />
      <div class="hd-note">Terlambat: {{ angka(r.telat) }} orang (bagian dari yang hadir)</div>
    </div>
  </div>

  <!-- PERLU PERHATIAN -->
  <div class="hd-section">Perlu perhatian</div>
  <div class="hd-grid hd-row3">
    <div class="hd-card">
      <div class="hd-h3">Ulang tahun 10 hari ke depan <span>{{ dash.ulang_tahun.total }} orang</span></div>
      <ul class="hd-list">
        <li v-for="u in dash.ulang_tahun.list" :key="u.id">
          <div>
            <div class="hd-t">{{ u.nama }}</div>
            <div class="hd-d">{{ u.jabatan || 'Jabatan belum diisi' }}<span v-if="dash.lingkup.semua"> · {{ u.cabang || 'Tanpa cabang' }}</span></div>
          </div>
          <div class="hd-right">
            <span class="hd-pill" :class="u.sisa_hari == 0 ? 'hd-p-green' : 'hd-p-blue'">{{ labelUltah(u) }}</span>
            <div class="hd-d">ke-{{ u.umur }}</div>
          </div>
        </li>
        <li v-if="!dash.ulang_tahun.list.length" class="hd-empty">Tidak ada yang berulang tahun dalam 10 hari ke depan</li>
      </ul>
      <div v-if="dash.ulang_tahun.total > dash.ulang_tahun.list.length" class="hd-note">Ditampilkan 10 terdekat dari {{ dash.ulang_tahun.total }} orang</div>
    </div>

    <div class="hd-card">
      <div class="hd-h3">Kelengkapan data <span>agar absensi &amp; gaji benar</span></div>
      <ul class="hd-list">
        <li v-for="k in dash.kelengkapan" :key="k.key" class="hd-click" @click="buka(k.path)">
          <div>
            <div class="hd-t">{{ k.label }}</div>
            <div class="hd-d">{{ k.desc }}</div>
          </div>
          <span class="hd-pill" :class="k.n == 0 ? 'hd-p-green' : (k.level === 'red' ? 'hd-p-red' : 'hd-p-amber')">{{ angka(k.n) }}</span>
        </li>
      </ul>
    </div>

    <div class="hd-card">
      <div class="hd-h3">Menunggu persetujuan</div>
      <ul class="hd-list">
        <li v-for="a in dash.approval" :key="a.label">
          <div class="hd-t">{{ a.label }}</div>
          <span class="hd-pill hd-p-violet">{{ angka(a.n) }}</span>
        </li>
        <li v-if="!dash.approval.length" class="hd-empty">Tidak ada pengajuan yang menunggu</li>
      </ul>
      <div class="hd-h3 hd-mt">Pengingat</div>
      <ul class="hd-list">
        <li><div><div class="hd-t">Kontrak berakhir ≤ 30 hari</div><div class="hd-d">karyawan tidak tetap</div></div><span class="hd-pill" :class="dash.pengingat.kontrak_30_hari ? 'hd-p-amber' : 'hd-p-green'">{{ dash.pengingat.kontrak_30_hari }}</span></li>
        <li><div><div class="hd-t">Masuk / keluar 90 hari</div><div class="hd-d">karyawan baru dan berhenti</div></div><span class="hd-pill hd-p-blue">{{ dash.pengingat.masuk_90_hari }} / {{ dash.pengingat.keluar_90_hari }}</span></li>
      </ul>
    </div>
  </div>

  <!-- KOMPOSISI -->
  <div class="hd-section">Komposisi karyawan</div>
  <div class="hd-grid hd-row2b">
    <div class="hd-card">
      <div class="hd-h3">Per divisi <span>8 terbesar</span></div>
      <div v-for="d in dash.komposisi.divisi" :key="d.label" class="hd-hbar">
        <div class="hd-hbar-h"><span>{{ d.label }}</span><b>{{ angka(d.n) }}</b></div>
        <div class="hd-track"><i :class="{ 'hd-warn': d.label === 'Tanpa divisi' }" :style="{ width: lebarDivisi(d.n) + '%' }"></i></div>
      </div>
    </div>
    <div class="hd-card">
      <div class="hd-grid hd-two">
        <div>
          <div class="hd-h3">Status</div>
          <pie-chart :data="chartStatusKary" :donut="true" :colors="['#1d4ed8', '#60a5fa', '#c7d7fe', '#93c5fd', '#e2e8f0']" height="190px" :legend="'bottom'" />
        </div>
        <div>
          <div class="hd-h3">Gender</div>
          <pie-chart :data="chartGender" :donut="true" :colors="['#0f766e', '#f472b6', '#e2e8f0']" height="190px" :legend="'bottom'" />
        </div>
      </div>
      <div class="hd-h3 hd-mt">Masa kerja</div>
      <column-chart :data="chartMasa" :colors="['#6d4ad8']" height="180px" />
    </div>
  </div>

  <!-- STRUKTUR ORGANISASI PER CABANG -->
  <div class="hd-section">Struktur organisasi</div>
  <div class="hd-card">
    <div class="sg-head">
      <div class="sg-title">{{ sNamaCabang }} <span>{{ angka(struktur.kary.length) }} karyawan aktif</span></div>
      <div class="sg-tools">
        <select v-if="struktur.cabang.length > 1" :value="struktur.m_branch_id" @change="muatStruktur($event.target.value)">
          <option v-for="c in struktur.cabang" :key="c.id" :value="c.id">{{ c.name }} ({{ c.n }})</option>
        </select>
        <div class="sg-seg">
          <button :class="{ on: struktur.mode === 'bagan' }" @click="struktur.mode = 'bagan'">Bagan</button>
          <button :class="{ on: struktur.mode === 'level' }" @click="struktur.mode = 'level'">Per level</button>
        </div>
      </div>
    </div>

    <div v-if="struktur.error" class="sg-empty">{{ struktur.error }}</div>
    <div v-else-if="struktur.loading && !struktur.kary.length" class="sg-empty">Memuat struktur...</div>
    <div v-else-if="!struktur.kary.length" class="sg-empty">Belum ada karyawan aktif di cabang ini.</div>
    <template v-else>
      <div class="sg-stats" :style="{ opacity: struktur.loading ? .5 : 1 }">
        <span class="sg-chip">{{ sInfo.divisi }} divisi</span>
        <span class="sg-chip">{{ sInfo.pimpinan }} pimpinan</span>
        <span class="sg-chip">{{ sInfo.kadivKaru }} Kadiv/Karu</span>
        <span v-if="sInfo.tanpaJabatan" class="sg-chip warn">{{ sInfo.tanpaJabatan }} belum ada jabatan</span>
        <span v-if="sInfo.tanpaLevel > 0" class="sg-chip warn">{{ sInfo.tanpaLevel }} jabatan belum ada level</span>
        <span v-if="sInfo.tanpaDivisi" class="sg-chip warn">{{ sInfo.tanpaDivisi }} belum ada divisi</span>
        <span class="sg-legend"><span v-for="g in legendLevel" :key="g.l"><i :style="{ background: g.warna }"></i>{{ g.nama }}</span></span>
      </div>

      <!-- Bagan: pimpinan di atas, lalu satu kolom per divisi -->
      <div v-if="struktur.mode === 'bagan'" class="sg-chart" :style="{ opacity: struktur.loading ? .5 : 1 }">
        <div v-for="row in sPimpinan" :key="row.lvl" class="sg-root">
          <div v-for="(k, i) in row.orang" :key="i" class="sg-node" :style="{ '--c': warnaLevel(k.lvl) }">
            <div class="lv">{{ namaLevel(k.lvl) }}</div>
            <div class="j">{{ k.jabatan }}</div>
            <div class="n">{{ namaRapi(k.nama) }}</div>
          </div>
        </div>
        <div class="sg-lanes">
          <div v-for="d in sDivisi" :key="d.nama" class="sg-lane" :class="{ lebar: d.lebar }">
            <div class="sg-lane-h" :class="{ warn: d.kosong }">{{ d.nama }} <span>{{ d.jumlah }}</span></div>
            <div class="sg-stack">
              <!-- bagian: orang di divisi induk dulu, lalu tiap sub divisi dengan judul kecil -->
              <div v-for="(bg, bi) in d.bagian" :key="bg.key" :class="bg.sub ? 'sg-sub' : 'sg-stack'" :style="bg.sub ? { '--s': warnaSub(bi) } : null">
              <div v-if="bg.sub" class="sg-sub-h">{{ bg.nama }} <span>{{ bg.jumlah }}</span></div>
              <!-- tiap baris: 1 kolom biasa, atau 2 kolom (kiri jabatan utama, kanan wakil) -->
              <div v-for="b in bg.baris" :key="b.key" class="sg-baris" :class="{ dua: b.dua }">
                <div v-for="(sisi, si) in (b.dua ? [b.kiri, b.kanan] : [b.kiri])" :key="si" class="sg-kol">
                  <template v-for="g in sisi" :key="g.key">
                    <div v-if="g.tunggal" class="sg-node" :style="{ '--c': warnaLevel(g.lvl) }">
                      <div class="lv">{{ namaLevel(g.lvl) }}</div>
                      <div class="j">{{ g.jabatan }}</div>
                      <div class="n">{{ namaRapi(g.orang[0].nama) }}</div>
                    </div>
                    <div v-else class="sg-node sg-grp" :style="{ '--c': warnaLevel(g.lvl) }" @click="toggleGrup(bg.key + g.key)">
                      <span class="cnt">{{ g.orang.length }} org</span>
                      <div class="lv">{{ namaLevel(g.lvl) }}</div>
                      <div class="j">{{ g.jabatan }}</div>
                      <div v-if="struktur.buka[bg.key + g.key]" class="names">
                        <div v-for="(o, i) in g.orang" :key="i">{{ namaRapi(o.nama) }}</div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Per level -->
      <div v-else :style="{ opacity: struktur.loading ? .5 : 1 }">
        <div v-for="row in sLevel" :key="row.lvl" class="sg-lv-row" :style="{ '--c': warnaLevel(row.lvl) }">
          <div class="sg-lv-name">{{ namaLevel(row.lvl) }}<span>{{ row.jumlah }} orang</span></div>
          <div class="sg-lv-items">
            <span v-for="g in row.grup" :key="g.key" class="sg-pill">
              <b>{{ g.jabatan }}</b>
              <small v-if="g.tunggal">{{ namaRapi(g.orang[0].nama) }}{{ g.orang[0].divisi ? ' · ' + g.orang[0].divisi : '' }}</small>
              <small v-else>{{ g.orang.length }} orang</small>
            </span>
          </div>
        </div>
      </div>
    </template>
  </div>

  <!-- PER CABANG (respo tanpa cabang, mis. HOLDING) -->
  <template v-if="dash.lingkup.semua && dash.per_cabang.length">
    <div class="hd-section">Per cabang</div>
    <div class="hd-card">
      <div class="hd-h3">Ringkasan per cabang <span>{{ labelHari }}</span></div>
      <div class="hd-table-wrap">
        <table class="hd-table">
          <thead>
            <tr><th>Cabang</th><th class="hd-num">Karyawan aktif</th><th class="hd-num">Hadir</th><th class="hd-num">% hadir</th><th class="hd-num">Tanpa jadwal</th><th class="hd-num">Tanpa jabatan</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in dash.per_cabang" :key="c.cabang" :class="{ 'hd-row-warn': !c.id }">
              <td>{{ c.cabang }}</td>
              <td class="hd-num">{{ angka(c.aktif) }}</td>
              <td class="hd-num">{{ angka(c.hadir) }}</td>
              <td class="hd-num">{{ c.aktif ? Math.round(c.hadir * 100 / c.aktif) : 0 }}%</td>
              <td class="hd-num">{{ angka(c.tanpa_jadwal) }}</td>
              <td class="hd-num">{{ angka(c.tanpa_jabatan) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  </template>

</div>
@endverbatim
