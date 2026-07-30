import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, computed, watchEffect, watch, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
// console.log('test',store.user.data.id)
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
const isModalOpen = ref(false);
const kary = route.query.isKaryId
// console.log('kary', route.query.isKaryId)
const kary_id = ref(route.query.isKaryId)
const karyId = ref(null)

// ------------------------------ PERSIAPAN
const endpointApi = 't_jadwal_kerja_d_n'
onBeforeMount(() => {
  document.title = 'Transaksi Jadwal Kerja Karyawan'
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);

})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault();
    onSave();
  }
}

let initialValues = {}
const changedValues = []

const user = JSON.parse(localStorage.getItem('user'))

const values = reactive({
  status: 'AKTIF',
  m_kary_id: route.query.isKaryId
    ? route.query.isKaryId.split(',').map(Number)
    : [],
  atasan_id: store.user.data.m_kary_id
})

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

const karyOptions = ref([])

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo', respoValues)
    values.comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }

  if (values.m_kary_id.length) {
    const res = await fetch(`${store.server.url_backend}/operation/m_kary?where=this.id IN (${values.m_kary_id.join(',')})&transform=true&join=true`, {
      headers: { 'Content-Type': 'application/json', Authorization: `${store.user.token_type} ${store.user.token}` }
    })
    const data = await res.json()
    karyOptions.value = data.data
  }

  if (isRead) {
    try {
      const editedId = route.params.id;
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${route.params.id}`;
      isRequesting.value = true;

      const params = {
        transform: false,
        join: false,
        simplest: true,

      };
      const fixedParams = new URLSearchParams(params);

      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!res.ok) throw new Error("Failed when trying to read data");

      const resultJson = await res.json();
      initialValues = resultJson.data;
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
    } finally {
      isRequesting.value = false;
    }
    // Assign ke values semua properti initialValues
    for (const key in initialValues) {
      values[key] = initialValues[key];
    }

    if (initialValues.t_jadwal_kerja_n_id) {
      const jadwalUrl = `${store.server.url_backend}/operation/t_jadwal_kerja_n/${initialValues.t_jadwal_kerja_n_id}`;
      const jadwalRes = await fetch(`${jadwalUrl}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      });

      if (!jadwalRes.ok) throw new Error("Failed when trying to read jadwal kerja detail");

      const jadwalJson = await jadwalRes.json();
      console.log('jadwal kerja detail', jadwalJson.data.t_jadwal_kerja_d_hari_n);
      jadwalJson.data.t_jadwal_kerja_d_hari_n.forEach(item => {
        console.log('test isi jadwal kerja n', item);
      });

      detailArr.value = Array.isArray(jadwalJson.data.t_jadwal_kerja_d_hari_n)
        ? jadwalJson.data.t_jadwal_kerja_d_hari_n.slice().reverse().map(item => ({ ...item }))
        : [];
    }
  }
});



// Table Detail
const detailArr = ref([])

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
  try {
    isRequesting.value = true

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : '/' + route.params.id}`

    values.t_assessment_kary_d = detailArr.value

    const payloads = Array.isArray(values.m_kary_id)
      ? values.m_kary_id.map(id => ({
        ...values,
        m_kary_id: id
      }))
      : [{
        ...values,
        m_kary_id: values.m_kary_id
      }]


    for (const payload of payloads) {
      const res = await fetch(dataURL, {
        method: isCreating ? 'POST' : 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        body: JSON.stringify(payload)
      })

      const responseJson = await res.json()

      if (!res.ok) {
        formErrors.value = responseJson.errors || {}
        const errorMessage = responseJson.message || Object.values(formErrors.value)[0] || 'Oops, terjadi kesalahan.'
        throw errorMessage
      }
    }

    router.replace(`/${modulPath}?reload=${Date.now()}`)
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
const infoOutstanding = ref(null)
let data = reactive({})

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    //console.log('ini respo coi', respoValues.id)
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
    data.respo_id = respoValues.id
  }
  console.log('jarwok', data.subcomp_id)
})

function multiCreate(items) {
  console.log('Isi items:', items)
  const ids = items.map(i => i.id).filter(Boolean)
  console.log('Hasil map IDs:', ids)
  router.push(`${route.path}/create?isKaryId=${ids}&ts=${Date.now()}`)
}


function openOutstanding() {
  infoOutstanding.value?.onEnter()
}

function filterShowData(params, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }
  
  if (activeBtn.value == null) {
    // clear params filter
    landing.api.params.where = null
  } else if (params) {
    landing.api.params.where = `this.status='DRAFT'`
  } else {
    landing.api.params.where = `this.status='POSTED'`
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
              const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${row.id}`
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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      // show: (row) => row['status'] == 'DRAFT',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&isKaryId=${row.m_kary_id}&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row['status'] == 'DRAFT',
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
              const dataURL = `${store.server.url_backend}/operation/t_assessment_kary/postData`
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
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation/${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: computed(() => {
      const params = {
        kary_id: store.user.data.m_kary_id ?? 0,
        join: true,
        transform: true,
        scopes: 'os',
      }

      const where = []

      if (data.subcomp_id != null) {
        where.push(`m_kary.m_subcomp_id  = ${data.subcomp_id}`)
      }

      if (data.branch_id != null) {
        where.push(`m_kary.m_branch_id = ${data.branch_id}`)
      }

      if (where.length) {
        params.where = where.join(' AND ')
      }

      return params
    }),
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
  // {
  //   headerName: 'Nama',
  //   field: 'nama_lengkap',
  //   filter: true,
  //   sortable: true,
  //   flex: 1,
  //   filter: 'ColFilter',
  //   resizable: true,
  //   wrapText: true,
  //   cellClass: ['border-r', '!border-gray-200', 'justify-start']
  // },
  {
    headerName: 'Nama',
    field: 'm_kary.nama_lengkap',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tanggal',
    field: 'start_date',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Status',
    field: 'status',
    sortable: true,
    resizable: true,
    wrapText: true,
    autoHeight: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: (params) => {
      const status = params.data['status']?.toUpperCase();
      return status === 'AKTIF'
        ? `<span class="text-green-600 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${status}</span>`
        : status === 'NON AKTIF'
          ? `<span class="text-amber-600 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${status}</span>`
          : `<span class="text-red-600 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Status Tidak Terdaftar</span>`;
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