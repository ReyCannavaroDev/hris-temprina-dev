<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <!-- <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
      </div> -->
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="max-h-[450px]">
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
        <h1 class="text-20px font-bold">Form Lowongan Kerja</h1>
        <p class="text-gray-100">Lowongan Kerja</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" type="text" :value="values.nomor" class="w-full mt-3"
        @input="v=>values.nomor=v" :check="false" placeholder="Masukan Nomor" label="Nomor" />
    </div>
      <div v-show="!isProfile">
            <!-- <FieldSelect :bind="{ disabled:true}" class="w-full !mt-0" :value="values.m_comp_id" @input="v=>{ -->
            <FieldSelect :bind="{ disabled: !actionText}" class="w-full !mt-3" :value="values.m_comp_id" @input="v=>{
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
          }" placeholder="SBU" label="SBU" fa-icon="sort-desc" :check="false" />
          </div>

          <!-- SUB -->
          <div v-show="!isProfile">
            <!-- <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt-3" :value="values.m_subcomp_id" @input="v=>{ -->
            <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt-3" :value="values.m_subcomp_id" @input="v=>{
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
              }" :errorText="formErrors.m_subcomp_id?'failed':''" :hints="formErrors.m_subcomp_id" valueField="id"
              displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_subcomp`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false,
                }
          }" placeholder="SUB" label="SUB" fa-icon="sort-desc" :check="false" />

          </div>

          <!-- BRANCH -->
          <div v-show="!isProfile">
            <FieldSelect :bind="{ disabled: !actionText , clearable:true }" class="w-full mt-3" :value="values.m_branch_id"
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
              }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id" valueField="id"
              displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false,
                }
          }" placeholder="BRANCH" label="BRANCH" fa-icon="sort-desc" :check="false" />
          </div>

          <!-- DIVISI -->
          <div v-show="!isProfile">
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.m_divisi_id"
              @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''" @update:valueFull="(objVal)=>{
                  values.m_dept_id = null
                }" label="Divisi" placeholder="Pilih Divisi" :hints="formErrors.m_divisi_id" :api="{
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
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.m_posisi_id"
              @input="v=>values.m_posisi_id=v" @update:valueFull="(items)=>{
                  values.m_standart_gaji_id = null
                  $log('ikiposisi')
                }" :errorText="formErrors.m_posisi_id?'failed':''" label="Posisi" placeholder="Pilih Posisi"
              :hints="formErrors.m_posisi_id" :api="{
                    url: `${store.server.url_backend}/operation/m_posisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                }" valueField="id" displayField="name" :check="false" />
          </div>
    <!-- <div>
      <FieldX placeholder="Masukan Tanggal" label="Tanggal" :bind="{ readonly: !actionText }" type="date"
        :value="values.tanggal" class="w-full mt-3" @input="v=>values.tanggal=v" fa-icon="calender" :check="false" />
    </div> -->
       <div>
      <FieldX placeholder="Masukan Nama Loker" label="Nama" :bind="{ readonly: !actionText }" type="textarea"
        :value="values.title" class="w-full mt-3" @input="v=>values.title=v" :check="false" />
    </div>
    <div>
      <FieldSelect placeholder="Masukan Jenis Lowongan Pekerjaan" label="Jenis Lowongan Pekerjaan"
        :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.jenis_loker_id"
        @input="v=>values.jenis_loker_id=v" :errorText="formErrors.jenis_loker_id?'failed':''"
        :hints="formErrors.jenis_loker_id" @update:valueFull="(objVal)=>{
                  values.jenis_loker_id = null
                }" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      where: `this.group='JENIS LOKER'`
                    }
              }" valueField="id" :check="false" />
    </div>

    <div>
      <FieldSelect placeholder="Masukan Prioritas" label="Prioritas" :bind="{ disabled: !actionText, clearable:false }"
        class="w-full mt-3" :value="values.prioritas_id" @input="v=>values.prioritas_id=v"
        :errorText="formErrors.prioritas_id?'failed':''" :hints="formErrors.prioritas_id" @update:valueFull="(objVal)=>{
                  values.prioritas_id = null
                }" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                     params: {
                      where: `this.group='PRIORITAS'`
                    }
              }" valueField="id" :check="false" />
    </div>

    <div>
      <FieldSelect placeholder="Pilih Jenis Kelamin" label="Jenis Kelamin" 
        :bind="{ disabled: !actionText, clearable:false }"
        class="w-full mt-3" :value="values.jk_id" @input="v=>values.jk_id=v"
        :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id" 
        displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              where: `this.group='JENIS KELAMIN'`
            }
        }" valueField="id" :check="false" />
    </div>

    <div>
      <FieldSelect placeholder="Pilih Status Karyawan" label="Status Karyawan" 
        :bind="{ disabled: !actionText, clearable:false }"
        class="w-full mt-3" :value="values.status_kary_id" @input="v=>values.status_kary_id=v"
        :errorText="formErrors.status_kary_id?'failed':''" :hints="formErrors.status_kary_id" 
        displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              where: `this.group='STATUS KARYAWAN'`
            }
        }" valueField="id" :check="false" />
    </div>

    <div>
      <FieldNumber placeholder="Masukan Jumlah" label="Jumlah Kebutuhan" 
        :bind="{ readonly: !actionText }"
        :value="values.jumlah" class="w-full mt-3" @input="v=>values.jumlah=v" 
        :check="false" />
    </div>

    <div>
      <FieldX placeholder="Masukan Tanggal Dibuka" label="Tanggal Dibuka" :bind="{ readonly: !actionText, disabled: !actionText }" type="date"
        :value="values.tgl_dibuka" class="w-full mt-3" @input="v=>values.tgl_dibuka=v" fa-icon="calender"
        :check="false" />
    </div>

    <div>
      <FieldX placeholder="Masukan Tanggal Berakhir" label="Tanggal Berakhir" :bind="{ readonly: !actionText, disabled: !actionText }" type="date"
        :value="values.tgl_akhir" class="w-full mt-3" @input="v=>values.tgl_akhir=v" fa-icon="calender"
        :check="false" />
    </div>

     <div>
      <FieldX placeholder="Masukan Deskripsi" label="Deskripsi" :bind="{ readonly: !actionText }" type="textarea"
        :value="values.deskripsi" class="w-full mt-3" @input="v=>values.deskripsi=v" :check="false" />
    </div>

        <div>
      <FieldSelect v-show="route.query.action?.toLowerCase() === 'verifikasi'" :value="values.target_id"
        @input="v=>values.target_id=v" :errorText="formErrors.target_id?'failed':''" :hints="formErrors.target_id"
        valueField="id" displayField="nama_lengkap" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel'
              }
          }" placeholder="Pilih Target Approval" label="Target Approval" fa-icon="" :check="false" />

    </div>

  <div class="px-4 pb-4 col-span-3">
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mt-4">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-gray-700 flex items-center">
          <icon fa="list-ul" class="mr-2" /> Daftar Kualifikasi
        </h2>
        <button v-show="actionText" type="button" @click="values.t_loker_d_kualifikasi.push({ value: '' })"
          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm transition-all flex items-center shadow-sm">
          <icon fa="plus" class="mr-1" /> Tambah Kualifikasi
        </button>
      </div>

      <div class="space-y-3">
        <div v-for="(item, index) in values.t_loker_d_kualifikasi" :key="index" 
          class="flex items-start gap-3 group animate-fade-in-down">
          
          <div class="mt-7 text-gray-500 font-medium text-sm">{{ index + 1 }}.</div>
          
          <div class="flex-grow">
            <FieldX :bind="{ readonly: !actionText }"
              type="text" :value="item.value" class="w-full" 
              @input="v => item.value = v" :check="false" />
          </div>

          <button type="button" @click="values.t_loker_d_kualifikasi.splice(index, 1)"
            class="mt-5 p-2 text-red-500 hover:bg-red-50 rounded-md transition-colors"
            v-if="values.t_loker_d_kualifikasi.length > 1">
            <icon fa="trash" />
          </button>
        </div>
      </div>

      <div v-if="values.t_loker_d_kualifikasi.length === 0" 
        class="text-center py-6 text-gray-400 italic border-2 border-dashed border-gray-200 rounded-md">
        Klik tombol tambah untuk mengisi kualifikasi
      </div>
    </div>
          <div v-show="route.query.is_approval || ['APPROVAL', 'APPROVED', 'REJECT', 'REVISED'].includes(values.status)"
        class="<md:col-span-1 col-span-2 p-4 grid <md:grid-cols-1 grid-cols-3 gap-2">

        <!-- <div>
          <table class=" w-[100%] my-3 border">
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Nomor</td>
              <td class="border px-2 py-1">{{ values.approval?.nomor ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Tanggal</td>
              <td class="border px-2 py-1">{{ values.approval?.created_at ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Pemohon</td>
              <td class="border px-2 py-1">{{ values.approval?.creator ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Status</td>
              <td class="border px-2 py-1">{{ values.approval?.status ?? '-' }}</td>
            </tr>
          </table>
        </div> -->
        <!-- <div class="">
          <table class=" w-[100%] my-3 ">
            <tr>
              <td class=" px-2 py-1">
                <button
                  v-show="route.query.is_approval || values.status?.toUpperCase() !== 'APPROVED'"
                    @click="openModal(values?.trx?.id ?? 0)"
                    class="hover:text-blue-500">
                    <icon fa="table" size="sm"/>
                    Log Approval
                  </button>
              </td>
            </tr>
          </table>
        </div> -->
        <div v-show="route.query.is_approval" class="w-1/2 mt-3">
          <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="textarea" :bind="{ readonly: !route.query.is_approval }" class="w-full py-2 !mt-0"
            :value="values.catatan" :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v"
            :hints="formErrors.catatan" :check="false" label="" placeholder="Tuliskan catatan" />
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
        v-show="route.query.is_approval" class="mx-1 bg-green-500 text-white hover:bg-green-600 rounded-lg py-[10px] px-[28px] " @click="onProcess('approve')">
        Approve
      </button>
    <button
        v-show="route.query.is_approval" class="mx-1 bg-rose-500 text-white hover:bg-rose-600 rounded-lg py-[10px] px-[28px] " @click="onProcess('reject')">
        Reject
      </button>
    <button
        v-show="route.query.is_approval" class="mx-1 bg-amber-500 text-white hover:bg-amber-600 rounded-lg py-[10px] px-[28px] " @click="onProcess('revise')">
        Revise
      </button>
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
        v-show="actionText && data.can_create && !route.query.action?.toLowerCase() === 'verifikasi'"
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
    <button
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="route.query.action?.toLowerCase() === 'verifikasi'"
        @click="onApproval"
      >
        <icon fa="location-arrow" />
        Send Approval
      </button>
  </div>
</div>
@endverbatim
@endif