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
  const tsId = `ts=`+(Date.parse(new Date()))

  // ------------------------------ PERSIAPAN
  onBeforeMount(()=>{
    document.title = 'Laporan Slip Gaji'
  })

  //  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
  
  //  @else----------------------- LANDING
  let initialValues = {}
  const changedValues = []
  const checkedState = ref()

  const values = reactive({
    tipe: 'MANDIRI'
  })

const onGenerate = async () => {
  console.log('token', store.user.token)
  const token = store.user.token

  if (!values.tipe) {
    swal.fire({
      icon: 'error',
      text: 'Harap Memilih Tipe Export Dahulu!',
    })
    return
  }

  const tempGet = []
  isRequesting.value = true

  if (values.periode_from) {
    let [tempDay, tempMonth, tempYear] = values.periode_from.split('/')
    tempGet.push(`periode_from=${tempYear}-${tempMonth}-${tempDay}`)
  }
  if (values.periode_to) {
    let [tempDay2, tempMonth2, tempYear2] = values.periode_to.split('/')
    tempGet.push(`periode_to=${tempYear2}-${tempMonth2}-${tempDay2}`)
  }
  if (values.f_id) {
    tempGet.push(`f_id=${values.f_id}`)
  }
  if (values.m_kary_id) {
    tempGet.push(`m_kary_id=${values.m_kary_id}`)
  }
  if (values.m_branch_id) {
    tempGet.push(`m_branch_id=${values.m_branch_id}`)
  }

  const paramsGet = tempGet.join("&")
  const url =
    values.tipe?.toLowerCase() === 'bni'
      ? `${store.server.url_backend}/operation/t_final_gaji/exportBNI?${paramsGet}`
      : `${store.server.url_backend}/operation/t_final_gaji/exportMandiri?${paramsGet}`

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'text/csv',
      },
    })
    if (!response.ok) throw new Error('Gagal download file')

    const blob = await response.blob()
    const urlBlob = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = urlBlob
    a.download = `export_${values.tipe}_${Date.now()}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(urlBlob)
  } catch (error) {
    swal.fire({
      icon: 'error',
      text: error.message,
    })
  }

  isRequesting.value = false
}




  //  @endif -------------------------------------------------END
  watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))