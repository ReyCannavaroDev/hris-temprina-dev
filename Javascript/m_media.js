import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, computed, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul || 'm_media'
const currentMenu = store.currentMenu
const data = computed(() => store.currentMenu || { can_create: true, can_read: true, can_update: true, can_delete: true })
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const activeBtn = ref(1)

// ------------------------------ PERSIAPAN
const endpointApi = '/m_media'
onBeforeMount(() => {
  document.title = 'Master Media & Aset Visual'
})

// ------------------------------ FORM VALUES
let initialValues = {}
const values = reactive({
  is_active: true,
  kode: null,
  nama: null,
  kategori_id: null,
  m_comp_id: null,
  file_path: null,
  keterangan: null,
})

function onBack() {
  router.replace('/' + modulPath)
}

function onReset(isManual = false) {
  if (isManual) {
    swal.fire({
      icon: 'warning',
      text: 'Reset semua inputan formulir?',
      showDenyButton: true,
      confirmButtonText: 'Ya, Reset',
      denyButtonText: 'Batal'
    }).then((res) => {
      if (res.isConfirmed) {
        for (const key in initialValues) {
          values[key] = initialValues[key]
        }
      }
    })
  } else {
    for (const key in initialValues) {
      values[key] = initialValues[key]
    }
  }
}

function filterShowData(val, btnIndex) {
  activeBtn.value = btnIndex
  if (apiTable.value) {
    if (val === null) {
      apiTable.value.params.where = ''
    } else {
      apiTable.value.params.where = `this.is_active = ${val}`
    }
    apiTable.value.reload()
  }
}

onBeforeMount(async () => {
  if (isRead && (currentMenu?.can_read ?? true)) {
    try {
      isRequesting.value = true
      const editedId = route.params.id
      const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
      const params = { join: true, transform: false }
      const fixedParams = new URLSearchParams(params)
      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })
      if (!res.ok) throw new Error("Gagal mengambil data media")
      const resultJson = await res.json()
      initialValues = resultJson.data || {}
      initialValues.is_active = Boolean(initialValues.is_active)
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

async function onSave() {
  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value) || route.params.id === 'create'
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true

    const payload = {
      ...values,
      is_active: Boolean(values.is_active)
    }

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    })

    if (!res.ok) {
      const responseJson = await res.json()
      formErrors.value = responseJson.errors || {}
      throw (responseJson.message || "Gagal menyimpan data media")
    }

    swal.fire({
      icon: 'success',
      text: 'Data Media berhasil disimpan!',
      timer: 1500,
      showConfirmButton: false
    })

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

// ------------------------------ LANDING CONFIG
const landing = computed(() => {
  const data = store.currentMenu || { can_create: true, can_read: true, can_update: true, can_delete: true }

  return {
    actions: [
      {
        icon: 'edit',
        title: "Edit",
        class: 'bg-blue-600 text-white rounded-md p-1.5',
        show: () => data.can_update,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
        }
      },
      {
        icon: 'copy',
        title: "Copy",
        class: 'bg-amber-600 text-white rounded-md p-1.5',
        show: () => data.can_create,
        click(row) {
          router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
        }
      },
      {
        icon: 'trash',
        title: "Hapus",
        class: 'bg-red-600 text-white rounded-md p-1.5',
        show: () => data.can_delete,
        click(row) {
          swal.fire({
            icon: 'warning',
            text: `Hapus media "${row.nama}"?`,
            confirmButtonText: 'Ya, Hapus',
            showDenyButton: true,
            denyButtonText: 'Batal'
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
                if (!res.ok) throw new Error("Gagal menghapus data media")
                if (apiTable.value) apiTable.value.reload()
                swal.fire({
                  icon: 'success',
                  text: 'Data berhasil dihapus!',
                  timer: 1500,
                  showConfirmButton: false
                })
              } catch (err) {
                swal.fire({ icon: 'error', text: err })
              }
              isRequesting.value = false
            }
          })
        }
      }
    ],
    api: {
      url: data.can_read ? `${store.server.url_backend}/operation${endpointApi}` : '',
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        where: 'this.is_active = true',
        searchfield: 'this.id, this.kode, this.nama, m_general.value, m_comp.name, this.keterangan',
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
        sortable: false,
        resizable: false,
        filter: false,
        cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
      },
      {
        headerName: "Kode",
        field: 'kode',
        filter: 'ColFilter',
        sortable: true,
        width: 140,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        headerName: "Nama Media",
        field: 'nama',
        filter: 'ColFilter',
        sortable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-start', 'font-semibold']
      },
      {
        headerName: "Kategori",
        field: 'kategori.value',
        valueGetter: (p) => p.data?.kategori?.value || p.data?.['m_general.value'] || p.data?.kategori_id || '-',
        filter: 'ColFilter',
        sortable: true,
        width: 140,
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        headerName: "Unit / Company",
        field: 'm_comp.name',
        valueGetter: (p) => p.data?.m_comp?.name || p.data?.['m_comp.name'] || 'Semua Unit',
        filter: 'ColFilter',
        sortable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
      },
      {
        headerName: "Preview",
        field: 'file_path',
        width: 120,
        cellClass: ['justify-center', 'border-r', '!border-gray-200'],
        cellRenderer: (params) => {
          if (!params.value) return '<span class="text-xs text-gray-400 italic">No Image</span>'
          const imgUrl = params.value.startsWith('http') ? params.value : `${store.server.url_backend}/${params.value.replace(/^\//, '')}`
          return `<img src="${imgUrl}" class="max-h-8 max-w-[80px] object-contain rounded border p-0.5 bg-white shadow-sm" alt="Thumbnail" />`
        }
      },
      {
        headerName: "Status",
        field: 'is_active',
        width: 110,
        cellClass: ['justify-center', 'border-r', '!border-gray-200'],
        cellRenderer: (params) => {
          const isActive = params.value === true || params.value === 1 || params.value === '1'
          return isActive
            ? '<span class="px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Aktif</span>'
            : '<span class="px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Non Aktif</span>'
        }
      }
    ]
  }
})

onActivated(() => {
  if (apiTable.value && route.query.reload) {
    apiTable.value.reload()
  }
})

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))