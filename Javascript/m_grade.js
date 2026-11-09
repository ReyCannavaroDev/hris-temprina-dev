import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated } from 'vue'

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
const endpointApi = '/m_grade'
onBeforeMount(() => {
  document.title = 'Master Grading'
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
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault();
    onSave();
  }
}

function preventInvalidInput(event) {
  const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', '+'];
  if (event.ctrlKey || event.metaKey) {
    return;
  }
  if (allowedKeys.includes(event.key) || !isNaN(Number(event.key))) {
    formErrors.value["number"] = null;
    return;
  }
  event.preventDefault();
  formErrors.value["number"] = ['Hanya dapat menginputkan angka'];
}

let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: true,

})


onBeforeMount(async () => {
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




function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Anda yakin akan mereset data ini?',
    showDenyButton: true
  }).then((res) => {

    if (res.isConfirmed) {
      const newValues = {
        code: '',
        value: '',
        desc: ''
      };

      for (const key in newValues) {
        if (newValues.hasOwnProperty(key)) {
          values[key] = newValues[key];
        }
      }
    }
  })
}

function onBack() {
  router.replace('/' + modulPath)
}

watchEffect(() => {
  if (values.score > 100) {
    swal.fire({
      icon: 'warning',
      title: 'Kesalahan Input',
      text: 'Score tidak dapat lebih dari 100!',
    });
    values.score = null; // Mengosongkan nilai score
  }
});

async function onSave() {
  //values.tags = JSON.stringify(values.tags)

    if (values.score > 100) {
    return swal.fire({
      icon: 'warning',
      title: 'Kesalahan Input',
      text: 'Score tidak boleh lebih dari 100!',
    });
  }

    if (values.max < values.min) {
    return swal.fire({
      icon: 'warning',
      title: 'Kesalahan Input',
      text: 'Nilai Maksimal tidak boleh lebih kecil dari Nilai Minimal!',
    });
  }

  if (values.min > values.max) {
    return swal.fire({
      icon: 'warning',
      title: 'Kesalahan Input',
      text: 'Nilai Minimal tidak boleh lebih besar dari Nilai Maksimal!',
    });
  }

  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    // values.is_active = values.is_active ? 1 : 0
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({
        ...values,
        is_active: values.is_active ? 1 : 0
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


window.downloadExcel = async function () {
  try {
    const url = new URL(`${store.server.url_backend}/operation/m_general`);
    url.searchParams.append("group", "BERKAS");
    url.searchParams.append("key", "FORMAT_GRADING");
    url.searchParams.append("is_active", true);

    const response = await fetch(url.toString(), {
      method: "GET",
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
    });

    const result = await response.json();

    if (result?.data?.length) {
      const target = result.data.find(item => item.group === "BERKAS" && item.key === "FORMAT_GRADING");

      if (target?.value) {
        window.open(target.value, "_blank");
      } else {
        alert("URL format grading tidak ditemukan.");
      }
    } else {
      alert("Data tidak ditemukan.");
    }
  } catch (error) {
    console.error("Error saat download format grading:", error);
    alert("Terjadi kesalahan saat mengunduh file.");
  }
};




window.uplodexcel = () => {
  swal.fire({
    title: "Konfirmasi Upload",
    text: "Apakah Anda ingin mengunggah file?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Upload",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      // Jika user menekan "Ya, Upload"
      const fileInput = document.createElement("input");
      fileInput.type = "file";
      fileInput.accept = ".xlsx, .xls";
      fileInput.style.display = "none"; // Sembunyikan elemen input

      // Tambahkan event listener untuk menangani perubahan file
      fileInput.addEventListener("change", excelFileUpload);

      // Panggil metode click() untuk membuka dialog pemilihan file
      fileInput.click();
    }
  });
};


const excelFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) {
    swal.fire({
      icon: 'error',
      text: 'No file selected',
      confirmButtonText: 'OK',
    });
    return;
  }

  // Validasi jenis file
  if (!['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'].includes(file.type)) {
    swal.fire({
      icon: 'error',
      text: 'Only Excel files (.xlsx or .xls) are allowed',
      confirmButtonText: 'OK',
    });
    return;
  }

  // Persiapan data untuk dikirim ke API
  const formData = new FormData();
  formData.append('file', file);

  try {
    isRequesting.value = true;

    // Kirim file ke API
    const uploadUrl = `${store.server.url_backend}/operation${endpointApi}/upload_grade_excel`;
    const res = await fetch(uploadUrl, {
      method: 'POST',
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
      body: formData,
    });

    if (!res.ok) {
      throw new Error('Failed to upload file');
    }

    const result = await res.json();
    swal.fire({
      icon: 'success',
      text: 'File uploaded successfully',
      confirmButtonText: 'OK',
    });

    // Refresh tabel setelah upload berhasil
    if (apiTable.value) {
      apiTable.value.reload();
    }
  } catch (err) {
    swal.fire({
      icon: 'error',
      text: err.message || 'An error occurred while uploading the file',
      confirmButtonText: 'OK',
    });
  } finally {
    isRequesting.value = false;
  }
};


const activeBtn = ref()

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
                if ([400, 422].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw new Error(responseJson.message || "Failed when trying to post data")
                } else {
                  throw new Error("Failed when trying to post data")
                }
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
        currentMenu?.can_update === true ,
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
      show: (row) => currentMenu?.can_create === true ,
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
    params: {
      paginate: 25,
      simplest: true,
      searchfield: 'this.code, this.value , this.value_2 , this.value_3 , this.value_4',
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
    field: 'code',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Keterangan',
    field: 'keterangan',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Min',
    field: 'min',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: params =>
      params.value != null && params.value !== 0
        ? 'text-green-500 font-bold border-r !border-gray-200'
        : 'text-black border-r !border-gray-200',
    valueFormatter: params =>
      params.value != null
        ? 'Rp. ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(params.value) + '.-'
        : 'Rp.0.-'
  },
  {
    headerName: 'Max',
    field: 'max',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: params =>
      params.value != null && params.value !== 0
        ? 'text-green-500 font-bold border-r !border-gray-200'
        : 'text-black border-r !border-gray-200',
    valueFormatter: params =>
      params.value != null
        ? 'Rp. ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(params.value) + '.-'
        : 'Rp.0.-'
  },

  {
    headerName: 'Score',
    field: 'score',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Status',
    field: 'is_active',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === true
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
        : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
    }
  },
  ]
})

onActivated(() => {
  //  reload table api landing
  if (apiTable.value && apiTable.value.reload) {
    if (route.query.reload) {
      apiTable.value.reload();
    }
  }
});
//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))