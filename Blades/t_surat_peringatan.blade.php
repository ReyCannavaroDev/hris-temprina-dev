@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
      </div>
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New SP
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="max-h-[450px]" />
</div>
@else

@verbatim
<div class="flex flex-col border rounded-md shadow-md w-full p-0 bg-white border-none min-h-screen">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali" @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Surat Peringatan</h1>
        <p class="text-gray-100">Disiplin Karyawan</p>
      </div>
    </div>
  </div>

  <div class="p-3 grid <md:grid-cols-1 grid-cols-3 gap-x-2 gap-y-4">
    <div>
      <FieldX :bind="{ readonly: false }" class="w-full !mt0 " :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
        @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false" label="Nomor Surat"
        placeholder="Input Nomor SP" />
    </div>

    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full mt-6" :value="values.m_kary_id" @input="v=>{
              if(v){
                values.m_kary_id=v
              }else{
                values.m_kary_id=null
                values.m_posisi_id=null
              }
            }" @update:valueFull="obj => {
              $log(obj)
                if (obj) {
                  values.m_kary_id = obj.id; 
                  values.m_posisi_id = obj.m_posisi_id; 
                } else {
                  values.m_kary_id = null;
                }
              }" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id" valueField="id"
        displayField="nama_lengkap" :api="apiKary" placeholder="Pilih Karyawan" label="Karyawan" :check="false"
        :columns="[
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
              field: 'kode',
              headerName: 'Kode',
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
              field: 'm_branch.name',
              headerName: 'Cabang',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },  
            {
              flex: 1,
              field: 'm_posisi.name',
              headerName: 'Jabatan',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            }, 
          ]" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full !mt0 " :value="values.tgl" label="Tanggal Terbit"
        @input="v=>values.tgl=v" :errorText="formErrors.tgl?'failed':''" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt0" :value="values.level_sp" 
        @input="v=>values.level_sp=v"
        label="Level SP"
        placeholder="Pilih Level SP" valueField="id" displayField="value" :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { Authorization: `${store.user.token_type} ${store.user.token}` },
          params: { where: `this.is_active = true AND this.group='LEVEL SP'` }
        }" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full !mt0" :value="values.masa_berlaku" 
        @input="v=>values.masa_berlaku=v" label="Masa Berlaku (Bulan)" placeholder="Masa Berlaku (Bulan)" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0" :value="values.m_posisi_id"
        label="Jabatan Saat Ini" valueField="id" displayField="name" :api="{
          url: `${store.server.url_backend}/operation/m_posisi`,
          headers: { Authorization: `${store.user.token_type} ${store.user.token}` }
        }" :check="false" />
    </div>

    <div>
      <FieldUpload class="w-full !mt0 " :bind="{ readonly: !actionText }" :value="values.file_dokumen"
        @input="(v)=>values.file_dokumen=v" :maxSize="5"
        :api="{
          url: `${store.server.url_backend}/operation/t_surat_peringatan/upload`,
          headers: { Authorization: `${store.user.token_type} ${store.user.token}` },
          params: { field: 'file_dokumen' }
        }" label="Upload Scan SP" accept="application/pdf" :check="false" />
    </div>

    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full mt-6" :value="values.signature_id" @input="v=>{
              if(v){
                values.signature_id=v
              }else{
                values.signature_id=null
              }
            }" @update:valueFull="obj => {
              $log(obj)
                if (obj) {
                  values.signature_id = obj.id; 
                } else {
                  values.signature_id = null;
                }
              }" :errorText="formErrors.signature_id?'failed':''" :hints="formErrors.signature_id" valueField="id"
        displayField="nama_lengkap" :api="apiKary" placeholder="Pilih Target" label="Target Tanda Tangan" :check="false"
        :columns="[
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
              field: 'kode',
              headerName: 'Kode',
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
              field: 'm_branch.name',
              headerName: 'Cabang',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },  
            {
              flex: 1,
              field: 'm_posisi.name',
              headerName: 'Jabatan',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },    
          ]" />
    </div>

    <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt0 " :value="values.status" label="Status" />
    </div>

    <div class="col-span-full mt-4">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold text-gray-700">Daftar Pelanggaran / Alasan</h3>
        <button :disabled="!actionText" @click="values.t_surat_peringatan_d_pelanggaran.push({value:''})" type="button" 
          class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-sm flex items-center gap-2">
          <icon fa="plus" /> Tambah Pelanggaran
        </button>
      </div>
      <table class="w-full table-auto border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <td class="p-2 text-center w-[5%] border">No.</td>
            <td class="p-2 border">Detail Pelanggaran</td>
            <td class="p-2 text-center w-[10%] border">Aksi</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in (values.t_surat_peringatan_d_pelanggaran || [])" :key="i">
            <td class="p-2 text-center border">{{ i + 1 }}</td>
            <td class="p-2 border">
              <FieldX class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.value" @input="(v)=>item.value=v" :check="false" />
            </td>
            <td class="p-2 border text-center">
              <button @click="values.t_surat_peringatan_d_pelanggaran.splice(i, 1)" class="text-red-500 hover:text-red-700">
                <icon fa="trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="col-span-full mt-4">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold text-gray-700">Tembusan</h3>
        <button :disabled="!actionText" @click="values.t_surat_peringatan_d_tembusan.push({value:''})" type="button" 
          class="bg-gray-600 hover:bg-gray-700 text-white py-1 px-3 rounded text-sm flex items-center gap-2">
          <icon fa="plus" /> Tambah Tembusan
        </button>
      </div>
      <table class="w-full table-auto border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <td class="p-2 text-center w-[5%] border">No.</td>
            <td class="p-2 border">Pihak Terkait</td>
            <td class="p-2 text-center w-[10%] border">Aksi</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in (values.t_surat_peringatan_d_tembusan || [])" :key="i">
            <td class="p-2 text-center border">{{ i + 1 }}</td>
            <td class="p-2 border">
              <FieldX class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.value" @input="(v)=>item.value=v" :check="false" />
            </td>
            <td class="p-2 border text-center">
              <button @click="values.t_surat_peringatan_d_tembusan.splice(i, 1)" class="text-red-500 hover:text-red-700">
                <icon fa="trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <div class="flex flex-row items-center justify-end space-x-2 p-4 bg-gray-50 border-t">
    <button class="bg-red-600 text-white px-4 py-2 rounded-md" v-show="actionText" @click="onReset(true)">Reset</button>
    <button class="bg-green-600 text-white px-4 py-2 rounded-md" v-show="actionText" @click="onSave">Simpan SP</button>
  </div>
</div>
@endverbatim
@endif