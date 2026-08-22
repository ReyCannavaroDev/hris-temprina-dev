import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, computed, watchEffect, watch, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
console.log('action', actionText.value)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const tsId = `ts=` + (Date.parse(new Date()))
const kary = (route.query.isKaryId && route.query.isKaryId !== 'undefined' && route.query.isKaryId !== 'null') ? route.query.isKaryId : null
const kary_id = ref(kary)
const karyId = ref(null)

// ------------------------------ PERSIAPAN
const endpointApi = 't_assessment_kary'
onBeforeMount(() => {
  document.title = 'Transaksi Penilaian Karyawan'
})

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault();
    onSave();
  }
}

let initialValues = {}

const values = reactive({
  m_kary_id: kary,
  atasan_id: store.user?.data?.m_kary_id || null
})

const apiKary = computed(() => ({
  url: `${store.server.url_backend}/operation/m_kary`,
  headers: {
    'Content-Type': 'Application/json',
    Authorization: `${store.user.token_type} ${store.user.token}`
  },
  params: {
    simplest: false,
    transform: false,
    join: true,
    searchfield: 'this.kode,this.nama_lengkap,atasan.nama_lengkap,m_posisi.name,m_divisi.name_old'
  },
  onsuccess(response) {
    response.page = response.current_page
    response.hasNext = response.has_next
    return response
  }
}))

const yearOptions = ref([])
const selectedSeq = ref({})
const detailArr = ref([])

function defaultValues() {
  values.m_kary_id = kary || null
  values.atasan_id = store.user?.data?.m_kary_id || null
  values.tanggal = null
  values.m_assessment_kary_id = null
  values.tipe_penilaian = null
  values.penilaian = null
  values.nama_jabatan = null
  values.nama_divisi = null
  values.nama_level = null
  values.catatan_1 = null
  values.catatan_2 = null
  values.catatan_3 = null
  values.catatan_4 = null
  values.rata_rata = null
  detailArr.value = []
  selectedSeq.value = {}
}

const onReset = async (alert = false) => {
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
        } else {
          for (const key in values) {
            delete values[key]
          }
          defaultValues()
        }
      }
    })
  }

  setTimeout(() => {
    defaultValues()
  }, 100)
}

const hitungNilaiAkhirLangsung = () => {
  let totalSkor = 0;
  let totalBobot = 0;
  let countItems = 0;

  detailArr.value.forEach(item => {
    const bobot = parseFloat(item.bobot) || 0;
    const totalNilai = parseFloat(item.total_nilai) || 0;

    totalSkor += totalNilai;
    if (bobot > 0) {
      totalBobot += bobot;
    }
    countItems++;
  });

  let hasil = 0;
  if (totalBobot > 0) {
    hasil = (totalSkor / (totalBobot * 5)) * 10;
  } else if (countItems > 0) {
    hasil = totalSkor / countItems;
  }

  if (isNaN(hasil) || !isFinite(hasil) || hasil < 0) {
    hasil = 0;
  } else if (hasil > 99.99) {
    hasil = 99.99;
  }

  values.rata_rata = hasil.toFixed(2);
  return hasil;
};

function isNilaiMatch(a, b) {
  if (a === undefined || a === null || a === '' || b === undefined || b === null || b === '') return false;
  return parseFloat(a) === parseFloat(b) || String(a).trim() === String(b).trim();
}

const hitungTotalNilai = (item, selectedValue) => {
  const subTerpilih = (item.t_assessment_kary_sub_d || []).find(sub => isNilaiMatch(sub.nilai, selectedValue));
  if (!subTerpilih) {
    item.total_nilai = 0;
    return 0;
  }

  const hasil = (parseFloat(subTerpilih.nilai) || 0) * (parseFloat(item.bobot) || 0);
  item.total_nilai = hasil;
  return hasil;
};

watch(selectedSeq, (newVal) => {
  detailArr.value.forEach((item, index) => {
    const selectedValue = newVal[index];
    if (Array.isArray(item.t_assessment_kary_sub_d)) {
      item.t_assessment_kary_sub_d.forEach(sub => {
        sub.is_selected = isNilaiMatch(sub.nilai, selectedValue);
      });
    }
    item.total_nilai = hitungTotalNilai(item, selectedValue);
  });

  hitungNilaiAkhirLangsung();
}, { deep: true });

async function resolveNamaDivisi(karyawanObj) {
  if (!karyawanObj) return '';

  const raw = karyawanObj.nama_divisi || karyawanObj['nama_divisi'] || karyawanObj['m_divisi.name'] || karyawanObj.divisi;

  if (raw && typeof raw === 'string' && isNaN(raw) && raw.trim() !== '') {
    return raw.trim();
  }

  const divisiId = karyawanObj.m_divisi_id || karyawanObj['m_divisi.id'] || (raw && !isNaN(raw) ? raw : null);

  if (divisiId) {
    try {
      const resDiv = await fetch(`${store.server.url_backend}/operation/m_divisi/${divisiId}?scopes=Name&simplest=true&transform=false`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(r => r.json()).then(j => j.data);

      if (resDiv) {
        const textVal = resDiv['name.value'] || resDiv.value || resDiv.name_old;
        if (textVal && isNaN(textVal)) return textVal;

        if (resDiv.name && !isNaN(resDiv.name)) {
          const resGen = await fetch(`${store.server.url_backend}/operation/m_general/${resDiv.name}?simplest=true`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          }).then(r => r.json()).then(j => j.data);
          if (resGen && resGen.value) return resGen.value;
        }

        if (resDiv.name && isNaN(resDiv.name)) return resDiv.name;
      }
    } catch (e) {
      console.error('Gagal resolve m_divisi:', e);
    }
  }

  if (raw && !isNaN(raw)) {
    try {
      const resGen = await fetch(`${store.server.url_backend}/operation/m_general/${raw}?simplest=true`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(r => r.json()).then(j => j.data);
      if (resGen && resGen.value) return resGen.value;
    } catch (e) {
      console.error('Gagal resolve m_general:', e);
    }
  }

  return (raw && isNaN(raw)) ? raw : '';
}

async function resolveNamaLevel(karyawanObj) {
  if (!karyawanObj) return '';

  const raw = karyawanObj.nama_level || karyawanObj.level_name || karyawanObj['m_level_posisi.level_name'] || karyawanObj.level || karyawanObj.lvl;

  if (raw && typeof raw === 'string' && isNaN(raw) && raw.trim() !== '') {
    return raw.trim();
  }

  const posisiId = karyawanObj.m_posisi_id || karyawanObj['m_posisi.id'] || karyawanObj.posisi_id;
  if (posisiId) {
    try {
      const resLvlD = await fetch(`${store.server.url_backend}/operation/m_level_posisi_d?where=this.m_posisi_id=${posisiId}&join=true&simplest=true&transform=false`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(r => r.json()).then(j => j.data);

      if (Array.isArray(resLvlD) && resLvlD.length > 0) {
        const first = resLvlD[0];
        const lvlName = first['m_level_posisi.level_name'] || first.level_name;
        if (lvlName && typeof lvlName === 'string' && lvlName.trim() !== '') {
          return lvlName.trim();
        }

        const lvlId = first.m_level_posisi_id || first['m_level_posisi.id'];
        if (lvlId) {
          const resLvl = await fetch(`${store.server.url_backend}/operation/m_level_posisi/${lvlId}?simplest=true&transform=false`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          }).then(r => r.json()).then(j => j.data);
          if (resLvl && resLvl.level_name) {
            return String(resLvl.level_name).trim();
          }
        }
      }
    } catch (e) {
      console.error('Gagal resolve m_level_posisi_d:', e);
    }
  }

  const directLevelId = karyawanObj.m_level_posisi_id || karyawanObj['m_level_posisi.id'];
  if (directLevelId) {
    try {
      const resLvl = await fetch(`${store.server.url_backend}/operation/m_level_posisi/${directLevelId}?simplest=true&transform=false`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(r => r.json()).then(j => j.data);
      if (resLvl && resLvl.level_name) {
        return String(resLvl.level_name).trim();
      }
    } catch (e) {
      console.error('Gagal resolve direct m_level_posisi:', e);
    }
  }

  return (raw && isNaN(raw)) ? String(raw).trim() : '';
}

async function onKaryawanSelected(karyawan) {
  values.m_assessment_kary_id = null;
  values.tipe_penilaian = null;
  values.penilaian = null;
  detailArr.value = [];
  selectedSeq.value = {};

  if (!karyawan) return;

  values.m_kary_id = karyawan.id;
  if (karyawan['nama_lengkap']) {
    values.nama = karyawan['nama_lengkap'];
    values.penilaian = karyawan['nama_lengkap'];
  }
  if (karyawan['m_posisi.name'] || karyawan?.jabatan) {
    values.nama_jabatan = karyawan['m_posisi.name'] || karyawan?.jabatan;
  }

  const initialDivisi = karyawan['nama_divisi'] || karyawan['m_divisi.name'];
  if (initialDivisi && isNaN(initialDivisi)) {
    values.nama_divisi = initialDivisi;
  } else {
    values.nama_divisi = '';
  }

  const initialLevel = karyawan['nama_level'] || karyawan['level_name'] || karyawan['m_level_posisi.level_name'];
  if (initialLevel && isNaN(initialLevel)) {
    values.nama_level = initialLevel;
  } else {
    values.nama_level = '';
  }

  if (karyawan.m_comp_id) values.m_comp_id = karyawan.m_comp_id;
  if (karyawan.m_subcomp_id) values.m_subcomp_id = karyawan.m_subcomp_id;
  if (karyawan.m_branch_id) values.m_branch_id = karyawan.m_branch_id;

  resolveNamaDivisi(karyawan).then(divName => {
    if (divName) values.nama_divisi = divName;
  });

  resolveNamaLevel(karyawan).then(lvlName => {
    if (lvlName) values.nama_level = lvlName;
  });

  if (karyawan?.id) {
    try {
      const url = `${store.server.url_backend}/operation/m_kary/${karyawan.id}`
      const params = new URLSearchParams({
        join: true,
        transform: false
      })

      const hasilData = await fetch(`${url}?${params}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      }).then(res => res.json()).then(json => json.data)

      if (hasilData) {
        values.nama = hasilData?.nama_lengkap || values.nama || '';
        values.penilaian = hasilData?.nama_lengkap || values.penilaian || '';
        if (hasilData['m_posisi.name'] || hasilData?.jabatan) {
          values.nama_jabatan = hasilData['m_posisi.name'] || hasilData?.jabatan;
        }

        const divNameDetail = await resolveNamaDivisi(hasilData);
        if (divNameDetail) {
          values.nama_divisi = divNameDetail;
        }

        const lvlNameDetail = await resolveNamaLevel(hasilData);
        if (lvlNameDetail) {
          values.nama_level = lvlNameDetail;
        }

        const posisiId = hasilData.m_posisi_id || karyawan.m_posisi_id;
        if (!values.nama_jabatan && posisiId) {
          try {
            const resPosisi = await fetch(`${store.server.url_backend}/operation/m_posisi/${posisiId}`, {
              headers: {
                'Content-Type': 'application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              }
            }).then(r => r.json()).then(j => j.data);
            if (resPosisi && resPosisi.name) {
              values.nama_jabatan = resPosisi.name;
            }
          } catch (e) {
            console.error('Gagal fetch master posisi:', e);
          }
        }
      }
    } catch (err) {
      console.error('Gagal fetch detail karyawan:', err)
    }
  }
}

const onTipePenilaianSelected = async (v) => {
  if (isRead || !['Tambah', 'Create', 'Copy'].includes(actionText.value)) {
    console.log('Skip onTipePenilaianSelected: bukan mode Tambah atau isRead aktif');
    return;
  }

  if (!v) {
    values.m_assessment_kary_id = null;
    detailArr.value = [];
    selectedSeq.value = {};
    return;
  }
  values.m_assessment_kary_id = v;

  try {
    isRequesting.value = true;

    // Ambil detail kriteria m_assessment_kary_d
    const urlDetail = `${store.server.url_backend}/operation/m_assessment_kary_d?where=this.m_assessment_kary_id=${v}&join=true&simplest=false&transform=false`;
    const res = await fetch(urlDetail, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    });

    if (res.ok) {
      const resultJson = await res.json();
      const items = resultJson.data || [];

      if (items.length > 0) {
        // Ambil semua sub opsi nilai untuk masing-masing item kriteria
        const detailResult = await Promise.all(
          items.map(async (item) => {
            let kategoriName = item['m_general.value'] || item.kategori_name;
            if (!kategoriName || typeof kategoriName !== 'string') {
              kategoriName = (typeof item.nama_kategori === 'string' && item.nama_kategori) ? item.nama_kategori : 'Kategori Penilaian';
            }

            let subList = Array.isArray(item.m_assessment_kary_sub_d) ? item.m_assessment_kary_sub_d : [];

            // Jika sub_d belum disertakan dalam response, fetch dari endpoint m_assessment_kary_sub_d
            if (!subList.length && item.id) {
              try {
                const urlSub = `${store.server.url_backend}/operation/m_assessment_kary_sub_d?where=this.m_assessment_kary_d_id=${item.id}&simplest=true&transform=false`;
                const resSub = await fetch(urlSub, {
                  headers: {
                    'Content-Type': 'application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  }
                });
                if (resSub.ok) {
                  const jsonSub = await resSub.json();
                  subList = jsonSub.data || [];
                }
              } catch (e) {
                console.error('Gagal fetch sub detail:', e);
              }
            }

            const formattedSubList = subList
              .sort((a, b) => (parseFloat(b.nilai) || 0) - (parseFloat(a.nilai) || 0))
              .map((sub) => ({
                t_assessment_kary_d_id: 0,
                nama_keterangan: String(sub.nama_keterangan || sub.keterangan || '-'),
                nilai: parseFloat(sub.nilai) || 0,
                is_selected: false
              }));

            return {
              t_assessment_kary_id: 0,
              nama_kategori: String(kategoriName),
              nama_assessment: String(item.nama_assessment || '-'),
              bobot: parseFloat(item.bobot) || 1,
              total_nilai: 0,
              t_assessment_kary_sub_d: formattedSubList
            };
          })
        );

        detailArr.value = detailResult;
        selectedSeq.value = {};
        console.log('detailArr berhasil dimuat secara lengkap:', detailArr.value);
        return;
      }
    }
  } catch (err) {
    console.error('Gagal ambil data template penilaian:', err);
  } finally {
    isRequesting.value = false;
  }
};

onBeforeMount(async () => {
  if (localStorage.getItem('respo')) {
    const respoValues = JSON.parse(localStorage.getItem('respo'))
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
    data.respo_id = respoValues.id
    data.m_subcomp_id = respoValues.m_subcomp_id
    data.m_branch_id = respoValues.m_branch_id
  }

  if (isRead) {
    try {
      isRequesting.value = true;
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${route.params.id}`;

      const res = await fetch(dataURL + '?transform=false', {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!res.ok) throw new Error("Gagal membaca data penilaian");

      const resultJson = await res.json();
      initialValues = resultJson.data || {};

      for (const key in initialValues) {
        values[key] = initialValues[key];
      }

      if (initialValues.tipe_penilaian) {
        values.tipe_penilaian = initialValues.tipe_penilaian;
      }
      if (initialValues.penilaian) {
        values.penilaian = initialValues.penilaian;
      }

      if (!values.tipe_penilaian && values.m_assessment_kary_id) {
        try {
          const urlAss = `${store.server.url_backend}/operation/m_assessment_kary?where=this.id=${values.m_assessment_kary_id}&simplest=true&transform=false&join=true`;
          const resAss = await fetch(urlAss, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          });
          if (resAss.ok) {
            const jsonAss = await resAss.json();
            const assData = (jsonAss.data && jsonAss.data[0]) ? jsonAss.data[0] : {};
            values.tipe_penilaian = assData['type.value'] || assData.tipe_penilaian || '';
            if (!values.penilaian) {
              values.penilaian = assData['type.value'] || assData.deskripsi || '';
            }
          }
        } catch (e) {
          console.error('Gagal resolve tipe_penilaian master:', e);
        }
      }

      let dList = Array.isArray(initialValues.t_assessment_kary_d) ? initialValues.t_assessment_kary_d : [];

      // Jika t_assessment_kary_d belum ter-include dalam response get single, fetch dari operation/t_assessment_kary_d
      if (!dList.length) {
        try {
          const urlD = `${store.server.url_backend}/operation/t_assessment_kary_d?where=this.t_assessment_kary_id=${route.params.id}&simplest=false&transform=false`;
          const resD = await fetch(urlD, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          });
          if (resD.ok) {
            const jsonD = await resD.json();
            dList = jsonD.data || [];
          }
        } catch (e) {
          console.error('Gagal fetch t_assessment_kary_d:', e);
        }
      }

      // Pastikan setiap detail baris memiliki t_assessment_kary_sub_d
      const loadedDetails = await Promise.all(
        dList.map(async (item) => {
          let subList = Array.isArray(item.t_assessment_kary_sub_d) ? item.t_assessment_kary_sub_d : [];

          if (!subList.length && item.id) {
            try {
              const urlSub = `${store.server.url_backend}/operation/t_assessment_kary_sub_d?where=this.t_assessment_kary_d_id=${item.id}&simplest=true&transform=false`;
              const resSub = await fetch(urlSub, {
                headers: {
                  'Content-Type': 'application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              });
              if (resSub.ok) {
                const jsonSub = await resSub.json();
                subList = jsonSub.data || [];
              }
            } catch (e) {
              console.error('Gagal fetch t_assessment_kary_sub_d:', e);
            }
          }

          const formattedSubList = subList
            .sort((a, b) => (parseFloat(b.nilai) || 0) - (parseFloat(a.nilai) || 0))
            .map((sub) => {
              const isChecked = sub.is_selected === true || sub.is_selected === 1 || sub.is_selected === '1' || sub.is_selected === 'true';
              return {
                id: sub.id,
                t_assessment_kary_d_id: item.id || 0,
                nama_keterangan: String(sub.nama_keterangan || sub.keterangan || '-'),
                nilai: parseFloat(sub.nilai) || 0,
                is_selected: isChecked
              };
            });

          return {
            id: item.id,
            t_assessment_kary_id: route.params.id,
            nama_kategori: String(item.nama_kategori || item['m_general.value'] || item.kategori_name || 'Kategori Penilaian'),
            nama_assessment: String(item.nama_assessment || '-'),
            bobot: parseFloat(item.bobot) || 1,
            total_nilai: parseFloat(item.total_nilai) || 0,
            t_assessment_kary_sub_d: formattedSubList
          };
        })
      );

      detailArr.value = loadedDetails;
      const initialSelected = {};
      detailArr.value.forEach((item, index) => {
        const selectedSub = (item.t_assessment_kary_sub_d || []).find(sub => sub.is_selected);
        if (selectedSub) {
          initialSelected[index] = selectedSub.nilai;
        } else if (item.total_nilai && item.bobot) {
          const calculatedNilai = parseFloat(item.total_nilai) / (parseFloat(item.bobot) || 1);
          const matchedSub = (item.t_assessment_kary_sub_d || []).find(sub => isNilaiMatch(sub.nilai, calculatedNilai));
          if (matchedSub) {
            initialSelected[index] = matchedSub.nilai;
            matchedSub.is_selected = true;
          }
        }
      });
      selectedSeq.value = initialSelected;

      detailArr.value.forEach((item, index) => {
        item.total_nilai = hitungTotalNilai(item, initialSelected[index]);
      });
      hitungNilaiAkhirLangsung();

      console.log('Detail loaded on isRead:', detailArr.value, selectedSeq.value);

      const karyIdTarget = initialValues.m_kary_id || initialValues['m_kary.id'];
      if (karyIdTarget) {
        try {
          const url = `${store.server.url_backend}/operation/m_kary/${karyIdTarget}`
          const params = new URLSearchParams({
            simplest: true,
            transform: false,
            join: false
          })

          const hasilData = await fetch(`${url}?${params}`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
            }
          }).then(r => r.json()).then(j => j.data)

          if (hasilData) {
            values.nama = hasilData?.nama_lengkap || values.nama || '';
            values.jabatan = hasilData?.jabatan || '';
            values.nama_jabatan = hasilData['m_posisi.name'] || hasilData?.jabatan || values.nama_jabatan || '';
            const resolvedDiv = await resolveNamaDivisi(hasilData);
            if (resolvedDiv) {
              values.nama_divisi = resolvedDiv;
            }
            const resolvedLvl = await resolveNamaLevel(hasilData);
            if (resolvedLvl) {
              values.nama_level = resolvedLvl;
            }
          }
        } catch (e) {
          console.error(e)
        }
      }
    } catch (err) {
      isBadForm.value = true;
      swal.fire({
        icon: 'error',
        text: err,
        allowOutsideClick: false,
        confirmButtonText: 'Kembali',
      }).then(() => {
        router.back();
      });
    } finally {
      isRequesting.value = false;
    }
  }
});

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + (modulPath || 't_penilaian_kary'))
  }
}

function extractAllErrorMessages(obj) {
  if (!obj) return '';
  if (typeof obj === 'string') return obj;
  let msgs = [];
  function rec(curr) {
    if (typeof curr === 'string') {
      msgs.push(curr);
    } else if (Array.isArray(curr)) {
      curr.forEach(c => rec(c));
    } else if (typeof curr === 'object' && curr !== null) {
      Object.values(curr).forEach(val => rec(val));
    }
  }
  rec(obj);
  return msgs.join('\n');
}

async function onSave() {
  try {
    isRequesting.value = true;

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : '/' + route.params.id}`;

    // Pastikan seluruh sub_d memiliki flag is_selected yang benar dan nama_kategori selalu string
    const detailPayload = detailArr.value.map((item, idx) => {
      const selectedVal = selectedSeq.value[idx];
      const subTerpilih = (item.t_assessment_kary_sub_d || []).find(sub => isNilaiMatch(sub.nilai, selectedVal));
      const bobotNum = parseFloat(item.bobot) || 1;
      const totalNilai = subTerpilih ? (parseFloat(subTerpilih.nilai) || 0) * bobotNum : (parseFloat(item.total_nilai) || 0);

      const subListPayload = (item.t_assessment_kary_sub_d || []).map(sub => ({
        t_assessment_kary_d_id: sub.t_assessment_kary_d_id || 0,
        nama_keterangan: String(sub.nama_keterangan || sub.keterangan || '-'),
        nilai: parseFloat(sub.nilai) || 0,
        is_selected: isNilaiMatch(sub.nilai, selectedVal)
      }));

      const kategoriStr = String(item.nama_kategori || item['m_general.value'] || item.kategori_name || 'Kategori Penilaian');

      return {
        t_assessment_kary_id: item.t_assessment_kary_id || 0,
        nama_assessment: String(item.nama_assessment || '-'),
        nama_kategori: kategoriStr,
        bobot: bobotNum,
        total_nilai: totalNilai,
        t_assessment_kary_sub_d: subListPayload
      };
    });

    values.t_assessment_kary_d = detailPayload;

    if (localStorage.getItem('respo')) {
      const respo = JSON.parse(localStorage.getItem('respo'));
      if (!values.m_comp_id && respo.m_comp_id) values.m_comp_id = respo.m_comp_id;
      if (!values.m_subcomp_id && respo.m_subcomp_id) values.m_subcomp_id = respo.m_subcomp_id;
      if (!values.m_branch_id && respo.m_branch_id) values.m_branch_id = respo.m_branch_id;
    }
    if (!values.atasan_id && store.user?.data?.m_kary_id) {
      values.atasan_id = store.user.data.m_kary_id;
    }

    hitungNilaiAkhirLangsung();

    const payload = {
      ...values,
      status: 'DRAFT',
    };

    console.log('Payload yang dikirim ke server:', payload);

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    });

    const responseJson = await res.json();
    console.log('Response dari server:', responseJson);

    if (!res.ok) {
      formErrors.value = responseJson.errors || {};
      let errorMsg = responseJson.message || 'Maaf data belum valid, silahkan dikoreksi';
      let detailList = [];

      if (responseJson.errors && typeof responseJson.errors === 'object') {
        for (const [key, val] of Object.entries(responseJson.errors)) {
          if (Array.isArray(val)) {
            detailList.push(`${key}: ${val.join(', ')}`);
          } else if (typeof val === 'object' && val !== null) {
            for (const [subKey, subVal] of Object.entries(val)) {
              detailList.push(`${key}.${subKey}: ${Array.isArray(subVal) ? subVal.join(', ') : subVal}`);
            }
          } else {
            detailList.push(`${key}: ${val}`);
          }
        }
      }

      if (detailList.length) {
        errorMsg += ':\n' + detailList.join('\n');
      }

      throw errorMsg;
    }

    await swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: responseJson.message || 'Data Penilaian Karyawan Berhasil Disimpan',
      timer: 1500,
      showConfirmButton: false
    });

    router.replace(`/${modulPath || 't_penilaian_kary'}?reload=${Date.now()}`);
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'warning',
      title: 'Gagal Menyimpan',
      text: typeof err === 'string' ? err : JSON.stringify(err)
    });
  } finally {
    isRequesting.value = false;
  }
}

// ------------------------------ LANDING
const activeBtn = ref(null)
const data = reactive({
  filter_divisi_id: null,
  can_read: true,
  can_create: true,
  can_update: true,
  can_delete: true
})

function filterShowData(params, noBtn) {
  if (activeBtn.value === noBtn) {
    activeBtn.value = null
  } else {
    activeBtn.value = noBtn
  }

  updateLandingFilter()
}

function onFilterDivisiChange() {
  updateLandingFilter()
}

function updateLandingFilter() {
  let whereClauses = [];

  if (activeBtn.value != null) {
    whereClauses.push(activeBtn.value === 1 ? `this.status='DRAFT'` : `this.status='POSTED'`);
  }

  if (data.filter_divisi_id) {
    whereClauses.push(`m_kary.m_divisi_id=${data.filter_divisi_id}`);
  }

  landing.api.params.where = whereClauses.length ? whereClauses.join(' AND ') : null;
  apiTable.value?.reload();
}

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row['status'] === 'DRAFT',
      click(row) {
        swal.fire({
          icon: 'warning',
          text: 'Hapus Data Terpilih?',
          confirmButtonText: 'Yes',
          showDenyButton: true,
        }).then(async (result) => {
          if (result.isConfirmed) {
            try {
              const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${row.id}`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                }
              })
              if (!res.ok) {
                const resultJson = await res.json()
                throw (resultJson.message || "Failed when trying to remove data")
              }
              apiTable.value?.reload()
            } catch (err) {
              isBadForm.value = true
              swal.fire({
                icon: 'error',
                text: err
              })
            } finally {
              isRequesting.value = false
            }
          }
        })
      }
    },
    {
      icon: 'eye',
      title: "Read",
      class: 'bg-green-600 text-light-100',
      show: () => true,
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row['status'] === 'DRAFT',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&isKaryId=${row.m_kary_id}&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row['status'] === 'DRAFT',
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
              const dataURL = `${store.server.url_backend}/operation/t_assessment_kary/postData`
              isRequesting.value = true
              const res = await fetch(dataURL, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                body: JSON.stringify({ id: row.id })
              })
              if (!res.ok) {
                if ([400, 422, 500].includes(res.status)) {
                  const responseJson = await res.json()
                  formErrors.value = responseJson.errors || {}
                  throw (responseJson.message + (responseJson.data?.errorText ? " " + responseJson.data.errorText : "") || "Failed when trying to post data")
                } else {
                  throw ("Failed when trying to post data")
                }
              }
              const responseJson = await res.json()
              swal.fire({
                icon: 'success',
                text: responseJson.message
              })
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
              apiTable.value?.reload()
            }
          }
        })
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => row['status'] === 'DRAFT',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
  ],
  api: {
    url: `${store.server.url_backend}/operation/${endpointApi}`,
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      searchfield: 'this.nama, m_kary.nama_lengkap'
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
      sortable: true,
      resizable: true,
      filter: true,
      cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
    },
    {
      headerName: 'Nama',
      field: 'm_kary.nama_lengkap',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      wrapText: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: 'Tanggal',
      field: 'tanggal',
      filter: true,
      sortable: true,
      flex: 1,
      filter: 'ColFilter',
      resizable: true,
      wrapText: true,
      cellClass: ['border-r', '!border-gray-200', 'justify-start']
    },
    {
      headerName: 'Status',
      field: 'status',
      sortable: true,
      resizable: true,
      wrapText: true,
      autoHeight: true,
      flex: 1,
      cellClass: ['border-r', '!border-gray-200', 'justify-center'],
      cellRenderer: ({ value }) => {
        const color = value === 'DRAFT' ? '#6b7280' : '#FFC107';
        return `<span style="color: ${color}; font-weight: bold;">${value}</span>`;
      }
    }
  ]
})

onActivated(() => {
  if (apiTable.value) {
    if (route.query.reload) {
      apiTable.value.reload()
    }
  }
})

watchEffect(() => store.commit?.('set', ['isRequesting', isRequesting.value]))