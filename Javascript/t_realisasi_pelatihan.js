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
const is_approval = route.query.is_approval ? true : false
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
let isApproved = ref(false)
let modalOpen = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })



// ------------------------------ PERSIAPAN
const endpointApi = '/t_realisasi_pelatihan'
onBeforeMount(() => {
  document.title = 'Realisasi Pelatihan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

onBeforeMount(async () => {
  document.title = is_approval ? 'Approval Realisasi Pelatihan' : 'Transasi Realisasi Pelatihan'
  // console.log('cek hc', store.user.data.is_hc)

  const respoData = localStorage.getItem('respo')
  if (respoData) {
    const respoValues = JSON.parse(respoData)
    // console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
    // console.log('ini comp',values.m_comp_id)
  }
})

const values = reactive({
  status: 'ACTIVE',
  // direktorat: store.user.data?.direktorat
})

onBeforeMount(async () => {
  if (isRead && currentMenu?.can_read) {
    try {
      if (route.query.is_approval) {
        const dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/app_detail?id=${route.params.id}`
        isRequesting.value = true
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        const resultJson = await apiApp.json()
        const apiTrxParams = new URLSearchParams({ join: true, transform: true })
        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}?${apiTrxParams}`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        if (!apiTrx.ok || !apiApp.ok) throw new Error('Failed when trying to read data')
        const resultTrxJson = await apiTrx.json()
        values.interval = resultJson.data.approval
        values.approval = resultJson.data.approval
        values.trx = resultJson.data.trx
        values.datalog = resultJson.data.approval_log
        initialValues = resultTrxJson.data

        const arr = initialValues.t_realisasi_pelatihan_d_kary || []
        const fetchName = async (url) => {
          const r = await fetch(url, { headers: { Authorization: `${store.user.token_type} ${store.user.token}` } })
          return r.ok ? (await r.json()).data?.name || '' : ''
        }
        const fetchKaryInfo = async (mKaryId) => {
          if (!mKaryId) return { atasan_id: null, atasan_kary: '-' }
          try {
            const r = await fetch(`${store.server.url_backend}/operation/m_kary/${mKaryId}?join=true`, {
              headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
            })
            if (!r.ok) return { atasan_id: null, atasan_kary: '-' }
            const json = await r.json()
            const d = json.data || {}
            return {
              atasan_id: d.atasan_id || d['atasan.id'] || null,
              atasan_kary: d['atasan.nama_lengkap'] || d['atasan.nama_depan'] || (d.atasan ? (d.atasan.nama_lengkap || d.atasan.nama_depan) : '') || '-'
            }
          } catch {
            return { atasan_id: null, atasan_kary: '-' }
          }
        }

        detailArr.value = await Promise.all(
          arr.map(async dt => {
            const divisiId = dt['m_kary.m_divisi_id']
            const divisi_kary = divisiId ? await fetchName(`${store.server.url_backend}/operation/m_divisi/${divisiId}`) : ''
            const mKaryId = dt.m_kary_id || dt['m_kary.id'] || null
            let atasan_id = dt['m_kary.atasan_id'] || dt.atasan_id || null
            let atasan_kary = dt['atasan.nama_lengkap'] || dt.atasan_kary || ''
            if (!atasan_id && mKaryId) {
              const info = await fetchKaryInfo(mKaryId)
              atasan_id = info.atasan_id
              atasan_kary = info.atasan_kary
            }
            return {
              ...dt,
              nama_kary: dt['m_kary.nama_lengkap'],
              atasan_id: atasan_id || null,
              atasan_kary: atasan_kary || '-',
              divisi_kary,
              m_branch_id: dt['m_kary.m_branch_id'],
              m_posisi_id: dt['m_kary.m_posisi_id']
            }
          })
        )

        isApproved.value = resultTrxJson.data?.cuti_status === 'APPROVED'
        isFinish.value = resultJson.data.approval?.tahap_saat_ini === resultJson.data.approval?.tahap_total
      } else {
        const editedId = route.params.id
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
        isRequesting.value = true
        const params = { join: true, transform: true }
        const fixedParams = new URLSearchParams(params)
        const res = await fetch(dataURL + '?' + fixedParams, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        if (!res.ok) throw new Error('Failed when trying to read data')
        const resultJson = await res.json()
        initialValues = resultJson.data
        if (initialValues.status) values.status_name = initialValues.status
        if (actionText.value?.toLowerCase() === 'copy' && initialValues.id) {
          delete initialValues.id
          delete initialValues.no
          delete initialValues.date
          delete initialValues.status
        }
        const arr = initialValues.t_realisasi_pelatihan_d_kary || []
        const fetchName = async (url) => {
          const r = await fetch(url, { headers: { Authorization: `${store.user.token_type} ${store.user.token}` } })
          return r.ok ? (await r.json()).data?.name || '' : ''
        }
        const fetchKaryInfo = async (mKaryId) => {
          if (!mKaryId) return { atasan_id: null, atasan_kary: '-' }
          try {
            const r = await fetch(`${store.server.url_backend}/operation/m_kary/${mKaryId}?join=true`, {
              headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
            })
            if (!r.ok) return { atasan_id: null, atasan_kary: '-' }
            const json = await r.json()
            const d = json.data || {}
            return {
              atasan_id: d.atasan_id || d['atasan.id'] || null,
              atasan_kary: d['atasan.nama_lengkap'] || d['atasan.nama_depan'] || (d.atasan ? (d.atasan.nama_lengkap || d.atasan.nama_depan) : '') || '-'
            }
          } catch {
            return { atasan_id: null, atasan_kary: '-' }
          }
        }
        detailArr.value = await Promise.all(
          arr.map(async dt => {
            const divisiId = dt['m_kary.m_divisi_id']
            const divisi_kary = divisiId ? await fetchName(`${store.server.url_backend}/operation/m_divisi/${divisiId}`) : ''
            const mKaryId = dt.m_kary_id || dt['m_kary.id'] || null
            let atasan_id = dt['m_kary.atasan_id'] || dt.atasan_id || null
            let atasan_kary = dt['atasan.nama_lengkap'] || dt.atasan_kary || ''
            if (!atasan_id && mKaryId) {
              const info = await fetchKaryInfo(mKaryId)
              atasan_id = info.atasan_id
              atasan_kary = info.atasan_kary
            }
            return {
              ...dt,
              nama_kary: dt['m_kary.nama_lengkap'],
              atasan_id: atasan_id || null,
              atasan_kary: atasan_kary || '-',
              divisi_kary,
              m_branch_id: dt['m_kary.m_branch_id'],
              m_posisi_id: dt['m_kary.m_posisi_id']
            }
          })
        )
      }
    } catch (err) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali'
      }).then(() => {
        router.back()
      })
    }
    isRequesting.value = false
  }
  for (const key in initialValues) values[key] = initialValues[key]
})




let _id = 0
const detailArr = ref([])

function mapKaryawanDetail(row, keepDetailId = true) {
  const mKaryId = row?.m_kary_id || row?.id || null

  return {
    ...row,
    id: keepDetailId ? (row?.id ?? null) : null,
    m_kary_id: mKaryId,
    nama_kary: row?.['m_kary.nama_lengkap'] || row?.nama_kary || row?.nama_lengkap || '',
    atasan_id: row?.['m_kary.atasan_id'] || row?.atasan_id || row?.['atasan.id'] || (row?.atasan ? row.atasan.id : null) || null,
    atasan_kary: row?.nama_atasan || row?.['atasan.nama_lengkap'] || row?.['atasan.nama_depan'] || row?.atasan_kary || row?.atasan?.nama_lengkap || row?.atasan || '-',
    cabang_kary: row?.cabang_kary || row?.['m_branch.name'] || '',
    divisi_kary: row?.nama_divisi || row?.divisi_kary || row?.['m_divisi.name'] || (row?.['m_divisi'] ? row['m_divisi'].name : '') || '',
    posisi_kary: row?.posisi_kary || row?.['m_posisi.name'] || (row?.['m_posisi'] ? row['m_posisi'].name : '') || '',
    m_branch_id: row?.['m_kary.m_branch_id'] || row?.m_branch_id || null,
    m_divisi_id: row?.['m_kary.m_divisi_id'] || row?.m_divisi_id || null,
    m_posisi_id: row?.['m_kary.m_posisi_id'] || row?.m_posisi_id || null
  }
}

function applyRequestPelatihan(obj) {
  if (!obj) {
    values.t_request_pelatihan_id = null
    values.m_comp_id = null
    values.m_subcomp_id = null
    values.m_branch_id = null
    values.m_divisi_id = null
    values.trainer_id = null
    values.m_prog_pelatihan_id = null
    values.date_from = null
    values.date_to = null
    values.desc = null
    values.sarana = null
    detailArr.value = []
    return
  }

  values.t_request_pelatihan_id = obj.id
  values.m_comp_id = obj.m_comp_id ?? obj['t_request_pelatihan.m_comp_id'] ?? obj['m_comp.id'] ?? null
  values.m_subcomp_id = obj.m_subcomp_id ?? obj['t_request_pelatihan.m_subcomp_id'] ?? obj['m_subcomp.id'] ?? null
  values.m_branch_id = obj.m_branch_id ?? obj['t_request_pelatihan.m_branch_id'] ?? obj['m_branch.id'] ?? null
  values.m_divisi_id = obj.m_divisi_id
    ?? obj['t_request_pelatihan.m_divisi_id']
    ?? obj['m_divisi.id']
    ?? obj.divisi_id
    ?? (obj.t_request_pelatihan_d_kary && obj.t_request_pelatihan_d_kary.length > 0 ? (obj.t_request_pelatihan_d_kary[0]['m_kary.m_divisi_id'] || obj.t_request_pelatihan_d_kary[0].m_divisi_id) : null)
    ?? null
  values.trainer_id = obj.trainer_id ?? obj['t_request_pelatihan.trainer_id'] ?? obj['trainer.id'] ?? null
  values.m_prog_pelatihan_id = obj.m_prog_pelatihan_id ?? obj['t_request_pelatihan.m_prog_pelatihan_id'] ?? obj['m_prog_pelatihan.id'] ?? null
  values.date_from = obj.date_from ?? obj['t_request_pelatihan.date_from'] ?? null
  values.date_to = obj.date_to ?? obj['t_request_pelatihan.date_to'] ?? null
  values.desc = obj.desc ?? obj['t_request_pelatihan.desc'] ?? null
  values.sarana = obj.sarana ?? obj['t_request_pelatihan.sarana'] ?? null
  values.status = values.status || 'ACTIVE'

  detailArr.value = (obj.t_request_pelatihan_d_kary || []).map(row => mapKaryawanDetail(row, false))
}

function cleanDetailForSubmit(row) {
  const mKaryId = row?.m_kary_id || (!row?.t_realisasi_pelatihan_id ? row?.id : null)
  if (!mKaryId) return null

  const cleanRow = {
    m_kary_id: mKaryId
  }

  if (row?.t_realisasi_pelatihan_id && row?.id) {
    cleanRow.id = row.id
  }

  return cleanRow
}

const onDetailAdd = (e) => {
  e.forEach(row => {
    detailArr.value.push(mapKaryawanDetail(row, false))
  });
}

const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
}

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

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'HALF APPROVED'),
    note: values.catatan,
  };
  // if(!payload.note) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: "Catatan wajib diisi",
  //   });
  //   return  
  // }


  swal.fire({
    icon: 'warning',
    text: typePar === 'revise' ? 'Revised data?' : (typePar === 'reject' ? 'Rejected data?' : 'Approved data?'),
    showDenyButton: true,
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation/t_realisasi_pelatihan/progress`;
        isRequesting.value = true;
        const res = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          body: JSON.stringify(payload),
        });




        if (!res.ok) {
          const responseJson = await res.json();
          if ([400, 422, 500].includes(res.status)) {
            formErrors.value = responseJson.errors || {};
            if (res.status === 422) {
              throw new Error(responseJson.message + " Pastikan anda sudah mengisi semua kolom dengan tanda bintang merah");
            }
            throw new Error(responseJson.message || "Failed when trying to post data");
          } else {
            throw new Error("Failed when trying to post data");
          }
        } else {
          // Success case
          swal.fire({
            icon: 'success',
            text: 'Proses berhasil',
          });
          router.replace('/notifikasi');
        }
      } catch (err) {
        isBadForm.value = true;
        swal.fire({
          icon: 'error',
          text: err || 'Failed when trying to post data',
        });
      } finally {
        isRequesting.value = false;
      }
    }
  });

  if (route.params.id === 'create') {
    activeTabIndex = 0;
  }
}

async function onApproval() {
  const payload = {
    id: parseInt(route.params.id),
    target_id: values.target_id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
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

async function posted() {
  const payload = {
    id: route.params.id
  }
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/posted`
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

async function onSave() {
  try {
    formErrors.value = {}
    let hasError = false
    const requiredFields = ['t_request_pelatihan_id', 'date_from', 'date_to', 'm_comp_id', 'm_branch_id', 'm_divisi_id', 'm_prog_pelatihan_id', 'sarana', 'trainer_id', 'status']

    requiredFields.forEach(field => {
      if (!values[field]) {
        formErrors.value[field] = ['Bidang ini wajib di isi']
        hasError = true
      }
    })

    if (hasError) {
      isBadForm.value = true
      swal.fire({
        icon: 'error',
        text: 'Maaf data belum valid, silahkan dikoreksi'
      })
      return
    }

    values.t_realisasi_pelatihan_d_kary = detailArr.value
      .map(row => cleanDetailForSubmit(row))
      .filter(row => row && row.m_kary_id)
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
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

let data = reactive({})

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    console.log('ini respo coi', respoValues)
    data.subcomp_id = respoValues.m_subcomp_id
    data.branch_id = respoValues.m_branch_id
  }
  console.log('jarwok', data.subcomp_id)
})

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
      console.log('x', result)
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

function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation${endpointApi}/app_log?id=${id}`
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const result = await res.json()
  dataLog.items = result
  console.log('cek', result)
}

let activeBtn = ref([])


function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'ACTIVE',
    0: 'INACTIVE',
  }

  if (noBtn !== null) {
    const activeBtnIdx = activeBtn.value.indexOf(noBtn)
    if (activeBtnIdx > -1) {
      // Jika sudah terpilih, hapus (Toggle Off)
      activeBtn.value.splice(activeBtnIdx, 1);
    } else {
      // Jika belum terpilih, masukkan ke daftar (Toggle On)
      activeBtn.value.push(noBtn);
    }
  }
  statusLabel = activeBtn.value.map(idx => statusMap[idx]) || null

  console.log("activeBtn", activeBtn.value)
  console.log("statusLabel", activeBtn.value.map(idx => statusMap[idx]))
  const filters = []
  if (statusLabel) {
    filters.push(...statusLabel.map(status => `this.status='${status?.toUpperCase()}'`))
  }
  console.log("filters", filters)

  // const [dateFrom, dateTo] = [valLand.start_date, valLand.end_date].map(d =>
  //   d ? parseTanggalToYMD(d) : null
  // )

  // if (dateFrom || dateTo) {
  //   const conditions = []
  //   if (dateFrom) conditions.push(`this.date >= '${dateFrom}'`)
  //   if (dateTo) conditions.push(`this.date <= '${dateTo}'`)
  //   filters.push(conditions.join(' AND '))
  // }
  landing.api.params.where = filters.length ? filters.join(' OR ') : null
  console.log("where", landing.api.params.where)
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
              const dataURL = `${store.server.url_backend}/operation/t_realisasi_pelatihan/${row.id}`
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
      show: () => data.can_read,
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
      show: (row) => row.status?.toUpperCase() !== 'POSTED' && data.can_delete,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    },
    {
      icon: 'paper-plane',
      title: 'Post Data',
      class: 'bg-gray-600 text-light-100',
      show: (row) => ['DRAFT'].includes(row.status) && data.can_update,
      async click(row) {
        const result = await swal.fire({
          icon: 'warning',
          text: 'Post Data?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',
          showDenyButton: true
        })

        if (!result.isConfirmed) return

        try {
          isRequesting.value = true

          const dataURL = `${store.server.url_backend}/operation/t_realisasi_pelatihan/posted`
          const res = await fetch(dataURL, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            },
            body: JSON.stringify({ id: row.id })
          })

          if (!res.ok) {
            const responseJson = await res.json()
            throw (
              responseJson?.message ||
              responseJson?.data?.errorText ||
              'Failed when trying to post data'
            )
          }

          const responseJson = await res.json()

          swal.fire({
            icon: 'success',
            text: responseJson.message || 'Data berhasil diposting'
          })

          apiTable.value.reload()
        } catch (err) {
          isBadForm.value = true
          swal.fire({
            icon: 'error',
            iconColor: '#1469AE',
            confirmButtonColor: '#1469AE',
            text: err
          })
        } finally {
          isRequesting.value = false
        }
      }
    },
    {
      icon: 'paper-plane',
      title: "Send For Approval",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row.status?.toUpperCase() === 'POSTED' && data.can_update,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Approve HC",
      class: 'bg-rose-700 rounded-lg text-white',
      show: row => {
        const status = row.status?.toUpperCase()
        const isUserHC = store.user.data?.is_hc === true || store.user.data?.is_hc === 1
        const isStatusValid = status === 'HALF APPROVED'
        return isUserHC && isStatusValid && data.can_update
      },
      async click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Full Approve?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',

          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_realisasi_pelatihan/approveHC`
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
                  throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
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
      icon: 'table',
      title: "Log Approval",
      class: 'bg-gray-700 rounded-lg text-white',
      show: (row) => ['APPROVED', 'IN APPROVAL', 'HALF APPROVED'].includes(row['status']) && data.can_read,
      click(row) {
        openModal(row.id)
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
        //kary_id: store.user.data.m_kary_id ?? 0,
        m_subcomp_id: `${data.subcomp_id}`,
        m_branch_id: `${data.branch_id}`,
        join: true,
        transform: true,
        // scopes: 'respo'
      }

      const where = []

      // if (data.subcomp_id != null) {
      //   where.push(`this.m_subcomp_id = ${data.subcomp_id}`)
      // }

      // if (data.branch_id != null) {
      //   where.push(`this.m_branch_id = ${data.branch_id}`)
      // }

      // where.push(`m_company_outsourcing.id IS NULL`)

      // if (where.length) {
      //   params.where = where.join(' AND ')
      // }

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
    headerName: 'Kode',
    field: 'kode',
    wrapText: true,
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'trainer.nama_trainer',
    headerName: 'Nama Trainer',
    filter: true,
    sortable: true,
    flex: 2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_prog_pelatihan.tema_pelatihan',
    headerName: 'Nama Pelatihan',
    filter: true,
    sortable: true,
    flex: 2,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tanggal Awal',
    field: 'date_from',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Tanggal Akhir',
    field: 'date_to',
    filter: true,
    sortable: true,
    flex: 1,
    filter: 'ColFilter',
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },

  // {
  //   headerName: 'Total Menjabat',
  //   // field: 'm_zona.nama',
  //   cellRenderer: (params) => {
  //     return '-'
  //   },
  //   filter: true,
  //   sortable: true,
  //   flex:1,
  //   filter: 'ColFilter',
  //   resizable: true,
  //   cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  // },
  {
    headerName: 'Status',
    field: 'status',
    filter: true,
    sortable: true,
    resizable: true,
    filter: 'ColFilter',
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