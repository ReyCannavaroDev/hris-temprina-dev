<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md h-full border-t-10 border-gray-500">
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
      <button
        v-if="!isUserOS"
        @click="syncData"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md mx-2 py-1 px-2"
      >
        Sync to ERP
      </button>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="h-screen">
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
        <h1 class="text-20px font-bold">Form User</h1>
        <p class="text-gray-100">Untuk konfigurasi pengguna pada sistem</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldPopup class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.m_kary_id"
        @input="(v)=>values.m_kary_id=v" @update:valueFull="onKarySelect" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
        valueField="id" displayField="nama_lengkap" :api="apiKaryPopup" placeholder="Pilih Nama Karyawan" :check="true" :columns="[
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
      headerName: 'NIK',
      sortable: false,
      resizable: true,
      filter: false,
      cellClass: ['border-r', '!border-gray-200', 'justify-center']
    },
    {
      flex: 1,
      headerName: 'Nama',
      cellClass: ['justify-center', 'border-r', '!border-gray-200'],
      field: 'nama_lengkap',
      sortable: false,
      resizable: true,
      filter: false,
    },
  ]" />

    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.name"
        :errorText="formErrors.name?'failed':''" @input="v=>values.name=v" :hints="formErrors.name" :check="true"
        label="Nama" placeholder="Nama | Ex : Budiono" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable: true }" :value="values.m_os_id"
        @input="v=>values.m_os_id=v" :errorText="formErrors.m_os_id?'failed':''" :hints="formErrors.m_os_id"
        valueField="id" displayField="name" :api="{
            url: `${store.server.url_backend}/operation/m_company_outsourcing`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              transform:false,
              join:false,
              where: 'this.is_active=true'
            }
        }" placeholder="Nama OS (Kosongkan jika Pegawai Internal)" label="Nama OS" fa-icon="" :check="false" />

    </div>
    <!-- <div>
        <FieldX :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.nama_comp" :errorText="formErrors.nama_comp?'failed':''"
          @input="v=>values.nama_comp=v" :hints="formErrors.nama_comp" 
          :check="true"
          label="Unit Bisnis"
          placeholder="Unit | Ex : Pemasaran"
        />
      </div> -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.username"
        :errorText="formErrors.username?'failed':''" @input="v=>values.username=v" :hints="formErrors.username"
        label="User Login" placeholder="User Login | Ex : marketing_1" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.email"
        :errorText="formErrors.email?'failed':''" @input="v=>{
            validateEmail(v)
            }" :hints="formErrors.email" placeholder="Masukan Email" label="Email" type="email" :check="false" />
    </div>

    <div>
      <FieldX type="password" class="w-full !mt-3" :bind="{ readonly: !actionText }"
        :value="(values.password||'').substr(0,12)" :errorText="formErrors.password?'failed':''"
        @input="v=>values.password=v" placeholder="Password" fa-icon="lock" :hints="formErrors.password"
        :check="false" />
    </div>

    <div v-if="actionText">
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="(values.password_confirm||'').substr(0,12)"
        :errorname="formErrors.password_confirm?'failed':''" @input="v=>values.password_confirm=v"
        :hints="formErrors.password_confirm" :check="false" type="password" fa-icon="lock" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.user_type"
        @input="v=>{
            if(v){
              values.user_type=v
            }else{
              values.user_type=null
            }
          }" :errorText="formErrors.user_type?'failed':''" :hints="formErrors.user_type" valueField="key"
        displayField="key" :options="[{'id' : 1 , 'key' : 'Admin'},{'id': 2, 'key' : 'User'}]"
        placeholder="Pilih Tipe User" label="Tipe User" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.note"
        :errorText="formErrors.note?'failed':''" @input="v=>values.note=v" :hints="formErrors.note" :check="false"
        type="textarea" label="Catatan" placeholder="Catatan | Ex : User ini digunakan untuk marketing 1" />
    </div>

    <!-- IS HC -->
    <!-- <div class="pl-3 flex flex-col justify-center !mt-3">
      <div class="flex items-center space-x-3">
        
        <input
          type="checkbox"
          id="is_hc_checkbox"
          class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 hover:cursor-pointer"
          :disabled="!actionText"
          v-model="values.is_hc"
        />
        
        <span :class="values.is_hc ? 'text-green-500 font-bold' : 'text-gray-400'">HC</span>
      </div>
      
    </div> -->
    <div class="pl-3 flex flex-col justify-center !mt-3">
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">nonHC</i>
        </div>
        <div class="flex-auto">
          <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.is_hc === false}"
                role="switch"
                id="is_active_for_click"
                :disabled="!actionText"
                v-model="values.is_hc"
                />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">HC</i>
        </div>
      </div>
    </div>

    <!-- STATUS -->
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


    <!-- START TABLE DETAIL -->
    <hr class="<md:col-span-1 col-span-3">
    <div class="<md:col-span-1 col-span-3">
      <!-- <button :disabled="!actionText" @click="addDetail" type="button" class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
          <icon fa="plus" />
          Add to List
        </button> -->
      <ButtonMultiSelect title="Add To List" @add="onDetailAdd" :api="apiRespoMultiSelect" :columns="[{
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerName: 'No',
            valueGetter:p=>'',
            width:60,
            sortable: false, resizable: true, filter: false,
            cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Unit Bisnis',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'm_comp.name',
            cellClass: ['justify-center','!border-gray-200']
          },
           {
            flex: 1,
            headerName:'Sub Unit Bisnis',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'm_subcomp.name',
            cellClass: ['justify-center','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Cabang',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'm_branch.name',
            cellClass: ['justify-center','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Responsibility',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'name',
            cellClass: ['justify-center','!border-gray-200']
          }]">
        <div v-show="actionText"
          class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
          <icon fa="plus" />
          Add To List
        </div>
      </ButtonMultiSelect>

      <div class="mt-4">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Unit Bisnis</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Sub Unit Bisnis</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Cabang</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Responsibility</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                Is Primary</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                Status</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                Action</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
              <td class="p-2 text-center border border-[#CACACA]">
                {{ i + 1 }}.
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: true , clearable:false }" class="w-full mt-0" :value="item.m_comp_id"
                  label="" placeholder="" @input="v=>item.m_comp_id=v" :errorText="formErrors.m_comp_id?'failed':''"
                  :hints="formErrors.m_comp_id" valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_comp`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,

                    join: true,

                  }
                }" fa-icon="sort-desc" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: true , clearable:false }" class="w-full mt-0" :value="item.m_subcomp_id"
                  label="" placeholder="" @input="v=>item.m_subcomp_id=v"
                  :errorText="formErrors.m_subcomp_id?'failed':''" :hints="formErrors.m_subcomp_id" valueField="id"
                  displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_subcomp`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,

                    join: true,

                  }
                }" fa-icon="sort-desc" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: true , clearable:false }" class="w-full mt-0" :value="item.m_branch_id"
                  label="" placeholder="" @input="v=>item.m_branch_id=v" :errorText="formErrors.m_branch_id?'failed':''"
                  :hints="formErrors.m_branch_id" valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_branch`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,

                    join: true,

                  }
                }" fa-icon="sort-desc" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: true , clearable:false }" class="w-full mt-0" :value="item.m_respo_id"
                  label="" placeholder="" @input="v=>item.m_respo_id=v" :errorText="formErrors.m_respo_id?'failed':''"
                  :hints="formErrors.m_respo_id" valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_respo`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,

                    join: true,

                  }
                }" fa-icon="sort-desc" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <input :disabled="!actionText" type="checkbox" id="is_primary" value="is_primary" @change="changePrimaryDetail(i)" v-model="item.is_primary" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                </div>
              </td>

              <!-- STATUS -->
              <div class="pl-3 flex flex-col justify-center !mt-3">
                <div class="flex w-40">
                  <div class="flex-auto">
                    <i class="text-red-500">InActive</i>
                  </div>
                  <div class="flex-auto">
                    <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': item.is_active === false}"
                role="switch"
                id="is_active_for_click"
                :disabled="!actionText"
                v-model="item.is_active"
                />
                  </div>
                  <div class="flex-auto">
                    <i class="text-green-500">Active</i>
                  </div>
                </div>
              </div>

              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                      <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
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
    <!-- END TABLE DETAIL -->

    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>
  <hr>
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
@endverbatim
@endif