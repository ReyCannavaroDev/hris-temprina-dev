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
.sg-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
.sg-title { font-size: 15px; font-weight: 700; color: #0f172a; }
.sg-title span { font-weight: 400; color: #66728c; font-size: 12px; margin-left: 6px; }
.sg-tools { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.sg-tools select { font: inherit; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 10px; background: #fff; max-width: 100%; }
.sg-seg { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
.sg-seg button { font: inherit; border: 0; background: #fff; padding: 6px 12px; cursor: pointer; }
.sg-seg button.on { background: #1d4ed8; color: #fff; }
.sg-stats { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 14px; }
.sg-chip { border-radius: 999px; padding: 4px 10px; font-size: 12px; background: #eef2ff; color: #3730a3; }
.sg-chip.warn { background: #fef2f2; color: #b91c1c; }
.sg-legend { display: flex; gap: 10px; flex-wrap: wrap; font-size: 12px; color: #66728c; margin-left: auto; }
.sg-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 4px; vertical-align: -1px; }
.sg-chart { display: flex; flex-direction: column; align-items: center; }
.sg-root { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; position: relative; padding-bottom: 22px; }
.sg-root::after { content: ""; position: absolute; left: 50%; bottom: 0; height: 22px; border-left: 2px solid #cbd5e1; }
.sg-root .sg-node { width: 230px; max-width: 100%; text-align: center; border-left-width: 1px; border-top: 4px solid var(--c); }
.sg-lanes { width: 100%; display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px; align-items: start; border-top: 2px solid #cbd5e1; padding-top: 16px; }
.sg-lane { min-width: 0; }
.sg-lane.lebar { grid-column: span 2; }
.sg-sub { display: flex; flex-direction: column; gap: 6px; padding: 8px; border-radius: 12px; background: color-mix(in srgb, var(--s) 7%, #fff); border: 1px solid color-mix(in srgb, var(--s) 25%, #fff); border-left: 4px solid var(--s); }
.sg-sub-h { display: flex; justify-content: space-between; gap: 6px; padding: 0 2px 2px; font-size: 12px; font-weight: 700; color: var(--s); text-transform: uppercase; letter-spacing: .03em; }
.sg-sub-h span { font-weight: 400; color: #66728c; }
.sg-baris.dua { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; align-items: start; }
.sg-kol { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
@media (max-width: 520px) { .sg-lane.lebar { grid-column: auto; } }
.sg-lane-h { background: #f1f5f9; border-radius: 10px; padding: 6px 10px; font-weight: 700; color: #0f172a; display: flex; justify-content: space-between; gap: 6px; margin-bottom: 8px; }
.sg-lane-h.warn { background: #fef2f2; color: #b91c1c; }
.sg-lane-h span { font-weight: 400; color: #66728c; }
.sg-stack { display: flex; flex-direction: column; gap: 6px; }
.sg-node { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid var(--c); border-radius: 10px; padding: 7px 10px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
.sg-node .j { font-weight: 600; font-size: 12.5px; color: #0f172a; }
.sg-node .n { font-size: 12px; color: #334155; margin-top: 2px; }
.sg-node .lv { font-size: 10.5px; color: var(--c); font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
.sg-grp { cursor: pointer; }
.sg-grp .cnt { float: right; background: #f1f5f9; border-radius: 999px; padding: 0 8px; font-size: 11.5px; color: #334155; }
.sg-grp .names { margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e2e8f0; font-size: 12px; color: #334155; line-height: 1.5; }
.sg-lv-row { display: grid; grid-template-columns: 150px 1fr; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eef2f7; }
.sg-lv-row:last-child { border-bottom: 0; }
.sg-lv-name { font-weight: 700; color: var(--c); }
.sg-lv-name span { display: block; font-weight: 400; color: #66728c; font-size: 12px; }
.sg-lv-items { display: flex; flex-wrap: wrap; gap: 6px; }
.sg-pill { border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 8px; background: #fff; font-size: 12px; }
.sg-pill b { font-weight: 600; }
.sg-pill small { color: #66728c; }
.sg-empty { padding: 24px; text-align: center; color: #66728c; }
@media (max-width: 700px) { .sg-legend { margin-left: 0; } .sg-lv-row { grid-template-columns: 1fr; gap: 6px; } }
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
    if (dash.akses === 'hc' && !struktur.kary.length) muatStruktur()
  } catch (err) {
    errorMsg.value = 'Gagal memuat data dashboard. Coba muat ulang.'
    console.error('dashboard_hc:', err)
  } finally {
    loading.value = false
  }
}

// ---------- struktur organisasi per cabang
// Puncak = Direktur dan GM/Wadir (level 5 ke atas), plus Manager/Asmen yang tidak punya divisi.
// Manager/Asmen yang punya divisi tampil paling atas di kolom divisinya, supaya lurus dengan bawahannya.
const diPuncak = (k) => k.lvl >= 5 || (k.lvl === 4 && !k.divisi)
const LEVEL = {
  6: ['Direktur', '#7c3aed'], 5: ['GM / Wadir', '#1d4ed8'], 4: ['Manager / Asmen', '#0891b2'],
  3: ['Kadiv / Wakadiv', '#059669'], 2: ['Karu / Wakaru', '#d97706'], 1: ['Staff', '#64748b']
}
const legendLevel = [6, 5, 4, 3, 2, 1].map((l) => ({ l, nama: LEVEL[l][0], warna: LEVEL[l][1] }))
const struktur = reactive({ cabang: [], m_branch_id: null, kary: [], divisi: [], loading: false, error: '', mode: 'bagan', buka: {} })

function warnaLevel(l) { return (LEVEL[l] || ['', '#94a3b8'])[1] }
function namaLevel(l) { return (LEVEL[l] || ['Belum ada level'])[0] }
// Dalam satu level, jabatan utama didahulukan dari wakil/asisten (Manager lalu Asmen, Kadiv lalu Wakadiv, GM lalu Vice GM)
const POLA_WAKIL = /\b(assistant|asisten|asst|asmen|wakil|vice|wa(kadiv|karu|dir|kasie|kabag))/i
function urutWakil(jabatan) { return POLA_WAKIL.test(jabatan || '') ? 1 : 0 }
// Warna blok sub divisi, bergantian supaya batas antar sub terlihat
const WARNA_SUB = ['#2563eb', '#0d9488', '#9333ea', '#c2410c', '#be185d', '#4d7c0f']
function warnaSub(i) { return WARNA_SUB[i % WARNA_SUB.length] }
function namaRapi(s) { return String(s || '').toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase()) }

// kelompokkan orang dengan jabatan sama; Karu ke atas yang hanya 1-2 orang tetap tampil per orang
function kelompokJabatan(list) {
  const m = new Map()
  list.forEach((k) => {
    const key = (k.lvl || 0) + '|' + (k.jabatan || '(belum ada jabatan)')
    if (!m.has(key)) m.set(key, { key, jabatan: k.jabatan || '(belum ada jabatan)', lvl: k.lvl || 0, orang: [] })
    m.get(key).orang.push(k)
  })
  const hasil = []
  ;[...m.values()].sort((a, b) => b.lvl - a.lvl || urutWakil(a.jabatan) - urutWakil(b.jabatan)).forEach((g) => {
    if (g.orang.length <= 2 && g.lvl >= 2) g.orang.forEach((k, i) => hasil.push({ key: g.key + '|' + i, jabatan: g.jabatan, lvl: g.lvl, orang: [k], tunggal: true }))
    else hasil.push({ ...g, tunggal: false })
  })
  return hasil
}

const sPimpinan = computed(() => {
  const lead = struktur.kary.filter(diPuncak)
  return [...new Set(lead.map((k) => k.lvl))].sort((a, b) => b - a).map((l) => ({ lvl: l, orang: lead.filter((k) => k.lvl === l).sort((a, b) => urutWakil(a.jabatan) - urutWakil(b.jabatan)) }))
})
// Susun isi satu divisi jadi baris. Pejabat per level (Manager/Asmen, Kadiv/Wakadiv, Karu/Wakaru) yang lebih dari 1 orang
// dibuat 2 kolom: kiri jabatan utama, kanan wakil/asisten. Kalau semuanya utama (mis. 2 Kadiv), dibagi rata kiri-kanan.
function susunBaris(list) {
  const baris = []
  const grup = kelompokJabatan(list)
  const sudah = new Set()
  grup.forEach((g) => {
    if (sudah.has(g.lvl)) return
    const satuLevel = g.tunggal ? grup.filter((x) => x.tunggal && x.lvl === g.lvl) : []
    if (g.tunggal && satuLevel.length > 1) {
      sudah.add(g.lvl)
      let kiri = satuLevel.filter((x) => !urutWakil(x.jabatan))
      let kanan = satuLevel.filter((x) => urutWakil(x.jabatan))
      if (!kanan.length) { kanan = kiri.filter((_, i) => i % 2); kiri = kiri.filter((_, i) => !(i % 2)) }
      else if (!kiri.length) { kiri = kanan.filter((_, i) => !(i % 2)); kanan = kanan.filter((_, i) => i % 2) }
      baris.push({ key: 'lv' + g.lvl, dua: true, kiri, kanan })
    } else {
      baris.push({ key: g.key, dua: false, kiri: [g], kanan: [] })
    }
  })
  return baris
}

// Satu kolom per divisi induk (m_divisi.parent_id, ditelusuri sampai paling atas). Orang di divisi induk dan tiap sub divisi
// tampil sebagai bagian di dalam kolom yang sama (sub divisi diberi judul kecil). Bagian dengan pejabat tertinggi tampil dulu.
const sDivisi = computed(() => {
  const info = new Map(struktur.divisi.map((d) => [Number(d.id), d]))
  const akar = (id) => {
    let cur = info.get(Number(id)); let jaga = 0
    while (cur && cur.parent_id && info.has(Number(cur.parent_id)) && jaga++ < 10) cur = info.get(Number(cur.parent_id))
    return cur
  }
  const kolom = new Map()
  struktur.kary.filter((k) => !diPuncak(k)).forEach((k) => {
    const r = k.divisi_id ? akar(k.divisi_id) : null
    const kunci = r ? 'd' + r.id : (k.divisi ? 'n' + k.divisi : 'kosong')
    if (!kolom.has(kunci)) kolom.set(kunci, { nama: r ? r.nama : (k.divisi || 'Belum ada divisi'), kosong: !k.divisi, induk: r ? Number(r.id) : null, orang: [] })
    kolom.get(kunci).orang.push(k)
  })
  const top = (list) => Math.max(...list.map((k) => k.lvl || 0))
  return [...kolom.values()]
    .sort((a, b) => a.kosong - b.kosong || top(b.orang) - top(a.orang) || b.orang.length - a.orang.length)
    .map((c) => {
      const bagian = new Map()
      c.orang.forEach((k) => {
        const sub = c.induk && Number(k.divisi_id) !== c.induk ? Number(k.divisi_id) : 0
        if (!bagian.has(sub)) bagian.set(sub, { sub, nama: sub ? (info.get(sub)?.nama || k.divisi) : '', orang: [] })
        bagian.get(sub).orang.push(k)
      })
      const daftar = [...bagian.values()]
        .sort((a, b) => top(b.orang) - top(a.orang) || (a.sub ? 1 : 0) - (b.sub ? 1 : 0) || b.orang.length - a.orang.length)
        .map((b) => ({ key: c.nama + '|' + b.sub, sub: b.sub, nama: b.nama, jumlah: b.orang.length, baris: susunBaris(b.orang) }))
      return { nama: c.nama, jumlah: c.orang.length, kosong: c.kosong, bagian: daftar, lebar: daftar.some((b) => b.baris.some((x) => x.dua)) }
    })
})
const sLevel = computed(() => {
  const lvs = [...new Set(struktur.kary.map((k) => k.lvl || 0))].sort((a, b) => b - a)
  return lvs.map((l) => {
    const list = struktur.kary.filter((k) => (k.lvl || 0) === l)
    return { lvl: l, jumlah: list.length, grup: kelompokJabatan(list) }
  })
})
const sInfo = computed(() => {
  const k = struktur.kary
  const tanpaJabatan = k.filter((x) => !x.jabatan).length
  return {
    divisi: new Set(k.filter((x) => x.divisi && !diPuncak(x)).map((x) => x.divisi)).size,
    pimpinan: k.filter((x) => x.lvl >= 4).length,
    kadivKaru: k.filter((x) => x.lvl === 2 || x.lvl === 3).length,
    tanpaJabatan,
    tanpaLevel: k.filter((x) => !x.lvl).length - tanpaJabatan,
    tanpaDivisi: k.filter((x) => !x.divisi && (x.lvl || 0) < 4).length
  }
})
const sNamaCabang = computed(() => (struktur.cabang.find((c) => Number(c.id) === Number(struktur.m_branch_id)) || {}).name || '-')

function toggleGrup(key) { struktur.buka[key] = !struktur.buka[key] }

async function muatStruktur(id) {
  struktur.loading = true
  struktur.error = ''
  try {
    const q = id ? `?m_branch_id=${id}` : ''
    const res = await fetch(`${store.server.url_backend}/operation/m_kary/struktur_cabang${q}`, {
      headers: { 'Content-Type': 'application/json', Authorization: `${store.user.token_type} ${store.user.token}` }
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const json = await res.json()
    struktur.cabang = json.cabang || []
    struktur.m_branch_id = json.m_branch_id
    struktur.kary = json.kary || []
    struktur.divisi = json.divisi || []
    struktur.buka = {}
  } catch (err) {
    struktur.error = 'Gagal memuat struktur organisasi.'
    console.error('struktur_cabang:', err)
  } finally {
    struktur.loading = false
  }
}

onMounted(muat)
