//   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, computed, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
console.log('ini store', store.user.data.m_kary_id)
const swal = inject('swal')
const isRead = route.params.id && route.params.id !== 'create'
const read = ref(false)
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const access = reactive({
  create: false,
  read: false,
  update: false,
  delete: false,
  verify: false,
})

console.log('test', access.create)

// const getAccess = async () => {
//   try {
//     const res = await fetch(`${store.server.url_backend}/operation/m_role/getAccess?endpoint=${endpointApi}`, {
//       headers: {
//         'Content-Type': 'application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`,
//       },
//     })

//     if (!res.ok) throw new Error('Gagal mengambil akses')

//     const result = await res.json()
//     console.log('result get', result)
//     return result
//   } catch (err) {
//     console.error('Fetch akses gagal:', err)
//     return null
//   }
// }


watchEffect(() => {
  console.log('watch', access)
  if (access.read || access.create || access.update) {
    landing.api = {
      url: `${store.server.url_backend}/operation${endpointApi}`,
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        searchfield: `this.id, this.nomor, m_kary.nama_depan, this.keterangan, this.nilai`,
      },
      onsuccess(response) {
        response.page = response.current_page
        response.hasNext = response.has_next
        return response
      }
    }
  } else {
    landing.api = null
  }
})

const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))


// ------------------------------ PERSIAPAN
const endpointApi = '/t_bonus'
onBeforeMount(async () => {
  document.title = 'Transaksi Bonus'
  // const result = await getAccess()
  // if (result) {
  //   access.create = result.create
  //   access.read = result.read
  //   access.update = result.update
  //   access.delete = result.delete
  //   access.verify = result.verify
  // }
  console.log('result call', access.create)
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  status: 'DRAFT',
  is_all_kary: false,
  is_lunas: false
})


onBeforeMount(async () => {
  // tampilkan default direktorat dengan store user comp.nama
  values.direktorat = store.user.data?.direktorat

  if (isRead) {
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

async function posted() {
  const payload = {
    id: route.params.id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/postData`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: 'POST',
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
        throw (responseJson.message || "Failed when trying to post data")
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

async function onSave() {
  console.log(values)
  //values.tags = JSON.stringify(values.tags)
  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    values.status = values.status === 'DRAFT' ? 'DRAFT' : 'POSTED'
    values.is_all_kary = values.is_all_kary === true ? 1 : 0
    values.is_lunas = values.is_lunas === true ? 1 : 0
    values.last_editor_id = store.user.data.id
    values.creator_id = store.user.data.id
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
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
  } catch (err) {
    console.log(err)
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err.message
    })
  }
  isRequesting.value = false
}


//  @else----------------------- LANDING
// LANDING LAMA 
const token = store.user.token
console.log('token', token)
let data = reactive({
  respo_id: null,
  subcomp_id: null,
  branch_id: null,
  can_read: false,
  can_create: false,
  can_delete: false,
  can_update: false
})
const fileInput = ref(null)

const testClick = () => {
  fileInput.value.click()
}

function uploadFile(file) {
  if (!file) return

  const formData = new FormData()
  formData.append('csv', file)

  console.log('1')

  const dataURL = `${store.server.url_backend}/operation/t_bonus/importBonus`

  console.log('2')


  fetch(dataURL, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`
    },
    body: formData
  })
    .then(res => {
      if (!res.ok) throw new Error("Upload gagal / Unauthorized")
      return res.json()
    })
    .then(result => {
      swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'File berhasil diupload!'
      })
      apiTable.value.reload()
      document.getElementById('fileUpload').value = ''
    })
    .catch(err => {
      swal.fire({
        icon: 'warning',
        title: 'Failed',
        text: 'File gagal diupload!'
      })

      document.getElementById('fileUpload').value = ''
    })
}

// const handleFileUpload = async (event) => {
//   const file = event.target.files[0]
//   if (!file) return

//   const formData = new FormData()
//   formData.append('file', file)

//   try {
//     const response = await fetch(
//       `${store.server.url_backend}/operation/t_bonus/importBonus`,
//       {
//         method: 'POST',
//         headers: {
//           Authorization: `${store.user.token_type} ${store.user.token}`
//         },
//         body: formData
//       }
//     )

//     const result = await response.json()

//     // ✅ CEK ERROR DARI BACKEND
//     if (!response.ok || result.error) {
//       throw new Error(result.error || 'Upload gagal')
//     }

//     swal.fire({
//       icon: 'success',
//       text: 'Upload berhasil'
//     })

//   } catch (e) {
//     swal.fire({
//       icon: 'error',
//       text: e.message
//     })
//   }

//   event.target.value = null
// }


function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
  // console.log('ini id', id)
}


function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/t_cuti/log?id=${id}`
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const result = await res.json()
  dataLog.items = result
}

const activeBtn = ref()

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'AKTIF',
    2: 'NON AKTIF',
  }

  if (noBtn !== null) {
    if (activeBtn.value === noBtn) {
      activeBtn.value = null
      statusLabel = null
    } else {
      activeBtn.value = noBtn
    }
  } else {
    statusLabel = statusMap[activeBtn.value] || null
  }
  const filters = []

  if (statusLabel) {
    filters.push(`this.status='${statusLabel?.toUpperCase()}'`)
  }

  landing.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}

const isAccessReady = ref(false)

onBeforeMount(async () => {
  const rs = localStorage.getItem('respo')
  if (rs) {
    const r = JSON.parse(rs)
    data.respo_id = r.id
    data.subcomp_id = r.m_subcomp_id
    data.branch_id = r.m_branch_id
  }

  if (!data.respo_id) {
    isAccessReady.value = true
    return
  }

  const params = new URLSearchParams({
    path: route.path,
    respo_id: data.respo_id
  })

  const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`

  try {
    const response = await fetch(endpoint, {
      method: 'GET',
      headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
    })
    const result = await response.json()
    data.can_read = result.can_read
    data.can_create = result.can_create
    data.can_delete = result.can_delete
    data.can_update = result.can_update
  } catch (e) { }
  finally {
    isAccessReady.value = true
  }
})


const landing = computed(() => {
  if (!isAccessReady.value) return null
  return {
    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_delete,
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
        show: row => (row.status?.toUpperCase() === 'DRAFT' || row.status?.toUpperCase() === 'REVISED') && data.can_update,
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
      {
        icon: 'location-arrow',
        title: "Post Data",
        class: 'bg-rose-700 rounded-lg text-white',
        show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_update,
        async click(row) {
          swal.fire({
            icon: 'warning',
            text: 'Post Data?',
            iconColor: '#1469AE',
            confirmButtonColor: '#1469AE',

            showDenyButton: true
          }).then(async (res) => {
            if (res.isConfirmed) {
              try {
                const dataURL = `${store.server.url_backend}/operation/t_bonus/posted`
                isRequesting.value = true
                const res = await fetch(dataURL, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  body: JSON.stringify({ id: row.id })
                })
                if (!res.ok) {
                  if ([400, 422, 500].includes(res.status)) {
                    const responseJson = await res.json()
                    formErrors.value = responseJson.errors || {}
                    throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
                  } else {
                    throw ("Failed when trying to post data")
                  }
                }
                const responseJson = await res.json()
                swal.fire({
                  icon: 'success',
                  text: responseJson.message
                })
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

              apiTable.value.reload()
            }
          })
        }
      },
      {
        icon: 'location-arrow',
        title: "Send Approval",
        class: 'bg-rose-700 rounded-lg text-white',
        show: (row) => {
          const status = row.status?.toUpperCase()
          return ['POSTED', 'REVISED'].includes(status) && data.can_update
        },
        click(row) {
          router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
        }
        // async click(row) {
        //   swal.fire({
        //     icon: 'warning',
        //     text: 'Send Approval?',
        //     iconColor: '#1469AE',
        //     confirmButtonColor: '#1469AE',

        //     showDenyButton: true
        //   }).then(async (res) => {
        //     if (res.isConfirmed) {
        //       try {
        //         const dataURL = `${store.server.url_backend}/operation/t_cuti/send_approval`
        //         isRequesting.value = true
        //         const res = await fetch(dataURL, {
        //           method: 'POST',
        //           headers: {
        //             'Content-Type': 'Application/json',
        //             Authorization: `${store.user.token_type} ${store.user.token}`
        //           },
        //           body: JSON.stringify({ id: row.id })
        //         })
        //         if (!res.ok) {
        //           if ([400, 422, 500].includes(res.status)) {
        //             const responseJson = await res.json()
        //             formErrors.value = responseJson.errors || {}
        //             throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
        //           } else {
        //             throw ("Failed when trying to post data")
        //           }
        //         }
        //         const responseJson = await res.json()
        //         swal.fire({
        //           icon: 'success',
        //           text: responseJson.message
        //         })
        //         // const resultJson = await res.json()
        //       } catch (err) {
        //         isBadForm.value = true
        //         swal.fire({
        //           icon: 'error',
        //           iconColor: '#1469AE',
        //           confirmButtonColor: '#1469AE',
        //           text: err
        //         })
        //       }
        //       isRequesting.value = false

        //       apiTable.value.reload()
        //     }
        //   })
        // }
      },
      {
        icon: 'location-arrow',
        title: "Approve HC",
        class: 'bg-rose-700 rounded-lg text-white',
        show: row => {
          const status = row.status?.toUpperCase()
          const isUserHC = store.user.data?.is_hc === true || store.user.data?.is_hc === 1
          const isStatusValid = status === 'HALF APPROVED'
          return isUserHC && isStatusValid && data.can_update
        },
        async click(row) {
          swal.fire({
            icon: 'warning',
            text: 'Full Approve?',
            iconColor: '#1469AE',
            confirmButtonColor: '#1469AE',

            showDenyButton: true
          }).then(async (res) => {
            if (res.isConfirmed) {
              try {
                const dataURL = `${store.server.url_backend}/operation/t_cuti/approveHC`
                isRequesting.value = true
                const res = await fetch(dataURL, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  body: JSON.stringify({ id: row.id })
                })
                if (!res.ok) {
                  if ([400, 422, 500].includes(res.status)) {
                    const responseJson = await res.json()
                    formErrors.value = responseJson.errors || {}
                    throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
                  } else {
                    throw ("Failed when trying to post data")
                  }
                }
                const responseJson = await res.json()
                swal.fire({
                  icon: 'success',
                  text: responseJson.message
                })
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

              apiTable.value.reload()
            }
          })
        }
      },
      {
        icon: 'table',
        title: "Log Approval",
        class: 'bg-gray-700 rounded-lg text-white',
        show: (row) => ['APPROVED', 'IN APPROVAL'].includes(row['status']) && data.can_read,
        click(row) {
          openModal(row.id)
        }
      }
    ],

    api: {
      url: data.can_read ? `${store.server.url_backend}/operation${endpointApi}`: null,
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      // params: (() => {
      //   const params = {
      //     // kary_id: store.user.data.m_kary_id ?? 0,
      //     join: true,
      //     transform: true,
      //   }

      //   const where = []

      //   if (data.subcomp_id != null) {
      //     where.push(`m_kary.m_subcomp_id  = ${data.subcomp_id}`)
      //   }

      //   if (data.branch_id != null) {
      //     where.push(`m_kary.m_branch_id = ${data.branch_id}`)
      //   }

      //   if (where.length) {
      //     params.where = where.join(' AND ')
      //   }

      //   return params
      // }),
      params: { simplest: true, join: true,transform: true, searchfield: 'this.nama, m_kary.nama_lengkap' },
      onsuccess(res) {
        res.page = res.current_page
        res.hasNext = res.has_next
        return res
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
      field: 'nomor',
      headerName: 'Nomor',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'm_kary.nama_depan',
      headerName: 'Karyawan',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-left']
    },
    {
      field: 'keterangan',
      headerName: 'Keterangan',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-left']
    },
    {
      field: 'nilai',
      headerName: 'Nominal',
      filter: 'ColFilter',
      sortable: true,
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-end'],
      cellRenderer: ({ value }) => {
        const nominal = parseFloat(value || 0);
        const formatted = new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          minimumFractionDigits: 0
        }).format(nominal);
        return `<span style="color:green">${formatted}</span>`;
      }
    },
    {
      field: 'status',
      headerName: 'Status',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: ({ value }) => {
        return value
          === "POSTED"
          ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
          : `<span class="text-red-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
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