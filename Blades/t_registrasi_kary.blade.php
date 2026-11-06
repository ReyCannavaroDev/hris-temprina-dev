<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
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
          <h1 class="text-20px font-bold">Form Registrasi Karyawan</h1>
          <p class="text-gray-100">Registrasi Karyawan</p>
        </div>
      </div>
    </div>
    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldPopup
            :bind="{ readonly: !actionText }" class="w-full !mt-3"
            :value="values.t_pelamar_id" @input="(v)=>values.t_pelamar_id=v"
            :errorText="formErrors.t_pelamar_id?'failed':''" 
            :hints="formErrors.t_pelamar_id" 
            @update:valueFull="(items)=>{
              if(items){
                values.m_dir_id = items.m_dir_id
                values.m_divisi_id = items.m_divisi_id
                values.m_dept_id = items.m_dept_id
                values.m_posisi_id = items.m_posisi_id
                values.tempat_lahir = items.tempat_lahir
                values.tgl_lahir = items.tgl_lahir
                values.nama_pelamar = items.nama_pelamar
                values.tgl_lahir = items.tgl_lahir
                values.ref = items.ref
                getNilai()
              }else if(items === null){
                values.m_dir_id = null 
                values.m_divisi_id = null 
                values.m_dept_id = null 
                values.m_posisi_id = null 
                values.tempat_lahir = null 
                values.tgl_lahir = null 
                values.nama_pelamar = null 
                values.tgl_lahir = null 
                values.ref = null 
                values.nilai_tes = null
              }
              
            }"
            valueField="id" displayField="nama_pelamar"
            :api="{
              url: `${store.server.url_backend}/operation/t_pelamar`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                searchfield: 'this.nama_pelamar, m_dir.nama, m_divisi.nama, m_dept.nama, m_posisi.desc_kerja'
              }
            }"
            placeholder="Pilih Pelamar" label="Pelamar" :check="false" 
            :columns="[{
              headerName: 'No',
              valueGetter:(p)=>p.node.rowIndex + 1,
              width: 60,
              sortable: false, resizable: false, filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              field: 'nama_pelamar',
              wrapText: true,
              headerName:  'Nama',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_dir.nama',
              wrapText: true,
              headerName:  'Direktorat',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_divisi.nama',
              wrapText: true,
              headerName:  'Divisi',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_dept.nama',
              wrapText: true,
              headerName:  'Departemen',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_posisi.desc_kerja',
              wrapText: true,
              headerName:  'Posisi',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            ]"
          />
      </div>
      <div>   
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3"
          :value="values.m_dir_id" @input="v=>values.m_dir_id=v"
          :errorText="formErrors.m_dir_id?'failed':''" 
          label="Direktorat" placeholder="Pilih Direktorat"
          :hints="formErrors.m_dir_id"
          :api="{
              url: `${store.server.url_backend}/operation/m_dir`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.is_active = 'true'`
              }
          }"
          valueField="id" displayField="nama" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3"
          :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v"
          :errorText="formErrors.m_divisi_id?'failed':''" 
          @update:valueFull="(objVal)=>{
            values.m_dept_id = null
          }"
          label="Divisi" placeholder="Pilih Divisi"
          :hints="formErrors.m_divisi_id"
          :api="{
              url: `${store.server.url_backend}/operation/m_divisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.is_active = 'true'`
              }
          }"
          valueField="id" displayField="nama" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3"
          :value="values.m_dept_id" @input="v=>values.m_dept_id=v"
          :errorText="formErrors.m_dept_id?'failed':''" 
          label="Departemen" placeholder="Pilih Departemen"
          :hints="formErrors.m_dept_id"
          :api="{
              url: `${store.server.url_backend}/operation/m_dept`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `m_divisi_id=${values.m_divisi_id} AND this.is_active = 'true'`
              }
          }"
          valueField="id" displayField="nama" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3"
          :value="values.m_posisi_id" @input="v=>values.m_posisi_id=v"
          :errorText="formErrors.m_posisi_id?'failed':''" 
          label="Posisi" placeholder="Pilih Posisi"
          :hints="formErrors.m_posisi_id"
          :api="{
              url: `${store.server.url_backend}/operation/m_posisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                where: `this.is_active = 'true'`
              }
          }"
          valueField="id" displayField="desc_kerja" :check="false"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: true }" label="Nama" placeholder="Autofield Nama" class="w-full !mt-3"
          :value="values.nama_pelamar" :errorText="formErrors.nama_pelamar?'failed':''"
          @input="v=>values.nama_pelamar=v" :hints="formErrors.nama_pelamar" :check="false"
        />
      </div>
      <div>
        <FieldSelect
          :bind="{ disabled: true, clearable:false }" class="w-full !mt-3"
          :value="values.tempat_lahir" @input="v=>values.tempat_lahir=v"
          :errorText="formErrors.tempat_lahir?'failed':''" 
          :hints="formErrors.tempat_lahir" label="Kota" placeholder="Autofield Kota"
          valueField="value" displayField="value"
          :api="{
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
          }"
          :check="false" label="Tempat Lahir" placeholder="Pilih Tempat Lahir"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true, disabled: true}" type="date" class="w-full !mt-3"
          :value="values.tgl_lahir" label="Tanggal Lahir" placeholder="Autofield Tanggal Lahir" :errorText="formErrors.tgl_lahir?'failed':''"
          @input="v=>values.tgl_lahir=v" :hints="formErrors.tgl_lahir" :check="false"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" label="Referensi" placeholder="Autofield Referensi" class="w-full !mt-3"
          :value="values.ref" :errorText="formErrors.ref?'failed':''"
          @input="v=>values.ref=v" :hints="formErrors.ref" :check="false"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" label="Nilai Tes" placeholder="Autofield Nilai Tes" class="w-full !mt-3"
          :value="values.nilai_tes" :errorText="formErrors.nilai_tes?'failed':''"
          @input="v=>values.nilai_tes=v" :hints="formErrors.nilai_tes" :check="false"
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
        class="bg-yellow-600 text-white font-semibold hover:bg-yellow-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText" 
        @click="onPostedKary('tolak')" 
      >
        <icon fa="cancel" />
        Tolak
      </button>
      <button 
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="actionText" 
        @click="onPostedKary('konfirmasi')" 
      >
        <icon fa="save" />
        Konfirmasi
      </button>
    </div>
  </div>
@endverbatim
@endif