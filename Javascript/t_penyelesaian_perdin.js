import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, computed, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')
console.log(store.user.data)

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : (route.query.action?.toLowerCase() === 'verifikasi' ? null : route.query.action))
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const formErrorsDetail = ref({})
const formErrorsDetailBarang = ref({})
const tableKey = ref(0)
const tableKeyBarang = ref(0)
var activeTabIndex = ref(0)
let modalOpen = ref(false)
let modalOpenHistory = ref(false)
let modalOpenHistoryStock = ref(false)
let modalOpenShipping = ref(false)
const tsId = `ts=` + (Date.parse(new Date()))
const is_approval = route.query.is_approval ? true : false
let isApproved = ref(false)
let isFinish = ref(false)
let dataLog = reactive({ items: [] })
let activeBtn = ref(null)

// ------------------------------ PERSIAPAN
const endpointApi = '/t_penyelesaian_perdin'
onBeforeMount(() => {
  document.title = is_approval ? 'Approval Penyelesaian Perdin' : ' Penyelesaian Perdin'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's' && actionText.value) {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}


let initialValues = {}
const changedValues = []

const values = reactive({
  status: 'DRAFT',
  m_kary_id: store.user.data.m_kary_id && store.user.data.m_kary_id !== 'null' ? store.user.data.m_kary_id : null,
})

let id = 0
const detailArr = ref([])
const detailArrLap = ref([])
function onDetailAdd(e) {
  detailArr.value.push({})
}
const removeDetail = (index) => {
  detailArr.value.splice(index, 1)
}
const removeDetailLap = (index) => {
  detailArrLap.value.splice(index, 1)
}

watchEffect(() => {
  detailArr.value.forEach(item => {
    item.amt = parseFloat(((item.qty ?? 0) * (item.price ?? 0)).toFixed(2))
  })
})

const addDetail = () => {
  const tempItem = {
    id: ++id,
  }
  detailArr.value = [...detailArr.value, tempItem]
}
const addDetailLap = () => {
  const tempItemLap = {
    id: ++id,
  }
  detailArrLap.value = [...detailArrLap.value, tempItemLap]
}

async function generatePerdin() {
  detailArr.value = [];

  swal.fire({
    title: "Memuat...",
    text: "Mohon tunggu, sedang mengambil data perhitungan gaji...",
    allowOutsideClick: false,
    didOpen: () => {
      swal.showLoading();
    }
  });

  try {
    const params = new URLSearchParams({
      t_perdin: values.t_perdin_id
    })
    const dataURL = `${store.server.url_backend}/public/t_penyelesaian_perdin/generateTarif?${params}`;

    const payload = {
      t_perdin_id: values.t_perdin_id
    };

    const res = await fetch(dataURL, {
      method: "POST",
      headers: {
        "Content-Type": "Application/json",
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    });

    if (!res.ok) {
      const responseJson = await res.json();
      formErrors.value = responseJson.errors || {};
      throw responseJson.message || "Failed to post data";
    }

    const result = await res.json();
    const resultData = result.data;

    swal.close();

    if (!resultData?.length) {
      return swal.fire({
        icon: "warning",
        text: "Tidak ditemukan data"
      });
    }

    resultData.forEach(item => {
      item.id = ++id;
      item.karyawan = item.nama_lengkap;
      item.deskripsi = item.desc;
      detailArr.value.push(item);
    });

  } catch (err) {
    isBadForm.value = true;
    swal.close();
    swal.fire({
      icon: "error",
      text: `${err}`
    });
  }
}

const hitungTotalPerdin = computed(() => {
  let jmlh = 0;

  detailArr.value.forEach((dt) => {
    jmlh += parseFloat(dt.total) || 0;
  });

  values.total = jmlh;
  return jmlh
});

const hitungSisaBiaya = computed(() => {
  const nominal = Number(values.nominal) || 0

  console.log(nominal)
  const sisa = Number(values.total_biaya) || 0
  return nominal - sisa
})


onBeforeMount(async () => {
  // onReset()
  if (isRead && currentMenu?.can_read) {
    //  READ DATA
    try {
      let dataURL = ''
      let dataURLAprv = ''
      let resAprv = ''
      if (route.query.is_approval) {
        dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/app_detail?id=${route.params.id}`;
        isRequesting.value = true
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        const resultJson = await apiApp.json()
        //console.log(resultJson.data)
        const apiTrx = await fetch(`${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
        })
        if (!apiTrx.ok || !apiApp.ok) throw new Error("Failed when trying to read data")
        const resultTrxJson = await apiTrx.json()
        values.interval = resultJson?.data.approval
        values.approval = resultJson?.data.approval
        values.trx = resultJson?.data.trx
        values.datalog = resultJson?.data.approval_log
        initialValues = resultTrxJson.data
        values.nominal = initialValues['t_kbs.nominal']
        values.sisa_biaya = initialValues.sisa_biaya

        detailArr.value = (initialValues.t_penyelesaian_perdin_det || []).map((dt) => ({
          ...dt,
        }))
        detailArrLap.value = (initialValues.t_penyelesaian_perdin_d_laporan || []).map((dt) => ({
          ...dt,
        }))

        // logic finish & Approved data
        isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
        isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
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
          },
        })
        if (!res.ok) throw new Error("Failed when trying to read data")
        const resultJson = await res.json()
        initialValues = resultJson.data
        console.log('init coi', initialValues)
        values.nominal = initialValues.nominal_kbs
        values.total_biaya = initialValues['total_biaya']
        values.sisa_biaya = initialValues.sisa_biaya

        values.provinsi_id = initialValues['t_perdin.provinsi_id'] || initialValues.provinsi_id
        values.kota_id = initialValues['t_perdin.kota_id'] || initialValues.kota_id
        values.tanggalAwal = initialValues['t_perdin.date_from'] || initialValues.date_from
        values.tanggalAkhir = initialValues['t_perdin.date_to'] || initialValues.date_to
        values.tujuan = initialValues['t_perdin.tempat_tujuan'] || initialValues.tempat_tujuan
        values.tugas = initialValues['t_perdin.tugas'] || initialValues.tugas
        values.alamat_tujuan = initialValues['t_perdin.alamat_tujuan'] || initialValues.alamat_tujuan
        values.m_posisi_id = initialValues['t_perdin.m_posisi_id'] || initialValues.m_posisi_id

        if (initialValues['status']) {
          values.status_name = initialValues['status']
        }
        if (actionText.value?.toLowerCase() === 'copy' && initialValues.id) {
          delete initialValues.id, delete initialValues.no, delete initialValues.date, delete initialValues.status
        }

        const detItems = initialValues.t_penyelesaian_perdin_det || []
        const lapItems = initialValues.t_penyelesaian_perdin_d_laporan || []

        detailArr.value = detItems.map((dt) => {
          if (actionText.value?.toLowerCase() === 'copy' && dt.id) {
            const copyItem = { ...dt }
            delete copyItem.id
            delete copyItem.no
            return copyItem
          }
          return { ...dt }
        })

        detailArrLap.value = lapItems.map((dt) => {
          if (actionText.value?.toLowerCase() === 'copy' && dt.id) {
            const copyItem = { ...dt }
            delete copyItem.id
            return copyItem
          }
          return { ...dt }
        })
      }
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

  // if(['Create','Copy','Tambah'].includes(actionText.value)){
  //   try{
  //     const dataURL = `${store.server.url_backend}/operation/m_general`
  //     const params = { simplest:true,
  //                   transform:false,
  //                   join:false,
  //                   where:`this.group='STATUS DELIVERY PLAN' and this.key1='DRAFT' and this.is_active=true` }
  //     const fixedParams = new URLSearchParams(params)
  //     const res = await fetch(dataURL + '?' + fixedParams, {
  //       headers: {
  //       'Content-Type': 'Application/json',
  //       Authorization: `${store.user.token_type} ${store.user.token}`
  //       }
  //     })
  //     const resultJson =await res.json();
  //     if(resultJson.data){
  //       values.status_id=resultJson.data[0].id
  //       values.status_name=resultJson.data[0].value1
  //       values['status.key1']=resultJson.data[0].key1
  //     }

  //   }catch(err){
  //     console.log(err)
  //   }
  // }
})

function onBack() {
  let isChanged = false
  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break;
    }
  }

  if (!isChanged || !actionText.value) {
    router.replace('/' + modulPath)
    return
  }

  swal.fire({
    icon: 'warning',
    text: 'Buang semua perubahan dan kembali ke list data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      router.replace('/' + modulPath)
    }
  })
}

const onReset = async (alert = false) => {
  let next = false;
  if (alert) {
    swal.fire({
      icon: 'warning',
      text: 'Anda yakin akan mereset data ini?',
      showDenyButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        const isCopyMode = route.query.action === "Copy";

        if (isCopyMode) {
          detailArr.value = [];
          detailArrLap.value = [];
          for (const key in values) {
            delete values[key];
          }
        } else if (isRead) {
          detailArr.value = (initialValues.t_penyelesaian_perdin_det || []).map((dt) => ({
            ...dt,
          }));
          detailArrLap.value = (initialValues.t_penyelesaian_perdin_d_laporan || []).map((dt) => ({
            ...dt,
          }));

          for (const key in initialValues) {
            values[key] = initialValues[key];
          }
        } else {
          detailArr.value = (detailArr.value || []).map((dt) => ({
            ...dt,
          }));
          detailArrLap.value = (detailArrLap.value || []).map((dt) => ({
            ...dt,
          }));

          tableKey.value++;
          tableKeyBarang.value++;

          for (const key in values) {
            delete values[key];
          }

          defaultValues();
        }
      }
    });
  }

  setTimeout(() => {
    if (route.query.action !== "Copy") {
      defaultValues();
    }
  }, 100);
};

async function onSave() {

  if (!values.t_perdin_id) {
    swal.fire({
      icon: 'warning',
      text: 'Silakan pilih Perjalanan Dinas terlebih dahulu.',
    });
    return;
  }

  if (detailArr.value.length === 0) {
    swal.fire({
      icon: 'warning',
      text: 'Data detail rincian biaya tidak boleh kosong. Silakan tambahkan detail terlebih dahulu.',
    });
    return;
  }

  //values.tags = JSON.stringify(values.tags)
  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;
    isRequesting.value = true;
    //values.is_change_default = values.is_change_default ? 1 : 0
    values.t_penyelesaian_perdin_det = detailArr.value//./
    values.t_penyelesaian_perdin_d_laporan = detailArrLap.value
    if (isCreating) {
      values.no = "1"
      values.currency_id = "1"
    }


    // if (qty_stock - qty < 0) {
    //   swal.fire({
    //     icon: 'warning',
    //     text: 'Stok tidak mencukupi untuk jumlah yang dimasukkan. Silakan kurangi jumlah atau periksa stok terlebih dahulu.',
    //   });
    // }

    //values.is_active = values.is_active ? 1 : 
    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    });
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data");
      } else {
        throw ("Failed when trying to post data");
      }
    }
    router.replace('/' + modulPath + '?reload=' + (Date.parse(new Date())));
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err
    });
  }
  isRequesting.value = false;
}


async function onPost() {
  //values.tags = JSON.stringify(values.tags)
  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/posted`;
    isRequesting.value = true;

    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({ id: values.id })
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

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'APPROVED'),
    note: values.catatan || values.note_approval || values.note || (typePar === 'approve' || typePar === 'APPROVED' ? 'Approved' : '-'),
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
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/progress`;
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


function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
  console.log(modalOpen.value)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/t_rencana_perdin/app_log?id=${initialValues.id}`
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const result = await res.json()
  dataLog.items = result
}


//  @else----------------------- LANDING

function openCreatePopUp(id) {
  modalOpenCreate.value = true
}

function closeCreatePopUp(i) {
  modalOpenCreate.value = false
}

function openModal(id) {
  dataLog.items = []
  modalOpen.value = true
  loadLog(id)
  console.log(modalOpen.value)
}

function closeModal(i) {
  dataLog.items = []
  modalOpen.value = false
}

async function loadLog(id) {
  const url = `${store.server.url_backend}/operation/t_rencana_perdin/app_log?id=${id}`
  const res = await fetch(url, {
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  })
  if (!res.ok) throw new Error("Failed when trying to read data")
  const result = await res.json()
  dataLog.items = result
}

const optGroup = []
onBeforeMount(async () => {
  try {
    const dataURL = `${store.server.url_backend}/operation/t_rencana_perdin`
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
    console.log(resultJson)
    if (resultJson.data.length > 0) {
      resultJson.data?.forEach((item) => {
        if (!optGroup.includes(item.status)) {
          optGroup.push(item.status)
        }
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

// fetch permission
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

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row['status'] == 'DRAFT' && data.can_delete,
      // show: () => store.user.data.direktorat==='ADMIN INSTANSI',
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
      // show: () => store.user.data.direktorat==='ADMIN INSTANSI',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      show: (row) => !['POSTED', 'IN APPROVAL', 'APPROVED', 'REJECTED'].includes(row['status']) && data.can_update,
      class: 'bg-blue-600 text-light-100',
      // show: () => store.user.data.direktorat==='ADMIN INSTANSI',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: 'Post Data',
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row?.status === 'DRAFT' && data.can_update,
      async click(row) {
        if (!row || row.status !== 'DRAFT') return

        swal.fire({
          icon: 'warning',
          text: 'Post Data?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',
          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/posted`
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
                const responseJson = await res.json()
                throw (responseJson.message || 'Failed when trying to post data')
              }

              const responseJson = await res.json()
              swal.fire({
                icon: 'success',
                text: responseJson.message
              })
            } catch (err) {
              swal.fire({
                icon: 'error',
                iconColor: '#1469AE',
                confirmButtonColor: '#1469AE',
                text: err
              })
            } finally {
              isRequesting.value = false
              apiTable.value.reload()
            }
          }
        })
      }
    },

    // {
    //   icon: 'location-arrow',
    //   title: "Send For Approval",
    //   class: 'bg-blue-700 rounded-lg text-white',
    //   show: (row) => row['status'] === 'POSTED' && data.can_update,
    //   async click(row) {
    //     swal.fire({
    //       icon: 'warning',
    //       text: 'Post Data?',
    //       iconColor: '#1469AE',
    //       confirmButtonColor: '#1469AE',
    //       showDenyButton: true
    //     }).then(async (res) => {
    //       if (res.isConfirmed) {
    //         try {
    //           const dataURL = `${store.server.url_backend}/operation${endpointApi}/send_approval`
    //           isRequesting.value = true
    //           const res = await fetch(dataURL, {
    //             method: 'POST',
    //             headers: {
    //               'Content-Type': 'Application/json',
    //               Authorization: `${store.user.token_type} ${store.user.token}`
    //             },
    //             body: JSON.stringify({ id: row.id })
    //           })
    //           if (!res.ok) {
    //             if ([400, 422, 500].includes(res.status)) {
    //               const responseJson = await res.json()
    //               formErrors.value = responseJson.errors || {}
    //               throw (responseJson.message + " " + responseJson.data.errorText || "Failed when trying to post data")
    //             } else {
    //               throw ("Failed when trying to post data")
    //             }
    //           }
    //           const responseJson = await res.json()
    //           swal.fire({
    //             icon: 'success',
    //             text: responseJson.message
    //           })
    //           // const resultJson = await res.json()
    //         } catch (err) {
    //           isBadForm.value = true
    //           swal.fire({
    //             icon: 'error',
    //             iconColor: '#1469AE',
    //             confirmButtonColor: '#1469AE',
    //             text: err
    //           })
    //         }
    //         isRequesting.value = false

    //         apiTable.value.reload()
    //       }
    //     })
    //   }
    // },
    {
      icon: 'location-arrow',
      title: "Send For Approval",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => {
        const status = row.status?.toUpperCase()
        return ['POSTED', 'REVISED'].includes(status) && data.can_update
      },
      click(row) {
        router.push(`${route.path}/${row.id}?action=Verifikasi&` + tsId)
      }
    },
    {
      icon: 'print',
      title: "Print",
      class: 'bg-purple-600 text-light-100',
      // show: (row) => row['jenis_surat.value'] === 'PROMOSI JABATAN' && data.can_create,
      click(row) {
        const url = `${store.server.url_backend}/web/perdin?export=pdf&orientation=potrait&id=${row.id}`;
        window.open(url, '_blank');
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: () => data.can_create,
      // show: () => store.user.data.direktorat==='ADMIN INSTANSI',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Approve HC",
      class: 'bg-rose-700 rounded-lg text-white',
      show: row => {
        const status = row.status?.toUpperCase()
        const isUserHC = store.user.data?.is_hc === true || store.user.data?.is_hc === 1
        //const isStatusValid = status === 'HALF APPROVED'
        //return isUserHC && isStatusValid && data.can_update
        return isUserHC && data.can_update
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
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/approveHC`
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
      show: (row) => !['POST', 'DRAFT'].includes(row['status']) && data.can_read,
      click(row) {
        openModal(row.id)
      }
    }
  ],
  api: {
    // url: `${store.server.url_backend}/operation${endpointApi}`,
    url: currentMenu?.can_read
      ? `${store.server.url_backend}/operation${endpointApi}`
      : null,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      join: true,
      transform: true,
      searchfield: 'this.id, this.total_biaya, m_kary.nama_lengkap, t_perdin.tugas',
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
    headerName: "Nomor",
    field: 'nomor',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Nama Karyawan',
    field: 'm_kary.nama_lengkap',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Perdin',
    field: 't_perdin.tugas',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: "Tanggal Mulai",
    field: 't_perdin.date_from',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: "Tanggal Selesai",
    field: 't_perdin.date_to',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Total Biaya',
    field: 'total_biaya',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
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

function filterShowData(params) {
  if (activeBtn.value === params) {
    activeBtn.value = null
  } else {
    activeBtn.value = params
  }
  if (activeBtn.value == null) {
    // clear params filter
    landing.api.params.where = null
  }
  else if (params) {
    landing.api.params.where = `this.status='${params}'`
  }

  apiTable.value.reload()
}

function filterShowDataTab(params) {

  return detailArr.value.filter(item => {
    let match = true;
    if (params.periode && item.periode !== params.periode) {
      match = false;
    }
    return match;
  });
}


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