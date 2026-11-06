//   javascript//   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, computed } from 'vue'

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
const detailArr = ref([])
const titleOpen = ref('')
const formErrors = ref({})
const modalOpen = ref(false)
const detailArrOpen = reactive({ items: [] })
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/t_perhitungan_gaji'
onBeforeMount(() => {
  document.title = 'Perhitungan Gaji Harian OS'
})

function openDetailFromLanding(row) {
  titleOpen.value =
    (row?.['m_kary.nama_lengkap'] ?? '-') +
    ' - ' +
    (row?.['m_kary.nik'] ?? '-');

  const dataFormat = row?.detail_gaji ?? [];

  // penting: force reactivity change
  detailArrOpen.items = [...dataFormat];

  modalOpen.value = true;
}



const totalHours = computed(() => {
  return (detailArrOpen.items || []).reduce((sum, item) => {
    // console.log(item.hours)
    return sum + (Number(item.hours) || 0);
  }, 0);
});

const totalValue = computed(() => {
  return (detailArrOpen.items || []).reduce((sum, item) => {
    let val = Number(item.value) || 0;

    if (item.factor === '-') {
      val = -val;
    }

    return sum + val;
  }, 0);
});

function closeModal(i) {
  modalOpen.value = false
}

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
}


//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
let _id = 0


let thisMonth = new Date().toISOString().split('T')[0]
let tempYear = thisMonth.split('-')[0]
let tempMonth = thisMonth.split('-')[1]

const values = reactive({
  // periode_awal: tempYear + '-' + tempMonth + '-' + '01',
  // periode_akhir: tempYear + '-' + tempMonth + '-' +'01'
  periode_awal: null,
  periode_akhir: null
})

async function generatePerhitungan() {
  detailArr.value = [];

  // Tampilkan SweetAlert loading sebelum fetch
  swal.fire({
    title: "Memuat...",
    text: "Mohon tunggu, sedang mengambil data perhitungan gaji...",
    allowOutsideClick: false,
    didOpen: () => {
      swal.showLoading(); // ✅ Panggil langsung dari `swal`
    }
  });

  try {
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji/generateHarianOS`;

    const res = await fetch(dataURL, {
      method: "POST",
      headers: {
        "Content-Type": "Application/json",
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    });

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (
          responseJson.errors.length
            ? responseJson.errors[0]
            : responseJson.message || "Failed when trying to post data"
        );
      } else if (res.status == 500) {
        throw "Sesuatu yang salah terjadi, silahkan coba kembali nanti atau hubungi administrator";
      } else {
        throw "Failed when trying to post data";
      }
    }

    const result = await res.json();
    const resultData = result.data;

    // Tutup loading setelah fetch berhasil
    swal.close();

    if (!resultData?.length) {
      return swal.fire({
        icon: "warning",
        text: "Tidak ditemukan data Perhitungan Gaji, pastikan karyawan sudah memiliki standar gaji"
      });
    }

    resultData.forEach((item) => {
      item.id = ++_id;
      item.karyawan = item["nama_lengkap"];
      item.deskripsi = item["desc"];
      detailArr.value.push(item);
    });

  } catch (err) {
    isBadForm.value = true;

    // Tutup loading dan tampilkan error
    swal.close();
    swal.fire({
      icon: "error",
      text: "Sesuatu yang salah terjadi, silahkan coba kembali nanti atau hubungi administrator"
    });
  }
}

function openDetail(i) {
  titleOpen.value = detailArr.value[i]?.nama_lengkap ?? '-' + ' - ' + detailArr.value[i]?.nama_lengkap ?? ''
  let dataFormat = detailArr.value[i]?.detail_gaji ?? []

  detailArrOpen.items = dataFormat
  modalOpen.value = true
}



onBeforeMount(async () => {

  // if (localStorage.getItem('respo')) {
  //   const respoValues = await JSON.parse(localStorage.getItem('respo'))
  //   console.log('ini respo', respoValues)
  //   // values.comp_id = respoValues.m_comp_id
  //   values.sbu = respoValues.m_subcomp_id
  //   values.m_branch_id = respoValues.m_branch_id
  // }

  if (isRead && currentMenu?.can_read) {
    //  READ DATA
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
      if (!res.ok) throw new Error("Failed when trying to read data")
      const resultJson = await res.json()
      initialValues = resultJson.data
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
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
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



async function onSave() {

  if (detailArr.value.length === 0) {
    swal.fire({
      icon: 'error',
      title: 'Oops..',
      text: "Silahkan generate data perhitungan gaji terlebih dahulu sebelum menyimpan."
    });
    return;
  }

  // merging detail data
  values['detail'] = detailArr._value
  values['deskripsi'] = 'HARIAN'

  try {
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji/save`
    isRequesting.value = true

    const res = await fetch(dataURL, {
      method: 'POST',
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
      // console.log('x', result)
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
      click(row) {
        openDetailFromLanding(row)
      }
    }
  ],
  api: {
    // url: `${store.server.url_backend}/operiation${endpointApi}`,
    url: currentMenu?.can_read
      ? `${store.server.url_backend}/operation${endpointApi}`
      : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      scopes: "getos",
      searchfield: 'this.id, this.nomor, m_kary.nik, m_kary.nama_lengkap, m_kary_dir.name, m_kary_divisi.name, m_kary_dept.nama, this.periode, periode.value, this.netto',
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
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start', 'w-[400px]']
  },
  {
    field: 'm_kary.nik',
    headerName: 'NIK',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_kary.nama_lengkap',
    headerName: 'Karyawan',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_branch.name',
    headerName: 'Cabang',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_general.value',
    headerName: 'Divisi',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'periode',
    headerName: 'Tgl Periode',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'periode.value',
    headerName: 'Periode Gaji',
    filter: true,
    wrapText: true,
    sortable: true,
    filter: 'ColFilter',
    flex: 1,
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'netto',
    headerName: 'Gaji',
    filter: true,
    wrapText: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-end'],
    cellRenderer: ({ value }) => {
      return formatRupiah(value);
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