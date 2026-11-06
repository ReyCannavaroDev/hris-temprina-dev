import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, computed, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Create' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
// ------------------------------ PERSIAPAN

const endpointApi = '/m_berkas'
onBeforeMount(()=>{
  document.title = 'Master berkas'
})

//  @if( $id )------------------- VALUES FORM
let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: 1,
})

console.log(store.server);

onBeforeMount(async () => {
  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { join: false, transform: false }
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
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        iconColor: '#1469AE',
              confirmButtonColor: '#1469AE',

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
    iconColor: '#1469AE',
    text: 'Buang semua perubahan dan kembali ke list data?',
          confirmButtonColor: '#1469AE',

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
    text: 'Anda yakin akan mereset data ini?',
    showDenyButton: true
  }).then((res) => {
  
    if (res.isConfirmed) {
      const newValues = {
        nama: '',
        kategori: '',
        desc: '',
        url: ''
      };
      
      for (const key in newValues) {
        if (newValues.hasOwnProperty(key)) {
          values[key] = newValues[key];
        }
      }
    }
  })
}

function onSave() {
  console.log(values)
  //values.tags = JSON.stringify(values.tags)
  swal.fire({
    icon: 'warning',
    iconColor: '#1469AE',
    text: 'Save data?',
          confirmButtonColor: '#1469AE',

    showDenyButton: true
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const isCreating = ['Create','Copy'].includes(actionText.value)
        const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
        isRequesting.value = true
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
            throw new Error(responseJson.message || "Failed when trying to post data")
          } else {
            throw new Error("Failed when trying to post data")
          }
        }
        router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
      } catch (err) {
        isBadForm.value = true
        swal.fire({
          icon: 'error',
          iconColor: '#1469AE',
                confirmButtonColor: '#1469AE',

          text: err
        })
      }
      isRequesting.value = false
    }
  })
}

//  @else----------------------- LANDING
let data = reactive({})

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo coi', respoValues)
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
  }
  console.log('jarwok', data.subcomp_id)
})

const landing = reactive({
  //  ACTIONS sesuai priviledge user dan data
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => (currentMenu?.can_delete),
      // show: () => store.user.data.username==='trial',
      click(row) {
        swal.fire({
          icon: 'warning',
          iconColor: '#1469AE',
          text: 'Hapus Data Terpilih?',
                confirmButtonColor: '#1469AE',

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
                iconColor: '#1469AE',
                      confirmButtonColor: '#1469AE',

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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='trial',
      click(row) {
        router.push(`${route.path}/${row.id}?ts=`+(Date.parse(new Date())))
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) =>
        currentMenu?.can_update === true,
      // show: (row) =>
      //   currentMenu?.can_update === true &&
      //   (row.status?.toUpperCase() === 'DRAFT' ||
      //     row.status?.toUpperCase() === 'REVISED'),
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='trial',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&ts=`+(Date.parse(new Date())))
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => currentMenu?.can_create === true,
      // show: (row) => currentMenu?.can_create === true && row.status?.toUpperCase() === 'DRAFT',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&ts=`+(Date.parse(new Date())))
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
    params: computed(() => {
      const params = {
        join: true,
        transform: true,
        searchfield: 'this.nama, kategori.value, this.url, this.desc'
      }

      const where = []

      if (data.subcomp_id != null) {
        where.push(`this.m_subcomp_id = ${data.subcomp_id}`)
      }

      if (data.branch_id != null) {
        where.push(`this.m_branch_id = ${data.branch_id}`)
      }

      if (where.length) {
        params.where = where.join(' AND ')
      }

      return params
    }),
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
    sortable: false,
    resizable: true,
    filter: false,
    cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
  },
  {
    field: 'nama',
    filter: false,
    sortable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200']
  },
  {
    headerName:'Kategori',
    field: 'kategori.value',
    filter: false,
    sortable: true,
    flex:1,
    wrapText: true,
    autoHeight: true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center', 'py-2']
  },
    {
    headerName: 'URL File',
    field: 'url',
    filter: false,
    sortable: true,
    flex:2,
    wrapText:true,
    autoHeight:true,
    cellClass: [ 'border-r', '!border-gray-200', 'ag-scrolls']
  },
  {
    headerName: 'Catatan',
    field: 'desc',
    filter: false,
    sortable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200']
  }]
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
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))