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
        <h1 class="text-20px font-bold">Form Company Outsourcing</h1>
        <p class="text-gray-100">Master Company Outsourcing</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: !actionText}" class="w-full !mt-3" :value="values.code"
        :errorText="formErrors.code?'failed':''" @input="v=>values.code=v" :hints="formErrors.code" label="Kode"
        placeholder="Tuliskan Kode" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.name"
        :errorText="formErrors.name?'failed':''" @input="v=>values.name=v" :hints="formErrors.name" label="Nama"
        placeholder="Nama | Ex : Company A" :check="false" /> 
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.address"
        :errorText="formErrors.address?'failed':''" @input="v=>values.address=v" :hints="formErrors.address"
        :check="false" type="textarea" label="Alamat" placeholder="Tuliskan Alamat" />
    </div>

    <!-- PROVINSI -->

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.prov_id"
       :errorText="formErrors.prov_id?'failed':''" 
                    @input="v=>{
                      if (v) {
                          values.prov_id=v       
                        } else {
                          values.city_id = null,
                          values.district_id = null,
                          values.postcode = null
                        }
                    }" 
                    @update:valueFull="obj => {
                        if (obj) {
                          values.prov_id=obj.id,
                          values.city_id = null,
                          values.district_id = null,
                          values.postcode = null
                        } else {
                          values.prov_id=null;
                        }
                      }" 
                      :hints="formErrors.prov_id" label="Provinsi" placeholder="Pilih Provinsi" valueField="id"
                      displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genProvinsi',
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

    </div>
    <!-- KOTA -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.city_id"
         :errorText="formErrors.city_id?'failed':''" 
         @input="v=>{
                      if (v) {
                          values.city_id=v       
                        } else {
                          values.district_id = null,
                          values.postcode = null
                        }
                    }" 
                    @update:valueFull="obj => {
                        if (obj) {
                          values.city_id=obj.id,
                          values.district_id = null,
                          values.postcode = null
                        } else {
                          values.city_id=null;
                        }
                      }" 
                  :hints="formErrors.city_id" label="Kota" placeholder="Pilih Kota" valueField="id"
                  displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKota',
                      provinsi_id: values.prov_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
    </div>
    <!-- KECAMATAN -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.district_id"
        @input="v=>values.district_id=v" :errorText="formErrors.district_id?'failed':''" 
        @update:valueFull="(objVal)=>{
                    values.postcode = ''
                  }" 
                  
                  :hints="formErrors.district_id" label="Kecamatan" placeholder="Pilih Kecamatan" valueField="id"
        displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKecamatan',
                      kota_id: values.city_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.postcode"
        :errorText="formErrors.postcode?'failed':''" @input="v=>values.postcode=v" :hints="formErrors.postcode"
        label="Kode Pos" placeholder="Tuliskan Kode Pos" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.nama_npwp"
        :errorText="formErrors.nama_npwp?'failed':''" @input="v=>values.nama_npwp=v" :hints="formErrors.nama_npwp"
        label="Nama NPWP" placeholder="Tuliskan Nama NPWP" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.npwp"
        :errorText="formErrors.npwp?'failed':''" @input="v=>values.npwp=v" @keydown="npwp_key" :hints="formErrors.npwp"
        label="Nomor NPWP" placeholder="Tuliskan Nomor NPWP" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.phone1"
        :errorText="formErrors.phone1?'failed':''" @input="v=>values.phone1=v" @keydown="preventInvalidInput"
        :hints="formErrors.number" label="Telp 1" placeholder="Tuliskan Telp 1" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.phone2"
        :errorText="formErrors.phone2?'failed':''" @input="v=>values.phone2=v" :hints="formErrors.number" label="Telp 2"
        @keydown="preventInvalidInput" placeholder="Tuliskan Telp 2" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.email"
        :errorText="formErrors.email?'failed':''" @input="v=>{
            validateEmail(v)
            }" :hints="formErrors.email" placeholder="Masukan Email" label="Email" type="email" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.website"
        :errorText="formErrors.website?'failed':''" @input="v=>{validateWebsite(v)}" :hints="formErrors.website"
        label="Website" placeholder="Tuliskan Website" :check="false" />
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