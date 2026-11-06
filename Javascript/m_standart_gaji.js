//   javascriptimport { useRouter, useRoute, RouterLink } from 'vue-router'
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
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/m_standart_gaji'
onBeforeMount(() => {
  document.title = 'Master Gaji'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS





let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: true,
  check_late: true,
  check_absence: true,
  kode: null,
  //start_date: null, // Format tanggalnya dd/mm/yyyy
});

const editKemahalan = ref(false);
const editFungsional = ref(false);
const editJabatan = ref(false);
const optTipe = ['Biaya Komunikasi 1', 'Biaya Komunikasi 2', 'BPJS Ketenagakerjaan', 'BPJS Kesehatan', 'BPJS Keluarga', 'BPJS Pensiun', 'Klaim Askes', 'Kas',
  'Pinjaman Kantor', 'Potongan Produktifitas', 'Sanksi Cetak/Kinerja', 'Sanksi Terlambat', 'Sanksi Tidak Hadir', 'TSM']



watchEffect(() => {
  if (isRead) {
    editKemahalan.value = true;
    editFungsional.value = true;
    editJabatan.value = true;
  }
});

watchEffect(() => {
  if (!editKemahalan.value) {
    values.tunjangan_kemahalan = null;
  }
});
watchEffect(() => {
  if (!editJabatan.value) {
    values.tunjangan_posisi = null;
  }
});

watchEffect(() => {
  if (!editFungsional.value) {
    values.tunjangan_fungsional = null;
  }
});



// watchEffect(() => {
//   if (values.start_date) {
//     const [day, month, year] = values.start_date.split('/');

//     // Jika values.kode memiliki format tahun diawal, hapus tahun terlebih dahulu
//     values.kode = values.kode?.replace(/^\d{4}-/, '') || '';

//     // Pastikan tahun tidak ditambahkan lagi jika sudah ada dalam values.kode
//     values.kode = `${year}-${values.kode}`;
//   }
// });

// Mengawasi perubahan values.grading_id
watchEffect(() => {
  if (!values.grading_id) {
    //values.start_date = null;
    values.kode = null;
    values.m_zona_id = null;
  }
});




watchEffect(() => {
  updateTunjanganKemahalan();

  values.take_home_pay = values.gaji_pokok + values.tunjangan_produktifitas + values.tunjangan_transport || 0
});




// SUMMARY 
// Fungsi untuk menghitung distribusi Take Home Pay
const distributeTakeHomePay = () => {
  const takeHomePay = parseFloat(values.take_home_pay) || 0;

  values.gaji_pokok = takeHomePay * 0.75;
  values.tunjangan_produktifitas = takeHomePay * 0.15;
  values.tunjangan_transport = takeHomePay * 0.10;



  // Hitung ulang total gaji dan grand total setelah distribusi
  calculateTotalGaji();
  calculateGrandTotal();
};

// Fungsi untuk menghitung total gaji
const calculateTotalGaji = () => {
  const gajiPokok = parseFloat(values.gaji_pokok) || 0;
  const tunjanganProduktifitas = parseFloat(values.tunjangan_produktifitas) || 0;
  const tunjanganTransport = parseFloat(values.tunjangan_transport) || 0;

  // Update totalGaji
  values.totalGaji = gajiPokok + tunjanganProduktifitas + tunjanganTransport;
};

// Fungsi untuk menghitung total tunjangan gaji
const calculateTotalTunjanganGaji = () => {
  const tunjanganJabatan = parseFloat(values.tunjangan_posisi) || 0;
  const tunjanganKemahalan = parseFloat(values.tunjangan_kemahalan) || 0;
  const tunjanganFungsional = parseFloat(values.tunjangan_fungsional) || 0;

  // Update totalTunjanganGaji
  values.totalTunjanganGaji = tunjanganJabatan + tunjanganKemahalan + tunjanganFungsional;
};

// Fungsi untuk menghitung grand total
const calculateGrandTotal = () => {
  const totalGaji = parseFloat(values.totalGaji) || 0;
  const totalTunjanganGaji = parseFloat(values.totalTunjanganGaji) || 0;

  // Update grandTotal
  values.grandTotal = totalGaji + totalTunjanganGaji;
};

// WatchEffect untuk memantau perubahan pada nilai-nilai yang relevan
watchEffect(() => {
  if (values.take_home_pay) {
    distributeTakeHomePay();
  } else {
    calculateTotalGaji();
  }

  calculateTotalTunjanganGaji();
  calculateGrandTotal();
});


// Computed property untuk format Rupiah
const formattedTotalGaji = computed(() => formatRupiah(values.totalGaji));
const formattedTotalTunjanganGaji = computed(() => formatRupiah(values.totalTunjanganGaji));
const formattedGrandTotal = computed(() => formatRupiah(values.grandTotal));

// Fungsi utilitas untuk format Rupiah
function formatRupiah(amount) {
  const formatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0, // Tidak menampilkan desimal
    maximumFractionDigits: 0, // Tidak menampilkan desimal
  });
  return formatter.format(amount);
}
// END SUMMARY



function updateTunjanganKemahalan() {
  if (values.grading_id && values.m_zona_id) {
    fetchTunjanganKemahalan(values.grading_id, values.m_zona_id);
  }
}

let timeoutId = null;
let isDataFetched = false;



// async function hitung(totalNominal) {
//   if (isDataFetched) {
//     console.log('Data sudah diambil melalui updateWaifu, tidak perlu fetch ulang.');
//     return;
//   }

//   try {
//     const dataURL = `${store.server.url_backend}/operation/m_grade`;
//     const params = 
//     {  
//     nominal: totalNominal 
//     };
//     const fixedParams = new URLSearchParams(params);

//     console.log(`Fetching data from: ${dataURL}?${fixedParams}`);

//     const res = await fetch(`${dataURL}?${fixedParams}`, {
//       method: 'GET',
//       headers: {
//         'Content-Type': 'Application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`,
//       },
//     });

//     if (!res.ok) {
//       throw new Error('Failed when trying to read data');
//     }

//     const resultJson = await res.json();
//     const initialValues = resultJson.data;

//     if (initialValues && initialValues.length > 0) {
//       console.log('getData', initialValues[0].id);
//       values.grading_id = initialValues[0].id;
//       console.log('Data berhasil diambil! Data berhasil diperoleh dari server.');
//     } else {
//       throw new Error('Data Grading Tidak Ditemukan');
//     }
//   } catch (error) {
//     console.error('API Error:', error.message);
//     console.log('Gagal mengambil data!:', error.message || 'Data Grading Tidak Ditemukan');
//   } finally {
//     isRequesting.value = false;
//   }
// }

const apiKary = computed(() => {
  const params = {
    kary_id: store.user.data?.m_kary_id ?? 0,
    scopes:"nonos",
    join: true,
    transform: true,
  }

  const where = []

  if (values.subcomp_id != null) {
    where.push(`m_subcomp_id = ${values.subcomp_id}`)
  }

  if (values.branch_id != null) {
    where.push(`m_branch_id = ${values.branch_id}`)
  }

  if (where.length) {
    params.where = where.join(' AND ')
  }

  return {
    url: `${store.server.url_backend}/operation/m_kary`,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`,
    },
    params,
    onsuccess(response) {
      response.page = response.current_page;
      response.hasNext = response.has_next;
      return response;
    }
  }
})



console.log('cek', actionText)

watchEffect(() => {
  const modeBaca = actionText.value === 'Edit' ? false : isRead;

  if (modeBaca) {
    return;
  }
  const { gaji_pokok, tunjangan_produktifitas, tunjangan_transport } = values;
  if (gaji_pokok && tunjangan_produktifitas && tunjangan_transport) {
    const totalNominal = gaji_pokok + tunjangan_produktifitas + tunjangan_transport;
    console.log(`Total Nominal: ${totalNominal}`);
    if (timeoutId) clearTimeout(timeoutId);

    // timeoutId = setTimeout(() => {
    //   hitung(totalNominal);
    // }, 3000);
  }
});


async function updateWaifu(grading_id) {
  if (!grading_id || !grading_id.id) {
    isDataFetched = false;
    values.gaji_pokok = null;
    values.tunjangan_produktifitas = null;
    values.tunjangan_transport = null;
    values.take_home_pay = null
    values.kode = '';
    return;
  }

  try {
    console.log('CEK ID', grading_id.id);
    const megumi = grading_id.id;
    const dataURL = `${store.server.url_backend}/operation/m_grade/${megumi}`;
    isRequesting.value = true;

    const params = { simplest: true };
    const fixedParams = new URLSearchParams(params);
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!res.ok) {
      throw new Error('Failed when trying to read data');
    }

    const resultJson = await res.json();
    const initialValues = resultJson.data;
    console.log('cek kode', initialValues.code);

    values.take_home_pay = initialValues.max || 0

    if (initialValues?.max) {
      const totalMax = parseFloat(initialValues.max);
      if (!isNaN(totalMax)) {
        values.gaji_pokok = Math.floor(totalMax * 0.75);
        values.tunjangan_produktifitas = Math.floor(totalMax * 0.15);
        values.tunjangan_transport = Math.floor(totalMax * 0.10);
        console.log('Gaji Pokok:', values.gaji_pokok);
        console.log('Tunjangan Produktifitas:', values.tunjangan_produktifitas);
        console.log('Tunjangan Transport:', values.tunjangan_transport);
      }
    }

    if (initialValues) {
      Object.keys(initialValues).forEach((key) => {
        values[key] = initialValues[key];
      });
    }


    isDataFetched = true;
  } catch (err) {
    console.error('Error in updateWaifu:', err.message);
    isBadForm.value = true;
    swal
      .fire({
        icon: 'error',
        text: err.message || 'Terjadi kesalahan',
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      })
      .then(() => {
        router.back();
      });
  } finally {
    isRequesting.value = false;
  }
}


async function fetchTunjanganKemahalan(gradingId, zonaId) {
  try {
    const response = await fetch(`${store.server.url_backend}/operation/m_tunj_kemahalan?` + new URLSearchParams({ where: `this.is_active='true' AND this.m_zona_id=${zonaId} AND this.grading_id=${gradingId}` }), {
      method: 'GET',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    });

    if (!response.ok) {
      throw new Error('Failed to fetch tunjangan kemahalan data');
    }

    const data = await response.json();
    console.log(data.data.length)
    if (data.data.length > 0) {
      values.tunjangan_kemahalan_id = data.data[0].id;
    }
  } catch (error) {
    console.error('Error fetching tunjangan kemahalan:', error);

  }
}



onBeforeMount(async () => {

  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }

  values.direktorat = store.user.data?.direktorat

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
      // initialValues.is_active=initialValues.is_active?1:0
      initialValues.m_standart_gaji_det?.forEach((items) => {
        items.__id = ++_id
        items.is_insert = false
        detailArr.value = [items, ...detailArr.value]
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

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})

let _id = 0
const detailArr = ref([]);

watchEffect(() => {
  if (!isRead) {
    detailArr.value = [
      { komponen: "Potongan BPJS Ketenagakerjaan" },
      { komponen: "Potongan BPJS Kesehatan" },
      { komponen: "Potongan BPJS Pensiun" },
      { komponen: "Potongan Koperasi Iuran Wajib" }
    ];
  }
});

const addDetail = () => {
  const tempItem = { is_insert: false };
  detailArr.value = [...detailArr.value, tempItem];
};

const onRetotal = (dArr) => {
  if (dArr) {
    detailArr.value = dArr

  }
}

function onDetailAdd(rows) {
  const data = [...detailArr.value]
  rows.forEach(row => {
    data.push(row)
  })
  //tableKey.value++
  onRetotal(data)
}


const removeDetail = (index) => {
  detailArr.value.splice(index, 1);
};




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

async function validateDetailArr() {
  let next = true;
  // if(values.gaji_pokok===0){
  //   swal.fire({
  //     icon: 'warning',
  //     text: `Gaji pokok harus diisi`
  //   })
  //   return false
  // }
  detailArr.value.forEach((item, i) => {
    if (!item.komponen_id) {
      swal.fire({
        icon: 'warning',
        text: `Baris ${i + 1}, Lengkapi kolom Komponen`
      })
      next = false
      return
    }
    if (!item.faktor) {
      swal.fire({
        icon: 'warning',
        text: `Baris ${i + 1}, Lengkapi kolom Faktor`
      })
      next = false
      return
    }
    if (!item.nilai) {
      swal.fire({
        icon: 'warning',
        text: `Baris ${i + 1}, Lengkapi kolom Nilai`
      })
      next = false
      return
    }
    if (!item.periode) {
      swal.fire({
        icon: 'warning',
        text: `Baris ${i + 1}, Lengkapi kolom Periode`
      })
      next = false
      return
    }
  })

  return next
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    // values.is_active=(values.is_active===true)?1:0
    let isNext = true;

    isNext = await validateDetailArr();
    if (!isNext) return;
    values.m_standart_gaji_det = detailArr.value
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({
        ...values,
        is_active: values.is_active ? 1 : 0,
        check_absence: values.check_absence ? 1 : 0,
        check_late: values.check_late ? 1 : 0
      })
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
let data = reactive({})
const page = ref(1)

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo coi', respoValues.m_subcomp_id)
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
  }
  console.log('jarwok', data.subcomp_id)
})


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
    landing.api.params.where = `this.is_active=true`
  } else {
    landing.api.params.where = `this.is_active=false`
  }

  apiTable.value.reload()
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
      // show: (row) =>
      //   currentMenu?.can_update === true &&
      //   (row.status?.toUpperCase() === 'DRAFT' ||
      //     row.status?.toUpperCase() === 'REVISED'),
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
      // show: (row) => currentMenu?.can_create === true && row.status?.toUpperCase() === 'DRAFT',
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
    params: computed(() => {
      const params = {
        page: page.value,
        paginate: 40,
        kary_id: store.user.data.m_kary_id ?? 0,
        join: true,
        transform: true,
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
  {
    headerName: "Kode",
    field: 'kode',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: "Start Date",
    field: 'start_date',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: "Karyawan",
    field: 'm_kary.nama_lengkap',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
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