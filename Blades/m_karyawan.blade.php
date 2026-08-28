<!-- LANDING -->
@if(!$req->has('id'))
  <div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
    <div class="flex justify-between items-center px-2.5 py-1">
      <div class="flex items-center gap-x-4">
        <p>Filter Status :</p>
        <div class="flex gap-x-2">
          <button @click="filterShowData(true,1)"
            :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'"
            class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
          <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
          <button @click="filterShowData(false,2)"
            :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'"
            class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
        </div>
      </div>
      <div v-show="data?.can_create">
        <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
          class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
          Create New
        </RouterLink>
        <button @click="syncData"
          class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md mx-2 py-1 px-2">
          Sync to ERP
        </button>
      </div>
    </div>
    <hr>
    <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
      <!-- <template #header>
          </template> -->
    </TableApi>
  </div>
@else
  @verbatim

    <div class="flex flex-col gap-y-2 scroll-auto h-full">
      <div class="flex gap-x-1 px-2">
        <div class="flex flex-col border rounded shadow-sm <md:w-full w-full bg-white ">

          <!-- HEADER START -->
          <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
            <div class="flex items-center">
              <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
                @click="onBack" />
              <div>
                <h1 class="text-20px font-bold">Form Karyawan</h1>
                <p class="text-gray-100">Master Karyawan</p>
              </div>
            </div>
          </div>
          <!-- HEADER END -->
          <div class="flex px-6 items-stretch w-full text-sm overflow-x-auto">
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 0}" @click="activeTabIndex = 0">
              Informasi
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 8}" @click="activeTabIndex = 8">
              Jabatan
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 9}" @click="activeTabIndex = 9">
              Lokasi
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 1}" @click="activeTabIndex = 1">
              Pendidikan
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 hover:text-yellow-500 hover:text-yellow-500 duration-300 border-gray-100 p-3"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 2}" @click="activeTabIndex = 2">
              Keluarga
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 3}" @click="activeTabIndex = 3">
              Pelatihan
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 4}" @click="activeTabIndex = 4">
              Prestasi
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 5}" @click="activeTabIndex = 5">
              Organisasi
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 6}" @click="activeTabIndex = 6">
              Bahasa
            </button>
            <button
              class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
              :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 7}" @click="activeTabIndex = 7">
              Pengalaman Kerja
            </button>
          </div>
          <div v-show="activeTabIndex === 0">
            <!-- Form Informasi -->
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
              <!-- NOT PROFILE -->
              <!-- SBU -->
              <div v-show="!isProfile">
                <label>SBU<label class="text-red-500 space-x-0 pl-0"></label></label>
                <!-- <FieldSelect :bind="{ disabled:true}" class="w-full !mt-0" :value="values.m_comp_id" @input="v=>{ -->
                <FieldSelect :bind="{ disabled: true}" class="w-full !mt-0" :value="values.m_comp_id" @input="v=>{
                          if(v){
                            values.m_comp_id=v
                          }else{
                            values.m_comp_id=null
                          }
                        }" @update:valueFull="obj => {
                            if (obj) {
                              values.m_comp_id = obj.id; 
                            } else {
                              values.m_comp_id = null;
                            }
                          }" :errorText="formErrors.m_comp_id?'failed':''" :hints="formErrors.m_comp_id" valueField="id"
                  displayField="name" :api="{
                            url: `${store.server.url_backend}/operation/m_comp`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              simplest:true,
                              transform:false,
                              join:false,
                            }
                      }" placeholder="SBU" label="" fa-icon="sort-desc" :check="false" />
              </div>

              <!-- SUB -->
              <div v-show="!isProfile">
                <label>SUB<label class="text-red-500 space-x-0 pl-0"></label></label>
                <!-- <FieldSelect :bind="{ disabled: true }" class="w-full !mt-0" :value="values.m_subcomp_id" @input="v=>{ -->
                <FieldSelect :bind="{ disabled: true }" class="w-full !mt-0" :value="values.m_subcomp_id" @input="v=>{
                          if(v){
                            values.m_subcomp_id=v
                          }else{
                            values.m_subcomp_id=null
                          }
                          }" @update:valueFull="obj => {
                            if (obj) {
                              values.m_subcomp_id = obj.id; 
                            } else {
                              values.m_subcomp_id = null;
                            }
                          }" :errorText="formErrors.m_subcomp_id?'failed':''" :hints="formErrors.m_subcomp_id"
                  valueField="id" displayField="name" :api="{
                            url: `${store.server.url_backend}/operation/m_subcomp`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              simplest:true,
                              transform:false,
                              join:false,
                            }
                      }" placeholder="SUB" label="" fa-icon="sort-desc" :check="false" />

              </div>

              <!-- BRANCH -->
              <div v-show="!isProfile">
                <label>BRANCH<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: true , clearable:true }" class="w-full !mt-0" :value="values.m_branch_id"
                  @input="v=>{
                          if(v){
                            values.m_branch_id=v
                          }else{
                            values.m_branch_id=null

                          }
                        }" @update:valueFull="obj => {
                            if (obj) {
                              values.m_branch_id = obj.id; 

                            } else {
                              values.m_branch_id = null;
                            }
                          }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id"
                  valueField="id" displayField="name" :api="{
                            url: `${store.server.url_backend}/operation/m_branch`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              simplest:true,
                              transform:false,
                              join:false,
                            }
                      }" placeholder="BRANCH" label="" fa-icon="sort-desc" :check="false" />
              </div>

              <!-- DIVISI -->
              <div v-show="!isProfile">
                <label>Divisi<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full mt-3" :value="values.m_divisi_id"
                  @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''" @update:valueFull="(objVal)=>{
                              values.m_dept_id = null
                            }" label="" placeholder="Pilih Divisi" :hints="formErrors.m_divisi_id" :api="{
                                url: `${store.server.url_backend}/operation/m_divisi`,
                                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                params: {
                                  scopes:'Name',
                                  //simplest:true,
                                  //where: `this.is_active = 'true'`
                                }
                            }" valueField="id" displayField="name.value" :check="false" />

              </div>


              <div v-show="!isProfile">
                <label>Posisi<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full mt-3" :value="values.m_posisi_id"
                  @input="v=>values.m_posisi_id=v" @update:valueFull="(items)=>{
                              values.m_standart_gaji_id = null
                            }" :errorText="formErrors.m_posisi_id?'failed':''" label="" placeholder="Pilih Posisi"
                  :hints="formErrors.m_posisi_id" :api="{
                                url: `${store.server.url_backend}/operation/m_posisi`,
                                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            }" valueField="id" displayField="name" :check="false" />
              </div>

              <div v-show="!isProfile">
                <label>Zona<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full mt-3" :value="values.m_zona_id"
                  @input="v=>{
                              values.m_zona_id=v
                              setStandartGaji()
                              }" :errorText="formErrors.m_zona_id?'failed':''" @update:valueFull="(items)=>{
                              values.m_standart_gaji_id=null
                            }" label="" placeholder="Pilih Zona" :hints="formErrors.m_zona_id" :api="{
                                url: `${store.server.url_backend}/operation/m_zona`,
                                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            }" valueField="id" displayField="nama" :check="false" />

              </div>

              <div>
                <label>Finger ID<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.finger_id"
                  @input="(v)=>values.finger_id=v" :errorText="formErrors.finger_id?'failed':''"
                  :hints="formErrors.finger_id" placeholder="" label="Masukkan Finger ID" fa-icon="" :check="false" />
              </div>



              <div v-show="!isProfile">
                <label>Status Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="w-full mt-3"
                  :value="values.status_kary_id" @input="v=>{
                              if(v){
                                values.status_kary_id=v
                              }else{
                                values.status_kary_id=null
                                values.m_company_outsourcing_id=null
                              }
                            }" @update:valueFull="obj => {
                                if (obj) {
                                  values.status_kary_id = obj.id; 
                                  values.m_company_outsourcing_id = null; 
                                } else {
                                  values.status_kary_id = null;
                                }
                              }" :errorText="formErrors.status_kary_id ? 'failed' : ''" :hints="formErrors.is_active"
                  label="" placeholder="Pilih Status Karyawan" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                where: `this.is_active = true AND this.group='STATUS KARYAWAN'`
                              }
                            }" :check="false" />
              </div>

              <div v-show="values.status_kary_id === 953">
                <label>PT.Outsourcing<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3"
                  :value="values.m_company_outsourcing_id" :errorText="formErrors.m_company_outsourcing_id?'failed':''"
                  @input="(v)=>m_company_outsourcing_id=v" :hints="formErrors.m_company_outsourcing_id" label=""
                  placeholder="PT.Outsourcing" valueField="id" displayField="name" :api="{
                                url: `${store.server.url_backend}/operation/m_company_outsourcing`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                }
                              }" :check="false" />
              </div>

              <div>
                <label>Fingerprint ID<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3"
                  :value="values.m_fingerprint_machine_id" :errorText="formErrors.m_fingerprint_machine_id?'failed':''"
                  @input="(v)=>m_fingerprint_machine_id=v" :hints="formErrors.m_fingerprint_machine_id" label=""
                  placeholder="Mesin Finger" valueField="id" displayField="name" :api="{
                                url: `${store.server.url_backend}/operation/m_fingerprint_machine`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                }
                              }" :check="false" />
              </div>

              <div class="pl-3 flex flex-col justify-center !mt-3">
                <div class="text-xs text-gray-500 mb-1">
                  Status
                </div>
                <div class="flex w-40">
                  <div class="flex-auto">
                    <i class="text-red-500">InActive</i>
                  </div>
                  <div class="flex-auto">
                    <input class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300
                    before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full
                    before:bg-transparent before:content-['']
                    after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full
                    after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)]
                    after:transition-[background-color_0.2s,transform_0.2s] after:content-['']
                    checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px]
                    checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5
                    checked:after:rounded-full checked:after:border-none checked:after:bg-primary
                    checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)]
                    hover:cursor-pointer focus:outline-none focus:ring-0" type="checkbox"
                      :class="{'after:bg-gray-500': values.is_active === false}" role="switch" id="is_active_for_click"
                      :disabled="!actionText" v-model="values.is_active" />
                  </div>
                  <div class="flex-auto">
                    <i class="text-green-500">Active</i>
                  </div>
                </div>
              </div>


              <div class="pl-3 flex flex-col justify-center !mt-3">
                <div class="text-xs text-gray-500 mb-1">
                  Can Outscope
                </div>
                <div class="flex w-40">
                  <div class="flex-auto">
                    <i class="text-red-500">Tidak</i>
                  </div>

                  <div class="flex-auto">
                    <input class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300
                              before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full
                              before:bg-transparent before:content-['']
                              after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full
                              after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)]
                              after:transition-[background-color_0.2s,transform_0.2s] after:content-['']
                              checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px]
                              checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5
                              checked:after:rounded-full checked:after:border-none checked:after:bg-primary
                              checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)]
                              hover:cursor-pointer focus:outline-none focus:ring-0" type="checkbox"
                      :class="{'after:bg-gray-500': values.can_outscope === false}" role="switch" id="can_outscope"
                      :disabled="!actionText" v-model="values.can_outscope" />
                  </div>

                  <div class="flex-auto">
                    <i class="text-green-500">Ya</i>
                  </div>
                </div>
              </div>



              <!-- <div v-show="!isProfile">
                        <FieldPopup v-show="values['tipe_jam_kerja.value'] == 'OFFICE'" :bind="{ readonly: true }"
                          class="w-full mt-3" :value="values.t_jadwal_kerja_id" @input="(v)=>values.t_jadwal_kerja_id=v"
                          :errorText="formErrors.t_jadwal_kerja_id?'failed':''" :hints="formErrors.t_jadwal_kerja_id"
                          valueField="id" displayField="nomor" @update:valueFull="(objVal)=>{  
                            values.t_jadwal_kerja_ket = objVal.keterangan
                            }" :api="{
                              url: `${store.server.url_backend}/operation/t_jadwal_kerja`,
                              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                              params: {
                                simplest:true,
                                where: `this.status = 'POSTED'`,
                                searchfield:'this.id, this.nomor, this.keterangan',
                              }
                            }" placeholder="Pilih Jadwal Kerja" label="OFFICE" :check="false" :columns="[{
                              headerName: 'No',
                              valueGetter:(p)=>p.node.rowIndex + 1,
                              width: 60,
                              sortable: false, resizable: false, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              field: 'nomor',
                              sortable: false, resizable: true, filter: 'ColFilter',
                              cellClass: ['border-r', '!border-gray-200', 'justify-center']
                            },
                            {
                              flex: 1,
                              field: 'keterangan',
                              sortable: false, resizable: true, filter: 'ColFilter', wrapText: true,
                              cellClass: ['border-r', '!border-gray-200', 'justify-center']
                            }
                            ]" />
                      </div> -->
            </div>
            <!-- Data Karyawan -->
            <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Data Karyawan</h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
              <!-- <div>
                        <FieldX :bind="{ readonly: !actionText }" type="text" class="w-full mt-3" :value="values.nik"
                          label="No. KTP" placeholder="No. KTP"
                          :errorText="formErrors.nik?'failed':''" @input="v=>values.nik=v" :hints="formErrors.nik"
                          :check="false" />
                      </div> -->
              <div class="flex gap-x-1 w-full">
                <div class="flex-1">
                  <label>Nomor Induk Pegawai<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="text" class="w-full mt-3" :value="values.nip" label=""
                    placeholder="Nomor Induk Pegawai" :errorText="formErrors.nip?'failed':''" @input="v=>values.nip=v"
                    :hints="formErrors.nip" :check="false" />
                </div>
                <div class="flex-1">
                  <label>Nomor Registrasi<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="text" class="w-full mt-3" :value="values.no_registrasi"
                    label="" placeholder="Nomor Registrasi" :errorText="formErrors.no_registrasi?'failed':''"
                    @input="v=>values.no_registrasi=v" :hints="formErrors.no_registrasi" :check="false" />
                </div>
              </div>
              <!-- Atasan Karyawan (Dihapus / Tidak Ditampilkan) - div kosong dipertahankan agar layout grid 3 kolom tetap rapi -->
              <div></div>

              <!-- <div>
                        <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }"
                          :value="values.presensi_lokasi_default_id" @input="v=>values.presensi_lokasi_default_id=v"
                          :errorText="formErrors.presensi_lokasi_default_id?'failed':''"
                          :hints="formErrors.presensi_lokasi_default_id" label="Presensi Lokasi" valueField="id" displayField="nama"
                          :api="{
                                url: `${store.server.url_backend}/operation/presensi_lokasi`,
                                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                params: {
                                  selectfield: 'this.id, this.nama, this.lat, this.long'
                                }
                              }" placeholder="Pilih Master Lokasi" :check="false" />
                      </div> -->

              <div class="flex gap-x-1">
                <div class="flex-1">
                  <label>Nama Depan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_depan" label=""
                    placeholder="Tuliskan Nama Depan" :errorText="formErrors.nama_depan?'failed':''"
                    @input="v=>values.nama_depan=v" :hints="formErrors.nama_depan" :check="false" />
                </div>
                <div class="flex-1">
                  <label>Nama Belakang<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_belakang" label=""
                    placeholder="Tuliskan Nama Belakang" :errorText="formErrors.nama_belakang?'failed':''"
                    @input="v=>values.nama_belakang=v" :hints="formErrors.nama_belakang" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Jenis Kelamin<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.jk_id"
                    label="" placeholder="Pilih Jenis Kelamin" @input="v=>values.jk_id=v"
                    :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id" valueField="id" displayField="value"
                    :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
                                  join: true,
                                  selectfield: 'this.id, this.code, this.value, this.is_active'
                                }
                              }" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Nama Panggilan<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_panggilan" label=""
                    placeholder="Tuliskan Nama Panggilan Karyawan" :errorText="formErrors.nama_panggilan?'failed':''"
                    @input="v=>values.nama_panggilan=v" :hints="formErrors.nama_panggilan" :check="false" />
                </div>
              </div>
              <div class="flex gap-x-1">
                <div class="flex-1">
                  <label>Tempat Lahir<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.tempat_lahir" label=""
                    placeholder="Tuliskan Tempat Lahir" :errorText="formErrors.tempat_lahir?'failed':''"
                    @input="v=>values.tempat_lahir=v" :hints="formErrors.tempat_lahir" :check="false" />
                  <!-- <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                            :value="values.tempat_lahir" @input="v=>values.tempat_lahir=v"
                            :errorText="formErrors.tempat_lahir?'failed':''" :hints="formErrors.tempat_lahir" label="Tempat Lahir"
                            placeholder="Tempat Lahir" valueField="value" displayField="value" :api="{
                                  url: `${store.server.url_backend}/operation/m_general`,
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  },
                                  params: {
                                    simplest: true,
                                    transform: false,
                                    join: true,
                                    where: `this.group='KOTA'`,
                                    paginate: 1000
                                  }
                                }" :check="false" /> -->
                </div>
                <div class="flex-1">
                  <label>Tanggal Lahir<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.tgl_lahir"
                    label="" placeholder="Pilih Tanggal Lahir" :errorText="formErrors.tgl_lahir?'failed':''"
                    @input="v=>values.tgl_lahir=v" :hints="formErrors.tgl_lahir" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Alamat<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3"
                    :value="values.alamat_domisili" label="" placeholder="Tuliskan Alamat"
                    :errorText="formErrors.alamat_domisili?'failed':''" @input="v=>values.alamat_domisili=v"
                    :hints="formErrors.alamat_domisili" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Provinsi<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.provinsi_id" @input="v=>values.provinsi_id=v"
                    :errorText="formErrors.provinsi_id?'failed':''" @update:valueFull="(objVal)=>{
                                  values.kota_id = '',
                                  values.kecamatan_id = '',
                                  values.kode_pos = ''
                                }" :hints="formErrors.provinsi_id" label="" placeholder="Pilih Provinsi" valueField="id"
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

              </div>
              <div>
                <div>
                  <label>Kota<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.kota_id" @input="v=>values.kota_id=v" :errorText="formErrors.kota_id?'failed':''"
                    @update:valueFull="(objVal)=>{
                                  values.kecamatan_id = '',
                                  values.kode_pos = ''
                                }" :hints="formErrors.kota_id" label="" placeholder="Pilih Kota" valueField="id"
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
                                    provinsi_id: values.provinsi_id ?? null,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Kecamatan<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.kecamatan_id" @input="v=>values.kecamatan_id=v"
                    :errorText="formErrors.kecamatan_id?'failed':''" @update:valueFull="(objVal)=>{
                                  values.kode_pos = ''
                                }" :hints="formErrors.kecamatan_id" label="" placeholder="Pilih Kecamatan" valueField="id"
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
                                    kota_id: values.kota_id ?? null,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Kode Pos<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.kode_pos"
                    label="" placeholder="Tuliskan Kode Pos" :errorText="formErrors.kode_pos?'failed':''"
                    @input="v=>values.kode_pos=v" :hints="formErrors.kode_pos" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Nomer Telepon<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_tlp"
                    label="" placeholder="Tuliskan Nomer Telepon" :errorText="formErrors.no_tlp?'failed':''"
                    @input="v=>values.no_tlp=v" :hints="formErrors.no_tlp" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>No Telepon Lainya<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_tlp_lainnya"
                    label="" placeholder="Tuliskan Nomer Telepon Lainnya" :errorText="formErrors.no_tlp_lainnya?'failed':''"
                    @input="v=>values.no_tlp_lainnya=v" :hints="formErrors.no_tlp_lainnya" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>No Telepon Darurat<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_darurat"
                    label="" placeholder="Tuliskan Nomer Telepon Darurat" :errorText="formErrors.no_darurat?'failed':''"
                    @input="v=>values.no_darurat=v" :hints="formErrors.no_darurat" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Nama Kontak Darurat<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_kontak_darurat" label=""
                    placeholder="Tuliskan Nama Kontak Darurat" :errorText="formErrors.nama_kontak_darurat?'failed':''"
                    @input="v=>values.nama_kontak_darurat=v" :hints="formErrors.nama_kontak_darurat" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Hubungan Dengan Karyawan<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.hub_dgn_karyawan" label=""
                    placeholder="Tulis Hubungan Dengan Karyawan" :errorText="formErrors.hub_dgn_karyawan?'failed':''"
                    @input="v=>values.hub_dgn_karyawan=v" :hints="formErrors.hub_dgn_karyawan" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Agama<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.agama_id" @input="v=>values.agama_id=v" :errorText="formErrors.agama_id?'failed':''"
                    :hints="formErrors.agama_id" label="" placeholder="Pilih Agama" valueField="id" displayField="value"
                    :api="{
                                  url: `${store.server.url_backend}/operation/m_general`,
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  },
                                  params: {
                                    simplest: true,
                                    transform: false,
                                    where: `this.group='AGAMA' AND this.is_active='true'`,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Golongan Darah<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.gol_darah_id" @input="v=>values.gol_darah_id=v"
                    :errorText="formErrors.gol_darah_id?'failed':''" :hints="formErrors.gol_darah_id" label=""
                    placeholder="Pilih Golongan Darah" valueField="id" displayField="value" :api="{
                                  url: `${store.server.url_backend}/operation/m_general`,
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  },
                                  params: {
                                    simplest: true,
                                    transform: false,
                                    where: `this.group='GOLONGAN DARAH' AND this.is_active='true'`,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>

              </div>
              <div>
                <div>
                  <label>Status Pernikahan<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.status_nikah_id" @input="v=>values.status_nikah_id=v"
                    :errorText="formErrors.status_nikah_id?'failed':''" :hints="formErrors.status_nikah_id" label=""
                    placeholder="Pilih Status Pernikahan" valueField="id" displayField="value" :api="{
                                  url: `${store.server.url_backend}/operation/m_general`,
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  },
                                  params: {
                                    simplest: true,
                                    transform: false,
                                    where: `this.group='STATUS NIKAH' AND this.is_active='true'`,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>
              </div>
              <div>
                <div>
                  <label>Tanggungan<label class="text-red-500 space-x-0 pl-0"></label></label>
                  <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                    :value="values.tanggungan_id" @input="v=>values.tanggungan_id=v"
                    :errorText="formErrors.tanggungan_id?'failed':''" :hints="formErrors.tanggungan_id" label=""
                    placeholder="Pilih Tanggungan" valueField="id" displayField="value" :api="{
                                  url: `${store.server.url_backend}/operation/m_general`,
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  },
                                  params: {
                                    simplest: true,
                                    transform: false,
                                    where: `this.group='TANGGUNGAN' AND this.is_active='true'`,
                                    join: true,
                                    selectfield: 'this.id, this.code, this.value, this.is_active'
                                  }
                                }" :check="false" />
                </div>
              </div>
              <!-- <div>
                        <FieldNumber :bind="{ readonly: !actionText }" :value="values.plafond_ranap" @input="(v)=>values.plafond_ranap=v"
                          :errorText="formErrors.plafond_ranap?'failed':''" :hints="formErrors.plafond_ranap" :check="false"
                          class="w-full !mt-3" label="Plafond Rawat Inap (Tahunan)" placeholder="Plafond Rawat Inap (Tahunan)" />
                      </div> -->
              <!-- <div>

                        <FieldX :bind="{ readonly: !actionText }" label="Limit Potong" type="number" class="w-full mt-3"
                          :value="values.limit_potong?.toString()" :errorText="formErrors.limit_potong?'failed':''"
                          @input="v=>values.limit_potong=v" :hints="formErrors.limit_potong" label="Limit Potong"
                          placeholder="Limit Potong" :check="false" />

                      </div> -->
            </div>
            <!-- Sosial Media -->
            <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Media Sosial</h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.email" label="Email"
                  placeholder="Email" :errorText="formErrors.email?'failed':''" @input="v=>values.email=v"
                  :hints="formErrors.email" :check="false" />

              </div>
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.linkedin" label="LinkedIn"
                  placeholder="Linked In" :errorText="formErrors.linkedin?'failed':''" @input="v=>values.linkedin=v"
                  :hints="formErrors.linkedin" :check="false" />

              </div>
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.ig" label="Instagram"
                  placeholder="Tuliskan Instagram" :errorText="formErrors.ig?'failed':''" @input="v=>values.ig=v"
                  :hints="formErrors.ig" :check="false" />

              </div>
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.facebook" label="Facebook"
                  placeholder="Tuliskan Facebook" :errorText="formErrors.facebook?'failed':''" @input="v=>values.facebook=v"
                  :hints="formErrors.facebook" :check="false" />

              </div>
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.x" label="X"
                  placeholder="Tuliskan X" :errorText="formErrors.x?'failed':''" @input="v=>values.x=v"
                  :hints="formErrors.x" :check="false" />

              </div>
            </div>
            <!-- INFO LAIN -->
            <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Info Lain</h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">

              <div>
                <div>
                  <label>Cuti Reguler<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: false }" type="number" class="w-full mt-3" :value="values.cuti_jatah_reguler"
                    label="" placeholder="Tuliskan Jatah Cuti Reguler"
                    :errorText="formErrors.cuti_jatah_reguler?'failed':''" @input="v=>values.cuti_jatah_reguler=v"
                    :hints="formErrors.cuti_jatah_reguler" :check="false" />
                </div>
                <div>
                  <label>Sisa Regular<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.sisa_cuti_reguler"
                    label="" placeholder="Tuliskan Sisa Jatah Cuti Reguler"
                    :errorText="formErrors.sisa_cuti_reguler?'failed':''" @input="v=>values.sisa_cuti_reguler=v"
                    :hints="formErrors.sisa_cuti_reguler" :check="false" />
                </div>
              </div>

              <div>
                <div>
                  <label>Cuti Tahun Lalu<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: false }" type="number" class="w-full mt-3"
                    :value="values.cuti_jatah_tahun_lalu" label="" placeholder="Tuliskan Jatah Cuti Tahun Lalu"
                    :errorText="formErrors.cuti_jatah_tahun_lalu?'failed':''" @input="v=>values.cuti_jatah_tahun_lalu=v"
                    :hints="formErrors.cuti_jatah_tahun_lalu" :check="false" />
                </div>
                <div>
                  <label>Sisa Cuti Tahun Lalu<label class="text-red-500 space-x-0 pl-0">*</label></label>
                  <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.sisa_cuti_tahun_lalu"
                    label="" placeholder="Tuliskan Sisa Jatah Cuti Reguler"
                    :errorText="formErrors.sisa_cuti_tahun_lalu?'failed':''" @input="v=>values.sisa_cuti_tahun_lalu=v"
                    :hints="formErrors.sisa_cuti_tahun_lalu" :check="false" />
                </div>
              </div>

              <!-- <div>
                        <div>
                          <label >Cuti Masa Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
                          <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.cuti_masa_kerja"
                            label="" :errorText="formErrors.cuti_masa_kerja?'failed':''" @input="v=>values.cuti_masa_kerja=v"
                            :hints="formErrors.cuti_masa_kerja" :check="false" />
                        </div>
                        <div>
                          <label >Sisa Masa Kerja<label class="text-red-500 space-x-0 pl-0">*</label></label>
                          <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.sisa_cuti_masa_kerja"
                            label="" :errorText="formErrors.sisa_cuti_masa_kerja?'failed':''"
                            @input="v=>values.sisa_cuti_masa_kerja=v" :hints="formErrors.sisa_cuti_masa_kerja" :check="false" />
                        </div>
                      </div> -->
              <!-- <div>
                        <div>
                          <label >Cuti P24<label class="text-red-500 space-x-0 pl-0">*</label></label>
                          <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.cuti_p24" label=""
                            :errorText="formErrors.cuti_p24?'failed':''" @input="v=>values.cuti_p24=v" :hints="formErrors.cuti_p24"
                            :check="false" />
                        </div>
                        <div>
                          <label >Sisa P24<label class="text-red-500 space-x-0 pl-0">*</label></label>
                          <FieldX :bind="{ readonly: true }" type="number" class="w-full mt-3" :value="values.cuti_p24_terpakai"
                            label="" :errorText="formErrors.cuti_p24_terpakai?'failed':''" @input="v=>values.cuti_p24_terpakai=v"
                            :hints="formErrors.cuti_p24_terpakai" :check="false" />
                        </div>
                      </div> -->

              <div>
                <div>
                  <label>Tanggal Masuk Kerja
                    <!-- <label class="text-red-500 space-x-0 pl-0">*</label> -->
                  </label>
                  <FieldX :bind="{ readonly: !actionText, disabled:!actionText }" type="date" class="w-full mt-3"
                    :value="values.tgl_masuk" label="" :errorText="formErrors.tgl_masuk?'failed':''"
                    @input="v=>values.tgl_masuk=v" :hints="formErrors.tgl_masuk" :check="false" />
                </div>
                <div>
                  <label>Tanggal Pengangkatan
                    <!-- <label class="text-red-500 space-x-0 pl-0">*</label> -->
                  </label>
                  <FieldX :bind="{ readonly: !actionText, disabled:!actionText }" type="date" class="w-full mt-3"
                    :value="values.tgl_pengangkatan" label="" :errorText="formErrors.tgl_pengangkatan?'failed':''"
                    @input="v=>values.tgl_pengangkatan=v" :hints="formErrors.tgl_pengangkatan" :check="false" />
                </div>
                <div>
                  <label>Tanggal Berhenti
                    <!-- <label l class="text-red-500 space-x-0 pl-0">*</label> -->
                  </label>
                  <FieldX :bind="{ readonly: !actionText, disabled: !actionText }" type="date" class="w-full mt-3"
                    :value="values.tgl_berhenti" label="" :errorText="formErrors.tgl_berhenti?'failed':''"
                    @input="v=>values.tgl_berhenti=v" :hints="formErrors.tgl_berhenti" :check="false" />
                </div>
              </div>


            </div>
            <!-- Berkas -->
            <h2 class="font-bold text-[18px] col-span-8 md:col-span-6 px-6" v-show="activeTabIndex === 0">Berkas Karyawan
            </h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">


              <div>
                <label>Foto Karyawan</label>
                <div class="w-full mt-3">
                  <input :disabled="!actionText ? true : false" ref="refPasFoto" type="file" accept="image/*"
                    class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrors.pas_foto}" id="inputPasFoto" @change="imageChange">
                </div>
                <img :src="urlPasFoto" class="col-span-12 !mt-0 w-[231px]">
              </div>

              <div>
                <label>Foto KTP</label>
                <div class="w-full mt-3">
                  <input :disabled="!actionText ? true : false" type="file" accept="image/*"
                    class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrors.ktp_foto}" id="inputKTPFoto" @change="imageChange">

                </div>
                <img :src="urlKTPFoto" class="col-span-12 !mt-0 w-[231px]">
              </div>


              <div>
                <label>No. KTP<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.nik" label=""
                  placeholder="Tuliskan Nomor Kartu Penduduk" :errorText="formErrors.nik?'failed':''"
                  @input="v=>values.nik=v" :hints="formErrors.nik" :check="false" />
              </div>


              <div>
                <label>Alamat Sesuai KTP</label>
                <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3" :value="values.alamat_asli"
                  label="" placeholder="Tuliskan Alamat Sesuai KTP" :errorText="formErrors.alamat_asli?'failed':''"
                  @input="v=>values.alamat_asli=v" :hints="formErrors.alamat_asli" :check="false" />
              </div>



              <div>
                <label>Foto Kartu Keluarga</label>
                <div class="w-full mt-3">
                  <input :disabled="!actionText ? true : false" type="file" accept="image/*"
                    class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrors.kk_foto}" id="inputKKFoto" @change="imageChange">

                </div>
                <img :src="urlKKFoto" class="col-span-12 !mt-0 w-[231px]">
              </div>



              <div>
                <label>No. Kartu Keluarga</label>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.kk_no" label=""
                  placeholder="Tuliskan Nomor Kartu Keluarga" :errorText="formErrors.kk_no?'failed':''"
                  @input="v=>values.kk_no=v" :hints="formErrors.kk_no" :check="false" />
              </div>



              <div>
                <label>Foto NPWP</label>
                <div class="w-full mt-3">
                  <input :disabled="!actionText ? true : false" type="file" accept="image/*"
                    class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrors.npwp_foto}" id="inputNPWPFoto" @change="imageChange">

                </div>
                <img :src="urlNPWPFoto" class="col-span-12 !mt-0 w-[231px]">
              </div>



              <div>
                <label>No. NPWP</label>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.npwp_no" label=""
                  placeholder="Tuliskan Nomor Pokok Wajib Pajak" :errorText="formErrors.npwp_no?'failed':''"
                  @input="v=>values.npwp_no=v" :hints="formErrors.npwp_no" :check="false" />
              </div>


              <div>
                <label>Tanggal Berlaku NPWP</label>
                <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.npwp_tgl_berlaku"
                  label="" placeholder="Masukan Tanggal Berlaku NPWP" :errorText="formErrors.npwp_tgl_berlaku?'failed':''"
                  @input="v=>values.npwp_tgl_berlaku=v" :hints="formErrors.npwp_tgl_berlaku" :check="false" />
              </div>


              <div>
                <label>No. BPJS Kesehatan</label>
                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.bpjs_no_kesehatan" label=""
                  placeholder="Tuliskan Nomor BPJS" :errorText="formErrors.bpjs_no_kesehatan?'failed':''"
                  @input="v=>values.bpjs_no_kesehatan=v" :hints="formErrors.bpjs_no_kesehatan" :check="false" />
              </div>

              <div>
                <label>No. BPJS Ketenagakerjaan</label>
                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.bpjs_no_ketenagakerjaan"
                  label="" placeholder="Tuliskan Nomor BPJS" :errorText="formErrors.bpjs_no_ketenagakerjaan?'failed':''"
                  @input="v=>values.bpjs_no_ketenagakerjaan=v" :hints="formErrors.bpjs_no_ketenagakerjaan" :check="false" />
              </div>

              <div>
                <label>Tipe BPJS</label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                  :value="values.bpjs_tipe_id" @input="v=>values.bpjs_tipe_id=v"
                  :errorText="formErrors.bpjs_tipe_id?'failed':''" :hints="formErrors.bpjs_tipe_id" label=""
                  placeholder="Pilih Tipe BPJS" valueField="id" displayField="value" :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  where: `this.group='TIPE BPJS' AND this.is_active='true'`,
                                  join: true,
                                  selectfield: 'this.id, this.code, this.value, this.is_active'
                                }
                              }" :check="false" />
              </div>


              <div>
                <label>Berkas Pendukung Lainnya<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText }" :value="values.berkas_lain"
                  @input="(v)=>values.berkas_lain=v" :maxSize="10"
                  :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                              url: `${store.server.url_backend}/operation/m_kary_det_kartu/upload`,
                              headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                              params: { field: 'berkas_lain' },
                              onsuccess: response=>response,
                              onerror:(error)=>{},
                             }" :hints="formErrors.berkas_lain" label="" placeholder="Upload Berkas" fa-icon="upload"
                  accept="application/pdf" :check="false" />

              </div>


              <div>
                <label>Keterangan<label class="text-red-500 space-x-0 pl-0"></label></label>
                <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3" :value="values.desc_file"
                  label="" placeholder="Tuliskan Keterangan" :errorText="formErrors.desc_file?'failed':''"
                  @input="v=>values.desc_file=v" :hints="formErrors.desc_file" :check="false" />
              </div>


            </div>
            <!-- Ukuran -->
            <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Ukuran</h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
              <div>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.uk_baju"
                  @input="v=>values.uk_baju=v" :errorText="formErrors.uk_baju?'failed':''" :hints="formErrors.uk_baju"
                  label="Ukuran Baju" placeholder="Pilih Ukuran Baju" valueField="key" displayField="key"
                  :options="['S', 'M', 'L', 'XL', 'XXL', 'XXXL']" :check="false" />
              </div>
              <div>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.uk_celana"
                  label="Ukuran Celana" placeholder="Tuliskan Ukuran Celana" :errorText="formErrors.uk_celana?'failed':''"
                  @input="v=>values.uk_celana=v" :hints="formErrors.uk_celana" :check="false" />
              </div>

              <div>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.uk_sepatu"
                  label="Ukuran Sepatu" placeholder="Tuliskan Ukuran Sepatu" :errorText="formErrors.uk_sepatu?'failed':''"
                  @input="v=>values.uk_sepatu=v" :hints="formErrors.uk_sepatu" :check="false" />
              </div>
            </div>
            <!-- pembayaran -->
            <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Pembayaran</h2>
            <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">

              <div v-show="!isProfile">

                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                  :value="values.periode_gaji_id" @input="v=>values.periode_gaji_id=v"
                  :errorText="formErrors.periode_gaji_id?'failed':''" :hints="formErrors.periode_gaji_id"
                  label="Perido Gaji" placeholder="Pilih Periode Gaji" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='PERIODE GAJI' AND this.is_active='true'`,
                              }
                            }" :check="false" />
              </div>
              <div v-show="!isProfile">
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.tipe_id"
                  @input="v=>values.tipe_id=v" :errorText="formErrors.tipe_id?'failed':''" :hints="formErrors.tipe_id"
                  label="Tipe Pembayaran" placeholder="Pilih Tipe Pembayaran" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='TIPE PEMBAYARAN' AND this.is_active='true'`,
                              }
                            }" :check="false" />
              </div>
              <div v-show="!isProfile">
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
                  :value="values.metode_id" @input="v=>values.metode_id=v" :errorText="formErrors.metode_id?'failed':''"
                  :hints="formErrors.metode_id" label="Pembayaran" placeholder="Pilih Metode Pembayaran" valueField="id"
                  displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='METODE PEMBAYARAN' AND this.is_active='true'`,
                              }
                            }" :check="false" />
              </div>

              <div v-show="!isProfile">
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.bank_id"
                  @input="v=>values.bank_id=v" :errorText="formErrors.bank_id?'failed':''" :hints="formErrors.bank_id"
                  label="Bank" placeholder="Pilih Bank" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='BANK' AND this.is_active='true'`,
                              }
                            }" :check="false" />

              </div>
              <div>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_rek"
                  label="Nomer Rekening" placeholder="Tuliskan Nomor Rekening" :errorText="formErrors.no_rek?'failed':''"
                  @input="v=>values.no_rek=v" :hints="formErrors.no_rek" :check="false" />

              </div>
              <div>

                <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.atas_nama_rek"
                  label="Nama Pemilik Rekening" placeholder="Tuliskan Atas Nama Pemilik Rekening"
                  :errorText="formErrors.atas_nama_rek?'failed':''" @input="v=>values.atas_nama_rek=v"
                  :hints="formErrors.atas_nama_rek" :check="false" />

              </div>
            </div>

          </div>


          <!-- JABATAN -->
          <div class="p-4 space-y-10" v-show="activeTabIndex === 8">
            <!-- DETAIL -->
            <div class="flex justify-end mb-4">
              <button @click="addDetail" type="button" v-show="actionText"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-md flex items-center justify-center">
                <icon fa="plus" size="sm mr-0.5" /> Tambah Jabatan
              </button>
            </div>
            <div class="w-0 min-w-full">
              <div class="overflow-x-auto bg-white rounded-lg shadow-md p-4"
                style="scrollbar-width: thin; -ms-overflow-style: none;">
                <table class="min-w-[1500px] border-collapse border rounded-lg">

                  <!-- ===================== HEADER ===================== -->
                  <thead class="bg-gray-100 sticky top-0">
                    <tr class="text-sm text-left">
                      <th v-show="actionText" class="p-3 border text-center w-[2%]">#</th>
                      <th class="p-3 border text-center w-[2%]">No</th>
                      <th class="p-3 border text-center w-[10%]">COMPANY</th>
                      <th class="p-3 border text-center w-[10%]">SBU</th>
                      <th class="p-3 border text-center w-[10%]">SUB</th>
                      <th class="p-3 border text-center w-[10%]">CABANG</th>
                      <th class="p-3 border text-center w-[10%]">DIVISI</th>
                      <th class="p-3 border text-center w-[10%]">JABATAN</th>
                      <th class="p-3 border text-center w-[10%]">LEVEL</th>
                      <th class="p-3 border text-center w-[10%]">START</th>
                      <th class="p-3 border text-center w-[10%]">END</th>
                      <th class="p-3 border text-center w-[5%]">PRIMARY</th>
                      <th class="p-3 border text-center w-[5%]">ACTIVE</th>
                      <th class="p-3 border text-center w-[10%]">NOTE</th>
                    </tr>
                  </thead>

                  <!-- ===================== BODY ===================== -->
                  <tbody>

                    <tr v-if="inDetailArr.length === 0">
                      <td colspan="14" class="py-6 text-center text-gray-500">
                        No Data to Show
                      </td>
                    </tr>

                    <template v-for="(item, i) in inDetailArr" :key="i" class="bg-black">
                      <tr class="bg-white border-t hover:bg-gray-50 transition mb-4">
                        <td class="p-2 border pt-4 pb-4 border" v-show="actionText">
                          <div class="flex justify-center">
                            <button type="button" @click="hapusDetail(i)">
                              <svg width="14" height="18" viewBox="0 0 14 18" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path id="Vector"
                                  d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                                  fill="#F24E1E" />
                              </svg>
                            </button>
                          </div>
                        </td>
                        <td class="p-3 text-center border font-bold">{{ i + 1 }}</td>
                        <!-- COMPANY -->
                        <td class="p-3 text-center border">
                          <FieldSelect :key="`comp_${i}_${item.m_company_id}`" :bind="{ disabled: true, clearable: false }" class="w-full !mt-0"
                            :value="item.m_company_id"
                            :errorText="formErrors.m_company_id?'failed':''" :hints="formErrors.m_company_id"
                            valueField="id" displayField="name" :api="{
                                  url: `${store.server.url_backend}/operation/m_company`,
                                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                  params: {
                                    simplest:true,
                                    transform:false,
                                    join:false,
                                  }
                            }" placeholder="" label="" fa-icon="sort-desc" :check="false" />
                        </td>
                        <!-- SBU -->
                        <td class="p-3 text-center border">
                          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-0"
                            :value="item.m_comp_id" @input="v=>{
                                if(v){
                                  item.m_comp_id=v
                                }else{
                                  item.m_comp_id=null
                                  item.m_subcomp_id=null
                                  item.m_company_id=null
                                  item.m_branch_id=null
                                  item.m_divisi_id = null;
                                  item.m_posisi_id = null; 
                                }
                              }" @update:valueFull="obj => {
                                  if (obj) {
                                    item.m_comp_id = obj.id; 
                                    item.m_subcomp_id = null; 
                                    item.m_company_id = null;
                                    item.m_branch_id = null;
                                    item.m_divisi_id = null;
                                    item.m_posisi_id = null; 
                                  } else {
                                    item.m_comp_id = null;
                                    item.m_subcomp_id = null;
                                    item.m_company_id = null;
                                    item.m_branch_id = null;
                                    item.m_divisi_id = null;
                                    item.m_posisi_id = null; 
                                  }
                                }" :errorText="formErrors.m_comp_id?'failed':''" :hints="formErrors.m_comp_id"
                            valueField="id" displayField="name" :api="{
                                  url: `${store.server.url_backend}/operation/m_comp`,
                                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                  params: {
                                    transform:false,
                                    join:false,
                                    where:`this.is_active='true'`
                                  }
                            }" placeholder="" label="" fa-icon="sort-desc" :check="false" />
                        </td>
                        <!-- SUB -->
                        <td class="p-3 text-center border">
                          <FieldSelect :bind="{ disabled: !actionText || !item.m_comp_id,  clearable:true }"
                            class="w-full !mt-0" :value="item.m_subcomp_id" @input="async v=>{
                          if(v){
                            item.m_subcomp_id=v;
                            if(!item.m_company_id){
                              try {
                                const res = await fetch(`${store.server.url_backend}/operation/m_subcomp/${v}?join=true&transform=false`, {
                                  headers: {
                                    'Content-Type': 'Application/json',
                                    Authorization: `${store.user.token_type} ${store.user.token}`
                                  }
                                });
                                if (res.ok) {
                                  const json = await res.json();
                                  const subData = json.data ?? json;
                                  const compId = subData.m_company_id ?? subData.company_id ?? subData.m_company?.id ?? subData['m_company.id'] ?? subData['m_company_id.id'] ?? null;
                                  if (compId) {
                                    item.m_company_id = compId;
                                  }
                                }
                              } catch (e) {}
                            }
                          }else{
                            item.m_subcomp_id=null;
                            item.m_company_id=null;
                            item.m_branch_id=null;
                            item.m_divisi_id = null;
                            item.m_posisi_id = null; 
                          }
                        }" @update:valueFull="async obj => {
                            if (obj) {
                              item.m_subcomp_id = obj.id; 
                              let compId = obj.m_company_id ?? obj.company_id ?? obj.m_company?.id ?? obj['m_company.id'] ?? obj['m_company_id.id'] ?? null;
                              if (!compId && obj.id) {
                                try {
                                  const res = await fetch(`${store.server.url_backend}/operation/m_subcomp/${obj.id}?join=true&transform=false`, {
                                    headers: {
                                      'Content-Type': 'Application/json',
                                      Authorization: `${store.user.token_type} ${store.user.token}`
                                    }
                                  });
                                  if (res.ok) {
                                    const json = await res.json();
                                    const subData = json.data ?? json;
                                    compId = subData.m_company_id ?? subData.company_id ?? subData.m_company?.id ?? subData['m_company.id'] ?? subData['m_company_id.id'] ?? null;
                                  }
                                } catch (e) {}
                              }
                              item.m_company_id = compId;
                              item.m_branch_id = null; 
                              item.m_divisi_id = null; 
                              item.m_posisi_id = null; 
                            } else {
                              item.m_subcomp_id = null;
                              item.m_company_id = null;
                              item.m_branch_id = null;
                              item.m_divisi_id = null;
                              item.m_posisi_id = null; 
                            }
                          }" :errorText="formErrors.m_subcomp_id?'failed':''" :hints="formErrors.m_subcomp_id"
                            valueField="id" displayField="name" :api="{
                            url: `${store.server.url_backend}/operation/m_subcomp`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              transform:false,
                              join:true,
                              where: `this.is_active='true' AND this.m_comp_id='${item.m_comp_id}'`
                            }
                      }" placeholder="" label="" fa-icon="sort-desc" :check="false" />
                        </td>
                        <!-- CABANG -->
                        <td class="p-3 text-center border">
                          <FieldSelect :bind="{ disabled: !actionText || !item.m_subcomp_id , clearable:true }"
                            class="w-full !mt-0" :value="item.m_branch_id" @input="v=>{
                          if(v){
                            item.m_branch_id=v
                          }else{
                            item.m_branch_id=null
                            item.m_divisi_id=null
                            item.m_posisi_id = null; 
                          }
                        }" @update:valueFull="obj => {
                            if (obj) {
                              item.m_branch_id = obj.id; 
                              item.m_divisi_id = null; 
                              item.m_posisi_id = null; 
                            } else {
                              item.m_branch_id = null;
                            }
                          }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id"
                            valueField="id" displayField="name" :api="{
                            url: `${store.server.url_backend}/operation/m_branch`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              simplest:true,
                              transform:false,
                              join:false,
                              where: `this.is_active='true' AND this.m_subcomp_id='${item.m_subcomp_id}'`
                            }
                      }" placeholder="" label="" fa-icon="sort-desc" :check="false" />
                        </td>
                        <!-- DIVISI -->
                        <td class="p-3 text-center border">
                          <FieldSelect :bind="{ disabled: !actionText || !item.m_branch_id , clearable:true }"
                            class="w-full !mt-0" :value="item.m_divisi_id" @input="v=>{
                          if(v){
                            item.m_divisi_id=v
                          }else{
                            item.m_divisi_id=null
                            item.m_posisi_id=null
                          }
                        }" @update:valueFull="obj => {
                            if (obj) {
                              item.m_divisi_id = obj.id; 
                              item.m_posisi_id = null; 
                            } else {
                              item.m_divisi_id = null;
                            }
                          }" :errorText="formErrors.m_divisi_id?'failed':''" :hints="formErrors.m_divisi_id"
                            valueField="id" displayField="name.value" :api="{
                            url: `${store.server.url_backend}/operation/m_divisi`,
                            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                            params: {
                              scopes:'Name',
                              where: `this.is_active='true' AND this.m_branch_id='${item.m_branch_id}'`,
                            }
                      }" placeholder="" label="" fa-icon="sort-desc" :check="false" />
                        </td>
                        <!-- JABATAN -->
                        <td class="p-3 text-center border">
                          <FieldSelect :bind="{ disabled: !actionText}" class="w-full !mt-0" :value="item.m_posisi_id"
                            @input="v => item.m_posisi_id = v"
                            @update:valueFull="obj => {
                              if (obj) {
                                item.m_posisi_id = obj.id;
                                item.level_name = obj.level_name ?? obj['m_level_posisi.level_name'] ?? obj['lp.level_name'] ?? '-';
                              } else {
                                item.m_posisi_id = null;
                                item.level_name = null;
                              }
                            }"
                            :errorText="formErrors.m_posisi_id ? 'failed' : ''"
                            :hints="formErrors.m_posisi_id" label="" placeholder="Pilih Jabatan" valueField="id"
                            displayField="name" :api="{
                                    url: `${store.server.url_backend}/operation/m_posisi`,
                                    headers: {
                                      'Content-Type': 'Application/json',
                                      Authorization: `${store.user.token_type} ${store.user.token}`
                                    },
                                    params: {
                                      scopes: 'GetValueGen',
                                      transform: false,
                                      where: `this.is_active='true' `,
                                      join: true,
                                    }
                                  }" :check="false" />
                        </td>

                        <!-- LEVEL -->
                        <td class="p-3 text-center border">
                          <FieldX class="!mt-0" label="" :bind="{ readonly: true, disabled: true }"
                            :value="item.level_name ?? item['m_level_posisi.level_name'] ?? item['lp.level_name'] ?? '-'"
                            placeholder="" :check="false" />
                        </td>

                        <!-- START -->
                        <td class="p-3 text-center border" v-show="actionText">
                          <FieldX class="!mt-0" label="" type="date" :bind="{ readonly: !actionText }"
                            :value="item.start_time" @input="(v)=>item.start_time=v" :dateTimeWithoutSec="true"
                            placeholder="" :check="false" />
                        </td>
                        <!-- END -->
                        <td class="p-3 text-center border" v-show="actionText">
                          <FieldX class="!mt-0" label="" type="date" :bind="{ readonly: !actionText }"
                            :value="item.end_time" @input="(v)=>item.end_time=v" :dateTimeWithoutSec="true" placeholder=""
                            :check="false" />
                        </td>

                        <!-- START -->
                        <td class="p-3 text-center border" v-show="!actionText">
                          <FieldX class="!mt-0" label="" :bind="{ readonly: !actionText }" :value="item.start_time"
                            @input="(v)=>item.start_time=v" :dateTimeWithoutSec="true" placeholder="" :check="false" />
                        </td>
                        <!-- END -->
                        <td class="p-3 text-center border" v-show="!actionText">
                          <FieldX class="!mt-0" label="" :bind="{ readonly: !actionText }" :value="item.end_time"
                            @input="(v)=>item.end_time=v" :dateTimeWithoutSec="true" placeholder="" :check="false" />
                        </td>
                        <!-- PRIMARY -->
                        <td class="p-3 text-center border">
                          <div class="flex justify-center">
                            <label class="relative h-6 w-6 cursor-pointer">
                              <input type="checkbox" class="peer hidden" v-model="item.is_primary" :disabled="!actionText"
                                @change="handlePrimaryChange(item)" />
                              <span
                                class="absolute inset-0 flex items-center justify-center border border-gray-300 rounded-md bg-white peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-disabled:opacity-50">
                                <svg v-show="item.is_primary" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white"
                                  viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-3.293-3.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                    clip-rule="evenodd" />
                                </svg>
                              </span>
                            </label>
                          </div>
                        </td>
                        <!-- ACTIVE -->
                        <td class="p-3 text-center border">
                          <div class="flex justify-center">
                            <label class="relative h-6 w-6 cursor-pointer">
                              <input type="checkbox" class="peer hidden" v-model="item.is_active"
                                :disabled="!actionText || item.is_primary" />
                              <span
                                class="absolute inset-0 flex items-center justify-center border border-gray-300 rounded-md bg-white peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-disabled:opacity-50">
                                <svg v-show="item.is_active" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white"
                                  viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-3.293-3.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                                    clip-rule="evenodd" />
                                </svg>
                              </span>
                            </label>
                          </div>
                        </td>
                        <!-- NOTE -->
                        <td class="p-3 text-center border">
                          <FieldX class="!mt-0" label="" :bind="{ readonly: !actionText }" :value="item.desc"
                            type="textarea" @input="(v)=>item.desc=v" placeholder="" :check="false" />
                        </td>
                      </tr>

                      <tr class="bg-gray-50">
                        <td colspan="13" class="px-6 py-6 border-t bg-gray-50">

                          <div class="flex gap-3 mb-4">
                            <button @click="addSubDetail(i)"
                              class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md">
                              + Tambah Jobdesc
                            </button>

                            <button @click="showSubDetail = !showSubDetail" class="text-sm px-4 py-2 rounded-md" :class="showSubDetail
                      ? 'bg-red-500 hover:bg-red-600 text-white'
                      : 'bg-gray-200 hover:bg-gray-300 text-gray-700'">
                              {{ showSubDetail ? 'Hide Detail' : 'Show Detail' }}
                            </button>
                          </div>

                          <div v-if="showSubDetail" class="border rounded-md bg-white overflow-hidden">
                            <div class="overflow-x-auto">
                              <table class="w-full text-sm border-collapse">
                                <thead class="bg-gray-100 text-gray-600">
                                  <tr>
                                    <th class="px-4 py-2 border w-12 text-center">#</th>
                                    <th class="px-4 py-2 border w-16 text-center">No</th>
                                    <th class="px-4 py-2 border text-left">Jobdesc</th>
                                    <th class="px-4 py-2 border w-24 text-center">Active</th>
                                  </tr>
                                </thead>

                                <tbody>
                                  <tr v-for="(sub, j) in item.subDetails" :key="j" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2 border text-center">
                                      <button type="button" @click="hapusSubDetail(i, j)"
                                        class="hover:opacity-80 transition">
                                        <svg width="14" height="18" viewBox="0 0 14 18" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path
                                            d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                                            fill="#F24E1E" />
                                        </svg>
                                      </button>
                                    </td>

                                    <td class="px-4 py-2 border text-center">
                                      {{ j + 1 }}
                                    </td>

                                    <td class="px-4 py-2 border">
                                      <FieldSelect class="mt-0" :bind="{ disabled: !actionText, clearable:false }"
                                        :value="sub.jobdesc" @input="v=>sub.jobdesc=v"
                                        :errorText="formErrors.jobdesc?'failed':''" :hints="formErrors.jobdesc"
                                        valueField="jobdesc" displayField="jobdesc" :api="{
                                                  url: `${store.server.url_backend}/operation/m_jobdesc_d`,
                                                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                                  params: {
                                                    posisi_id: `${item.m_posisi_id}`,
                                                    scopes: 'WithJabatan',
                                                    simplest:true,
                                                    transform:false,
                                                    join:true,
                                                    notin: item.subDetails.length
                                                          ? `this.id:${item.subDetails
                                                              .map(x => x.jobdesc)
                                                              .filter(id => id)
                                                              .join(',')}`
                                                          : null
                                                  }
                                              }" fa-icon="" :check="false" />
                                    </td>

                                    <td class="px-4 py-2 border text-center">
                                      <input type="checkbox" v-model="sub.is_active" :disabled="!actionText"
                                        class="w-4 h-4" />
                                    </td>
                                  </tr>

                                  <tr v-if="!item.subDetails || item.subDetails.length === 0">
                                    <td colspan="4" class="text-center py-4 text-gray-400">
                                      Belum ada jobdesc ditambahkan
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>

                        </td>
                      </tr>
                    </template>

                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="p-4 space-y-10" v-show="activeTabIndex === 9">

            <ButtonMultiSelect title="Add To List" @add="onDetailAdd_Lokasi" :api="apiLokasi.apiUrlAndParam"
              :columns="apiLokasi.columns">
              <div class="flex items-center space-x-2">
                <div v-show="actionText"
                  class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
                  <icon fa="plus" />
                  Tambah Lokasi
                </div>
              </div>
            </ButtonMultiSelect>

            <div class="mt-4">
              <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
                <thead>
                  <tr class="border">
                    <td
                      class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                      No.
                    </td>
                    <td
                      class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                      Lokasi
                    </td>
                    <td
                      class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                      Action
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, i) in detail_lokasi" :key="item.id" class="border-t" v-if="detail_lokasi.length > 0">
                    <td class="p-2 text-center border border-[#CACACA]">
                      {{ i + 1 }}.
                    </td>


                    <td class="p-2 border border-[#CACACA]">
                      <!-- {{item.nama ?? '-'}} -->
                      <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full"
                        :value="item.presensi_lokasi_id" @input="v=>item.presensi_lokasi_id=v"
                        :errorText="formErrors.presensi_lokasi_id?'failed':''" :hints="formErrors.presensi_lokasi_id"
                        label="" placeholder="Pilih Lokasi" valueField="id" displayField="nama" :api="{
                                url: `${store.server.url_backend}/operation/presensi_lokasi`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                }
                              }" :check="false" />
                    </td>



                    <td class="p-2 border border-[#CACACA]">
                      <div class="flex justify-center">
                        <button type="button" @click="removeDetail_Lokasi(i)" :disabled="!actionText" title="Hapus">
                          <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path id="Vector"
                              d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                              fill="#F24E1E" />
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


          <!-- Form Pendidikan -->
          <div class="p-4 " v-show="activeTabIndex === 1">
            <div class="grid <md:grid-cols-1 grid-cols-3 gap-2">
              <div>
                <label>Tingkat Pendidikan <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
                  :value="valuesPendidikan.tingkat_id" label="" placeholder="Pilih Tingkat Pendidikan"
                  @input="v=>valuesPendidikan.tingkat_id=v" :errorText="formErrorsPend.tingkat_id?'failed':''"
                  @update:valueFull="(objVal)=>{
                            $log('isi pendidikan',objVal)
                              valuesPendidikan.pendidikan = objVal.value
                            }" :hints="formErrorsPend.tingkat_id" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
              </div>
              <div>
                <label>Tahun Masuk <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full" label=""
                  placeholder="Pilih Tahun Masuk" :value="valuesPendidikan.thn_masuk"
                  @input="v=>valuesPendidikan.thn_masuk=v" :options="ArrTahun"
                  :errorText="formErrorsPend.thn_masuk?'failed':''" :hints="formErrorsPend.thn_masuk" valueField="key"
                  displayField="key" :check="false" />
              </div>
              <div>
                <label>Nama Sekolah <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" class="w-full" :value="valuesPendidikan.nama_sekolah" label=""
                  placeholder="Tuliskan Nama Sekolah" @input="v=>valuesPendidikan.nama_sekolah=v" :check="false"
                  :errorText="formErrorsPend.nama_sekolah?'failed':''" :hints="formErrorsPend.nama_sekolah" />
              </div>
              <div>
                <label>Tahun Lulus <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
                  :value="valuesPendidikan.thn_lulus" label="" placeholder="Pilih Tahun Lulus"
                  @input="v=>valuesPendidikan.thn_lulus=v" :options="ArrTahun"
                  :errorText="formErrorsPend.thn_lulus?'failed':''" :hints="formErrorsPend.thn_lulus" valueField="key"
                  displayField="key" :check="false" />
              </div>
              <div>
                <label> Kota <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
                  :value="valuesPendidikan.kota_id" @input="v=>valuesPendidikan.kota_id=v"
                  :errorText="formErrorsPend.kota_id?'failed':''" :hints="formErrorsPend.kota_id" label=""
                  placeholder="Pilih Kota" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" />
              </div>
              <div>
                <label> Nilai<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full" :value="valuesPendidikan.nilai"
                  label="" placeholder="Tuliskan Nilai" @input="v=>valuesPendidikan.nilai=v" :check="false"
                  :errorText="formErrorsPend.nilai?'failed':''" :hints="formErrorsPend.nilai" />
              </div>
              <div>
                <label> Jurusan <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Jurusan" class="w-full"
                  :value="valuesPendidikan.jurusan" :errorText="formErrorsPend.jurusan?'failed':''"
                  :hints="formErrorsPend.jurusan" @input="v=>valuesPendidikan.jurusan=v" :check="false" />
              </div>
              <div>
                <label>Ijasah Terakhir</label>
                <div class="w-full flex items-center">
                  <input :disabled="!actionText ? true : false" ref="fileIjz" type="file" accept="application/pdf"
                    class="w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrorsPend.ijazah_foto}" @change="fileIjazah"
                    @input="(v)=>valuesPendidikan.ijazah_foto=v">
                </div>
              </div>
              <div>
                <label>Catatan</label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Catatan" type="textarea"
                  class="w-full" :value="valuesPendidikan.desc" :errorText="formErrorsPend.desc?'failed':''"
                  @input="v=>valuesPendidikan.desc=v" :hints="formErrorsPend.desc" :check="false" />
              </div>
              <div>
                <label>Pendidikan Akhir <label class="text-red-500 space-x-0 pl-0">*</label></label>
                <div class="flex items-center space-x-5 ">
                  <input :disabled="!actionText ? true : false" type="radio" value="1"
                    v-model="valuesPendidikan.is_pend_terakhir"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                  <label for="aktif_status" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Iya</label>

                  <input :disabled="!actionText ? true : false" type="radio" value="0"
                    v-model="valuesPendidikan.is_pend_terakhir"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                  <label for="tidak_aktif_status"
                    class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak</label>
                </div>
              </div>
            </div>
            <!-- TABLE -->
            <div class="w-full mt-3">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailPendidikan"
                @cellValueChanged="onCellValueChangedPend" :columns="[{
                    headerName: 'No',
                    cellRenderer: !actionText?null:'ButtonGrid',
                    valueGetter:p=>p.node.rowIndex + 1,
                    cellRendererParams: !actionText?null:{
                      showValue: true,
                      icon: 'times',
                      class: 'btn-text-danger',
                      click:(app)=>{
                        if (app && app.params) {
                          const row = app.params.node.data
                          swal.fire({
                            icon: 'warning', showDenyButton: true,
                            text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                          }).then((res) => {
                            if (res.isConfirmed) {
                              detailPendidikan = detailPendidikan.filter((e) => e._id != app.params.node.data._id)
                              app.params.api.applyTransaction({ remove: [app.params.node.data] })
                            }
                          })
                        }
                      }
                    },
                    width: 60,
                    sortable: false, resizable: true, filter: false, wrapText: true,
                    cellClass: ['justify-center', 'bg-gray-50']
                  },
                  {
                    flex: 1,
                    headerName: 'Tingkat',
                    field: 'pendidikan',
                    editable: true,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: () => ({
                      values: pendidikan,
                    }),
                    sortable: false, resizable: true, filter: false, wrapText:true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Nama Sekolah',
                    editable: true,
                    field: 'nama_sekolah',
                    sortable: false, resizable: true, filter: false, wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Jurusan',
                    field: 'jurusan',
                    editable: true,
                    sortable: false, resizable: true, filter: false, wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Tahun Masuk',
                    field: 'thn_masuk',
                    editable: true,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: () => ({
                                      values: tahun,
                                    }),
                    sortable: false,
                    resizable: true,
                    filter: false,
                    wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Nilai',
                    field: 'nilai',
                    editable: true,
                    sortable: false, resizable: true, filter: false, wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Pendidikan Terakhir',
                    field: 'is_pend_terakhir',
                    editable: true,
                    cellEditor: 'agSelectCellEditor',
                    cellEditorParams: {
                      values: ['Iya', 'Tidak']
                    },
                    valueFormatter: (params) => {
                      if (params.value === '1') return 'Iya'
                      if (params.value === '0') return 'Tidak'
                      return params.value
                    },
                    valueParser: (params) => {
                      if (params.newValue === 'Iya') return '1'
                      if (params.newValue === 'Tidak') return '0'
                      return params.newValue
                    },
                    sortable: false,
                    resizable: true,
                    filter: false,
                    wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  },
                  {
                    flex: 1,
                    headerName: 'Note',
                    field: 'desc',
                    editable: true,
                    sortable: false, resizable: true, filter: false, wrapText: true,
                    cellClass: ['!border-gray-200', 'justify-center'],
                  }
                ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addPendidikan" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailPendidikan = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>
            </div>
            <!-- END TABLE -->
          </div>

          <div class="<md:col-span-1 col-span-2 p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-if="activeTabIndex === 2">
            <div class="<md:col-span-1 col-span-3">
              <button v-if="actionText" title="Add Detail" @click="onDetailAddKeluarga">
                <div class="flex items-center space-x-2" v-if="actionText">
                  <div v-show=" actionText"
                    class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
                    <icon fa="plus" />
                    Add To List
                  </div>
                </div>
              </button>
              <div class="overflow-scroll lg:overflow-visible <md:col-span-1 col-span-3 mt-4">
                <table class="w-full overflow-x-auto table-auto border border-[#CACACA] pt-4">
                  <thead>
                    <tr class="border">
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                        No</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Keluarga</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Nama</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Pekerjaan</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Pendidikan Teakhir</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Jenis Kelamin</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Tanggal Lahir</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Askes
                      </td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        BPJS
                      </td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                        Catatan</td>
                      <td
                        class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, i) in detailKeluarga" :key="item.id" class="border-t">
                      <td class="text-[12px] text-center border border-[#CACACA]">
                        {{ i + 1 }}.
                      </td>
                      <td class="text-[12px] text-left border border-[#CACACA]">
                        <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.keluarga_id"
                          @input="v=>item.keluarga_id=v" :errorText="formErrors.keluarga_id?'failed':''"
                          :hints="formErrors.keluarga_id" valueField="id" displayField="value" :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                                params: {
                                  simplest:true,
                                  where: `this.group='HUBUNGAN KELUARGA' AND this.is_active='true'`,
                                  order_by:'value',
                                  selectfield: 'this.id, this.code, this.value, this.is_active',
                                  order_type: 'ASC'
                                }
                              }" fa-icon="caret-down" label="" placeholder="Pilih Bank" :check="false" />
                      </td>
                      <td class="text-[12px] text-left border border-[#CACACA]">
                        <FieldX :bind="{ readonly: !actionText }" type="text" :value="item.nama"
                          :errorText="formErrors.nama?'failed':''" @input="v=>item.nama=v" :hints="formErrors.nama"
                          :check="false" placeholder="" label="" />
                      </td>
                      <td class="text-[12px] text-left border border-[#CACACA]">
                        <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.pekerjaan_id"
                          @input="v=>item.pekerjaan_id=v" :errorText="formErrors.pekerjaan_id?'failed':''"
                          :hints="formErrors.pekerjaan_id" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                where: `this.group='PEKERJAAN' AND this.is_active='true'`,
                                join: true,
                                selectfield: 'this.id, this.code, this.value, this.is_active'
                              }
                            }" label="" :check="false" />
                      </td>
                      <td class="text-[12px] text-left border border-[#CACACA]">
                        <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.pend_terakhir_id"
                          @input="v=>item.pend_terakhir_id=v" :errorText="formErrors.pend_terakhir_id?'failed':''"
                          :hints="formErrors.pend_terakhir_id" valueField="id" displayField="value" :api="{
                                    url: `${store.server.url_backend}/operation/m_general`,
                                    headers: {
                                      'Content-Type': 'Application/json',
                                      Authorization: `${store.user.token_type} ${store.user.token}`
                                    },
                                    params: {
                                      simplest: true,
                                      transform: false,
                                      where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
                                      join: true,
                                      selectfield: 'this.id, this.code, this.value, this.is_active'
                                    }
                                  }" label="" :check="false" />
                      </td>
                      <td class="text-[12px] text-left w-[15%] border border-[#CACACA]">
                        <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.jk_id"
                          @input="v=>item.jk_id=v" :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id"
                          valueField="id" displayField="value" :api="{
                                    url: `${store.server.url_backend}/operation/m_general`,
                                    headers: {
                                      'Content-Type': 'Application/json',
                                      Authorization: `${store.user.token_type} ${store.user.token}`
                                    },
                                    params: {
                                      simplest: true,
                                      transform: false,
                                      where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
                                      join: true,
                                      selectfield: 'this.id, this.code, this.value, this.is_active'
                                    }
                                  }" label="" :check="false" />
                      </td>
                      <td class="text-[12px] text-right border border-[#CACACA]">
                        <FieldX type="date" class="text-right" :bind="{ readonly: !actionText }" type="text"
                          :value="item.tgl_lahir" :errorText="formErrors.tgl_lahir?'failed':''" @input="v=>item.tgl_lahir=v"
                          :hints="formErrors.tgl_lahir" :check="false" placeholder="" label="" />
                      </td>
                      <td class="text-[12px] text-center border border-[#CACACA]">
                        <input type="checkbox" :disabled="!actionText" :checked="item.include_askes"
                          @change="e => item.include_askes = e.target.checked"
                          class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                      </td>
                      <td class="text-[12px] text-center border border-[#CACACA]">
                        <input type="checkbox" :disabled="!actionText" :checked="item.include_bpjs"
                          @change="e => item.include_bpjs = e.target.checked"
                          class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                      </td>
                      <td class="text-[12px] text-left border border-[#CACACA]">
                        <FieldX type="textarea" :bind="{ readonly: !actionText }" type="text" :value="item.desc"
                          :errorText="formErrors.desc?'failed':''" @input="v=>item.desc=v" :hints="formErrors.desc"
                          :check="false" placeholder="" label="" />
                      </td>
                      <td>
                        <div class="flex justify-center">
                          <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                            <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path id="Vector"
                                d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                                fill="#F24E1E" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="detailKeluarga.length === 0" class="text-center">
                      <td colspan="7" class="py-[20px]">
                        No data to show
                      </td>
                    </tr>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- END TABLE DETAIL -->
            </div>
          </div>


          <!-- Form Pelatihan -->
          <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
            v-show="activeTabIndex === 3">
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nama Pelatihan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Pelatihan"
                  class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_pel"
                  :errorText="formErrorsPel.nama_pel?'failed':''" @input="v=>valuesPelatihan.nama_pel=v"
                  :hints="formErrorsPel.nama_pel" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
                  class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.tahun" @input="v=>valuesPelatihan.tahun=v"
                  :options="ArrTahun" :errorText="formErrorsPel.tahun?'failed':''" :hints="formErrorsPel.tahun"
                  valueField="key" displayField="key" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nama Lembaga<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Lembaga"
                  class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_lem"
                  :errorText="formErrorsPel.nama_lem?'failed':''" @input="v=>valuesPelatihan.nama_lem=v"
                  :hints="formErrorsPel.nama_lem" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="valuesPelatihan.kota_id" @input="v=>valuesPelatihan.kota_id=v"
                  :errorText="formErrorsPel.kota_id?'failed':''" @update:valueFull="(objVal)=>{
                                valuesPelatihan.kota = objVal.value
                              }" :hints="formErrorsPel.kota_id" label="" placeholder="Pilih Kota" valueField="id"
                  displayField="value" :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                  where: `this.group='KOTA'`,
                                  paginate: 1000
                                }
                              }" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-12">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailPelatihan" :columns="[{
                              headerName: 'No',
                              cellRenderer: !actionText?null:'ButtonGrid',
                              valueGetter:p=>p.node.rowIndex + 1,
                              cellRendererParams: !actionText?null:{
                                showValue: true,
                                icon: 'times',
                                class: 'btn-text-danger',
                                click:(app)=>{
                                  if (app && app.params) {
                                    const row = app.params.node.data
                                    swal.fire({
                                      icon: 'warning', showDenyButton: true,
                                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                                    }).then((res) => {
                                      if (res.isConfirmed) {
                                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                                        detailPelatihan = detailPelatihan.filter((e) => e._id != app.params.node.data._id)
                                      }
                                    })
                                  }
                                }
                              },
                              width: 60,
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              headerName: 'Nama Pelatihan',
                              field: 'nama_pel',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Nama Lembaga',
                              field: 'nama_lem',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Kota',
                              field: 'kota',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'tahun',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addPelatihan" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailPelatihan = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>

            </div>
          </div>

          <!-- Form Prestasi -->
          <div class="grid px-6 grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
            v-show="activeTabIndex === 4">
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tingkat<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tingkat"
                  class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tingkat_pres_id" @update:valueFull="(objVal)=>{
                              valuesPrestasi.tingkat = objVal.value
                            }" @input="v=>valuesPrestasi.tingkat_pres_id=v"
                  :errorText="formErrorsPres.tingkat_pres_id?'failed':''" :hints="formErrorsPres.tingkat_pres_id"
                  valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
                                paginate: 1000
                              }
                            }" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
                  class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tahun" @input="v=>valuesPrestasi.tahun=v"
                  :options="ArrTahun" :errorText="formErrorsPres.tahun?'failed':''" :hints="formErrorsPres.tahun"
                  valueField="key" displayField="key" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Prestasi / Penghargaan<label
                    class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Prestasi / Penghargaan"
                  class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.nama_pres"
                  :errorText="formErrorsPres.nama_pres?'failed':''" @input="v=>valuesPrestasi.nama_pres=v"
                  :hints="formErrorsPres.nama_pres" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-12">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailPrestasi" :columns="[{
                              headerName: 'No',
                              cellRenderer: !actionText?null:'ButtonGrid',
                              valueGetter:p=>p.node.rowIndex + 1,
                              cellRendererParams: !actionText?null:{
                                showValue: true,
                                icon: 'times',
                                class: 'btn-text-danger',
                                click:(app)=>{
                                  if (app && app.params) {
                                    const row = app.params.node.data
                                    swal.fire({
                                      icon: 'warning', showDenyButton: true,
                                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                                    }).then((res) => {
                                      if (res.isConfirmed) {
                                        detailPrestasi = detailPrestasi.filter((e) => e._id != app.params.node.data._id)
                                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                                      }
                                    })
                                  }
                                }
                              },
                              width: 60,
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              field: 'tingkat',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'nama_pres',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'tahun',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addPrestasi" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailPrestasi = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>

            </div>
          </div>

          <!-- Form Organisasi -->
          <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
            v-show="activeTabIndex === 5">
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nama Organisasi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Organisasi"
                  class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.nama"
                  :errorText="formErrorsOrg.nama?'failed':''" @input="v=>valuesOrganisasi.nama=v"
                  :hints="formErrorsOrg.nama" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
                  class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.tahun" @input="v=>valuesOrganisasi.tahun=v"
                  :options="ArrTahun" :errorText="formErrorsOrg.tahun?'failed':''" :hints="formErrorsOrg.tahun"
                  valueField="key" displayField="key" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Jenis Organisasi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label=""
                  placeholder="Pilih Jenis Organisasi" class="col-span-12 !mt-0 w-full"
                  :value="valuesOrganisasi.jenis_org_id" @input="v=>valuesOrganisasi.jenis_org_id=v"
                  :errorText="formErrorsOrg.jenis_org_id?'failed':''" @update:valueFull="(objVal)=>{
                              valuesOrganisasi.jenis = objVal.value
                            }" :hints="formErrorsOrg.jenis_org_id" valueField="id" displayField="value" :api="{
                              url: `${store.server.url_backend}/operation/m_general`,
                              headers: {
                                'Content-Type': 'Application/json',
                                Authorization: `${store.user.token_type} ${store.user.token}`
                              },
                              params: {
                                simplest: true,
                                transform: false,
                                join: true,
                                where: `this.group='JENIS ORGANISASI' AND this.is_active='true'`,
                                paginate: 1000
                              }
                            }" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tingkat"
                  class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.kota_id" @input="v=>valuesOrganisasi.kota_id=v"
                  :errorText="formErrorsOrg.kota_id?'failed':''" :hints="formErrorsOrg.kota_id" @update:valueFull="(objVal)=>{
                                valuesOrganisasi.kota = objVal.value
                              }" valueField="id" displayField="value" :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                  where: `this.group='KOTA'`,
                                  paginate: 1000
                                }
                              }" :check="false" />
              </div>
            </div>

            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.posisi"
                  label="" placeholder="Tuliskan Posisi" :errorText="formErrorsOrg.posisi?'failed':''"
                  @input="v=>valuesOrganisasi.posisi=v" :hints="formErrorsOrg.posisi" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-12">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailOrganisasi" :columns="[{
                              headerName: 'No',
                              cellRenderer: !actionText?null:'ButtonGrid',
                              valueGetter:p=>p.node.rowIndex + 1,
                              cellRendererParams: !actionText?null:{
                                showValue: true,
                                icon: 'times',
                                class: 'btn-text-danger',
                                click:(app)=>{
                                  if (app && app.params) {
                                    const row = app.params.node.data
                                    swal.fire({
                                      icon: 'warning', showDenyButton: true,
                                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                                    }).then((res) => {
                                      if (res.isConfirmed) {
                                        detailOrganisasi = detailOrganisasi.filter((e) => e._id != app.params.node.data._id)
                                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                                      }
                                    })
                                  }
                                }
                              },
                              width: 60,
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              headerName: 'Nama Organisasi',
                              field: 'nama',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Jenis Organisasi',
                              field: 'jenis',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'posisi',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'tahun',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Kota',
                              field: 'kota',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addOrganisasi" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailOrganisasi = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>

            </div>
          </div>

          <!-- Form Bahasa -->
          <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
            v-show="activeTabIndex === 6">
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Bahasa yang Dikuasai<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Bahasa Yang Dikuasai"
                  class="col-span-12 !mt-0 w-full" :value="valuesBahasa.bhs_dikuasai"
                  :errorText="formErrorsBhs.bhs_dikuasai?'failed':''" @input="v=>valuesBahasa.bhs_dikuasai=v"
                  :hints="formErrorsBhs.bhs_dikuasai" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nilai Lisan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" type="number" label="" placeholder="Contoh: 89"
                  class="col-span-12 !mt-0 w-full" :value="valuesBahasa.nilai_lisan"
                  :errorText="formErrorsBhs.nilai_lisan?'failed':''" @input="v=>valuesBahasa.nilai_lisan=v"
                  :hints="formErrorsBhs.nilai_lisan" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Level Lisan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Contoh: 3-Intermidate"
                  class="col-span-12 !mt-0 w-full" :value="valuesBahasa.level_lisan"
                  :errorText="formErrorsBhs.level_lisan?'failed':''" @input="v=>valuesBahasa.level_lisan=v"
                  :hints="formErrorsBhs.level_lisan" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nilai Tertulis<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" type="number" label="" placeholder="Contoh 89"
                  class="col-span-12 !mt-0 w-full" :value="valuesBahasa.nilai_tertulis"
                  :errorText="formErrorsBhs.nilai_tertulis?'failed':''" @input="v=>valuesBahasa.nilai_tertulis=v"
                  :hints="formErrorsBhs.nilai_tertulis" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Level Tertulis<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Contoh: 3-Intermidate"
                  class="col-span-12 !mt-0 w-full" :value="valuesBahasa.level_tertulis"
                  :errorText="formErrorsBhs.level_tertulis?'failed':''" @input="v=>valuesBahasa.level_tertulis=v"
                  :hints="formErrorsBhs.level_tertulis" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-12">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailBahasa" :columns="[{
                              headerName: 'No',
                              cellRenderer: !actionText?null:'ButtonGrid',
                              valueGetter:p=>p.node.rowIndex + 1,
                              cellRendererParams: !actionText?null:{
                                showValue: true,
                                icon: 'times',
                                class: 'btn-text-danger',
                                click:(app)=>{
                                  if (app && app.params) {
                                    const row = app.params.node.data
                                    swal.fire({
                                      icon: 'warning', showDenyButton: true,
                                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                                    }).then((res) => {
                                      if (res.isConfirmed) {
                                        detailBahasa = detailBahasa.filter((e) => e._id != app.params.node.data._id)
                                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                                      }
                                    })
                                  }
                                }
                              },
                              width: 60,
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              headerName: 'Bahasa',
                              field: 'bhs_dikuasai',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Nilai Lisan',
                              field: 'nilai_lisan',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Level Lisan',
                              field: 'level_lisan',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Nilai Tertuis',
                              field: 'nilai_tertulis',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Level Tertulis',
                              field: 'level_tertulis',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addBahasa" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailPengalaman = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>

            </div>
          </div>

          <!-- Form Pengalaman Kerja -->
          <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
            v-show="activeTabIndex === 7">
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Nama Perusahaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Perusahaan"
                  class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.instansi"
                  :errorText="formErrorsPK.instansi?'failed':''" @input="v=>valuesPengalaman.instansi=v"
                  :hints="formErrorsPK.instansi" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Bidang Usaha<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Bidang Usaha"
                  class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.bidang_usaha"
                  :errorText="formErrorsPK.bidang_usaha?'failed':''" @input="v=>valuesPengalaman.bidang_usaha=v"
                  :hints="formErrorsPK.bidang_usaha" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">No. Telp<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan No Telp" type="number"
                  class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.no_tlp"
                  :errorText="formErrorsPK.no_tlp?'failed':''" @input="v=>valuesPengalaman.no_tlp=v"
                  :hints="formErrorsPK.no_tlp" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Posisi" class="col-span-12 !mt-0 w-full"
                  :value="valuesPengalaman.posisi" :errorText="formErrorsPK.posisi?'failed':''"
                  @input="v=>valuesPengalaman.posisi=v" :hints="formErrorsPK.posisi" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tahun Masuk<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="valuesPengalaman.thn_masuk" label="" placeholder="Pilih Tahun Masuk"
                  @input="v=>valuesPengalaman.thn_masuk=v" :options="ArrTahun"
                  :errorText="formErrorsPK.thn_masuk?'failed':''" :hints="formErrorsPK.thn_masuk" valueField="key"
                  displayField="key" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Tahun Keluar<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="valuesPengalaman.thn_keluar" label="" placeholder="Pilih Tahun Keluar"
                  @input="v=>valuesPengalaman.thn_keluar=v" :options="ArrTahun"
                  :errorText="formErrorsPK.thn_keluar?'failed':''" :hints="formErrorsPK.thn_keluar" valueField="key"
                  displayField="key" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Alamat Kantor<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Alamat Kantor" type="textarea"
                  class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.alamat_kantor"
                  :errorText="formErrorsPK.alamat_kantor?'failed':''" @input="v=>valuesPengalaman.alamat_kantor=v"
                  :hints="formErrorsPK.alamat_kantor" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
                  :value="valuesPengalaman.kota_id" @input="v=>valuesPengalaman.kota_id=v"
                  :errorText="formErrorsPK.kota_id?'failed':''" :hints="formErrorsPK.kota_id" label=""
                  placeholder="Pilih Kota" valueField="id" displayField="value" :api="{
                                url: `${store.server.url_backend}/operation/m_general`,
                                headers: {
                                  'Content-Type': 'Application/json',
                                  Authorization: `${store.user.token_type} ${store.user.token}`
                                },
                                params: {
                                  simplest: true,
                                  transform: false,
                                  join: true,
                                  where: `this.group='KOTA'`,
                                  paginate: 1000
                                }
                              }" :check="false" />
              </div>
            </div>
            <div class="col-span-8 md:col-span-6">
              <div class="grid grid-cols-12 items-center">
                <label class="col-span-12">Surat Refrensi<label class="text-red-500 space-x-0 pl-0">*</label></label>
                <div class="col-span-12 flex items-center">
                  <input :disabled="!actionText ? true : false" ref="fileSurat" type="file" accept="application/pdf"
                    class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                    :class="{'border-red-500': formErrorsPK.surat_referensi}" @change="fileSrtRef"
                    @input="(v)=>valuesPengalaman.surat_referensi=v">

                </div>
              </div>
            </div>
            <div class="col-span-8 md:col-span-12">
              <TableStatic customClass="h-50vh" ref="detail" :value="detailPengalaman" :columns="[{
                              headerName: 'No',
                              cellRenderer: !actionText?null:'ButtonGrid',
                              valueGetter:p=>p.node.rowIndex + 1,
                              cellRendererParams: !actionText?null:{
                                showValue: true,
                                icon: 'times',
                                class: 'btn-text-danger',
                                click:(app)=>{
                                  if (app && app.params) {
                                    const row = app.params.node.data
                                    swal.fire({
                                      icon: 'warning', showDenyButton: true,
                                      text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                                    }).then((res) => {
                                      if (res.isConfirmed) {
                                        detailPengalaman = detailPengalaman.filter((e) => e._id != app.params.node.data._id)
                                        app.params.api.applyTransaction({ remove: [app.params.node.data] })
                                      }
                                    })
                                  }
                                }
                              },
                              width: 60,
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['justify-center', 'bg-gray-50']
                            },
                            {
                              flex: 1,
                              headerName: 'Nama Instansi',
                              field: 'instansi',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Bidang Usaha',
                              field: 'bidang_usaha',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              field: 'posisi',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Tahun Masuk',
                              field: 'thn_masuk',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Tahun Keluar',
                              field: 'thn_keluar',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            {
                              flex: 1,
                              headerName: 'Alamat Kantor',
                              field: 'alamat_kantor',
                              sortable: false, resizable: true, filter: false,
                              cellClass: ['!border-gray-200', 'justify-center'],
                            },
                            ]">
                <template #header>
                  <button :disabled="!actionText ? true : false" @click="addPengalaman" type="button"
                    class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="plus" /> <span>Add to List</span>
                  </button>
                  <button :disabled="!actionText ? true : false" @click="detailPengalaman = []" type="button"
                    class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                    <icon fa="trash" /> <span>Remove</span>
                  </button>
                </template>
              </TableStatic>

            </div>
          </div>
          <div class="flex flex-row mb-4 px-6 justify-end space-x-[20px] mt-[5em]">
            <button @click="onBack" v-show="!isProfile"
              class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
              Batal
            </button>
            <button v-show="(actionText || isProfile) && (currentMenu?.can_create || currentMenu?.can_update)"
              @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px]">
              Simpan
            </button>
          </div>
          <!-- FORM END -->
        </div>
      </div>
    </div>
  @endverbatim
@endif