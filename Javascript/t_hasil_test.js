import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, computed, onBeforeMount, watchEffect, onActivated, watch } from 'vue'

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
const isApproved = ref(false)
const isFinish = ref(false)
const is_approval = route.query.is_approval ? true : false
const tsId = 'ts=' + Date.now()

// ------------------------------ PERSIAPAN
const endpointApi = '/t_hasil_tes'
onBeforeMount(() => {
  document.title = is_approval ? 'Approval Hasil Tes' : 'Transaksi Hasil Test'
})

function onPrint(id = null) {
  const targetId = id || route.params.id
  if (!targetId || targetId === 'create') return
  window.open(`${store.server.url_backend}/web/report_hasil_tes?id=${targetId}&export=pdf`, '_blank')
}

function onLokerChange(v) {
  values.t_loker_id = v
  values.t_pelamar_id = null
}

async function onSendApproval(id = null) {
  const targetId = id || route.params.id
  if (!targetId || targetId === 'create') return

  swal.fire({
    icon: 'question',
    title: 'Kirim Approval',
    text: 'Apakah Anda yakin ingin mengirim hasil tes ini untuk approval?',
    showCancelButton: true,
    confirmButtonText: 'Ya, Kirim',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        isRequesting.value = true
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
        const res = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify({ id: targetId })
        })
        const resJson = await res.json()
        if (!res.ok) throw new Error(resJson.message || 'Gagal mengirim approval')

        swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: resJson.message || 'Approval berhasil dikirim'
        }).then(() => {
          if (isRead) {
            router.replace('/' + modulPath + '?reload=' + Date.now())
          } else if (apiTable.value) {
            apiTable.value.reload()
          }
        })
      } catch (err) {
        swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: err.message || err
        })
      } finally {
        isRequesting.value = false
      }
    }
  })
}

const isHC = computed(() => {
  const user = store.user?.data
  const userType = (user?.user_type || '').toLowerCase()
  return user?.is_hc === true || user?.is_hc === 1 || user?.is_hc === '1' || userType === 'admin' || userType === 'superadmin'
})

const canShowApprovalActions = computed(() => {
  if (route.query.is_approval) return true
  const st = (values.status || '').toUpperCase()
  return (st === 'PROSES' || st === 'PENDING') && (values.can_approve === true || values.can_approve === 1)
})

async function onApproveHC(id = null) {
  const targetId = id || route.params.id
  if (!targetId || targetId === 'create') return

  swal.fire({
    icon: 'question',
    title: 'Penerimaan Karyawan (HC)',
    text: 'Apakah Anda yakin ingin menyetujui dan menerima pelamar ini? Data pelamar akan otomatis disinkronkan ke Master Karyawan.',
    showCancelButton: true,
    confirmButtonText: 'Ya, Terima',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#16a34a'
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        isRequesting.value = true
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/approveHC`
        const resp = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify({ id: targetId })
        })
        const resJson = await resp.json()
        if (!resp.ok) throw new Error(resJson.message || 'Gagal memproses approval HC')

        swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: resJson.message || 'Pelamar resmi diterima'
        }).then(() => {
          if (isRead) {
            router.replace('/' + modulPath + '?reload=' + Date.now())
          } else if (apiTable.value) {
            apiTable.value.reload()
          }
        })
      } catch (err) {
        swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: err.message || err
        })
      } finally {
        isRequesting.value = false
      }
    }
  })
}

async function onRejectHC(id = null) {
  const targetId = id || route.params.id
  if (!targetId || targetId === 'create') return

  swal.fire({
    icon: 'warning',
    title: 'Tolak Hasil Tes (HC)',
    text: 'Apakah Anda yakin ingin menandai pelamar ini TIDAK DITERIMA?',
    input: 'text',
    inputPlaceholder: 'Alasan penolakan (opsional)...',
    showCancelButton: true,
    confirmButtonText: 'Ya, Tidak Diterima',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc2626'
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        isRequesting.value = true
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/rejectHC`
        const resp = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify({ id: targetId, note: res.value || 'Kandidat Tidak Diterima oleh HC' })
        })
        const resJson = await resp.json()
        if (!resp.ok) throw new Error(resJson.message || 'Gagal memproses penolakan HC')

        swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: resJson.message || 'Hasil tes berhasil ditandai Tidak Diterima'
        }).then(() => {
          if (isRead) {
            router.replace('/' + modulPath + '?reload=' + Date.now())
          } else if (apiTable.value) {
            apiTable.value.reload()
          }
        })
      } catch (err) {
        swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: err.message || err
        })
      } finally {
        isRequesting.value = false
      }
    }
  })
}

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
  t_loker_id: null,
  t_pelamar_id: null,
  tahapan_id: null,
  catatan: null,
  is_active: true,
  status: 'PENDING',
  //direktorat: store.user.data?.direktorat,
})

const statusOptions = computed(() => {
  const current = (values.status || 'PENDING').toUpperCase()
  if (current === 'HALF APPROVED' && isHC.value) {
    return [
      { value: 'HALF APPROVED' },
      { value: 'DITERIMA' },
      { value: 'TIDAK DITERIMA' }
    ]
  }
  if (['HALF APPROVED', 'DITERIMA', 'TIDAK DITERIMA', 'REVISED'].includes(current)) {
    return [
      { value: current },
      { value: 'PENDING' },
      { value: 'PROSES' }
    ]
  }
  return [
    { value: 'PENDING' },
    { value: 'PROSES' }
  ]
})

onBeforeMount(async () => {

  //values.direktorat = store.user.data?.direktorat

  if (isRead) {
    //  READ DATA
    try {
      const editedId = route.params.id
      isRequesting.value = true

      if (route.query.is_approval) {
        const dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/detail?id=${editedId}`
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!apiApp.ok) throw new Error("Failed when trying to read data")
        const resultJson = await apiApp.json()

        const trxId = resultJson.data?.approval?.trx_id || resultJson.data?.trx?.id || resultJson.data?.id
        
        if (!trxId) throw new Error("Failed when trying to read data")

        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${trxId}?join=false&transform=true`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!apiTrx.ok) throw new Error("Failed when trying to read data")
        const resultTrxJson = await apiTrx.json()

        values.interval = resultJson.data.approval
        values.approval = resultJson.data.approval
        values.trx = resultJson.data.trx
        values.datalog = resultJson.data.approval_log

        initialValues = resultTrxJson.data
        isApproved.value = initialValues.status === 'APPROVED' || initialValues.status === 'DITERIMA' || initialValues.status === 'HALF APPROVED'
        isFinish.value = resultJson.data.approval?.tahap_saat_ini === resultJson.data.approval?.tahap_total
      } else {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
        const params = { join: false, transform: true }
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
      }
      detailArr.value = (initialValues.t_hasil_tes_det || []).map((items) => ({
        ...items,
        nilai_tes: items.nilai_tes !== null && items.nilai_tes !== undefined && items.nilai_tes !== '' ? Number(items.nilai_tes) : null,
        __id: ++_id,
      }))
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

  // Proteksi Edit Lock jika status sudah diproses atau selesai
  if (isRead) {
    const currentStatus = (initialValues.status || '').toUpperCase()
    if (['PROSES', 'HALF APPROVED', 'DITERIMA', 'TIDAK DITERIMA'].includes(currentStatus)) {
      if (actionText.value === 'Edit') {
        actionText.value = null
        swal.fire({
          icon: 'info',
          title: 'Dokumen Terkunci',
          text: `Data hasil tes dengan status ${initialValues.status} sudah terkunci dan tidak dapat diedit.`
        })
      }
    }
  }
})

let _id = 0
const detailArr = ref([])
const addDetail = () => {
  const tempItem = {
    __id: ++_id,
    tanggal: new Date().toISOString().slice(0, 10),
    nama_tes: null,
    nilai_tes: null,
    dokumen: null,
  }
  detailArr.value = [...detailArr.value, tempItem]
}

const removeDetail = (detailItem) => {
  detailArr.value = detailArr.value.filter((e) => e.__id != detailItem.__id)
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

const hitungTotalPerdin = computed(() => {
  let total = 0;

  detailArr.value.forEach((dt) => {
    total += parseFloat(dt.nominal) || 0;
  });

  values.total_biaya = total;
  return total
});


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
      detailArr.value = (initialValues.t_hasil_tes_det || []).map((items) => ({
        ...items,
        __id: ++_id,
      }))
    }
  })
}


async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    //values.code = 1
    values.is_active = values.is_active ? true : false
    values.t_hasil_tes_det = detailArr.value
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true
    //console.log(values);
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

function onProcess(typePar) {
  const targetId = values.active_approval_id || route.params.id;
  const payload = {
    id: targetId,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'APPROVED'),
    note: values.catatan || values.note_approval || values.note || '',
  };

  swal.fire({
    icon: 'warning',
    title: typePar === 'revise' ? 'Revise Hasil Tes?' : (typePar === 'reject' ? 'Reject Hasil Tes?' : 'Approve Hasil Tes?'),
    text: typePar === 'revise' ? 'Kembalikan data ke HC untuk revisi?' : (typePar === 'reject' ? 'Tolak hasil tes pelamar ini?' : 'Setujui hasil tes ini? Status akan menjadi Half Approved untuk diproses lebih lanjut oleh HC.'),
    input: typePar !== 'approve' ? 'text' : undefined,
    inputPlaceholder: typePar !== 'approve' ? 'Masukkan catatan / alasan...' : undefined,
    showCancelButton: true,
    confirmButtonText: 'Ya, Proses',
    cancelButtonText: 'Batal',
  }).then(async (res) => {
    if (res.isConfirmed) {
      if (typePar !== 'approve' && res.value) {
        payload.note = res.value;
      }
      try {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/progress`;
        isRequesting.value = true;
        const resp = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
          body: JSON.stringify(payload),
        });

        const responseJson = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          throw new Error(responseJson.message || "Gagal memproses approval");
        }

        swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: responseJson.message || 'Persetujuan berhasil diproses',
        }).then(() => {
          if (route.query.is_approval) {
            router.replace('/notifikasi');
          } else {
            router.replace('/' + modulPath + '?reload=' + Date.now());
          }
        });
      } catch (err) {
        isBadForm.value = true;
        swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: err.message || err,
        });
      } finally {
        isRequesting.value = false;
      }
    }
  });
}

//  @else----------------------- LANDING
const activeBtn = ref()
const filterButton = ref(null);

function filterShowData(statusLabel = null, noBtn = null) {
  const statusMap = {
    1: 'PENDING',
    2: 'PROSES',
    3: 'HALF APPROVED',
    4: 'DITERIMA',
    5: 'TIDAK DITERIMA',
  }

  if (noBtn !== null) {
    if (activeBtn.value === noBtn) {
      activeBtn.value = null
      statusLabel = null
    } else {
      activeBtn.value = noBtn
      statusLabel = statusMap[noBtn] || statusLabel
    }
  } else if (statusLabel) {
    const entry = Object.entries(statusMap).find(([k, v]) => v.toUpperCase() === statusLabel.toUpperCase())
    activeBtn.value = entry ? Number(entry[0]) : null
  } else {
    statusLabel = statusMap[activeBtn.value] || null
  }
  const filters = []

  if (statusLabel) {
    filters.push(`upper(this.status)='${statusLabel?.toUpperCase()}'`)
  }

  landing.value.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}

function onStatusChange(e) {
  const val = e.target.value

  if (val !== "") {
    activeBtn.value = Number(val)
    filterShowData(null, Number(val))
  } else {
    activeBtn.value = null
    filterShowData(null, null)
  }
}

let data = reactive({
  respo_id: null,
  subcomp_id: null,
  branch_id: null,
  can_read: false,
  can_create: false,
  can_delete: false,
  can_update: false
})

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

const landing = computed(() => {
  if (!isAccessReady.value) return null
  return {
    actions: [
      {
        icon: 'trash',
        class: 'bg-red-600 text-light-100',
        title: "Hapus",
        show: (row) => data.can_delete && ['PENDING', 'DRAFT', 'REVISED'].includes((row.status || '').toUpperCase()),
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
        show: (row) => data.can_update && ['PENDING', 'DRAFT', 'REVISED'].includes((row.status || '').toUpperCase()),
        // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
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
      },
      {
        icon: 'check-circle',
        title: "Review & Approval",
        class: 'bg-amber-600 text-white rounded-lg',
        show: (row) => (row.can_approve === true || row.can_approve === 1) && ['PROSES', 'PENDING'].includes((row.status || '').toUpperCase()),
        click(row) {
          router.push(`${route.path}/${row.id}?is_approval=true&${tsId}`)
        }
      },
      {
        icon: 'paper-plane',
        title: "Kirim Approval",
        class: 'bg-indigo-600 text-light-100',
        show: (row) => ['PENDING', 'DRAFT', 'REVISED'].includes(row.status?.toUpperCase()) && data.can_update,
        click(row) {
          onSendApproval(row.id)
        }
      },
      {
        icon: 'check',
        title: "Diterima (Approve HC)",
        class: 'bg-green-600 rounded-lg text-white',
        show: row => {
          const status = (row.status || '').toUpperCase()
          return isHC.value && status === 'HALF APPROVED' && data.can_update
        },
        click(row) {
          onApproveHC(row.id)
        }
      },
      {
        icon: 'times',
        title: "Tidak Diterima (Reject HC)",
        class: 'bg-rose-600 rounded-lg text-white',
        show: row => {
          const status = (row.status || '').toUpperCase()
          return isHC.value && status === 'HALF APPROVED' && data.can_update
        },
        click(row) {
          onRejectHC(row.id)
        }
      },
      {
        icon: 'location-arrow',
        class: 'bg-rose-700 rounded-lg text-white',
        title: "Register Karyawan",
        show: (row) => ['DITERIMA', 'HIRED', 'OFFERING / TIDAK'].includes(row.status?.toUpperCase()),
        click(row) {
          const pelamarId = row.t_pelamar_id || row['t_pelamar.id']
          if (!pelamarId) {
            swal.fire({
              icon: 'warning',
              text: 'Data pelamar tidak ditemukan pada hasil tes ini.'
            })
            return
          }

          const namaPelamar = row.nama_pelamar || row['t_pelamar.nama_depan'] || row['t_pelamar.nama_pelamar'] || 'pelamar ini'
          swal.fire({
            icon: 'question',
            title: 'Register Karyawan Baru',
            text: `Buka formulir pendaftaran karyawan untuk ${namaPelamar}? Data pelamar akan otomatis dimuat ke formulir Master Karyawan.`,
            confirmButtonText: 'Buka Form Tambah Karyawan',
            showCancelButton: true,
            cancelButtonText: 'Batal'
          }).then((result) => {
            if (result.isConfirmed) {
              router.push(`/m_karyawan/create?pelamar_id=${pelamarId}&hasil_tes_id=${row.id}&${tsId}`)
            }
          })
        }
      },
      {
        icon: 'print',
        title: "Cetak Hasil Tes",
        class: 'bg-emerald-600 rounded-lg text-white',
        show: () => data.can_read,
        click(row) {
          const targetId = row?.id 
            || (apiTable.value?.selectedRows || [])[0]?.id 
            || apiTable.value?.selectedRow?.id 
            || apiTable.value?.selected?.id
            || apiTable.value?.dataSelected?.id
          if (targetId && targetId !== 'create') {
            const url = `${store.server.url_backend}/web/report_hasil_tes?id=${targetId}&export=pdf`
            window.open(url, '_blank')
          } else {
            swal.fire({
              icon: 'info',
              text: 'Silakan klik salah satu baris hasil tes pada tabel terlebih dahulu, lalu klik tombol Cetak.'
            })
          }
        }
      },
    ],
    api: {
       url: data.can_read
        ? `${store.server.url_backend}/operation${endpointApi}`
        : '',
      // url: `${store.server.url_backend}/operation${endpointApi}`,
      headers: {
        'Content-Type': 'Application/json',
        authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        searchfield: 'this.id, m_dir.nama, this.kode, m_divisi.nama, m_dept.nama, m_zona.nama, m_posisi.desc_kerja',
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
      headerName: "Kode",
      field: 'nomor',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Nama Pelamar",
      field: 't_pelamar.nama_depan',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Loker",
      field: 't_loker.title',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Tahapan",
      field: 'tahapan.value',
      valueGetter: (params) => params.data?.tahapan?.value || params.data?.['tahapan.value'] || params.data?.['m_general.value'] || params.data?.tahapan || '-',
      filter: true,
      sortable: true,
      filter: 'ColFilter',
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: "Status",
      field: "status",
      sortable: true,
      filter: "ColFilter",
      resizable: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: (params) => {
        if (!params.value) return ''
        const val = (params.value || '').toUpperCase()
        let color = 'gray'
        if (val === 'PROSES') color = 'blue'
        else if (val === 'HALF APPROVED') color = 'yellow'
        else if (val === 'REVISED') color = 'orange'
        else if (val === 'DITERIMA' || val === 'APPROVED') color = 'green'
        else if (val === 'TIDAK DITERIMA' || val === 'REJECTED' || val === 'DITOLAK') color = 'red'

        return `<span class="text-${color}-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${params.value}</span>`
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

watch(
  () => route.query.reload,
  () => {
    if (apiTable.value) {
      apiTable.value.reload()
    }
  }
)

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))