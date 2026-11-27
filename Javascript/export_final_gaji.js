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
  tipe: 'excel',
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
      text: 'Harap Memilih Tipe Export Dahulu!',
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
    window.open(
      `${store.server.url_backend}/public/t_final_gaji/exportGajiCC?${paramsGet}`
    )
    isRequesting.value = false
  }
}

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))