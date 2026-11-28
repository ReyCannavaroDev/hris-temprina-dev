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
const detailArr = ref([])
const titleOpen = ref('')
const formErrors = ref({})
const modalOpen = ref(false)
const detailArrOpen = reactive({ items: [] })
const detailArrAdjOpen = reactive({ items: [] })
let objectOpen = reactive({ items: 0 })
let idxOpen = reactive({ value: null })
let totalAdjOpen = reactive({ value: 0 })
let totalAdjFinalOpen = reactive({ value: 0 })
const tsId = `ts=` + (Date.parse(new Date()))
const isOsLocked = ref(false)

// ------------------------------ PERSIAPAN
const endpointApi = '/t_final_gaji'
onBeforeMount(() => {
  document.title = 'Finalisasi Gaji Outsourcing'
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount || 0);
}

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
let _id = 0
let thisMonth = new Date().toISOString().split('T')[0];
let tempYear = thisMonth.split('-')[0];
let tempMonth = thisMonth.split('-')[1];
let tempDayAwal = "01";
let tempDayAkhir = new Date(tempYear, tempMonth, 0).getDate();

const values = reactive({
  nomor: null,
  is_os: true,
  m_company_outsourcing_id: null,
  m_branch_id: null,
  total_pengeluaran_gaji: 0,
  desc: '',
  status: 'DRAFT',
  periode_awal: tempYear + '-' + tempMonth + '-01',
  periode_akhir: tempYear + '-' + tempMonth + '-' + tempDayAkhir 
})

const canEdit = computed(() => {
  if (!actionText.value) return false
  const status = values.status ? values.status.toUpperCase() : 'DRAFT'
  return status === 'DRAFT' || status === 'REJECTED'
})

function closeModal() {
  detailArrOpen.items = []
  modalOpen.value = false
}

function openDetail(i) {
  idxOpen.value = i
  detailArrAdjOpen.items = []
  objectOpen.items = detailArr.value[i]
  titleOpen.value = (detailArr.value[i]['m_kary.nama_lengkap'] || detailArr.value[i].karyawan || '-') + ' (' + (detailArr.value[i]['m_kary.nik'] || '') + ')'
  let dataFormat = detailArr.value[i]?.detail_gaji?.slice() || detailArr.value[i].t_final_gaji_det_rincian?.sort((a, b) => a.seq - b.seq) || []

  detailArrOpen.items = dataFormat

  dataFormat = detailArr.value[i]?.detail_adj?.length ? detailArr.value[i]?.detail_adj : dataFormat
  dataFormat.filter(item => item.can_adjust == 1).forEach((v) => {
    const formattedValue = parseFloat(v.value || 0);
    detailArrAdjOpen.items.push({
      ...v,
      default: v.default ?? true,
      type: v.type,
      value_ref: formattedValue,
      value: formattedValue
    });
  })

  summaryAdj()
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
    type: 'Harian',
    value_ref: null,
    value: 0
  })
}

function summaryAdj() {
  totalAdjOpen.value = 0
  detailArrAdjOpen.items.forEach((v) => {
    if (v.factor == '-') {
      totalAdjOpen.value -= Number(v.value || 0)
    } else if (v.factor == '+') {
      totalAdjOpen.value += Number(v.value || 0)
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
      return a;
    }
  }, 0)
}

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    try {
      const respoValues = JSON.parse(localStorage.getItem('respo'))
      if (respoValues.m_branch_id) {
        values.m_branch_id = respoValues.m_branch_id
      }
      const osId = respoValues.m_company_outsourcing_id || respoValues.m_os_id
      if (osId) {
        values.m_company_outsourcing_id = osId
        isOsLocked.value = true
      }
    } catch (e) {
      console.warn("Failed parsing respo in t_finalisasi_gaji_os:", e)
    }
  }

  if (isRead && currentMenu?.can_read) {
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { transform: false, scopes: 'os' }
      const fixedParams = new URLSearchParams(params)
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Gagal membaca data finalisasi gaji OS")
      const resultJson = await res.json()
      initialValues = resultJson.data

      detailArr.value = initialValues.t_final_gaji_det?.sort((a, b) => a.id - b.id) || []
      detailArr.value.forEach((items) => {
        items.karyawan = items['m_kary.nama_lengkap'] || items.karyawan
      })
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err.message || err,
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
  if (!values.status) {
    values.status = 'DRAFT'
  }
  values.is_os = true
})

function onBack() {
  router.replace('/' + modulPath)
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset data form ini?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

function formatToStandardDate(dateStr) {
  if (!dateStr) return '';
  let cleanDate = String(dateStr).substring(0, 10).replace(/\//g, '-');
  if (cleanDate.charAt(2) === '-') {
    const parts = cleanDate.split('-');
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
  }
  return cleanDate; 
}

async function generatePerhitunganOs() {
  if (!values.periode_awal || !values.periode_akhir) {
    swal.fire({
      icon: 'warning',
      text: 'Harap tentukan Periode Awal dan Periode Akhir terlebih dahulu.'
    })
    return
  }

  detailArr.value = []
  isRequesting.value = true
  try {
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji`

    const startDate = formatToStandardDate(values.periode_awal);
    const endDate = formatToStandardDate(values.periode_akhir);
   
    const params = {
      scopes: 'GenerateForFinalOs',
      periode_awal: startDate,
      periode_akhir: endDate,
      m_company_outsourcing_id: values.m_company_outsourcing_id || '',
      m_branch_id: values.m_branch_id || '',
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
      const responseJson = await res.json()
      formErrors.value = responseJson.errors || {}
      throw new Error(responseJson.message || "Gagal menarik data gaji OS")
    }

    const result = await res.json()
    const resultData = result.data || []

    if (resultData.length === 0) {
      swal.fire({
        icon: 'info',
        text: 'Tidak ada data perhitungan gaji OS yang ditemukan pada periode dan filter terpilih.'
      })
    }

    resultData.forEach((item) => {
      item._id = ++_id
      item.t_perhitungan_gaji_id = item.id
      item.karyawan = item['m_kary.nama_lengkap'] || item.karyawan
      item.deskripsi = item['deskripsi'] || ''
      item.detail_gaji = item['detail_gaji'] || []
    })

    detailArr.value = resultData
    summaryPengeluaranGaji()
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err.message || err
    })
  } finally {
    isRequesting.value = false
  }
}

function deleteRow(item) {
  detailArrAdjOpen.items = detailArrAdjOpen.items.filter((e) => e._id != item._id)
  summaryAdj()
}

function saveModal() {
  detailArrAdjOpen.items = detailArrAdjOpen.items
    .filter(v => v !== 0 && typeof v === 'object')
    .map((v, i) => {
      v.seq = i + 1
      return v
    })

  if (idxOpen.value !== null && detailArr.value[idxOpen.value]) {
    detailArr.value[idxOpen.value]['detail_adj'] = detailArrAdjOpen.items
    detailArr.value[idxOpen.value]['netto'] = totalAdjFinalOpen.value
  }

  summaryPengeluaranGaji()
  modalOpen.value = false
}

async function onSave() {
  if (!values.periode_awal || !values.periode_akhir) {
    swal.fire({
      icon: 'warning',
      text: 'Periode Awal dan Akhir wajib diisi.'
    })
    return
  }

  if (!values.desc) {
    swal.fire({
      icon: 'warning',
      text: 'Deskripsi / Catatan Pengajuan wajib diisi.'
    })
    return
  }

  if (!detailArr.value.length) {
    swal.fire({
      icon: 'warning',
      text: 'Rincian karyawan OS masih kosong. Harap Tarik Data Gaji OS sebelum menyimpan.'
    })
    return
  }

  let dataSave = JSON.parse(JSON.stringify(values))
  dataSave.is_os = true
  dataSave.periode_awal = formatToStandardDate(values.periode_awal);
  dataSave.periode_akhir = formatToStandardDate(values.periode_akhir);

  detailArr.value.forEach((v) => {
    if (!v.detail_adj) {
      v.detail_adj = v.detail_gaji
    }
    v.detail_gaji?.forEach((d, i) => {
      d.seq = i + 1
    })
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
      const responseJson = await res.json()
      formErrors.value = responseJson.errors || {}
      throw new Error(responseJson.message || "Gagal menyimpan data finalisasi gaji OS")
    }

    swal.fire({
      icon: 'success',
      text: 'Data Finalisasi Gaji Outsourcing berhasil disimpan sebagai DRAFT.',
      timer: 1500
    }).then(() => {
      router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
    })
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err.message || err
    })
  } finally {
    isRequesting.value = false
  }
}

async function onSendApproval(rowParam = null) {
  const targetId = rowParam?.id || values.id
  if (!targetId) {
    swal.fire({
      icon: 'warning',
      text: 'Simpan data draft terlebih dahulu sebelum mengajukan approval.'
    })
    return
  }

  const confirm = await swal.fire({
    icon: 'question',
    title: 'Ajukan Approval HC Cabang?',
    text: 'Data finalisasi gaji OS akan dikirim ke HC Cabang untuk persetujuan dan terkunci dari perubahan.',
    showCancelButton: true,
    confirmButtonText: 'Ya, Ajukan Approval',
    cancelButtonText: 'Batal'
  })

  if (!confirm.isConfirmed) return

  isRequesting.value = true
  try {
    const res = await fetch(`${store.server.url_backend}/operation${endpointApi}/send_approval?id=${targetId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    const result = await res.json()
    if (!res.ok) {
      throw new Error(result.message || 'Gagal mengajukan approval')
    }

    await swal.fire({
      icon: 'success',
      title: 'Berhasil Diajukan',
      text: result.message || 'Pengajuan approval finalisasi gaji OS berhasil dikirim ke HC Cabang',
      timer: 2000
    })

    if (apiTable.value) {
      apiTable.value.reload()
    } else {
      router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
    }
  } catch (err) {
    swal.fire({
      icon: 'error',
      title: 'Pengajuan Gagal',
      text: err.message || err
    })
  } finally {
    isRequesting.value = false
  }
}

//  @else----------------------- LANDING

let data = reactive({})
onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    try {
      const respoValues = JSON.parse(localStorage.getItem('respo'))
      data.respo_id = respoValues.id
      data.subcomp_id = respoValues.m_subcomp_id
      data.branch_id = respoValues.m_branch_id
      data.m_company_outsourcing_id = respoValues.m_company_outsourcing_id || respoValues.m_os_id
    } catch (e) {
      console.warn("Failed reading respo on landing:", e)
    }
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
    } catch (err) {
      console.error(err)
    }
  }
})

const activeBtn = ref()

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'DRAFT',
    2: 'IN_APPROVAL',
    3: 'POSTED',
    4: 'REJECTED'
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
    if (statusLabel === 'IN_APPROVAL') {
      filters.push(`(this.status='IN_APPROVAL' OR this.status='IN APPROVAL')`)
    } else {
      filters.push(`this.status='${statusLabel.toUpperCase()}'`)
    }
  }

  landing.api.params.where = filters.length ? filters.join(' AND ') : null
  if (apiTable.value) {
    apiTable.value.reload()
  }
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => ['DRAFT', 'REJECTED'].includes(row.status?.toUpperCase()) && data.can_delete,
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus data finalisasi gaji OS ini?',
          confirmButtonText: 'Ya, Hapus',
          showDenyButton: true,
          denyButtonText: 'Batal'
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
                throw new Error(resultJson.message || "Gagal menghapus data")
              }
              apiTable.value.reload()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                text: err.message || err
              })
            } finally {
              isRequesting.value = false
            }
          }
        })
      }
    },
    {
      icon: 'eye',
      title: "Lihat Rincian",
      class: 'bg-green-600 text-light-100',
      show: () => data.can_read,
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => ['DRAFT', 'REJECTED'].includes(row.status?.toUpperCase()) && data.can_update,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'paper-plane',
      title: "Ajukan Approval",
      class: 'bg-amber-600 rounded-lg text-white',
      show: (row) => ['DRAFT', 'REJECTED'].includes(row.status?.toUpperCase()) && data.can_update,
      click(row) {
        onSendApproval(row)
      }
    }
  ],
  api: {
    url: currentMenu?.can_read
         ? `${store.server.url_backend}/operation${endpointApi}` 
         : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      scopes: 'os',
      searchfield: 'this.nomor, this.desc, this.periode_awal, this.periode_akhir, this.total_pengeluaran_gaji, this.status, m_company_outsourcing.name, m_branch.name',
    },
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  },
  columns: [
    {
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
      cellClass: ['border-r', '!border-gray-200', 'justify-start', 'font-mono', 'text-xs']
    },
    {
      field: 'm_company_outsourcing.name',
      headerName: 'PT Outsourcing',
      filter: true,
      sortable: true,
      wrapText: true,
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
      wrapText: true,
      width: 140,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      field: 'periode_awal',
      headerName: 'Periode Awal',
      filter: true,
      sortable: true,
      width: 130,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'periode_akhir',
      headerName: 'Periode Akhir',
      filter: true,
      sortable: true,
      width: 130,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      field: 'total_pengeluaran_gaji',
      headerName: 'Total Pengeluaran Gaji OS',
      filter: true,
      sortable: true,
      wrapText: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-end', 'font-semibold'],
      cellRenderer: ({ value }) => {
        return formatRupiah(value)
      }
    },
    {
      field: 'status',
      headerName: 'Status',
      filter: true,
      sortable: true,
      width: 140,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: (p) => {
        const st = p.value ? p.value.toUpperCase() : 'DRAFT'
        let badgeClass = 'bg-gray-100 text-gray-700 border-gray-300'
        if (st === 'POSTED') badgeClass = 'bg-green-100 text-green-700 border-green-300 font-semibold'
        else if (st === 'IN_APPROVAL' || st === 'IN APPROVAL') badgeClass = 'bg-blue-100 text-blue-700 border-blue-300'
        else if (st === 'REJECTED') badgeClass = 'bg-red-100 text-red-700 border-red-300'
        return `<span class="px-2.5 py-0.5 rounded-full text-xs border ${badgeClass}">${st}</span>`
      }
    }
  ]
})

onActivated(() => {
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
