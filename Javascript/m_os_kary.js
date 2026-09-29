import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated, computed, watch } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isProfile = ref(route.query.profile ? true : false)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const formErrorsPend = ref({})
const formErrorsKel = ref({})
const formErrorsPel = ref({})
const formErrorsPres = ref({})
const formErrorsOrg = ref({})
const formErrorsBhs = ref({})
const formErrorsPK = ref({})
const activeTabIndex = ref(0)
const tableKey = ref(0)
const tableKeyJobdesc = ref(0)
const tableKeyMaterial = ref(0)
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/m_kary'
onBeforeMount(() => {
    document.title = 'Master Karyawan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
const thisYear = new Date().getFullYear()
let tempKTP = ''
let tempBPJS = ''
let tempNPWP = ''
let tempKK = ''
let tempPasfoto = ''


const setStandartGaji = async () => {
    if (values.m_zona_id && values.grading_id) {
        const fixedParams = new URLSearchParams({ simplest: true, where: `this.is_active='true' AND this.m_zona_id=${values.m_zona_id ?? 0} AND this.grading_id=${values.grading_id ?? 0}` })
        const res = await fetch(`${store.server.url_backend}/operation/m_standart_gaji` + '?' + fixedParams, {
            headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
            },
        })
        if (!res.ok) throw new Error("Failed when trying to read data")
        const resultJson = await res.json()
        const data = resultJson.data

        if (initialValues.m_standart_gaji_id === values.m_standart_gaji_id) {
            values.m_standart_gaji_id = initialValues.m_standart_gaji_id
        } else {
            values.m_standart_gaji_id = data[0]?.id
        }
    }

}

const values = reactive({
    is_active: true,
    direktorat: store.user.data?.direktorat,
    cuti_p24: 0,
    cuti_reguler: 0,
    cuti_masa_kerja: 0,
    cuti_p24_terpakai: 0,
    sisa_cuti_reguler: 0,
    sisa_cuti_masa_kerja: 0,
    m_company_outsourcing_id: store.user.data.m_os_id

})

// const defaultValues = ()=>{
//   values.m_company_outsourcing_id = 1
// }

const valuesPendidikan = reactive({
    tingkat_id: null,
    thn_masuk: thisYear,
    nama_sekolah: null,
    thn_lulus: thisYear,
    kota_id: null,
    nilai: null,
    jurusan: null,
    is_pend_terakhir: null,
    desc: null,
    ijazah_foto: null
})

const valuesKeluarga = reactive({
    keluarga_id: null,
    nama: null,
    pend_terakhir_id: null,
    pekerjaan_id: null,
    jk_id: null,
    usia: null,
    desc: null
})

const valuesPelatihan = reactive({
    nama_pel: null,
    tahun: thisYear,
    nama_lem: null,
    kota_id: null
})

const valuesPrestasi = reactive({
    tingkat_pres_id: null,
    tahun: thisYear,
    nama_pres: null
})

const valuesOrganisasi = reactive({
    nama: null,
    tahun: thisYear,
    jenis_org_id: null,
    kota_id: null,
    posisi: null
})

const valuesBahasa = reactive({
    bhs_dikuasai: null,
    nilai_lisan: null,
    nilai_tertulis: null
})

const valuesPengalaman = reactive({
    instansi: null,
    thn_masuk: thisYear,
    thn_keluar: thisYear,
    kota_id: null,
    alamat_kantor: null,
    surat_referensi: null,
    bidang_usaha: null,
    no_tlp: null,
    posisi: null
})

// watchEffect(() => {

//   if (values.status_kary_id !== 953) {
//     values.m_company_outsourcing_id = null;
//   }
// });

onBeforeMount(async () => {

    if (isRead) {
        //  READ DATA
        try {
            const editedId = route.params.id
            const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
            isRequesting.value = true

            const params = { transform: false, detail: true }
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

            initialValues.m_kary_d_lokasi?.forEach((items) => {
                detail_lokasi.value = [items, ...detail_lokasi.value]
            })

            initialValues.m_kary_det_pend?.forEach((dt) => {
                detailPendidikan.value = [dt, ...detailPendidikan.value]
            })

            inDetailArr.value = initialValues.m_kary_det_jabatan.map((jabatan) => {
                const subDetails = initialValues.m_kary_det_jobdesc.filter(
                    (jobdesc) => jobdesc.m_posisi_id === jabatan.m_posisi_id
                );

                return {
                    ...jabatan,
                    subDetails: subDetails.length > 0 ? subDetails : [
                        {
                            m_posisi_id: jabatan.m_posisi_id,
                            m_divisi_id: jabatan['m_divisi.name'],
                            jobdesc: '',
                            is_active: true
                        }
                    ],
                };

            });

            const primaryIndex = inDetailArr.value.findIndex(item => item.is_primary);
            if (primaryIndex > 0) {
                const primaryItem = inDetailArr.value.splice(primaryIndex, 1)[0];
                inDetailArr.value.unshift(primaryItem);
            }

            if (initialValues['tipe_jam_kerja.value'] == 'OFFICE') {
                getJadwalKerjaOffice()
            }
            initialValues['m_kary_det_pend']?.forEach(async (item) => {
                const res = await fetch(`${store.server.url_backend}/operation/m_general/${item.tingkat_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res.ok) throw new Error("Failed when trying to read data")
                const resultJson = await res.json()
                const tempinitialValues = resultJson.data
                item.tingkat = tempinitialValues.value
                item._id = ++_idPend
                item.is_pend_terakhir ? item.is_pend_terakhir = 1 : item.is_pend_terakhir = 0
                detailPendidikan.value.push(item)
            })
            initialValues['m_kary_det_bhs']?.forEach((item) => {
                item._id = ++_idBhs
                detailBahasa.value.push(item)
            })
            initialValues['m_kary_det_kel']?.forEach(async (item) => {
                const res = await fetch(`${store.server.url_backend}/operation/m_general/${item.keluarga_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res.ok) throw new Error("Failed when trying to read data")
                const resultJson = await res.json()
                const tempinitialValues = resultJson.data
                const res2 = await fetch(`${store.server.url_backend}/operation/m_general/${item.pend_terakhir_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res2.ok) throw new Error("Failed when trying to read data")
                const resultJson2 = await res2.json()
                const tempinitialValues2 = resultJson2.data
                const res3 = await fetch(`${store.server.url_backend}/operation/m_general/${item.jk_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res3.ok) throw new Error("Failed when trying to read data")
                const resultJson3 = await res3.json()
                const tempinitialValues3 = resultJson3.data
                const res4 = await fetch(`${store.server.url_backend}/operation/m_general/${item.pekerjaan_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res4.ok) throw new Error("Failed when trying to read data")
                const resultJson4 = await res4.json()
                const tempinitialValues4 = resultJson4.data
                item.jk = tempinitialValues3.value
                item.keluarga = tempinitialValues.value
                item.pendidikan = tempinitialValues2.value
                item.pekerjaan = tempinitialValues4.value
                item._id = ++_idKel
                detailKeluarga.value.push(item)
            })
            initialValues['m_kary_det_pk']?.forEach((item) => {
                item._id = ++_idPk
                detailPengalaman.value.push(item)
            })
            initialValues['m_kary_det_org']?.forEach(async (item) => {
                const res = await fetch(`${store.server.url_backend}/operation/m_general/${item.jenis_org_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res.ok) throw new Error("Failed when trying to read data")
                const resultJson = await res.json()
                const tempinitialValues = resultJson.data
                const res2 = await fetch(`${store.server.url_backend}/operation/m_general/${item.kota_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res2.ok) throw new Error("Failed when trying to read data")
                const resultJson2 = await res2.json()
                const tempinitialValues2 = resultJson2.data
                item.kota = tempinitialValues2.value
                item.jenis = tempinitialValues.value
                item._id = ++_idOrg
                detailOrganisasi.value.push(item)
            })
            initialValues['m_kary_det_pres']?.forEach(async (item) => {
                const res = await fetch(`${store.server.url_backend}/operation/m_general/${item.tingkat_pres_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res.ok) throw new Error("Failed when trying to read data")
                const resultJson = await res.json()
                const tempinitialValues = resultJson.data
                item.tingkat = tempinitialValues.value
                item._id = ++_idPres
                detailPrestasi.value.push(item)
            })
            initialValues['m_kary_det_pel']?.forEach(async (item) => {
                const res = await fetch(`${store.server.url_backend}/operation/m_general/${item.kota_id}`, {
                    headers: {
                        'Content-Type': 'Application/json',
                        Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                })
                if (!res.ok) throw new Error("Failed when trying to read data")
                const resultJson = await res.json()
                const tempinitialValues = resultJson.data
                item.kota = tempinitialValues.value
                item._id = ++_idPel
                detailPelatihan.value.push(item)
            })
            if (initialValues.info_cuti) {
                for (let key in initialValues.info_cuti) {
                    if (initialValues.info_cuti.hasOwnProperty(key) && initialValues.info_cuti[key] === null) {
                        initialValues.info_cuti[key] = 0;
                    }
                }
            }
            initialValues = { ...initialValues, ...initialValues.info_cuti }
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
    if (values.m_kary_det_pemb?.length > 0) {
        values.periode_gaji_id = values.m_kary_det_pemb[0].periode_gaji_id
        values.metode_id = values.m_kary_det_pemb[0].metode_id
        values.tipe_id = values.m_kary_det_pemb[0].tipe_id
        values.bank_id = values.m_kary_det_pemb[0].bank_id
        values.atas_nama_rek = values.m_kary_det_pemb[0].atas_nama_rek
        values.no_rek = values.m_kary_det_pemb[0].no_rek
    }
    if (values.m_kary_det_kartu?.length > 0) {
        values.ktp_no = values.m_kary_det_kartu[0].ktp_no
        values.kk_no = values.m_kary_det_kartu[0].kk_no
        values.npwp_no = values.m_kary_det_kartu[0].npwp_no
        values.npwp_tgl_berlaku = values.m_kary_det_kartu[0].npwp_tgl_berlaku
        values.bpjs_tipe_id = values.m_kary_det_kartu[0].bpjs_tipe_id
        // values.bpjs_no = values.m_kary_det_kartu[0].bpjs_no
        values.bpjs_no_kesehatan = values.m_kary_det_kartu[0].bpjs_no_kesehatan
        values.bpjs_no_ketenagakerjaan = values.m_kary_det_kartu[0].bpjs_no_ketenagakerjaan
        values.desc_file = values.m_kary_det_kartu[0].desc_file
        values.berkas_lain = values.m_kary_det_kartu[0].berkas_lain
        // urlBPJSFoto.value = values.m_kary_det_kartu[0].bpjs_foto
        urlPasFoto.value = values.m_kary_det_kartu[0].pas_foto
        urlKKFoto.value = values.m_kary_det_kartu[0].kk_foto
        urlKTPFoto.value = values.m_kary_det_kartu[0].ktp_foto
        urlNPWPFoto.value = values.m_kary_det_kartu[0].npwp_foto

        tempBPJS = values.m_kary_det_kartu[0].bpjs_foto
        tempNPWP = values.m_kary_det_kartu[0].npwp_foto
        tempPasfoto = values.m_kary_det_kartu[0].pas_foto
        tempKTP = values.m_kary_det_kartu[0].ktp_foto
        tempKK = values.m_kary_det_kartu[0].kk_foto
    }
})



async function getJadwalKerjaOffice() {
    try {
        const response = await fetch(`${store.server.url_backend}/operation/t_jadwal_kerja/get_jadwal_office`, {
            method: 'GET',
            headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
            },

            // ajarane sopo get ngirim body
            // body: JSON.stringify({
            //   where: `this.is_active='true' AND this.m_zona_id=${zonaId} AND this.grading_id=${gradingId}`,
            // })
        });

        if (!response.ok) {
            throw new Error('Coba kembali nanti');
        }

        const data = await response.json();

        values.t_jadwal_kerja_id = data?.data?.id
        values.t_jadwal_kerja_ket = data?.data?.keterangan
    } catch (error) {
        console.error('Error fetching tunjangan kemahalan:', error);

    }
}
const changeTipeJamKerja = (v) => {

    values['tipe_jam_kerja.value'] = v.value
    if (v.value?.toLowerCase() == 'office') {
        if (initialValues.jadwal_kerja?.id) {
            values.t_jadwal_kerja_id = initialValues.jadwal_kerja?.id
        } else {
            getJadwalKerjaOffice()
        }

    }
}





// preview image
const refPasFoto = ref()
const urlPasFoto = ref('')
const refKTPFoto = ref()
const urlKTPFoto = ref('')
const urlKKFoto = ref('')
const urlNPWPFoto = ref('')
const urlBPJSFoto = ref('')
const urlImg = ref('')
async function imageChange(e) {
    const file = e.target.files
    // console.log(e.target.id)
    if (file[0]) {
        const maxAllowedSize = 1 * 1024 * 1024;
        if (file[0].size >= maxAllowedSize) {
            swal.fire({
                icon: 'error',
                text: 'Error: File terlalu besar, max 1MB'
            })
            e.target.value = null
            return
        }
        if (e.target.id === 'inputPasFoto') {
            var formData = new FormData()
            formData.append('file', file[0])
            // console.log(file[0])
            const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_kartu/upload` + '?' + `field=pas_foto`, {
                method: 'POST',
                headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: formData
            })
            if (res.ok) {
                tempPasfoto = file[0].name
                urlPasFoto.value = URL.createObjectURL(file[0])
            }
        }
        else if (e.target.id === 'inputKTPFoto') {
            var formData = new FormData()
            formData.append('file', file[0])
            // console.log(file[0])
            const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_kartu/upload` + '?' + `field=ktp_foto`, {
                method: 'POST',
                headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: formData
            })
            if (res.ok) {
                tempKTP = file[0].name
                urlKTPFoto.value = URL.createObjectURL(file[0])
            }
        }
        else if (e.target.id === 'inputKKFoto') {
            var formData = new FormData()
            formData.append('file', file[0])
            // console.log(file[0])
            const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_kartu/upload` + '?' + `field=kk_foto`, {
                method: 'POST',
                headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: formData
            })
            if (res.ok) {
                tempKK = file[0].name
                urlKKFoto.value = URL.createObjectURL(file[0])
            }
        }
        else if (e.target.id === 'inputNPWPFoto') {
            var formData = new FormData()
            formData.append('file', file[0])

            const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_kartu/upload` + '?' + `field=npwp_foto`, {
                method: 'POST',
                headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: formData
            })
            if (res.ok) {
                tempNPWP = file[0].name
                urlNPWPFoto.value = URL.createObjectURL(file[0])
            }
        }
        else if (e.target.id === 'inputBPJSFoto') {
            var formData = new FormData()
            formData.append('file', file[0])

            const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_kartu/upload` + '?' + `field=bpjs_foto`, {
                method: 'POST',
                headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: formData
            })
            if (res.ok) {
                tempBPJS = file[0].name
                urlBPJSFoto.value = URL.createObjectURL(file[0])
            }
        }
    }
}

let ArrTahun = []
for (let i = thisYear; i >= 1973; i--) {
    ArrTahun.push(i)
}



// DETAIL LOKASI 

const apiLokasi = computed(() => {
    let tempParam = {}
    let url, tempDisp, tempPlace, tempLabel, tempColumns

    // Api
    url = `${store.server.url_backend}/operation/presensi_lokasi`
    tempParam.notin = detail_lokasi.value.length > 0 ? `this.id:${detail_lokasi.value.map(dt => dt.presensi_lokasi_id)?.filter(presensi_lokasi_id => presensi_lokasi_id)?.join(',')}` : null
    tempParam.searchfield = 'this.nama , this.lat , this.long , m_branch.name ',
        tempParam.where = `this.is_active = true`,
        tempParam.join = true

    // Columns
    tempColumns = [{
        checkboxSelection: true,
        headerCheckboxSelection: true,
        headerName: 'No',
        valueGetter: p => '',
        width: 60,
        sortable: false, resizable: true, filter: false,
        cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
    },
    {
        flex: 1,
        headerName: 'Cabang',
        sortable: false, resizable: true, filter: 'ColFilter',
        field: 'm_branch.name',
        cellClass: ['justify-start', '!border-gray-200']
    },
    {
        flex: 1,
        headerName: 'Nama',
        sortable: false, resizable: true, filter: 'ColFilter',
        field: 'nama',
        cellClass: ['justify-start', '!border-gray-200']
    },
    {
        flex: 1,
        headerName: 'Lat',
        sortable: false, resizable: true, filter: 'ColFilter',
        field: 'lat',
        cellClass: ['justify-start', '!border-gray-200']
    },
    {
        flex: 1,
        headerName: 'Long',
        sortable: false, resizable: true, filter: 'ColFilter',
        field: 'long',
        cellClass: ['justify-start', '!border-gray-200']
    },
    ]

    // Display
    return {
        columns: tempColumns,
        apiUrlAndParam: {
            url: url,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
            params: tempParam,
        },
    }
})

const detail_lokasi = ref([])

const removeDetail_Lokasi = (index) => {
    detail_lokasi.value.splice(index, 1)
}
function onDetailAdd_Lokasi(rows) {
    const newItems = rows.map(row => ({
        presensi_lokasi_id: row.id,
        nama: row.nama ?? '-',
    }));

    detail_lokasi.value = detail_lokasi.value.concat(newItems);
    tableKey.value++;
}




// LOGIC NEW DETAIL JABATAN
// Variabel reactive untuk menyimpan detail data
const inDetailArr = ref([]);

watch(inDetailArr, (newVal) => {
    values.m_subcomp_id = newVal[0]?.m_subcomp_id ?? null
}, { deep: true })

// Menambahkan detail item baru
const addDetail = () => {
    const tempItem = {
        is_primary: inDetailArr.value.length === 0,
        is_active: true,
        m_posisi_id: null,
        subDetails: [
            {
                m_posisi_id: null,
                jobdesc: '',
                is_active: true,
            },
        ],
    };
    inDetailArr.value = [...inDetailArr.value, tempItem];
};

// Menghapus detail item dari inDetailArr berdasarkan index
const hapusDetail = (i) => {
    inDetailArr.value.splice(i, 1);
};

// Menambahkan subDetail ke dalam item di inDetailArr
const addSubDetail = (index) => {
    const detailItem = inDetailArr.value[index];
    const newSubDetail = {
        m_posisi_id: detailItem.m_posisi_id,
        jobdesc: '',
        is_active: true,
    };

    if (!detailItem.subDetails) {
        detailItem.subDetails = [];
    }

    detailItem.subDetails.push(newSubDetail);
};



// Fungsi untuk menangani perubahan primary
const handlePrimaryChange = (emilia) => {
    inDetailArr.value.forEach(item => {
        if (item !== emilia) {
            item.is_primary = false;
        }
    });
    emilia.is_primary = true;
    emilia.is_active = true;
};


// Fungsi untuk mengambil detail item berdasarkan m_posisi_id dan index
async function detail_item(m_posisi_id, index) {
    try {
        const ids = m_posisi_id.id;
        const dataURL = `${store.server.url_backend}/operation/m_jobdesc`;
        isRequesting.value = true;

        const params = {
            where: `this.m_posisi_id=${ids}`,
            join: true,
            transform: true,
        };
        const fixedParams = new URLSearchParams(params);

        const res = await fetch(`${dataURL}?${fixedParams}`, {
            headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`,
            },
        });

        if (!res.ok) throw new Error('Gagal mengambil data');
        const resultJson = await res.json();
        const data = resultJson.data?.[0];
        if (!data) return;

        const id_jobdesc = data.id;

        const dataURL2 = `${store.server.url_backend}/operation/m_jobdesc_d`;
        const params2 = {
            where: `this.m_jobdesc_id=${id_jobdesc}`,
            join: true,
            transform: true,
        };
        const fixedParams2 = new URLSearchParams(params2);

        const res2 = await fetch(`${dataURL2}?${fixedParams2}`, {
            headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`,
            },
        });

        if (!res2.ok) throw new Error('Gagal mengambil detail jobdesc');
        const resultJson2 = await res2.json();
        const detailList = resultJson2.data;
        const detailItem = inDetailArr.value[index];

        if (!detailItem) {
            return;
        }
        if (!detailItem.subDetails) {
            detailItem.subDetails = [];
        }

        detailItem.subDetails = detailList.map((item) => ({
            m_posisi_id: m_posisi_id.id,
            jobdesc: item.jobdesc,
            is_active: item.is_active,
            // kunci: true, 
        }));

    } catch (err) {
        isBadForm.value = true;
        swal.fire({
            icon: 'error',
            text: err.message || err,
            allowOutsideClick: true,
        }).then(() => {
            router.back();
        });
    } finally {
        isRequesting.value = false;
    }
}

// Menghapus subDetail dari item di inDetailArr
const hapusSubDetail = (detailIndex, subIndex) => {
    const detailItem = inDetailArr.value[detailIndex];
    detailItem.subDetails.splice(subIndex, 1);
};


watchEffect(() => {
    inDetailArr.value.forEach((item, index) => {
        if (
            item.m_posisi_id &&
            item.subDetails.length === 1 &&
            !item.subDetails[0].jobdesc
        ) {
            detail_item({ id: item.m_posisi_id }, index);
        }
    });
});

// Menghubungkan perubahan posisi dengan pemanggilan detail_item
const onPosisiSelected = (index, posisi) => {
    inDetailArr.value[index].m_posisi_id = posisi.id;
    detail_item({ id: posisi.id }, index);
};

// Logika primary item yang aktif
watchEffect(() => {
    const primaryItem = inDetailArr.value.find(item => item.is_primary);
    if (primaryItem) {
        values.m_posisi_id = primaryItem.m_posisi_id;
        values.m_divisi_id = primaryItem.m_divisi_id;
        values.m_comp_id = primaryItem.m_comp_id;
        values.m_subcomp_id = primaryItem.m_subcomp_id;
        values.m_branch_id = primaryItem.m_branch_id;
    }
});




// Pendidikan
let _idPend = 0
const detailPendidikan = ref([])
const fileIjz = ref(null)
async function fileIjazah(e) {
    const file = e.target.files
    if (file[0]) {
        const maxAllowedSize = 1 * 1024 * 1024;
        if (file[0].size >= maxAllowedSize) {
            swal.fire({
                icon: 'error',
                text: 'File terlalu besar, max 1MB'
            })
            e.target.value = null
            return
        }
        valuesPendidikan.ijazah_foto = file[0].name
        var formData = new FormData()
        formData.append('file', file[0])
        const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_pend/upload` + '?' + `field=ijazah_foto`, {
            method: 'POST',
            headers: {
                Authorization: `${store.user.token_type} ${store.user.token}`
            },
            body: formData
        })
        //   if(res.ok){
        //     const resData = await res.json()
        //     const data = resData.key
        //     const _data = data.split("m_kelas_d_gambar_foto_1_")[1]
        //     // const _data = data.split('_')[data.split('_').length-1]
        //     // addImage(resData.key)
        //     // addImage(_data)
        //   }
    }
}
const addPendidikan = async () => {
    var tempObj = {}
    valuesPendidikan._id = ++_idPend
    for (const key in valuesPendidikan) {
        if (key !== 'desc') {
            if (valuesPendidikan[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsPend.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailPendidikan.value = [...detailPendidikan.value, { ...valuesPendidikan }]
    Object.keys(valuesPendidikan).forEach(key => valuesPendidikan[key] = null)
    fileIjz.value.value = null
    formErrorsPend.value = {}
    valuesPendidikan.thn_masuk = thisYear
    valuesPendidikan.thn_lulus = thisYear
}

// Keluarga
let _idKel = 0
const detailKeluarga = ref([])

const addKeluarga = async () => {
    var tempObj = {}
    valuesKeluarga._id = ++_idKel
    for (const key in valuesKeluarga) {
        if (key !== 'desc') {
            if (valuesKeluarga[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsKel.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailKeluarga.value = [...detailKeluarga.value, { ...valuesKeluarga }]
    Object.keys(valuesKeluarga).forEach(key => valuesKeluarga[key] = null)
    // gambarKK.value.value = null
    formErrorsKel.value = {}
}

// Pelatihan
let _idPel = 0
const detailPelatihan = ref([])
const addPelatihan = async () => {
    var tempObj = {}
    valuesPelatihan._id = ++_idPel
    for (const key in valuesPelatihan) {
        if (key !== 'catatan') {
            if (valuesPelatihan[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsPel.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailPelatihan.value = [...detailPelatihan.value, { ...valuesPelatihan }]
    Object.keys(valuesPelatihan).forEach(key => valuesPelatihan[key] = null)
    formErrorsPel.value = {}
    valuesPelatihan.tahun = thisYear
}

// Prestasi
let _idPres = 0
const detailPrestasi = ref([])
const addPrestasi = async () => {
    var tempObj = {}
    valuesPrestasi._id = ++_idPres
    for (const key in valuesPrestasi) {
        if (key !== 'catatan') {
            if (valuesPrestasi[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsPres.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailPrestasi.value = [...detailPrestasi.value, { ...valuesPrestasi }]
    Object.keys(valuesPrestasi).forEach(key => valuesPrestasi[key] = null)
    formErrorsPres.value = {}
    valuesPrestasi.tahun = thisYear
}

// Organisasi
let _idOrg = 0
const detailOrganisasi = ref([])
const addOrganisasi = async () => {
    var tempObj = {}
    valuesOrganisasi._id = ++_idOrg
    for (const key in valuesOrganisasi) {
        if (key !== 'catatan') {
            if (valuesOrganisasi[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsOrg.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailOrganisasi.value = [...detailOrganisasi.value, { ...valuesOrganisasi }]
    Object.keys(valuesOrganisasi).forEach(key => valuesOrganisasi[key] = null)
    formErrorsOrg.value = {}
    valuesOrganisasi.tahun = thisYear
}

// Bahasa
let _idBhs = 0
const detailBahasa = ref([])
const addBahasa = async () => {
    var tempObj = {}
    valuesBahasa._id = ++_idBhs
    for (const key in valuesBahasa) {
        if (key !== 'catatan') {
            if (valuesBahasa[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj).length >= 1) {
        formErrorsBhs.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailBahasa.value = [...detailBahasa.value, { ...valuesBahasa }]
    Object.keys(valuesBahasa).forEach(key => valuesBahasa[key] = null)
    formErrorsBhs.value = {}
}

// Pengalaman Kerja
let _idPk = 0
const detailPengalaman = ref([])
const fileSurat = ref(null)
async function fileSrtRef(e) {
    const file = e.target.files
    if (file[0]) {
        const maxAllowedSize = 1 * 1024 * 1024;
        if (file[0].size >= maxAllowedSize) {
            swal.fire({
                icon: 'error',
                text: 'File terlalu besar, max 1MB'
            })
            e.target.value = null
            return
        }
        valuesPengalaman.surat_referensi = file[0].name
        var formData = new FormData()
        formData.append('file', file[0])
        const res = await fetch(`${store.server.url_backend}/operation/m_kary_det_pk/upload` + '?' + `field=surat_referensi`, {
            method: 'POST',
            headers: {
                Authorization: `${store.user.token_type} ${store.user.token}`
            },
            body: formData
        })
    }
}
const addPengalaman = async () => {
    var tempObj = {}
    valuesPengalaman._id = ++_idPk
    for (const key in valuesPengalaman) {
        if (key !== 'catatan') {
            if (valuesPengalaman[key] == null) {
                tempObj[key] = ['Bidang ini wajib diisi']
            }
        }
    }
    if (Object.keys(tempObj)?.length >= 1) {
        formErrorsPK.value = tempObj
        swal.fire({
            icon: 'error',
            text: 'Masih ada field yang belum terisi'
        })
        return
    }
    detailPengalaman.value = [...detailPengalaman.value, { ...valuesPengalaman }]
    Object.keys(valuesPengalaman).forEach(key => valuesPengalaman[key] = null)
    fileSurat.value.value = null
    formErrorsPK.value = {}
    valuesPengalaman.thn_masuk = thisYear
    valuesPengalaman.thn_keluar = thisYear
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

async function onSave() {

    const detail_filter = inDetailArr.value;

    const requiredFields = [
        { key: 'nama_depan', msg: 'Nama Depan wajib diisi' },
        { key: 'nik', msg: 'No. KTP wajib diisi' },
        { key: 'nama_belakang', msg: 'Nama Belakang wajib diisi' },
        { key: 'jk_id', msg: 'Jenis Kelamin wajib diisi' },
        { key: 'tempat_lahir', msg: 'Tempat Lahir wajib diisi' },
        { key: 'no_tlp', msg: 'No Telepon wajib diisi' },
        { key: 'status_kary_id', msg: 'Status Karyawan wajib diisi' },
        { key: 'm_subcomp_id', msg: 'Sub wajib diisi' },
        { key: 'm_branch_id', msg: 'Branch wajib diisi' }
    ]

    for (const field of requiredFields) {
        if (!values[field.key]) {
            swal.fire({
                icon: 'warning',
                text: field.msg
            })
            return
        }
    }

    // Ambil detail jabatan
    const detail_Jabatan = detail_filter.map(item => {
        const { subDetails, ...header } = item;
        return header;
    });

    // Ambil detail jobdesk dan hanya tambahkan jika `jobdesc` tidak kosong
    const detail_Jobdesk = detail_filter.flatMap(item => {
        return (item.subDetails || []).filter(subDetail => {
            // Hanya tambahkan jika `jobdesc` tidak kosong
            return subDetail.jobdesc && subDetail.jobdesc.trim() !== '';
        });
    });

    // Tetapkan nilai ke `values`
    values.m_kary_det_jabatan = detail_Jabatan;
    values.m_kary_det_jobdesc = detail_Jobdesk.length > 0 ? detail_Jobdesk : [];

    values.m_kary_d_lokasi = detail_lokasi;

    // Debugging log
    console.log('JABATAN', values.m_kary_det_jabatan);
    console.log('JOBDESK', values.m_kary_det_jobdesc);





    try {

        // values.nama_lengkap = values.nama_depan + ' ' + values.nama_belakang
        values.nama_lengkap = (values.nama_depan ?? '') + ' ' + (values.nama_belakang ?? '')
        values.m_kary_det_pres = detailPrestasi.value
        if (values.periode_gaji_id) {
            values.m_kary_det_pemb = [{
                periode_gaji_id: values.periode_gaji_id,
                metode_id: values.metode_id,
                tipe_id: values.tipe_id,
                bank_id: values.bank_id,
                no_rek: values.no_rek,
                atas_nama_rek: values.atas_nama_rek,
                desc: values.desc,
                is_active: values.periode_gaji_idtrue,
            }]
        }

        // if (!values.no_registrasi || !values.nip) {
        //   swal.fire({
        //     icon: 'warning',
        //     text: 'No. Registrasi & No. NIP wajib diisi'
        //   })
        //   return
        // }
        // if(detailKeluarga.value.length === 0){
        //   throw ("Tab Keluarga Tidak Boleh Kosong")
        // }
        // if(detailPendidikan.value.length === 0){
        //   throw ("Tab Pendidikan Tidak Boleh Kosong")
        // }

        values.m_kary_det_kel = detailKeluarga.value
        values.m_kary_det_org = detailOrganisasi.value
        values.m_kary_det_bhs = detailBahasa.value
        values.m_kary_det_pend = detailPendidikan.value
        values.m_kary_det_pel = detailPelatihan.value
        values.m_kary_det_pk = detailPengalaman.value
        if (Array.isArray(values.m_jam_kerja_id)) {
            values.m_jam_kerja_id = JSON.stringify(values.m_jam_kerja_id);
        }
        const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)
        // console.log(values.m_kary_det_kartu.length)
        if (isCreating || values.m_kary_det_kartu?.length === 0) {

            values.m_kary_det_kartu = [{
                ktp_no: values.ktp_no,
                ktp_foto: tempKTP,
                pas_foto: tempPasfoto,
                kk_no: values.kk_no,
                kk_foto: tempKK,
                npwp_no: values.npwp_no,
                npwp_foto: tempNPWP,
                npwp_tgl_berlaku: values.npwp_tgl_berlaku,
                bpjs_tipe_id: values.bpjs_tipe_id,
                bpjs_no_kesehatan: values.bpjs_no_kesehatan,
                bpjs_no_ketenagakerjaan: values.bpjs_no_ketenagakerjaan,
                // bpjs_no: values.bpjs_no,
                // bpjs_foto: tempBPJS,
                berkas_lain: values.berkas_lain,
                desc_file: values.desc_file,
                is_active: true
            }]
        } else {
            console.log(values.m_kary_det_kartu)
            values.m_kary_det_kartu[0].ktp_no = values.ktp_no
            if (initialValues.m_kary_det_kartu[0]?.ktp_foto !== tempKTP) {
                values.m_kary_det_kartu[0].ktp_foto = tempKTP
            }
            if (initialValues.m_kary_det_kartu[0]?.pas_foto !== tempPasfoto) {
                values.m_kary_det_kartu[0].pas_foto = tempPasfoto
            }
            values.m_kary_det_kartu[0].kk_no = values.kk_no
            if (initialValues.m_kary_det_kartu[0]?.kk_foto !== tempKK) {
                values.m_kary_det_kartu[0].kk_foto = tempKK
            }
            values.m_kary_det_kartu[0].npwp_no = values.npwp_no
            values.m_kary_det_kartu[0].npwp_tgl_berlaku = values.npwp_tgl_berlaku
            if (initialValues.m_kary_det_kartu[0]?.npwp_foto !== tempNPWP) {
                values.m_kary_det_kartu[0].npwp_foto = tempNPWP
            }
            values.m_kary_det_kartu[0].bpjs_tipe_id = values.bpjs_tipe_id
            // values.m_kary_det_kartu[0].bpjs_no = values.bpjs_no
            values.m_kary_det_kartu[0].bpjs_no_kesehatan = values.bpjs_no_kesehatan
            values.m_kary_det_kartu[0].bpjs_no_ketenagakerjaan = values.bpjs_no_ketenagakerjaan
            values.m_kary_det_kartu[0].berkas_lain = values.berkas_lain
            values.m_kary_det_kartu[0].desc_file = values.desc_file
            // if(initialValues.m_kary_det_kartu[0].bpjs_foto !== tempBPJS){
            //   values.m_kary_det_kartu[0].bpjs_foto = tempBPJS
            // }

        }
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
            // values.m_kary_det_kartu = []
            if ([400, 422].includes(res.status)) {
                const responseJson = await res.json()
                formErrors.value = responseJson.errors || {}
                throw (responseJson.errors?.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
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
let data = reactive({})

// onBeforeMount(async () => {
//   try {
//     const res = await fetch(`${store.server.url_backend}/operation/m_respo/active_respo`, {
//       headers: {
//         'Content-Type': 'application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`
//       }
//     })
//     if (!res.ok) throw new Error('Gagal fetch m_respo')
//     const respoJson = await res.json()
//     const datas = respoJson.data
//     console.log('ini respo',datas)
//     data.subcomp_id = datas.m_subcomp_id
//     data.branch_id = datas.m_branch_id
//     console.log('jarwok',data.subcomp_id)

//     // const statusList = respoJson.data.map(r => r.status)
//     // console.log('Daftar status m_respo:', statusList)
//   } catch (err) {
//     console.error('Error ambil m_respo:', err)
//   }
// })


onBeforeMount(async () => {
    console.log('ini user', store.user.data)
    if (localStorage.getItem('respo')) {
        const respoValues = await JSON.parse(localStorage.getItem('respo'))
        console.log('ini respo coi', respoValues)
        data.subcomp_id = respoValues.m_subcomp_id
        data.branch_id = respoValues.m_branch_id
    }
    console.log('jarwok', data.subcomp_id)
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

const landing = reactive({
    actions: [
        {
            icon: 'trash',
            class: 'bg-red-600 text-light-100',
            title: "Hapus",
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
            // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
            click(row) {
                router.push(`${route.path}/${row.id}?` + tsId)
            }
        },
        {
            icon: 'edit',
            title: "Edit",
            class: 'bg-blue-600 text-light-100',
            // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
            click(row) {
                router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
            }
        },
        {
            icon: 'copy',
            title: "Copy",
            class: 'bg-gray-600 text-light-100',
            click(row) {
                router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
            }
        },
    ],
    api: {
        url: `${store.server.url_backend}/operation${endpointApi}`,
        headers: {
            'Content-Type': 'Application/json',
            authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: computed(() => ({
            scopes: 'kary_os,respo',
            m_subcomp_id: `${data.subcomp_id}`,
            m_branch_id: `${data.branch_id}`,
            kary_id: store.user.data.m_kary_id ?? 0,
            os_id: store.user.data.m_os_id ?? null,
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
        field: 'kode',
        headerName: 'NIP',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        field: 'nik',
        headerName: 'No. KTP',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        headerName: 'Nama',
        field: 'nama_lengkap',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        field: 'm_company_outsourcing.name',
        headerName: 'PT OS',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        field: 'm_subcomp.name',
        headerName: 'SUB',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        field: 'm_branch.name',
        headerName: 'Cabang',
        filter: true,
        sortable: true,
        flex: 1,
        filter: 'ColFilter',
        resizable: true,
        cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
        headerName: 'Status',
        field: 'is_active',
        filter: true,
        sortable: true,
        flex: 1,
        cellClass: ['border-r', '!border-gray-200', 'justify-center'],
        cellStyle: (params) => {
            const val = params.value;
            const isActive = val === true || val === 'true' || val === 1 || val === '1';
            if (!isActive) {
                return { color: 'red', fontWeight: 'bold' };
            }
            return null;
        },
        cellRenderer: (params) => {
            const val = params.value;
            const isActive = val === true || val === 'true' || val === 1 || val === '1';

            const el = document.createElement('span');
            el.className = 'rounded-md text-xs px-4 py-1 inline-block capitalize';

            if (isActive) {
                el.classList.add('text-green-500', 'font-medium');
                el.style.color = '#22c55e';
                el.textContent = 'Active';
            } else {
                el.classList.add('text-red-600', 'font-bold');
                el.style.color = '#ef4444';
                el.textContent = 'Inactive';
            }
            return el;
        }
    },
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

