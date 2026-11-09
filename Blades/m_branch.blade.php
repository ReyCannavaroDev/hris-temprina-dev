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
      <RouterLink v-if="data.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))"
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
        <h1 class="text-20px font-bold">Form Cabang</h1>
        <p class="text-gray-100">Master Cabang</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.m_comp_id"
        @input="v=>{
                      if (v) {
                          values.m_comp_id=v       
                        } else {
                          values.m_subcomp_id = null;
                        }
                    }" @update:valueFull="obj => {
                        if (obj) {
                          values.m_comp_id=obj.id,
                          values.m_subcomp_id = null;
                        } else {
                          values.m_comp_id=null;
                        }
                      }" :errorText="formErrors.m_subcomp_id?'failed':''" :hints="formErrors.m_subcomp_id"
        valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_comp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest: true,
                transform: false,
                join:true,
                where: `this.is_active='true'`
              }
          }" placeholder="Pilih SBU" label="SBU" fa-icon="sort-desc" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText || !values.m_comp_id , clearable:true }" class="w-full !mt-3"
        :value="values.m_subcomp_id" @input="v=>values.m_subcomp_id=v" :errorText="formErrors.m_subcomp_id?'failed':''"
        :hints="formErrors.m_subcomp_id" valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_subcomp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest: true,
                transform: false,
                join:true,
                searchfield: 'this.id, this.m_comp_id, this.is_active, this.name',
                where: `this.is_active='true' AND this.m_comp_id='${values.m_comp_id}'`
              }
          }" placeholder="Pilih SUB" fa-icon="sort-desc" label="SUB" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt-3" :value="values.code"
        :errorText="formErrors.code?'failed':''" @input="v=>values.code=v" :hints="formErrors.code" label="Kode"
        placeholder="Kode |  Auto Generate By Sistem" :check="false" />
    </div>
    <!-- <div>   
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3"
          :value="values.cost_centre_id" @input="v=>{
            if(v){
              values.cost_centre_id=v
            }else{
              values.cost_centre_id=null
            }
          }"
          :errorText="formErrors.cost_centre_id?'failed':''" 
          :hints="formErrors.cost_centre_id"
          valueField="id" displayField="value1"
          :api="{
              url: `${store.server.url_backend}/operation/m_general`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest: true,
                transform: false,
                join:true,
                where:'this.is_active=true',
                scopes:'filterByGroup',
                group: 'cost centre',
                searchfield: 'this.id, this.group, this.is_active, this.value1'
              }
          }"
          placeholder="Pilih Cost Centre" label="Cost Centre" :check="true"
        /> 
      </div> -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.name"
        :errorText="formErrors.name?'failed':''" @input="v=>values.name=v" :hints="formErrors.name" label="Nama"
        placeholder="Nama | Ex : Cabang A" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.no_rek_cabang"
        :errorText="formErrors.no_rek_cabang?'failed':''" @input="v=>values.no_rek_cabang=v" :hints="formErrors.no_rek_cabang" label="No. Rekening Cabang"
        placeholder="No. Rekening Cabang" :check="false" />
    </div>


    <!-- PROVINSI -->

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.province_id"
        :errorText="formErrors.province_id?'failed':''" @input="v=>{
                      if (v) {
                          values.province_id=v       
                        } else {
                          values.city_id = null,
                          values.district_id = null,
                          values.postcode = null
                        }
                    }" @update:valueFull="obj => {
                        if (obj) {
                          values.province_id=obj.id,
                          values.city_id = null,
                          values.district_id = null,
                          values.postcode = null
                        } else {
                          values.province_id=null;
                        }
                      }" :hints="formErrors.province_id" label="Provinsi" placeholder="Pilih Provinsi" valueField="id"
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
        :errorText="formErrors.city_id?'failed':''" @input="v=>{
                      if (v) {
                          values.city_id=v       
                        } else {
                          values.district_id = null,
                          values.postcode = null
                        }
                    }" @update:valueFull="obj => {
                        if (obj) {
                          values.city_id=obj.id,
                          values.district_id = null,
                          values.postcode = null
                        } else {
                          values.city_id=null;
                        }
                      }" :hints="formErrors.city_id" label="Kota" placeholder="Pilih Kota" valueField="id"
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
                      provinsi_id: values.province_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
    </div>

    <!-- KECAMATAN -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.district_id"
        @input="v=>values.district_id=v" :errorText="formErrors.district_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.postcode = ''
                  }" :hints="formErrors.district_id" label="Kecamatan" placeholder="Pilih Kecamatan" valueField="id"
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

    <!-- ALAMAT -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.address"
        :errorText="formErrors.address?'failed':''" @input="v=>values.address=v" :hints="formErrors.address"
        :check="false" type="textarea" label="Alamat" placeholder="Alamat" />
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

  <div class="p-2">
    <div class="flex flex-col items-stretch mt-2 mx-4">
      <div class="content-end flex">
        <button
        @click="addDetail"
        type="button"
        v-show="actionText"
        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold ml-2 px-2 py-1 rounded-md flex items-center justify-center mt-2"
      >
        <icon fa="plus" size="sm mr-0.5" /> Tambah Presensi Lokasi
      </button>
      </div>
    </div>

    <div class="mx-4">
      <table class="w-full overflow-x-auto mt-2">
        <thead>
          <tr class="border">
            <td class="border text-black font-bold text-capitalize px-2 text-center w-[1%]">No.</td>
            <td class="border text-black font-bold text-capitalize px-2 text-center w-[10%]">
              Nama Lokasi <label class="text-red-500">*</label>
            </td>

            <td class="border text-black font-bold text-capitalize px-0 text-center w-[10%]">
              Long <label class="text-red-500">*</label>
            </td>

            <td class="border text-black font-bold text-capitalize px-0 text-center w-[10%]">
              Lat <label class="text-red-500">*</label>
            </td>
            
            <td class="border text-black font-bold text-capitalize px-0 text-center w-[10%]">
              Titik Lokasi <label class="text-red-500">*</label>
            </td>
            <td class="border text-black font-bold text-capitalize px-0 text-center w-[2%]">
              Status <label class="text-red-500">*</label>
            </td>
            <td v-show="actionText" class="border text-black font-bold text-capitalize px-2 text-center w-[1%]">#</td>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(item, index) in detailArr" :key="index" class="border" v-if="detailArr.length > 0">
            <td class="p-2 text-center border pt-4 pb-4">{{ index + 1 }}.</td>

            <td class="p-2 pt-4 pb-4 border">
              <FieldX @input="(v) => item.nama = v" :value="item.nama" :error-text="formErrors.nama"
                :bind="{ readonly: !actionText }" placeholder="Masukan Nama Lokasi" label="" class="w-full !mt-3"
                :check="false" />
            </td>

            

            <td class="p-2 pt-4 pb-4 border">
              <FieldX :bind="{ readonly:!actionText}" type="number" label="" class="w-full !mt-3" :value="item.long"
                :errorText="formErrors.long ? 'failed' : ''" @input="v=>item.long=v" :hints="formErrors.long" @change="(e)=>{
              if(item.long && item.lat){  
                item.geo_checkin = `POINT(${item.long} ${item.lat})`
              }
            }" :check="false" label="" placeholder="Tuliskan Longtitude" />
            </td>

            <td class="p-2 pt-4 pb-4 border">
              <FieldX :bind="{ readonly:!actionText}" type="number" label="" class="w-full !mt-3" :value="item.lat"
                :errorText="formErrors.lat ? 'failed' : ''" @input="v=>item.lat=v" :hints="formErrors.lat" @change="(e)=>{
              if(item.long && item.lat){  
                item.geo_checkin = `POINT(${item.long} ${item.lat})`
              }
            }" :check="false" label="" placeholder="Tuliskan Latitude" />
            </td>

            <td class="p-2 pt-4 pb-4 border" >
              <FieldGeo class="w-full !mt-3" :bind="{ readonly: !actionText, search:true}"
                @input="(v)=>{item.geo_checkin=v}" :center="[-7.3244677, 112.7550714]"
                :errorText="formErrors.geo_checkin?'failed':''" :hints="formErrors.geo_checkin"
                geostring="POINT(112.7550714 -7.3244677)" :value="item.geo_checkin" Label=""
                placeholder="Pilih Titik Lokasi" fa-icon="map-marker-alt" :check="false" />
            </td>
            
            <td class="p-2 pt-4 pb-4 border"> 
              <div class="flex justify-center">
                      <label class="relative h-6 w-6 cursor-pointer">
                        <input
                          type="checkbox"
                          class="peer hidden"
                          v-model="item.is_active"
                          :disabled="!actionText || item.is_primary"

                        />
                        <span
                          class="absolute inset-0 flex items-center justify-center border border-gray-300 rounded-md bg-white peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-disabled:opacity-50"
                        >
                          <svg
                            v-if="item.is_active"
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
            </td>

            <td class="p-2 border pt-4 pb-4 border" v-show="actionText">
              <div class="flex justify-center">
                <button type="button" @click="removeDetail(index)">
                <svg
                  width="14"
                  height="18"
                  viewBox="0 0 14 18"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    id="Vector"
                    d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                    fill="#F24E1E"
                  />
                </svg>
              </button>
              </div>
            </td>
          </tr>
           <tr v-else class="text-center border">
                  <td colspan="6" class="py-[20px]">
                    No data to show
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