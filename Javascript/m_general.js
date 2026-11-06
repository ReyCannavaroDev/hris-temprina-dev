import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount,watch, computed,onBeforeUnmount, onUnmounted, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul

const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/m_general'
onBeforeMount(() => {
  document.title = 'Master General'
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
  // console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []

const values = reactive({
  is_active: 1
})

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

onBeforeMount(async () => {
  // tampilkan default direktorat dengan store user comp.nama
  values.direktorat = store.user.data?.direktorat
  if (isRead) {
    //  READ DATA
    try {
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
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
      initialValues.is_active = initialValues.is_active ? 1 : 0
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
  if (values['m_dir.nama']) {
    values.direktorat = values['m_dir.nama']
  }
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

function onBack() {
  router.replace('/' + modulPath)
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    values.is_active = values.is_active ? 1 : 0
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

const data = reactive({
  can_read: false,
  can_create: false,
  can_update: false,
  can_delete: false,
  respo_id: null
})

const isAccessReady = ref(false)

onBeforeMount(async () => {

  try {

    const respo = localStorage.getItem('respo')

    if (respo) {
      const v = JSON.parse(respo)
      data.respo_id = v.id
    }

    if (!data.respo_id) return

    const params = new URLSearchParams({
      path: route.path,
      respo_id: data.respo_id
    })

    const endpoint =
      `${store.server.url_backend}/operation/m_general/access?${params}`

    const res = await fetch(endpoint, {
      headers: {
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })

    if (!res.ok) throw "Failed access API"

    const r = await res.json()

    // masuk ke tampungan dulu
    data.can_read = r.can_read
    data.can_create = r.can_create
    data.can_update = r.can_update
    data.can_delete = r.can_delete

    console.log("DATA TAMPUNGAN", data)

    // baru aktifkan landing
    isAccessReady.value = true

  } catch (err) {
    console.error(err)
  }

})

var optGroup = []
onBeforeMount(async () => {
  try {
    const dataURL = `${store.server.url_backend}/operation/m_general/getByGroup`
    const params = {
      simplest: true,
    }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    const resultJson = await res.json();
    if (resultJson.data.length > 0) {
      resultJson.data?.forEach((item) => {
        optGroup.push(item.group)
      })
    }
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'warning',
      text: err,
      allowOutsideClick: false,
    })
  }
  isRequesting.value = false
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



const landing = computed(() => {

  if (!isAccessReady.value) return null

  return {

    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        title: "Hapus",
        show: () => data?.can_delete === true,

        async click(row) {

          const result = await swal.fire({
            icon: 'warning',
            text: 'Hapus Data Terpilih?',
            confirmButtonText: 'Yes',
            showDenyButton: true
          })

          if (!result.isConfirmed) return

          try {

            isRequesting.value = true

            const dataURL =
              `${store.server.url_backend}/operation/${endpointApi}/destroy`

            const res = await fetch(dataURL, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              body: JSON.stringify({
                id: row.id
              })
            })

            if (!res.ok) {
              const resultJson = await res.json()
              throw (resultJson.message || 'Failed when trying to remove data')
            }

            swal.fire({
              icon: 'success',
              text: 'Data berhasil dihapus'
            })

            apiTable.value.reload()

          } catch (err) {

            isBadForm.value = true

            swal.fire({
              icon: 'error',
              text: err
            })

          } finally {
            isRequesting.value = false
          }
        }
      },

      {
        icon: 'eye',
        title: "Read",
        class: 'bg-green-600 text-light-100',
        show: () => data?.can_read === true,

        click(row) {
          router.push(`${route.path}/${row.id}?${tsId}`)
        }
      },

      {
        icon: 'edit',
        title: "Edit",
        class: 'bg-blue-600 text-light-100',
        show: () => data?.can_update === true,

        click(row) {
          router.push(`${route.path}/${row.id}?action=Edit&${tsId}`)
        }
      },

      {
        icon: 'copy',
        title: "Copy",
        class: 'bg-gray-600 text-light-100',
        show: () => data?.can_create === true,

        click(row) {
          router.push(`${route.path}/${row.id}?action=Copy&${tsId}`)
        }
      }
    ],

    api: {
      url: data?.can_read
        ? `${store.server.url_backend}/operation${endpointApi}`
        : null,

      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },

      params: {
        scopes: 'NotIncludeByGroup',
        simplest: true,
        searchfield: 'this.id, this.group, this.key, this.code, this.value',
        paginate: 250
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
        cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
      },

      {
        headerName: 'Code',
        field: 'code',
        flex: 1,
        filter: 'ColFilter',
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },

      {
        headerName: 'Group',
        field: 'group',
        flex: 1,
        filter: 'ColFilter',
        filterParams: {
          options: optGroup
        },
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },

      {
        field: 'key',
        flex: 1,
        filter: 'ColFilter',
        sortable: true,
        cellClass: ['border-r', '!border-gray-200']
      },

      {
        field: 'code',
        flex: 1,
        filter: 'ColFilter',
        sortable: true,
        cellClass: ['border-r', '!border-gray-200']
      },

      {
        field: 'value',
        flex: 1,
        filter: 'ColFilter',
        sortable: true,
        cellClass: ['border-r', '!border-gray-200']
      },

      {
        headerName: 'Status',
        field: 'is_active',
        flex: 1,
        filter: 'ColFilter',
        sortable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-center'],

        cellRenderer: ({ value }) => {
          return value
            ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block">Active</span>`
            : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block">Inactive</span>`
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