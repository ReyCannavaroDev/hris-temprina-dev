<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
      </div>
    </div>
    <div v-show="currentMenu?.can_create">
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="">
    <!-- <template #header>
    </template> -->
  </TableApi>
</div>
@else

<!-- CONTENT -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Gaji</h1>
        <p class="text-gray-100">Master Gaji</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" :value="values.kode" :errorText="formErrors.kode?'failed':''"
        @input="v=>values.kode=v" :hints="formErrors.kode" :check="false" class="w-full !mt-3" label="Kode"
        placeholder="Tuliskan Kode" />
    </div>
    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.m_kary_id" @input="v=>{
              if(v){
                values.m_kary_id=v
              }else{
                values.m_kary_id=null
                values.m_posisi_id=null
              }
            }" @update:valueFull="obj => {
              $log(obj)
                if (obj) {
                  values.m_kary_id = obj.id; 
                  values.m_posisi_id = obj.m_posisi_id; 
                } else {
                  values.m_kary_id = null;
                }
              }" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id" valueField="id"
        displayField="nama_lengkap" :api="apiKary" placeholder="Pilih Karyawan" label="Karyawan" :check="false"
        :columns="[
            {
              headerName: 'No',
              valueGetter: (p) => p.node.rowIndex + 1,
              width: 60,
              sortable: false, 
              resizable: false, 
              filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              field: 'kode',
              headerName: 'Kode',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'nama_lengkap',
              headerName: 'Nama',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },    
          ]" />
    </div>
    <div>
      <FieldPopup :bind="{ disabled: !actionText, readonly:!actionText, clearable:false }" class="w-full !mt-3"
        :value="values.grading_id" @input="(v) => {
              values.grading_id = v;
              updateTunjanganKemahalan();
            }" :errorText="formErrors.grading_id?'failed':''" displayField="code" @update:valueFull="(v) => {
                        if (v && v.code) { 
                          if (values.kode?.split(' ').length > 1) {
                            values.kode = `${v.code} ${values.kode?.split(' ')[1]} ${values.kode?.split(' ')[2]}`;
                          } else {
                            values.kode = v.code;

                          }
                        }  updateWaifu(v)
          }" :hints="formErrors.grading_id" :api="{
                  url: `${store.server.url_backend}/operation/m_grade`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                      simplest:true,
                      searchfield: 'this.code, this.value , this.value_2 , this.value_3 , this.value_4',
                  }
            }" valueField="id" :check="false" label="Grading" placeholder="Pilih Grading" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
                {
    field: 'code',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
  {
    headerName: 'Min',
    field: 'min',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: params =>
      params.value != null && params.value !== 0
        ? 'text-green-500 font-bold border-r !border-gray-200'
        : 'text-black border-r !border-gray-200',
    valueFormatter: params =>
      params.value != null
        ? 'Rp. ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(params.value) + '.-'
        : 'Rp.0.-'
  },
  {
    headerName: 'Max',
    field: 'max',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: params =>
      params.value != null && params.value !== 0
        ? 'text-green-500 font-bold border-r !border-gray-200'
        : 'text-black border-r !border-gray-200',
    valueFormatter: params =>
      params.value != null
        ? 'Rp. ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(params.value) + '.-'
        : 'Rp.0.-'
  },
  {
    headerName: 'Score',
    field: 'score',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },

    {
    headerName: 'Keterangan Grade',
    field: 'keterangan',
    filter: true,
    sortable: true,
    filter: 'ColFilter',
    resizable: true,
    flex: 1,
    cellClass: ['border-r', '!border-gray-200']
  },
              ]" />
    </div>
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3" :value="values.m_zona_id"
        @input="(v) => {
              values.m_zona_id = v;
              updateTunjanganKemahalan(); 
            }" :errorText="formErrors.m_zona_id?'failed':''" :hints="formErrors.m_zona_id" displayField="nama"
        @update:valueFull="(objVal)=>{
              if(values.kode?.split(' ').length === 1){                
                values.kode+=` ${objVal.nama}`
              }else if(values.kode?.split(' ').length > 1){
                values.kode = `${values.kode?.split(' ')[0]} ${objVal.nama}`
              }
            }" :api="{
                  url: `${store.server.url_backend}/operation/m_zona`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    join:true, 
                    single: true,
                    selectfield: 'this.id,this.nama,this.kode'
                  }
            }" valueField="id" :check="false" label="Zona" placeholder="Pilih Zona" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true , clearable:false }" class="w-full !mt-3" :value="values.m_posisi_id"
        @input="v => values.m_posisi_id = v" :errorText="formErrors.m_posisi_id ? 'failed' : ''"
        :hints="formErrors.m_posisi_id" label="" placeholder="Pilih Jabatan" valueField="id" displayField="name" :api="{
                        url: `${store.server.url_backend}/operation/m_posisi`,
                        headers: {
                          'Content-Type': 'Application/json',
                          Authorization: `${store.user.token_type} ${store.user.token}`
                        },
                        params: {
                          simplest: true,
                          transform: false,
                          where: `this.is_active='true'`,
                          join: true,
                        }
                      }" :check="false" />
    </div>

    <!-- DATE START -->
    <div>
      <FieldX :bind="{ readonly: !actionText,disabled: !actionText }" type="date" class="w-full !mt-3"
        :value="values.start_date" label="Tanggal" placeholder="Pilih Tanggal"
        :errorText="formErrors.start_date?'failed':''" @input="v=>values.start_date=v" :hints="formErrors.start_date"
        :check="false" />
    </div>



    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.desc"
        :errorText="formErrors.desc?'failed':''" @input="v=>values.desc=v" type="textarea" :hints="formErrors.desc"
        label="Keterangan" placeholder="Tuliskan Keterangan" :check="false" />
    </div>

    <div>
      <FieldNumber :bind="{ readonly: !actionText }" :value="values.take_home_pay" @input="(v)=>values.take_home_pay=v"
        :errorText="formErrors.take_home_pay?'failed':''" :hints="formErrors.take_home_pay" :check="false"
        class="w-full !mt-3" label="Take Home Pay" placeholder="TAKE HOME PAY" />
    </div>
    <!-- status -->
    <div class="pl-3 flex flex-col justify-center !mt-3">
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">InActive</i>
        </div>
        <div class="flex-auto">
          <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.is_active === false}"
                role="switch"
                id="is_active_for_click"
                :disabled="!actionText"
                v-model="values.is_active"
                />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">Active</i>
        </div>
      </div>
    </div>

    <div class="pl-3 flex flex-col justify-center !mt-3">
      <label
    for="check_absence_for_click"
    class="mb-1 text-xs font-medium text-gray-700"
  >
    Check Absen
  </label>

      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">Tidak</i>
        </div>

        <div class="flex-auto">
          <input
        class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:bg-primary"
        type="checkbox"
        :class="{'after:bg-gray-500': values.check_absence === false}"
        role="switch"
        id="check_absence_for_click"
        :disabled="!actionText"
        v-model="values.check_absence"
      />
        </div>

        <div class="flex-auto">
          <i class="text-green-500">Ya</i>
        </div>
      </div>
    </div>


    <div class="pl-3 flex flex-col justify-center !mt-3">
      <label
    for="check_late_for_click"
    class="mb-1 text-xs font-medium text-gray-700"
  >
    Check Terlambat
  </label>

      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">Tidak</i>
        </div>

        <div class="flex-auto">
          <input
        class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:bg-primary"
        type="checkbox"
        :class="{'after:bg-gray-500': values.check_late === false}"
        role="switch"
        id="check_late_for_click"
        :disabled="!actionText"
        v-model="values.check_late"
      />
        </div>

        <div class="flex-auto">
          <i class="text-green-500">Ya</i>
        </div>
      </div>
    </div>


    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>



  <!-- FORM GAJI -->
  <div class="grid grid-cols-1 md:grid-cols-2 text-[14px] gap-x-[29px] gap-y-[26px] mx-4">
    <!-- Left Grid -->
    <div class="col-span-1">
      <!-- Gaji Pokok -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Gaji Pokok<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.gaji_pokok_periode || 'Bulanan'" @input="v=>values.gaji_pokok_periode=v"
            :errorText="formErrors.gaji_pokok_periode?'failed':''" :hints="formErrors.gaji_pokok_periode"
            valueField="id" displayField="key" :options="['Bulanan']" :check="false" label=""
            placeholder="Pilih Periode" />
        </div>
        <div class="flex flex-col flex-1 ">
          <FieldNumber :bind="{ readonly: true }" :value="values.gaji_pokok" @input="(v)=>values.gaji_pokok=v"
            :errorText="formErrors.gaji_pokok?'failed':''" :hints="formErrors.gaji_pokok" :check="false"
            class="col-span-12 !mt-5 w-full" label="" placeholder="0" />
        </div>
      </div>

      <!-- Tunjangan Produktifitas -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Tunjangan Produktifitas<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tunjangan_produktifitas_periode || 'Bulanan'"
            @input="v=>values.tunjangan_produktifitas_periode=v"
            :errorText="formErrors.tunjangan_produktifitas_periode?'failed':''"
            :hints="formErrors.tunjangan_produktifitas_periode" valueField="id" displayField="key"
            :options="['Bulanan']" :check="false" label="" placeholder="Pilih Periode" />
        </div>
        <div class="flex flex-col flex-1 ">
          <FieldNumber :bind="{ readonly: true }" :value="values.tunjangan_produktifitas"
            @input="(v)=>values.tunjangan_produktifitas=v" :errorText="formErrors.tunjangan_produktifitas?'failed':''"
            :hints="formErrors.tunjangan_produktifitas" :check="false" class="col-span-12 !mt-5 w-full" label=""
            placeholder="0" />
        </div>
      </div>

      <!-- Tunjangan Transport -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Tunjangan Transport<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tunjangan_transport_periode || 'Bulanan'" @input="v=>values.tunjangan_transport_periode=v"
            :errorText="formErrors.tunjangan_transport_periode?'failed':''"
            :hints="formErrors.tunjangan_transport_periode" valueField="id" displayField="key" :options="['Bulanan']"
            :check="false" label="" placeholder="Pilih Periode" />
        </div>
        <div class="flex flex-col flex-1 ">
          <FieldNumber :bind="{ readonly: true }" :value="values.tunjangan_transport"
            @input="(v)=>values.tunjangan_transport=v" :errorText="formErrors.tunjangan_transport?'failed':''"
            :hints="formErrors.tunjangan_transport" :check="false" class="col-span-12 !mt-5 w-full" label=""
            placeholder="0" />
        </div>
      </div>
    </div>

    <!-- Right Grid -->
    <div class="col-span-1">
      <!-- Tunjangan Jabatan -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Tunjangan Jabatan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tunjangan_posisi_periode || 'Bulanan'" @input="v=>values.tunjangan_posisi_periode=v"
            :errorText="formErrors.tunjangan_posisi_periode?'failed':''" :hints="formErrors.tunjangan_posisi_periode"
            valueField="id" displayField="key" :options="['Bulanan']" :check="false" label=""
            placeholder="Pilih Periode" />
        </div>

        <div class="flex flex-col flex-1">
          <div class="flex items-center gap-2">
            <FieldPopup v-if="!editJabatan" class="w-full py-2 mt-5" :bind="{ readonly: !actionText }"
              :value="values.tunjangan_posisi" @input="(v)=>values.tunjangan_posisi=v"
              :errorText="formErrors.tunjangan_posisi?'failed':''" :hints="formErrors.tunjangan_posisi"
              valueField="besaran" displayField="besaran" :api="{
                  url: `${store.server.url_backend}/operation/m_tunjangan_jabatan`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    //single:true,
                    where:`this.is_active='true'`,
                    //transform:false,
                    searchfield: 'this.kode,this.besaran,this.desc'
                  },
                  onsuccess(response) {
        response.page = response.current_page;
        response.hasNext = response.has_next;
        return response;
      }
              }" placeholder="Pilih Tunjangan Kemahalan" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'kode',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'besaran',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'desc',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              ]" />


            <FieldNumber v-else :bind="{ readonly: !actionText }" :value="values.tunjangan_posisi"
              @input="(v)=>values.tunjangan_posisi=v" :errorText="formErrors.tunjangan_posisi?'failed':''"
              :hints="formErrors.tunjangan_posisi" :check="false" class="col-span-12 !mt-5 w-full" label=""
              placeholder="Tuliskan Tunjangan Jabatan" />

            <button @click="editJabatan = !editJabatan"
            :disabled="!actionText"
              class="mt-5 p-2 text-gray-500 hover:text-blue-600 transition-colors"
              :title="editJabatan ? 'Cancel' : 'Edit'">
        <icon v-if="!editJabatan" fa="pencil"></icon>
        <icon v-else fa="times" class="text-red-500"></icon>
            </button>
          </div>
        </div>
      </div>

      <!-- Tunjangan Kemahalan -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Tunjangan Kemahalan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tunjangan_kemahalan_periode || 'Bulanan'" @input="v=>values.tunjangan_kemahalan_periode=v"
            :errorText="formErrors.tunjangan_kemahalan_periode?'failed':''"
            :hints="formErrors.tunjangan_kemahalan_periode" valueField="id" displayField="key" :options="['Bulanan']"
            :check="false" label="" placeholder="Pilih Periode" />
        </div>

        <div class="flex flex-col flex-1">
          <div class="flex items-center gap-2">
            <!-- FieldPopup (shown by default) -->
            <FieldPopup v-if="!editKemahalan" class="w-full py-2 mt-5" :bind="{ readonly: !actionText }"
              :value="values.tunjangan_kemahalan" @input="(v)=>values.tunjangan_kemahalan=v"
              :errorText="formErrors.tunjangan_kemahalan?'failed':''" :hints="formErrors.tunjangan_kemahalan"
              valueField="besaran" displayField="besaran" :api="{
                  url: `${store.server.url_backend}/operation/m_tunj_kemahalan`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    single:true,
                    where:`this.is_active='true'`,
                    transform:false,
                    searchfield: 'this.kode,this.besaran,this.desc'
                  },
                  onsuccess(response) {
        response.page = response.current_page;
        response.hasNext = response.has_next;
        return response;
      }
              }" placeholder="Pilih Tunjangan Kemahalan" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'kode',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'besaran',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'desc',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              ]" />

            <!-- FieldNumber (hidden by default) -->
            <FieldNumber v-else :bind="{ readonly: !actionText }" :value="values.tunjangan_kemahalan"
              @input="(v)=>values.tunjangan_kemahalan=v" :errorText="formErrors.tunjangan_kemahalan?'failed':''"
              :hints="formErrors.tunjangan_kemahalan" :check="false" class="w-full !mt-5" label=""
              placeholder="Tuliskan Tunjangan Kemahalan" />

            <!-- Edit Button -->
            <button @click="editKemahalan = !editKemahalan"
            :disabled="!actionText"
              class="mt-5 p-2 text-gray-500 hover:text-blue-600 transition-colors"
              :title="editKemahalan ? 'Cancel' : 'Edit'">
        <icon v-if="!editKemahalan" fa="pencil"></icon>
        <icon v-else fa="times" class="text-red-500"></icon>
            </button>
          </div>
        </div>
      </div>


      <!-- Tunjangan Fungsional -->
      <div class="flex items-center gap-2 mb-4">
        <div class="flex flex-col w-[161px] items-start gap-y-2">
          <label class="col-span-12">Tunjangan Fungsional<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
            :value="values.tunjangan_fubngsional_periode || 'Bulanan'"
            @input="v=>values.tunjangan_fubngsional_periode=v"
            :errorText="formErrors.tunjangan_fubngsional_periode?'failed':''"
            :hints="formErrors.tunjangan_fubngsional_periode" valueField="id" displayField="key" :options="['Bulanan']"
            :check="false" label="" placeholder="Pilih Periode" />
        </div>
        <div class="flex flex-col flex-1">
          <div class="flex items-center gap-2">
            <!-- FieldPopup (shown by default) -->
            <FieldPopup v-if="!editFungsional" class="w-full py-2 mt-5" :bind="{ readonly: !actionText }"
              :value="values.tunjangan_fungsional" @input="(v)=>values.tunjangan_fungsional=v"
              :errorText="formErrors.tunjangan_fungsional?'failed':''" :hints="formErrors.tunjangan_fungsional"
              valueField="besaran" displayField="besaran" :api="{
                  url: `${store.server.url_backend}/operation/m_tunjangan_fungsional`,
                  headers: {
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest:true,
                    single:true,
                    where:`this.is_active='true'`,
                    transform:false,
                    searchfield: 'this.kode,this.besaran,this.desc'
                  }
              }" placeholder="Pilih Tunjangan Fungsional" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'kode',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'besaran',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'desc',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              ]" />

            <!-- FieldNumber (hidden by default) -->
            <FieldNumber v-else :bind="{ readonly: !actionText }" :value="values.tunjangan_fungsional"
              @input="(v)=>values.tunjangan_fungsional=v" :errorText="formErrors.tunjangan_fungsional?'failed':''"
              :hints="formErrors.tunjangan_fungsional" :check="false" class="w-full !mt-5" label=""
              placeholder="Tuliskan Tunjangan Fungsional" />

            <!-- Edit Button -->
            <button
            @click="editFungsional = !editFungsional"
             :disabled="!actionText"
              class="mt-5 p-2 text-gray-500 hover:text-blue-600 transition-colors"
              :title="editFungsional ? 'Cancel' : 'Edit'">
        <icon v-if="!editFungsional" fa="pencil"></icon>
        <icon v-else fa="times" class="text-red-500"></icon>
            </button>
          </div>
        </div>
      </div>


    </div>
  </div>

  <!-- TABLE -->
  <div class="p-4">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Description
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Total Gaji Row -->
          <tr>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">TOTAL GAPOK + PRODUKTIFITAS + TRANSPORT</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
            values.totalGaji === 0 ? 'text-gray-900' : 'text-green-600',
            'text-sm font-bold'
          ]">
            {{ formattedTotalGaji }}.-
          </span>
            </td>
          </tr>

          <!-- Total Tunjangan Row -->
          <tr>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">TOTAL TUNJANGAN JABATAN + KEMAHALAN + FUNGSIONAL</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
            values.totalTunjanganGaji === 0 ? 'text-gray-900' : 'text-green-600',
            'text-sm font-bold'
          ]">
            {{ formattedTotalTunjanganGaji }}.-
          </span>
            </td>
          </tr>

          <!-- Grand Total Row -->
          <tr class="bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-bold text-gray-900">GRAND TOTAL</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
            values.grandTotal === 0 ? 'text-gray-900' : 'text-green-600',
            'text-sm font-bold'
          ]">
            {{ formattedGrandTotal }}.-
          </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>



  <!-- TABLE -->
  <div class="p-4">
    <!-- <button
      :disabled="!actionText"
      @click="addDetail"
      type="button"
      class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-3 px-6 flex items-center justify-center space-x-2 rounded transition-all duration-200 ease-in-out shadow-md hover:shadow-lg"
    >
      <icon fa="plus" /> 
      <span>Add to List</span> 
    </button> -->
    <div v-if="actionText">
      <ButtonMultiSelect title="Add Detail" @add="onDetailAdd" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
          params: { simplest: false,  where: `this.group = 'KOMPONEN_GAJI_DETAIL'`, selectfield:'this.id, this.group, this.value', searchfield:'this.id, this.group, this.value',
          notin:detailArr.length>0?`id:${detailArr.map(dt=>dt.komponen_id??0).join(',')}`:null },
          onsuccess:(response)=>{
            response.data = [...response.data].map((dt)=>{
              Object.keys(dt).forEach(k=>dt['m_general.'+k] = dt[k])
              dt['komponen_id'] = dt.id
              return dt
            })
            response.page = response.current_page
            response.hasNext = response.has_next
            return response
          }
        }" :columns="[{
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerName: 'No',
            valueGetter:(params)=>{
              return ''
            },
            width:60,
            sortable: false, resizable: true, filter: false,
            cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Komponen',
            sortable: false, resizable: true, filter: false,
            field: 'value',
            cellClass: ['justify-left','!border-gray-200']
          }
          ]">
        <div class="flex items-center space-x-2">
          <div v-show="actionText"
            class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-3 px-6 flex items-center justify-center space-x-2 rounded transition-all duration-200 ease-in-out shadow-md hover:shadow-lg">
            <icon fa="plus" />
            Add To List
          </div>
        </div>
      </ButtonMultiSelect>
    </div>
    <div class="mx-1 mt-4">
      <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
        <thead>
          <tr class="border bg-gray-100">
            <td
              class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 py-3 text-center w-[5%] border border-[#CACACA]">
              No.
            </td>
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center w-[20%] border border-[#CACACA]">
              Komponen
            </td>
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center border border-[#CACACA]">
              Tipe Komponen
            </td>
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center border border-[#CACACA]">
              Faktor
            </td>
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center border border-[#CACACA]">
              Nilai
            </td>
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center w-[20%] border border-[#CACACA]">
              Periode
            </td>
            <!-- <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center w-[5%] border border-[#CACACA]">
              THP
            </td> -->
            <td class="text-[#8F8F8F] font-semibold text-sm uppercase px-4 text-center w-[5%] border border-[#CACACA]">
              Aksi
            </td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
            <td class="p-2 text-center border border-[#CACACA]">
              {{ i + 1 }}.
            </td>
            <td class="p-2 border border-[#CACACA]">
              <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full"
                :value="item.komponen_id" @input="(v) => {
                        item.komponen_id = v;
                      }" :errorText="formErrors.komponen_id?'failed':''" displayField="value"
                :hints="formErrors.komponen_id" :api="{
                            url: `${store.server.url_backend}/operation/m_general`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              where: `this.group = 'KOMPONEN_GAJI_DETAIL'`,
                              selectfield:'this.id,this.value'
                            }
                      }" valueField="id" :check="false" label="" placeholder="Pilih Komponen" />
              <!-- <div class="grid grid-cols-12 items-center">
                <FieldX :bind="{ readonly: !actionText }" class="col-span-11 w-full !mt-0" :value="item.komponen" @input="v=>item.komponen=v" v-if="item.is_insert"
                :errorText="formErrors.komponen?'failed':''" :hints="formErrors.komponen" label=""
                placeholder="Tuliskan Komponen" :check="false" />  
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:true }" class="col-span-11 w-full !mt-0" v-else
                  :value="item.komponen" @input="v=>{
                      item.komponen=v
                  }"
                  :errorText="formErrors.komponen?'failed':''" 
                  :hints="formErrors.komponen"
                  :options="optTipe"
                  placeholder="Pilih Komponen" label="" :check="false"
                />
                
                <input type="checkbox" v-model="item.is_insert" id="is_insert"  class="col-span-1 w-full !mt-0" :disabled="!actionText">
                </div> -->
            </td>



            <td class="p-2 border border-[#CACACA]">
              <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full"
                :value="item.komponen_tipe_id" @input="(v) => {
                        item.komponen_tipe_id = v;
                      }" :errorText="formErrors.komponen_tipe_id?'failed':''" displayField="value"
                :hints="formErrors.komponen_tipe_id" :api="{
                            url: `${store.server.url_backend}/operation/m_general`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              where: `this.group = 'TIPE KOMPONEN' AND this.is_active = 'true'`,
                              selectfield:'this.id,this.value'
                            }
                      }" valueField="id" :check="false" label="" placeholder="Pilih Tipe" />
            </td>



            <td class="p-2 border border-[#CACACA]">
              <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full" :value="item.faktor"
                @input="v=>item.faktor=v" :errorText="formErrors.faktor?'failed':''" label="" placeholder="Pilih Faktor"
                :hints="formErrors.faktor" :options="['-','+']" valueField="key" displayField="key" :check="false" />
            </td>

            <td class="p-2 border border-[#CACACA]">
              <FieldNumber class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.nilai"
                @input="(v)=>item.nilai=v" :errorText="formErrors.nilai?'failed':''" :hints="formErrors.nilai"
                placeholder="Tuliskan Nilai" label="" :check="false" />
            </td>

            <td class="p-2 border border-[#CACACA]">
              <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="!mt-0 w-full" :value="item.periode"
                @input="v=>item.periode=v" :errorText="formErrors.periode?'failed':''" label=""
                placeholder="Pilih Periode" :hints="formErrors.periode" :options="['Bulanan','Harian']" valueField="key"
                displayField="key" :check="false" />
            </td>

            <!-- <td class="p-3 text-center border">
              <div class="flex justify-center items-center">
                <label class="h-6 w-6 cursor-pointer flex items-center justify-center">
      <input 
        type="checkbox" 
        class="peer hidden" 
        v-model="item.is_from_thp" 
        :disabled="!actionText || item.komponen_tipe_id !== 15539" 
      />
      <span class="h-6 w-6 flex items-center justify-center border border-gray-300 rounded-md bg-white peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-disabled:opacity-50">
        <svg 
          v-if="item.is_from_thp" 
          xmlns="http://www.w3.org/2000/svg" 
          class="h-4 w-4 text-white" 
          viewBox="0 0 20 20" 
          fill="currentColor"
        >
          <path 
            fill-rule="evenodd" 
            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-3.293-3.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" 
            clip-rule="evenodd" 
          />
        </svg>
      </span>
    </label>
              </div>
            </td> -->



            <td class="p-2 border border-[#CACACA]">
              <div class="flex justify-center">
                <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
              </div>

            </td>
          </tr>
          <tr v-else class="text-center">
            <td colspan="7" class="py-[20px]">
              No data to show
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
  <!--BUTTON-->

  <div class="flex flex-row items-center justify-end space-x-2 p-2">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
    <button
        class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText"
        @click="onReset(true)"
      >
        <icon fa="times" />
        Reset
      </button>
    <button
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)"
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>

<hr>


</div>
@endverbatim
@endif