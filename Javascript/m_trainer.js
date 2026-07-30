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
const readValue = ref(true)
const adjKary = ref(route.query.action?.toLowerCase() === 'adjusment' ? true : false)

// ------------------------------ PERSIAPAN
const endpointApi = '/m_trainer'
onBeforeMount(() => {
  document.title = 'Laporan Kerja'
})


//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

let initialValues = {}
let tempInfo = {}
const changedValues = []
const informasiCuti = reactive({})

const values = reactive({
  is_active : 1,
  date: new Date().toLocaleDateString('en-GB'),
})

function changeFormatDate(dateInput) {
  function toYmdHis(dateInput) {
    const d = new Date(dateInput)
    const pad = n => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
  }
}

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if(route.query.view_gaji_final){
    router.replace('/t_info_gaji')
  }else{
    router.replace('/' + modulPath)
  }
  return
}


onBeforeMount(async () => {
  console.log(adjKary.value)
  //  READ DATA

  if (isRead && currentMenu?.can_read) {
    try {
      isRequesting.value = true
      const editedId = route.params.id
      const dataURL = adjKary.value === true ? `${store.server.url_backend}/operation/m_kary/${editedId}` : `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      const params = { join: true, transform: false, ...(adjKary.value === true && { detail: true }) }
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
      initialValues.is_active = initialValues ? 1 : 0
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

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    const isCreating = ['Create', 'Copy', 'Tambah', 'Adjusment'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    values.is_active = values.is_active ? 1 : 0
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
      console.log('x',result)
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
      show: () => data.can_delete,
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
      show: () => data.can_read,
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: () => data.can_update,
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
      ?`${store.server.url_backend}/operation${endpointApi}`
      : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: false,
      join: false,
      searchfield: 'this.kode, this.nama_trainer, this.alamat, this.no_hp, this.cp',
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
    cellClass: ['justify-left', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'nama_trainer',
    headerName: 'Nama Trainer',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'tipe_trainer',
    headerName: 'Tipe Trainer',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'jenis_training',
    headerName: 'Jenis Training',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  // {
  //   field: 'alamat',
  //   headerName: 'Alamat',
  //   filter: true,
  //   sortable: true,
  //   flex: 1,
  //   filter: 'ColFilter',
  //   resizable: true,
  //   cellClass: ['border-r', '!border-gray-200', 'justify-left']
  // },
  {
    field: 'no_hp',
    headerName: 'No. Telepon/Fak',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  // {
  //   field: 'cp',
  //   headerName: 'Contact Person',
  //   filter: true,
  //   sortable: true,
  //   flex: 1,
  //   filter: 'ColFilter',
  //   resizable: true,
  //   cellClass: ['border-r', '!border-gray-200', 'justify-left']
  // },
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
      const statusText = value ? 'Aktif' : 'Non Aktif'
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