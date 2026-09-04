//   javascript
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watch, watchEffect, onActivated, computed } from 'vue'

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
const modalLogOpen = ref(false)
const dataLog = reactive({ items: [] })
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ DAFTAR KARYAWAN DIGANTIKAN (MULTI / BULK SELECT)
const jenisPermintaanList = ref([])
const detailKaryawanDigantikan = ref([])

const isReplacement = computed(() => {
  if (!values.jenis_permintaan_id) return false
  const item = jenisPermintaanList.value.find(i => String(i.id) === String(values.jenis_permintaan_id))
  if (!item) return false
  const str = (item.value || '').toLowerCase()
  return str.includes('penggantian') || str.includes('replacement') || str.includes('replace')
})

async function loadJenisPermintaan() {
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_general?where=this.group='JENIS PERMINTAAN KARYAWAN'&simplest=true&transform=false&join=false`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    if (res.ok) {
      const json = await res.json()
      jenisPermintaanList.value = json.data || []
    }
  } catch (e) {
    console.error(e)
  }
}

const onKaryawanAdd = (rows) => {
  if (!rows || !rows.length) return
  rows.forEach(row => {
    if (!detailKaryawanDigantikan.value.some(k => k.id === row.id)) {
      detailKaryawanDigantikan.value.push({
        id: row.id,
        kode: row.kode || '-',
        nama_lengkap: row.nama_lengkap || row.nama_depan || '-'
      })
    }
  })
  updateKaryawanDigantikanValues()
}

const removeKaryawanDigantikan = (index) => {
  detailKaryawanDigantikan.value.splice(index, 1)
  updateKaryawanDigantikanValues()
}

function updateKaryawanDigantikanValues() {
  if (detailKaryawanDigantikan.value.length === 0) {
    values.karyawan_digantikan_id = null
  } else {
    values.karyawan_digantikan_id = detailKaryawanDigantikan.value[0].id
  }
}

watch(() => values.jenis_permintaan_id, (newVal) => {
  if (!isReplacement.value) {
    values.karyawan_digantikan_id = null
    detailKaryawanDigantikan.value = []
  }
})

watch(() => values.m_divisi_id, (newVal, oldVal) => {
  if (oldVal !== undefined && newVal !== oldVal) {
    values.karyawan_digantikan_id = null
    detailKaryawanDigantikan.value = []
  }
})

// ------------------------------ PERSIAPAN
const endpointApi = '/t_req_recruitment'
onBeforeMount(() => {
  document.title = 'Permintaan Karyawan (FPTK)'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}

const values = reactive({
  nomor: null,
  tanggal: new Date().toISOString().slice(0, 10),
  m_kary_id: store.user.data?.m_kary_id || null,
  m_comp_id: null,
  m_subcomp_id: null,
  m_branch_id: null,
  m_divisi_id: null,
  m_dept_id: null,
  m_posisi_id: null,
  jumlah_kebutuhan: 1,
  status_kary_id: null,
  jenis_permintaan_id: null,
  karyawan_digantikan_id: null,
  tgl_dibutuhkan: null,
  prioritas_id: null,
  alasan: '',
  status: 'DRAFT',
  catatan_approval: ''
})

onBeforeMount(async () => {
  await loadJenisPermintaan()

  // Auto-fill SBU, Sub, Branch from localStorage respo
  const respoData = localStorage.getItem('respo')
  if (respoData) {
    try {
      const respoValues = JSON.parse(respoData)
      if (!isRead) {
        values.m_comp_id = respoValues.m_comp_id || null
        values.m_subcomp_id = respoValues.m_subcomp_id || null
        values.m_branch_id = respoValues.m_branch_id || null
      }
    } catch (e) {
      console.error(e)
    }
  }

  if (isRead) {
    // READ DATA
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
      if (!res.ok) throw new Error("Gagal membaca data pengajuan")
      const resultJson = await res.json()
      initialValues = resultJson.data

      for (const key in initialValues) {
        values[key] = initialValues[key]
      }

      if (values.karyawan_digantikan_id) {
        try {
          const resKary = await fetch(`${store.server.url_backend}/operation/m_kary/${values.karyawan_digantikan_id}?simplest=true&transform=false&join=false`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          })
          if (resKary.ok) {
            const jsonKary = await resKary.json()
            const k = jsonKary.data
            if (k && !detailKaryawanDigantikan.value.some(item => item.id === k.id)) {
              detailKaryawanDigantikan.value.push({
                id: k.id,
                kode: k.kode || '-',
                nama_lengkap: k.nama_lengkap || k.nama_depan || '-'
              })
            }
          }
        } catch (e) {
          console.error(e)
        }
      }
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
})

function onBack() {
  router.replace('/' + modulPath)
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset data form ini?',
    showDenyButton: true,
    confirmButtonText: 'Ya, Reset',
    denyButtonText: 'Batal'
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

async function onSave() {
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
        throw new Error(responseJson.message || (responseJson.errors?.length ? responseJson.errors[0] : "Gagal menyimpan data"))
      } else {
        throw new Error("Gagal menyimpan data")
      }
    }

    swal.fire({
      icon: 'success',
      text: 'Data berhasil disimpan!',
      timer: 1500,
      showConfirmButton: false
    })

    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
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

async function onSendApproval() {
  swal.fire({
    icon: 'warning',
    text: 'Kirim pengajuan ini untuk approval?',
    confirmButtonText: 'Ya, Kirim',
    showDenyButton: true,
    denyButtonText: 'Batal'
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
        isRequesting.value = true
        const response = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify({ id: route.params.id })
        })

        const resultJson = await response.json()
        if (!response.ok) {
          throw new Error(resultJson.message || "Gagal mengirim approval")
        }

        swal.fire({
          icon: 'success',
          text: resultJson.message || 'Approval berhasil diajukan!'
        })
        router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
      } catch (err) {
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

async function onProcess(type) {
  swal.fire({
    icon: 'warning',
    text: `Yakin ingin memproses status: ${type}?`,
    confirmButtonText: 'Ya, Proses',
    showDenyButton: true,
    denyButtonText: 'Batal'
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/progress`
        isRequesting.value = true
        const response = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify({
            id: route.params.id,
            type: type,
            note: values.catatan_approval
          })
        })

        const resultJson = await response.json()
        if (!response.ok) {
          throw new Error(resultJson.message || "Gagal memproses approval")
        }

        swal.fire({
          icon: 'success',
          text: resultJson.message || 'Status approval berhasil diperbarui!'
        })
        router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
      } catch (err) {
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

//  @else----------------------- LANDING
const activeBtn = ref()
const statusFilter = ref(null)

let data = reactive({
  respo_id: null,
  subcomp_id: null,
  branch_id: null,
  can_read: true,
  can_create: true,
  can_delete: true,
  can_update: true
})

onBeforeMount(async () => {
  const rs = localStorage.getItem('respo')
  if (rs) {
    const r = JSON.parse(rs)
    data.respo_id = r.id
    data.subcomp_id = r.m_subcomp_id
    data.branch_id = r.m_branch_id
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
        headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
      })
      const result = await response.json()
      if (result.can_read !== undefined) data.can_read = result.can_read
      if (result.can_create !== undefined) data.can_create = result.can_create
      if (result.can_delete !== undefined) data.can_delete = result.can_delete
      if (result.can_update !== undefined) data.can_update = result.can_update
    } catch (e) {
      console.error(e)
    }
  }
})

function filterShowData(statusLabel = null, noBtn = null) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
    statusFilter.value = null
  } else {
    activeBtn.value = noBtn
    statusFilter.value = statusLabel ? `this.status='${statusLabel}'` : null
  }
  apiTable.value?.reload()
}

function onStatusChange(e) {
  const val = e.target.value
  const statusMap = {
    '1': 'DRAFT',
    '2': 'IN APPROVAL',
    '3': 'APPROVED',
    '4': 'REJECTED'
  }
  if (val && statusMap[val]) {
    activeBtn.value = Number(val)
    statusFilter.value = `this.status='${statusMap[val]}'`
  } else {
    activeBtn.value = null
    statusFilter.value = null
  }
  apiTable.value?.reload()
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: () => data.can_delete,
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
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
              apiTable.value?.reload()
              swal.fire({
                icon: 'success',
                text: 'Data berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
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
        })
      }
    },
    {
      icon: 'eye',
      title: "Detail",
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
      show: (row) => data.can_update && ['DRAFT', 'REVISED'].includes(row.status?.toUpperCase()),
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Send Approval",
      class: 'bg-rose-700 text-white',
      show: (row) => data.can_update && ['DRAFT', 'REVISED'].includes(row.status?.toUpperCase()),
      click(row) {
        router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
      }
    },
    {
      icon: 'history',
      title: "Riwayat Approval",
      class: 'bg-purple-600 text-white',
      show: (row) => row.status?.toUpperCase() !== 'DRAFT',
      async click(row) {
        try {
          const dataURL = `${store.server.url_backend}/operation${endpointApi}/log?id=${row.id}`
          const res = await fetch(dataURL, {
            headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
          })
          if (res.ok) {
            const resJson = await res.json()
            dataLog.items = resJson.data || resJson || []
            modalLogOpen.value = true
          }
        } catch (e) {
          console.error(e)
        }
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: computed(() => ({
      paginate: 25,
      m_subcomp_id: data.subcomp_id,
      m_branch_id: data.branch_id,
      join: true,
      transform: true,
      scopes: 'respo',
      ...(statusFilter.value ? { where: statusFilter.value } : {})
    })),
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
      headerName: "Nomor",
      field: 'nomor',
      filter: 'ColFilter',
      sortable: true,
      flex: 1,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Tanggal",
      field: 'tanggal',
      filter: 'ColFilter',
      sortable: true,
      width: 120,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      headerName: "Pemohon",
      field: 'm_kary.nama_lengkap',
      valueGetter: (params) => params.data?.m_kary?.nama_lengkap || params.data?.['m_kary.nama_lengkap'] || '-',
      filter: 'ColFilter',
      sortable: true,
      flex: 1,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Divisi",
      field: 'm_divisi.name',
      valueGetter: (params) => params.data?.m_divisi?.name || params.data?.['m_divisi.name'] || '-',
      filter: 'ColFilter',
      sortable: true,
      flex: 1,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Posisi yang Diminta",
      field: 'm_posisi.name',
      valueGetter: (params) => params.data?.m_posisi?.name || params.data?.['m_posisi.name'] || '-',
      filter: 'ColFilter',
      sortable: true,
      flex: 1,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Kebutuhan",
      field: 'jumlah_kebutuhan',
      valueGetter: (params) => (params.data?.jumlah_kebutuhan || 1) + ' Orang',
      filter: 'ColFilter',
      sortable: true,
      width: 120,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      headerName: "Tgl Dibutuhkan",
      field: 'tgl_dibutuhkan',
      filter: 'ColFilter',
      sortable: true,
      width: 140,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      headerName: "Status",
      field: "status",
      sortable: true,
      filter: "ColFilter",
      resizable: true,
      width: 140,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: (params) => {
        const status = (params.value || '').toUpperCase()
        const colorMap = {
          'DRAFT': 'text-gray-700 bg-gray-100',
          'IN APPROVAL': 'text-amber-700 bg-amber-100',
          'APPROVED': 'text-green-700 bg-green-100',
          'REJECTED': 'text-red-700 bg-red-100',
          'REVISED': 'text-blue-700 bg-blue-100'
        }
        const colorClass = colorMap[status] || 'text-gray-700 bg-gray-100'
        return `
          <span class="px-2 py-1 rounded-md text-xs font-semibold ${colorClass}">
            ${params.value || 'DRAFT'}
          </span>
        `
      }
    }
  ]
})

onActivated(() => {
  if (apiTable.value && route.query.reload) {
    apiTable.value.reload()
  }
})

watch(
  () => route.query.reload,
  () => {
    apiTable.value?.reload()
  }
)

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))