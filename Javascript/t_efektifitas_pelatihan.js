import { useRouter, useRoute } from 'vue-router'
import { ref, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, computed, onActivated, watch, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

// ========================== STATE DASAR ==========================
const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const modulPath = route.params.modul
const apiTable = ref(null)
const formErrors = ref({})
const isRequesting = ref(false)
const isModalOpen = ref(false)
const activeBtn = ref()
const infoOutstanding = ref(null)
const endpointApi = 't_efektifitas_pelatihan'
const tsId = `ts=${Date.now()}`
const user = JSON.parse(localStorage.getItem('user'))
const showForm = ref([])
const currentMenu = store.currentMenu
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })

const today = new Date()
const dd = String(today.getDate()).padStart(2, '0')
const mm = String(today.getMonth() + 1).padStart(2, '0')
const yy = String(today.getFullYear()).slice(-2)

const values = reactive({
  tanggal: `${dd}/${mm}/${yy}`,
  m_kary_id: store.user.data.m_kary_id,
})

const detailArr = ref([])
const selectedSeq = ref([])
let seq = 1
let initialValues = {}

// ========================== HOOK ==========================
onBeforeMount(() => {
  document.title = 'Transaksi Efektifitas Pelatihan'
  if (isRead) loadData()
})

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown)
})

onActivated(() => {
  if (apiTable.value && route.query.reload) apiTable.value.reload()
})

// ========================== WATCH ==========================
watch(() => values.t_realisasi_pelatihan_id, (newVal) => {
  if (!newVal) {
    detailArr.value = []
  }
})

// ========================== FUNCTION ==========================

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

function handleKeyDown(e) {
  if (e?.ctrlKey && e?.key === 's') {
    e.preventDefault()
    onSave()
  }
}

function onBack() {
  router.replace('/' + modulPath)
}

async function onApproval() {
  const payload = {
    id: parseInt(route.params.id),
    target_id: values.target_id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}/send_approval`
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

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'HALF APPROVED'),
    note: values.catatan,
  };

  swal.fire({
    icon: 'warning',
    text: typePar === 'revise' ? 'Revised data?' : (typePar === 'reject' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/t_efektifitas_pelatihan/progress`;
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

// ========================== BUILD DETAIL (READ) ==========================
async function buildDetailArr(data, store) {
  if (!Array.isArray(data)) return []

  const baseURL = `${store.server.url_backend}/operation/m_general`
  const headers = {
    'Content-Type': 'application/json',
    Authorization: `${store.user.token_type} ${store.user.token}`
  }

  const groupedByKaryawan = {}
  const kategoriCache = {}

  for (const item of data) {
    if (!groupedByKaryawan[item.m_kary_id]) {
      groupedByKaryawan[item.m_kary_id] = {
        m_kary_id: item.m_kary_id,
        nama_lengkap: item['m_kary.nama_lengkap'],
        komponen: []
      }
    }

    const karyawan = groupedByKaryawan[item.m_kary_id]
    const namaKategori = item.komponen_efektifitas

    if (!kategoriCache[namaKategori]) {
      const res = await fetch(
        `${baseURL}?where=this.group='NILAI EFEKTIFITAS' and this.value_2='${namaKategori}'`,
        { headers }
      )
      const result = await res.json()
      kategoriCache[namaKategori] = (result.data || []).map(k => ({
        nama_komponen: k.value,
        nilai: Number(k.key)
      }))
    }

    const existingKategori = karyawan.komponen.find(k => k.nama_kategori === namaKategori)
    if (!existingKategori) {
      karyawan.komponen.push({
        nama_kategori: namaKategori,
        komponen: kategoriCache[namaKategori],
        selectedKomponen: item.nilai ?? null
      })
    } else {
      existingKategori.selectedKomponen = item.nilai ?? null
    }
  }

  const hasil = Object.values(groupedByKaryawan)
  console.log('buildDetailArr hasil (struktur sesuai Blade):', hasil)
  return hasil
}

// ========================== BUILD DETAIL (READ) END ==========================

const onDetailAdd = async (e) => {
  for (const row of e) {
    const newKaryawan = {
      sequence: seq++,
      m_kary_id: row.id,
      nama_lengkap: row.nama_lengkap,
      komponen: []
    }

    detailArr.value.push(newKaryawan)
    if (detailArr.value.length === 1) {
      showForm.value = [true]
    } else {
      showForm.value.push(false)
    }

    console.log('karyawan', newKaryawan)

    const index = detailArr.value.length - 1

    await loadTipePenilaianForKaryawan(index)
  }
}

const btnidx = (index) => {
  showForm.value[index] = !showForm.value[index];
  console.log('test', showForm.value)
};

const removeDetail = (targetSeq) => {
  const index = detailArr.value.findIndex(item => item.seq === targetSeq);
  if (index !== -1) {
    detailArr.value.splice(index, 1);
    showForm.value.splice(index, 1);
  }
};

// ========================== LOAD DATA ==========================
async function loadData() {
  try {
    isRequesting.value = true
    const editedId = route.params.id

    const headers = {
      'Content-Type': 'application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    }

    if (route.query.is_approval) {
      const apiApp = await fetch(
        `${store.server.url_backend}/operation/t_evaluasi_pelatihan/detail?id=${editedId}`,
        { headers }
      )

      if (!apiApp.ok) throw new Error('Failed when trying to read approval data')

      const resultJson = await apiApp.json()

      const apiTrx = await fetch(
        `${store.server.url_backend}/operation/${endpointApi}/${resultJson.data.approval.trx_id}`,
        { headers }
      )

      if (!apiTrx.ok) throw new Error('Failed when trying to read trx data')

      const resultTrxJson = await apiTrx.json()

      values.interval = resultJson.data.approval
      values.approval = resultJson.data.approval
      values.trx = resultJson.data.trx
      values.datalog = resultJson.data.approval_log

      initialValues = resultTrxJson.data

      const builtDetail = await buildDetailArr(
        initialValues.t_efektifitas_pelatihan_detail,
        store
      )

      detailArr.value = builtDetail.sort((a, b) => a.sequence - b.sequence)
      showForm.value = detailArr.value.map((_, idx) => idx === 0)

      for (const key in initialValues) values[key] = initialValues[key]

      selectedSeq.value = detailArr.value.map(item =>
        item.komponen.map(komp => komp.nilai ?? null)
      )
    } else {
      const res = await fetch(
        `${store.server.url_backend}/operation/${endpointApi}/${editedId}`,
        { headers }
      )

      if (!res.ok) throw new Error('Gagal membaca data')

      const result = await res.json()
      initialValues = result.data

      const builtDetail = await buildDetailArr(
        initialValues.t_efektifitas_pelatihan_detail,
        store
      )

      detailArr.value = builtDetail.sort((a, b) => a.sequence - b.sequence)
      showForm.value = detailArr.value.map((_, idx) => idx === 0)

      for (const key in initialValues) values[key] = initialValues[key]

      const resKary = await fetch(
        `${store.server.url_backend}/operation/m_kary/${initialValues['m_kary.id']}?simplest=true`,
        { headers }
      )

      const dataKary = await resKary.json()

      values.nama = dataKary.data?.nama_lengkap || ''
      values.penilaian = dataKary.data?.nama_lengkap || ''
      values.jabatan = dataKary.data?.jabatan || ''
    }
  } catch (err) {
    swal.fire({
      icon: 'error',
      text: err.message || err,
      confirmButtonText: 'Kembali'
    }).then(() => router.back())
  } finally {
    isRequesting.value = false
  }
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
  console.log('cek',result)
}

// ========================== LOAD MASTER ==========================
async function loadTipePenilaianForKaryawan(index) {
  try {
    const baseURL = `${store.server.url_backend}/operation/m_general`
    const headers = {
      'Content-Type': 'application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    }

    const resJenis = await fetch(`${baseURL}?where=this.group='KOMPONEN EFEKTIFITAS'`, { headers })
    const dataJenis = await resJenis.json()
    const listJenis = dataJenis.data || []

    const komponenPromises = listJenis.map(jenis =>
      fetch(`${baseURL}?where=this.group='NILAI EFEKTIFITAS' AND this.value_2='${jenis.value}'`, { headers })
        .then(res => res.json())
        .then(data => ({
          nama_kategori: jenis.value,
          komponen: (data.data || []).map(k => ({
            nama_komponen: k.value,
            nilai: Number(k.key)
          }))
        }))
    )

    const kategoriList = await Promise.all(komponenPromises)
    detailArr.value[index].komponen = kategoriList
  } catch (err) {
    console.error('Gagal load tipe penilaian:', err)
  }
}


// ========================== SAVE ==========================
async function onSave() {
  try {
    isRequesting.value = true
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const url = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : '/' + route.params.id}`

    const detailPayload = detailArr.value.flatMap(group =>
      group.komponen.map(komp => ({
        komponen_efektifitas: komp.nama_kategori,
        nilai: komp.selectedKomponen ?? null,
        m_kary_id: group.m_kary_id,
        sequence: group.sequence,
      }))
    )

    const payload = {
      ...values,
      status: 'DRAFT',
      t_efektifitas_pelatihan_detail: detailPayload
    }

    const res = await fetch(url, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    })

    const json = await res.json()
    if (!res.ok) throw new Error(json.message || 'Gagal simpan data')

    router.replace(`/${modulPath}?reload=${Date.now()}`)
  } catch (err) {
    swal.fire({ icon: 'warning', text: err.message || err })
  } finally {
    isRequesting.value = false
  }
}

// ========================== DELETE ==========================
async function deleteData(row) {
  try {
    const res = await swal.fire({
      icon: 'warning',
      text: 'Hapus Data Terpilih?',
      confirmButtonText: 'Yes',
      showDenyButton: true
    })
    if (!res.isConfirmed) return

    isRequesting.value = true
    const delRes = await fetch(`${store.server.url_backend}/operation/${endpointApi}/${row.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    if (!delRes.ok) throw new Error('Gagal hapus data')
    apiTable.value.reload()
  } catch (err) {
    swal.fire({ icon: 'error', text: err.message || err })
  } finally {
    isRequesting.value = false
  }
}

// ========================== LANDING TABLE ==========================

let data = reactive({
  respo_id: null,
  subcomp_id: null,
  branch_id: null,
  can_read: false,
  can_create: false,
  can_delete: false,
  can_update: false
})

// function openModal(id) {
//   dataLog.items = []
//   modalOpen.value = true
//   loadLog(id)
// }

// function closeModal(i) {
//   dataLog.items = []
//   modalOpen.value = false
// }


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


  landing.value.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}

const landing = computed(() => {
  if (!isAccessReady.value) return null
  return {
    actions: [
      {
        icon: 'trash',
        title: 'Hapus',
        class: 'bg-red-600 text-light-100',
        show: row => row.status && row.status.toUpperCase() === 'DRAFT' && data.can_delete,
        click: deleteData
      },
      {
        icon: 'eye',
        title: 'Read',
        class: 'bg-green-600 text-light-100',
        show: () => data.can_read,
        click: row => router.push(`${route.path}/${row.id}?${tsId}`)
      },
      {
        icon: 'edit',
        title: 'Edit',
        class: 'bg-blue-600 text-light-100',
        show: row => row.status && row.status.toUpperCase() === 'DRAFT' && data.can_update,
        click: row => router.push(`${route.path}/${row.id}?action=Edit&isKaryId=${row.m_kary_id}&${tsId}`)
      },
      {
        icon: 'paper-plane',
        title: "Send For Approval",
        class: 'bg-rose-700 rounded-lg text-white',
        show: (row) => row.status?.toUpperCase() === 'POSTED' && data.can_update,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
        }
      },
      {
        icon: 'paper-plane',
        title: 'Post Data',
        class: 'bg-blue-600 text-light-100',
        show: row => row.status && row.status.toUpperCase() === 'DRAFT' && data.can_update,
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
            const url = `${store.server.url_backend}/operation/${endpointApi}/posted`
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              body: JSON.stringify({ id: row.id })
            })
            const json = await res.json()
            if (!res.ok) throw (json.message || json.data?.errorText || 'Failed')
            swal.fire({ icon: 'success', text: json.message || 'Data berhasil diposting' })
            apiTable.value.reload()
          } catch (err) {
            isBadForm.value = true
            swal.fire({ icon: 'error', text: err, iconColor: '#1469AE', confirmButtonColor: '#1469AE' })
          } finally {
            isRequesting.value = false
          }
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
                const dataURL = `${store.server.url_backend}/operation/t_efektifitas_pelatihan/approveHC`
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
        icon: 'copy',
        title: 'Copy',
        class: 'bg-gray-600 text-light-100',
        show: row => row.status && row.status.toUpperCase() === 'DRAFT' && data.can_create,
        click: row => router.push(`${route.path}/${row.id}?action=Copy&${tsId}`)
      },
      {
        icon: 'table',
        title: "Log Approval",
        class: 'bg-gray-700 rounded-lg text-white',
        show: (row) => ['APPROVED', 'IN APPROVAL', 'HALF APPROVED'].includes(row['status']) && data.can_read,
        click(row) {
          openModal(row.id)
        }
      }
    ],
    api: {
      url: data.can_read ? `${store.server.url_backend}/operation/${endpointApi}` : null,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: { simplest: true, searchfield: 'this.nama, m_kary.nama_lengkap' },
      onsuccess(res) {
        res.page = res.current_page
        res.hasNext = res.has_next
        return res
      }
    },
    columns: [
      {
        headerName: 'No',
        valueGetter: p => p.node.rowIndex + 1,
        width: 60,
        cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
      },
      {
        headerName: 'Tema Pelatihan',
        field: 'm_prog_pelatihan.tema_pelatihan',
        flex: 1,
        filter: true,
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        headerName: 'Nama Trainer',
        field: 'trainer.nama_trainer',
        flex: 1,
        filter: true,
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        headerName: 'Tanggal',
        field: 'tanggal',
        flex: 1,
        filter: true,
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        headerName: 'Status',
        field: 'status',
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