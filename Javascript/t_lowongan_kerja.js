//   javascript
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
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
let modalOpen = ref(false)
let modalOpenHistory = ref(false)
let modalOpenHistoryStock = ref(false)
let modalOpenShipping = ref(false)
const tsId = `ts=` + (Date.parse(new Date()))
const is_approval = route.query.is_approval ? true : false
let isApproved = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })

// ------------------------------ PERSIAPAN
const endpointApi = '/t_loker'
onBeforeMount(() => {
  document.title = 'Lowongan Kerja'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  deskripsi: "",
  t_loker_d_kualifikasi: [
    { value: '' }
  ],
})

onBeforeMount(async () => {
  if (!isRead || !currentMenu?.can_read) return;

  isRequesting.value = true;

  try {
    let initialValues = {};
    let resultJson = null;

    // Jika halaman approval
    if (route.query.is_approval) {
      const dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/app_detail?id=${route.params.id}`;

      const apiApp = await fetch(dataURLAprv, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!apiApp.ok) throw new Error("Gagal membaca data approval");

      resultJson = await apiApp.json();

      const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!apiTrx.ok) throw new Error("Gagal membaca transaksi");

      const resultTrxJson = await apiTrx.json();

      values.interval = resultJson.data.approval;
      values.approval = resultJson.data.approval;
      values.trx = resultJson.data.trx;
      values.datalog = resultJson.data.approval_log;

      initialValues = resultTrxJson.data;

      isApproved.value = initialValues.cuti_status === 'APPROVED';
      isFinish.value = resultJson.data.approval.tahap_saat_ini === resultJson.data.approval.tahap_total;

    } else {
      // Halaman normal (bukan approval)
      const editedId = route.params.id;
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`;
      const params = { join: false, transform: false };
      const fixedParams = new URLSearchParams(params);

      const res = await fetch(`${dataURL}?${fixedParams}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!res.ok) throw new Error("Gagal membaca data");

      const result = await res.json();
      initialValues = result.data;
    }

    // Assign ke reactive values
    for (const key in initialValues) {
      values[key] = initialValues[key];
    }

  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err.message || err,
      allowOutsideClick: false,
      confirmButtonText: 'Kembali',
    }).then(() => {
      router.back();
    });
  } finally {
    isRequesting.value = false;
  }
});

// onBeforeMount(async () => {
//   if (isRead) {
//     //  READ DATA
//     try {
//       const editedId = route.params.id
//       const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
//       isRequesting.value = true

//       const params = { join: false, transform: false }
//       const fixedParams = new URLSearchParams(params)
//       const res = await fetch(dataURL + '?' + fixedParams, {
//         headers: {
//           'Content-Type': 'Application/json',
//           Authorization: `${store.user.token_type} ${store.user.token}`
//         },
//       })
//       if (!res.ok) throw new Error("Failed when trying to read data")
//       const resultJson = await res.json()
//       initialValues = resultJson.data
//       const resData = resultJson.data
//     } catch (err) {
//       isBadForm.value = true
//       swal.fire({
//         icon: 'error',
//         text: err,
//         allowOutsideClick: false,
//         confirmButtonText: 'Kembali',
//       }).then(() => {
//         router.back()
//       })
//     }
//     isRequesting.value = false
//   }

//   for (const key in initialValues) {
//     values[key] = initialValues[key]
//   }
// })

var bln = [];
var Bulan = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];
for (let nama = 1; nama <= 12; nama++) {
  bln.push(Bulan[nama - 1]);
}

var periode = [];
for (let prd = 1; prd <= 500; prd++) {
  periode.push(prd.toString())
}

var scp = [];
for (let tahun = 2000; tahun <= 2100; tahun++) {
  scp.push(tahun.toString());
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

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset this form data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      values.t_loker_d_kualifikasi = [{ value: '' }];
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

async function onSave() {
  // values.tags = JSON.stringify(values.tags)
  // swal.fire({
  //   icon: 'warning',
  //   text: 'Save data?',
  //   showDenyButton: true
  // }).then(async (res) => {
  //   if (res.isConfirmed) {

  // })
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
        throw new Error(responseJson.message || "Failed when trying to post data")
      } else {
        throw new Error("Failed when trying to post data")
      }
    }
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err.message
    })
  }
  isRequesting.value = false
}

//  @else----------------------- LANDING
const activeBtn = ref()
let data = reactive({})

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    // console.log('ini respo coi', respoValues)
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
    data.respo_id = respoValues.id
  }
  console.log('ini dta', data)

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

function filterShowData(params, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }
  if (params) {
    landing.api.params.where = `this.is_active=true`
  } else if (activeBtn.value == null) {
    // clear params filter
    landing.api.params.where = null
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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
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
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/approveHC`
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
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      class: 'bg-rose-700 rounded-lg text-white',
      title: "Post Data",
      show: (row) => row.status?.toUpperCase() === 'DRAFT',
      // show: () => store.user.data.username==='developer',
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Posting Data?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/posted`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: JSON.stringify({ id: row.id })
              })
              if (!res.ok) throw new Error("Failed when trying to post data")
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
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    // params: {
    //   simplest: true,
    //   scopes: 'respo',
    //   m_subcomp_id: data.subcomp_id,
    //   m_branch_id: data.branch_id,
    //   searchfield:`this.nomor, m_dept.nama, jenis_loker.value, this.tgl_dibuka, this.tgl_akhir, this.status`
    // },
    params: computed(() => ({
      paginate: 25,
      m_subcomp_id: data.subcomp_id,
      m_branch_id: data.branch_id,
      join: true,
      transform: true,
      scopes: 'respo'
    })),

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
    headerName: 'Nomor',
    field: 'nomor',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Cabang',
    field: 'm_branch.name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Nama Loker',
    field: "title",
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Jenis Status',
    field: "status_kary.value",
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Tanggal Dibuka',
    field: 'tgl_dibuka',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    headerName: 'Tanggal Akhir',
    field: 'tgl_akhir',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
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