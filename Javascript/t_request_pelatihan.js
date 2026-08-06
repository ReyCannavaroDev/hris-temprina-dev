import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, computed } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const is_approval = route.query.is_approval ? true : false
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })


// ------------------------------ PERSIAPAN
const endpointApi = '/t_request_pelatihan'
onBeforeMount(() => {
  document.title = 'Request Pelatihan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

onBeforeMount(async () => {
  document.title = is_approval ? 'Approval Cuti' : 'Transaksi Cuti'
  // console.log('cek hc', store.user.data.is_hc)

  const respoData = localStorage.getItem('respo')
  if (respoData) {
    const respoValues = JSON.parse(respoData)
    // console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
    // console.log('ini comp',values.m_comp_id)
  }
})

const values = reactive({
  // status: true,
  // direktorat: store.user.data?.direktorat
})

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'HALF APPROVED'),
    note: values.catatan,
  };
  // if(!payload.note) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: "Catatan wajib diisi",
  //   });
  //   return  
  // }


  swal.fire({
    icon: 'warning',
    text: typePar === 'revise' ? 'Revised data?' : (typePar === 'reject' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/t_request_pelatihan/progress`;
        isRequesting.value = true;
        const res = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          body: JSON.stringify(payload),
        });




        if (!res.ok) {
          const responseJson = await res.json();
          if ([400, 422, 500].includes(res.status)) {
            formErrors.value = responseJson.errors || {};
            if (res.status === 422) {
              throw new Error(responseJson.message + " Pastikan anda sudah mengisi semua kolom dengan tanda bintang merah");
            }
            throw new Error(responseJson.message || "Failed when trying to post data");
          } else {
            throw new Error("Failed when trying to post data");
          }
        } else {
          // Success case
          swal.fire({
            icon: 'success',
            text: 'Proses berhasil',
          });
          router.replace('/notifikasi');
        }
      } catch (err) {
        isBadForm.value = true;
        swal.fire({
          icon: 'error',
          text: err || 'Failed when trying to post data',
        });
      } finally {
        isRequesting.value = false;
      }
    }
  });

  if (route.params.id === 'create') {
    activeTabIndex = 0;
  }
}

async function onApproval() {
  const payload = {
    id: parseInt(route.params.id),
    target_id: values.target_id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
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

onBeforeMount(async () => {
  // console.log('test')
  // onReset()
  if (isRead) {
    //  READ DATA
    try {
      let dataURL = ''
      let dataURLAprv = ''
      let resAprv = ''
      if (route.query.is_approval) {
        dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/app_detail?id=${route.params.id}`;
        isRequesting.value = true
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        const resultJson = await apiApp.json()
        console.log('test', resultJson.data)
        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}?join=true&transform=true`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!apiTrx.ok || !apiApp.ok) throw new Error("Failed when trying to read data")
        const resultTrxJson = await apiTrx.json()
        values.interval = resultJson?.data.approval
        values.approval = resultJson?.data.approval
        values.trx = resultJson?.data.trx
        values.datalog = resultJson?.data.approval_log
        initialValues = resultTrxJson.data
        const requestPelatihanDetail = initialValues.t_request_pelatihan_d_kary ?? []
        detailArr.value = await Promise.all(
          requestPelatihanDetail.map(async (dt) => {
            let cabang_kary = ''
            let divisi_kary = ''
            let posisi_kary = ''
            const branchId = dt['m_kary.m_branch_id']
            const divisiId = dt['m_kary.m_divisi_id']
            const posisiId = dt['m_kary.m_posisi_id']

            if (branchId) {
              try {
                const resBranch = await fetch(`${store.server.url_backend}/operation/m_branch/${branchId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resBranch.ok) {
                  const branchJson = await resBranch.json()
                  cabang_kary = branchJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama branch:', err)
              }
            }

            if (divisiId) {
              try {
                const resDivisi = await fetch(`${store.server.url_backend}/operation/m_divisi/${divisiId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resDivisi.ok) {
                  const divisiJson = await resDivisi.json()
                  divisi_kary = divisiJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama divisi:', err)
              }
            }

            if (posisiId) {
              try {
                const resDivisi = await fetch(`${store.server.url_backend}/operation/m_posisi/${posisiId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resDivisi.ok) {
                  const posisiJson = await resDivisi.json()
                  posisi_kary = posisiJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama divisi:', err)
              }
            }

            return {
              ...dt,
              nama_kary: dt['m_kary.nama_lengkap'],
              cabang_kary,
              divisi_kary,
              posisi_kary
            }
          })
        )

        // logic finish & Approved data
        isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
        isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
      } else {
        const editedId = route.params.id
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
        isRequesting.value = true

        const params = { join: true, transform: true }
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

        if (initialValues['status']) {
          values.status_name = initialValues['status']
        }
        if (actionText.value?.toLowerCase() === 'copy' && initialValues.id) {
          delete initialValues.id, delete initialValues.no, delete initialValues.date, delete initialValues.status
        }

        // Menambahkan Data Ke Array

        initialValues.t_request_pelatihan_d_kary?.forEach((items) => {
          if (actionText.value?.toLowerCase() === 'copy' && items.id) {
            delete items.id, delete items.no
          }

          detailArr.value = [items, ...detailArr.value]
        })

        // initialValues.t_mou_d?.forEach((items)=>{  
        //   if(actionText.value?.toLowerCase() === 'copy' && items.id){
        //     delete items.id
        //   }                
        //   detailArrPengambilan.value = [items, ...detailArrPengambilan.value]
        // })
        // if(!initialValues.sopir_id){
        //   initialValues.flag='customer'
        // }

        // initialValues.t_mou_d.forEach((dt) => {
        //   console.log('cek 3', dt.vi.qty_2_stock)
        //   console.log(dt)
        // })


        const requestPelatihanDetail = initialValues.t_request_pelatihan_d_kary ?? []
        detailArr.value = await Promise.all(
          requestPelatihanDetail.map(async (dt) => {
            let cabang_kary = ''
            let divisi_kary = ''
            let posisi_kary = ''
            const branchId = dt['m_kary.m_branch_id']
            const divisiId = dt['m_kary.m_divisi_id']
            const posisiId = dt['m_kary.m_posisi_id']

            if (branchId) {
              try {
                const resBranch = await fetch(`${store.server.url_backend}/operation/m_branch/${branchId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resBranch.ok) {
                  const branchJson = await resBranch.json()
                  cabang_kary = branchJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama branch:', err)
              }
            }

            if (divisiId) {
              try {
                const resDivisi = await fetch(`${store.server.url_backend}/operation/m_divisi/${divisiId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resDivisi.ok) {
                  const divisiJson = await resDivisi.json()
                  divisi_kary = divisiJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama divisi:', err)
              }
            }

            if (posisiId) {
              try {
                const resDivisi = await fetch(`${store.server.url_backend}/operation/m_posisi/${posisiId}`, {
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                })
                if (resDivisi.ok) {
                  const posisiJson = await resDivisi.json()
                  posisi_kary = posisiJson.data?.name || ''
                }
              } catch (err) {
                console.warn('Gagal ambil nama divisi:', err)
              }
            }

            return {
              ...dt,
              nama_kary: dt['m_kary.nama_lengkap'],
              cabang_kary,
              divisi_kary,
              posisi_kary
            }
          })
        )

        // console.log(initialValues.t_mou_d)
      }
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

let _id = 0
const detailArr = ref([])
const onDetailAdd = (e) => {
  e.forEach(row => {
    row.t_mou_d = row.id;
    detailArr.value.push(row)
  });
}

const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
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

async function posted() {
  const payload = {
    id: route.params.id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/posted`
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

async function approval() {
  const payload = {
    id: parseInt(route.params.id),
    target_id: values.target_id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
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

async function onSave() {
  try {

    values.t_request_pelatihan_d_kary = detailArr.value
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
const activeBtn = ref()
function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'DRAFT',
    2: 'POSTED',
    2: 'IN APPROVAL',
    2: 'REVISED',
    2: 'APPROVED',
    2: 'REJECTED',
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

const data = reactive({
  can_read: false,
  can_create: false,
  can_update: false,
  can_delete: false,
  respo_id: null,
  subcomp_id: null,
  branch_id: null
})

const isAccessReady = ref(false)

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const v = JSON.parse(localStorage.getItem('respo'))
    data.respo_id = v.id
    data.subcomp_id = v.m_subcomp_id
    data.branch_id = v.m_branch_id
  }

  if (!data.respo_id) return

  const params = new URLSearchParams({
    path: route.path,
    respo_id: data.respo_id
  })

  const res = await fetch(
    `${store.server.url_backend}/operation/m_general/access?${params}`,
    { headers: { Authorization: `${store.user.token_type} ${store.user.token}` } }
  )

  const r = await res.json()

  data.can_read = r.can_read
  data.can_create = r.can_create
  data.can_update = r.can_update
  data.can_delete = r.can_delete

  isAccessReady.value = true
})

const landing = computed(() => {
  if (!isAccessReady.value) return null

  return {
    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        title: "Hapus",
        show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_delete,
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
                if (!res.ok) throw ("Failed when trying to remove data")
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
        click(row) {
          router.push(`${route.path}/${row.id}?` + tsId)
        }
      },
      {
        icon: 'edit',
        title: "Edit",
        class: 'bg-blue-600 text-light-100',
        show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_update,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
        }
      },
      {
        icon: 'copy',
        title: "Copy",
        class: 'bg-gray-600 text-light-100',
        show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_create,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
        }
      },
      {
        icon: 'paper-plane',
        title: "Posted Data",
        class: 'bg-rose-700 rounded-lg text-white',
        show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_update,
        async click(row) {
          const result = await swal.fire({
            icon: 'warning',
            text: 'Post Data?',
            iconColor: '#1469AE',
            confirmButtonColor: '#1469AE',
            showDenyButton: true
          })
          if (!result.isConfirmed) return
          try {
            isRequesting.value = true
            const url = `${store.server.url_backend}/operation${endpointApi}/posted`
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              body: JSON.stringify({ id: row.id })
            })
            const json = await res.json()
            if (!res.ok) throw (json?.message || json?.data?.errorText || 'Failed when trying to post data')
            if (json.status && json.status.toUpperCase() !== 'SUCCESS') {
              swal.fire({ icon: 'info', text: json.message || 'Status tidak success' })
              apiTable.value.reload()
              return
            }
            swal.fire({ icon: 'success', text: json.message || 'Data berhasil diposting' })
            apiTable.value.reload()
          } catch (err) {
            isBadForm.value = true
            swal.fire({ icon: 'error', iconColor: '#1469AE', confirmButtonColor: '#1469AE', text: err })
          } finally {
            isRequesting.value = false
          }
        }
      }
      ,
      {
        icon: 'paper-plane',
        title: "Send For Approval",
        class: 'bg-rose-700 rounded-lg text-white',
        show: (row) => row.status?.toUpperCase() === 'POSTED' && data.can_update,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
        }
      }, {
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
              const dataURL = `${store.server.url_backend}/operation/t_request_pelatihan/approveHC`
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

    ],

    api: {
      url: data.can_read
        ? `${store.server.url_backend}/operation${endpointApi}`
        : null,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        m_subcomp_id: data.subcomp_id,
        m_branch_id: data.branch_id,
        join: true,
        transform: true,
        scopes: 'respo'
      },
      onsuccess(r) {
        r.page = r.current_page
        r.hasNext = r.has_next
        return r
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
      field: 'kode',
      wrapText: true,
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'trainer.nama_trainer',
      headerName: 'Nama Trainer',
      filter: true,
      sortable: true,
      flex: 2,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'm_prog_pelatihan.tema_pelatihan',
      headerName: 'Nama Pelatihan',
      filter: true,
      sortable: true,
      flex: 2,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: 'Tanggal Awal',
      field: 'date_from',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: 'Tanggal Akhir',
      field: 'date_to',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: 'Status',
      field: 'status',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: ({ value }) => {
        let color = 'gray'
        if (value == 'POSTED')
          color = 'green'
        else if (value == 'IN APPROVAL')
          color = 'blue'
        else if (value == 'REVISED')
          color = 'yellow'
        else if (value == 'REJECTED')
          color = 'red'
        return `<span class="text-${color}-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
      }
    }
    ]
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
