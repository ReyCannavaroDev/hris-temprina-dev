import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, computed, watchEffect, onActivated } from 'vue'

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
const tsId = `ts=` + (Date.parse(new Date()))
const dateArr = ref([1, 2, 3, 4, 5, 6, 7, 8])
const dateActive = ref(1)
const showItemDate = ref(false)
const activeDate = (index) => {
  dateActive.value = index
}
const activeTabIndex = ref(0)

// ------------------------------ PERSIAPAN
const endpointApi = '/t_jadwal_kerja'
onBeforeMount(() => {
  document.title = 'Transaksi Jadwal Kerja'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []

const selectedMonth = ref(dayjs().format('YYYY-MM'))
const selectedDate = ref(null)
const selectedShiftId = ref(null)
const employeeList = ref([])
const activeShift = ref(null)



const shiftApi = {
  url: `${store.server.url_backend}/operation/m_jam_kerja`,
  headers: { Authorization: `${store.user.token_type} ${store.user.token}` },
  params: {}
}
const divisiApi = {
  url: `${store.server.url_backend}/operation/m_divisi`,
  headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
}
const deptApi = {
  url: `${store.server.url_backend}/operation/m_dept`,
  headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
}

function onDateSelected(date) {
  selectedDate.value = date
}

function setActiveShift(item) {
  activeShift.value = item
}
function openAddEmployee() {
}
function removeEmployee(index) {
  employeeList.value.splice(index, 1)
}
function updateMonth(val) {
  selectedMonth.value = val
}

let _id = 0

//  @else----------------------- LANDING
let isiLabel = ref(null)
const selectedShift = ref(null)
const shiftList = ref([])
const karyawanList = computed({
  get: () => selectedShift.value?.t_jadwal_kerja_det ?? [],
  set: (val) => {
    if (selectedShift.value) {
      selectedShift.value.t_jadwal_kerja_det = val
    }
  }
})

const hapusKaryawan = (index) => {
  karyawanList.value.splice(index, 1)
}

const values = reactive({
  status: 'DRAFT',
  jml_kary: 0,
  selectedShiftId: null,
  selectedShiftData: null
})



const tambahKaryawan = (selectedKaryawanList) => {
  if (!selectedShift.value) return

  karyawanList.value ||= []

  selectedKaryawanList.forEach(kary => {
    const exists = karyawanList.value.some(d => d.m_kary_id === kary.id)
    if (!exists) {
      karyawanList.value.push({
        m_kary_id: kary.id,
        m_dir_id: kary.m_dir_id || null,
        m_divisi_id: kary.m_divisi_id || null,
        divisi: kary.divisi || '',
        dept: kary.dept || '',
        nik: kary.nik || '',
        nama: kary.nama_lengkap || ''
      })
    }
  })
}


const removeShift = (index) => {
  shiftList.value.splice(index, 1)
}

const openSubDetail = (shift) => {
  selectedShift.value = shift
  karyawanList.value = shift.t_jadwal_kerja_det || []
  // console.log('ini shift',selectedShift)
}



const currentYear = ref(new Date().getFullYear());
const currentMonth = ref('');
const currentDate = ref(new Date().getDate())
const daysOfWeek = ['Mng', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const daysOfWeekDynamic = ref([...daysOfWeek]);
const calendarRows = ref([]);
const dateNow = ref()
var months = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

const initializeCalendar = async () => {
  console.log('ini ku klik')

  const currentDateObj = new Date();
  const year = currentDateObj.getFullYear();
  const month = currentDateObj.getMonth();
  const todayDate = currentDateObj.getDate();

  currentMonth.value = currentDateObj.toLocaleString('default', { month: 'long' });
  values.month = `${year}-${(month + 1).toString().padStart(2, '0')}`;

  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const firstDay = new Date(year, month, 1).getDay();

  daysOfWeekDynamic.value = [
    ...daysOfWeek.slice(firstDay),
    ...daysOfWeek.slice(0, firstDay)
  ];

  let tempCalendarRows = [];
  let dayCounter = 1;

  for (let i = 0; i < 6; i++) {
    let row = [];
    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '', dayLabel: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(year, month, dayCounter);
        const formatted = formatDate(date);
        const isCurrentDay = dayCounter === todayDate;

        if (isCurrentDay) {
          dateNow.value = formatted;
          values.start_date = formatted;
          await getlabelDate(formatted);

          const hari = daysOfWeek[date.getDay()];
          // console.log('Tanggal hari ini:', formatted);
          // console.log('Hari hari ini:', hari);
        }

        row.push({
          day: dayCounter,
          date,
          isToday: isCurrentDay,
          dayLabel: daysOfWeek[date.getDay()]
        });

        dayCounter++;
      }
    }

    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;
};

const changeCalendar = async () => {
  resetAll();
  console.log('ini ku klik')

  let [year, month] = values.month?.split('-') || [];
  const selectedDate = new Date(year, month - 1);
  const daysInMonth = new Date(year, month, 0).getDate();
  const firstDay = new Date(year, month - 1, 1).getDay();

  let tempCalendarRows = [];
  let dayCounter = 1;

  const isSameMonthAsToday = month === formatDate(new Date()).split('-')[1];

  for (let i = 0; i < 6; i++) {
    let row = [];

    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '', dayLabel: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(year, month - 1, dayCounter);
        const isTodayDate = isToday(date);
        const isSelected = isSameMonthAsToday ? isTodayDate : dayCounter === 1;

        if (isSelected) {
          values.start_date = formatDate(date);
          await getlabelDate(values.start_date);
        }

        row.push({
          day: dayCounter,
          date,
          isToday: isSelected,
          dayLabel: daysOfWeek[date.getDay()]
        });

        dayCounter++;
      }
    }

    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;

  // console.log('Data kalender (ganti bulan):', calendarRows.value);
};

const addShift = () => {
  const selected = values.selectedShiftData;
  console.log('selectedShiftData:', selected);
  if (!selected || shiftList.value.some(s => s.id === selected.id)) return;

  const dateObj = new Date(values.start_date);
  const dayName = daysOfWeek[dateObj.getDay()];
  const dayNum = dateObj.getDay();

  shiftList.value.push({
    m_jam_kerja_id: selected.id,
    nama: selected.desc,
    waktu_mulai: selected.waktu_mulai,
    waktu_akhir: selected.waktu_akhir,
    tanggal: values.start_date,
    day: dayName,
    day_num: dayNum
  });

  values.selectedShiftId = null;
  values.selectedShiftData = null;
};

const isToday = (date) => {
  const today = new Date();
  return date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate();
};

const formatDate = (date) => {
  const day = date.getDate();
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${year}-${month}-${day}`;
};

const handleDateClick = async (date) => {
  resetAll()
  if (!date) return;

  calendarRows.value.forEach(row => {
    row.forEach(cell => {
      cell.isToday = cell.date && cell.date.toDateString() === date.toDateString();
    });
  });

  values.start_date = formatDate(date);
  await getlabelDate(values.start_date);

  const hari = daysOfWeek[date.getDay()];
  // console.log('Tanggal:', values.start_date);
  // console.log('Hari:', hari);
};



const isDateSucs = ref(false)
const labelArr = ref([])

async function getlabelDate(tanggal) {
  try {
    detailArr.value = [];
    detailArrKary.value = [];
    delete values.label;
    delete values.m_group_kary_id;

    const dataURL = `${store.server.url_backend}/operation/t_jadwal_kerja`;
    isRequesting.value = true;

    let formattedDate = values.start_date;
    if (formattedDate.includes('/')) {
      const [day, month, year] = formattedDate.split('/');
      formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }

    const params = {
      join: false,
      transform: false,
      scopes: 'DateRange',
      date_from: formattedDate,
      date_to: formattedDate
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
    const dataArr = resultJson.data || [];

    labelArr.value = dataArr
      .filter(item => item.nomor && item.id)
      .map(item => ({
        id: item.id,
        keterangan: item.keterangan
      }));

  } catch (err) {
    isDateSucs.value = false;
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err.message || err,
    });
  }

  isRequesting.value = false;
  tableKey.value++;
  tableKeyKary.value++;
}


async function getDate(idParam = null) {
  if (!idParam) {
    console.warn('ID tidak valid, tidak bisa ambil data');
    return;
  }

  let initialValues;
  try {
    detailArr.value = [];
    detailArrKary.value = [];
    delete values.label;
    delete values.m_group_kary_id;

    const dataURL = `${store.server.url_backend}/operation/t_jadwal_kerja/${idParam}`;
    isRequesting.value = true;

    const params = {
      join: true,
      transform: true,
      date: values.date
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
    isiLabel.value = resultJson.data
    initialValues = isiLabel.value;

    shiftList.value = (initialValues.t_jadwal_kerja_det_hari || []).map(item => {
      const karyawanList = (item.t_jadwal_kerja_det || []).map(k => {
        console.log('kuntul', k);
        return {
          m_kary_id: k.m_kary_id,
          nama: k['m_kary.nama_lengkap'] || '',
          divisi: k['m_divisi.name'],
          nik: k['m_kary.nik']
        };
      });


      console.log('Karyawan untuk shift:', item);

      return {
        m_jam_kerja_id: item.m_jam_kerja_id,
        day: item.day,
        day_num: item.day_num,
        nama: item['m_jam_kerja.desc'] || '',
        waktu_mulai: item.waktu_mulai || '',
        waktu_akhir: item.waktu_akhir || '',
        tipe_hari: item.tipe_hari || '',
        t_jadwal_kerja_det: karyawanList,
      };
    });
    if (shiftList.value.length > 0) {
      selectedShift.value = shiftList.value[0];
    }

    // initialValues.nomor = values.nomor
    // console.log('test', resultJson);
  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err,
    });
  }

  isRequesting.value = false;

  for (const key in initialValues) {
    values[key] = initialValues[key];
  }

  tableKey.value++;
  tableKeyKary.value++;
}

function resetAll() {
  values.id =''
  values.nomor = ''
  values.status = ''
  values.keterangan = ''
  values.m_dept_id = null
  values.m_divisi_id = null

  shiftList.value.length = 0;
  karyawanList.value.length = 0
  detailArr.value = []
  delete values.label
  delete values.m_group_kary_id
  delete values.pic_id
  tableKey.value++
  tableKeyKary.value++
}

onMounted(() => {
  initializeCalendar();
});


const formatCurrency = (text) => {
  if (!text) text = 0

  const formatter = new Intl.NumberFormat('id', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  })

  if (typeof text === 'string') {
    if (isNaN(parseFloat(text)) || isNaN(parseInt(text))) {
      return formatter.format(0)
    }

    if (text.includes(',') || text.includes('.')) {
      return formatter.format(parseFloat(text))
    }

    return formatter.format(parseInt(text))
  }

  return formatter.format(text)
}


const detailArr = ref([])

const tableKey = ref(0)
const tableKeyKary = ref(0)
const onDetailAdd = (e) => {
  e.forEach((row, i) => {
    detailArr.value = [...detailArr.value, row]
    // detailArr.value.push(row)
  })

  tableKey.value++
  tableKeyKary.value++
}

const detailArrKary = ref([])
const onRetotal = () => {
  if (detailArr.value) {
    values.total_pendapatan = detailArr.value?.reduce((acm, item) => {
      return parseFloat(acm ?? 0) + parseFloat(item.subtotal ?? 0);
    }, 0);

    values.jml_kary = detailArrKary.value.length ?? 0
    values.pend_kary = values.jml_kary !== 0 ? values.total_pendapatan / values.jml_kary : 0
  }
}
watchEffect(() => {
  onRetotal()
})

const removeDetail = (index) => {
  detailArr.value = detailArr.value.filter((e) => e.id != index)
  tableKey.value++
  tableKeyKary.value++
}
const removeDetailKary = (index) => {
  detailArrKary.value = detailArrKary.value.filter((e) => e.id != index)
  tableKey.value++
  tableKeyKary.value++
}

const onDetailAddKary = (e) => {
  e.forEach((row, i) => {
    detailArrKary.value = [...detailArrKary.value, row]
    // detailArrKary.value.push(row)
  })

  tableKey.value++
  tableKeyKary.value++
}

function hapusLabel(row) {
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
          throw (resultJson.message || "Gagal menghapus data")
        }

        apiTable.value?.reload?.()
        resetAll()
        labelArr.value = labelArr.value.filter(item => item.id !== row.id)

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



async function onSave() {
  try {
    if (!values.keterangan) {
      swal.fire({
        icon: 'warning',
        text: 'Harap Isi Deskripsi'
      });
    return;
    }

    if (
      values.start_date &&
      typeof values.start_date === 'string' &&
      values.start_date.includes('-')
    ) {
      const [year, month, dayRaw] = values.start_date.split('-');
      const day = dayRaw?.padStart(2, '0') || '01';
      values.start_date = `${year}-${month}-${day}`;
    }

    const isUpdate = !!values.id;
    const method = isUpdate ? 'PUT' : 'POST';
    const url = isUpdate
      ? `${store.server.url_backend}/operation${endpointApi}/${values.id}`
      : `${store.server.url_backend}/operation${endpointApi}`;

    values.status = 'DRAFT';
    values.tipe_jam_kerja_id = 7731;

    const dateObj = new Date(values.start_date);
    const daysOfWeek = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayName = daysOfWeek[dateObj.getDay()];
    const dayNum = dateObj.getDay();

    detailArr.value = detailArr.value.map(item => ({
      ...item,
      tanggal: values.start_date,
      day: dayName,
      day_num: dayNum
    }));

    values.t_jadwal_kerja_det_hari = detailArr.value;

    isRequesting.value = true;

    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
      body: JSON.stringify(values),
    });

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json();
        formErrors.value = responseJson.errors || {};
        throw (
          responseJson.errors?.length
            ? responseJson.errors[0]
            : responseJson.message || "Gagal menyimpan data"
        );
      } else {
        throw "Gagal menyimpan data";
      }
    }

    const responseJson = await res.json();
    swal.fire({
      icon: 'success',
      text: responseJson.message || "Data berhasil disimpan"
    }).then(() => {
      resetAll();
      getlabelDate();
    });

  } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err
    });
  }

  isRequesting.value = false;
}



//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))