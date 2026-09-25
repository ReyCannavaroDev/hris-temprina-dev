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
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/dashboard'
onBeforeMount(async () => {
  document.title = 'Dashboard'
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_respo/main_respo`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })

    if (!res.ok) throw new Error('Gagal fetch m_respo')

    const respoJson = await res.json()
    console.log('Respon dari API:', respoJson)

    localStorage.setItem('respo', JSON.stringify(respoJson.data || respoJson))

  } catch (err) {
    console.error('Error ambil m_respo:', err)
  }
})


const is_superadmin = ref(false)
const beforeLoad = ref(false)
const branchSalary = ref([])
const subSalary = ref([])

const chartData = ref({})
const chartDataSub = ref({})

const totalDivisi = ref(0)
const totalDepartemen = ref(0)
const pegawaiAbsen = ref(0)
const pegawaiMasuk = ref(0)

onMounted(async () => {
  beforeLoad.value = true
  try {
    isRequesting.value = true

    const resMe = await fetch(`${store.server.url_backend}/me`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    const dataMe = await resMe.json()
    is_superadmin.value = dataMe?.user_type ?? 'User'

    const resDash = await fetch(`${store.server.url_backend}/operation/m_subcomp/dashboard`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    const dataDash = await resDash.json()
    console.log('FULL dataDash:', dataDash)

    branchSalary.value = dataDash?.branch_salary || []
    subSalary.value = dataDash.subcomp_salary
    console.log('branchSalary dari API:', subSalary.value)

    if (Array.isArray(branchSalary.value)) {
      chartData.value = Object.fromEntries(
        branchSalary.value.map(item => [item.branch_name, item.total_gaji])
      )
      // console.log('chartData hasil:', chartData.value)
    } 

    if (Array.isArray(subSalary.value)) {
      chartDataSub.value = Object.fromEntries(
        subSalary.value.map(item => [item.subcomp_name, item.total_gaji])
      )
      console.log('chartDataSub hasil:', chartDataSub.value)
    } 

    totalDivisi.value = dataDash?.sub_count ?? 0
    totalDepartemen.value = dataDash?.branch_count ?? 0
    pegawaiAbsen.value = dataDash?.total_absen ?? 0
    pegawaiMasuk.value = dataDash?.total_hadir ?? 0

  } catch (err) {
    console.error('Error onMounted:', err)
  } finally {
    beforeLoad.value = false
  }
})


const openJobs = ref([])
const isRequestingJobs = ref(false)

const fetchLokerData = async () => {
  try {
    isRequestingJobs.value = true
    const params = new URLSearchParams({
      paginate: 100,
      join: true,
      transform: true,
      where: "upper(this.status) in ('OPEN', 'PROGRES', 'PROGRESS', 'CLOSED')"
    })
    
    const response = await fetch(`${store.server.url_backend}/operation/t_loker?${params.toString()}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    
    const res = await response.json()
    if (res && res.data) {
      openJobs.value = res.data
    } else if (res && res.data === undefined && Array.isArray(res)) {
      openJobs.value = res
    }
} catch (error) {
    console.error('Gagal memuat data lowongan kerja:', error)
  } finally {
    isRequestingJobs.value = false
  }
}

const goToDetail = (id) => {
  const tsId = `ts=` + Date.now();
  router.push(`/t_lowongan_kerja/${id}?${tsId}`);
}

onMounted(() => {
  fetchLokerData()
})

