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
const endpointApi = 'm_assessment_kary'
onBeforeMount(() => {
  document.title = 'Master Penilaian Karyawan'
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

const values = reactive({
  is_active: true,
  m_assessment_kary_d_level: []
})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = () => {
}

function validateNumericInput(event) {
  if (!/^[0-9]$/.test(event.key) && !['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'].includes(event.key)) {
    event.preventDefault();
    formErrors.value["npwp"] = ['Hanya angka yang diperbolehkan'];
    return;
  }

  formErrors.value["npwp"] = null;
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


onBeforeMount(async () => {
  if (isRead && currentMenu?.can_read) {
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${editedId}`

      isRequesting.value = true

      const params = new URLSearchParams({
        join: true,
        transform: true
      })

      const res = await fetch(`${dataURL}?${params}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })

      if (!res.ok) throw new Error("Failed when trying to read data")

      const resultJson = await res.json()
      initialValues = resultJson.data

      // Biarkan sebagai array of integer (id) agar FieldSelect multiple dapat match via valueField="id"
      initialValues.m_assessment_kary_d_level =
        Array.isArray(initialValues.m_assessment_kary_d_level)
          ? initialValues.m_assessment_kary_d_level
            .map(item => typeof item === 'object' && item !== null ? item.m_level_posisi_id : item)
            .map(val => parseInt(val))
            .filter(val => !isNaN(val) && val > 0)
          : []

      if (Array.isArray(initialValues.m_assessment_kary_d)) {
        detailArr.value = initialValues.m_assessment_kary_d.map(item => {
          const sub = Array.isArray(item.m_assessment_kary_sub_d)
            ? [...item.m_assessment_kary_sub_d]
              .filter(Boolean)
              .sort((a, b) => (b.nilai ?? 0) - (a.nilai ?? 0))
            : [
              {
                m_assessment_kary_d_id: 0,
                keterangan: '',
                nilai: 0,
              }
            ]

          return {
            ...item,
            m_assessment_kary_sub_d: sub
          }
        })
      }

    } catch (err) {
      isBadForm.value = true

      swal.fire({
        icon: 'error',
        text: err?.message || err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back()
      })
    } finally {
      isRequesting.value = false
    }
  } else {
    if (detailArr.value.length === 0) {
      detailArr.value.push({
        m_assessment_kary_id: 0,
        nama_assessment: '',
        kategori: null,
        bobot: 0,
        m_assessment_kary_sub_d: [
          {
            m_assessment_kary_d_id: 0,
            keterangan: '',
            nilai: 0,
          }
        ]
      })
    }
  }

  if (initialValues && typeof initialValues === 'object') {
    for (const key in initialValues) {
      values[key] = initialValues[key]
    }
  }
})


// Table Detail
const detailArr = ref([])

// Tambah Detail Baru
const addDetail = () => {
  const tempItem = {
    m_assessment_kary_id: 0,
    nama_assessment: '',
    kategori: null,
    bobot: 0,
    m_assessment_kary_sub_d: [
      {
        m_assessment_kary_d_id: 0, // Penting untuk validasi backend
        keterangan: '',
        nilai: 0,
      }
    ]
  }
  detailArr.value.push(tempItem)
}

// Hapus Detail
const removeDetail = (index) => {
  if (detailArr.value.length > 1) {
    detailArr.value.splice(index, 1)
  } else {
    swal.fire({
      icon: 'warning',
      text: 'Harus memiliki minimal 1 detail assessment.',
      confirmButtonText: 'OK',
    })
  }
}

// Tambah Subdetail Baru
const addSubDetail = (parentIndex) => {
  const subDetail = {
    m_assessment_kary_d_id: 0, // Penting!
    keterangan: '',
    nilai: 0,
  }

  if (!detailArr.value[parentIndex].m_assessment_kary_sub_d) {
    detailArr.value[parentIndex].m_assessment_kary_sub_d = []
  }

  detailArr.value[parentIndex].m_assessment_kary_sub_d.push(subDetail)
}

// Hapus Subdetail
const removeSubDetail = (parentIndex, subIndex) => {
  const subDetails = detailArr.value[parentIndex].m_assessment_kary_sub_d

  if (subDetails && subDetails.length > 1) {
    subDetails.splice(subIndex, 1)
  } else {
    swal.fire({
      icon: 'warning',
      text: 'Subdetail tidak boleh kosong. Minimal 1 subdetail diperlukan.',
      confirmButtonText: 'OK',
    })
  }
}



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
  // Validasi: hanya bisa memilih 1 komponen per kategori
  const categories = detailArr.value.map(d => d.kategori).filter(Boolean);
  const uniqueCategories = new Set(categories);
  if (categories.length !== uniqueCategories.size) {
    swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Hanya bisa memilih 1 komponen per kategori'
    });
    return;
  }

  //values.tags = JSON.stringify(values.tags)
  try {
    values.m_assessment_kary_d = detailArr.value
    // console.log('kontol',values.m_assessment_kary_d)
    // values.m_assessment_kary_d_level =
    //   values.m_asssessment_kary_d_level.map(id => ({
    //     m_asssessment_kary_id: 1,
    //     m_level_posisi_id: id,
    //     creator_id: store.user?.data?.id
    //   }))

    const level = values.m_assessment_kary_d_level

    if (Array.isArray(level)) {
      values.m_assessment_kary_d_level = level
        .map(id => {
          const rawId = typeof id === 'object' && id !== null ? (id.m_level_posisi_id || id.id) : id
          const numId = parseInt(rawId)
          return isNaN(numId) ? null : {
            m_assessment_kary_id: isNaN(parseInt(route.params.id)) ? 0 : parseInt(route.params.id),
            m_level_posisi_id: numId,
            creator_id: store.user?.data?.id
          }
        })
        .filter(Boolean)
    } else {
      values.m_assessment_kary_d_level = []
    }


    // Inti onSave
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;
    isRequesting.value = true;

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
    });
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
      } else {
        throw ("Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
      }
    }
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())));
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'warning',
      text: err
    });
  }
  isRequesting.value = false;
}

//  @else----------------------- LANDING

let data = reactive({})
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

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = JSON.parse(localStorage.getItem('respo'));
    data.respo_id = respoValues.id;
    data.subcomp_id = respoValues.subcomp_id;
    data.branch_id = respoValues.branch_id;
  }
  if (data.respo_id) {
    const params = URLSearchParams({
      path: route.path,
      respo_id: data.respo_id
    })

    const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`;

    try {
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })

      const result = await response.json()
      console.log('result', result)
      data.can_read = result.can_read
      data.can_create = result.can_create
      data.can_update = result.can_update
      data.can_delete = result.can_delete
      data.rows = result.data

    } catch (err) {
      console.log(err);
    }
  }

})

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => (data?.can_delete),
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
      show: (row) => (data?.can_update),
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => (data?.can_create),
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
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      transform: true,
      divisi_name: true,
      searchfield: 'this.deskripsi , type.value , m_comp.name , m_subcomp.name , m_branch.name , m_divisi.name'
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
    headerName: 'SBU',
    field: 'm_comp.name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },


  {
    headerName: 'SUB',
    field: 'm_subcomp.name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },

  {
    headerName: 'BRANCH',
    field: 'm_branch.name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'LEVEL',
    field: 'level',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'DIVISI',
    field: 'm_divisi_name',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tipe Penilaian',
    field: 'type.value',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    wrapText: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Deskripsi',
    field: 'deskripsi',
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
    field: 'is_active',
    // filter: true,
    //filter: 'ColFilter',
    sortable: true,
    resizable: true, wrapText: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === true
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
        : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
    }
  }]
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