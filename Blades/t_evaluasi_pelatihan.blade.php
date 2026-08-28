<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  
  <!-- TAB HEADER LANDING -->
  <div class="flex items-center justify-between border-b border-gray-200 px-4 text-sm">
    <div class="flex items-center gap-x-2">
      <!-- Tab 0: Belum Diisi -->
      <button 
        class="flex items-center justify-center border-b-2 p-3 duration-300 hover:text-[#428BCA] cursor-pointer font-medium"
        :class="{'border-[#428BCA] text-[#428BCA] !font-bold': activeTabLanding === 0, 'border-transparent text-gray-500': activeTabLanding !== 0}"
        @click="switchLandingTab(0)">
        <span>Belum Diisi</span>
        <span 
          v-if="pendingCount > 0"
          class="ml-2 text-xs bg-amber-500 text-white px-2 py-0.5 rounded-full font-bold">
          {{ pendingCount }}
        </span>
      </button>

      <!-- Tab 1: Sudah Diisi -->
      <button 
        class="flex items-center justify-center border-b-2 p-3 duration-300 hover:text-[#428BCA] cursor-pointer font-medium"
        :class="{'border-[#428BCA] text-[#428BCA] !font-bold': activeTabLanding === 1, 'border-transparent text-gray-500': activeTabLanding !== 1}"
        @click="switchLandingTab(1)">
        <span>Sudah Diisi</span>
      </button>
    </div>

    <!-- Right Actions: Hanya untuk HC jika diperlukan -->
    <div v-if="data.can_create && (store.user.data?.is_hc === true || store.user.data?.is_hc === 1)">
      <RouterLink :to="$route.path + '/create?' + Date.now()" 
        class="border border-[#428BCA] font-semibold text-[#428BCA] bg-white hover:bg-[#428BCA] hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 text-sm">
        Tambah Baru (HC)
      </RouterLink>
    </div>
  </div>

  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="" />

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
<div class="flex flex-col position: sticky border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Evaluasi Pelatihan</h1>
        <p class="text-gray-100">Master Evaluasi Pelatihan</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <div>
      <FieldPopup class="w-full col-span-9 !mt-3" :bind="{ readonly: !actionText }"
        :value="values.t_realisasi_pelatihan_id" @input="(v)=>values.t_realisasi_pelatihan_id=v"
        :errorText="formErrors.t_realisasi_pelatihan_id?'failed':''" :hints="formErrors.t_realisasi_pelatihan_id"
        valueField="id" displayField="m_prog_pelatihan.tema_pelatihan" label="Pelatihan" placeholder="Pilih Pelatihan"
        @update:valueFull="v => {
          if (v && Object.keys(v).length) {
            values.m_prog_pelatihan_id = v['m_prog_pelatihan.id'] || v.m_prog_pelatihan_id || null
            values.trainer_id = v['trainer_id'] || null
          } else {
            values.m_prog_pelatihan_id = null
            values.trainer_id = null
          }
        }" :api="{
           url:  `${store.server.url_backend}/operation/t_realisasi_pelatihan`,
              headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                scopes: 'owndata',
                transform: true,
                join: true
              }
        }" placeholder="" fa-icon="" :check="false" :columns="[{
          headerName: 'No',
          valueGetter:(p)=>p.node.rowIndex + 1,
          width: 60,
          sortable: false, resizable: false, filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
         {
              flex: 1,
              field: 'kode',
              headerName: 'Kode',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_prog_pelatihan.tema_pelatihan',
              headerName: 'Tema',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'desc',
              headerName: 'Keterangan',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
           ]" />
    </div>

    <div>
      <FieldX type="date" class="w-full !mt-3" :bind="{ readonly: true }" :value="values.tanggal"
        :errorText="formErrors.tanggal ? 'failed' : ''" @input="v => values.tanggal = v" :hints="formErrors.tanggal"
        label="Periode" placeholder="Pilih Periode" :check="true" />
    </div>

    <div>
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled: actionText !== 'Tambah', clearable:true }"
        :value="values.trainer_id" @input="v=>values.trainer_id=v" :errorText="formErrors.trainer_id?'failed':''"
        :hints="formErrors.trainer_id" valueField="id" displayField="nama_trainer" :api="{
              url: `${store.server.url_backend}/operation/m_trainer`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //where: `this.group = 'TIPE_PENILAIAN'`,
                simplest:true,
                transform:false,
                join:false
              }
          }" fa-icon="caret-down" label="Trainer" placeholder="Pilih Trainer" :check="false" />
    </div>

    <div v-show="route.query.is_approval">
      <FieldX :bind="{ readonly: false }" class="text-[11px] font-medium text-gray-700 mb-4" type="textarea"
        :value="values.catatan" :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v"
        :hints="formErrors.catatan" :check="false" label="Catatan Approval" placeholder="Tuliskan catatan Approval" />
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
  </div>

  <div class="p-6">
    <div class="flex justify-between mb-4">
      <div>
        <h2 class="text-xl font-semibold">Penilaian</h2>
      </div>
    </div>


    <!-- Main Table -->
    <div v-for="(item, index) in detailArr" :key="`assessment-${index}`" class="mb-6 border shadow-lg overflow-hidden">
      <div class="bg-gray-600 px-4 py-3">
        <h2 class="text-white font-semibold text-lg">{{ item.nama_kategori }}</h2>
      </div>

      <div class="p-4 space-y-4">
        <div v-for="(komp, kIndex) in item.komponen" :key="kIndex"
          class="border border-gray-200 rounded-xl p-3 hover:bg-gray-50 transition">
          <div class="font-medium text-gray-800 mb-2">
            {{ komp.nama_komponen }}
          </div>

          <div class="flex flex-wrap gap-4 text-sm text-gray-700">
            <label class="flex items-center gap-1 cursor-pointer">
          <input
            type="radio"
            :name="`komp-${index}-${kIndex}`"
            :value="1"
            v-model="komp.nilai"
            class="text-gray-600 focus:ring-gray-500"
          />
          <span>Kurang</span>
        </label>
            <label class="flex items-center gap-1 cursor-pointer">
          <input
            type="radio"
            :name="`komp-${index}-${kIndex}`"
            :value="2"
            v-model="komp.nilai"
            class="text-gray-600 focus:ring-gray-500"
          />
          <span>Cukup</span>
        </label>
            <label class="flex items-center gap-1 cursor-pointer">
          <input
            type="radio"
            :name="`komp-${index}-${kIndex}`"
            :value="3"
            v-model="komp.nilai"
            class="text-gray-600 focus:ring-gray-500"
          />
          <span>Baik</span>
        </label>
            <label class="flex items-center gap-1 cursor-pointer">
          <input
            type="radio"
            :name="`komp-${index}-${kIndex}`"
            :value="4"
            v-model="komp.nilai"
            class="text-gray-600 focus:ring-gray-500"
          />
          <span>Baik Sekali</span>
        </label>
          </div>
        </div>
      </div>
    </div>



    <div v-if="values.m_assessment_kary_id" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-6.8">Catatan Manager / OM</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_1"
          @input="v => values.catatan_1 = v" :errorText="formErrors.catatan_1 ? 'failed' : ''"
          :hints="formErrors.catatan_1" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-6.8">Catatan Departemen HRD</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_2"
          @input="v => values.catatan_2 = v" :errorText="formErrors.catatan_2 ? 'failed' : ''"
          :hints="formErrors.catatan_2" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-4">Komentar atasan atas kelebihan & kekurangan karyawan
        </div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_3"
          @input="v => values.catatan_3 = v" :errorText="formErrors.catatan_3 ? 'failed' : ''"
          :hints="formErrors.catatan_3" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-4">Pelatihan & pengembangan yang diusulkan untuk
          meningkatkan prestasi kerja</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_4"
          @input="v => values.catatan_4 = v" :errorText="formErrors.catatan_4 ? 'failed' : ''"
          :hints="formErrors.catatan_4" :check="false" />
      </div>
    </div>


  </div>

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
        v-show="actionText && route.query.action?.toLowerCase() !== 'verifikasi' && (['Tambah','Create','Copy'].includes(actionText) ? data.can_create : data.can_update)"
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