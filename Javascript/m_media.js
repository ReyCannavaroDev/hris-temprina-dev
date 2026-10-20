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

function getWebBaseUrl() {
  const backend = (store.server?.url_backend || '').replace(/\/api\/?$/i, '').replace(/\/$/, '')
  if (backend) return backend
  if (typeof window !== 'undefined' && window.location?.origin) {
    return window.location.origin
  }
  return ''
}

function getBackendUrl() {
  const backend = (store.server?.url_backend || '').replace(/\/$/, '')
  if (backend) return backend
  if (typeof window !== 'undefined' && window.location?.origin) {
    return `${window.location.origin}/api`
  }
  return '/api'
}

// Menghasilkan daftar seluruh kandidat URL aset media yang valid
function getMediaCandidateUrls(path, kode = '', id = '') {
  const rawPath = String(path || '').trim()
  if (rawPath.startsWith('data:image') || rawPath.startsWith('blob:')) {
    return [rawPath]
  }

  const backend = getBackendUrl()
  const webBase = getWebBaseUrl()
  const urls = []

  // 1. Prioritas Utama: Streaming langsung via endpoint backend web/media_preview
  const queryParts = []
  if (id) queryParts.push(`id=${encodeURIComponent(id)}`)
  if (kode) queryParts.push(`kode=${encodeURIComponent(kode)}`)
  if (rawPath) queryParts.push(`path=${encodeURIComponent(rawPath)}`)

  if (queryParts.length > 0) {
    urls.push(`${backend}/web/media_preview?${queryParts.join('&')}`)
  }

  if (id) {
    urls.push(`${backend}/web/media_preview?id=${encodeURIComponent(id)}`)
  }
  if (kode) {
    urls.push(`${backend}/web/media_preview?kode=${encodeURIComponent(kode)}`)
  }
  if (rawPath) {
    urls.push(`${backend}/web/media_preview?path=${encodeURIComponent(rawPath)}`)
  }

  // 2. Jika rawPath adalah URL eksternal absolut
  if (rawPath.startsWith('http://') || rawPath.startsWith('https://')) {
    urls.push(rawPath)
  }

  // 3. Fallback direktori statis lokal jika web server memiliki berkas
  let hashPart = rawPath
  let namePart = ''
  if (rawPath.includes(':::')) {
    const parts = rawPath.split(':::')
    hashPart = parts[0].trim()
    namePart = (parts[1] || '').trim()
  }

  const cleanHash = hashPart.replace(/^\//, '').replace(/^storage\//, '')
  const cleanName = namePart.replace(/^\//, '').replace(/^storage\//, '')
  const baseHash = cleanHash.split('/').pop()
  const baseName = cleanName.split('/').pop()
  const rawBase = rawPath.split('/').pop()

  const fileNames = [
    cleanName,
    baseName,
    cleanHash,
    baseHash,
    rawBase,
  ].filter(Boolean)

  const uniqueFiles = Array.from(new Set(fileNames))
  const prefixes = [
    'storage/uploads/m_media',
    'uploads/m_media',
    'media',
    'storage',
    'images',
    ''
  ]

  for (const prefix of prefixes) {
    for (const f of uniqueFiles) {
      const cleanF = f.replace(/^uploads\/m_media\//, '').replace(/^media\//, '').replace(/^storage\//, '').replace(/^\//, '')
      if (!cleanF) continue

      const enc = encodeURIComponent(cleanF)
      urls.push(prefix ? `${webBase}/${prefix}/${enc}` : `${webBase}/${enc}`)

      if (enc !== cleanF) {
        urls.push(prefix ? `${webBase}/${prefix}/${cleanF}` : `${webBase}/${cleanF}`)
      }
    }
  }

  urls.push(`${webBase}/images/logo.png`)

  return Array.from(new Set(urls.filter(Boolean)))
}

function getMediaPreview(path, kode = '', id = '') {
  const candidates = getMediaCandidateUrls(path, kode, id)
  return candidates[0] || ''
}

// Reactive state untuk modal pratinjau gambar penuh (lightbox)
const modalPreview = reactive({
  show: false,
  src: '',
  title: '',
  code: ''
})

function openPreviewModal(src, title = '', code = '') {
  modalPreview.src = src
  modalPreview.title = title
  modalPreview.code = code
  modalPreview.show = true
}

function onImgPreviewError(e) {
  const target = e.target
  if (!target) return
  if (!target.dataset.candidates) {
    const list = getMediaCandidateUrls(values.file_path, values.kode, values.id)
    target.dataset.candidates = JSON.stringify(list)
    target.dataset.step = '0'
  }
  let list = []
  try {
    list = JSON.parse(target.dataset.candidates || '[]')
  } catch (err) {
    list = []
  }
  let step = parseInt(target.dataset.step || '0', 10)
  if (step < list.length) {
    const nextUrl = list[step]
    target.dataset.step = String(step + 1)
    target.src = nextUrl
  } else {
    target.onerror = null
  }
}

// Registrasi global handler untuk AG-Grid / TableApi cellRenderer
if (typeof window !== 'undefined') {
  window.__mMediaHandleImgError = function(imgEl) {
    if (!imgEl) return
    let fallback = []
    try {
      fallback = JSON.parse(imgEl.getAttribute('data-fallback') || '[]')
    } catch (e) {
      fallback = []
    }
    let step = parseInt(imgEl.getAttribute('data-step') || '0', 10)
    if (step < fallback.length) {
      const nextUrl = fallback[step]
      imgEl.setAttribute('data-step', String(step + 1))
      imgEl.src = nextUrl
    } else {
      imgEl.onerror = null
      imgEl.style.display = 'none'
      const parent = imgEl.parentElement
      if (parent && !parent.querySelector('.no-preview-badge')) {
        const badge = document.createElement('span')
        badge.className = 'no-preview-badge text-[11px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-200 inline-flex items-center gap-1 font-sans italic'
        badge.innerHTML = '<i class="fa fa-image text-gray-300"></i> No Image'
        parent.appendChild(badge)
      }
    }
  }

  window.__mMediaOpenModal = function(imgEl) {
    if (!imgEl) return
    const currentSrc = imgEl.currentSrc || imgEl.src
    const nama = imgEl.getAttribute('data-nama') || ''
    const kode = imgEl.getAttribute('data-kode') || ''
    openPreviewModal(currentSrc, nama, kode)
  }

  // Intersepsi klik pada tombol mata (fa-eye) di FieldUpload menggunakan Capture Phase
  document.addEventListener('click', function(e) {
    const eyeBtn = e.target?.closest?.('.fa-eye, [title="Open Image"]') ||
                   (e.target?.classList?.contains('fa-eye') ? e.target : null)
    if (eyeBtn && eyeBtn.closest('.form-input')) {
      if (typeof values !== 'undefined' && values.file_path) {
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()
        const previewUrl = getMediaPreview(values.file_path, values.kode, values.id)
        if (previewUrl) {
          openPreviewModal(previewUrl, values.nama || 'Aset Media', values.kode || '')
        }
      }
    }
  }, true)
}

function onFieldUploadClick(e) {
  const target = e.target
  if (!target) return
  const eyeBtn = target.closest?.('.fa-eye, [title="Open Image"]') ||
                 (target.classList?.contains('fa-eye') ? target : null)
  if (eyeBtn) {
    e.preventDefault()
    e.stopPropagation()
    e.stopImmediatePropagation()
    const previewUrl = getMediaPreview(values.file_path, values.kode, values.id)
    if (previewUrl) {
      openPreviewModal(previewUrl, values.nama || 'Aset Media', values.kode || '')
    }
  }
}

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
        width: 130,
        sortable: false,
        filter: false,
        cellClass: ['justify-center', 'border-r', '!border-gray-200', 'items-center'],
        cellRenderer: (params) => {
          const raw = String(params.value || '').trim()
          const kode = String(params.data?.kode || '').trim()
          const nama = String(params.data?.nama || '').trim()
          const id = params.data?.id || ''
          const candidates = getMediaCandidateUrls(raw, kode, id)
          if (!candidates.length) {
            return '<span class="text-xs text-gray-400 italic">No Image</span>'
          }
          const initialSrc = candidates[0]
          const fallbackData = JSON.stringify(candidates.slice(1)).replace(/"/g, '&quot;')
          const safeNama = nama.replace(/'/g, "\\'")
          const safeKode = kode.replace(/'/g, "\\'")

          return `
            <div class="flex items-center justify-center p-1 w-full h-full">
              <img src="${initialSrc}" 
                   data-fallback="${fallbackData}" 
                   data-step="0" 
                   data-kode="${safeKode}"
                   data-nama="${safeNama}"
                   onclick="window.__mMediaOpenModal && window.__mMediaOpenModal(this)"
                   onerror="window.__mMediaHandleImgError && window.__mMediaHandleImgError(this)" 
                   class="max-h-8 max-w-[85px] h-auto w-auto object-contain rounded border border-gray-200 p-0.5 bg-white shadow-sm cursor-pointer hover:scale-110 hover:border-blue-400 transition-all duration-200" 
                   title="Klik untuk melihat pratinjau penuh"
                   alt="Thumbnail" />
            </div>
          `
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