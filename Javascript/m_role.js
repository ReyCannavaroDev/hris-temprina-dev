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
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const detail = ref(null)

// ------------------------------ PERSIAPAN
const endpointApi = '/m_role'
onBeforeMount(() => {
  document.title = 'Master Role'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
let trx_dtl = ref([])

const onDetailAdd = (rows) => {
  const arr = trx_dtl.value
  rows.forEach(r => {
    const row = { ...r }
    row.m_menu_id = r.id
    row.can_read = true
    row.can_create = true
    row.can_update = true
    row.can_delete = true
    row.can_verify = true
    arr.push(row)
  })
  trx_dtl.value = [...arr]
}


function deleteAll() {
  detail.value.key++
}

const values = reactive({
  is_superadmin: false,
  is_active: true
})


onBeforeMount(async () => {
  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const url = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { join: false, transform: false, simplest: true }
      const query = new URLSearchParams(params)
      const res = await fetch(url + '?' + query, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })

      if (!res.ok) throw new Error("Failed when trying to read data")

      const json = await res.json()
      initialValues = json.data
      initialValues.username = initialValues['default_user.username']
      initialValues.email = initialValues['default_user.email']

      const base = `${store.server.url_backend}/operation/m_role_det?where=m_role_id=${editedId ?? 0}`

      const get = (page = 1) => fetch(`${base}&page=${page}`, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(r => r.json())

      const first = await get(1)
      const total = first.total
      const perPage = first.per_page
      const totalPages = Math.ceil(total / perPage)

      const pages = totalPages > 1
        ? await Promise.all(Array.from({ length: totalPages - 1 }, (_, i) => get(i + 2)))
        : []

      const merged = [...first.data, ...pages.flatMap(j => j.data)]
      merged.forEach(d => d.menu = d['m_menu.menu'])

      trx_dtl.value = merged
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

const onDeleteRow = (app) => {
  if (!app?.params) return

  const rowIndex = app.params.node.rowIndex

  swal.fire({
    icon: 'warning',
    showDenyButton: true,
    text: `Hapus Baris ${rowIndex + 1}?`,
  }).then(res => {
    if (res.isConfirmed) {
      app.params.api.applyTransaction({
        remove: [app.params.node.data]
      })

      if (Array.isArray(trx_dtl)) {
        trx_dtl.splice(rowIndex, 1)
      } else {
        trx_dtl.value.splice(rowIndex, 1)
      }
    }
  })
}



function onBack() {
  let isChanged = false
  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break;
    }
  }

  if (!isChanged) {
    router.replace('/' + modulPath)
    return
  }

  swal.fire({
    icon: 'warning',
    text: 'Buang semua perubahan dan kembali ke list data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      router.replace('/' + modulPath)
    }
  })
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset this form data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

function clearAll() {
  trx_dtl.value = []
}

function buildPayload() {
  return {
    name: values.name,
    is_active: values.is_active ? 1 : 0,
    is_superadmin: values.is_superadmin ? 1 : 0,
    m_role_det: trx_dtl.value.map(d => ({
      m_menu_id: d.m_menu_id,
      can_read: d.can_read ? 1 : 0,
      can_create: d.can_create ? 1 : 0,
      can_update: d.can_update ? 1 : 0,
      can_delete: d.can_delete ? 1 : 0,
      can_verify: d.can_verify ? 1 : 0,
    }))
  }
}

async function onSave() {
  values.m_role_det = trx_dtl.value
  swal.fire({
    icon: 'warning',
    text: 'Save data?',
    showDenyButton: true
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
        const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
        isRequesting.value = true
        const payload = buildPayload()
        const res = await fetch(dataURL, {
          method: isCreating ? 'POST' : 'PUT',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify(payload)
        })
        if (!res.ok) {
          if ([400, 422].includes(res.status)) {
            const responseJson = await res.json()
            formErrors.value = responseJson.errors || {}
            throw new Error(responseJson.errors || responseJson.message || "Failed when trying to post data")
          } else {
            throw new Error("Failed when trying to post data")
          }
        }
        router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
      } catch (err) {
        console.log("ERR ", err)
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


//  @else----------------------- LANDING
const activeBtn = ref()
function filterShowData(params, noBtn) {

  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }
  
  if (activeBtn.value == null) {
    // clear params filter
    landing.api.params.where = null
  } else if (params) {
    landing.api.params.where = `this.is_active=true`
  } else {
    landing.api.params.where = `this.is_active=false`
  }

  apiTable.value.reload()
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => (currentMenu?.can_delete),
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
              if (!res.ok) {
                const resultJson = await res.json()
                throw (resultJson.message || "Failed when trying to remove data")
              }
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
      show: (row) => (currentMenu?.can_read),
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) =>
        currentMenu?.can_update === true,
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => currentMenu?.can_create === true ,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
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
      simplest: true
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
    headerName: 'Kode',
    field: 'code',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Nama',
    field: 'name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Super Admin',
    field: 'is_superadmin',
    filter: true,
    // resizable: true,
    // valueGetter: (p) => p.node.data['status'].toLowerCase()==='active'? 'Aktif':'Tidak Aktif',
    sortable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === true
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Yes</span>`
        : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">No</span>`
    }
  },
  {
    headerName: 'Status',
    field: 'is_active',
    filter: true,
    // resizable: true,
    // valueGetter: (p) => p.node.data['status'].toLowerCase()==='active'? 'Aktif':'Tidak Aktif',
    sortable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === true
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
        : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
    }
  },
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