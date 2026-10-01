     //   javascript

import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, computed } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ REAKTIVITAS FORM DINAMIS PERSURATAN
const selectedJenisSurat = ref(null)

function onSelectJenisSurat(obj) {
  selectedJenisSurat.value = obj
  if (!isTipeMutasiVisible.value) {
    values.tipe_mutasi = obj?.value || 'Non-Mutasi / Persuratan'
  }
}

const currentJenisSuratName = computed(() => {
  return (selectedJenisSurat.value?.value || initialValues['jenis_surat.value'] || '').toUpperCase()
})

const isTipeMutasiVisible = computed(() => {
  const name = currentJenisSuratName.value
  return name.includes('MUTASI') && !name.includes('PROMOSI') && !name.includes('DEMOSI')
})

const isCareerMutation = computed(() => {
  const name = currentJenisSuratName.value
  if (!name) return true // default tampil sebelum user memilih
  return name.includes('MUTASI') ||
         name.includes('PROMOSI') ||
         name.includes('DEMOSI') ||
         name.includes('PENAMBAHAN TUGAS') ||
         name.includes('PENGANGKATAN') ||
         name.includes('TUNJANGAN JABATAN')
})

const isSuratTugas = computed(() => {
  const name = currentJenisSuratName.value
  return name.includes('SURAT TUGAS') || name.includes('PELATIHAN')
})

const detailPelatihan = reactive({
  pemateri: '',
  hari_tgl: '',
  jam: '',
  tempat: ''
})

const labelDeskripsi = computed(() => {
  const name = currentJenisSuratName.value
  if (name.includes('KETERANGAN KERJA')) return 'Keperluan Surat'
  if (name.includes('SURAT TUGAS')) return 'Tugas / Materi Pelatihan'
  if (name.includes('PKWT') || name.includes('PERJANJIAN')) return 'Rincian / Catatan Kontrak'
  return 'Deskripsi'
})

const placeholderDeskripsi = computed(() => {
  const name = currentJenisSuratName.value
  if (name.includes('KETERANGAN KERJA')) return 'Contoh: Pengajuan KPR / Pencairan BPJS / Visa'
  if (name.includes('SURAT TUGAS')) return 'Contoh: Pelatihan & Sertifikasi Ahli K3 Umum'
  return 'Tuliskan Deskripsi'
})

const labelKeterangan = computed(() => {
  const name = currentJenisSuratName.value
  if (name.includes('PENGALAMAN KERJA')) return 'Alasan Berakhir Kerja'
  if (name.includes('SURAT TUGAS')) return 'Penyelenggara / Jadwal & Tempat'
  return 'Keterangan'
})

const placeholderKeterangan = computed(() => {
  const name = currentJenisSuratName.value
  if (name.includes('PENGALAMAN KERJA')) return 'Contoh: Resign / Pensiun Dini / Selesai Masa Kontrak'
  if (name.includes('SURAT TUGAS')) return 'Contoh: PT. Trust Bimo Indonesia, 22 April - 06 Mei 2026, Via Zoom'
  return 'Tuliskan Keterangan'
})

function onPrintSurat() {
  const targetId = route.params.id
  if (!targetId || targetId === 'create') return
  const name = currentJenisSuratName.value
  let endpoint = 'sk_mutasi'
  if (name.includes('PROMOSI') || name.includes('TUNJANGAN')) endpoint = 'sk_promosi'
  else if (name.includes('DEMOSI')) endpoint = 'sk_demosi'
  else if (name.includes('PENGANGKATAN') || name.includes('PKWTT') || name.includes('TETAP')) endpoint = 'sk_tetap'
  else if (name.includes('PENAMBAHAN TUGAS')) endpoint = 'sk_penambahan_tugas'
  else if (name.includes('PENGALAMAN KERJA')) endpoint = 'sk_pengalaman_kerja'
  else if (name.includes('KETERANGAN KERJA') || name.includes('SKK')) endpoint = 'sk_kerja'
  else if (name.includes('SURAT TUGAS') || name.includes('PELATIHAN')) endpoint = 'sk_tugas_pelatihan'
  else if (name.includes('PERJANJIAN BERSAMA') || name.includes('BERAKHIR') || name.includes('PEMBERHENTIAN')) endpoint = 'sk_phk'
  else if (name.includes('FREELANCE') || name.includes('HARIAN LEPAS') || name.includes('PHL')) endpoint = 'sk_freelance'
  else if (name.includes('PKWT')) endpoint = 'sk_pkwt'
  else if (name.includes('PERINGATAN') || name.includes('SP')) endpoint = 'sk_sp'
  else if (name.includes('MAGANG') || name.includes('PKL') || name.includes('PENERIMAAN')) endpoint = 'sk_penerimaan'

  const url = `${store.server.url_backend}/web/${endpoint}?export=pdf&orientation=potrait&id=${targetId}`
  window.open(url, '_blank')
}

// ------------------------------ PERSIAPAN
const endpointApi = '/t_mutasi'
onBeforeMount(() => {
  document.title = 'Mutasi & Persuratan Karyawan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  tgl: `${String(new Date().getDate()).padStart(2, '0')}/${String(new Date().getMonth() + 1).padStart(2, '0')}/${new Date().getFullYear()}`,
  status: 'DRAFT',
  t_mutasi_d_tembusan: [],
  t_mutasi_d_memperhatikan: []
})

onBeforeMount(async () => {
  values.direktorat = store.user.data?.direktorat;
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
      if (!values.t_mutasi_d_tembusan) values.t_mutasi_d_tembusan = []
      if (!values.t_mutasi_d_memperhatikan) values.t_mutasi_d_memperhatikan = []
      // initialValues.status=(initialValues.status==='Aktif')?true:false

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

  if (initialValues.keterangan) {
    try {
      const parsed = JSON.parse(initialValues.keterangan)
      if (parsed && typeof parsed === 'object') {
        detailPelatihan.pemateri = parsed.pemateri || ''
        detailPelatihan.hari_tgl = parsed.hari_tgl || ''
        detailPelatihan.jam = parsed.jam || ''
        detailPelatihan.tempat = parsed.tempat || ''
      }
    } catch (e) {
      detailPelatihan.tempat = initialValues.keterangan || ''
    }
  }
})

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

async function posted() {
  const payload = {
    id: route.params.id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/postData`
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    })
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}
        throw (responseJson.message || "Failed when trying to post data")
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
          if (initialValues.keterangan) {
            try {
              const parsed = JSON.parse(initialValues.keterangan)
              if (parsed && typeof parsed === 'object') {
                detailPelatihan.pemateri = parsed.pemateri || ''
                detailPelatihan.hari_tgl = parsed.hari_tgl || ''
                detailPelatihan.jam = parsed.jam || ''
                detailPelatihan.tempat = parsed.tempat || ''
              }
            } catch (e) {
              detailPelatihan.tempat = initialValues.keterangan || ''
            }
          }
        } else {
          for (const key in values) {
            delete values[key]
          }
          detailPelatihan.pemateri = ''
          detailPelatihan.hari_tgl = ''
          detailPelatihan.jam = ''
          detailPelatihan.tempat = ''
          defaultValues()
        }
      }
    })
  }

  setTimeout(() => {
    defaultValues()
  }, 100)
}

function onSave() {
  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    const payload = { ...values };
    if (!payload.tipe_mutasi) {
      payload.tipe_mutasi = selectedJenisSurat.value?.value || initialValues['jenis_surat.value'] || 'Non-Mutasi / Persuratan';
    }
    if (!payload.no_dokumen) {
      payload.no_dokumen = payload.nomor || '-';
    }
    if (!payload.deskripsi) {
      payload.deskripsi = '-';
    }
    if (isSuratTugas.value) {
      payload.keterangan = JSON.stringify({
        pemateri: detailPelatihan.pemateri || '',
        hari_tgl: detailPelatihan.hari_tgl || '',
        jam: detailPelatihan.jam || '',
        tempat: detailPelatihan.tempat || ''
      });
    }
    payload.t_mutasi_d_tembusan = (values.t_mutasi_d_tembusan || []).filter(i => i && i.value);
    payload.t_mutasi_d_memperhatikan = (values.t_mutasi_d_memperhatikan || []).filter(i => i && i.value);
    fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    }).then(async (res) => {
      if (!res.ok) {
        if ([400, 422].includes(res.status)) {
          const responseJson = await res.json()
          formErrors.value = responseJson.errors || {}
          const errorMsg = responseJson.message || Object.values(responseJson.errors || {})[0]?.[0] || "Failed when trying to post data";
          throw new Error(errorMsg)
        } else {
          throw new Error("Failed when trying to post data")
        }
      }
      router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())))
    }).catch((err) => {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err.message || 'Harap Lengkapi Data'
      })
    }).finally(() => {
      isRequesting.value = false
    });
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: 'Harap Lengkapi Data'
    })
    isRequesting.value = false
  }
}

const addMemperhatikan = () => {
  values.t_mutasi_d_memperhatikan.push({ value: '' })
}

const addTembusan = () => {
  values.t_mutasi_d_tembusan.push({ value: '' })
}

//  @else----------------------- LANDING

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
    } catch (err) {
      console.error(err)
    }
  }
})

const activeBtn = ref()

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'DRAFT',
    2: 'POSTED',
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

  // const [dateFrom, dateTo] = [valLand.start_date, valLand.end_date].map(d =>
  //   d ? parseTanggalToYMD(d) : null
  // )

  // if (dateFrom || dateTo) {
  //   const conditions = []
  //   if (dateFrom) conditions.push(`this.date >= '${dateFrom}'`)
  //   if (dateTo) conditions.push(`this.date <= '${dateTo}'`)
  //   filters.push(conditions.join(' AND '))
  // }
  console.log("status", statusLabel)
  console.log("activeBtn", activeBtn.value)
  landing.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_delete,
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
              if (!res.ok) throw ("Failed when trying to remove data")
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
      show: (row) => data.can_read,
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_update,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_create,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row.status?.toUpperCase() === 'DRAFT' && data.can_update,
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
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/post`
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
      icon: 'print',
      title: "Print Surat",
      class: 'bg-purple-600 text-light-100',
      show: (row) => data.can_create || data.can_read,
      click(row) {
        const name = (row['jenis_surat.value'] || '').toUpperCase();
        let endpoint = 'sk_mutasi';
        if (name.includes('PROMOSI') || name.includes('TUNJANGAN')) endpoint = 'sk_promosi';
        else if (name.includes('DEMOSI')) endpoint = 'sk_demosi';
        else if (name.includes('PENGANGKATAN') || name.includes('PKWTT') || name.includes('TETAP')) endpoint = 'sk_tetap';
        else if (name.includes('PENAMBAHAN TUGAS')) endpoint = 'sk_penambahan_tugas';
        else if (name.includes('PENGALAMAN KERJA')) endpoint = 'sk_pengalaman_kerja';
        else if (name.includes('KETERANGAN KERJA') || name.includes('SKK')) endpoint = 'sk_kerja';
        else if (name.includes('SURAT TUGAS') || name.includes('PELATIHAN')) endpoint = 'sk_tugas_pelatihan';
        else if (name.includes('PERJANJIAN BERSAMA') || name.includes('BERAKHIR') || name.includes('PEMBERHENTIAN')) endpoint = 'sk_phk';
        else if (name.includes('FREELANCE') || name.includes('HARIAN LEPAS') || name.includes('PHL')) endpoint = 'sk_freelance';
        else if (name.includes('PKWT')) endpoint = 'sk_pkwt';
        else if (name.includes('PERINGATAN') || name.includes('SP')) endpoint = 'sk_sp';
        else if (name.includes('MAGANG') || name.includes('PKL') || name.includes('PENERIMAAN')) endpoint = 'sk_penerimaan';

        const url = `${store.server.url_backend}/web/${endpoint}?export=pdf&orientation=potrait&id=${row.id}`;
        window.open(url, '_blank');
      }
    },

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
      simplest: true,
      //scopes:'landing',
      searchfield: 'this.nomor , m_kary.kode , m_kary.nama_lengkap , m_posisi_lama.name , m_posisi_baru.name',
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
    headerName: 'No. Surat',
    filter: true,
    sortable: true,
    flex: 1.2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'm_kary.kode',
    headerName: 'NIP',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'm_kary.nama_lengkap',
    headerName: 'Nama Karyawan',
    filter: true,
    sortable: true,
    flex: 1.3,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left']
  },
  {
    field: 'jenis_surat.value',
    headerName: 'Jenis Surat',
    filter: true,
    sortable: true,
    flex: 1.3,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left'],
    cellRenderer: ({ value }) => {
      if (!value) return '-'
      return `<span class="font-semibold text-gray-800">${value}</span>`
    }
  },
  {
    field: 'tgl',
    headerName: 'Tanggal Surat',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-center']
  },
  {
    field: 'm_posisi_lama.name',
    headerName: 'Jabatan Saat Ini',
    filter: true,
    sortable: true,
    flex: 1.2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left'],
    cellRenderer: ({ value }) => value || '-'
  },
  {
    field: 'm_posisi_baru.name',
    headerName: 'Jabatan Baru',
    filter: true,
    sortable: true,
    flex: 1.2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-left'],
    cellRenderer: ({ value }) => value || '-'
  },
  {
    headerName: 'Status',
    field: 'status',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      let color = 'gray'
      if (value == 'POSTED')
        color = 'green'
      else if (value == 'IN APPROVAL')
        color = 'blue'
      else if (value == 'REVISED')
        color = 'yellow'
      else if (value == 'REJECTED')
        color = 'red'
      return `<span class="text-${color}-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
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