//   javascript//   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, computed } from 'vue'

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
let trx_dtl = reactive({ items: [] })
let detailKey = ref(0)
let modalOpen = ref(false)
let detailIdxSelected = ref(0)
let trx_dtl_sub = reactive({ items: [] })
let data = reactive({
  respo_id: null,
  subcomp_id: null,
  branch_id: null,
  can_read: false,
  can_create: false,
  can_delete: false,
  can_update: false
})

const isAccessReady = ref(false)


// ------------------------------ PERSIAPAN
const endpointApi = '/m_spd'
onBeforeMount(async () => {
  document.title = 'Master Surat Perjalanan Dinas'
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
      data.can_read = result.can_read
      data.can_create = result.can_create
      data.can_delete = result.can_delete
      data.can_update = result.can_update
      data.rows = result.data
      console.log('x', data)
    } catch (err) {
      console.error(err)
    } finally {
      isAccessReady.value = true;
    }
  }
})


//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: 1,
})




onBeforeMount(async () => {
  // tampilkan default direktorat dengan store user comp.nama
  values.direktorat = store.user.data?.direktorat


  if (isRead && data.can_read) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

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
      trx_dtl.items = resultJson.data?.m_spd_det_biaya ?? []
      initialValues.is_active = initialValues.is_active ? 1 : 0
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

let _id = ref(0)
function addRow() {
  const newItem = {
    _id: ++_id.value,
  }
  trx_dtl.items.push(newItem)
}

function openSub(i) {
  trx_dtl_sub.items = []
  total_sub.value = 0
  total_sub_text.value = 0
  modalOpen.value = true
  detailIdxSelected.value = i
  trx_dtl_sub.items = trx_dtl.items[i]['m_spd_det_transport'] ?? []
}

function addRowSub(i) {
  const newItem = {
    _id: ++_id.value,
    zona_tujuan_id: null,
    jenis_transport_id: null,
    nama_transport: null,
    biaya_transport: null,
    keterangan: null
  }
  trx_dtl_sub.items.push(newItem)
}

const total_sub = ref(0)
const total_sub_text = ref(0)
function countSub() {
  const total = trx_dtl_sub.items.reduce((acm, item) => {
    return acm + item.biaya_transport;
  }, 0);
  total_sub.value = total
  total_sub_text.value = total.toLocaleString('id-ID', {
    style: 'currency',
    currency: 'IDR'
  });
}
function deleteAll(item) {
  swal.fire({
    icon: 'warning',
    text: 'Hapus semua detail biaya?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      trx_dtl.items = []
    }
  })
}
function deleteDetail(item) {
  trx_dtl.items = trx_dtl.items.filter((e) => e._id != item._id)
}
function deleteSub(item) {
  trx_dtl_sub.items = trx_dtl_sub.items.filter((e) => e._id != item._id)
  countSub()
}

function saveSub() {
  const text = 'Lengkapi kolom dengan tanda bintang merah'
  let next = true
  trx_dtl_sub.items.forEach((item, i) => {
    if (!item.jenis_transport_id || !item.nama_transport || !item.biaya_transport) {
      swal.fire({
        icon: 'warning',
        text: `Baris ${i + 1}, ` + text
      })
      next = false
      return
    }
  })
  if (!next) return
  trx_dtl.items[detailIdxSelected.value]['m_spd_det_transport'] = trx_dtl_sub.items
  trx_dtl.items[detailIdxSelected.value]['total_biaya'] = total_sub.value
  modalOpen.value = false
  trx_dtl_sub.items = []
  total_sub.value = 0
  total_sub_text.value = 0
}

function closeModal(i) {
  modalOpen.value = false
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

  router.replace('/' + modulPath)
}

// function onReset() {
//   swal.fire({
//     icon: 'warning',
//     text: 'Anda yakin akan mereset data ini?',
//     showDenyButton: true
//   }).then((res) => {

//     if (res.isConfirmed) {
//       const newValues = {
//         m_divisi_id: '',
//         m_posisi_id: '',
//         m_dept_id: '',
//         m_zona_id: '',
//         grading_id: '',
//         grading: '',
//         desc: ''
//       };

//       for (const key in newValues) {
//         if (newValues.hasOwnProperty(key)) {
//           values[key] = newValues[key];
//         }
//       }
//       trx_dtl.items = []
//     }
//   })
// }

async function onSave() {
  let next = true
  trx_dtl.items.forEach((item, i) => {
    if (!item.tipe_id || !item.total_biaya) {
      swal.fire({
        icon: 'warning',
        text: `Detail Biaya baris ${i + 1}, Lengkapi kolom dengan tanda bintang merah`
      })
      next = false
      return
    }
  })
  if (!next) return

  // merging detail data
  values['m_spd_det_biaya'] = trx_dtl.items

  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
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

// onBeforeMount(async () => {
//   // if (localStorage.getItem('respo')) {
//   //   const respoValues = JSON.parse(localStorage.getItem('respo'))
//   //   data.respo_id = respoValues.id
//   //   data.subcomp_id = respoValues.m_subcomp_id
//   //   data.branch_id = respoValues.m_branch_id
//   // }

//   // if (data.respo_id) {
//   //   const params = new URLSearchParams({
//   //     path: route.path,
//   //     respo_id: data.respo_id
//   //   })
//   //   const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`

//   //   try {
//   //     const response = await fetch(endpoint, {
//   //       method: 'GET',
//   //       headers: {
//   //         Authorization: `${store.user.token_type} ${store.user.token}`
//   //       }
//   //     })
//   //     const result = await response.json()
//   //     console.log('x',result)
//   //     data.can_read = result.can_read
//   //     data.can_create = result.can_create
//   //     data.can_delete = result.can_delete
//   //     data.can_update = result.can_update
//   //     data.rows = result.data

//       // 2. UPDATE URL landing secara manual di sini!
//       if (data.can_read) {
//         landing.api.url = `${store.server.url_backend}/operation${endpointApi}`
//         // Tunggu sejenak agar reaktivitas Vue memproses perubahan, lalu reload
//         nextTick(() => {
//           apiTable.value?.reload()
//         })
//       } else {
//         landing.api.url = '' // Tetap kosong jika tidak punya akses
//       }
//       console.log(landing.api.url)
//   //   } catch (err) {
//   //     console.error(err)
//   //   }
//   // }
// })



const activeBtn = ref()

function filterShowData(params, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }
  
  if (activeBtn.value == null) {
    // clear params filter
    landing.value.api.params.where = null
  } else if (params) {
    landing.value.api.params.where = `this.is_active=true`
  } else {
    landing.value.api.params.where = `this.is_active=false`
  }

  apiTable.value.reload()
}




const landing = computed(() => {
  if (!isAccessReady.value) return null
  return {
    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        title: "Hapus",
        show: (row) => (data.can_delete),
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
        show: (row) => (data.can_read),
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
          data.can_update === true &&
          (row.status?.toUpperCase() === 'DRAFT' ||
            row.status?.toUpperCase() === 'REVISED'),
        // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
        click(row) {
          router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
        }
      },
      {
        icon: 'copy',
        title: "Copy",
        class: 'bg-gray-600 text-light-100',
        show: (row) => data.can_create === true && row.status?.toUpperCase() === 'DRAFT',
        click(row) {
          router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
        }
      }
    ],
    api: {
      // url: `${store.server.url_backend}/operation${endpointApi}`,
      url: data.can_read
           ? `${store.server.url_backend}/operation${endpointApi}` 
           : '',
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        searchfield: 'this.kode , m_comp.name , m_subcomp.name , m_branch.name , m_divisi.name , m_posisi.name , m_zona.nama , this.desc',
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
      field: 'kode',
      headerName: 'Kode',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'm_comp.name',
      headerName: 'SBU',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize'],
    },
    {
      field: 'm_subcomp.name',
      headerName: 'SUB',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      field: 'm_branch.name',
      headerName: 'CABANG',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      field: 'm_divisi.name',
      headerName: 'DIVISI',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      field: 'm_posisi.name',
      headerName: 'Posisi',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      field: 'm_zona.nama',
      headerName: 'Zona',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      field: 'desc',
      headerName: 'Keterangan',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'capitalize']
    },
    {
      headerName: 'Status',
      field: 'is_active',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: ({ value }) => {
        return value === true
          ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
          : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
      }
    }]
  }
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