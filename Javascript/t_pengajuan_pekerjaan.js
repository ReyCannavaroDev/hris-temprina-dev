import { useRouter, useRoute, RouterLink } from "vue-router";
import {
  ref,
  readonly,
  reactive,
  inject,
  onMounted,
  onBeforeMount,
  watchEffect,
  onActivated,
} from "vue";

const router = useRouter();
const route = useRoute();
const store = inject("store");
console.log("ini store", store.user.data.user_type);
const swal = inject("swal");

const isRead = route.params.id && route.params.id !== "create";
const actionText = ref(
  route.params.id === "create" ? "Tambah" : route.query.action
);
const isBadForm = ref(false);
const isRequesting = ref(false);
const modulPath = route.params.modul;
const currentMenu = store.currentMenu;
const apiTable = ref(null);
const formErrors = ref({});
const tsId = `ts=` + Date.parse(new Date());
const readValue = ref(true);
const adjKary = ref(
  route.query.action?.toLowerCase() === "adjusment" ? true : false
);
const is_approval = route.query.is_approval ? true : false
const is_to_upload = route.query.is_to_upload ? true : false
let isApproved = ref(false)
let isFinish = ref(false)

// ------------------------------ PERSIAPAN
const endpointApi = "/t_pengajuan_pekerjaan";
onBeforeMount(() => {
  document.title =  is_approval ? 'Approval Pengajuan Pekerjaan' : 'Pengajuan Pekerjaan';
  if (store.user.data.user_type.toLowerCase() === "user") {
    values.request_id = store.user.data.m_kary_id;
  }
});

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

let initialValues = {};
const changedValues = [];

const values = reactive({
  date: new Date().toLocaleDateString("en-GB"),
  request_id: null,
  status : 'DRAFT'
});

function changeFormatDate(dateInput) {
  function toYmdHis(dateInput) {
    const d = new Date(dateInput);
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }
}

onBeforeMount(async () => {
  if (isRead) {
    try {
      let dataURL = ''
      let dataURLAprv = ''
      let resAprv = ''
      if (route.query.is_approval) {
        dataURLAprv = `${store.server.url_backend}/operation${endpointApi}/app_detail?id=${route.params.id}`;
        isRequesting.value = true;
        const apiApp = await fetch(dataURLAprv, {
          headers: {
            "Content-Type": "Application/json",
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
        });
        const resultJson = await apiApp.json();
        // console.log("resultJson", resultJson);
        const apiTrx = await fetch(
          `${store.server.url_backend}/operation${endpointApi}/${resultJson.data.approval.trx_id}`,
          {
            headers: {
              "Content-Type": "Application/json",
              Authorization: `${store.user.token_type} ${store.user.token}`,
            },
          }
        );
        if (!apiTrx.ok || !apiApp.ok)
          throw new Error("Failed when trying to read data");
        const resultTrxJson = await apiTrx.json();
        values.interval = resultJson?.data.approval;
        values.approval = resultJson?.data.approval;
        values.trx = resultJson?.data.trx;
        values.datalog = resultJson?.data.approval_log;
        initialValues = resultTrxJson.data;
        // tempRef.value = initialValues.ref_type
        if (actionText.value?.toLowerCase() === "copy" && initialValues.uid) {
          delete initialValues.uid;
        }

        // logic finish & Approved data
        isApproved.value =
          resultTrxJson?.data?.cuti_status == "APPROVED" ? true : false;
        isFinish.value =
          resultJson?.data?.approval?.tahap_saat_ini ==
          resultJson?.data?.approval?.tahap_total
            ? true
            : false;
      } else {
        isRequesting.value = true;
        const editedId = route.params.id;
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`;
        const params = { join: true, transform: false };
        const fixedParams = new URLSearchParams(params);
        const res = await fetch(dataURL + "?" + fixedParams, {
          headers: {
            "Content-Type": "Application/json",
            Authorization: `${store.user.token_type} ${store.user.token}`,
          },
        });
        if (!res.ok) throw new Error("Failed when trying to read data");
        const resultJson = await res.json();
        initialValues = resultJson.data;
        initialValues.m_divisi1_pic_id = initialValues['m_divisi_pic.id']
      }
    } catch (err) {
      isBadForm.value = true;
      swal
        .fire({
          icon: "error",
          text: err,
          allowOutsideClick: false,
          confirmButtonText: "Kembali",
        })
        .then(() => {
          router.back();
        });
    }
    isRequesting.value = false;
  }
  for (const key in initialValues) {
    values[key] = initialValues[key];
  }
});

// function onBack() {
//   if (route.query.view_gaji) {
//     router.replace("/t_info_gaji");
//   } else if (route.query.view_gaji_final) {
//     router.replace("/t_info_gaji");
//   } else {
//     router.replace("/" + modulPath);
//   }
//   return;
// }

function onBack() {
  if (!is_approval) {
    router.replace('/' + modulPath)
  } else {
    router.replace('/notifikasi')
  }
  return
}

function onProcess(typePar) {
  const payload = {
    id: route.params.id,
    type: typePar?.toLowerCase() === 'revise' ? 'REVISED' : (typePar?.toLowerCase() === 'reject' ? 'REJECTED' : (typePar?.toLowerCase() === 'posted' ? 'POSTED' :'APPROVED')),
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
    text: typePar?.toLowerCase() === 'revise' ? 'Revised data?' : (typePar?.toLowerCase() === 'reject' ? 'Rejected data?' : 'Approved data?'),
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
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    const isCreating = ["Create", "Copy", "Tambah", "Adjusment"].includes(
      actionText.value
    );
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${
      isCreating ? "" : "/" + route.params.id
    }`;
    isRequesting.value = true;
    values.is_active = values.is_active ? 1 : 0;
    const res = await fetch(dataURL, {
      method: isCreating ? "POST" : "PUT",
      headers: {
        "Content-Type": "Application/json",
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
      body: JSON.stringify(values),
    });
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw responseJson.errors.length
          ? responseJson.errors[0]
          : responseJson.message || "Failed when trying to post data";
      } else {
        throw "Failed when trying to post data";
      }
    }
    router.replace("/" + modulPath + "?reload=" + Date.parse(new Date()));
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: "error",
      text: err,
    });
  }
  isRequesting.value = false;
}

//  @else----------------------- LANDING
// LANDING LAMA

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
    2: 'IN APPROVAL',
    2: 'REVISED',
    2: 'APPROVED',
    2: 'REJECTED',
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

  landing.api.params.where = filters.length ? filters.join(' AND ') : null
  apiTable.value.reload()
}

const landing = reactive({
  actions: [
    {
      icon: "trash",
      class: "bg-red-600 text-light-100",
      show: () => data.can_delete,
      // show: (row) =>row.status?.toUpperCase() === 'DRAFT',
      title: "Hapus",
      // show: () => store.user.data.username==='developer',
      click(row) {
        swal
          .fire({
            icon: "warning",
            text: "Hapus Data Terpilih?",
            confirmButtonText: "Yes",
            showDenyButton: true,
          })
          .then(async (result) => {
            if (result.isConfirmed) {
              try {
                const dataURL = `${store.server.url_backend}/operation${endpointApi}/${row.id}`;
                isRequesting.value = true;
                const res = await fetch(dataURL, {
                  method: "DELETE",
                  headers: {
                    "Content-Type": "Application/json",
                    Authorization: `${store.user.token_type} ${store.user.token}`,
                  },
                });
                if (!res.ok)
                  throw new Error("Failed when trying to remove data");
                apiTable.value.reload();
                // const resultJson = await res.json()
              } catch (err) {
                isBadForm.value = true;
                swal.fire({
                  icon: "error",
                  text: err,
                });
              }
              isRequesting.value = false;
            }
          });
      },
    },
    {
      icon: "eye",
      title: "Read",
      class: "bg-green-600 text-light-100",
      show: () => data.can_read,
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId);
      },
    },
    {
      icon: "edit",
      title: "Edit",
      class: "bg-blue-600 text-light-100",
            show: (row) => row.status?.toUpperCase() === "DRAFT" && data.can_update,
      // show: (row) => row.status?.toUpperCase() === 'DRAFT' || row.status?.toUpperCase() === 'REVISED',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId);
      },
    },
    {
      icon: "copy",
      title: "Copy",
      show: (row) => row.status?.toUpperCase() === "DRAFT" && data.can_create,
      class: "bg-gray-600 text-light-100",
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId);
      },
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
              const dataURL = `${store.server.url_backend}/operation/t_pengajuan_pekerjaan/posted`
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
      icon: 'location-arrow',
      title: "Send Approval",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => {
        const status = row.status?.toUpperCase()
        return ['POSTED', 'REVISED'].includes(status) && data.can_update
      },
      async click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Send Approval?',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',

          showDenyButton: true
        }).then(async (res) => {
          if (res.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/t_pengajuan_pekerjaan/send_approval`
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
    }
    
  ],
  api: {
    url: `${store.server.url_backend}/operation${endpointApi}`,
    // url: currentMenu?.can_read
    //      ? `${store.server.url_backend}/operation${endpointApi}` 
    //      : '',
    headers: {
      "Content-Type": "Application/json",
      authorization: `${store.user.token_type} ${store.user.token}`,
    },
    params: {
      simplest: true,
      join: true,
      // where: `${
      //   store.user.data?.user_type?.toLowerCase() === "user"
      //     ? `this.request_id=${store.user.data.m_kary_id}`
      //     : ""
      // }`,
      searchfield:
        "this.start_date, pic.nama_lengkap,request.nama_lengkap, jenis_pekerjaan.value, this.pekerjaan",
    },
    onsuccess(response) {
      response.page = response.current_page;
      response.hasNext = response.has_next;
      return response;
    },
  },
  columns: [
    {
      headerName: "No",
      valueGetter: (params) => params.node.rowIndex + 1,
      width: 60,
      sortable: true,
      resizable: true,
      filter: true,
      cellClass: [
        "justify-center",
        "bg-gray-50",
        "border-r",
        "!border-gray-200",
      ],
    },
    {
      field: "start_date",
      headerName: "Tanggal Mulai",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-left"],
    },
     {
      field: "m_branch.name",
      headerName: "Cabang",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-start"],
    },
    {
      field: "request.nama_lengkap",
      headerName: "User",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-start"],
    },
    {
      field: "pic.nama_lengkap",
      headerName: "Pic",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-start"],
    },
    {
      field: "jenis_pekerjaan.value",
      headerName: "Jenis Pekerjaan",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-left"],
    },
    {
      field: "pekerjaan",
      headerName: "Pekerjaan",
      filter: true,
      sortable: true,
      flex: 1,
      filter: "ColFilter",
      resizable: true,
      cellClass: ["border-r", "!border-gray-200", "justify-left"],
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
        if (value == 'POSTED' || value == 'APPROVED')
          color = 'green'
        else if (value == 'IN APPROVAL' || value == 'PROGRESS')
          color = 'blue'
        else if (value == 'REVISED')
          color = 'yellow'
        else if (value == 'REJECTED')
          color = 'red'
        return `<span class="text-${color}-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">${value}</span>`
      }
    }
  ],
});
onActivated(() => {
  //  reload table api landing
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload();
    }
  }
});
//  @endif -------------------------------------------------END
watchEffect(() => store.commit("set", ["isRequesting", isRequesting.value]));
