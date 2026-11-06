import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated } from 'vue'

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
const tsId = `ts=`+(Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = 'm_respo'
onBeforeMount(async ()=>{
  document.title = 'Akses Responsibility'

 
})

// const clickItem = async (id) =>{
//   const data = {
//     m_respo_id: id
//   };
//   const dataURL = `${store.server.url_backend}/operation/${endpointApi}/change`
//   isRequesting.value = true;
//   const res = await fetch(dataURL, {
//     method: 'POST',
//     headers: {
//       'Content-Type': 'Application/json',
//       Authorization: `${store.user.token_type} ${store.user.token}`
//     },
//     body: JSON.stringify(data)
//   });
//   const responseData = await res.json();
//   const dataObject = responseData.data;
//   if(localStorage.getItem('respo')){
//    swal.fire({
//      icon: 'warning',
//      text: 'Ubah Akses Respo?',
//      iconColor: '#1469AE',
//      confirmButtonColor: '#1469AE',

//      showDenyButton: true
//    }).then(async (res) => {
//      if (res.isConfirmed) {  
//       localStorage.setItem('respo', JSON.stringify(responseData.data));
//       isRequesting.value = false
//       window.location.reload();
//      }else{    
//       isRequesting.value = false
//      }
//    } 
//   )}else{
//     localStorage.setItem('respo', JSON.stringify(responseData.data));
//     isRequesting.value = false
//     window.location.reload();
//   }
// }

const clickItem = async (id) => {
  try {
    const data = {
      m_respo_id: id
    };

    const dataURL = `${store.server.url_backend}/operation/${endpointApi}/change`;

    // ⛔ jangan set loading di sini
    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(data)
    });

    const responseData = await res.json();

    // Kalau sudah ada respo sebelumnya → minta konfirmasi
    if (localStorage.getItem('respo')) {
      const confirm = await swal.fire({
        icon: 'warning',
        text: 'Ubah Akses Respo?',
        iconColor: '#1469AE',
        confirmButtonColor: '#1469AE',
        showDenyButton: true
      });

      if (!confirm.isConfirmed) {
        return; // ❌ batal → tidak lanjut
      }
    }

    // ✅ baru set loading setelah user confirm
    isRequesting.value = true;

    // simpan data
    localStorage.setItem('respo', JSON.stringify(responseData.data));

    // reload halaman
    window.location.reload();

  } catch (err) {
    isRequesting.value = false;
    swal.fire({
      icon: 'error',
      text: err?.message || 'Terjadi kesalahan'
    });
  }
};


// HOT KEY
onMounted(()=>{
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(()=>{
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {
  console.log(event)
  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
let dataRespo = ref([])
const changedValues = []

let values = reactive({})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = ()=>{
  values.is_active = 1
}

const onReset = async (alert = false) => {
  let next = false
  if(alert){
    swal.fire({
      icon: 'warning',
      text: 'Anda yakin akan mereset data ini?',
      showDenyButton: true
    }).then((res) => {
      if (res.isConfirmed) {
        if(isRead){
          detailArr.value=[]
          initialValues.m_respo_d?.forEach((items)=>{
            if(actionText.value?.toLowerCase() === 'copy' && items.uid){
              delete items.uid
            }
            items.name = items['m_role.name']
            items.is_active = items.is_active ? 1 : 0
            detailArr.value = [items, ...detailArr.value]
          })
          for (const key in initialValues) {
            values[key] = initialValues[key]
          }
        }else{
          detailArr.value=[]
          for (const key in values) {
            delete values[key]
          }
          defaultValues()
        }
      }
    })
  }
  
  setTimeout(()=>{
    defaultValues() 
  }, 100)
}


// Table Detail
const detailArr = ref([])
const onDetailAdd = (e) => {
  e.forEach(row=>{
    delete row.uid
    row.m_role_id = row.id
    row.is_active = 1
    detailArr.value.push(row)
  })
}
const removeDetail = (index) => {
  detailArr.value.splice(index,1)
  // detailArr.value = detailArr.value.filter((e) => e.__id != index.__id)
}
// End Table Detail

onBeforeMount(async () => {
   // LOAD DEFAULT 
  const dataURL = `${store.server.url_backend}/operation/${endpointApi}/active_respo`
  isRequesting.value = true;
  const res = await fetch(dataURL, {
    method: 'GET',
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
  });
  const responseData = await res.json();
  if(responseData.data.id) {
    localStorage.setItem('respo', JSON.stringify(responseData.data));
    console.log('res',responseData)
  }
  if(localStorage.getItem('respo')){
    const respoValues =  await JSON.parse(localStorage.getItem('respo'))
    values.id_active = respoValues.id
  }
  //  READ DATA
  try {
    const dataURL = `${store.server.url_backend}/operation/m_respo`
    isRequesting.value = true

    const params = { join: true, transform: true, scopes: 'byUserRespo' }
    const fixedParams = new URLSearchParams(params)
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })
    if (!res.ok) throw new Error("Failed when trying to read data")
    const resultJson = await res.json()
    dataRespo.value = resultJson.data

    isRequesting.value = false
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
 

  for (const key in initialValues) {
    values[key] = initialValues[key]
  }
})

function onBack() {
  if (route.query.view_gaji) {
    router.replace('/t_info_gaji')
  } else if(route.query.view_gaji_final){
    router.replace('/t_info_gaji')
  }else{
    router.replace('/' + modulPath)
  }
  return
}


async function onSave() {
  //values.tags = JSON.stringify(values.tags)
    try {
      if(detailArr.value.length === 0){
          throw ("Detail Tidak Boleh Kosong")
        }
      const isCreating = ['Create','Copy','Tambah'].includes(actionText.value);
      const dataURL = `${store.server.url_backend}/operation/${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;
      isRequesting.value = true;
      values.is_active = values.is_active ? 1 : 0
      values.m_respo_d = detailArr.value
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
          throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
        } else {
          throw ("Oops, sesuatu yang salah terjadi. Coba kembali nanti.");
        }
      }
      router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())));
    } catch (err) {
      isBadForm.value = true;
      swal.fire({
        icon: 'warning',
        text: err
      });
    }
    isRequesting.value = false;
    }

const activeBtn = ref()

function filterShowData(params,noBtn){
  if(activeBtn.value === noBtn){
    activeBtn.value = null
  }else{
    activeBtn.value = noBtn
  }
  if(params){
    landing.api.params.where = `this.is_active=true`
  }else if(activeBtn.value == null){
    // clear params filter
    landing.api.params.where = null
  }else{
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
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?`+tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&`+tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&`+tsId)
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
      searchfield: 'this.id, m_comp.name, m_subcomp.name, this.name, this.is_active'
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
    field: 'm_comp.name',
    headerName: 'Business Unit',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText:true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_subcomp.name',
    headerName: 'Sub Business Unit',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText:true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'name',
    headerName: 'Nama',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,wrapText:true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'is_active',
    headerName: 'Status',
    filter: true,
    // resizable: true,
    // valueGetter: (p) => p.node.data['status'].toLowerCase()==='active'? 'Aktif':'Tidak Aktif',
    sortable: true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-center'],
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
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))