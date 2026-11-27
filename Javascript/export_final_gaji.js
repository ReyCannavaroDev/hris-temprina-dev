import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const exportHtml = ref(false)
const formErrors = ref({})
const activeTabIndex = ref(0)
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Laporan Slip Gaji'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

//  @else----------------------- LANDING
let initialValues = {}
const changedValues = []
const checkedState = ref()

// const values = reactive({
//   tipe: 'HTML',
//   m_kary_id: store.user.data.m_kary_id,
//   m_kary_nama_lengkap: store.user.data.name,
//   m_kary: {
//     nama_lengkap: store.user.data.name
//   }
// })
const values = reactive({
  tipe: 'Excel',
  tipe_template: 'Cost Center',
  f_id: null,
  m_branch_id: null,
  m_kary_id: store.user?.data?.m_kary_id || null,
  m_kary: {
    nama_lengkap: store.user?.data?.name || ''
  }
})

const onGenerate = async () => {
  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Format Output Dahulu!',
    })
    return
  }
  if (!values.f_id || (Array.isArray(values.f_id) && values.f_id.length === 0)) {
    swal.fire({
      icon: 'warning',
      text: 'Harap memilih minimal satu periode final gaji terlebih dahulu!',
    })
    return
  }

  const tempGet = []
  isRequesting.value = true

  const finalIds = Array.isArray(values.f_id) ? values.f_id.join(',') : values.f_id
  tempGet.push(`f_id=${finalIds}`)

  if (values.m_branch_id) {
    tempGet.push(`m_branch_id=${values.m_branch_id}`)
  }
  if (values.tipe_template) {
    tempGet.push(`tipe_template=${encodeURIComponent(values.tipe_template)}`)
  }

  const paramsGet = tempGet.join("&")

  if (values.tipe?.toLowerCase() === 'html') {
    try {
      const response = await fetch(`${store.server.url_backend}/public/t_final_gaji/exportGajiHtml?${paramsGet}`, {
        headers: {
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (!response.ok) throw new Error("Gagal mengambil tampilan laporan HTML")
      const htmlText = await response.text()
      exportHtml.value = true
      const targetDiv = document.getElementById('exportTable')
      if (targetDiv) {
        targetDiv.innerHTML = htmlText
      }
    } catch (err) {
      swal.fire({
        icon: 'error',
        text: err?.message || 'Terjadi kesalahan saat memuat laporan'
      })
    } finally {
      isRequesting.value = false
    }
  } else {
    // Excel export
    exportHtml.value = false
    const endpointMethod = values.tipe_template === 'Detail Karyawan' ? 'exportGaji' : 'exportGajiCC'
    window.open(
      `${store.server.url_backend}/public/t_final_gaji/${endpointMethod}?${paramsGet}`
    )
    isRequesting.value = false
  }
}

const onPrintReport = () => {
  const printContent = document.getElementById('exportTable')
  if (!printContent) return
  const win = window.open('', '_blank')
  win.document.write(`
    <html>
      <head>
        <title>Laporan Final Gaji</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          table { width: 100%; border-collapse: collapse; font-size: 10px; }
          th, td { border: 1px solid #333; padding: 4px 6px; }
          th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
          @media print {
            body { padding: 0; }
          }
        </style>
      </head>
      <body>
        ${printContent.innerHTML}
      </body>
    </html>
  `)
  win.document.close()
  win.focus()
  setTimeout(() => {
    win.print()
  }, 500)
}

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))