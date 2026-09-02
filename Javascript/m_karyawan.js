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
const changedValues = []
const thisYear = new Date().getFullYear()
const endpointApi = '/m_kary'
let initialValues = {}
let tempKTP = ''
let tempBPJS = ''
let tempNPWP = ''
let tempKK = ''
let tempPasfoto = ''
// console.log('menu', currentMenu)

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Master Karyawan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

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
  can_outscope: true,
  direktorat: store.user.data?.direktorat,
  cuti_p24: 0,
  cuti_reguler: 12,
  cuti_masa_kerja: 0,
  cuti_p24_terpakai: 0,
  sisa_cuti_reguler: 0,
  sisa_cuti_masa_kerja: 0,
  atasan_id: null,
})

const valuesPendidikan = reactive({
  tingkat_id: null,
  nama_sekolah: null,
  thn_lulus: thisYear,
  kota_id: null,
  nilai: null,
  jurusan: null,
  is_pend_terakhir: null,
  desc: null,
  ijazah_foto: null
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

const showSubDetail = ref(false)
const toggleSubDetail = () => {
  showSubDetail.value = !showSubDetail.value
}

watchEffect(() => {

  if (values.status_kary_id !== 953) {
    values.m_company_outsourcing_id = null;
  }
});

const keluarga = []
const kerjaan = []
const pendidikan = []
const kelamin = []
const tahun = []

onBeforeMount(async () => {
  try {
    const baseURL = `${store.server.url_backend}/operation/m_general`
    const headers = {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    }

    const makeParams = (group) =>
      new URLSearchParams({
        simplest: true,
        where: `this.group='${group}' AND this.is_active='true'`
      })

    const [resKeluarga, resKerjaan, resPendidikan, resKelamin] = await Promise.all([
      fetch(`${baseURL}?${makeParams('HUBUNGAN KELUARGA')}`, { headers }),
      fetch(`${baseURL}?${makeParams('PEKERJAAN')}`, { headers }),
      fetch(`${baseURL}?${makeParams('PENDIDIKAN')}`, { headers }),
      fetch(`${baseURL}?${makeParams('JENIS KELAMIN')}`, { headers })
    ])

    const [dataKeluarga, dataKerjaan, dataPendidikan, dataKelamin] = await Promise.all([
      resKeluarga.json(),
      resKerjaan.json(),
      resPendidikan.json(),
      resKelamin.json()
    ])

    const pushUnique = (target, source) => {
      if (Array.isArray(source?.data)) {
        source.data.forEach((item) => {
          if (item.value && !target.includes(item.value)) target.push(item.value)
        })
      }
    }

    pushUnique(keluarga, dataKeluarga)
    pushUnique(kerjaan, dataKerjaan)
    pushUnique(pendidikan, dataPendidikan)
    pushUnique(kelamin, dataKelamin)

    const currentYear = new Date().getFullYear()
    for (let i = currentYear; i >= currentYear - 50; i--) tahun.push(String(i))

    await loadPosisiLevel()

    if (isRead && currentMenu?.can_read) {
      try {
        const editedId = route.params.id
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/${editedId}`
        isRequesting.value = true

        const params = new URLSearchParams({ transform: false, detail: true })
        const res = await fetch(`${dataURL}?${params}`, { headers })
        if (!res.ok) throw new Error('Failed when trying to read data')
        const resultJson = await res.json()
        initialValues = resultJson.data

        const assignDetails = (key, target, idRef) => {
          initialValues[key]?.forEach(async (item) => {
            if (key === 'm_kary_det_org' || key === 'm_kary_det_pres' || key === 'm_kary_det_pel' || key === 'm_kary_det_kel' || key === 'm_kary_det_pend') {
              const fetchGeneral = async (id) => {
                const res = await fetch(`${baseURL}/${id}`, { headers })
                if (!res.ok) throw new Error('Failed when trying to read data')
                const json = await res.json()
                return json.data
              }
              if (key === 'm_kary_det_kel') {
                const [kel, pend, jk, pek] = await Promise.all([
                  fetchGeneral(item.keluarga_id),
                  fetchGeneral(item.pend_terakhir_id),
                  fetchGeneral(item.jk_id),
                  fetchGeneral(item.pekerjaan_id)
                ])
                item.keluarga = kel.value
                item.pendidikan = pend.value
                item.jk = jk.value
                item.pekerjaan = pek.value
              } else if (key === 'm_kary_det_org') {
                const [jenis, kota] = await Promise.all([
                  fetchGeneral(item.jenis_org_id),
                  fetchGeneral(item.kota_id)
                ])
                item.jenis = jenis.value
                item.kota = kota.value
              } else if (key === 'm_kary_det_pres') {
                const tingkat = await fetchGeneral(item.tingkat_pres_id)
                item.tingkat = tingkat.value
              } else if (key === 'm_kary_det_pel') {
                const kota = await fetchGeneral(item.kota_id)
                item.kota = kota.value
              } else if (key === 'm_kary_det_pend') {
                const tingkat = await fetchGeneral(item.tingkat_id)
                item.tingkat = tingkat.value
                item.is_pend_terakhir = item.is_pend_terakhir ? 1 : 0
              }
            }
            item._id = ++idRef.value
            target.value.push(item)
          })
        }

        assignDetails('m_kary_d_lokasi', detail_lokasi, { value: 0 })
        assignDetails('m_kary_det_pend', detailPendidikan, { value: _idPend })
        assignDetails('m_kary_det_pel', detailPelatihan, { value: _idPel })
        assignDetails('m_kary_det_pres', detailPrestasi, { value: _idPres })
        assignDetails('m_kary_det_org', detailOrganisasi, { value: _idOrg })
        assignDetails('m_kary_det_bhs', detailBahasa, { value: _idBhs })
        assignDetails('m_kary_det_pk', detailPengalaman, { value: _idPk })
        assignDetails('m_kary_det_kel', detailKeluarga, { value: _idKel })

        inDetailArr.value = initialValues.m_kary_det_jabatan.map((jabatan) => {
          const subDetails = initialValues.m_kary_det_jobdesc.filter(
            (jobdesc) => jobdesc.m_posisi_id === jabatan.m_posisi_id
          )
          return {
            ...jabatan,
            m_company_id: jabatan.m_company_id ?? jabatan['m_company.id'] ?? jabatan['m_subcomp.m_company_id'] ?? null,
            level_name: jabatan.level_name ?? posisiLevelMap.value[jabatan.m_posisi_id] ?? jabatan['m_level_posisi.level_name'] ?? jabatan['lp.level_name'] ?? null,
            subDetails:
              subDetails.length > 0
                ? subDetails
                : [
                  {
                    m_posisi_id: jabatan.m_posisi_id,
                    m_divisi_id: jabatan['m_divisi.name'],
                    jobdesc: '',
                    is_active: true
                  }
                ]
          }
        })

        const primaryIndex = inDetailArr.value.findIndex((i) => i.is_primary)
        if (primaryIndex > 0) {
          const primaryItem = inDetailArr.value.splice(primaryIndex, 1)[0]
          inDetailArr.value.unshift(primaryItem)
        }

        // if (initialValues['tipe_jam_kerja.value'] == 'OFFICE') getJadwalKerjaOffice()

        if (initialValues.info_cuti) {
          for (let key in initialValues.info_cuti) {
            if (initialValues.info_cuti[key] === null) initialValues.info_cuti[key] = 0
          }
        }
        initialValues = { ...initialValues, ...initialValues.info_cuti }

        if (initialValues.atasan_id) {
          values.atasan_id = initialValues.atasan_id
        }
        if (initialValues.m_divisi_id) {
          fetchAtasanByDivisi(initialValues.m_divisi_id, initialValues.id, initialValues.m_posisi_id)
        }

      } catch (err) {
        isBadForm.value = true
        swal.fire({
          icon: 'error',
          text: err,
          allowOutsideClick: false,
          confirmButtonText: 'Kembali'
        }).then(() => router.back())
      } finally {
        isRequesting.value = false
      }
    }

    for (const key in initialValues) values[key] = initialValues[key]

    if (values.m_kary_det_pemb?.length > 0) {
      const pemb = values.m_kary_det_pemb[0]
      Object.assign(values, {
        periode_gaji_id: pemb.periode_gaji_id,
        metode_id: pemb.metode_id,
        tipe_id: pemb.tipe_id,
        bank_id: pemb.bank_id,
        atas_nama_rek: pemb.atas_nama_rek,
        no_rek: pemb.no_rek
      })
    }

    if (values.m_kary_det_kartu?.length > 0) {
      const kartu = values.m_kary_det_kartu[0]
      Object.assign(values, {
        ktp_no: kartu.ktp_no,
        kk_no: kartu.kk_no,
        npwp_no: kartu.npwp_no,
        npwp_tgl_berlaku: kartu.npwp_tgl_berlaku,
        bpjs_tipe_id: kartu.bpjs_tipe_id,
        bpjs_no_kesehatan: kartu.bpjs_no_kesehatan,
        bpjs_no_ketenagakerjaan: kartu.bpjs_no_ketenagakerjaan,
        desc_file: kartu.desc_file,
        berkas_lain: kartu.berkas_lain
      })
      urlPasFoto.value = kartu.pas_foto
      urlKKFoto.value = kartu.kk_foto
      urlKTPFoto.value = kartu.ktp_foto
      urlNPWPFoto.value = kartu.npwp_foto
      tempBPJS = kartu.bpjs_foto
      tempNPWP = kartu.npwp_foto
      tempPasfoto = kartu.pas_foto
      tempKTP = kartu.ktp_foto
      tempKK = kartu.kk_foto
    }

  } catch (err) {
    swal.fire({ icon: 'warning', text: err.message || err })
  }
})

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

const posisiLevelMap = ref({})
const posisiSequenceMap = ref({})

const getPosisiRank = (posisiId, posisiName = '') => {
  if (posisiId && posisiSequenceMap.value[posisiId] !== undefined) {
    return posisiSequenceMap.value[posisiId];
  }

  const name = String(posisiName || posisiLevelMap.value[posisiId] || '').toLowerCase();
  
  if (/direktur|director|president/i.test(name)) return 1;
  if (/general manager|gm\b|head of/i.test(name)) return 2;
  if (/kadiv|kepala divisi|senior manager/i.test(name)) return 3;
  if (/manager|kabag|kepala bagian/i.test(name)) return 4;
  if (/supervisor|spv|asst\.? manager|assistant manager|section head|koordinator/i.test(name)) return 5;
  if (/team lead|leader|senior/i.test(name)) return 6;
  if (/staff|specialist|officer|analyst|programmer|developer|designer|admin/i.test(name)) return 7;
  if (/operator|pelaksana|helper|teknisi|magang|intern|pkl|driver|security/i.test(name)) return 8;

  return 7;
};

const loadPosisiLevel = async () => {
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_posisi?scopes=GetValueGen&transform=false&join=true`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    })
    if (res.ok) {
      const json = await res.json()
      const list = json.data || []
      const map = {}
      const seqMap = {}
      list.forEach((p) => {
        if (p.id) {
          map[p.id] = p.level_name || p['m_level_posisi.level_name'] || p['lp.level_name'] || p.name || '-'
          const seq = p.level_sequence ?? p.sequence;
          seqMap[p.id] = (seq !== null && seq !== undefined) ? Number(seq) : getPosisiRank(null, p.name || '');
        }
      })
      posisiLevelMap.value = map
      posisiSequenceMap.value = seqMap
    }
  } catch (e) {
    console.error(e)
  }
}

const inDetailArr = ref([]);

watch(inDetailArr, (newVal) => {
  values.m_company_id = newVal[0]?.m_company_id ?? null
  values.m_subcomp_id = newVal[0]?.m_subcomp_id ?? null
  values.m_branch_id = newVal[0]?.m_branch_id ?? null
  values.m_comp_id = newVal[0]?.m_comp_id ?? null
  values.m_divisi_id = newVal[0]?.m_divisi_id ?? null
  values.m_posisi_id = newVal[0]?.m_posisi_id ?? null
}, { deep: true })

// Menambahkan detail item baru
const addDetail = () => {
  const tempItem = {
    primary: false,
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
  showSubDetail.value = true
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


// // Fungsi untuk mengambil detail item berdasarkan m_posisi_id dan index
// async function detail_item(m_posisi_id, index) {
//   try {
//     const ids = m_posisi_id.id;
//     const dataURL = `${store.server.url_backend}/operation/m_jobdesc`;
//     isRequesting.value = true;

//     const params = {
//       where: `this.m_posisi_id=${ids}`,
//       join: true,
//       transform: true,
//     };
//     const fixedParams = new URLSearchParams(params);

//     const res = await fetch(`${dataURL}?${fixedParams}`, {
//       headers: {
//         'Content-Type': 'application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`,
//       },
//     });

//     if (!res.ok) throw new Error('Gagal mengambil data');
//     const resultJson = await res.json();
//     const data = resultJson.data?.[0];
//     if (!data) return;

//     const id_jobdesc = data.id;

//     const dataURL2 = `${store.server.url_backend}/operation/m_jobdesc_d`;
//     const params2 = {
//       where: `this.m_jobdesc_id=${id_jobdesc}`,
//       join: true,
//       transform: true,
//     };
//     const fixedParams2 = new URLSearchParams(params2);

//     const res2 = await fetch(`${dataURL2}?${fixedParams2}`, {
//       headers: {
//         'Content-Type': 'application/json',
//         Authorization: `${store.user.token_type} ${store.user.token}`,
//       },
//     });

//     if (!res2.ok) throw new Error('Gagal mengambil detail jobdesc');
//     const resultJson2 = await res2.json();
//     const detailList = resultJson2.data;
//     const detailItem = inDetailArr.value[index];

//     if (!detailItem) {
//       return;
//     }
//     if (!detailItem.subDetails) {
//       detailItem.subDetails = [];
//     }

//     detailItem.subDetails = detailList.map((item) => ({
//       m_posisi_id: m_posisi_id.id,
//       jobdesc: item.jobdesc,
//       is_active: item.is_active,
//     }));

//   } catch (err) {
//     isBadForm.value = true;
//     swal.fire({
//       icon: 'error',
//       text: err.message || err,
//       allowOutsideClick: true,
//     }).then(() => {
//       router.back();
//     });
//   } finally {
//     isRequesting.value = false;
//   }
// }

// Menghapus subDetail dari item di inDetailArr
const hapusSubDetail = (detailIndex, subIndex) => {
  const detailItem = inDetailArr.value[detailIndex];
  detailItem.subDetails.splice(subIndex, 1);
};


// watchEffect(() => {
//   inDetailArr.value.forEach((item, index) => {
//     if (
//       item.m_posisi_id &&
//       item.subDetails.length === 1 &&
//       !item.subDetails[0].jobdesc
//     ) {
//       detail_item({ id: item.m_posisi_id }, index);
//     }
//   });
// });

const onSbuSelected = (index, v) => {
  const item = inDetailArr.value[index];
  if (!item) return;
  if (v) {
    item.m_comp_id = v;
  } else {
    item.m_comp_id = null;
    item.m_subcomp_id = null;
    item.m_company_id = null;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  }
};

const onSbuFullSelected = (index, obj) => {
  const item = inDetailArr.value[index];
  if (!item) return;
  if (obj) {
    item.m_comp_id = obj.id;
    item.m_subcomp_id = null;
    item.m_company_id = null;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  } else {
    item.m_comp_id = null;
    item.m_subcomp_id = null;
    item.m_company_id = null;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  }
};

const onSubSelected = async (index, v) => {
  const item = inDetailArr.value[index];
  if (!item) return;
  if (v) {
    item.m_subcomp_id = v;
    if (!item.m_company_id) {
      try {
        const res = await fetch(`${store.server.url_backend}/operation/m_subcomp/${v}?join=true&transform=false`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        });
        if (res.ok) {
          const json = await res.json();
          const subData = json.data ?? json;
          const compId = subData.m_company_id ?? subData.company_id ?? subData.m_company?.id ?? subData['m_company.id'] ?? subData['m_company_id.id'] ?? null;
          if (compId) {
            item.m_company_id = compId;
          }
        }
      } catch (e) {
        console.error('Error fetching subcomp detail:', e);
      }
    }
  } else {
    item.m_subcomp_id = null;
    item.m_company_id = null;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  }
};

const onSubFullSelected = async (index, obj) => {
  const item = inDetailArr.value[index];
  if (!item) return;
  if (obj) {
    item.m_subcomp_id = obj.id;
    let compId = obj.m_company_id ?? obj.company_id ?? obj.m_company?.id ?? obj['m_company.id'] ?? obj['m_company_id.id'] ?? null;
    if (!compId && obj.id) {
      try {
        const res = await fetch(`${store.server.url_backend}/operation/m_subcomp/${obj.id}?join=true&transform=false`, {
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        });
        if (res.ok) {
          const json = await res.json();
          const subData = json.data ?? json;
          compId = subData.m_company_id ?? subData.company_id ?? subData.m_company?.id ?? subData['m_company.id'] ?? subData['m_company_id.id'] ?? null;
        }
      } catch (e) {
        console.error('Error fetching subcomp detail:', e);
      }
    }
    item.m_company_id = compId;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  } else {
    item.m_subcomp_id = null;
    item.m_company_id = null;
    item.m_branch_id = null;
    item.m_divisi_id = null;
    item.m_posisi_id = null;
  }
};

// Menghubungkan perubahan posisi dengan pemanggilan detail_item
const onPosisiSelected = (index, posisi) => {
  inDetailArr.value[index].m_posisi_id = posisi.id;
  detail_item({ id: posisi.id }, index);
};

// Logika primary item yang aktif
watchEffect(() => {
  const primaryItem = inDetailArr.value.find(item => item.is_primary);
  if (primaryItem) {
    values.m_company_id = primaryItem.m_company_id;
    values.m_posisi_id = primaryItem.m_posisi_id;
    values.m_divisi_id = primaryItem.m_divisi_id;
    values.m_comp_id = primaryItem.m_comp_id;
    values.m_subcomp_id = primaryItem.m_subcomp_id;
    values.m_branch_id = primaryItem.m_branch_id;
  }
});

// Auto-detect Atasan berdasarkan Divisi & Posisi Hierarki
const listAtasan = ref([]);
const hierarchicalAtasan = ref([]);
const loadingAtasan = ref(false);

const getLevelLabel = (rank) => {
  switch (rank) {
    case 1: return 'Level 1 - Direksi';
    case 2: return 'Level 2 - General Manager';
    case 3: return 'Level 3 - Kepala Divisi';
    case 4: return 'Level 4 - Manager';
    case 5: return 'Level 5 - Supervisor';
    case 6: return 'Level 6 - Team Leader / Senior';
    case 7: return 'Level 7 - Staff';
    default: return `Level ${rank}`;
  }
};

const fetchAtasanByDivisi = async (divisiId, currentKaryId = null, currentPosisiId = null) => {
  if (!divisiId) {
    listAtasan.value = [];
    hierarchicalAtasan.value = [];
    return;
  }

  try {
    loadingAtasan.value = true;
    const params = new URLSearchParams({
      where: `this.is_active = 'true' AND this.m_divisi_id = '${divisiId}'`,
      join: true,
      transform: false,
      paginate: 100
    });

    const res = await fetch(`${store.server.url_backend}/operation/m_kary?${params}`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    });

    if (res.ok) {
      const json = await res.json();
      let data = json.data ?? [];
      
      const currentId = currentKaryId ?? route.params.id;
      if (currentId && currentId !== 'create') {
        data = data.filter(k => String(k.id) !== String(currentId));
      }

      const posisiTarget = currentPosisiId ?? values.m_posisi_id;
      const myRank = getPosisiRank(posisiTarget);

      // Filter: Hanya ambil karyawan dengan ranking jabatan LEBIH TINGGI (rank number lebih kecil)
      // Mencegah sesama Staff/Pelaksana muncul sebagai atasan
      const filtered = data.filter(item => {
        const kPosisiId = item.m_posisi_id;
        const kPosisiName = item['m_posisi.name'] ?? item.posisi_name ?? '';
        const kRank = getPosisiRank(kPosisiId, kPosisiName);

        // Jika karyawan saat ini adalah level Staff/Pelaksana/Non-Struktural (rank >= 6):
        // Atasan HARUS level struktural (rank <= 5: Supervisor, Manager, Kadiv, GM, Direktur)
        if (myRank >= 6) {
          return kRank <= 5;
        }

        // Jika karyawan saat ini sudah level struktural (misal Supervisor rank 5):
        // Atasan HARUS level yang lebih tinggi lagi (rank < 5: Manager ke atas)
        return kRank < myRank && kRank <= 4;
      });

      // Group per Level / Rank
      const groups = {};
      filtered.forEach(item => {
        const kPosisiId = item.m_posisi_id;
        const kPosisiName = item['m_posisi.name'] ?? item.posisi_name ?? '';
        const kRank = getPosisiRank(kPosisiId, kPosisiName);
        const rawLevel = posisiLevelMap.value[kPosisiId];
        const levelName = rawLevel && rawLevel !== '-' ? rawLevel : getLevelLabel(kRank);
        const nama = item.nama_lengkap ?? `${item.nama_depan ?? ''} ${item.nama_belakang ?? ''}`.trim();
        const displayItem = kPosisiName ? `${nama} (${kPosisiName})` : nama;

        if (!groups[kRank]) {
          groups[kRank] = {
            rank: kRank,
            level_name: levelName.startsWith('Level') ? levelName : `Level ${kRank} - ${levelName}`,
            pejabat: []
          };
        }
        groups[kRank].pejabat.push(displayItem);
      });

      // Urutkan dari level terdekat di atasnya (misal level 5 Supervisor -> level 4 Manager -> level 3 Kadiv -> level 1 Direksi)
      const sortedRanks = Object.keys(groups).map(Number).sort((a, b) => b - a);
      hierarchicalAtasan.value = sortedRanks.map(r => groups[r]);

      listAtasan.value = filtered.map(item => {
        const nama = item.nama_lengkap ?? `${item.nama_depan ?? ''} ${item.nama_belakang ?? ''}`.trim();
        const posisi = item['m_posisi.name'] ?? item.posisi_name ?? '';
        return {
          ...item,
          nama_lengkap: nama,
          posisi_name: posisi,
          display_text: posisi ? `${nama} (${posisi})` : nama
        };
      });

      if (hierarchicalAtasan.value.length > 0) {
        const closestRank = hierarchicalAtasan.value[0].rank;
        const closestCandidates = filtered.filter(item => getPosisiRank(item.m_posisi_id, item['m_posisi.name']) === closestRank);
        if (closestCandidates.length > 0) {
          const exist = closestCandidates.some(k => k.id === values.atasan_id);
          if (!exist || !values.atasan_id) {
            values.atasan_id = closestCandidates[0].id;
          }
        }
      } else {
        values.atasan_id = null;
      }
    }
  } catch (e) {
    console.error('Error fetching atasan:', e);
  } finally {
    loadingAtasan.value = false;
  }
};

watch([() => values.m_divisi_id, () => values.m_posisi_id], ([newDivisiId, newPosisiId]) => {
  if (newDivisiId) {
    fetchAtasanByDivisi(newDivisiId, route.params.id, newPosisiId);
  } else {
    listAtasan.value = [];
    hierarchicalAtasan.value = [];
    values.atasan_id = null;
  }
});

// Pendidikan
let _idPend = 0
const detailPendidikan = ref([])

const onCellValueChangedPend = (params) => {
  const rowIndex = params.node.rowIndex
  const field = params.colDef.field
  const newValue = params.newValue

  detailPendidikan.value[rowIndex][field] = newValue
}

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
  }
}

const addPendidikan = async () => {
  var tempObj = {}
  valuesPendidikan._id = ++_idPend
  for (const key in valuesPendidikan) {
    if (key !== 'desc' && key !== 'ijazah_foto') {
      if (valuesPendidikan[key] == null || valuesPendidikan[key] === '') {
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

function onDetailAddKeluarga(rows) {
  detailKeluarga.value.push({ seq: 1 })
  console.log(detailKeluarga, 'DETAILLL')
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
  valuesPengalaman._id = ++_idPk
  const tempObj = {}

  for (const key in valuesPengalaman) {
    if (key !== 'catatan' && valuesPengalaman[key] == null) {
      tempObj[key] = ['Bidang ini wajib diisi']
    }
  }

  if (Object.keys(tempObj).length) {
    formErrorsPK.value = tempObj
    swal.fire({ icon: 'error', text: 'Masih ada field yang belum terisi' })
    return
  }

  detailPengalaman.value.push({ ...valuesPengalaman })
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
  const detail_filter = inDetailArr.value

  const requiredFields = [
    { key: 'nama_depan', psn: 'Nama Depan wajib diisi' },
    { key: 'nik', psn: 'No. KTP wajib diisi' },
    { key: 'nama_belakang', psn: 'Nama Belakang wajib diisi' },
    { key: 'jk_id', psn: 'Jenis Kelamin wajib diisi' },
    { key: 'tempat_lahir', psn: 'Tempat Lahir wajib diisi' },
    { key: 'no_tlp', psn: 'No Telepon wajib diisi' },
    { key: 'status_kary_id', psn: 'Status Karyawan wajib diisi' },
    { key: 'm_subcomp_id', psn: 'Sub wajib diisi' },
    //{ key: 'm_branch_id', psn: 'Branch wajib diisi' }
  ]

  for (const field of requiredFields) {
    if (!values[field.key]) {
      swal.fire({
        icon: 'warning',
        text: field.psn
      })
      return
    }
  }

  if (!values.nama_depan) {
    swal.fire({
      icon: 'warning',
      text: 'Nama Depan Wajib Di Isi'
    })
    return
  }

  // if (!values.nik) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'No. Ktp Wajib Di Isi'
  //   })
  //   return
  // }

  // if (!values.nama_belakang) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Nama Belakang Wajib diisi'
  //   })
  //   return
  // }

  // if (!values.jk_id) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Jenis Kelamin Wajib Di Isi'
  //   })
  //   return
  // }

  // if (!values.tempat_lahir) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Tempat Lahir wajib diisi'
  //   })
  //   return
  // }
  // if (!values.no_tlp) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'No Telp wajib diisi'
  //   })
  //   return
  // }
  // if (!values.status_kary_id) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Status Karyawan wajib diisi'
  //   })
  //   return
  // }

  // if (!values.m_subcomp_id) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Sub Wajib diisi'
  //   })
  //   return
  // }

  // if (!values.m_branch_id) {
  //   swal.fire({
  //     icon: 'warning',
  //     text: 'Branch Wajib diisi'
  //   })
  //   return
  // }

  const detail_Jabatan = detail_filter.map(({ subDetails, ...header }) => header)
  const detail_Jobdesk = detail_filter.flatMap(item =>
    (item.subDetails || []).filter(sub => sub.jobdesc?.trim())
  )

  values.m_kary_det_jabatan = detail_Jabatan
  values.m_kary_det_jobdesc = detail_Jobdesk.length ? detail_Jobdesk : []
  values.m_kary_d_lokasi = detail_lokasi
  values.nama_lengkap = `${values.nama_depan ?? ''} ${values.nama_belakang ?? ''}`.trim()
  values.m_kary_det_pres = detailPrestasi.value
  values.m_kary_det_kel = detailKeluarga.value
  values.m_kary_det_org = detailOrganisasi.value
  values.m_kary_det_bhs = detailBahasa.value
  values.m_kary_det_pend = detailPendidikan.value
  values.m_kary_det_pel = detailPelatihan.value
  values.m_kary_det_pk = detailPengalaman.value

  if (values.periode_gaji_id) {
    values.m_kary_det_pemb = [{
      periode_gaji_id: values.periode_gaji_id,
      metode_id: values.metode_id,
      tipe_id: values.tipe_id,
      bank_id: values.bank_id,
      no_rek: values.no_rek,
      atas_nama_rek: values.atas_nama_rek,
      desc: values.desc,
      is_active: values.periode_gaji_idtrue
    }]
  }

  if (Array.isArray(values.m_jam_kerja_id)) {
    values.m_jam_kerja_id = JSON.stringify(values.m_jam_kerja_id)
  }

  const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)

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
      berkas_lain: values.berkas_lain,
      desc_file: values.desc_file,
      is_active: true
    }]
  } else {
    const kartu = values.m_kary_det_kartu[0]
    const init = initialValues.m_kary_det_kartu[0] || {}

    Object.assign(kartu, {
      ktp_no: values.ktp_no,
      kk_no: values.kk_no,
      npwp_no: values.npwp_no,
      npwp_tgl_berlaku: values.npwp_tgl_berlaku,
      bpjs_tipe_id: values.bpjs_tipe_id,
      bpjs_no_kesehatan: values.bpjs_no_kesehatan,
      bpjs_no_ketenagakerjaan: values.bpjs_no_ketenagakerjaan,
      berkas_lain: values.berkas_lain,
      desc_file: values.desc_file
    })

    if (init.ktp_foto !== tempKTP) kartu.ktp_foto = tempKTP
    if (init.pas_foto !== tempPasfoto) kartu.pas_foto = tempPasfoto
    if (init.kk_foto !== tempKK) kartu.kk_foto = tempKK
    if (init.npwp_foto !== tempNPWP) kartu.npwp_foto = tempNPWP
  }

  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
    isRequesting.value = true

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify({
        ...values,
        is_active: values.is_active ? 1 : 0,
        can_outscope: values.can_outscope ? 1 : 0,
      })
    })

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}
        throw (responseJson.errors?.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
      }
      throw ("Failed when trying to post data")
    }

    router.replace(`/${modulPath}?reload=${Date.parse(new Date())}`)
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
const statusFilter = ref(null)
const page = ref(1)



async function syncData() {
  swal.fire({
    icon: 'warning',
    text: 'Sync Karyawan?',
    iconColor: '#1469AE',
    confirmButtonColor: '#1469AE',
    showDenyButton: true
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}/syncKary`
        isRequesting.value = true
        const response = await fetch(dataURL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          }
        })
        if (!response.ok) {
          const responseJson = await response.json().catch(() => ({}))
          const msg = responseJson.message || 'Gagal melakukan sync'
          throw msg
        }
        const result = await response.json()
        swal.fire({
          icon: 'success',
          text: result.message || 'Sync berhasil'
        })
      } catch (err) {
        isBadForm.value = true
        swal.fire({
          icon: 'error',
          iconColor: '#1469AE',
          confirmButtonColor: '#1469AE',
          text: err.toString()
        })
      } finally {
        isRequesting.value = false
        apiTable.value.reload()
      }
    }
  })
}

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

function filterShowData(params, noBtn) {

  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }

  if (activeBtn.value == null) {
    // clear params filter
    statusFilter.value = null
  } else if (params) {
    statusFilter.value = `this.is_active=true`
  } else {
    statusFilter.value = `this.is_active=false`
  }

  page.value = 1
  apiTable.value.reload()
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => (currentMenu?.can_delete),
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation${endpointApi}/destroy?id=${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok) {
                const resultJson = await res.json()
                throw (resultJson.message || "Failed when trying to remove data")
              }
              swal.fire({
                icon: 'success',
                text: 'Data berhasil dihapus!',
                timer: 1500,
                showConfirmButton: false
              })
              apiTable.value.reload()
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
      show: (row) => (currentMenu?.can_read),
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) =>
        currentMenu?.can_update === true,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&` + tsId)
      }
    },
    // {
    //   icon: 'database',
    //   title: "Adjusment Cuti",
    //   class: 'bg-yellow-600 text-light-100',
    //   show: (row) => (currentMenu?.can_update),
    //   click(row) {
    //     router.replace(`/adj_cuti/${row.id}?action=Adjusment&` + tsId)
    //   }
    // }
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => currentMenu?.can_create,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
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
      page: page.value,
      paginate: 25,
      kary_id: store.user.data.m_kary_id ?? 0,
      ...(statusFilter.value ? { where: statusFilter.value } : {}),
      join: true,
      transform: true
    })),

    onsuccess(response) {
      response.page = response.current_page
      response.hasNext = response.has_next

      if (response.has_next) {
        page.value++
      }
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
    headerName: 'Kode',
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
    field: 'status_kary.value',
    headerName: 'Status Karyawan',
    filter: 'ColFilter',
    sortable: true,
    flex: 1,
    resizable: true,
    cellClass: ['border-r', '!border-gray-200', 'justify-start']
  },
  {
    headerName: 'Status',
    field: 'is_active',
    filter: true,
    // resizable: true,
    // valueGetter: (p) => p.node.data['status'].toLowerCase()==='active'? 'Aktif':'Tidak Aktif',
    sortable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200', 'justify-center'],
    cellRenderer: ({ value }) => {
      return value === true
        ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
        : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
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