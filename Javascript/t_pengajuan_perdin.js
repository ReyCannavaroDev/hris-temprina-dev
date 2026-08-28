import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, computed, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated } from 'vue'

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
const endpointApi = '/t_rencana_perdin'
onBeforeMount(() => {
    document.title = 'Transaksi Pengajuan Tarif Perjalanan Dinas'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const values = reactive({
    is_active: true,
    direktorat: store.user.data?.direktorat,
    status: 'DRAFT',
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
                // console.log('tes',values.trx
                values.datalog = resultJson?.data.approval_log
                initialValues = resultTrxJson.data
                values.provinsi_id = initialValues['t_perdin.provinsi_id']
                values.kota_id = initialValues['t_perdin.kota_id']
                values.tanggalAwal = initialValues['t_perdin.date_from']
                values.tanggalAkhir = initialValues['t_perdin.date_to']
                values.tujuan = initialValues['t_perdin.tempat_tujuan']
                values.tugas = initialValues['t_perdin.tempat_tujuan']
                values.alamat_tujuan = initialValues['t_perdin.alamat_tujuan']
                values.m_posisi_id = initialValues['t_perdin.m_posisi_id']
                initialValues.t_rencana_perdin_det?.forEach((items) => {
                    items.id = ++id
                    detailArr.value = [items, ...detailArr.value]
                })
                isApproved.value = resultTrxJson?.data?.cuti_status == 'APPROVED' ? true : false
                isFinish.value = resultJson?.data?.approval?.tahap_saat_ini == resultJson?.data?.approval?.tahap_total ? true : false
            } else {
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
                values.provinsi_id = initialValues['t_perdin.provinsi_id']
                values.kota_id = initialValues['t_perdin.kota_id']
                values.tanggalAwal = initialValues['t_perdin.date_from']
                values.tanggalAkhir = initialValues['t_perdin.date_to']
                values.tujuan = initialValues['t_perdin.tempat_tujuan']
                values.tugas = initialValues['t_perdin.tempat_tujuan']
                values.alamat_tujuan = initialValues['t_perdin.alamat_tujuan']
                values.m_posisi_id = initialValues['t_perdin.m_posisi_id']
                initialValues.t_rencana_perdin_det?.forEach((items) => {
                    items.id = ++id
                    detailArr.value = [items, ...detailArr.value]
                })
                if (actionText.value?.toLowerCase() === 'copy') {
                    initialValues.status = 'DRAFT'
                }
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
})

let id = 0
const detailArr = ref([])
const addDetail = () => {
    const tempItem = {
        id: ++id,
    }
    detailArr.value = [...detailArr.value, tempItem]
}

const onDetailAdd = async (e) => {
    const id = e[0].id

    const url = `${store.server.url_backend}/operation/m_tarif_perdin/${id}`

    const params = new URLSearchParams({
        scopes: 'WithDetail',
        searchfield: 'this.id, this.nomor, t_perdin.tugas., t_perdin.tempat_tujuan, t_perdin.date_from, t_perdin.date_to, status'
    })

    const res = await fetch(`${url}?${params.toString()}`, {
        headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
        }
    })

    const json = await res.json()
    const result = json.data.m_tarif_perdin_det

    result.forEach(r => {
        detailArr.value.push({
            ...r,
            jumlah: 1
        })
    })

}


// async function generatePerhitungan() {
//   detailArr.value = [];

//   swal.fire({
//     title: "Memuat...",
//     text: "Mohon tunggu, sedang mengambil data perhitungan gaji...",
//     allowOutsideClick: false,
//     didOpen: () => {
//       swal.showLoading();
//     }
//   });

//   try {
//     const dataURL = `${store.server.url_backend}/operation/t_rencana_perdin/generateTarif`;

//     const payload = {
//       provinsi_id: values.provinsi_id,
//       kota_id: values.kota_id,
//       posisi_id: values.posisi_id
//     };

//     const res = await fetch(dataURL, {
//       method: "POST",
//       headers: {
//         "Content-Type": "Application/json",
//         Authorization: `${store.user.token_type} ${store.user.token}`
//       },
//       body: JSON.stringify(payload)
//     });

//     if (!res.ok) {
//       const responseJson = await res.json();
//       formErrors.value = responseJson.errors || {};
//       throw responseJson.message || "Failed to post data";
//     }

//     const result = await res.json();
//     const resultData = result.data;

//     swal.close();

//     if (!resultData?.length) {
//       return swal.fire({
//         icon: "warning",
//         text: "Tidak ditemukan data"
//       });
//     }

//     resultData.forEach(item => {
//       item.id = ++id;
//       item.karyawan = item.nama_lengkap;
//       item.deskripsi = item.desc;
//       detailArr.value.push(item);
//     });

//   } catch (err) {
//     isBadForm.value = true;
//     swal.close();
//     swal.fire({
//       icon: "error",
//       text: `${err}`
//     });
//   }
// }

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

const hitungTotalPerdin = computed(() => {
    let jmlh = null;

    detailArr.value.forEach((dt) => {
        jmlh += parseFloat(dt.total) || 0;
    });

    values.total = jmlh;
    return jmlh
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
        }
    })
}



async function onSave() {
    try {
        values.code = 1
        values.is_active = values.is_active ? true : false
        values.t_rencana_perdin_det = detailArr.value
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

function onProcess(typePar) {
    const payload = {
        id: route.params.id,
        type: typePar === 'revise' ? 'REVISED' : (typePar === 'reject' ? 'REJECTED' : 'APPROVED'),
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
}

function closeModal(i) {
    dataLog.items = []
    modalOpen.value = false
}

async function loadLog(id) {
    const targetId = id || initialValues.id || values.id
    const url = `${store.server.url_backend}/operation${endpointApi}/app_log?id=${targetId}`
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

let data = reactive({})


onBeforeMount(async () => {
    if (localStorage.getItem('respo')) {
        const respoValues = await JSON.parse(localStorage.getItem('respo'))
        console.log('ini respo coi', respoValues)
        data.subcomp_id = respoValues.m_subcomp_id
        data.branch_id = respoValues.m_branch_id
        data.respo_id = respoValues.id
    }
    console.log('ini dta', data)
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


// openModal, closeModal, loadLog sudah dideklarasikan di atas (section form)

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

const landing = reactive({
    actions: [
        {
            icon: 'trash',
            class: 'bg-red-600 text-light-100',
            title: "Hapus",
            show: (row) => !['POSTED', 'IN APPROVAL', 'APPROVED', 'REJECTED'].includes(row['status']) && data.can_delete,
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
            icon: 'print',
            title: "Print",
            class: 'bg-purple-600 text-light-100',
            // show: (row) => row['jenis_surat.value'] === 'PROMOSI JABATAN' && data.can_create,
            click(row) {
                const url = `${store.server.url_backend}/web/rincian_perjalanan_d?export=pdf&orientation=potrait&id=${row.id}`;
                window.open(url, '_blank');
            }
        },
        {
            icon: 'edit',
            title: "Edit",
            class: 'bg-blue-600 text-light-100',
            show: (row) => !['POSTED', 'IN APPROVAL', 'APPROVED', 'REJECTED'].includes(row['status']) && data.can_update,
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
            icon: 'location-arrow',
            title: "Post Data",
            class: 'bg-rose-700 rounded-lg text-white',
            show: (row) => !['POSTED', 'HALF APPROVED', 'IN APPROVAL', 'APPROVED', 'REJECTED'].includes(row['status']) && data.can_update,
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

        params: computed(() => ({
            scopes: 'landing',
            m_subcomp_id: data.subcomp_id,
            m_branch_id: data.branch_id,
            simplest: false,
            searchfield: 'this.id, this.nomor, t_perdin.tugas., t_perdin.tempat_tujuan, t_perdin.date_from, t_perdin.date_to, status',
        })),

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
        headerName: "Nama",
        field: 'm_kary.nama_lengkap',
        filter: true,
        sortable: true,
        filter: 'ColFilter',
        resizable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        headerName: "Tugas",
        field: 't_perdin.tugas',
        filter: true,
        sortable: true,
        filter: 'ColFilter',
        resizable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        headerName: "Tujuan",
        field: 't_perdin.tempat_tujuan',
        filter: true,
        sortable: true,
        filter: 'ColFilter',
        resizable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
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