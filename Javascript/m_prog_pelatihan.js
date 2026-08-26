import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watch, watchEffect, onActivated } from 'vue'

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
const readValue = ref(true)
const adjKary = ref(route.query.action?.toLowerCase() === 'adjusment' ? true : false)

// ------------------------------ PERSIAPAN
const endpointApi = '/m_prog_pelatihan'
onBeforeMount(() => {
  document.title = 'Program Pelatihan'
})


//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

let initialValues = {}
const changedValues = []

const values = reactive({
  date: new Date().toLocaleDateString('en-GB'),
})


function changeFormatDate(dateInput) {
  function toYmdHis(dateInput) {
    const d = new Date(dateInput)
    const pad = n => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
  }
}

const arrDivisi = ref([])
const arrLevel = ref([])

const addLevel = (selectedRows) => {
  if (!selectedRows || selectedRows.length === 0) {
    values.m_prog_pelatihan_d_level = []
    values.tampilLevel = []
    arrLevel.value = []
    return
  }

  const existingIds = arrLevel.value.map(item => item.id)

  selectedRows.forEach(row => {
    if (!existingIds.includes(row.id)) {
      arrLevel.value.push({ ...row })
    }
  })

  values.tampilLevel = arrLevel.value.map(item => item.id)

  values.m_prog_pelatihan_d_level = arrLevel.value.map(item => ({
    m_prog_pelatihan_id: 1,
    m_level_posisi_id: item.id,
    creator_id: store.user.data.id,
    last_editor_id: store.user.data.id
  }))
}

const addDivisi = (selectedRows) => {
  if (!selectedRows || selectedRows.length === 0) {
    values.m_prog_pelatihan_d_divisi = []
    values.tampilDivisi = []
    arrDivisi.value = []
    return
  }

  const existingIds = arrDivisi.value.map(item => item.id)

  selectedRows.forEach(row => {
    if (!existingIds.includes(row.id)) {
      arrDivisi.value.push({ ...row })
    }
  })

  values.tampilDivisi = arrDivisi.value.map(item => item.id)

  values.m_prog_pelatihan_d_divisi = arrDivisi.value.map(item => ({
    m_prog_pelatihan_id: 1,
    m_divisi_id: item.id,
    creator_id: store.user.data.id,
    last_editor_id: store.user.data.id
  }))
}


const removeDivisi = (index) => {
  arrDivisi.values.splice(index, 1)
}

const removeLevel = (index) => {
  arrLevel.values.splice(index, 1)
}


onBeforeMount(async () => {
  if (isRead && currentMenu?.can_read) {
    try {
      isRequesting.value = true
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      const params = { join: true, transform: false }
      const fixedParams = new URLSearchParams(params)
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      initialValues = resultJson.data
      initialValues.tampilDivisi = (initialValues.m_prog_pelatihan_d_divisi || []).map(item => typeof item === 'object' ? (item['m_divisi.id'] || item.m_divisi_id || item.id) : item)
      initialValues.tampilLevel = (initialValues.m_prog_pelatihan_d_level || []).map(item => {
        if (typeof item === 'object') {
          return Number(item.m_level_posisi_id || item['m_level_posisi.id'])
        }
        return Number(item)
      }).filter(v => Number.isInteger(v) && v > 0)
      
      initialValues.mont = initialValues.month.slice(0, 7)
      initialValues.is_active = initialValues.is_active ? 1 : 0
      
      console.log('--- DEBUG DETAIL LEVEL RAW ---', resultJson.data.m_prog_pelatihan_d_level)
      
      const rawLevels = Array.isArray(resultJson.data.m_prog_pelatihan_d_level) ? resultJson.data.m_prog_pelatihan_d_level : []
      initialValues.m_prog_pelatihan_d_level = rawLevels.map(item => {
          if (typeof item === 'object') {
            return Number(item.m_level_posisi_id || item['m_level_posisi.id'])
          }
          return Number(item)
        }).filter(v => Number.isInteger(v) && v > 0)

    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back()
      })
    }
    isRequesting.value = false
  }
  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    const isCreating = ['Create', 'Copy', 'Tambah', 'Adjusment'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    values.is_active = values.is_active ? 1 : 0
    values.kode = '1'
    values.month = `${values.mont}-01`
    const levelArr = Array.isArray(values.m_prog_pelatihan_d_level) ? values.m_prog_pelatihan_d_level : []
    values.m_prog_pelatihan_d_level = levelArr
      .map(id => {
        const levelId = typeof id === 'object' ? (id.m_level_posisi_id || id['m_level_posisi.id']) : id
        return Number(levelId)
      })
      .filter(v => !isNaN(v) && v > 0)
      .map(id => ({
        m_prog_pelatihan_id: 1,
        m_level_posisi_id: id,
        creator_id: store.user?.data?.id
      }))

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    })
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}
        throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
  isRequesting.value = false
}

//  @else----------------------- LANDING

const activeBtn = ref()

function filterShowData(params,noBtn){
  if(activeBtn.value === noBtn){
    activeBtn.value = null
  }else{
    activeBtn.value = noBtn
  }
  
  if(activeBtn.value == null){
    // clear params filter
    landing.api.params.where = null
  }else if(params){
    landing.api.params.where = `this.is_active=true` 
  }else{
    landing.api.params.where = `this.is_active=false`
  }

  apiTable.value.reload()
}

let data = reactive({})
onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = JSON.parse(localStorage.getItem('respo'))
    data.respo_id = respoValues.id
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
  }

  if (data.respo_id) {
    const params = new URLSearchParams({
      path: route.path,
      respo_id: data.respo_id
    })
    const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`

    try {
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      const result = await response.json()
      console.log('x', result)
      data.can_read = result.can_read
      data.can_create = result.can_create
      data.can_delete = result.can_delete
      data.can_update = result.can_update
      data.rows = result.data
    } catch (err) {
      console.error(err)
    }
  }
})

// LANDING LAMA 
const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      // show: (row) =>row.status?.toUpperCase() === 'DRAFT',
      title: "Hapus",
      show: (row) => data.can_delete,
      // show: () => store.user.data.username==='developer',
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok) throw new Error("Failed when trying to remove data")
              apiTable.value.reload()
              // const resultJson = await res.json()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                text: err
              })
            }
            isRequesting.value = false
          }
        })
      }
    },
    {
      icon: 'eye',
      title: "Read",
      class: 'bg-green-600 text-light-100',
      show: (row) => data.can_read,
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => data.can_update,
      // show: (row) => row.status?.toUpperCase() === 'DRAFT' || row.status?.toUpperCase() === 'REVISED',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId);
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_create,
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    },
  ],
  api: {
    // url: `${store.server.url_backend}/operation${endpointApi}`,
    url: currentMenu?.can_read
      ? `${store.server.url_backend}/operation${endpointApi}`
      : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      join: true,
      searchfield: 'this.kode, this.tema_pelatihan, this.sasaran',
    },
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [{
    headerName: 'No',
    valueGetter: (params) => params.node.rowIndex + 1,
    width: 60,
    sortable: true,
    resizable: true,
    filter: true,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'tema_pelatihan',
    headerName: 'Tema Pelatihan',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'sasaran',
    headerName: 'Sasaran',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'jumlah_peserta',
    headerName: 'Jumlah Peserta',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'total_budget',
    headerName: 'Budget',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'is_active',
    headerName: 'Status',
    filter: true,
    sortable: true,
    flex: 1,
    resizable: true,
    filter: 'ColFilter',
    cellClass: ['border-r', '!border-gray-200', 'justify-left'],
    cellRenderer: ({ value }) => {
      const statusText = value ? 'Active' : 'Inactive'
      const colorClass = value ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'
      return `<span class="${colorClass}">${statusText}</span>`
    }
  }
  ]
})
onActivated(() => {
  //  reload table api landing
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})
//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))