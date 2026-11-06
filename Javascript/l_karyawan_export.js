//   javascript
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

const file_report = ref('')

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Export Karyawan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

//  @else----------------------- LANDING
let initialValues = {}
const changedValues = []
const checkedState = ref()
let thisMonth = new Date().toISOString().split('T')[0]
let tempYear = thisMonth.split('-')[0]
let tempMonth = thisMonth.split('-')[1]
const openDateSelected = ref(null)
const dataByDate = ref([])
const dataByDateDetail = ref([])

const values = reactive({
  tipe: 'excel',
  tipe_report: 'Laporan Absensi Karyawan Rekap',
  periode: tempYear + '-' + tempMonth,
})

const resetValuesPeriode = () => {
  values.periode = tempYear + '-' + tempMonth
}

const buildWhereKary = () => {
  const conditions = []

  if (values.m_divisi_id) {
    conditions.push(`this.m_divisi_id = ${values.m_divisi_id}`)
  }
  if (values.m_branch_id) {
    conditions.push(`this.m_branch_id = ${values.m_branch_id}`)
  }
  if (values.m_subcomp_id) {
    conditions.push(`this.m_subcomp_id = ${values.m_subcomp_id}`)
  }
  if (values.m_comp_id) {
    conditions.push(`this.m_comp_id = ${values.m_comp_id}`)
  }

  return conditions.length ? conditions.join(' AND ') : null
}


const onGenerate = async () => {

  if (values.tipe_report == 'Laporan Absensi Karyawan Rekap') {
    file_report.value = 'report_karyawan_absensi_rekap'
  }
  if (values.tipe_report == 'Laporan Absensi Karyawan Detail') {
    file_report.value = 'report_karyawan_absensi_detail'
  }
  if (values.tipe_report == 'Laporan Sisa Cuti Karyawan') {
    file_report.value = 'report_sisa_cuti_karyawan'
  }

  if (values.tipe === null || values.tipe_report === null) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Tipe Export dan Tipe Report Dahulu!',
    })
    return
  }
  if (values.tipe_report === 'Laporan Absensi Karyawan Detail' && (values.periode === null)) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Periode Dahulu!',
    })
    return
  }
  const tempGet = []
  isRequesting.value = true
  // if (values.tipe) {
  //   if (values.tipe?.toLowerCase() === 'excel') {
  //     tempGet.push(`export=xls`)
  //   } else if (values.tipe?.toLowerCase() === 'pdf') {
  //     tempGet.push(`export=pdf`)
  //   }
  // }

  // if (values.periode_awal) {
  //   const [day, month, year] = values.periode_awal.split('/')
  //   const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
  //   tempGet.push(`periode_awal=${formattedDate}`)
  // }

  // if (values.periode_akhir) {
  //   const [day, month, year] = values.periode_akhir.split('/')
  //   const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
  //   tempGet.push(`periode_akhir=${formattedDate}`)
  // }

  // if (values.tipe_report) {
  //   tempGet.push(`tipe_report=${values.tipe_report}`)
  // }
  if (values.m_kary_id) {
    tempGet.push(`m_kary_id=${values.m_kary_id}`)
  }
  if (values.m_comp_id) {
    tempGet.push(`m_comp_id=${values.m_comp_id}`)
  }
  if (values.m_subcomp_id) {
    tempGet.push(`m_subcomp_id=${values.m_subcomp_id}`)
  }
  if (values.m_divisi_id) {
    tempGet.push(`m_divisi_id=${values.m_divisi_id}`)
  }
  if (values.m_branch_id) {
    tempGet.push(`m_branch_id=${values.m_branch_id}`)
  }

  const paramsGet = tempGet.join("&")
  // if (values.tipe?.toLowerCase() !== 'html') {
    exportHtml.value = false
    window.open(`${store.server.url_backend}//public/m_kary/exportKary` + '?' + paramsGet)
  // } else {
  //   await fetch(`${store.server.url_backend}/web/${file_report.value}` + '?' + paramsGet, {
  //     headers: {
  //       'Content-Type': 'html',
  //     },
  //   })
  //     .then(response => response.text())
  //     .then(html => {
  //       exportHtml.value = true
  //       const tempDiv = document.createElement('div')
  //       tempDiv.innerHTML = html
  //       const targetDiv = document.getElementById('exportTable')
  //       targetDiv.innerHTML = ''
  //       targetDiv.appendChild(tempDiv)
  //     })
  //     .catch(error => {
  //       swal.fire({
  //         icon: 'error',
  //         text: error,
  //       })
  //     })
  // }

  isRequesting.value = false
}

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))