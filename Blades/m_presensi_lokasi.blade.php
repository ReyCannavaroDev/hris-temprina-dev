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
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))" class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="h-full">
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
          <h1 class="text-20px font-bold">Form Lokasi Absensi</h1>
          <p class="text-gray-100">Master Lokasi Absensi</p>
        </div>
      </div>
    </div>
    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldSelect 
            :bind="{ readonly: !actionText }" 
            class="col-span-12 !mt-0"
            :value="values.branch_id || values.comp_id" 
            :errorText="formErrors.comp_id || formErrors.branch_id ? 'failed' : ''"
            @input="v => { values.comp_id = v; values.branch_id = v; }" 
            :hints="formErrors.comp_id || formErrors.branch_id" 
            :check="false"
            label="Cabang"
            placeholder="Pilih Cabang"
            valueField="id" 
            displayField="name"
            :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: { 
                    'Content-Type': 'Application/json', 
                    Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                    single: true,
                    join: false,                    
                    where: `this.is_active='true'`
                }
            }"
          fa-icon="search" :check="true"
        />
      </div>
      <!-- <div>   
        <FieldPopup
            class="col-span-12 !mt-0"
            :bind="{ readonly: !actionText, multiple:true }"
            :value="values.default_user_id" @input="(v)=>values.default_user_id=v"
            :errorText="formErrors.default_user_id?'failed':''" 
            :hints="formErrors.default_user_id" 
            valueField="id" displayField="name"
            :api="{
              url: `${store.server.url_backend}/operation/default_users`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                searchfield: 'this.name,this.username,m_kary.no_tlp,m_comp.nama',
                where: `this.m_comp_id = ${values.comp_id ?? 0}`
              }
            }"
            placeholder="Pilih User" label="User" :check="false" 
            :columns="[{
              headerName: 'No',
              valueGetter:(p)=>p.node.rowIndex + 1,
              width: 60,
              sortable: false, resizable: false, filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              field: 'name',
              headerName:  'Nama Karyawan',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'm_comp.nama',
              headerName:  'Nama Company',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'username',
              headerName:  'Username',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'm_kary.no_tlp',
              headerName:  'Nomer Telepon',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            ]"
          />
      </div> -->
      <div>
        <FieldX  
            class="col-span-12 !mt-0"
            :bind="{ readonly: !actionText }"
            :value="values.nama" :errorText="formErrors.nama?'failed':''"
            @input="v=>values.nama=v"
            :hints="formErrors.nama"
            label="Nama Lokasi"
            placeholder="Tuliskan Nama Lokasi"
            :check="false" />
      </div>
      <div>
        <FieldX :bind="{ readonly: !actionText }" type="number" label=""  class="w-full !mt-0"
            :value="values.lat" :errorText="formErrors.lat ? 'failed' : ''"
            @input="v=>values.lat=v" :hints="formErrors.lat" 
            @change="(e)=>{
              if(values.long && values.lat){  
                values.geo_checkin = `POINT(${values.long} ${values.lat})`
              }
            }"
            :check="false"
            label="Latitude"
            placeholder="Tuliskan Latitude"
          />
      </div>
      <div>
        <FieldX :bind="{ readonly: !actionText }"type="number" label=""  class="w-full !mt-0"
            :value="values.long" :errorText="formErrors.long ? 'failed' : ''"
            @input="v=>values.long=v" :hints="formErrors.long" 
            @change="(e)=>{
              if(values.long && values.lat){  
                values.geo_checkin = `POINT(${values.long} ${values.lat})`
              }
            }"
            :check="false"
            label="Longtitude"
            placeholder="Tuliskan Longtitude"
          />
      </div>
      <div>
        <FieldGeo class="w-full !mt-3"
          :bind="{ readonly: !actionText, search:true}"  
          @input="(v)=>{values.geo_checkin=v}"
          :center="[-7.3244677, 112.7550714]"
          :errorText="formErrors.geo_checkin?'failed':''" 
          :hints="formErrors.geo_checkin"
          geostring="POINT(112.7550714 -7.3244677)"
          :value="values.geo_checkin" Label="Titik lokasi" placeholder="Pilih Titik Lokasi" fa-icon="map-marker-alt" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3"
          :value="values.is_active" @input="v=>values.is_active=v"
          :errorText="formErrors.is_active?'failed':''" 
          :hints="formErrors.is_active"
          valueField="id" displayField="key"
          :options="[{'id' : 1 , 'key' : 'Active'},{'id': 0, 'key' : 'InActive'}]"
          placeholder="Pilih Status" label="Status" :check="false"
        />
      </div>

      <!-- SECTION DAFTAR IP KANTOR (DETAIL INPUT) -->
      <div class="<md:col-span-1 col-span-3 bg-gray-50 p-4 rounded-md border border-gray-200 mt-2">
        <div class="flex justify-between items-center mb-3">
          <div>
            <h2 class="text-sm font-bold text-gray-700 flex items-center gap-1.5">
              <icon fa="network-wired" class="text-blue-600" />
              Daftar IP Kantor (Filter Akses Accept / Deny)
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
              Atur alamat IP kantor untuk validasi presensi. Pilih <b>Accept</b> untuk IP yang diizinkan (On Scope) atau <b>Deny</b> untuk IP yang ditolak (misal IP Server, Mikrotik/Gateway).
            </p>
          </div>
          <button 
            type="button" 
            v-show="actionText" 
            @click="addIp" 
            class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold px-3 py-1.5 rounded flex items-center gap-1 shadow-sm transition-transform duration-200 transform hover:-translate-y-0.5"
          >
            <icon fa="plus" />
            Tambah IP
          </button>
        </div>

        <div style="-ms-overflow-style: none; scrollbar-width: thin;" class="overflow-x-auto">
          <table class="table-auto border border-[#CACACA] w-full bg-white rounded">
            <thead>
              <tr class="bg-[#f8f8f8] border-b border-[#CACACA]">
                <th class="p-2 text-center text-xs font-semibold text-gray-600 border-r border-[#CACACA] w-[50px]">No.</th>
                <th class="p-2 text-left text-xs font-semibold text-gray-600 border-r border-[#CACACA] w-[35%]">Alamat IP Kantor <span class="text-red-500">*</span></th>
                <th class="p-2 text-center text-xs font-semibold text-gray-600 border-r border-[#CACACA] w-[150px]">Access (Akses) <span class="text-red-500">*</span></th>
                <th class="p-2 text-left text-xs font-semibold text-gray-600 border-r border-[#CACACA]">Keterangan (Opsional)</th>
                <th class="p-2 text-center text-xs font-semibold text-gray-600 w-[70px]" v-show="actionText">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in ipList" :key="idx" class="border-b border-[#CACACA] hover:bg-gray-50">
                <td class="p-2 text-center text-xs text-gray-600 border-r border-[#CACACA]">
                  {{ idx + 1 }}.
                </td>
                <td class="p-2 border-r border-[#CACACA]">
                  <input 
                    type="text" 
                    v-model="item.ip" 
                    :disabled="!actionText"
                    @input="onIpInput(item, $event)"
                    placeholder="Contoh: 180.252.10.1 atau 192.168.1.0/24"
                    class="w-full text-xs p-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:outline-none font-mono"
                  />
                </td>
                <td class="p-2 border-r border-[#CACACA] text-center">
                  <select 
                    v-model="item.access" 
                    :disabled="!actionText"
                    class="w-full text-xs p-1.5 border rounded font-semibold focus:outline-none focus:ring-1"
                    :class="item.access === 'deny' ? 'bg-red-50 text-red-700 border-red-300 focus:ring-red-400' : 'bg-green-50 text-green-700 border-green-300 focus:ring-green-400'"
                  >
                    <option value="accept">Accept (Izinkan)</option>
                    <option value="deny">Deny (Tolak)</option>
                  </select>
                </td>
                <td class="p-2 border-r border-[#CACACA]">
                  <input 
                    type="text" 
                    v-model="item.note" 
                    :disabled="!actionText"
                    placeholder="Contoh: IP Server / Mikrotik / ISP Utama"
                    class="w-full text-xs p-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:outline-none"
                  />
                </td>
                <td class="p-2 text-center" v-show="actionText">
                  <button 
                    type="button" 
                    @click="removeIp(idx)" 
                    title="Hapus IP"
                    class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors"
                  >
                    <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="ipList.length === 0">
                <td colspan="5" class="p-4 text-center text-xs text-gray-400 italic">
                  Belum ada IP kantor yang ditambahkan. Klik tombol <b>+ Tambah IP</b> di atas jika lokasi ini divalidasi via IP kantor.
                </td>
              </tr>
            </tbody>
          </table>
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