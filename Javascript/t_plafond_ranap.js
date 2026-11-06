import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated, computed } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
console.log('test', store.user.data)
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
const endpointApi = 't_plafond_ranap'
onBeforeMount(() => {
  document.title = 'Transaksi Plafond Rawat Inap'
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const currentYear = new Date().getFullYear()

const years = Array.from({ length: 10 }, (_, i) => {
  const year = currentYear + i
  return {
    id: year,
    key: year
  }
})
const handleKeyDown = (event) => {

  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault();
    onSave();
  }
}

let initialValues = {}

let values = reactive({
  status: 'DRAFT'
})

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

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))

    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }

  onReset()

  if (isRead) {
    try {
      const editedId = route.params.id
      console.log('test', editedId)

      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${editedId}`
      isRequesting.value = true

      const params = { join: true, transform: false }
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

  // assign data ke values
  for (const key in initialValues) {
    values[key] = initialValues[key]
  }

  // parse integer field ke string untuk form
  if (values.tahun !== null && values.tahun !== undefined) {
    values.tahun = String(values.tahun)
  }
})

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_perdin')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_perdin')
  } else {
    router.replace('/' + modulPath)
  }
  return
}

async function onSave() {
  try {

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;

    isRequesting.value = true;

    const payload = {
      ...values,
      tahun: values.tahun ? parseInt(values.tahun) : null
    };

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    });

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (responseJson.errors?.length ? responseJson.errors[0] : responseJson.message || "Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
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
const activeBtn = ref()
let data = reactive({})

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
    landing.api.params.where = `this.status='Active'`
  } else {
    landing.api.params.where = `this.status='InActive'`
  }

  apiTable.value.reload()
}

const landing = computed(() => {
  if (!isAccessReady.value) return null

  return {
    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        title: "Hapus",
        show: () => data.can_delete,
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
        click(row) {
          router.push(`${route.path}/${row.id}?` + tsId)
        }
      },
      {
        icon: 'edit',
        title: "Edit",
        class: 'bg-blue-600 text-light-100',
        show: () => data.can_update,
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
      }
    ],
    api: {
      url: data.can_read ? `${store.server.url_backend}/operation/${endpointApi}` : null,
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        m_subcomp_id: data.subcomp_id,
        m_branch_id: data.branch_id,
        simplest: true,
        searchfield: 'this.id, m_kary.nama_lengkap, this.tugas, this.tempat_tujuan, this.date_from, this.date_to'
      },
      onsuccess(response) {
        response.page = response.current_page
        response.hasNext = response.has_next
        return response
      }
    },
    columns: [
      {
        headerName: 'No',
        valueGetter: (params) => params.node.rowIndex + 1,
        width: 60,
        sortable: true,
        resizable: true,
        filter: true,
        cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
      },
      {
        field: 'm_kary.nama_lengkap',
        headerName: 'Karyawan',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        wrapText: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        field: 'tahun',
        headerName: 'Tahun',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        wrapText: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        field: 'plafond',
        headerName: 'Plafond',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        wrapText: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        field: 'status',
        headerName: 'Status',
        filter: true,
        filter: 'ColFilter',
        sortable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-center'],
        cellRenderer: ({ value }) => {
          const v = String(value).toLowerCase()
          return v === 'active'
            ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">DRAFT</span>`
            : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">POSTED</span>`
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

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))