import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watchEffect, onActivated , computed } from 'vue'

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
const endpointApi = '/m_divisi'
onBeforeMount(()=>{
  document.title = 'Master Divisi'
})

//  @if( $id )------------------- JS CONTENT ! PENTING JANGAN DIHAPUS


// FIELD X With DropDown
const isDropdownOpen = ref(false) 
const optionsNama = ref([]) 


// HOT KEY
onMounted(()=>{
  window.addEventListener('keydown', handleKeyDown);
})
onBeforeUnmount(()=>{
  window.removeEventListener('keydown', handleKeyDown);
})

const handleKeyDown = (event) => {

  if (event?.ctrlKey && event?.key === 's') {
    event.preventDefault(); // Prevent the default behavior (e.g., saving the page)
    onSave();
  }
}

let initialValues = {}
const changedValues = []

let values = reactive({
})

// DEFAULT VALUE BEFORE MOUNT --UBAH DISINI
const defaultValues = ()=>{
  values.is_active = 1
  values.is_parent = false
  values.level='0'
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
        if(isRead && currentMenu?.can_read){
          for (const key in initialValues) {
            values[key] = initialValues[key]
          }
        }else{
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
const addDetail = () => {
  const tempItem = {
  }
  detailArr.value = [...detailArr.value, tempItem]
}
const onDetailAdd = (e) => {
  e.forEach(row=>{
    if(row.uid){
      delete row.uid
    }
    detailArr.value.push(row)
  })
}
const removeDetail = (index) => {
  detailArr.value.splice(index,1)
  // detailArr.value = detailArr.value.filter((e) => e.__id != index.__id)
}
// End Table Detail

const nilaiAwal = ref()


function changeParent (){
  if(isRead){
    if(values.is_parent === false){
      values.parent_id = null
      values.nomorParent =null
      values.nomor = nilaiAwal.value
      values.tempNomor = nilaiAwal.value
    }else{
      // if(values.nomor !== initialValues.nomor){
      //   values.nomor = initialValues.nomor?.replace(initialValues['parent.nomor'],"")
      //   values.tempNomor = initialValues.nomor?.replace(initialValues['parent.nomor'],"")
      // }
    }
  }else{
    values.level = 0
    values.nomorParent = null
    delete values.parent_id
  }
}

onBeforeMount(async () => {
  onReset()
  if (isRead) {
    //  READ DATA
    try {
      const editedId = route.params.id
      console.log('test',editedId)
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
      // initialValues.is_active=initialValues.is_active?1:0
      // console.log(initialValues.parent_id)
      initialValues.is_parent = initialValues.parent_id?true:false
      if(initialValues.parent_id){
        nilaiAwal.value = initialValues.nomor
        initialValues.nomor_awal = initialValues.nomor
        initialValues.nomor = initialValues.nomor?.replace(initialValues['parent.nomor'],"")
      }
      if(actionText.value?.toLowerCase() === 'copy' && initialValues.uid){
        delete initialValues.uid
      }
      initialValues.tempNomor = initialValues.nomor 
      if(initialValues['parent.nomor']){
        initialValues.nomorParent = initialValues['parent.nomor']
      }
      // Menambahkan Data Ke Array
      // initialValues.table_det?.forEach((items)=>{  
        // if(actionText.value?.toLowerCase() === 'copy' && items.uid){
        //   delete items.uid
        // }    
        // items.is_active = items.is_active ? 1 : 0
      //   detailArr.value = [items, ...detailArr.value]
      // })
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
    if(values.tempNomor){
      console.log(parseInt(values.tempNomor))
      console.log(typeof values.tempNomor)
      values.nomor = String(values.nomorParent ?? 0) + '-' + String(values.tempNomor);
    }
    try {

      // Inti onSave
      const isCreating = ['Create','Copy','Tambah'].includes(actionText.value);
      const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`;
      isRequesting.value = true;
      values.level = "" + values.level
      // values.is_parent = values.is_parent ? 1 : 0
      // values.is_oci = values.is_oci ? 1 : 0
      const res = await fetch(dataURL, {
        method: isCreating ? 'POST' : 'PUT',
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        body: JSON.stringify({
          ...values,
          is_parent: values.is_parent ? 1 : 0,
          is_active: values.is_active ? 1 : 0,
          name_old : '-'
          })
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

//  @else----------------------- LANDING
const activeBtn = ref()

function filterShowData(params,noBtn){
  if(activeBtn.value === noBtn){
    activeBtn.value = null
  }else{
    activeBtn.value = noBtn
  }
  
  if(activeBtn.value == null){
    // clear params filter
    landing.api.params.where = null
  }else if(params){
    landing.api.params.where = `this.is_active=true`
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
      show: (row) => (currentMenu?.can_delete),
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
      show: (row) => (currentMenu?.can_read),
      // show: (row) => (currentMenu?.can_read)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?`+tsId)
      }
    },
    {
      icon: 'edit',
      title: "Edit",
      class: 'bg-blue-600 text-light-100',
      show: (row) =>
        currentMenu?.can_update === true ||
        (row.status?.toUpperCase() === 'DRAFT' ||
          row.status?.toUpperCase() === 'REVISED'),
      // show: (row) => (currentMenu?.can_update)||store.user.data.username==='developer',
      click(row) {
        router.push(`${route.path}/${row.id}?action=Edit&`+tsId)
      }
    },
    {
      icon: 'copy',
      title: "Copy",
      class: 'bg-gray-600 text-light-100',
      show: (row) => currentMenu?.can_create === true,
      click(row) {
        router.push(`${route.path}/${row.id}?action=Copy&`+tsId)
      }
    }
  ],
  api: {
    // url: `${store.server.url_backend}/operation/${endpointApi}`,
    url: currentMenu?.can_read 
         ? `${store.server.url_backend}/operation${endpointApi}` 
         : '',
    headers: {
      'Content-Type': 'Application/json',
      authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      paginate: 25,
      simplest: true,
      order_by: "nomor",
		  // order_type: "ASC",
      searchfield: 'this.id, kategori.value1, this.name, this.nomor, report_group, this.is_active'
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
    field: 'nomor',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,wrapText:true,
    flex:1,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'm_branch.name',
    headerName: 'Cabang',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText:true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'name.value',
    headerName: 'Nama',
    filter: true,
    sortable: true,
    flex:1,
    filter: 'ColFilter',
    resizable: true,
    wrapText:true,
    cellClass: [ 'border-r', '!border-gray-200', 'justify-start']
  },
  {
    field: 'is_active',
    headerName:'Status',
    filter: true,
    filter: 'ColFilter',
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
  // {
  //   field:'is_active',
  //   filter: 'ColFilter',
  //   sortable: false,
  //   width: 110,
  //   filterParams:{
  //     options: {
  //     'Active': true,
  //     'Inactive': false
  //   },
  //   // Fungsi untuk mengonversi nilai yang dipilih dari filter menjadi nilai boolean
  //   valueGetter: (params) => params.value === 'Active' ? true : false
  //   },
  //   // valueGetter: (p) => p.node.data['is_active']===true ? 'Active':'Inactive',
  //   cellRenderer: ({ value }) => {
  //     return value === true
  //       ? `<span class="text-green-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Active</span>`
  //       : `<span class="text-gray-500 rounded-md text-xs font-medium px-4 py-1 inline-block capitalize">Inactive</span>`
  //   },    
  // },  
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
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))