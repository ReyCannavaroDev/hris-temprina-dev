import { useRouter } from 'vue-router'
import { ref, reactive, computed, inject, onMounted, onBeforeMount, onBeforeUnmount } from 'vue'

// Dashboard baru (dashboard lama disimpan di dashboard_lama.js).
// Data dari GET operation/m_kary/dashboard_hc; hak lihat (HC/HOLDING vs pribadi) dan lingkup cabang ditentukan server dari respo aktif user.
const router = useRouter()
const store = inject('store')

const loading = ref(false)
const errorMsg = ref('')
// tanggal lokal (bukan UTC), format YYYY-MM-DD
function hariIniLokal() {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}
const tanggal = ref(hariIniLokal())

// akses: 'hc' (respo HC / HOLDING / Admin) atau 'pribadi' (karyawan lain: sambutan + absensi sendiri), ditentukan server
const kosong = () => ({
  akses: null,
  karyawan: { nama: '', panggilan: '', jabatan: null, divisi: null, cabang: null, respo: null, sisa_cuti: null, ulang_tahun_hari_ini: false },
  periode: { awal: null, akhir: null },
  hari_ini: null,
  riwayat: [],
  tanggal: null,
  hari: '',
  lingkup: { semua: false, nama: '', respo: '' },
  ringkasan: { aktif: 0, per_status: [], hadir: 0, sudah_pulang: 0, masih_bekerja: 0, telat: 0, cuti_hari_ini: 0, belum_absen: 0, approval_tertunda: 0 },
  kehadiran_7_hari: [],
  approval: [],
  kelengkapan: [],
  ulang_tahun: { total: 0, list: [] },
  pengingat: { kontrak_30_hari: 0, masuk_90_hari: 0, keluar_90_hari: 0 },
  komposisi: { divisi: [], gender: [], masa_kerja: [] },
  per_cabang: []
})
const dash = reactive(kosong())
const r = computed(() => dash.ringkasan)
// data tampilan pribadi (ringkasan pribadi punya bentuk sendiri: hadir, telat, tanpa_pulang, cuti)
const p = computed(() => ({
  ...dash,
  ringkasan: { hadir: 0, telat: 0, tanpa_pulang: 0, cuti: 0, ...(dash.akses === 'pribadi' ? dash.ringkasan : {}) }
}))

// CSS halaman ini dipasang sendiri (class Windi hanya tersedia dari hasil build)
const STYLE_ID = 'hris-dashboard-hc-style'
const css = `
.hd-page { padding: 8px 20px 32px 8px; color: #0f1b3d; font-size: 14px; }
@media (max-width: 900px) { .hd-page { padding: 8px 12px 24px; } }
.hd-top { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.hd-title { margin: 0; font-size: 21px; font-weight: 700; }
.hd-subtitle { margin: 3px 0 0; color: #66728c; }
.hd-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.hd-chip { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e3e8f0; border-radius: 999px; padding: 6px 12px; font-size: 13px; color: #33415e; }
.hd-date { border: 0; outline: none; font: inherit; color: #1d4ed8; background: transparent; }
.hd-btn { cursor: pointer; color: #1d4ed8; font-weight: 600; }
.hd-btn:disabled { opacity: .6; cursor: wait; }
.hd-alert { margin-bottom: 14px; padding: 10px 14px; border-radius: 10px; background: #fdeeee; border: 1px solid #f6cfcf; color: #a12b2b; }
.hd-grid { display: grid; gap: 16px; }
.hd-kpis { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.hd-row2 { grid-template-columns: 2fr 1fr; }
.hd-row2b { grid-template-columns: 1fr 1fr; }
.hd-row3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.hd-two { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 10px; }
.hd-grid > * { min-width: 0; }
.hd-card { background: #fff; border: 1px solid #e3e8f0; border-radius: 14px; padding: 16px 18px; min-width: 0; }
.hd-h3 { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin: 0 0 12px; font-size: 15px; font-weight: 700; }
.hd-h3 span { font-weight: 400; color: #66728c; font-size: 12px; }
.hd-mt { margin-top: 18px; }
.hd-lbl { color: #66728c; font-size: 13px; }
.hd-val { font-size: 30px; font-weight: 700; margin: 6px 0 4px; line-height: 1.1; }
.hd-of { font-size: 15px; color: #66728c; font-weight: 600; }
.hd-sub, .hd-note { font-size: 12px; color: #66728c; }
.hd-note { margin-top: 8px; }
.hd-bar { height: 6px; background: #e3e8f0; border-radius: 99px; margin-top: 10px; overflow: hidden; }
.hd-bar i { display: block; height: 100%; background: #12915a; border-radius: 99px; }
.hd-c-blue { color: #1d4ed8; } .hd-c-green { color: #12915a; } .hd-c-amber { color: #b86e00; } .hd-c-red { color: #c43232; } .hd-c-violet { color: #6d4ad8; }
.hd-section { margin: 26px 0 12px; font-size: 13px; text-transform: uppercase; letter-spacing: .6px; color: #66728c; font-weight: 700; }
.hd-list { list-style: none; margin: 0; padding: 0; }
.hd-list li { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #e3e8f0; }
.hd-list li:last-child { border-bottom: 0; }
.hd-list .hd-empty { color: #66728c; font-size: 13px; justify-content: center; }
.hd-click { cursor: pointer; }
.hd-click:hover .hd-t { color: #1d4ed8; }
.hd-t { font-weight: 600; }
.hd-d { color: #66728c; font-size: 12px; margin-top: 2px; }
.hd-right { text-align: right; }
.hd-pill { display: inline-block; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 4px 10px; white-space: nowrap; }
.hd-p-amber { background: #fff3dc; color: #b86e00; } .hd-p-red { background: #fde9e9; color: #c43232; }
.hd-p-blue { background: #e8efff; color: #1d4ed8; } .hd-p-green { background: #e3f6ec; color: #12915a; }
.hd-p-violet { background: #efe9ff; color: #6d4ad8; }
.hd-hbar { margin: 8px 0; }
.hd-hbar-h { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; margin-bottom: 4px; }
.hd-track { height: 8px; background: #e3e8f0; border-radius: 99px; overflow: hidden; }
.hd-track i { display: block; height: 100%; background: #1d4ed8; border-radius: 99px; }
.hd-track i.hd-warn { background: #f59e0b; }
.hd-table-wrap { overflow-x: auto; }
.hd-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.hd-table th, .hd-table td { text-align: left; padding: 9px 8px; border-bottom: 1px solid #e3e8f0; white-space: nowrap; }
.hd-table th { color: #66728c; font-weight: 600; font-size: 12px; }
.hd-table .hd-num { text-align: right; }
.hd-row-warn td { color: #c43232; }
.hd-foot { color: #66728c; font-size: 12px; margin-top: 18px; }
.hd-loading { padding: 40px; text-align: center; color: #66728c; }
.hd-hero { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 16px; padding: 22px 24px; border-radius: 16px; color: #fff; background: linear-gradient(90deg, #0c1a3f 0%, #14275f 60%, #1d4ed8 100%); }
.hd-hero-hi { font-size: 15px; opacity: .85; }
.hd-hero-name { margin: 4px 0 12px; font-size: 26px; font-weight: 800; line-height: 1.3; letter-spacing: .3px; }
.hd-hero-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.hd-hero-meta span { font-size: 12px; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); }
.hd-hero-bday { margin-top: 10px; font-weight: 700; }
.hd-hero-date { text-align: right; opacity: .9; }
.hd-hero-day { font-size: 20px; font-weight: 700; }
.hd-today { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.hd-today-box { border: 1px solid #e3e8f0; border-radius: 12px; padding: 14px; }
.hd-today-time { font-size: 30px; font-weight: 700; margin: 4px 0; }
.hd-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.hd-action { cursor: pointer; border: 1px solid #c9d6f5; background: #fff; color: #1d4ed8; font-weight: 600; border-radius: 10px; padding: 9px 14px; font-size: 13px; }
.hd-action-main { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.hd-mini { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
.hd-mini-val { font-size: 26px; font-weight: 700; margin: 4px 0 0; }
.hd-empty-cell { text-align: center !important; color: #66728c; }
@media (max-width: 600px) { .hd-two { grid-template-columns: minmax(0, 1fr); gap: 20px; } .hd-mini { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hd-hero-date { text-align: left; } .hd-hero { padding: 20px; } .hd-hero-name { font-size: 22px; } }
@media (max-width: 1200px) { .hd-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } .hd-row3 { grid-template-columns: 1fr 1fr; } }
@media (max-width: 900px) { .hd-row2, .hd-row2b, .hd-row3 { grid-template-columns: 1fr; } .hd-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
`

onBeforeMount(() => {
  document.title = 'Dashboard HC'
  if (!document.getElementById(STYLE_ID)) {
    const el = document.createElement('style')
    el.id = STYLE_ID
    el.textContent = css
    document.head.appendChild(el)
  }
})

onBeforeUnmount(() => {
  document.getElementById(STYLE_ID)?.remove()
})

// ---------- format
const fmt = new Intl.NumberFormat('id-ID')
function angka(n) {
  return fmt.format(Number(n) || 0)
}
const namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
function labelUltah(u) {
  const s = Number(u.sisa_hari)
  if (s === 0) return 'Hari ini'
  if (s === 1) return 'Besok'
  const [, m, d] = String(u.tanggal).split('-')
  return `${Number(d)} ${namaBulan[Number(m) - 1]}`
}

function jam(t) {
  return t ? String(t).slice(0, 5) : '--:--'
}
const bulanPanjang = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
function tanggalPanjang(t) {
  if (!t) return ''
  const [y, m, d] = String(t).split('-')
  return `${Number(d)} ${bulanPanjang[Number(m) - 1]} ${y}`
}
const hariPendek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
function tanggalPendek(t, denganHari = false) {
  if (!t) return '-'
  const [y, m, d] = String(t).split('-').map(Number)
  const teks = `${d} ${namaBulan[m - 1]}`
  return denganHari ? `${hariPendek[new Date(y, m - 1, d).getDay()]}, ${teks}` : teks
}
const sapaan = computed(() => {
  const h = new Date().getHours()
  return h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam'
})

const labelHari = computed(() => {
  const hariIni = hariIniLokal()
  return dash.tanggal && dash.tanggal !== hariIni ? `${dash.hari}, ${dash.tanggal.split('-').reverse().join('/')}` : 'hari ini'
})
const persenHadir = computed(() => (r.value.aktif ? Math.round((r.value.hadir * 100) / r.value.aktif) : 0))
const teksStatus = computed(() => r.value.per_status.map((s) => `${s.label} ${angka(s.n)}`).join(' · ') || '-')
const maxDivisi = computed(() => Math.max(1, ...dash.komposisi.divisi.map((d) => Number(d.n) || 0)))
function lebarDivisi(n) {
  return Math.max(3, Math.round(((Number(n) || 0) * 100) / maxDivisi.value))
}

// ---------- data grafik (vue-chartkick)
const chartKehadiran = computed(() => [
  { name: 'Hadir', data: dash.kehadiran_7_hari.map((d) => [d.label, d.hadir]) },
  { name: 'Tidak hadir', data: dash.kehadiran_7_hari.map((d) => [d.label, d.tidak_hadir]) }
])
const chartStatus = computed(() => [
  ['Sudah pulang', r.value.sudah_pulang],
  ['Masih bekerja', r.value.masih_bekerja],
  ['Belum absen', r.value.belum_absen]
])
const chartStatusKary = computed(() => r.value.per_status.map((s) => [s.label, Number(s.n)]))
const chartGender = computed(() => dash.komposisi.gender.map((g) => [g.label, Number(g.n)]))
const chartMasa = computed(() => dash.komposisi.masa_kerja.map((m) => [m.label, Number(m.n)]))

// ---------- aksi
function buka(path) {
  if (path) router.push(`${path}?menu_click=true`)
}

function gantiTanggal(v) {
  if (!v) return
  tanggal.value = v
  muat()
}

async function muat() {
  loading.value = true
  errorMsg.value = ''
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_kary/dashboard_hc?tanggal=${tanggal.value}`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const json = await res.json()
    Object.assign(dash, kosong(), json)
  } catch (err) {
    errorMsg.value = 'Gagal memuat data dashboard. Coba muat ulang.'
    console.error('dashboard_hc:', err)
  } finally {
    loading.value = false
  }
}

onMounted(muat)
