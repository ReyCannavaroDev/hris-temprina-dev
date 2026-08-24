import { useRouter, useRoute } from 'vue-router'
import { ref, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, computed, onActivated, watch, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

// ========================== STATE DASAR ==========================
const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const modulPath = route.params.modul
const apiTable = ref(null)
const formErrors = ref({})
const isRequesting = ref(false)
const isModalOpen = ref(false)
const isBadForm = ref(false)
const activeBtn = ref()
const infoOutstanding = ref(null)
const endpointApi = 't_evaluasi_pelatihan'
const tsId = `ts=${Date.now()}`
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })

const user = JSON.parse(localStorage.getItem('user'))

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
let initialValues = {}

// ========================== HOOK ==========================
onBeforeMount(() => {
  document.title = 'Transaksi Evaluasi Pelatihan'
  if (!isRead) loadTipePenilaian()
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
watch(selectedSeq, (newVal) => {
  if (!newVal.length) return
  detailArr.value = detailArr.value.map((item, i) => ({
    ...item,
    komponen: item.komponen.map((komp, j) => ({
      ...komp,
      nilai: newVal[i]?.[j] ?? komp.nilai ?? null
    }))
  }))
}, { deep: true })

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))

// ========================== FUNCTION ==========================
function handleKeyDown(e) {
  if (e?.ctrlKey && e?.key === 's') {
    e.preventDefault()
    onSave()
  }
}

function onBack() {
  router.replace('/' + modulPath)
}

// ========================== BUILD DETAIL ==========================
function buildDetailArr(data) {
  if (!data || !Array.isArray(data)) return []
  const sorted = [...data].sort((a, b) => a.id - b.id)

  const grouped = Object.values(
    sorted.reduce((acc, item) => {
      if (!acc[item.jenis_evaluasi]) {
        acc[item.jenis_evaluasi] = {
          nama_kategori: item.jenis_evaluasi,
          komponen: []
        }
      }
      acc[item.jenis_evaluasi].komponen.push({
        id_detail: item.id,
        nama_komponen: item.komponen_evaluasi,
        nilai: item.nilai ?? null
      })
      return acc
    }, {})
  )

  return grouped
}

// ========================== Function Approval ==========================

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
        const dataURL = `${store.server.url_backend}/operation/t_evaluasi_pelatihan/progress`;
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

// ========================== LOAD DATA ==========================
async function loadData() {
  try {
    let dataURL = ''
    let dataURLAprv = ''
    let resAprv = ''
    isRequesting.value = true
    const editedId = route.params.id
    if (route.query.is_approval) {
      dataURLAprv = `${store.server.url_backend}/operation/t_evaluasi_pelatihan/detail?id=${route.params.id}`
      isRequesting.value = true
      const apiApp = await fetch(dataURLAprv, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      const resultJson = await apiApp.json()
      console.log(resultJson.data)
      const apiTrx = await fetch(`${store.server.url_backend}/operation/${endpointApi}/${resultJson.data.approval.trx_id}?transform=true&join=true`, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })

      if (!apiTrx.ok || !apiApp.ok) throw new Error('Failed when trying to read data')
      const resultTrxJson = await apiTrx.json()
      values.interval = resultJson.data.approval
      values.approval = resultJson.data.approval
      values.trx = resultJson.data.trx
      values.datalog = resultJson.data.approval_log
      initialValues = resultTrxJson.data

      detailArr.value = buildDetailArr(initialValues.t_evaluasi_pelatihan_detail)
      selectedSeq.value = detailArr.value.map(group =>
        group.komponen.map(komp => komp.nilai ?? null)
      )

      for (const key in initialValues) values[key] = initialValues[key]
    } else {
      const res = await fetch(`${store.server.url_backend}/operation/${endpointApi}/${editedId}?transform=true&join=true`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (!res.ok) throw new Error('Gagal membaca data')
      const result = await res.json()
      initialValues = result.data

      detailArr.value = buildDetailArr(initialValues.t_evaluasi_pelatihan_detail)
      selectedSeq.value = detailArr.value.map(group =>
        group.komponen.map(komp => komp.nilai ?? null)
      )

      for (const key in initialValues) values[key] = initialValues[key]

      const karyId = initialValues['m_kary.id'] || initialValues.m_kary_id
      if (karyId) {
        const resKary = await fetch(`${store.server.url_backend}/operation/m_kary/${karyId}?simplest=true`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        const dataKary = await resKary.json()
        values.nama = dataKary.data?.nama_lengkap || values['m_kary.nama_lengkap'] || ''
        values.penilaian = dataKary.data?.nama_lengkap || values['m_kary.nama_lengkap'] || ''
        values.jabatan = dataKary.data?.jabatan || ''
      }
    }
  } catch (err) {
    swal.fire({ icon: 'error', text: err.message || err, confirmButtonText: 'Kembali' }).then(() => router.back())
  } finally {
    isRequesting.value = false
  }
}

// ========================== LOAD MASTER ==========================
async function loadTipePenilaian() {
  try {
    const baseURL = `${store.server.url_backend}/operation/m_general`
    const headers = {
      'Content-Type': 'application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    }

    const resJenis = await fetch(`${baseURL}?where=this.group='JENIS EVALUASI'`, { headers })
    const dataJenis = await resJenis.json()
    const listJenis = dataJenis.data || []

    detailArr.value = []
    for (const jenis of listJenis) {
      const resKomponen = await fetch(`${baseURL}?where=this.group='KOMPONEN EVALUASI' AND this.value_2='${jenis.value}'`, { headers })
      const dataKomponen = await resKomponen.json()
      const listKomponen = dataKomponen.data || []

      detailArr.value.push({
        nama_kategori: jenis.value,
        komponen: listKomponen.map(k => ({
          nama_komponen: k.value,
          nilai: null
        }))
      })
    }

    selectedSeq.value = detailArr.value.map(item => item.komponen.map(() => null))
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
        jenis_evaluasi: group.nama_kategori,
        komponen_evaluasi: komp.nama_komponen,
        nilai: komp.nilai ?? null,
        id: komp.id_detail
      }))
    )

    const hasEmptyNilai = detailPayload.some(komp => komp.nilai === null || komp.nilai === '');
    if (hasEmptyNilai) {
      isRequesting.value = false;
      swal.fire({ icon: 'warning', text: 'Semua komponen penilaian wajib diisi!' });
      return;
    }

    const payload = {
      ...values,
      status: 'DRAFT',
      t_evaluasi_pelatihan_detail: detailPayload
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

const data = reactive({
  can_read: false,
  can_update: false,
  can_delete: false,
  can_create: false,
  respo_id: null
})

function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
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
  console.log('cek', result)
}


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

  const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`

  const res = await fetch(endpoint, {
    method: 'GET',
    headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
  })

  const r = await res.json()

  data.can_read = r.can_read
  data.can_create = r.can_create
  data.can_delete = r.can_delete
  data.can_update = r.can_update

  isAccessReady.value = true
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
      { icon: 'trash', class: 'bg-red-600 text-light-100', title: 'Hapus', show: row => ['DRAFT', 'REVISED'].includes(row.status) && data.can_delete, click: deleteData },
      { icon: 'eye', title: 'Read', class: 'bg-green-600 text-light-100', show: row => data.can_read, click: row => router.push(`${route.path}/${row.id}`) },
      { icon: 'edit', title: 'Edit', class: 'bg-blue-600 text-light-100', show: row => ['DRAFT', 'REVISED'].includes(row.status) && data.can_update, click: row => router.push(`${route.path}/${row.id}?action=Edit`) },
      { icon: 'copy', title: 'Copy', class: 'bg-gray-600 text-light-100', show: row => data.can_create && row.status === 'DRAFT', click: row => router.push(`${route.path}/${row.id}?action=Copy`) },
      {
        icon: 'paper-plane',
        title: 'Post Data',
        class: 'bg-gray-600 text-light-100',
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
      }, {
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
                const dataURL = `${store.server.url_backend}/operation/t_evaluasi_pelatihan/approveHC`
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
      params: { simplest: true, paginate: 25, transform: true, join: true, searchfield: 'm_trainer.nama_trainer, m_prog_pelatihan.tema_pelatihan' },
      onsuccess: r => ({ ...r, page: r.current_page, hasNext: r.has_next })
    },
    columns: [
      { headerName: 'No', valueGetter: p => p.node.rowIndex + 1, width: 60, cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200'] },
      { headerName: 'Tema Pelatihan', field: 'm_prog_pelatihan.tema_pelatihan', flex: 1, filter: true, sortable: true, cellClass: ['border-r', '!border-gray-200', 'justify-start'] },
      { headerName: 'Nama Trainer', field: 'trainer.nama_trainer', flex: 1, filter: true, sortable: true, cellClass: ['border-r', '!border-gray-200', 'justify-start'] },
      { headerName: 'Tanggal', field: 'tanggal', flex: 1, filter: true, sortable: true, cellClass: ['border-r', '!border-gray-200', 'justify-start'] },
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