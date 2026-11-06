import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, computed, onBeforeMount, watchEffect, onActivated, } from 'vue'

const router = useRouter()
const route = useRoute()
// console.log('x',route.path)
const store = inject('store')
const swal = inject('swal')

console.log('cek kary',store.user.data.m_kary_id)

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const is_approval = route.query.is_approval ? true : false
const is_to_upload = route.query.is_to_upload ? true : false
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })

// ------------------------------ PERSIAPAN
const endpointApi = '/t_cuti'
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

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  m_kary_id: store.user.data.m_kary_id,
})

// const values = reactive({
// })
const totalDays = ref();

watchEffect(() => {
  if (values.date_from && values.date_to && values.date_from !== '' && values.date_to !== '') {
    const dateFrom = new Date(values.date_from);
    const dateTo = new Date(values.date_to);

    if (!isNaN(dateFrom.getTime()) && !isNaN(dateTo.getTime())) {
      const timeDifference = dateTo.getTime() - dateFrom.getTime();
      const daysDifference = Math.floor(timeDifference / (1000 * 3600 * 24));

      totalDays.value = daysDifference;
    } else {
      totalDays.value = 'error';
    }
  } else {
    totalDays.value = 0;
  }
});

console.log(actionText.value)

const apiKary = computed(() => {
  const params = {
    join: false,
    transform: false,
    // where: '',
  }

  // Jika is_approval tidak true, baru masukkan params filter
  if (!is_approval) {
    params.m_subcomp_id = values.m_subcomp_id ?? null
    params.m_branch_id = values.m_branch_id ?? null
  }

  console.log('Mode Approval:', is_approval)
  console.log('branch', params.m_branch_id)
  console.log('subcomp', params.m_subcomp_id)

  const where = []

  // Logika 'where' juga bisa dibungkus kondisi yang sama jika diperlukan
  if (!is_approval) {
    if (values.m_subcomp_id != null) {
      where.push(`this.m_subcomp_id = ${values.m_subcomp_id}`)
    }
    if (values.m_branch_id != null) {
      where.push(`this.m_branch_id = ${values.m_branch_id}`)
    }
  }

  if (where.length) {
    params.where = where.join(' AND ')
  }

  return {
    url: `${store.server.url_backend}/operation/m_kary`,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`,
    },
    params,
  }
})

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }

  console.log('branch', values.m_branch_id)

  // tampilkan default direktorat dengan store user comp.nama
  values.direktorat = store.user.data?.direktorat


  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      let dataURL = ''
      let dataURLAprv = ''
      let resAprv = ''
      if (route.query.is_approval) {
        dataURLAprv = `${store.server.url_backend}/operation/t_cuti/detail?id=${route.params.id}`
        isRequesting.value = true
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        const resultJson = await apiApp.json()
        console.log(resultJson.data)
        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}`, {
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

        // logic finish & Approved data
        isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
        isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
      } else {
        const editedId = route.params.id
        dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
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
        values.m_kary_id = initialValues.m_kary_id
        // logic Approved data
        isApproved.value = resultJson?.data?.cuti_status == 'APPROVED' ? true : false
        console.log(resultJson?.data)
        console.log(isApproved.value)
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

async function downloadDoc() {
  window.open(`${store.server.url_backend}/operation/t_cuti/cuti?id=${values.t_cuti_id}`)
}

function onBack() {
  if (!is_approval) {
    router.replace('/' + modulPath)
  } else {
    router.replace('/notifikasi')
  }
  return
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Anda yakin akan mereset data ini?',
    showDenyButton: true
  }).then((res) => {

    if (res.isConfirmed) {
      const newValues = {
        m_kary_id: '',
        alasan_id: '',
        tipe_cuti_id: '',
        date_from: '',
        date_to: '',
        time_from: '',
        time_to: '',
        attachment: '',
        keterangan: '',
        interval: 0,
      };

      for (const key in newValues) {
        if (newValues.hasOwnProperty(key)) {
          values[key] = newValues[key];
        }
      }
    }
  })
}

function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
  console.log('kontol', modalOpen.value)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/${endpointApi}/log?id=${id}`
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

function formatDate(date) {
  const formattedDate = date.split('/')
  if (formattedDate.length > 1) {
    const year = formattedDate[2]
    const month = formattedDate[1]
    const day = formattedDate[0]
    return `${year}-${month}-${day}`
  } else {
    return date
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

function onSave() {
  try {
    // Format Tanggal Awal
    values.date_from = formatDate(values.date_from)
    // Format Tanggal Akhir
    if (values.tipe_string?.toLowerCase() === 'p24') {
      values.date_to = values.date_from
    } else {
      values.date_to = formatDate(values.date_to)
    }
    if (values.date_from && values.date_to && values.date_to < values.date_from) {
      isBadForm.value = true;
      swal.fire({
        icon: 'error',
        text: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal',
      });
      return;
    }

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);

    if (values.status === 'REVISED') {
      values.status = 'DRAFT'; // Change status to DRAFT if it was REVISED
    }

    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;

    isRequesting.value = true;

    fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
      body: JSON.stringify(values),
    })
      .then(async (res) => {
        if (!res.ok) {
          if ([400, 422].includes(res.status)) {
            const responseJson = await res.json();
            formErrors.value = responseJson.errors || {};
            throw new Error(responseJson.message || 'Failed when trying to post data');
          } else {
            throw new Error('Failed when trying to post data');
          }
        }
        router.replace('/' + modulPath + '?reload=' + Date.parse(new Date()));
      })
      .catch((err) => {
        isBadForm.value = true;
        swal.fire({
          icon: 'error',
          text: 'Harap Lengkapi Data',
        });
      })
      .finally(() => {
        isRequesting.value = false;
      });
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: 'Harap Lengkapi Data',
    });
    isRequesting.value = false;
  }
}


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
        const dataURL = `${store.server.url_backend}/operation/t_cuti/progress`;
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


//  @else----------------------- LANDING
// LANDING LAMA 
let data = reactive({})

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
    1: 'DRAFT',
    2: 'POSTED',
    3: 'IN APPROVAL',
    4: 'APPROVED',
    5: 'HALF APPROVED',
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

const landing = reactive({
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
              const dataURL = `${store.server.url_backend}/operation/t_cuti/posted`
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
    // {
    //   icon: 'location-arrow',
    //   title: "Approve HC",
    //   class: 'bg-rose-700 rounded-lg text-white',
    //   show: row => {
    //     const status = row.status?.toUpperCase()
    //     const isUserHC = store.user.data?.is_hc === true || store.user.data?.is_hc === 1
    //     const isStatusValid = status === 'HALF APPROVED'
    //     return isUserHC && isStatusValid && data.can_update
    //   },
    //   async click(row) {
    //     swal.fire({
    //       icon: 'warning',
    //       text: 'Full Approve?',
    //       iconColor: '#1469AE',
    //       confirmButtonColor: '#1469AE',

    //       showDenyButton: true
    //     }).then(async (res) => {
    //       if (res.isConfirmed) {
    //         try {
    //           const dataURL = `${store.server.url_backend}/operation/t_cuti/approveHC`
    //           isRequesting.value = true
    //           const res = await fetch(dataURL, {
    //             method: 'POST',
    //             headers: {
    //               'Content-Type': 'Application/json',
    //               Authorization: `${store.user.token_type} ${store.user.token}`
    //             },
    //             body: JSON.stringify({ id: row.id })
    //           })
    //           if (!res.ok) {
    //             if ([400, 422, 500].includes(res.status)) {
    //               const responseJson = await res.json()
    //               formErrors.value = responseJson.errors || {}
    //               throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
    //             } else {
    //               throw ("Failed when trying to post data")
    //             }
    //           }
    //           const responseJson = await res.json()
    //           swal.fire({
    //             icon: 'success',
    //             text: responseJson.message
    //           })
    //           // const resultJson = await res.json()
    //         } catch (err) {
    //           isBadForm.value = true
    //           swal.fire({
    //             icon: 'error',
    //             iconColor: '#1469AE',
    //             confirmButtonColor: '#1469AE',
    //             text: err
    //           })
    //         }
    //         isRequesting.value = false

    //         apiTable.value.reload()
    //       }
    //     })
    //   }
    // },
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
        // kary_id: store.user.data.m_kary_id ?? 0,
        join: true,
        transform: true,
        scopes: 'owndata,branch',
      }

      // const where = []

      // if (data.subcomp_id != null) {
      //   where.push(`m_kary.m_subcomp_id  = ${data.subcomp_id}`)
      // }

      // if (data.branch_id != null) {
      //   where.push(`m_kary.m_branch_id = ${data.branch_id}`)
      // }

      // if (where.length) {
      //   params.where = where.join(' AND ')
      // }

      return params
    }),
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
    field: 'm_kary.nama_lengkap',
    headerName: 'Nama Karyawan',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_branch.name',
    headerName: 'Cabang',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'alasan.value',
    headerName: 'Alasan',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'tipe_cuti.value',
    headerName: 'Tipe Cuti',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'date_from',
    headerName: 'Tanggal Awal',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'date_to',
    headerName: 'Tanggal Akhir',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
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