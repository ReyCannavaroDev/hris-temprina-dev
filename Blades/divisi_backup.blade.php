<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Aktif</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inaktif</button>
      </div>
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))" class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="max-h-[450px]">
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
        <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali" @click="onBack"/>
        <div>
          <h1 class="text-20px font-bold">Form Divisi</h1>
          <p class="text-gray-100">Master Divisi</p>
        </div>
      </div>
    </div>
    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.m_branch_id" @input="v=>{
            if(v){
              values.m_branch_id=v
            }else{
              values.m_branch_id=null
              values.parent_id=null
              values.nomorParent = null
              values.level = 0
            }
            }"
          @update:valueFull="v=>{
            values.parent_id=null
            values.nomorParent = null
            values.level = 0
          }"
          :errorText="formErrors.m_branch_id?'failed':''" 
          :hints="formErrors.m_branch_id"
          valueField="id" displayField="name"
          :api="{          
              url: `${store.server.url_backend}/operation/m_branch`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.is_active='true'`
              }
          }"
          placeholder="Pilih Cabang" label="Cabang" :check="false"
        />
      </div>
        
      <div class="grid grid-cols-12 items-center">
        <div class="col-span-3">
          <label class="inline-block" for="isParent">Parent COA</label>
        </div>
        <span>:</span>
        <div class="flex">
          <input type="checkbox" v-model="values.is_parent" @change="changeParent" id="isParent" class="col-span-8" :disabled="!actionText">
          <span class="ml-2">Yes</span>
        </div>
      </div>

      <FieldPopup  v-if="values.is_parent" class="w-full !mt-3"
        :bind="{ readonly: !actionText }"
        :value="values.parent_id" @input="v=>{
          if(v){
            values.parent_id=v
          }else{
            values.parent_id=null
            values.nomorParent = null
            values.level = 0
          }
        }"
        :errorText="formErrors.parent_id?'failed':''" 
        :hints="formErrors.parent_id" 
        @update:valueFull="v=>{
          if(v){
            values.nomorParent = v.nomor
            values.level = (parseInt(v.level ?? 0) + 1).toString()
          }else{
            values.level = 0
            values.nomorParent = null
          }
        }"
        valueField="id" displayField="name"
        :api="{
          url: `${store.server.url_backend}/operation/m_divisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            selectfield: 'this.id, this.name, this.level, this.nomor',
            where: `this.m_branch_id = ${values.m_branch_id ?? 0} AND this.is_active = true`
          }
        }"
        placeholder="Pilih Parent Divisi" label="Parent Divisi" :check="false" 
        :columns="[{
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
          headerName:  'Nama Divisi',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        }
        ]"
      />
      
      <div class="grid grid-cols-2 gap-x-4">
        <FieldNumber v-show="values.is_parent" :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.nomorParent" :errorText="formErrors.nomorParent?'failed':''"
          @input="v=>values.nomorParent=v" :hints="formErrors.nomorParent" 
          :check="false"
          label="Nomor Parent"
          placeholder="Autofield Nomor Parent"
        />
        <FieldNumber :bind="{ readonly: !actionText }" :class="{'col-span-2':!values.is_parent}" class="w-full !mt-3"
          :value="values.tempNomor" :errorText="formErrors.nomor?'failed':''"
          @input="v=>{
            if(v===''){
              values.nomor=null
            }
            values.tempNomor=v}" :hints="formErrors.nomor" 
          :check="true"
          label="Nomor"
          placeholder="Masukan Nomor"
        />
      </div>
      
      <div>
        <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3"
          :value="values.name" :errorText="formErrors.name?'failed':''"
          @input="v=>values.name=v" :hints="formErrors.name" 
          :check="true"
          label="Nama"
          placeholder="Masukan Nama"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.level?.toString()" @input="(v)=>values.level=v"
          :errorText="formErrors.level?'failed':''" 
          :hints="formErrors.level"
          label="Level"
          placeholder="Masukan Level" :check="true"
        />
      </div>




      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.is_active" @input="v=>values.is_active=v"
          :errorText="formErrors.is_active?'failed':''" 
          :hints="formErrors.is_active"
          valueField="id" displayField="key"
          :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]"
          placeholder="Pilih Status" label="Status" :check="false"
        />
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
        @click="onReset(true)" 
      >
        <icon fa="times" />
        Reset
      </button>
      <button 
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText" 
        @click="onSave" 
      >
        <icon fa="save" />
        Simpan
      </button>
    </div>
  </div>
@endverbatim
@endif