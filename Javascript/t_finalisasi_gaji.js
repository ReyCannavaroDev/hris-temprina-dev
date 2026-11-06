//   javascript//   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

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
const detailArr = ref([])
const titleOpen = ref('')
const formErrors = ref({})
const modalOpen = ref(false)
const detailArrOpen = reactive({ items: [] })
const detailArrAdjOpen = reactive({ items: [] })
let objectOpen = reactive({ items: 0 })
let idxOpen = reactive({ value: null })
let totalAdjOpen = reactive({ value: 0 })
let totalAdjPPHOpen = reactive({ value: 0 })
let totalAdjFinalOpen = reactive({ value: 0 })
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/t_final_gaji'
onBeforeMount(() => {
  document.title = 'Finalisasi Gaji'
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
}


//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
let _id = 0
let thisMonth = new Date().toISOString().split('T')[0];
let tempYear = thisMonth.split('-')[0];
let tempMonth = thisMonth.split('-')[1];
let tempDayAwal = "01"; // Periode awal selalu tanggal 1
let tempDayAkhir = new Date(tempYear, tempMonth, 0).getDate();
const values = reactive({
  total_pengeluaran_gaji: 0,
  periode_awal: tempYear + '-' + tempMonth + '-1',
  periode_akhir: tempYear + '-' + tempMonth + '-1' 
})


function closeModal() {
  detailArrOpen.items = []
  modalOpen.value = false
}

function openDetail(i) {
  idxOpen.value = i
  detailArrAdjOpen.items = []
  objectOpen.items = detailArr.value[i]
  titleOpen.value = detailArr.value[i]['m_kary.nama_lengkap'] ?? '-' + ' - ' + detailArr.value[i]['m_kary.nik'] ?? ''
  let dataFormat = detailArr.value[i]?.detail_gaji?.slice() ?? detailArr.value[i].t_final_gaji_det_rincian?.sort((a, b) => a.seq - b.seq)

  // ambil dari detail_adj ketika sudah pernah memproses data adj
  detailArrOpen.items = dataFormat

  dataFormat = detailArr.value[i]?.detail_adj?.length ? detailArr.value[i]?.detail_adj : dataFormat
  dataFormat.filter(item => item.can_adjust == 1).forEach((v) => {
    const formattedValue = parseFloat(v.value);
    // Push a new object with the formatted value
    detailArrAdjOpen.items.push({
      ...v,
      default: v.default ?? true,
      type: v.type,
      value_ref: formattedValue,
      value: formattedValue
    });
  })

  summaryAdj()
  generatePPH(false)

  modalOpen.value = true
}

function addRowAdj() {
  detailArrAdjOpen.items.push({
    _id: ++_id,
    label: '',
    name: '',
    can_adjust: 1,
    default: false,
    factor: '+',
    type: 'Bulanan',
    value_ref: null,
    value: 0
  })
}

function summaryAdj() {
  totalAdjPPHOpen.value = []
  totalAdjOpen.value = 0
  detailArrAdjOpen.items.forEach((v) => {
    if (v.factor == '-') {
      totalAdjOpen.value -= Number(v.value ?? 0)
    } else if (v.factor == '+') {
      totalAdjOpen.value += Number(v.value ?? 0)
    }
  })

  totalAdjFinalOpen.value = totalAdjOpen.value
  summaryPengeluaranGaji()
}

function summaryPengeluaranGaji() {
  values.total_pengeluaran_gaji = detailArr.value.reduce((a, b) => {
    const netto = Number(b.netto);
    if (!isNaN(netto)) {
      return a + netto;
    } else {
      console.warn(`Skipping non-numeric value: ${b.netto}`);
      return a;
    }
  }, 0)
}

onBeforeMount(async () => {

  // if (localStorage.getItem('respo')) {
  //   const respoValues = await JSON.parse(localStorage.getItem('respo'))
  //   console.log('ini respo', respoValues)
  //   // values.comp_id = respoValues.m_comp_id
  //   values.sub = respoValues.m_subcomp_id
  //   values.m_branch_id = respoValues.m_branch_id
  // }

  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { transform: false }
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

      //Ambil hanya bagian YYYY-MM dari periode_awal dan periode_akhir
      // let tempYear2 = initialValues.periode_awal?.split('-')[0]
      // let tempMonth2 = initialValues.periode_awal?.split('-')[1]
      //  let tempDay2 = initialValues.periode_awal?.split('-')[2]
      // initialValues.periode_awal = `${tempYear2}-${tempMonth2}-${tempDay2}` // Hanya YYYY-MM

      // let tempYear3 = initialValues.periode_akhir?.split('-')[0]
      // let tempMonth3 = initialValues.periode_akhir?.split('-')[1]
      // let tempDay3 = initialValues.periode_akhir?.split('-')[2]
      // initialValues.periode_akhir = `${tempYear3}-${tempMonth3}-${tempDay3}` // Hanya YYYY-MM

      detailArr.value = initialValues.t_final_gaji_det?.sort((a, b) => a.id - b.id)
      detailArr.value.forEach((items) => {
        items.karyawan = items['m_kary.nama_lengkap']
      })
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

  // Mentransfer nilai ke values
  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})


function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
  // let isChanged = false
  // for (const key in initialValues) {
  //   if (values[key] !== initialValues[key]) {
  //     isChanged = true
  //     break;
  //   }
  // }

  // if (!isChanged) {
  //   router.replace('/' + modulPath)
  //   return
  // }

  // swal.fire({
  //   icon: 'warning',
  //   text: 'Buang semua perubahan dan kembali ke list data?',
  //   showDenyButton: true
  // }).then((res) => {
  //   if (res.isConfirmed) {
  //     router.replace('/' + modulPath)
  //   }
  // })
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


async function generatePPH(popup = true) {
  try {
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji/generatePPH`
    const params = {
      m_kary_id: objectOpen.items['m_kary_id'],
      netto: totalAdjOpen.value,
    }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      }
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

    const result = await res.json()
    console.log('result',result)
    if (result.length) {
      totalAdjPPHOpen.value = result
      totalAdjFinalOpen.value = totalAdjOpen.value - totalAdjPPHOpen.value[0]?.value ?? 0
    } else {
      totalAdjPPHOpen.value = 0;
      if (!popup) return
      swal.fire({
        icon: 'warning',
        text: 'Total Gaji berdasarkan jenis tanggungan dibawah standar minimun perhitungan PPH'
      })
    }

  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
}

function formatToStandardDate(dateStr) {
  if (!dateStr) return '';
  
  // Ubah "/" menjadi "-" terlebih dahulu (jaga-jaga)
  let cleanDate = String(dateStr).substring(0, 10).replace(/\//g, '-');
  
  // Jika formatnya DD-MM-YYYY (Karakter ke-3 adalah strip)
  if (cleanDate.charAt(2) === '-') {
    const parts = cleanDate.split('-'); // Hasil: ['22', '04', '2026']
    return `${parts[2]}-${parts[1]}-${parts[0]}`; // Balik jadi '2026-04-22'
  }
  
  // Jika sudah YYYY-MM-DD, biarkan saja
  return cleanDate; 
}

async function generatePerhitungan() {
  detailArr.value = []
  try {
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji`

    const startDate = formatToStandardDate(values.periode_awal);
    const endDate = formatToStandardDate(values.periode_akhir);
   
    const params = {
      scopes: 'GenerateForFinal',
      type_perhitungan: values.type_perhitungan,
      periode_awal: startDate,
      periode_akhir: endDate,
      // periode_awal: values.periode_awal,
      // periode_akhir: values.periode_akhir,
      // date_from: values.periode_awal,
      // date_to: values.periode_akhir,
      m_divisi_id: values.m_divisi_id,
      m_dept_id: values.m_dept_id,
      sub : values.sub,
      m_branch_id : values.m_branch_id,
      paginate: 9999
    }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      }
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

    const result = await res.json()
    const resultData = result.data

    resultData.forEach((item) => {
      item._id = ++_id
      item.t_perhitungan_gaji_id = item.id
      item.karyawan = item['m_kary.nama_lengkap']
      item.deskripsi = item['deskripsi']
      item.detail_gaji = item['detail_gaji']
    })

    detailArr.value = resultData
    summaryPengeluaranGaji()
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
}

function deleteRow(item) {
  detailArrAdjOpen.items = detailArrAdjOpen.items.filter((e) => e._id != item._id)
  summaryAdj()
  generatePPH(false)
}

// function saveModal() {
//   // idxOpen.value

//   console.log(detailArrAdjOpen.items)
//   detailArrAdjOpen.items = detailArrAdjOpen.items.concat(totalAdjPPHOpen.value)

//   // assign seq
//   // if(detailArrAdjOpen.items.length !== length){
//   //   detailArrAdjOpen.items.forEach((v,i)=>{
//   //     v.seq = i+1
//   //   })
//   // }
//   detailArrAdjOpen.items.forEach((v, i) => {
//     if (v == 0) {
//       return
//     }

//     v.seq = i + 1
//   })

//   // assign for subdetail
//   detailArr.value[idxOpen.value]['detail_adj'] = detailArrAdjOpen.items

//   detailArr.value[idxOpen.value]['total_tax'] = totalAdjPPHOpen.value.length ? totalAdjPPHOpen.value[0].value : 0
//   detailArr.value[idxOpen.value]['netto'] = totalAdjFinalOpen.value
//   generatePPH(false)
//   summaryPengeluaranGaji()
//   modalOpen.value = false
// }

function saveModal() {
  console.log(detailArrAdjOpen.items)

  let mergedItems = detailArrAdjOpen.items.concat(totalAdjPPHOpen.value)

  detailArrAdjOpen.items = mergedItems
    .filter(v => v !== 0 && typeof v === 'object')
    .map((v, i) => {
      v.seq = i + 1
      return v
    })

  detailArr.value[idxOpen.value]['detail_adj'] = detailArrAdjOpen.items

  let hasTaxData = Array.isArray(totalAdjPPHOpen.value) && totalAdjPPHOpen.value.length > 0;
  detailArr.value[idxOpen.value]['total_tax'] = hasTaxData ? totalAdjPPHOpen.value[0].value : 0;
  
  detailArr.value[idxOpen.value]['netto'] = totalAdjFinalOpen.value

  generatePPH(false)
  summaryPengeluaranGaji()
  modalOpen.value = false
}

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


async function onSave() {
  let dataSave = JSON.parse(JSON.stringify(values))
  
  // Format periode_awal
  if (values.periode_awal) {
    dataSave.periode_awal = formatToStandardDate(values.periode_awal);
  }

  // Format periode_akhir
  if (values.periode_akhir) {
    dataSave.periode_akhir = formatToStandardDate(values.periode_akhir);
  }

  // merging detail data
  detailArr.value.forEach((v) => {
    // transform key
    if (!v.detail_adj) {
      v.detail_adj = v.detail_gaji
    }
    v.detail_gaji?.forEach((d, i) => {
      d.seq = i + 1
    })
    // v.t_final_gaji_det_rincian = v.detail_gaji
    v.t_final_gaji_det_rincian = v.detail_adj
  })
  dataSave['t_final_gaji_det'] = detailArr.value

  try {

    isRequesting.value = true
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(dataSave)
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

  // fetch permission
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

const activeBtn = ref()

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'DRAFT',
    2: 'POSTED',
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

  landing.api.params.where = filters.length ? filters.join(' OR ') : null
  apiTable.value.reload()
}


const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_delete,
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
              const resultJson = await res.json()
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
      // click(row) {
      //   openDetailFromLanding(row)
      // }'
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_update ,
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'paper-plane',
      title: "Posted Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_update,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
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
      simplest: true,
      searchfield: 'this.nomor, this.desc, this.periode_awal, this.periode_akhir, this.total_pengeluaran_gaji, this.status',
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
    field: 'nomor',
    headerName: 'Nomor Generate',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'desc',
    headerName: 'Deskripsi Pendek',
    filter: true,
    sortable: true,
    wrapText: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'periode_awal',
    headerName: 'Periode Awal',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'periode_akhir',
    headerName: 'Periode Akhir',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'total_pengeluaran_gaji',
    headerName: 'Total Pengeluaran Gaji',
    filter: true,
    sortable: true,
    wrapText: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-end'],
    cellRenderer: ({ value }) => {
      return formatRupiah(value)
    }
  },
  {
    field: 'status',
    headerName: 'Status',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === 'DRAFT'
        ? `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
        : `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
    },
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