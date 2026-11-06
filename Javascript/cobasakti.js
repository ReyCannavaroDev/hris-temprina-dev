import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

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
const endpointApi = '/t_perhitungan_gaji'
onBeforeMount(() => {
  document.title = 'Transaksi Salary Borongan'
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

const shiftList = ref([])
const karyawanList = ref([])


const values = reactive({
  is_active: true,
  jml_kary: 0,
  selectedShiftId: null,
  selectedShiftData: null
})

const addShift = () => {
  const selected = values.selectedShiftData
  if (!selected || shiftList.value.some(s => s.id === selected.id)) return;

  shiftList.value.push({
    id: selected.id,
    nama: selected.nama || `Shift ${shiftList.value.length + 1}`,
    jam_mulai: selected.jam_mulai || '08:00',
    jam_selesai: selected.jam_selesai || '16:00'
  });

  values.selectedShiftId = null;
  values.selectedShiftData = null;
}

const removeShift = (index) => {
  shiftList.value.splice(index, 1)
}

const openSubDetail = (shift) => {
  console.log('Buka detail:', shift)
}



const currentYear = ref(new Date().getFullYear());
const currentMonth = ref('');
const currentDate = ref(new Date().getDate())
const daysOfWeek = ['Mng', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const calendarRows = ref([]);
const dateNow = ref()
var months = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

const initializeCalendar = async () => {
  const currentDate = new Date();
  currentMonth.value = currentDate.toLocaleString('default', { month: 'long' });
  values.month = `${currentYear.value}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}`
  const daysInMonth = new Date(currentYear.value, currentDate.getMonth() + 1, 0).getDate();
  const firstDay = new Date(currentYear.value, currentDate.getMonth(), 1).getDay();
  let tempCalendarRows = [];
  let dayCounter = 1;

  for (let i = 0; i < 6; i++) {
    let row = [];
    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(currentYear.value, currentDate.getMonth(), dayCounter);
        if (isToday(date)) {
          dateNow.value = formatDate(date)
          values.date = formatDate(date)
          await getlabelDate(values.date)
        }
        row.push({ day: dayCounter, date: date, isToday: isToday(date) });
        dayCounter++;
      }
    }
    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;
};


const changeCalendar = async () => {
  resetAll()
  let tempBulan = values.month?.split('-')
  const currentDate = new Date(tempBulan[0], tempBulan[1] - 1);
  const daysInMonth = new Date(currentYear.value, currentDate.getMonth() + 1, 0).getDate();
  const firstDay = new Date(currentYear.value, currentDate.getMonth(), 1).getDay();
  let tempCalendarRows = [];
  let dayCounter = 1;
  console.log((currentDate.getMonth() + 1).toString().padStart(2, '0'), dateNow.value?.split('-')[1])

  for (let i = 0; i < 6; i++) {
    let row = [];
    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(currentYear.value, currentDate.getMonth(), dayCounter);
        if (isToday(date)) {
          dateNow.value = formatDate(date)
        }
        if ((currentDate.getMonth() + 1).toString().padStart(2, '0') == dateNow.value?.split('-')[1] ? isToday(date) : dayCounter === 1) {
          values.date = formatDate(date)
          await getlabelDate(values.date)
        }
        row.push({ day: dayCounter, date: date, isToday: (currentDate.getMonth() + 1).toString().padStart(2, '0') == dateNow.value?.split('-')[1] ? isToday(date) : dayCounter === 1 });
        dayCounter++;
      }
    }
    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;
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
  if (date) {
    calendarRows.value.forEach(row => {
      row.forEach(cell => {
        cell.isToday = cell.date && cell.date.toDateString() === date.toDateString(); // Mengatur isToday berdasarkan tanggal yang diklik
      });
    });
    values.date = formatDate(date)
    await getlabelDate(values.date)
    console.log('Tanggal:', formatDate(date));
  }
};

const isDateSucs = ref(false)
const labelArr = ref([])

async function getlabelDate(tanggal) {
  try {
    detailArr.value = []
    detailArrKary.value = []
    delete values.label
    delete values.m_group_kary_id
    const dataURL = `${store.server.url_backend}/operation/t_perhitungan_gaji`
    isRequesting.value = true
    const params = { join: false, transform: false, date: tanggal }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })
    if (!res.ok) throw new Error("Failed when trying to read data")
    const resultJson = await res.json()
    // initialValues = resultJson.data
    console.log(resultJson)
    if (resultJson.length > 1) {
      isDateSucs.value = true
      labelArr.value = resultJson
    } else if (resultJson.length == 1) {
      values.label = resultJson[0]?.label
      isDateSucs.value = true
      labelArr.value = resultJson
      console.log(values.label)
      await getDate(resultJson[0]?.label)
    } else {
      isDateSucs.value = false
    }
  } catch (err) {
    isDateSucs.value = false
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err,
    })
  }
  isRequesting.value = false
  tableKey.value++
  tableKeyKary.value++
}

function resetAll() {
  isDateSucs.value = false
  detailArr.value = []
  detailArrKary.value = []
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


async function onSave() {
  //values.tags = JSON.stringify(values.tags)
  try {
    if (values.date) {
      const [year, month, day] = values.date.split('-');
      values.date = `${year}-${month}-${day.padStart(2, '0')}`;
    }

    const dataURL = `${store.server.url_backend}/operation/${endpointApi}/post`
    values.is_active = values.is_active ? 1 : 0
    // values.t_salary_borongan_det = detailArr.value
    // values.t_salary_borongan_det_kary = detailArrKary.value
    isRequesting.value = true
    const res = await fetch(dataURL, {
      method: 'POST',
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
        throw (responseJson.errors?.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    const responseJson = await res.json()
    swal.fire({
      icon: 'success',
      text: responseJson.message || "Failed when trying to post data"
    })
  } catch (err) {
    isBadForm.value = true
    swal.fire({
      icon: 'error',
      text: err
    })
  }
  isRequesting.value = false
}

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))