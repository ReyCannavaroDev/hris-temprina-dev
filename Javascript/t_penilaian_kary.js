import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, computed, watchEffect, watch, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
// console.log('test',store.user.data.id)
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
const isModalOpen = ref(false);
const kary = route.query.isKaryId
// console.log('kary', route.query.isKaryId)
const kary_id = ref(route.query.isKaryId)
const karyId = ref(null)

// ------------------------------ PERSIAPAN
const endpointApi = 't_assessment_kary'
onBeforeMount(() => {
  document.title = 'Transaksi Penilaian Karyawan'
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS

// HOT KEY
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
})



onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);

})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault();
    onSave();
  }
}

let initialValues = {}
const changedValues = []

const user = JSON.parse(localStorage.getItem('user'))

const values = reactive({
  // penilaian: route.query.isKaryId || '',
  m_kary_id: route.query.isKaryId,
  atasan_id: store.user.data.m_kary_id
})

const yearOptions = ref([])

const selectedSeq = ref({})
watch(selectedSeq, (newVal) => {
  detailArr.value.forEach((item, index) => {
    const selected = newVal[index]
    item.t_assessment_kary_sub_d.forEach(sub => {
      sub.is_selected = sub.nilai === selected
    })
  })
}, { deep: true })



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
  let totalBobotMax = 0;

  detailArr.value.forEach(item => {
    const bobot = parseFloat(item.bobot) || 0;
    const totalNilai = parseFloat(item.total_nilai) || 0;

    totalSkor += totalNilai;
    totalBobotMax += 5 * bobot;
  });

  const hasil = totalBobotMax ? (totalSkor / totalBobotMax) * 10 : 0;
  values.rata_rata = hasil.toFixed(2);
  return hasil;
};


watch(selectedSeq, (newVal) => {
  detailArr.value.forEach((item, index) => {
    const selectedValue = newVal[index];
    // item.total_bobot = initialValues.bobot * 5;
    // console.log('total',total_bobot)
    item.total_nilai = hitungTotalNilai(item, selectedValue);
    // item.total_bobot = hitungTotalBobot(item, bobot)
  });

   hitungNilaiAkhirLangsung();
}, { deep: true });


const hitungTotalNilai = (item, selectedValue) => {
  const subTerpilih = item.t_assessment_kary_sub_d.find(sub => sub.nilai === selectedValue);
  if (!subTerpilih) {
    item.total_nilai = 0;
    updateTotalKeseluruhan();
    return 0;
  }

  const hasil = (parseFloat(subTerpilih.nilai) || 0) * (parseFloat(item.bobot) || 0);
  item.total_nilai = hasil;
  updateTotalKeseluruhan();
  return hasil;
};

const hitungTotalKeseluruhan = (listItem) => {
  return listItem.reduce((total, item) => {
    return total + (parseFloat(item.total_nilai) || 0);
  }, 0);
};

const updateTotalKeseluruhan = () => {
  const total = hitungTotalKeseluruhan(detailArr.value);
  console.log('Total keseluruhan:', total);
};



function extractField(groupList, callback) {
  return groupList.flatMap(group =>
    group.data?.map(callback) || []
  );
}

function extractSub(groupList) {
  let globalIndex = 0

  return groupList.flatMap(group =>
    group.data?.flatMap(item =>
      item.m_assessment_kary_sub_d?.map(data => ({
        ...data,
        t_assessment_kary_d_id: 0,
        nama_keterangan: data.keterangan,
        is_selected: false,
        seq: globalIndex++
      })) || []
    ) || []
  );
}


const onTipePenilaianSelected = async (v) => {
  values.m_assessment_kary_id = v;

  try {
    const dataURLType = `${store.server.url_backend}/operation/m_assessment_kary/${v}?group=true`;

    const res = await fetch(dataURLType, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      }
    });

    const resultDetail = await res.json();
    const groupList = resultDetail.data?.m_assessment_kary_d_group || [];

    const bawahKat = extractField(groupList, item => item.nama_assessment);
    const bobot = extractField(groupList, item => item.bobot);
    const sub = extractSub(groupList);


    const detailResult = groupList.flatMap(group => {
      return group.data.map((item) => {
        console.log('ini item', item)
        return {
          t_assessment_kary_id: 0,
          nama_kategori: group.name_kategori,
          nama_assessment: item.nama_assessment,
          bobot: item.bobot,
          deskripsi: resultDetail.deskripsi,
          is_active: resultDetail.is_active,
          t_assessment_kary_sub_d: item.m_assessment_kary_sub_d.map((sub) => {
            console.log('ini sub', sub)
            return {
              t_assessment_kary_d_id: 0,
              nama_keterangan: sub.keterangan,
              nilai: sub.nilai,
              is_selected: false

            }
          })
        };
      });
    });

    detailArr.value = detailResult;
    console.log('ini detailArr', detailArr.value)

  } catch (err) {
    console.error('Gagal ambil data:', err);
  }
};

onBeforeMount(async () => {
  let nama_penilaian = ''
  if (localStorage.getItem('respo')) {
    const respoValues = await JSON.parse(localStorage.getItem('respo'))
    // console.log('ini respo', respoValues)
    values.m_comp_id = respoValues.m_comp_id
    values.m_subcomp_id = respoValues.m_subcomp_id
    values.m_branch_id = respoValues.m_branch_id
  }
  if (!isRead) {
    try {
      const url = `${store.server.url_backend}/operation/m_kary/${kary}`
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
      }).then(res => res.json()).then(json => json.data)

      console.log('ini hasil data',hasilData)
      // initialValues.t_assessment_kary_d_level =
      //   Array.isArray(initialValues.t_assessment_kary_d_level)
      //     ? initialValues.t_assessment_kary_d_level.map(item => item['m_level_posisi.level_name'])
      //     : []

      // values.nama = hasilData?.nama_lengkap || ''
      // values.jabatan = hasilData?.jabatan || ''
      // values.penilaian = hasilData?.nama_lengkap || ''
      // nama_penilaian = hasilData?.nama_lengkap || ''
    } catch (err) {
      console.error(err)
    }
  }


  if (isRead && currentMenu?.can_read) {
    try {
      const editedId = route.params.id;
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${route.params.id}`;
      isRequesting.value = true;

      const params = {
        transform: false
      };
      const fixedParams = new URLSearchParams(params);

      const res = await fetch(dataURL + '?' + fixedParams, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      });

      if (!res.ok) throw new Error("Failed when trying to read data");

      const resultJson = await res.json();
      initialValues = resultJson.data;
      console.log('init coy', initialValues)

      detailArr.value = initialValues.t_assessment_kary_d.map(item => ({ ...item }));
      selectedSeq.value = detailArr.value.map(item => {
        console.log('item', item)
        const selectedSub = item.t_assessment_kary_sub_d.reverse().find(sub => sub.is_selected)
        return selectedSub ? selectedSub.nilai : null
      })
      console.log('ini id', initialValues['m_kary.id'])
      // initialValues.nama = initialValues['m_kary.nama_lengkap']
      // if (detailArr.value.length === 0 && Array.isArray(initialValues.t_assessment_kary_d)) {
      //   detailArr.value = initialValues.t_assessment_kary_d.sort((a, b) => a.id - b.id).map(item => ({
      //     ...item
      //   }));
      //   selectedSeq.value = detailArr.value.map(item => {
      //     const selectedSub = item.t_assessment_kary_sub_d.find(sub => sub.is_selected)
      //     return selectedSub ? selectedSub.nilai : null
      //   })
      // }

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
    // Assign ke values semua properti initialValues
    for (const key in initialValues) {
      values[key] = initialValues[key];
    }

    try {
      const url = `${store.server.url_backend}/operation/m_kary/${initialValues['m_kary.id']}`
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
      }).then(res => res.json()).then(json => json.data)
      console.log('hasilData', hasilData)

      values.nama = hasilData?.nama_lengkap || ''
      values.penilaian = hasilData?.nama_lengkap || '' 
      values.jabatan = hasilData?.jabatan || ''
      // values.penilaian = nama_penilaian || ''
    } catch (err) {
      console.error(err)
    }
  }
});



// Table Detail
const detailArr = ref([])

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if (route.query.view_gaji_final) {
    router.replace('/t_info_gaji')
  } else {
    router.replace('/' + modulPath)
  }
  return
}


async function onSave() {
  try {
    isRequesting.value = true;

    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value);
    const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : '/' + route.params.id}`;

    values.t_assessment_kary_d = detailArr.value;
    // values.t_assessment_kary_d_level =
    //   values.t_assessment_kary_d_level.map(id => ({
    //     t_assessment_kary_id: 1,
    //     m_level_posisi_id: id,
    //     creator_id: store.user?.data?.id
    //   }))

    const payload = {
      ...values,
      status: 'DRAFT',
    };

    const res = await fetch(dataURL, {
      method: isCreating ? 'POST' : 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(payload)
    });

    const responseJson = await res.json();

    if (!res.ok) {
      formErrors.value = responseJson.errors || {};
      const errorMessage = responseJson.message || Object.values(formErrors.value)[0] || 'Oops, terjadi kesalahan.';
      throw errorMessage;
    }

    router.replace(`/${modulPath}?reload=${Date.now()}`);
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'warning',
      text: err
    });
  } finally {
    isRequesting.value = false;
  }
}


//  @else----------------------- LANDING

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
    landing.api.params.where = `this.status='DRAFT'`
  } else {
    landing.api.params.where = `this.status='POSTED'`
  }

  apiTable.value.reload()
}

const activeBtn = ref()
const infoOutstanding = ref(null)

function openOutstanding() {
  infoOutstanding.value?.onEnter()
}
// tst


let data = reactive({})

onBeforeMount(async () => {
  if(localStorage.getItem('respo')){
    const respoValues = JSON.parse(localStorage.getItem('respo'));
    data.respo_id = respoValues.id;
    data.m_subcomp_id = respoValues.m_subcomp_id;
    data.m_branch_id = respoValues.m_branch_id;
    landing.api.params.m_subcomp_id = respoValues.m_subcomp_id;
    landing.api.params.m_branch_id = respoValues.m_branch_id;
    console.log('respoValues', respoValues)
    console.log('data', data)
  }
  if(data.respo_id){
    const params = URLSearchParams({
      path: route.path,
      respo_id: data.respo_id
    })

    const endpoint = `${store.server.url_backend}/operation/m_general/access?${params.toString()}`;
    console.log(endpoint)

    try{
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          Authorization: `${store.user.token_type} ${store.user.token}`
        }
      })

      const result = await response.json()
      console.log('result', result)
      data.can_read = result.can_read
      data.can_create = result.can_create
      data.can_update = result.can_update
      data.can_delete = result.can_delete
      data.rows = result.data
      
    }catch(err){
      console.log(err);
    }
  }

})

const landing = reactive({
  actions: [
    {
      icon: 'trash',
      class: 'bg-red-600 text-light-100',
      title: "Hapus",
      show: (row) => row['status'] == 'DRAFT' && data?.can_delete,
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
              const dataURL = `${store.server.url_backend}/operation/${endpointApi}/${row.id}`
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
      show: (row) => (data?.can_read),
      click(row) {
        router.push(`${route.path}/${row.id}?` + tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) => row['status'] == 'DRAFT' && data?.can_update,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&isKaryId=${row.m_kary_id}&` + tsId)
      }
    },
    {
      icon: 'location-arrow',
      title: "Post Data",
      class: 'bg-rose-700 rounded-lg text-white',
      show: (row) => row['status'] == 'DRAFT' && data?.can_update,
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
      icon: 'copy',
      title: "Copy",
      show: (row) => row['status'] == 'DRAFT' && data?.can_create,
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&` + tsId)
      }
    }
  ],
  api: {
    // url: `${store.server.url_backend}/operation/${endpointApi}`,
    url: currentMenu?.can_read
         ? `${store.server.url_backend}/operation/${endpointApi}` 
         : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      simplest: true,
      scopes:'respo,atasan',
      searchfield: 'this.nama, m_kary.nama_lengkap',
      m_subcomp_id: data.m_subcomp_id,
      m_branch_id: data.m_branch_id,
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
      const color = value === 'DRAFT' ? '#6b7280' : '#FFC107'; // Hijau & Abu Tailwind
      return `<span style="color: ${color}; font-weight: bold;">${value}</span>`;
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