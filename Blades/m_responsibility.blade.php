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
    <div>
      <RouterLink v-if="data.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))" class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
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
          <h1 class="text-20px font-bold">Form Responsibility</h1>
          <p class="text-gray-100">Master Responsibility</p>
        </div>
      </div>
    </div>
    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.m_comp_id" @input="v=>{
            $log(v)
            if(v){
              values.m_comp_id=v
            }else{            
              values.m_comp_id=null
              values.m_subcomp_id=null
            }
          }"
          :errorText="formErrors.m_comp_id?'failed':''" 
          :hints="formErrors.m_comp_id"
          @update:valueFull="v=>{
            values.m_subcomp_id=null
          }"
          valueField="id" displayField="name"
          :api="{
              url: `${store.server.url_backend}/operation/m_comp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: 'this.is_active=true'
              }
          }"
          placeholder="Pilih Business Unit" label="Business Unit" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.m_subcomp_id" @input="v=>{
            if(v){
              values.m_subcomp_id=v
            }else{            
              values.m_subcomp_id=null
              values.m_branch_id=null
            }
          }"
          @update:valueFull="v=>{
            values.m_branch_id=null
          }"
          :errorText="formErrors.m_subcomp_id?'failed':''" 
          :hints="formErrors.m_subcomp_id"
          valueField="id" displayField="name"
          :api="{
              url: `${store.server.url_backend}/operation/m_subcomp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.m_comp_id=${values.m_comp_id??0} AND this.is_active=true`
              }
          }"
          placeholder="Pilih Sub Business Unit" label="Sub Business Unit" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.m_branch_id" @input="v=>values.m_branch_id=v"
          :errorText="formErrors.m_branch_id?'failed':''" 
          :hints="formErrors.m_branch_id"
          valueField="id" displayField="name"
          :api="{
              url: `${store.server.url_backend}/operation/m_branch`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.m_subcomp_id=${values.m_subcomp_id??0} AND this.is_active=true`
              }
          }"
          placeholder="Pilih Cabang" label="Cabang" :check="false"
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

      <!-- START TABLE DETAIL -->
      <hr class="<md:col-span-1 col-span-3">
      <div class="<md:col-span-1 col-span-3">
        <ButtonMultiSelect
          title="Add To List"
          @add="onDetailAdd"
          :api="{
            url: `${store.server.url_backend}/operation/m_role`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { 
                simplest: true,
                where: 'this.is_active=true',
                searchfield: 'this.code, this.name, this.id',
                notin:detailArr.length>0?`m_role.id:${detailArr.map(dt=>dt.m_role_id).join(',')}`:null
              },
            onsuccess:(response)=>{
              response.data = [...response.data].map((dt)=>{
                return dt
              })
              response.page = response.current_page
              response.hasNext = response.has_next
              return response
            }
          }"
          :columns="[{
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
            headerName:'Kode Role',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'code',
            cellClass: ['justify-center','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Nama Role',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'name',
            cellClass: ['justify-center','!border-gray-200']
          },
          ]">
            <div v-show="actionText" class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
              <icon fa="plus" />
              Add To List
            </div>
        </ButtonMultiSelect>
        
        <div class="mt-4">
            <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
              <thead>
                <tr class="border">
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Kode Role</td>
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Nama Role</td>
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Catatan</td>
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Status</td>
                  <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">Action</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in detailArr" :key="i" class="border-t" v-if="detailArr.length > 0">
                  <td class="p-2 text-center border border-[#CACACA]">
                    {{ i + 1 }}.
                  </td>
                  <td class="p-2 border border-[#CACACA]">
                    {{item.code ?? '-'}}
                  </td>
                  <td class="p-2 border border-[#CACACA]">
                    {{item.name ?? '-'}}
                  </td>
                  <td class="p-2 border border-[#CACACA]">
                    <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0"
                      :value="item.note" @input="v=>item.note=v"
                      placeholder="Tuliskan Catatan" type="textarea" label="" :check="false"
                    />
                    
                  </td>
                  <td class="p-2 border border-[#CACACA]">
                    <FieldSelect
                      :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-0"
                      :value="item.is_active" @input="v=>item.is_active=v"
                      valueField="id" displayField="key"
                      :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]"
                      placeholder="Status" label="" :check="false"
                    />
                  </td>
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
        v-show="actionText  && (currentMenu?.can_create || currentMenu?.can_update)" 
        @click="onSave" 
      >
        <icon fa="save" />
        Simpan
      </button>
    </div>
  </div>
@endverbatim
@endif