<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
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
    class="">
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

 <div class="p-5 flex flex-col gap-6">
    <div class="grid <md:grid-cols-1 grid-cols-3 gap-4 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
      <FieldX label="Nomor" :bind="{ readonly: true }" :value="values.nomor" class="w-full mt-3" @input="v=>values.nomor=v" :check="false" />
      
      <!-- <FieldSelect label="SBU" :value="values.m_comp_id" :bind="{ disabled: !actionText }" class="w-full" @update:valueFull="obj => values.m_comp_id = obj?.id || null" :api="{ url: `${store.server.url_backend}/operation/m_comp`, params: { simplest:true } }" displayField="name" valueField="id" :check="false" />
      
      <FieldSelect label="SUB" :value="values.m_subcomp_id" :bind="{ disabled: !actionText }" class="w-full" @update:valueFull="obj => values.m_subcomp_id = obj?.id || null" :api="{ url: `${store.server.url_backend}/operation/m_subcomp`, params: { simplest:true } }" displayField="name" valueField="id" :check="false" />

      <FieldSelect label="Branch" :value="values.m_branch_id" :bind="{ disabled: !actionText }" class="w-full" @update:valueFull="obj => values.m_branch_id = obj?.id || null" :api="{ url: `${store.server.url_backend}/operation/m_branch`, params: { simplest:true } }" displayField="name" valueField="id" :check="false" />

      <FieldSelect label="Divisi" :value="values.m_divisi_id" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.m_divisi_id=v" :api="{ url: `${store.server.url_backend}/operation/m_divisi`, params: { scopes:'Name' } }" displayField="name.value" valueField="id" :check="false" />

      <FieldSelect label="Posisi" :value="values.m_posisi_id" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.m_posisi_id=v" :api="{ url: `${store.server.url_backend}/operation/m_posisi` }" displayField="name" valueField="id" :check="false" />
      -->

      <div v-show="!isProfile">
            <!-- <FieldSelect :bind="{ disabled:true}" class="w-full !mt-0" :value="values.m_comp_id" @input="v=>{ -->
            <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.m_comp_id" @input="v=>{
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
            <!-- <FieldSelect :bind="{ disabled: false }" class="w-full !mt-3" :value="values.m_subcomp_id" @input="v=>{ -->
            <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-3" :value="values.m_subcomp_id" @input="v=>{
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
            <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.m_branch_id"
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
            <FieldSelect :bind="{ disabled: !actionText || !values.m_branch_id, clearable:true }" class="w-full mt-3" :value="values.m_divisi_id"
              @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''" @update:valueFull="(objVal)=>{
                  values.m_dept_id = null
                }" label="Divisi" placeholder="Pilih Divisi" :hints="formErrors.m_divisi_id" :api="{
                    url: `${store.server.url_backend}/operation/m_divisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      scopes:'Name',
                      simplest:true,
                      groupBy: 'name.value',
                      where: `this.is_active = 'true'` + (values.m_branch_id ? ` AND this.m_branch_id = '${values.m_branch_id}'` : '')
                    }
                }" valueField="id" displayField="name.value" :check="false" />

          </div>


          <div v-show="!isProfile">
            <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full mt-3" :value="values.m_posisi_id"
              @input="v=>values.m_posisi_id=v" @update:valueFull="(items)=>{
                  values.m_standart_gaji_id = null
                  $log('ikiposisi')
                }" :errorText="formErrors.m_posisi_id?'failed':''" label="Posisi" placeholder="Pilih Posisi"
              :hints="formErrors.m_posisi_id" :api="{
                    url: `${store.server.url_backend}/operation/m_posisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                }" valueField="id" displayField="name" :check="false" />
          </div>
    </div>

    <div class="grid <md:grid-cols-1 grid-cols-3 gap-4">
      <div class="col-span-1 <md:col-span-1">
        <FieldX placeholder="Masukkan nama lowongan" label="Nama Lowongan" type="textarea" :bind="{ disabled: !actionText }" :value="values.title" class="w-full" @input="v=>values.title=v" :check="false" />
      </div>

      <!-- <FieldSelect placeholder="Pilih jenis pekerjaan" label="Jenis Pekerjaan" :value="values.jenis_loker_id" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.jenis_loker_id=v" :api="{ url: `${store.server.url_backend}/operation/m_general`, headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`}, params: { where: `this.group='JENIS LOKER'` } }" displayField="value" valueField="id" :check="false" /> -->

      <FieldSelect placeholder="Pilih Prioritas" label="Prioritas" :value="values.prioritas_id" :bind="{ disabled: !actionText, clearable:true }" class="w-full" @input="v=>values.prioritas_id=v" :api="{ url: `${store.server.url_backend}/operation/m_general`, headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`}, params: { where: `this.group='PRIORITAS'` } }" displayField="value" valueField="id" :check="false" />

      <!-- <FieldSelect label="Jenis Kelamin" :value="values.jk_id" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.jk_id=v" :api="{ url: `${store.server.url_backend}/operation/m_general`, params: { where: `this.group='JENIS KELAMIN'` } }" displayField="value" valueField="id" :check="false" />

      <FieldSelect label="Status Karyawan" :value="values.status_kary_id" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.status_kary_id=v" :api="{ url: `${store.server.url_backend}/operation/m_general`, params: { where: `this.group='STATUS KARYAWAN'` } }" displayField="value" valueField="id" :check="false" /> -->

      <!-- <div>
      <FieldSelect placeholder="Masukan Jenis Lowongan Pekerjaan" label="Jenis Lowongan Pekerjaan"
        :bind="{ disabled: !actionText, clearable:false }" class="w-full" :value="values.jenis_loker_id"
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
        class="w-full" :value="values.prioritas_id" @input="v=>values.prioritas_id=v"
        :errorText="formErrors.prioritas_id?'failed':''" :hints="formErrors.prioritas_id" @update:valueFull="(objVal)=>{
                  values.prioritas_id = null
                }" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                     params: {
                      where: `this.group='PRIORITAS'`
                    }
              }" valueField="id" :check="false" />
    </div> -->

    <div>
      <FieldSelect placeholder="Pilih Jenis Kelamin" label="Jenis Kelamin" 
        :bind="{ disabled: !actionText, clearable:true }"
        class="w-full" :value="values.jk_id" @input="v=>values.jk_id=v"
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
        class="w-full" :value="values.status_kary_id" @input="v=>values.status_kary_id=v"
        :errorText="formErrors.status_kary_id?'failed':''" :hints="formErrors.status_kary_id" 
        displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              where: `this.group='STATUS KARYAWAN'`
            }
        }" valueField="id" :check="false" />
    </div>

      <FieldNumber placeholder="Masukkan jumlah kebutuhan" label="Jumlah Kebutuhan" :value="values.jumlah" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.jumlah=v" :check="false" />

      <FieldX placeholder="Masukkan tanggal awal dibuka" label="Tanggal Dibuka" type="date" :value="values.tgl_dibuka" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.tgl_dibuka=v" :check="false" />

      <FieldX placeholder="Masukkan tanggal akhir dibuka" label="Tanggal Berakhir" type="date" :value="values.tgl_akhir" :bind="{ disabled: !actionText }" class="w-full" @input="v=>values.tgl_akhir=v" :check="false" />

      <FieldSelect v-if="route.query.action?.toLowerCase() === 'verifikasi'" label="Target Approval" :value="values.target_id" class="w-full" @input="v=>values.target_id=v" :api="{ url: `${store.server.url_backend}/operation/m_kary`, params: { scopes: 'higherlevel' } }" displayField="nama_lengkap" valueField="id" :check="false" />
    </div>

    <div class="mt-2">
      <label class="font-semibold block mb-2 text-gray-700">Deskripsi Pekerjaan</label>
      <Writer placeholder="Masukan Text" :value="values.deskripsi" class="w-full min-h-[200px]" @input="v=>values.deskripsi=v" :check="false" />
    </div>

    <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
      <div class="flex justify-between items-center mb-5">
        <h2 class="font-bold text-gray-800 flex items-center">
          <icon fa="list-check" class="mr-2 text-blue-600" /> Kualifikasi Spesifik
        </h2>
        <button type="button" @click="values.t_loker_d_kualifikasi.push({ value: '' })" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center shadow-md active:scale-95">
          <icon fa="plus" class="mr-2" /> Tambah Item
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div v-for="(item, index) in values.t_loker_d_kualifikasi" :key="index" class="flex items-center gap-4 bg-white p-2 rounded-lg border border-gray-100 shadow-sm transition-all hover:border-blue-200">
          <div class="bg-gray-100 text-gray-600 font-bold rounded-full w-8 h-8 flex items-center justify-center text-xs">
            {{ index + 1 }}
          </div>
          <div class="flex-grow">
            <FieldX :bind="{ disabled: !actionText, clearable:false }" type="text" :value="item.value" class="w-full !mt-0" @input="v => item.value = v" :check="false" />
          </div>
          <button  v-show="(actionText || isProfile) && (currentMenu?.can_create || currentMenu?.can_update)" type="button" @click="values.t_loker_d_kualifikasi.splice(index, 1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all" v-if="values.t_loker_d_kualifikasi.length > 1">
            <icon fa="trash-can" />
          </button>
        </div>
      </div>

      <div v-if="values.t_loker_d_kualifikasi.length === 0" class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl bg-white">
        <icon fa="clipboard-list" size="2x" class="mb-2 block opacity-20" />
        Belum ada kualifikasi ditambahkan.
      </div>
    </div>

    <div v-if="route.query.is_approval || ['APPROVAL', 'APPROVED', 'REJECT', 'REVISED'].includes(values.status)" class="bg-amber-50 p-5 border border-amber-200 rounded-xl">
      <div v-if="route.query.is_approval" class="max-w-2xl">
        <label class="font-bold text-amber-900 block mb-2">Catatan Feedback / Approval <span class="text-red-500">*</span></label>
        <FieldX type="textarea" :bind="{ readonly: false }" class="w-full !mt-0" :value="values.catatan" :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan" placeholder="Berikan alasan jika Revisi atau Reject..." :check="false" />
      </div>
      <div v-else class="flex gap-4 items-center">
        <div class="px-3 py-1 bg-amber-200 text-amber-800 rounded text-xs font-bold uppercase">{{ values.status }}</div>
        <p class="text-sm text-amber-900"><span class="font-semibold">Catatan:</span> {{ values.catatan || 'Tidak ada catatan.' }}</p>
      </div>
    </div>
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

        <button   v-show="(actionText || isProfile) && (currentMenu?.can_create || currentMenu?.can_update)"
        class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        @click="onReset(true)"
      >
        <icon fa="times" />
        Reset
      </button>

    <button  v-show="(actionText || isProfile) && (currentMenu?.can_create || currentMenu?.can_update)"
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
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