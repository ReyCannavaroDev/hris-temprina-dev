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
    class="max-h-[100%]">
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
        <h1 class="text-20px font-bold">Form Approval User</h1>
        <p class="text-gray-100">Master Approval User</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt-3" :value="values.code"
        :errorText="formErrors.code?'failed':''" @input="v=>values.code=v" :hints="formErrors.code"
        placeholder="Autofill Kode" label="Kode" :check="false" />
    </div>
    <!-- <div>
        <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3"
          :value="values.name" :errorText="formErrors.name?'failed':''"
          @input="v=>values.name=v" :hints="formErrors.name" 
          placeholder="Masukan Nama" label="Nama" :check="true"
        />
      </div> -->
       <div>
          <FieldSelect class="w-full mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.name" @input="v=>values.name=v"
            :errorText="formErrors.name?'failed':''" :hints="formErrors.name" valueField="value" displayField="value" :options="[
            { id: 1 , label: 'APPROVAL CUTI', value: 'APPROVAL CUTI' },
            { id: 2 , label: 'APPROVAL LEMBUR', value: 'APPROVAL LEMBUR' },
            { id: 3 , label: 'APPROVAL LAPORAN PEKERJAAN', value: 'APPROVAL LAPORAN PEKERJAAN' },
            { id: 4 , label: 'APPROVAL PENGAJUAN PELATIHAN', value: 'APPROVAL PENGAJUAN PELATIHAN' },
            { id: 5 , label: 'APPROVAL RINCIAN PERDIN', value: 'APPROVAL RINCIAN PERDIN' },
            { id: 6 , label: 'APPROVAL PENYELESAIAN PERDIN', value: 'APPROVAL PENYELESAIAN PERDIN' },
            { id: 7 , label: 'APPROVAL REALISASI PELATIHAN', value: 'APPROVAL REALISASI PELATIHAN' },
            { id: 8 , label: 'APPROVAL EVALUASI PELATIHAN', value: 'APPROVAL EVALUASI PELATIHAN' },
            { id: 9 , label: 'APPROVAL EFEKTIFITAS PELATIHAN', value: 'APPROVAL EFEKTIFITAS PELATIHAN' },
            { id: 10 , label: 'APPROVAL LOWONGAN PEKERJAAN', value: 'APPROVAL LOWONGAN PEKERJAAN' },
          ]" placeholder="Pilih Nama" label="Nama" fa-icon="" :check="false" />
        </div>
      <!-- <div>
        <FieldSelect 
          :bind="{ readonly: !actionText }" 
          class="w-full !mt-3"
          :value="values.name" 
          :errorText="formErrors.name ? 'failed' : ''"
          @input="v => values.name = v" 
          :hints="formErrors.name" 
          placeholder="Pilih Nama" 
          label="Nama" 
          :check="true"
          :options="[
            { label: 'APPROVAL CUTI', value: 'APPROVAL CUTI' },
            { label: 'APPROVAL LEMBUR', value: 'APPROVAL LEMBUR' },
            { label: 'APPROVAL LAPORAN PEKERJAAN', value: 'APPROVAL LAPORAN PEKERJAAN' },
            { label: 'APPROVAL PENGAJUAN PELATIHAN', value: 'APPROVAL PENGAJUAN PELATIHAN' },
            { label: 'APPROVAL RINCIAN PERDIN', value: 'APPROVAL RINCIAN PERDIN' },
            { label: 'APPROVAL PENYELESAIAN PERDIN', value: 'APPROVAL PENYELESAIAN PERDIN' },
            { label: 'APPROVAL REALISASI PELATIHAN', value: 'APPROVAL REALISASI PELATIHAN' },
            { label: 'APPROVAL EVALUASI PELATIHAN', value: 'APPROVAL EVALUASI PELATIHAN' },
            { label: 'APPROVAL EFEKTIFITAS PELATIHAN', value: 'APPROVAL EFEKTIFITAS PELATIHAN' },
          ]"
        />
      </div> -->
    <!-- MENU ID -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.m_menu_id"
        @input="v=>values.m_menu_id=v" :errorText="formErrors.m_menu_id?'failed':''" :hints="formErrors.m_menu_id"
        valueField="id" displayField="menu" :api="{
              url: `${store.server.url_backend}/operation/m_menu`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                scopes:'approval',
                simplest:true,
                where: 'this.is_active=true',
                paginate: 100,
                order_by: 'menu',
                order_type: 'ASC'
              }
          }" placeholder="Pilih Menu" label="Menu" :check="true" />
    </div>
    <!-- Identifier -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.identifier"
        :errorText="formErrors.identifier?'failed':''" @input="v=>values.identifier=v" :hints="formErrors.identifier"
        placeholder="Masukan Identifier" label="Identifier" :check="false" />
    </div>
    <!-- NOTE -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.note"
        :errorText="formErrors.note?'failed':''" @input="v=>values.note=v" :hints="formErrors.note"
        placeholder="Pilih Catatan" label="Catatan" type="textarea" :check="false" />

    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.is_active"
        @input="v=>values.is_active=v" :errorText="formErrors.is_active?'failed':''" :hints="formErrors.is_active"
        valueField="id" displayField="key" :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]"
        placeholder="Pilih Status" label="Status" :check="false" />
    </div>

    <!-- START TABLE DETAIL -->
    <hr class="<md:col-span-1 col-span-3">
    <div class="<md:col-span-1 col-span-3">
      <button v-show="actionText" @click="addDetail" type="button" class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
          <icon fa="plus" />
          Add to List
        </button>

      <div style="-ms-overflow-style: none;scrollbar-width: thin; "
        class="mt-4 overflow-x-scroll overflow-y-visible pb-15">
        <table class="table-auto overflow-auto border border-[#CACACA] w-[100%] min-h-[10%]">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                Level<label class="text-red-600">*</label></td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">
                Tipe<label class="text-red-600">*</label></td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">
                User</td>
              <!-- <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[10%]">
                Level Jabatan</td> -->
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                Full Approval</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                Status</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                Action</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in detailArr" :key="i" class="border-t" v-if="detailArr.length > 0">
              <td class="p-2 text-center border border-[#CACACA]">
                {{ i + 1 }}.
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber :bind="{ readonly: !actionText || i == 0 }" class="!mt-0" :value="item.level"
                  @input="(v)=>item.level=v" placeholder="" :check="false" label="" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText || i == 0, clearable:true }" class="!mt-0"
                  :value="item.type" @input="v=>item.type=v" :options="['MENGAJUKAN', 'MENYETUJUI']" placeholder=""
                  label="" :check="false" />
              </td>
              <!-- <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="!mt-0"
                  :value="item.m_level_posisi_id" @input="v=>item.m_level_posisi_id=v" valueField="id" displayField="level_name"
                  :api="{
                          url: `${store.server.url_backend}/operation/m_level_posisi`,
                          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                          params: {
                      simplest:true,
                      transform:false,
                      join:false,
                      selectfield:`level_name,id`,
                      searchfield:'level_name'
                          }
                      }" placeholder="" label="" :check="false" />
              </td> -->
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="!mt-0"
                  :value="item.default_users_id" @input="v=>item.default_users_id=v" valueField="id" displayField="username"
                  :api="{
                          url: `${store.server.url_backend}/operation/default_users`,
                          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                          params: {
                      simplest:true,
                      transform:false,
                      join:false,
                      selectfield:`username,id,this.profil_image`,
                      searchfield:'username'
                          }
                      }" placeholder="" label="" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <input type="checkbox" v-model="item.is_full" :id="i" :disabled="!actionText">
                  <label :for="i" class="ml-2"> Ya</label>
                </div>
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-0"
                  :value="item.is_active" @input="v=>item.is_active=v" valueField="id" displayField="key"
                  :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]" placeholder="Status" label=""
                  :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <button type="button" @click="removeDetail(i)" :disabled="!actionText || i == 0">
                      <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                      </svg>
                    </button>
                </div>

              </td>
            </tr>
            <tr v-else class="text-center">
              <td colspan="12" class="py-[20px]">
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