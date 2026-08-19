<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-amber-600 text-white hover:bg-amber-400':'border border-amber-600 text-amber-600 bg-white  hover:bg-amber-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
      </div>
    </div>

    <div v-show="data.can_create">
      <RouterLink :to="$route.path + '/create?' + Date.now()" class="border border-[#428BCA] font-semibold text-[#428BCA] bg-white hover:bg-[#428BCA] hover:text-white 
        duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Tambah Baru
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
<div class="flex flex-col position: sticky border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Efektivitas Pelatihan</h1>
        <p class="text-gray-100">Master Efektivitas Pelatihan</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- <div>
      <FieldPopup class="w-full col-span-9 !mt-3" :bind="{ readonly: !actionText }"
        :value="values.t_request_pelatihan_id" @input="(v)=>values.t_request_pelatihan_id=v"
        :errorText="formErrors.t_request_pelatihan_id?'failed':''" :hints="formErrors.t_request_pelatihan_id"
        valueField="id" displayField="m_prog_pelatihan.tema_pelatihan" label="Pelatihan" placeholder="Pilih Pelatihan" @update:valueFull="v => {
          if (v && Object.keys(v).length) {
            values.m_prog_pelatihan_id = v['m_prog_pelatihan.id'] || v.m_prog_pelatihan_id || null
            values.trainer_id = v['trainer_id'] || null
          } else {
            values.m_prog_pelatihan_id = null
            values.trainer_id = null
          }
        }" :api="{
        url: `${store.server.url_backend}/operation/t_request_pelatihan`,
        headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          where: `this.status='POSTED'`,
          scopes: 'efektifitas',
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
    </div> -->
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
        url: `${store.server.url_backend}/operation/t_realisasi_pelatihan`,
        headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
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
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled: true, clearable:true }" :value="values.trainer_id"
        @input="v=>values.trainer_id=v" :errorText="formErrors.trainer_id?'failed':''" :hints="formErrors.trainer_id"
        valueField="id" displayField="nama_trainer" :api="{
              url: `${store.server.url_backend}/operation/m_trainer`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //where: `this.group = 'TIPE_PENILAIAN'`,
                simplest:true,
                transform:false,
                join:false
              }
          }" fa-icon="caret-down" label="Nama Trainer" placeholder="Pilih Trainer" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled: true, clearable:true }"
        :value="values.m_prog_pelatihan_id" @input="v=>values.m_prog_pelatihan_id=v"
        :errorText="formErrors.m_prog_pelatihan_id?'failed':''" :hints="formErrors.m_prog_pelatihan_id" valueField="id"
        displayField="tema_pelatihan" :api="{
              url: `${store.server.url_backend}/operation/m_prog_pelatihan`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //where: `this.group = 'TIPE_PENILAIAN'`,
                simplest:true,
                transform:false,
                join:false
              }
          }" fa-icon="caret-down" label="Tema Pelatihan" placeholder="Tema Terisi Otomatis" :check="false" />
    </div>

    <div>
      <FieldX type="textarea" class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.saran"
        :errorText="formErrors.saran ? 'failed' : ''" @input="v => values.saran = v" :hints="formErrors.saran"
        label="Saran" placeholder="Masukkan Saran" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" v-show="route.query.action?.toLowerCase() === 'verifikasi'"
        :value="values.target_id" @input="v=>values.target_id=v" :errorText="formErrors.target_id?'failed':''"
        :hints="formErrors.target_id" valueField="id" displayField="nama_lengkap" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel'
              }
          }" placeholder="Pilih Target Approval" label="Target Approval" fa-icon="" :check="false" />

    </div>

    <div v-show="route.query.is_approval">
      <FieldX :bind="{ readonly: false }" class="text-[11px] font-medium text-gray-700 mb-4" type="textarea"
        :value="values.catatan" :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v"
        :hints="formErrors.catatan" :check="false" label="Catatan Approval" placeholder="Tuliskan catatan Approval" />
    </div>

  </div>

  <div class="p-6">
    <div class="flex justify-between mb-4">
      <div>
        <h2 class="text-xl font-semibold">Penilaian Karyawan</h2>
      </div>
    </div>

    <ButtonMultiSelect v-show="actionText && values.t_realisasi_pelatihan_id" title="Add to list" @add="onDetailAdd" :api="{
            url: `${store.server.url_backend}/operation/m_kary`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { 
              t_realisasi_pelatihan_id : `${values.t_realisasi_pelatihan_id}`,
              kary_id: `${values.m_kary_id}`,
              scopes:'Efektifitas',
              //notin: `this.id:${detailArr.map(dt=> dt.m_kary_id)}`,
              where: `this.is_active = true`,
              selectfield: 'id, kode, nama_lengkap'
            },
            onsuccess:(response)=>{
              response.data = [...response.data].map((dt)=>{
                //Object.keys(dt).forEach(k=>dt['m_barang.'+k] = dt[k])
                return dt
              })
              response.page = response.current_page
              response.hasNext = response.has_next
              return response
            }
          }" :columns="[{
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerName: 'No',
            valueGetter:p=>'',
            width:60,
            sortable: false, resizable: true, filter: false,
            cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Kode',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'kode',
            cellClass: ['justify-start','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Nama',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'nama_lengkap',
            cellClass: ['justify-start','!border-gray-200']
          }
       ]">
      <div
        class="bg-gray-600 text-white font-semibold hover:bg-gray-500 transition-transform duration-300 transform hover:-translate-y-0.5 mb-5 rounded p-1.5 mt-3">
        <icon fa="plus" size="sm mr-0.5" /> Add to list
      </div>
    </ButtonMultiSelect>


    <div v-for="(item, index) in detailArr" :key="`assessment-${index}`"
      class="mt-1 border rounded-xl shadow-lg overflow-hidden transition-transform duration-300 transform hover:-translate-y-0.5">

      <div class="relative flex items-center justify-between bg-gray-700 px-6 py-0 text-white font-semibold text-lg ">
        <button @click="btnidx(index)" class="text-left flex-1 font-normal tracking-wide text-lg hover:bg-gray-700 px-2 py-1 rounded transition duration-200">
      {{ item.nama_lengkap }}
    </button>

        <button @click="removeDetail(item.sequence)" class="ml-2 w-6 h-6 flex items-center justify-center bg-gray-200 rounded-full shadow hover:bg-gray-300 transition duration-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
           class="w-4 h-4 stroke-2 text-gray-600 transform transition-transform duration-200 hover:scale-125">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
      </div>

      <div v-show="showForm[index]" class="p-4 space-y-5 bg-gray-50 rounded-b-2xl transition-all duration-300">

        <div v-for="(kategori, kIndex) in item.komponen" :key="kIndex" class="mb-3">

          <h3 class="text-xs md:text-sm text-gray-800 mb-1 font-medium">{{ kategori.nama_kategori }}</h3>

          <div class="flex flex-col gap-2">
            <label v-for="(komp, cIndex) in kategori.komponen" :key="cIndex"
             class="flex items-center gap-2 cursor-pointer border border-gray-200 p-2 rounded-lg hover:bg-gray-100 transition duration-200">
        <input
          type="radio"
          :name="`kategori-${index}-${kIndex}`"
          :value="komp.nilai"
          v-model.number="kategori.selectedKomponen"
          class="accent-gray-600 w-4 h-4"
        />
        <span class="text-gray-700 text-xs">{{ komp.nama_komponen }}</span>
      </label>
          </div>

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