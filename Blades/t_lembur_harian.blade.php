<!-- LANDING -->
<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="gap-x-2 flex">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('IN APPROVAL',3)" :class="activeBtn === 3?'bg-blue-600 text-white hover:bg-blue-400':'border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">IN APPROVAL</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('REVISED',4)" :class="activeBtn === 4?'bg-amber-600 text-white hover:bg-amber-400':'border border-amber-600 text-amber-600 bg-white  hover:bg-amber-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">REVISED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('APPROVED',6)" :class="activeBtn === 6?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">APPROVED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('REJECTED',7)" :class="activeBtn === 7?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">REJECTED</button>
      </div>
    </div>
    <div v-if="data?.can_create">
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
                <td class="border px-2 py-1 font-medium ">Target</td>
                <td class="border px-2 py-1 font-medium ">Tanggal Aksi </td>
                <td class="border px-2 py-1 font-medium ">User Aksi</td>
                <td class="border px-2 py-1 font-medium ">Catatan</td>
              </tr>
            </thead>
            <tr class="border" v-for="d,i in dataLog.items" :key="i">
              <td class="border px-2 py-1">{{ i+1 }}</td>
              <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
              <td class="border px-2 py-1">
                {{
                Array.isArray(d.target_approval)
                ? d.target_approval[0] ?? '-'
                : d.target_approval ?? '-'
                }}
              </td>
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
</div>
@endverbatim
@else

<!-- CONTENT -->
@verbatim
<div class="flex  flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="rounded-t-md flex items-center mb-2 border-b pb-4 bg-gray-500 p-5">
    <Icon fa="arrow-left" class="cursor-pointer mr-2 text-white font-bold hover:text-yellow-500" title="Kembali"
      @click="onBack" />
    <div v-show="is_to_upload">
      <h2 v-if="!is_approval" class="mx-4 font-sans text-xl flex justify-left font-bold">
        Upload Data
      </h2>
    </div>
    <div v-show="!is_to_upload">
      <h2 v-if="!is_approval" class=" text-white mx-4 font-sans text-xl flex justify-left font-bold">
        {{actionText==='Edit'?'Ubah':actionText}} Lembur
      </h2>
      <h2 v-else class="mx-4 font-sans text-xl flex justify-left font-bold">
        Notifikasi Approval Lembur
      </h2>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" type="text" :value="values.nomor" class="w-full mt-3"
        @input="v=>values.nomor=v" :check="false" placeholder="Masukan Nomor" label="Nomor" />
    </div>
    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.m_kary_id"
        @input="(v)=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
        valueField="id" displayField="nama_lengkap" :api="apiKary" placeholder="Pilih Karyawan" label="Karyawan"
        :check="false" :columns="[
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
          ]" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.tanggal"
        label="Tanggal Lembur" placeholder="Pilih Tanggal Lembur" :errorText="formErrors.tanggal?'failed':''"
        @input="v=>values.tanggal=v" :hints="formErrors.tanggal" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.tanggal_selesai"
        label="Tanggal Selesai" placeholder="Pilih Tanggal Selesai" :errorText="formErrors.tanggal_selesai?'failed':''"
        @input="v=>values.tanggal_selesai=v" :hints="formErrors.tanggal_selesai" :check="false" />
    </div>
    <div>
      <FieldX placeholdedr="Masukan Jam Mulai" label="Jam Mulai" :bind="{ readonly: !actionText   }" class="w-full mt-3"
        type="time" fa-icon="clock" :value="values.jam_mulai" :errorText="formErrors.jam_mulai ? 'failed' : ''"
        @input="v => values.jam_mulai =v" :hints="formErrors.jam_mulai" :check="false" />
    </div>

    <div>
      <FieldX label="Jam Selesai" placeholder="Masukan Jam Selesai" :bind="{ readonly: !actionText }"
        class="w-full mt-3" type="time" fa-icon="clock" :value="values.jam_selesai"
        :errorText="formErrors.jam_selesai ? 'failed' : ''" @input="v => values.jam_selesai =v"
        :hints="formErrors.jam_selesai" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.tipe_lembur_id"
        @input="v=>values.tipe_lembur_id=v" :errorText="formErrors.tipe_lembur_id?'failed':''"
        :hints="formErrors.tipe_lembur_id" label="Tipe Lembur" placeholder="Masukan Tipe Lembur" valueField="id"
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
            where:`this.group='TIPE LEMBUR' AND this.is_active='true'`,
            selectfield: 'this.id, this.code, this.value, this.is_active'
            }
            }" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.alasan_id"
        @input="v=>values.alasan_id=v" :errorText="formErrors.alasan_id?'failed':''" :hints="formErrors.alasan_id"
        label="Alasan Lembur" placeholder="Masukan Alasan Lembur" valueField="id" displayField="value" :api="{
              url: `${store.server.url_backend}/operation/m_general`,
              headers: {
              'Content-Type': 'Application/json',
              Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
              simplest: true,
              transform: false,
              join: true,
              where:`this.group='ALASAN LEMBUR' AND this.is_active='true'`,
              selectfield: 'this.id, this.code, this.value, this.is_active'
              }
              }" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.no_doc"
        :errorText="formErrors.no_doc?'failed':''" @input="v=>values.no_doc=v" :hints="formErrors.no_doc" :check="false"
        label="Nomor Dokumen Lembur" placeholder="Masukan Nomor Dokumen bila ada" />
    </div>

    <div>
      <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText }" :value="values.doc" @input="(v)=>values.doc=v"
        :maxSize="10" :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/t_lembur/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'doc' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.doc" label="File Dokumen" placeholder="Upload Dokumen bila ada" fa-icon="upload"
        accept="application/pdf" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type='textarea' class="w-full mt-3" :value="values.keterangan"
        :errorText="formErrors.keterangan?'failed':''" @input="v=>values.keterangan=v" :hints="formErrors.keterangan"
        :check="false" label="Keterangan" placeholder="Tuliskan keterangan" />
    </div>

    <div>
      <FieldX placeholder="Masukan Status" label="Status" :bind="{ readonly: true }" type="text" :value="values.status"
        class="w-full mt-3" @input="v=>values.status=v" :check="false" />
    </div>

     <div>
    <FieldSelect class="w-full mt-3" v-show="route.query.action?.toLowerCase() === 'verifikasi'"
      :value="values.target_id" @input="v=>values.target_id=v" :errorText="formErrors.target_id?'failed':''"
      :hints="formErrors.target_id" valueField="id" displayField="nama_lengkap" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                t_m_kary_id: values.m_kary_id,
                scopes: 'higherlevel'
              }
          }" placeholder="Pilih Target Approval" label="Target Approval" fa-icon="" :check="false" />

  </div>

    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>
  <hr>

  <div v-show="route.query.is_approval" class="w-1/2 mt-6">
    <FieldX :bind="{ readonly: false }" class="w-full !mt-3" :value="values.catatan"
      :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan"
      :check="false" label="Catatan Approval" placeholder="Tuliskan catatan Approval" />
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