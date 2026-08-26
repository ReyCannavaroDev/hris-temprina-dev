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
    <div v-if="data?.can_create">
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
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
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Penilaian</h1>
        <p class="text-gray-100">Master Penilaian</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->

    <!-- SBU  -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt-3" :value="values.m_comp_id" @input="v => {
    if (v) {
      if (values.m_comp_id !== v && actionText) {
        values.m_subcomp_id = null;
        values.m_branch_id = null;
        values.m_divisi_id = null;
      }
      values.m_comp_id = v;
    } else {
      values.m_comp_id = null;
      if (actionText) {
        values.m_subcomp_id = null;
        values.m_branch_id = null;
        values.m_divisi_id = null;
      }
    }
  }" :errorText="formErrors.m_comp_id ? 'failed' : ''" :hints="formErrors.m_comp_id" :check="false" label="SBU "
        placeholder="Pilih SBU " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_comp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true'`,
      transform: false
    }
  }" />

    </div>
    <!-- SUB  -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText || !values.m_comp_id }" class="w-full !mt-3"
        :value="values.m_subcomp_id" @input="v => {
    if (v) {
      if (values.m_subcomp_id !== v && actionText) {
        values.m_branch_id = null;
        values.m_divisi_id = null;
      }
      values.m_subcomp_id = v;
    } else {
      values.m_subcomp_id = null;
      if (actionText) {
        values.m_branch_id = null;
        values.m_divisi_id = null;
      }
    }
  }" :errorText="formErrors.m_subcomp_id ? 'failed' : ''" :hints="formErrors.m_subcomp_id" :check="false" label="SUB "
        placeholder="Pilih SUB " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_subcomp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true' AND this.m_comp_id='${values.m_comp_id}'`,
      transform: false
    }
  }" />

    </div>
    <!-- CABANG -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText || !values.m_subcomp_id }" class="w-full !mt-3"
        :value="values.m_branch_id" @input="v => {
    if (v) {
      if (values.m_branch_id !== v && actionText) {
        values.m_divisi_id = null;
      }
      values.m_branch_id = v;
    } else {
      values.m_branch_id = null;
      if (actionText) {
        values.m_divisi_id = null;
      }
    }
  }" :errorText="formErrors.m_branch_id ? 'failed' : ''" :hints="formErrors.m_branch_id" :check="false" label="Cabang "
        placeholder="Pilih Cabang " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_branch`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true' AND this.m_subcomp_id='${values.m_subcomp_id}'`,
      transform: false
    }
  }" />

    </div>
    <!-- DIVISI  -->
    <!-- <div>
      <FieldSelect :bind="{ disabled: !actionText || !values.m_branch_id }" class="w-full !mt-3"
        :value="values.m_divisi_id" @input="v => {
    values.m_divisi_id = v || null;
  }" @update:valueFull="obj => {
    values.m_divisi_id = obj ? obj.id : null;
  }" :errorText="formErrors.m_divisi_id ? 'failed' : ''" :hints="formErrors.m_divisi_id" :check="false" label="Divisi "
        placeholder="Pilih Divisi " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_divisi`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true' AND this.m_branch_id='${values.m_branch_id}'`,
      transform: false
    }
  }" />

    </div> -->
    <div>
      <FieldSelect
        class="w-full !mt-3"
        :bind="{ disabled: !actionText, clearable: true }"
        :value="values.m_level_posisi_id" 
        @input="v => { values.m_level_posisi_id = v ? parseInt(v) : null; }"
        @update:valueFull="obj => { values.m_level_posisi_id = obj ? parseInt(obj.id) : null; }"
        :errorText="formErrors.m_level_posisi_id ? 'failed' : ''" 
        :hints="formErrors.m_level_posisi_id"
        valueField="id" displayField="level_name"
        :api="{
          url: `${store.server.url_backend}/operation/m_level_posisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest: true,
            transform: false,
            join: false
          }
        }"
        fa-icon="caret-down" label="Level" placeholder="Pilih Level" :check="false"
      />
    </div>

     <div>
       <FieldSelect
         :bind="{ disabled: !actionText, clearable: true }"
         class="w-full mt-3"
         :value="values.m_divisi_id"
         @input="v => values.m_divisi_id = v ? parseInt(v) : null"
         :errorText="formErrors.m_divisi_id ? 'failed' : ''"
         label="Divisi"
         placeholder="Pilih Divisi"
         :hints="formErrors.m_divisi_id"
         :api="{
           url: `${store.server.url_backend}/operation/m_divisi`,
           headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
           params: {
             scopes: 'Name',
             where: `this.is_active = 'true'`
           }
         }"
         valueField="id"
         displayField="value"
         :check="false"
       />
     </div>

    <!-- TYPE NIlai -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.type"
        @input="v => values.type = v" :errorText="formErrors.type ? 'failed' : ''" label="Tipe Penilaian"
        placeholder="Pilih Tipe Penilaian" :hints="formErrors.type" :api="{
                      url: `${store.server.url_backend}/operation/m_general`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where:`this.group='TIPE_PENILAIAN' AND this.is_active='true'`,
                        join:true, 
                        selectfield: 'this.id, this.code, this.value, this.is_active'
                      }
                  }" valueField="id" displayField="value" :check="true" />

    </div>

    <!-- DESKRIPSI -->
    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.deskripsi"
        :errorText="formErrors.deskripsi?'failed':''" @input="v=>values.deskripsi=v" type="textarea"
        :hints="formErrors.deskripsi" label="deskripsi" placeholder="Tuliskan deskripsi" :check="true" />
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

  </div>

  <div class="p-4">
    <!-- Add Button -->
    <div class="flex justify-end mb-4">
      <button
      @click="addDetail"
      type="button"
      v-show="actionText"
      class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg flex items-center shadow-md transition-all"
    >
      <icon fa="plus" size="sm mr-2" /> Tambah Assessment
    </button>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-xl">No.
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nama Indicator <span class="text-red-500">*</span>
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Kategori <span class="text-red-500">*</span>
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Bobot <span class="text-red-500">*</span>
            </th>
            <th v-show="actionText"
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-xl">Aksi
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          <template v-if="detailArr.length > 0">
            <template v-for="(item, index) in detailArr" :key="index">
              <!-- Main Row -->
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ index + 1 }}.</td>

                <td class="px-4 py-4 whitespace-nowrap">
                  <FieldX @input="v => item.nama_assessment = v" :value="item.nama_assessment"
                    :error-text="formErrors.nama_assessment" :bind="{ readonly: !actionText }"
                    placeholder="Masukkan Nama Assessment" class="w-full" label="" :check="false" />
                </td>

                <td class="px-4 py-4 whitespace-nowrap">
                  <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="w-full" :value="item.kategori"
                    @input="v => item.kategori = v" :errorText="formErrors.kategori ? 'failed' : ''"
                    :hints="formErrors.kategori" label="" placeholder="Pilih Kategori Penilaian" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      where: `this.group='KATEGORI_PENILAIAN' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" valueField="id" displayField="value" :check="false" />
                </td>

                <td class="px-4 py-4 whitespace-nowrap">
                  <FieldNumber @input="v => item.bobot = v" :value="item.bobot" :error-text="formErrors.bobot"
                    :bind="{ readonly: !actionText }" placeholder="Masukkan Bobot" class="w-full" :check="false"
                    label="" />
                </td>

                <td class="px-4 py-4 whitespace-nowrap" v-show="actionText">
                  <button
                  type="button"
                  @click="removeDetail(index)"
                  class="text-red-500 hover:text-red-700 transition-colors"
                >
                  <svg width="20" height="20" viewBox="0 0 14 18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                    />
                  </svg>
                </button>
                </td>
              </tr>

              <!-- Sub Table -->
              <tr>
                <td colspan="5" class="p-0">
                  <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Sub-Detail</h4>
                    <div class="bg-white rounded-lg shadow-xs overflow-hidden">
                      <table class="w-full">
                        <thead class="bg-gray-100">
                          <tr>
                            <th
                              class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                              No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Keterangan <span class="text-red-500">*</span>
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Nilai <span class="text-red-500">*</span>
                            </th>
                            <th
                              class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                            </th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                          <!-- Inside your v-for loop for detailArr -->
                          <tr v-for="(subItem, subIndex) in item.m_assessment_kary_sub_d" :key="subIndex">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ subIndex + 1 }}.</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                              <FieldX @input="v => subItem.keterangan = v" :value="subItem.keterangan"
                                :error-text="formErrors[`keterangan_${index}_${subIndex}`]"
                                :bind="{ readonly: !actionText }" placeholder="Masukkan Keterangan" class="w-full"
                                :check="false" label="" />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                              <FieldNumber @input="v => subItem.nilai = v" :value="subItem.nilai"
                                :error-text="formErrors[`nilai_${index}_${subIndex}`]" :bind="{ readonly: !actionText }"
                                placeholder="Masukkan Nilai" label="" class="w-full" :check="false" />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                              <button v-show="actionText" type="button" @click="removeSubDetail(index, subIndex)" class="text-red-500 hover:text-red-700 transition-colors">
                                  <svg width="20" height="20" viewBox="0 0 14 18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"/>
                                  </svg>
                                </button>
                            </td>
                          </tr>

                          <!-- Add sub-detail button -->
                          <tr v-show="actionText">
                            <td colspan="4" class="px-4 py-2">
                              <button
      @click="addSubDetail(index)"
      type="button"
      class="text-blue-500 hover:text-blue-700 text-sm font-medium flex items-center"
    >
      <icon fa="plus" size="xs mr-1" /> Tambah Detail Penilaian
    </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </td>
              </tr>

            </template>
          </template>

          <tr v-else>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
              Tidak ada data untuk ditampilkan
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


  <div class="flex flex-row items-center justify-end space-x-2 p-2" v-show="actionText">
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