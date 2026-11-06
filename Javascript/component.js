import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')
const apiTable = ref(null)

const values = reactive({})
let detailArr = ref([])

onBeforeMount(async () => {
      const dataURL = `${store.server.url_backend}/operation/m_menu`

      const params = { join: true, transform: false }
      const fixedParams = new URLSearchParams(params)

      // header
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      const initialValues = resultJson.data
      console.log(initialValues)
      initialValues.forEach((d)=>{
        detailArr.value.push(d)
      })
      detailArr.value = resultJsonDet?.data ?? []
    

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
}
)

function clearAll(){
  detailArr.value = []
}

function onDetailAdd(e){
  console.log(e)
}
function addA(){
  detailArr.value.push({
    menu: values.username,
    project: 'HRIS 1.0'
  })
}

const landing = reactive({
  //  ACTIONS sesuai priviledge user dan data
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='trial',
      click(row) {
        router.push(`${route.path}/${row.id}?ts=`+(Date.parse(new Date())))
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&ts=`+(Date.parse(new Date())))
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&ts=`+(Date.parse(new Date())))
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation/m_menu`,
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
    resizable: false,
    filter: true,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'menu',
    headerName: 'Nama Menu',
    filter: true,
    sortable: true,
     filter: 'ColFilter',
    resizable: false,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center', 'capitalize']
  },
  {
    field: 'path',
    headerName: 'Path Menu',
    filter: true,
    sortable: true,
     filter: 'ColFilter',
    resizable: false,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center', 'capitalize']
  },
  {
    field: 'project',
    headerName: 'Nama Project',
    filter: true,
    sortable: true,
     filter: 'ColFilter',
    resizable: false,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center', 'capitalize']
  },
  {
    field: 'modul',
    headerName: 'Nama Modul',
    filter: true,
    sortable: true,
     filter: 'ColFilter',
    resizable: false,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center', 'capitalize']
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