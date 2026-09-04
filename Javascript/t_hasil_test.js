import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, computed, onBeforeMount, watchEffect, onActivated, watch } from 'vue'

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

// ------------------------------ PERSIAPAN
const endpointApi = '/t_hasil_tes'
onBeforeMount(() => {
  document.title = 'Transaksi Hasil Test'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: true,
  status: 'PENDING',
  //direktorat: store.user.data?.direktorat,
})

onBeforeMount(async () => {

  //values.direktorat = store.user.data?.direktorat

  if (isRead) {
    //  READ DATA
    try {
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
      detailArr.value = (initialValues.t_hasil_tes_det || []).map((items) => ({
        ...items,
        __id: ++_id,
      }))
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
const addDetail = () => {
  const tempItem = {
    __id: ++_id,
    tanggal: new Date().toISOString().slice(0, 10),
    nama_tes: null,
    nilai_tes: null,
    dokumen: null,
  }
  detailArr.value = [...detailArr.value, tempItem]
}

const removeDetail = (detailItem) => {
  detailArr.value = detailArr.value.filter((e) => e.__id != detailItem.__id)
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

const hitungTotalPerdin = computed(() => {
  let total = 0;

  detailArr.value.forEach((dt) => {
    total += parseFloat(dt.nominal) || 0;
  });

  values.total_biaya = total;
  return total
});


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
      detailArr.value = (initialValues.t_hasil_tes_det || []).map((items) => ({
        ...items,
        __id: ++_id,
      }))
    }
  })
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    //values.code = 1
    values.is_active = values.is_active ? true : false
    values.t_hasil_tes_det = detailArr.value
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    //console.log(values);
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
const filterButton = ref(null);

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'PENDING',
    2: 'PROSES',
    3: 'DITERIMA',
    4: 'TIDAK DITERIMA',
  }

  if (noBtn !== null) {
    if (activeBtn.value === noBtn) {
      activeBtn.value = null
      statusLabel = null
    } else {
      activeBtn.value = noBtn
      statusLabel = statusMap[noBtn] || statusLabel
    }
  } else if (statusLabel) {
    const entry = Object.entries(statusMap).find(([k, v]) => v.toUpperCase() === statusLabel.toUpperCase())
    activeBtn.value = entry ? Number(entry[0]) : null
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

function onStatusChange(e) {
  const val = e.target.value

  if (val !== "") {
    activeBtn.value = Number(val)
    filterShowData(null, Number(val))
  } else {
    activeBtn.value = null
    filterShowData(null, null)
  }
}

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
        title: "Hapus",
        show: () => data.can_delete,
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
        show: () => data.can_read,
        // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
        click(row) {
          router.push(`${route.path}/${row.id}?` + tsId)
        }
      },
      {
        icon: 'edit',
        title: "Edit",
        class: 'bg-blue-600 text-light-100',
        show: () => data.can_update,
        // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
        click(row) {
          router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
        }
      },
      {
        icon: 'copy',
        title: "Copy",
        class: 'bg-gray-600 text-light-100',
        show: () => data.can_create,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
        }
      },
      {
        icon: 'location-arrow',
        class: 'bg-rose-700 rounded-lg text-white',
        title: "Register Karyawan",
        show: (row) => ['DITERIMA', 'HIRED', 'OFFERING / TIDAK'].includes(row.status?.toUpperCase()),
        // show: () => store.user.data.username==='developer',
        click(row) {
          swal.fire({
            icon: 'warning',
            text: 'Register Karyawan?',
            confirmButtonText: 'Yes',
            showDenyButton: true,
          }).then(async (result) => {
            if (result.isConfirmed) {
              try {
                const dataURL = `${store.server.url_backend}/operation${endpointApi}/registerKary`
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
    ],
    api: {
       url: data.can_read
        ? `${store.server.url_backend}/operation${endpointApi}`
        : '',
      // url: `${store.server.url_backend}/operation${endpointApi}`,
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        searchfield: 'this.id, m_dir.nama, this.kode, m_divisi.nama, m_dept.nama, m_zona.nama, m_posisi.desc_kerja',
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
      headerName: "Kode",
      field: 'nomor',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Nama Pelamar",
      field: 't_pelamar.nama_depan',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Loker",
      field: 't_loker.title',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Tahapan",
      field: 'tahapan.value',
      valueGetter: (params) => params.data?.tahapan?.value || params.data?.['tahapan.value'] || params.data?.['m_general.value'] || params.data?.tahapan || '-',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Status",
      field: "status",
      sortable: true,
      filter: "ColFilter",
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start'],
      cellRenderer: (params) => {
        const status = (params.value || '').toUpperCase()

        const colorMap = {
          'PENDING': 'text-gray-700 bg-gray-100',
          'PROSES': 'text-blue-700 bg-blue-100',
          'DITERIMA': 'text-green-700 bg-green-100',
          'TIDAK DITERIMA': 'text-red-700 bg-red-100'
        }

        const colorClass = colorMap[status] || 'text-gray-700 bg-gray-100'

        return `
      <span class="px-2 py-1 rounded-md text-xs font-semibold ${colorClass}">
        ${params.value || '-'}
      </span>
    `
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

watch(
  () => route.query.reload,
  () => {
    if (apiTable.value) {
      apiTable.value.reload()
    }
  }
)

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))