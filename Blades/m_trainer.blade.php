@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl border-t-10 border-gray-500">
  <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">AKTIF</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">NON AKTIF</button>
      </div>
    </div>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <div>
        <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        v-if="data.can_create"
          class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
          Create New
        </RouterLink>
      </div>
    </template>
  </TableApi>
</div>
@else

@verbatim

<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded-2xl shadow-sm px-6 py-6 <md:w-full w-full bg-white">

      <!-- HEADER START -->
      <div class=" text-white rounded-t-md pt-2 mb-6">
        <div class="flex items-center">
          <div>
            <h1 class="text-20px text-black font-bold">Master Trainer </h1>
          </div>
        </div>
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-20">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Kode<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX :bind="{ readonly: true }" label="" class="w-full py-2 !mt-0" :value="values.kode"
            :errorText="formErrors.kode?'failed':''" @input="v=>values.kode=v" :hints="formErrors.kode"
            :check="false" label="" placeholder="Auto Generate by System" />
        </div>

        <div>
          <label class="col-span-12">Nama Trainer<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText, disabled:!actionText }" class="w-full py-2 !mt-0"
            :value="values.nama_trainer" :errorText="formErrors.nama_trainer?'failed':''"
            @input="v=>values.nama_trainer=v" :hints="formErrors.nama_trainer" :check="false" label=""
            placeholder="Pilih Nama Trainer" />
        </div>

        <!-- <div>
          <label class="font-semibold">Karyawan<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldPopup :bind="{ readonly: isRead, clearable:false }" class="w-full py-2 !mt-0" :value="values.m_kary_id"
            @input="(v)=>{
              values.m_kary_id=v
             }" @update:valueFull="(v)=>{
              $log(v)
              }" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id" valueField="id"
            displayField="nama_lengkap" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    searchfield:'this.id, this.nama_depan, this.nama_belakang, this.nama_lengkap, this.nik, m_divisi.nama, m_zona.nama, m_dir.nama',
                  }
                }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[
              {
                headerName: 'No',
                valueGetter: (p) => p.node.rowIndex + 1,
                width: 60,
                sortable: false, 
                resizable: false, 
                filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'nik',
                headerName: 'NIK',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'nama_lengkap',
                headerName: 'Nama',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_zona.nama',
                headerName: 'Zona',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },              
              {
                flex: 1,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },      
              {
                flex: 1,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },                                         
            ]" />
        </div> -->

        <div>
          <label class="col-span-12">Jenis Training<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0"
            :bind="{ disabled: !actionText, readonly: !actionText, clearable:false }" :value="values.jenis_training_id"
            @input="v=>values.jenis_training_id=v" :errorText="formErrors.jenis_training_id?'failed':''"
            :hints="formErrors.jenis_training_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false,
                  where: `this.group = 'JENIS TRAINING'`
                }
            }" placeholder="Pilih Jenis Training" label="" fa-icon="" :check="false" />

        </div>

        <div>
          <label class="col-span-12">Alamat<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.alamat"
            :errorText="formErrors.alamat?'failed':''" @input="v=>values.alamat=v" :hints="formErrors.alamat"
            placeholder="Masukkan Alamat" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">No. Telepon/Fak<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.no_hp"
            :errorText="formErrors.no_hp?'failed':''" @input="v=>values.no_hp=v" :hints="formErrors.no_hp"
            placeholder="Masukkan Start " label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Contact Person<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.cp"
            :errorText="formErrors.cp?'failed':''" @input="v=>values.cp=v" :hints="formErrors.cp"
            placeholder="Masukkan Deadline" label="" fa-icon="" :check="false" />
        </div>

         <div>
          <label class="col-span-12">Tipe Trainer<label class="text-red-500 space-x-0 pl-0 !mt-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable: false }" :value="values.tipe_trainer"
            @input="v => values.tipe_trainer = v" :errorText="formErrors.tipe_trainer ? 'failed' : ''"
            :hints="formErrors.tipe_trainer" valueField="key" displayField="key" :options="[{'id' : 1 , 'key' : 'INTERNAL'}, {'id': 0, 'key' : 'EXTERNAL'}]" placeholder="Pilih Tipe" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0 !mt-0">*</label></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable: false }" :value="values.is_active"
            @input="v => values.is_active = v" :errorText="formErrors.is_active ? 'failed' : ''"
            :hints="formErrors.is_active" valueField="id" displayField="key" :options="[{'id' : 1 , 'key' : 'Aktif'}, {'id': 0, 'key' : 'Nonaktif'}]" placeholder="Pilih Status" label="" fa-icon="" :check="false" />
        </div>


      </div>
      <hr class="mt-10">
      <!-- FORM END -->
      <div class="flex justify-end gap-4">
        <button @click="onBack" class="mt-2 bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Batal 
        </button>
        <button v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)" @click="onSave" class="mt-2 bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Simpan
        </button>
      </div>
    </div>

  </div>


</div>

@endverbatim
@endif