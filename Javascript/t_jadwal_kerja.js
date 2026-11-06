import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, computed, onBeforeUnmount, watchEffect, onActivated, nextTick } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')
console.log(store.user.data.id)
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
const endpointApi = 't_jadwal_kerja_n'
onBeforeMount(() => {
  document.title = 'Jadwal Kerja'
})


//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);

  const today = new Date();
  // Format tanggal sesuai dengan "dd-mm-yyyy"
  const day = String(today.getDate()).padStart(2, '0');
  const month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
  const year = today.getFullYear();
  const formattedDate = `${day}/${month}/${year}`;
  values.date = formattedDate;
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's' && actionText.value) {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []
const is_js = ref(false);

let values = reactive({
  juru_sortir_id: `${store.user.data.id}`,
  status: 'AKTIF'
})

const hitungTotalsak = computed(() => {
  let total = 0;

  detailArr.value.forEach((dt) => {
    total += parseFloat(dt.sak) || 0;
  });

  values.total_sak = total;
  return total
});

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = () => {
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
const hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']
const detailArr = ref(hari.map((h, i) => ({
  day: h,
  day_num: i + 1,
  tipe_hari: 'KERJA',
  m_jam_kerja_id: null,
})))

const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
}

const blmSortir = computed(() => {
  let total = 0;
  detailArr.value.forEach((dt) => {
    total += parseFloat(dt.sak) || 0;
  });
  return (values.jumlah_sak || 0) - total;
});

// End Table Detail

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }
  onReset()
  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      // format Tanggal
      const today = new Date();
      const day = String(today.getDate()).padStart(2, '0');
      const month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
      const year = today.getFullYear();
      const formattedDate = `${day}/${month}/${year}`;
      //<---------->

      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${editedId}`
      isRequesting.value = true

      const res = await fetch(dataURL, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      initialValues = resultJson.data
      console.log('test', initialValues)
      if (actionText.value?.toLowerCase() === 'copy') {
        delete initialValues.no
        initialValues.date = formattedDate
      }

      if (Array.isArray(initialValues.t_jadwal_kerja_det_hari) && initialValues.t_jadwal_kerja_det_hari.length > 0) {
        detailArr.value = initialValues.t_jadwal_kerja_det_hari.map((dt) => {
          if (actionText.value?.toLowerCase() === 'copy' && dt.id) {
            delete dt.id;
            delete dt.no;
          }
          return {
            ...dt,
            code: dt['m_sortir.code'],
            nama: dt['m_sortir.name']
          };
        });
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

function onBack() {
  router.replace('/' + modulPath);
}

async function getTypeItem(gen_id) {
  const dataURL = `${store.server.url_backend}/operation/m_gen/${gen_id}`
  const res = await fetch(dataURL, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const resultJson = await res.json();
  if (resultJson.data.value2 === 'JS') {
    is_js.value = true;
  } else {
    is_js.value = false;
  }
}


async function onSave() {
  const result = await swal.fire({
    icon: 'warning', text: 'Simpan data?', showDenyButton: true,
  });

  if (!result.isConfirmed) return;

  if (detailArr.value) {
    const invalid = detailArr.value.some(item => !item.m_jam_kerja_id);
    if (invalid) {
      await swal.fire({
        icon: 'error',
        text: 'Jam kerja perlu diisi',
      });
      return;
    }
    values.t_jadwal_kerja_det_hari = detailArr.value;
  }


  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : '/' + route.params.id}`;
    isRequesting.value = true;
    values.nomor = '1'
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
      body: JSON.stringify(values),
    });

    if (!res.ok) {
      const responseJson = await res.json();
      formErrors.value = responseJson.errors || {};
      throw new Error(responseJson.message || "Failed when trying to post data");
    }

    router.replace(`/${modulPath}?reload=${Date.now()}`);
  } catch (err) {
    isBadForm.value = true;
    console.log(err)
    swal.fire({ icon: 'error', text: err });
  } finally {
    isRequesting.value = false;
  }
}

//  @else----------------------- LANDING
const filterButton = ref(null);
const activeBtn = ref()
const valLand = reactive({})
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

      if(data.can_read){
        landing.api.url = `${store.server.url_backend}/operation/${endpointApi}`
        nextTick(()=>{
          apiTable.value.reload()
        })
      }
      
    } catch (err) {
      console.error(err)
    }
  }
})

function parseTanggalToYMD(tanggal) {
  const [dd, mm, yyyy] = tanggal.split('/');
  return `${yyyy}-${mm}-${dd}`;
}


function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'AKTIF',
    2: 'NON AKTIF',
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

  const [dateFrom, dateTo] = [valLand.start_date, valLand.end_date].map(d =>
    d ? parseTanggalToYMD(d) : null
  )

  if (dateFrom || dateTo) {
    const conditions = []
    if (dateFrom) conditions.push(`this.date >= '${dateFrom}'`)
    if (dateTo) conditions.push(`this.date <= '${dateTo}'`)
    filters.push(conditions.join(' AND '))
  }
  landing.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}


// function filterShowData(params) {
//   filterButton.value = filterButton.value === params ? null : params;

//   if (filterButton.value) {
//     landing.api.params.where = `this.status='${filterButton.value.toUpperCase()}'`;
//   } else {
//     landing.api.params.where = null;
//   }

//   apiTable.value.reload();
// }



const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => data?.can_delete,
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
      show: (row) => data?.can_read,
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => data?.can_update,
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => ['DRAFT'].includes(row['status']) && data.can_update,
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
              const dataURL = `${store.server.url_backend}/operation/${endpointApi}/post`
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
                  throw (responseJson?.message + " " + responseJson?.data?.errorText || "Failed when trying to post data")
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
      show: (row) => data.can_create,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
  ],
  api: {
    // url: `${store.server.url_backend}/operation/${endpointApi}`,
    url: currentMenu?.can_read
         ? `${store.server.url_backend}/operation/${endpointApi}` 
         : '',
    //url:'',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      searchfield: 'this.nomor, this.keterangan'
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
    headerName: 'No. Jadwal Kerja',
    field: 'nomor',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Keterangan',
    field: 'keterangan',
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
    filter: true,
    sortable: true,
    flex: 1,
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start'],
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
