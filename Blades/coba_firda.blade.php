<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
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
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="max-h-[450px]">
    <!-- <template #header>
    </template> -->
  </TableApi>
</div>
<div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
  <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
  <div class="modal-container bg-white  w-[70%] mx-auto rounded shadow-lg z-50 overflow-y-auto">
    <div class="modal-content py-4 text-left px-6">
      <!-- Modal Header -->
      <div class="modal-header flex items-center justify-between flex-wrap">
        <div class="flex items-center">
          <h3 class="text-xl font-semibold ml-2">Log Approval
            <span v-if="!dataLog.items.length" class="!text-red-600"> | Belum ada log approval</span>
          </h3>
        </div>
      </div>

      <!-- Modal Body -->
      <div v-if="dataLog.items.length" class="modal-body">
        <table class="w-[100%] my-3 border">
          <thead>
            <tr class="border">
              <td class="border px-2 py-1 font-medium ">Urutan</td>
              <td class="border px-2 py-1 font-medium ">Nomor Transaksi</td>
              <td class="border px-2 py-1 font-medium ">Tipe Aksi</td>
              <td class="border px-2 py-1 font-medium ">Tanggal Aksi </td>
              <td class="border px-2 py-1 font-medium ">User Aksi</td>
              <td class="border px-2 py-1 font-medium ">Catatan</td>
            </tr>
          </thead>
          <tr class="border" v-for="d,i in dataLog.items" :key="i">
            <td class="border px-2 py-1">{{ i+1 }}</td>
            <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
            <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
            <td class="border px-2 py-1">{{ d.action_at ?? '-' }}</td>
            <td class="border px-2 py-1">{{ d.action_user ?? '-' }}</td>
            <td class="border px-2 py-1">{{ d.action_note ?? '-' }}</td>
          </tr>
        </table>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer flex justify-end mt-2">
        <button @click="closeModal" class="modal-button bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-2 py-1 rounded-sm">
      Tutup
    </button>
      </div>
    </div>
  </div>
</div>
@endverbatim

@else

<!-- CONTENT -->
@verbatim
  <div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
    <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
      <div class="flex items-center">
        <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali" @click="onBack"/>
        <div>
          <h1 class="text-20px font-bold">Form Cuti</h1>
          <p class="text-gray-100">Cuti</p>
        </div>
      </div>
    </div>

    <div class="flex items-center mb-2 border-b pb-4">
      <div v-show="is_to_upload">
        <h2 v-if="!is_approval" class="mx-4 font-sans text-xl flex justify-left font-bold">
          Upload Data
        </h2>
      </div>
      <div v-show="!is_to_upload">
        <h2 v-if="!is_approval" class="mx-4 font-sans text-xl flex justify-left font-bold">
          
        </h2>
        <h2 v-else class="mx-4 font-sans text-xl flex justify-left font-bold">
          Notifikasi Approval Cuti
        </h2>
      </div>
    </div>

    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3" :value="values.nomor"
          :errorText="formErrors.nomor?'failed':''" @input="v=>values.nomor=v" :hints="formErrors.nomor"
          :check="false" label="Nomor" placeholder="Autofield Nomor" 
        />
      </div>
      <div>   
        <FieldPopup :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.m_kary_id"
          @input="(v)=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
          valueField="id" displayField="nama_lengkap" :api="apiKary" placeholder="Pilih Karyawan" label="Karyawan" :check="false" :columns="[
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
          ]"
        />
      </div>
     <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3" :value="values.tipe_cuti_id"
        @input="v=>values.tipe_cuti_id=v" :errorText="formErrors.tipe_cuti_id?'failed':''"
        :hints="formErrors.tipe_cuti_id" label="Tipe Cuti" @update:valueFull="(e) => {
  $log('isi e:',e)
  $log('value yg dipilih:', e.value)

  if (e.value?.toLowerCase() !== 'p24') {
    values.date_to = null
  }

  values.tipe_string = e.value
  values.alasan = e.value_2
  $log('values setelah update:', values.alasan)
}"
 placeholder="Tipe Cuti" valueField="id" displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
            },
            params: {
            join: true,
            where:`this.group='TIPE CUTI' AND this.is_active='true'`,
            selectfield: 'this.id, this.code, this.value, this.value_2, this.is_active'
            }
            }" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3" :value="values.alasan_id"
        @input="v=>values.alasan_id=v" :errorText="formErrors.alasan_id?'failed':''" :hints="formErrors.alasan_id"
        label="Alasan" placeholder="Pilih Alasan" valueField="id" displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
            },
            params: {
            simplest: true,
            transform: false,
            join: true,
            where: `this.is_active = true AND this.group = '${values.alasan}'`,
            selectfield: 'this.id, this.code, this.value, this.is_active'
            }
            }" :check="false" />
    </div>
      <div>
        <FieldX
          :bind="{ readonly: !actionText,disabled: !actionText }" type="date" class="w-full !mt-3" :value="values.date_from"
          label="Tanggal Awal" placeholder="Pilih Tanggal Awal" :errorText="formErrors.date_from?'failed':''"
          @input="v=>values.date_from=v" :hints="formErrors.date_from" :check="false"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: !actionText,disabled: !actionText }" type="date" class="w-full !mt-3" :value="values.date_to"
          label="Tanggal Akhir" placeholder="Pilih Tanggal Akhir" :errorText="formErrors.date_to?'failed':''"
          @input="v=>values.date_to=v" :hints="formErrors.date_to" :check="false"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: !actionText,disabled: !actionText }" type="time" class="w-full !mt-3" :value="values.time_from"
          label="Waktu Awal" placeholder="Masukan Waktu Awal" :errorText="formErrors.time_from?'failed':''"
          @input="v=>values.time_from=v" :hints="formErrors.time_from" :check="false"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: !actionText,disabled: !actionText }" type="time" class="w-full !mt-3" :value="values.time_to"
          label="Waktu Akhir" placeholder="Masukan Waktu Akhir" :errorText="formErrors.time_to?'failed':''"
          @input="v=>values.time_to=v" :hints="formErrors.time_to" :check="false"
        />
      </div>
      <div>
        <FieldUpload
          class="w-full !mt-3"
          :bind="{ readonly: !actionText,disabled: !actionText }"
            :value="values.attachment" @input="(v)=>values.attachment=v" :maxSize="10"
            :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]"
            :api="{
              url: `${store.server.url_backend}/operation/t_cuti/upload`,
              headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
              params: { field: 'attachment' },
              onsuccess: response=>response,
              onerror:(error)=>{},
              }"
              :hints="formErrors.attachment" placeholder="Upload Surat Dokter" fa-icon="upload"
              accept="application/pdf" label="Surat Dokter" :check="false"  
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: !actionText }" type='textarea' class="w-full !mt-3" :value="values.keterangan"
          :errorText="formErrors.keterangan?'failed':''" @input="v=>values.keterangan=v"
          :hints="formErrors.keterangan" :check="false" label="Keterangan" placeholder="Tuliskan keterangan"
        />
      </div>
      <div>
        <FieldNumber
          :bind="{  readonly: true }" class="w-full !mt-3"
          :value="values.interval" :errorText="formErrors.interval?'failed':''"
          @input="v=>values.interval=v" :hints="formErrors.interval" :check="false"
          label="Total Pengajuan Cuti" placeholder="Autofield Total Pengajuan Cuti"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.status" :errorText="formErrors.status?'failed':''"
          @input="v=>values.status=v" :hints="formErrors.status" :check="false"
          label="Status" placeholder="Autofield Status"
        />
      </div>

      
    <div v-show="route.query.is_approval">
      <FieldX :bind="{ readonly: false }" class="w-full !mt-3" :value="values.catatan"
        :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan"
        :check="false" label="Catatan Approval" placeholder="Tuliskan catatan Approval"
      />
    </div>

      <div>
        <FieldSelect v-show="route.query.action?.toLowerCase() === 'verifikasi'" 
          :value="values.target_id" @input="v=>values.target_id=v"
          :errorText="formErrors.target_id?'failed':''" 
          :hints="formErrors.target_id"
          valueField="id" displayField="nama_lengkap"
          :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel'
              }
          }"
          placeholder="Pilih Target Approval" label="Target Approval" fa-icon="" :check="false"
        />
        
      </div>

       <!-- <div>   
        <FieldPopup v-show="route.query.action?.toLowerCase() === 'verifikasi'" :value="values.target_id"
          @input="(v)=>values.target_id=v" :errorText="formErrors.target_id?'failed':''" :hints="formErrors.target_id"
          valueField="id" displayField="nama_lengkap" :
           :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel,divisi'
              }
          }" placeholder="Pilih Karyawan" label="Karyawan" :check="false" :columns="[
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
              field: 'm_subcomp.name',
              headerName: 'SUB',
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
              field: 'nama_divisi',
              headerName: 'Divisi',
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
          ]"
        />
      </div> -->


      <!-- END COLUMN -->
      <!-- ACTION BUTTON START -->
    </div>

    <!-- Modal Content -->
        <div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
          <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
          <div class="modal-container bg-white  w-[70%] mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content py-4 text-left px-6">
              <!-- Modal Header -->
              <div class="modal-header flex items-center justify-between flex-wrap">
                <div class="flex items-center">
                  <h3 class="text-xl font-semibold ml-2">Log Approval
                    <span v-if="!dataLog.items.length" class="!text-red-600"> | Belum ada log approval</span></h3>
                </div>
              </div>
  
              <!-- Modal Body -->
              <div v-if="dataLog.items.length" class="modal-body">
                <table class="w-[100%] my-3 border">
                  <thead>
                    <tr class="border">
                      <td class="border px-2 py-1 font-medium ">Urutan</td>
                      <td class="border px-2 py-1 font-medium ">Nomor Transaksi</td>
                      <td class="border px-2 py-1 font-medium ">Tipe Aksi</td>
                      <td class="border px-2 py-1 font-medium ">Tanggal Aksi </td>
                      <td class="border px-2 py-1 font-medium ">User Aksi</td>
                      <td class="border px-2 py-1 font-medium ">Catatan</td>
                    </tr>
                  </thead>
                  <tr class="border" v-for="d,i in dataLog.items" :key="i">
                    <td class="border px-2 py-1">{{ i+1 }}</td>
                    <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ d.action_at ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ d.action_user ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ d.action_note ?? '-' }}</td>
                  </tr>
                </table>
              </div>
              <!-- Modal Footer -->
              <div class="modal-footer flex justify-end mt-2">
                <button @click="closeModal" class="modal-button bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-2 py-1 rounded-sm">
            Tutup
          </button>
              </div>
            </div>
          </div>
        </div>
    <!-- End Modal Content -->


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
        v-show="actionText" 
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