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
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="h-full">
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
        <h1 class="text-20px font-bold">Form Jabatan</h1>
        <p class="text-gray-100">Master Jabatan</p>
      </div>
    </div>

  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.m_divisi_id"
        @input="v=>{
            if(v){
              values.m_divisi_id=v
            }else{
              values.m_divisi_id=null
              values.parent_id=null
              values.nomorParent = null
              values.level = 0
            }
            }" @update:valueFull="v=>{
            values.parent_id=null
            values.nomorParent = null
            values.level = 0
          }" :errorText="formErrors.m_divisi_id?'failed':''" :hints="formErrors.m_divisi_id" valueField="id"
        displayField="value" :api="{          
              url: `${store.server.url_backend}/operation/m_general`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //concat_branch : true,
                //simplest:true,
                //scopes: 'Names',
                where: `this.group='MASTER_DIVISI'`,
                selectfield: 'this.id, this.value',
                searchfield: 'this.value',
              }
          }" placeholder="Pilih Divisi" label="Divisi" :check="false" />
    </div>


    <!-- <div class="grid grid-cols-12 items-center">
      <div class="col-span-3">
        <label class="inline-block" for="isParent">Parent Jabatan</label>
      </div>
      <span>:</span>
      <div class="flex">
        <input type="checkbox" v-model="values.is_parent" @change="changeParent" id="isParent" class="col-span-8" :disabled="!actionText">
        <span class="ml-2">Yes</span>
      </div>
    </div> -->



    <div v-if="values.is_parent">
      <FieldPopup class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.parent_id" @input="v=>{
          if(v){
            values.parent_id=v
          }else{
            values.parent_id=null
            values.nomorParent = null
            values.level = 0
          }
        }" :errorText="formErrors.parent_id?'failed':''" :hints="formErrors.parent_id" @update:valueFull="v=>{
          if(v){
            values.nomorParent = v.nomor
            values.level = (parseInt(v.level ?? 0) + 1).toString()
          }else{
            values.level = 0
            values.nomorParent = null
          }
        }" valueField="id" displayField="name" :api="{
          url: `${store.server.url_backend}/operation/m_posisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            selectfield: 'this.id, this.name, this.level, this.nomor',
            where: `this.m_divisi_id = ${values.m_divisi_id ?? 0} AND this.is_active = true`
          }
        }" placeholder="Pilih Jabatan" label="Jabatan" :check="false" :columns="[{
          headerName: 'No',
          valueGetter:(p)=>p.node.rowIndex + 1,
          width: 60,
          sortable: false, resizable: false, filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
        {
          flex: 1,
          field: 'nomor',
          headerName:  'Nomor Divisi',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'name',
          headerName:  'Nama Jabatan',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'level',
          headerName:  'Level Jabatan',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        ]" />
    </div>


    <!-- <div class="grid grid-cols-12 items-center" v-if="values.is_parent">
      <div class="col-span-3">
        <label class="inline-block" for="isParent">Same Level</label>
      </div>
      <span>:</span>
      <div class="flex">
        <input type="checkbox" v-model="values.is_same_level" @change="changeParent" id="isParent" class="col-span-8" :disabled="!actionText">
        <span class="ml-2">Yes</span>
      </div>
    </div> -->

    <!-- <div class="grid grid-cols-2 gap-x-4">
      <FieldX v-show="values.is_parent" :bind="{ readonly: true }" class="w-full !mt-3" :value="values.nomorParent"
        :errorText="formErrors.nomorParent?'failed':''" @input="v=>values.nomorParent=v" :hints="formErrors.nomorParent"
        :check="false" label="Nomor Parent" placeholder="Autofield Nomor Parent" />
      <FieldX :bind="{ readonly: !actionText }" :class="{'col-span-2':!values.is_parent}" class="w-full !mt-3"
        :value="values.tempNomor" :errorText="formErrors.nomor?'failed':''" @input="v=>{
            if(v===''){
              values.nomor=null
            }
            values.tempNomor=v}" :hints="formErrors.nomor" :check="true" label="Nomor" placeholder="Masukan Nomor" />
    </div> -->

    <div class="relative">
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.name"
        :errorText="formErrors.name ? 'failed' : ''" @input="handleInput" @focus="openDropdown" ref="inputField"
        label="Nama Jabatan" placeholder="Tuliskan Jabatan" />


      <div v-if="isDropdownOpen && filteredOptions.length" ref="dropdownMenu"
        class="absolute mt-1 w-full bg-white border border-gray-300 rounded-md shadow-md max-h-40 overflow-auto z-10">
        <ul>
          <li v-for="(nama, index) in filteredOptions" :key="index" @mousedown.prevent="selectName(nama)"
            class="px-3 py-2 cursor-pointer hover:bg-gray-100">
            {{ nama }}
          </li>
        </ul>
      </div>
    </div>



    <!-- <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt-3" :value="values.level?.toString()"
        @input="(v)=>values.level=v" :errorText="formErrors.level?'failed':''" :hints="formErrors.level" label="Level"
        placeholder="Masukan Level" :check="true" />
    </div> -->

    <!-- <div>
      <FieldNumber :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.nominal"
        @input="(v)=>values.nominal=v" :errorText="formErrors.nominal?'failed':''" :hints="formErrors.nominal"
        label="Nominal" placeholder="Masukan Nominal" :check="true" />
    </div> -->



    <!-- <div class="pl-3 flex flex-col justify-center ">
      <span> No Salary Deducation.</span>
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">Tidak</i>
        </div>
        <div class="flex-auto">
          <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.no_salary_deduction === false}"
                role="switch"
                id="no_salary_deduction_for_click"
                :disabled="!actionText"
                v-model="values.no_salary_deduction"
                />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">Iya</i>
        </div>
      </div>
    </div> -->



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


    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>

  <hr>
  <div class="flex flex-row items-center justify-end space-x-2 p-2">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
    <button
        class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText"
        @click="onBack"
      >
        <icon fa="times" />
        Kembali
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