import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated, computed, watch } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

// ------------------------------ PERSIAPAN
const userLocal = JSON.parse(localStorage.getItem('user') || '{}')
const loggedInUser = computed(() => store.user?.data || userLocal?.data || {})
const isUserOS = computed(() => !!loggedInUser.value?.m_os_id)
const userOsId = computed(() => loggedInUser.value?.m_os_id || null)

// Cache PT Outsource map for landing column without join
const osMap = reactive({})
const fetchCompanyOs = async () => {
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_company_outsourcing?simplest=true&paginate=100`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    if (res.ok) {
      const json = await res.json()
      const list = json.data || []
      list.forEach(item => {
        osMap[item.id] = item.name
      })
    }
  } catch (e) {}
}

// Active respo tracking (SBU / Comp, SUB / Subcomp, Cabang / Branch)
const activeRespoData = reactive({
  comp_id: null,
  subcomp_id: null,
  branch_id: null,
  id: null
})

const reloadActiveRespo = async () => {
  try {
    let stored = localStorage.getItem('respo')
    let r = stored ? JSON.parse(stored) : null
    if (!r || !r.id) {
      const res = await fetch(`${store.server.url_backend}/operation/m_respo/main_respo`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (res.ok) {
        const json = await res.json()
        r = json.data || json
        if (r && r.name) {
          localStorage.setItem('respo', JSON.stringify(r))
        }
      }
    }
    if (r) {
      let comp = r.m_comp_id ?? r.comp_id ?? null
      let sub = r.m_subcomp_id ?? r.subcomp_id ?? null
      let branch = r.m_branch_id ?? r.branch_id ?? null

      activeRespoData.comp_id = Array.isArray(comp) ? comp[0] : (typeof comp === 'string' && comp.startsWith('[') ? JSON.parse(comp)[0] : comp)
      activeRespoData.subcomp_id = Array.isArray(sub) ? sub[0] : (typeof sub === 'string' && sub.startsWith('[') ? JSON.parse(sub)[0] : sub)
      activeRespoData.branch_id = Array.isArray(branch) ? branch[0] : (typeof branch === 'string' && branch.startsWith('[') ? JSON.parse(branch)[0] : branch)
      activeRespoData.id = r.id ?? r.m_respo_id ?? null
    }
  } catch (e) {
    console.error('Error reading respo from localStorage:', e)
  }
}

// Table Detail
const detailArr = ref([])

// Selected Karyawan tracking for SBU, SUB, Cabang
const selectedKaryData = reactive({
  m_comp_id: null,
  m_subcomp_id: null,
  m_branch_id: null
})

const onKarySelect = (kary) => {
  if (kary) {
    selectedKaryData.m_comp_id = kary.m_comp_id ?? null
    selectedKaryData.m_subcomp_id = kary.m_subcomp_id ?? null
    selectedKaryData.m_branch_id = kary.m_branch_id ?? null
    if (kary.nama_lengkap) {
      values.name = kary.nama_lengkap
    }
    // Mengikuti karyawan yang dipilih: jika OS diisi ID OS, jika internal/kosong direset ke null
    values.m_os_id = kary.m_company_outsourcing_id ?? null
  } else {
    selectedKaryData.m_comp_id = null
    selectedKaryData.m_subcomp_id = null
    selectedKaryData.m_branch_id = null
    values.m_os_id = null
  }
}

// Popup Karyawan: Mengikuti respo yang aktif (SBU, SUB, Cabang)
const apiKaryPopup = computed(() => {
  const params = {
    simplest: true,
    transform: false,
    join: false,
    scopes: 'respo',
    searchfield: 'this.kode,this.nama_lengkap'
  }

  const conds = ['this.is_active=true']
  if (activeRespoData.comp_id) {
    conds.push(`this.m_comp_id=${activeRespoData.comp_id}`)
  }

  if (activeRespoData.subcomp_id) {
    params.m_subcomp_id = activeRespoData.subcomp_id
  }
  if (activeRespoData.branch_id) {
    params.m_branch_id = activeRespoData.branch_id
  }

  params.where = conds.join(' and ')

  return {
    url: `${store.server.url_backend}/operation/m_kary`,
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
    params,
    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  }
})

const effectiveCompId = computed(() => {
  return selectedKaryData.m_comp_id || (detailArr.value[0]?.m_comp_id) || activeRespoData.comp_id || null
})

const effectiveSubcompId = computed(() => {
  return selectedKaryData.m_subcomp_id || (detailArr.value[0]?.m_subcomp_id) || activeRespoData.subcomp_id || null
})

const effectiveBranchId = computed(() => {
  return selectedKaryData.m_branch_id || (detailArr.value[0]?.m_branch_id) || activeRespoData.branch_id || null
})

const apiRespoMultiSelect = computed(() => {
  const conds = ['this.is_active=true']

  const branchId = effectiveBranchId.value
  const subcompId = effectiveSubcompId.value
  const compId = effectiveCompId.value

  if (compId) {
    conds.push(`this.m_comp_id=${compId}`)
  }
  if (subcompId) {
    conds.push(`(this.m_subcomp_id=${subcompId} or this.m_subcomp_id is null)`)
  }
  if (branchId) {
    conds.push(`(this.m_branch_id=${branchId} or this.m_branch_id is null)`)
  }

  const whereClause = conds.join(' and ')

  const notinClause = detailArr.value.length > 0
    ? `m_respo.id:${detailArr.value.map(dt => dt.m_respo_id).filter(Boolean).join(',')}`
    : null

  return {
    url: `${store.server.url_backend}/operation/m_respo`,
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: false,
      transform: false,
      join: true,
      override: true,
      where: whereClause,
      notin: notinClause
    },
    onsuccess: (response) => {
      response.data = [...(response.data || [])].map((dt) => {
        Object.keys(dt).forEach(k => dt['m_respo.' + k] = dt[k])
        dt['m_respo_id'] = dt.id
        dt['m_comp_id'] = dt['m_comp_id']
        dt['m_subcomp_id'] = dt['m_subcomp_id']
        dt['m_branch_id'] = dt['m_branch_id']
        return dt
      })
      response.page = response.current_page
      response.hasNext = response.has_next
      return response
    }
  }
})

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

const endpointApi = '/default_users'
onBeforeMount(() => {
  document.title = 'Master Pengguna'
  reloadActiveRespo()
  fetchCompanyOs()
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

/// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []

let values = reactive({
  is_active: true,
  name: null,
  username: null,
  email: null,
  user_type: null,
  password: null

})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = () => {
  values.is_active = true
  values.is_hc = false
  values.m_os_id = null
  selectedKaryData.m_comp_id = null
  selectedKaryData.m_subcomp_id = null
  selectedKaryData.m_branch_id = null
}

const isValidEmail = ref(true)
function validateEmail(v) {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v)) {
    formErrors.value["email"] = null
    isValidEmail.value = true
  } else {
    formErrors.value["email"] = ['Please enter a valid email address']
    isValidEmail.value = false
  }
  values.email = v
}

const onReset = async (alert = false) => {
  let next = false
  if (alert) {
    swal.fire({
      icon: 'warning',
      text: 'Anda yakin akan mereset data ini?',
      showDenyButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        if (isRead) {
          for (const key in initialValues) {
            values[key] = initialValues[key]
          }
        } else {
          for (const key in values) {
            delete values[key]
          }
          defaultValues()
        }
      }
    })
  }

  setTimeout(() => {
    defaultValues()
  }, 100)
}

// Table Detail
const addDetail = () => {
  const tempItem = {

  }
  detailArr.value = [...detailArr.value, tempItem]
}
const changePrimaryDetail = (index) => {
  detailArr.value?.forEach((item, i) => {
    if (i !== index) {
      item.is_primary = false;
    }
  })
}
const onDetailAdd = (e) => {
  e.forEach(row => {
    row.is_active = true
    detailArr.value.push(row)
  })
}
const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
  // detailArr.value = detailArr.value.filter((e) => e.__id != index.__id)
}
// End Table Detail

watch(() => values.m_kary_id, async (newVal) => {
  if (newVal) {
    try {
      const res = await fetch(`${store.server.url_backend}/operation/m_kary/${newVal}?simplest=true`, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })
      if (res.ok) {
        const json = await res.json()
        const k = json.data
        if (k) {
          selectedKaryData.m_comp_id = k.m_comp_id ?? null
          selectedKaryData.m_subcomp_id = k.m_subcomp_id ?? null
          selectedKaryData.m_branch_id = k.m_branch_id ?? null
          if (!values.name && k.nama_lengkap) {
            values.name = k.nama_lengkap
          }
          // Mengikuti karyawan yang dipilih: jika OS diisi ID OS, jika internal/kosong direset ke null
          values.m_os_id = k.m_company_outsourcing_id ?? null
        }
      }
    } catch (e) {}
  } else {
    selectedKaryData.m_comp_id = null
    selectedKaryData.m_subcomp_id = null
    selectedKaryData.m_branch_id = null
    values.m_os_id = null
  }
})

onBeforeMount(async () => {
  onReset(); // Reset form ke kondisi awal

  if (isRead && currentMenu?.can_read) {
    // READ DATA
    try {
      const editedId = route.params.id;
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`;
      isRequesting.value = true;

      const params = { join: true, transform: false };
      const fixedParams = new URLSearchParams(params);
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`,
        },
      });

      if (!res.ok) throw new Error("Failed when trying to read data");
      const resultJson = await res.json();
      initialValues = resultJson.data;

      // Restriksi OS: tidak boleh buka user dari PT OS lain
      if (isUserOS.value && initialValues.m_os_id && initialValues.m_os_id != userOsId.value) {
        throw new Error("Anda tidak memiliki akses ke user ini");
      }

      initialValues.m_os_id = initialValues.m_os_id
      // values.m_kary_id = initialValues.m_kary_id
      console.log('',initialValues)

      console.log(values.m_kary_id); // Pastikan nilai m_kary_id ada

      // Menambahkan Data Ke Array
            detailArr.value = [
        ...initialValues.default_users_respo.map(item => ({
          ...item,
          m_comp_id: item['m_respo.m_comp_id'],
          m_subcomp_id: item['m_respo.m_subcomp_id'],
          m_branch_id: item['m_respo.m_branch_id'],
        })),
        ...detailArr.value
      ];

    } catch (err) {
      isBadForm.value = true;
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back();
      });
    }
    isRequesting.value = false;
  }

  // Sinkronisasi nilai initialValues ke values
  for (const key in initialValues) {
    values[key] = initialValues[key];
  }

  // Jika mode Edit, kosongkan password
  if (actionText.value === 'Edit') {
    values.password = '';
  }
});

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
}


async function onSave() {
  if (isRequesting.value) return
  isRequesting.value = true

  try {
    // if (!values.m_kary_id) {
    //   swal.fire({
    //     icon: 'warning',
    //     text: 'Karyawan perlu di isi'
    //   })
    //   return
    // }

    const details = detailArr.value.map((items, idx) => ({
      ...items,
      is_primary: items.is_primary ? 1 : 0,
      is_active: items.is_active ? 1 : 0,
      seq: idx + 1
    }))

    let invalid = false

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)

    if (isCreating) {
      if (!values.name) {
        formErrors.value.name = ['Please enter a name']
        invalid = true
      }
      if (!values.password) {
        formErrors.value.password = ['Please enter a password']
        invalid = true
      }
      if (!values.user_type) {
        formErrors.value.user_type = ['Please choose a User Type']
        invalid = true
      }
      if (!values.email) {
        formErrors.value.email = ['Please enter a Email']
        invalid = true
      } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(values.email)) {
        formErrors.value.email = ['Please enter a valid email address']
        invalid = true
      } else {
        formErrors.value.email = null
      }
      if (!values.username) {
        formErrors.value.username = ['Please enter a User Login']
        invalid = true
      }
    }

    let dataURL = `${store.server.url_backend}/operation${endpointApi}`

    if (actionText.value === 'Edit') {
      values.id = route.params.id
      dataURL = `${store.server.url_backend}/operation${endpointApi}/update`

      if (!values.name) {
        formErrors.value.name = ['Please enter a name']
        invalid = true
      }
      if (!values.user_type) {
        formErrors.value.user_type = ['Please choose a User Type']
        invalid = true
      }
      if (!values.email) {
        formErrors.value.email = ['Please enter a Email']
        invalid = true
      } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(values.email)) {
        formErrors.value.email = ['Please enter a valid email address']
        invalid = true
      } else {
        formErrors.value.email = null
      }
      if (!values.username) {
        formErrors.value.username = ['Please enter a User Login']
        invalid = true
      }
    }

    if (details.length > 0 && details.filter(e => e.is_primary === 1).length === 0) {
      swal.fire({
        icon: 'error',
        text: 'User harus mempunyai responsibility primary minimal 1'
      })
      return
    }

    if (invalid) return

    if (actionText.value === 'Copy') {
      delete values.id
    }

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({
        ...values,
        m_os_id: values.m_os_id ? values.m_os_id : null,
        default_users_respo: details,
        is_active: values.is_active ? 1 : 0,
        is_hc: values.is_hc ? true : false
      })
    })

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson?.errors || (responseJson?.data?.errors ?? {})
        throw responseJson.message || 'Oops, sesuatu yang salah terjadi. Coba kembali nanti.'
      }
      throw 'Oops, sesuatu yang salah terjadi. Coba kembali nanti.'
    }

    router.replace('/' + modulPath + '?reload=' + Date.now())
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'warning',
      text: err
    })
  } finally {
    isRequesting.value = false
  }
}



//  @else----------------------- LANDING
const activeBtn = ref()

const getWhereFilter = (noBtn) => {
  const conds = []
  if (userOsId.value) {
    conds.push(`this.m_os_id=${userOsId.value}`)
  }
  if (noBtn === 1) {
    conds.push(`this.is_active=true`)
  } else if (noBtn === 2) {
    conds.push(`this.is_active=false`)
  }
  return conds.length > 0 ? conds.join(' and ') : null
}

function filterShowData(params, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }
  
  landing.api.params.where = getWhereFilter(activeBtn.value)
  apiTable.value.reload()
}

async function syncData() {
  swal.fire({
    icon: 'warning',
    text: 'Sync Data to ERP?',
    iconColor: '#1469AE',
    confirmButtonColor: '#1469AE',
    showDenyButton: true
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/m_kary/syncKary`
        isRequesting.value = true
        const response = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        if (!response.ok) {
          const responseJson = await response.json().catch(() => ({}))
          const msg = responseJson.message || 'Gagal melakukan sync'
          throw msg
        }
        const result = await response.json()
        swal.fire({
          icon: 'success',
          text: result.message || 'Sync berhasil'
        })
      } catch (err) {
        isBadForm.value = true
        swal.fire({
          icon: 'error',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',
          text: err.toString()
        })
      } finally {
        isRequesting.value = false
        apiTable.value.reload()
      }
    }
  })
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => (currentMenu?.can_delete),
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
      show: (row) => (currentMenu?.can_read),
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) =>
        currentMenu?.can_update === true,
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => currentMenu?.can_create === true,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
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
      paginate: 25,
      simplest: true,
      searchfield: 'this.id, this.name, this.username, this.user_type, this.is_active',
      where: userOsId.value ? `this.m_os_id=${userOsId.value}` : null
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
    headerName: 'Nama',
    field: 'm_kary.nama_lengkap',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start'],
    valueGetter: (params) => params.data?.m_kary?.nama_lengkap || params.data?.name || '-'
  },
  {
    headerName: 'PT Outsource',
    field: 'm_os_id',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start'],
    valueGetter: (params) => {
      const osId = params.data?.m_os_id
      if (!osId) return '-'
      return osMap[osId] || '-'
    }
  },
  {
    headerName: 'User Login',
    field: 'username',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tipe User',
    field: 'user_type',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true, wrapText: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
 {
    headerName: 'Status',
    field: 'is_active',
    filter: true,
    sortable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      const isActive = value === true || value === 'true' || value === 1 || value === '1' || value === 'Active';
      return isActive
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize" style="color: #16a34a !important;">Active</span>`
        : `<span class="text-red-600 rounded-md text-xs font-bold px-4 py-1 inline-block capitalize" style="color: #dc2626 !important;">Inactive</span>`
    }
  },]
})

onActivated(() => {
  reloadActiveRespo()
  fetchCompanyOs()
  //  reload table api landing
  if (apiTable.value) {
    landing.api.params.where = getWhereFilter(activeBtn.value)
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))