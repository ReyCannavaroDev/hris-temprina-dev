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

onBeforeMount(() => {
  document.title = 'Laporan Klaim Rawat Inap'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

//  @else----------------------- LANDING
let initialValues = {}
const changedValues = []

const values = reactive({
  tipe: 'HTML',
  filter_type: 'transaksi',
  m_kary_id: null,
  nomor: null,
  periode_awal: null,
  periode_akhir: null
})

const resetFilters = () => {
  values.m_kary_id = null
  values.nomor = null
  values.periode_awal = null
  values.periode_akhir = null
  exportHtml.value = false
  const targetDiv = document.getElementById('exportTable')
  if (targetDiv) {
    targetDiv.innerHTML = ''
  }
}

const onGenerate = async () => {
  if (values.tipe === null) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Tipe Export Dahulu!',
    })
    return
  }

  const tempGet = []
  isRequesting.value = true

  // Export Type parameter
  if (values.tipe) {
    if (values.tipe.toLowerCase() === 'excel') {
      tempGet.push(`export=xls`)
    } else if (values.tipe.toLowerCase() === 'pdf') {
      tempGet.push(`export=pdf`)
    }
  }

  // Filter Type parameter
  tempGet.push(`filter_type=${values.filter_type}`)

  // Apply conditional parameters based on Filter Type
  if (values.filter_type === 'transaksi') {
    if (values.m_kary_id) {
      tempGet.push(`m_kary_id=${values.m_kary_id}`)
    }
    if (values.nomor) {
      tempGet.push(`nomor=${values.nomor}`)
    }
  } else if (values.filter_type === 'periode') {
    if (values.periode_awal && values.periode_akhir) {
      tempGet.push(`periode_awal=${values.periode_awal}`)
      tempGet.push(`periode_akhir=${values.periode_akhir}`)
    } else {
      swal.fire({
        icon: 'warning',
        text: 'Harap Memilih Tanggal Awal dan Tanggal Akhir Terlebih Dahulu!',
      })
      isRequesting.value = false
      return
    }
  }

  const paramsGet = tempGet.join("&")

  if (values.tipe.toLowerCase() !== 'html') {
    exportHtml.value = false
    window.open(`${store.server.url_backend}/web/report_klaim_ranap` + '?' + paramsGet)
  } else {
    try {
      const response = await fetch(`${store.server.url_backend}/web/report_klaim_ranap` + '?' + paramsGet, {
        headers: {
          'Content-Type': 'html',
        },
      })
      if (!response.ok) {
        throw new Error('Gagal mengambil laporan klaim rawat inap.')
      }
      const html = await response.text()
      exportHtml.value = true

      const tempDiv = document.createElement('div')
      tempDiv.innerHTML = html
      const targetDiv = document.getElementById('exportTable')
      if (targetDiv) {
        targetDiv.innerHTML = ''
        targetDiv.appendChild(tempDiv)
      }
    } catch (error) {
      swal.fire({
        icon: 'error',
        text: error.message || error,
      })
    }
  }

  isRequesting.value = false
}

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
