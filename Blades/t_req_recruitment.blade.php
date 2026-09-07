<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
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

    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="" />

  <!-- Modal Log Approval -->
  <div v-show="modalLogOpen" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-[70%] mx-auto rounded shadow-lg z-50 overflow-y-auto">
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
                <td class="border px-2 py-1 font-medium ">Tanggal Aksi</td>
                <td class="border px-2 py-1 font-medium ">User Aksi</td>
                <td class="border px-2 py-1 font-medium ">Catatan</td>
              </tr>
            </thead>
            <tr class="border" v-for="d,i in dataLog.items" :key="i">
              <td class="border px-2 py-1">{{ i+1 }}</td>
              <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
              <td class="border px-2 py-1">{{
                Array.isArray(d.target_approval)
                ? d.target_approval[0] ?? '-'
                : d.target_approval ?? '-'
                }}</td>
              <td class="border px-2 py-1">{{ d.action_at ? d.action_at.replace(/-/g, '/') : '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_user ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_note ?? '-' }}</td>
            </tr>
          </table>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer flex justify-end mt-2">
          <button @click="modalLogOpen = false" class="modal-buttona bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-2 py-1 rounded-sm">
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
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Permintaan Karyawan (FPTK)</h1>
        <p class="text-gray-100">Pengajuan Permintaan Tenaga Kerja Baru / Pengganti</p>
      </div>
    </div>
  </div>

  <div class="p-5 flex flex-col gap-6">
    <!-- SECTION 1: INFORMASI PENGAJUAN & PEMOHON -->
    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
      <h2 class="text-base font-bold text-gray-700 mb-3 border-b pb-2">Informasi Pengajuan & Pemohon</h2>
      <div class="grid <md:grid-cols-1 grid-cols-3 gap-4">
        <div>
          <FieldX label="Nomor Pengajuan" :bind="{ readonly: true }" :value="values.nomor" class="w-full !mt-1"
            @input="v=>values.nomor=v" :hints="formErrors.nomor" placeholder="Auto Generate By System" :check="false" />
        </div>

        <div>
          <FieldX type="date" label="Tanggal Pengajuan" :bind="{ readonly: !actionText }" :value="values.tanggal"
            class="w-full !mt-1" @input="v=>values.tanggal=v" :errorText="formErrors.tanggal?'failed':''"
            :hints="formErrors.tanggal" placeholder="Pilih Tanggal" :check="false" />
        </div>

        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.m_kary_id" class="w-full !mt-1"
            @input="v=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
            valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Karyawan Pemohon" label="Karyawan Pemohon" fa-icon="" :check="false" />
        </div>
      </div>
    </div>

    <!-- SECTION 2: STRUKTUR ORGANISASI & PENEMPATAN -->
    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
      <h2 class="text-base font-bold text-gray-700 mb-3 border-b pb-2">Penempatan & Jabatan yang Diminta</h2>
      <div class="grid <md:grid-cols-1 grid-cols-3 gap-4">
        <!-- SBU -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.m_comp_id"
            @input="v=>values.m_comp_id=v" :errorText="formErrors.m_comp_id?'failed':''" :hints="formErrors.m_comp_id"
            valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_comp`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih SBU" label="SBU / Perusahaan" fa-icon="sort-desc" :check="false" />
        </div>

        <!-- SUB -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.m_subcomp_id"
            @input="v=>values.m_subcomp_id=v" :errorText="formErrors.m_subcomp_id?'failed':''"
            :hints="formErrors.m_subcomp_id" valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_subcomp`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih SUB" label="Sub Company" fa-icon="sort-desc" :check="false" />
        </div>

        <!-- BRANCH -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.m_branch_id"
            @input="v=>{
              values.m_branch_id = v;
              values.m_divisi_id = null;
            }" :errorText="formErrors.m_branch_id?'failed':''"
            :hints="formErrors.m_branch_id" valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Cabang" label="Cabang / Branch" fa-icon="sort-desc" :check="false" />
        </div>

        <!-- DIVISI -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText || !values.m_branch_id, clearable:true }" class="w-full !mt-1" :value="values.m_divisi_id"
            @input="v=>{
              values.m_divisi_id = v;
              values.karyawan_digantikan_id = null;
              selectedKaryawanName = '';
            }" :errorText="formErrors.m_divisi_id?'failed':''"
            :hints="formErrors.m_divisi_id" valueField="id" displayField="name.value" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  scopes:'Name',
                  simplest:true,
                  groupBy: 'name.value',
                  where: `this.is_active = 'true'` + (values.m_branch_id ? ` AND this.m_branch_id = '${values.m_branch_id}'` : '')
                }
            }" placeholder="Pilih Divisi" label="Divisi" fa-icon="sort-desc" :check="false" />
        </div>

        <!-- POSISI -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.m_posisi_id"
            @input="v=>values.m_posisi_id=v" :errorText="formErrors.m_posisi_id?'failed':''"
            :hints="formErrors.m_posisi_id" valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_posisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Posisi / Jabatan" label="Posisi / Jabatan" fa-icon="sort-desc" :check="false" />
        </div>
      </div>
    </div>

    <!-- SECTION 3: DETAIL KEBUTUHAN PERSONIL -->
    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
      <h2 class="text-base font-bold text-gray-700 mb-3 border-b pb-2">Detail Kebutuhan & Kriteria</h2>
      <div class="grid <md:grid-cols-1 grid-cols-3 gap-4">
        <!-- JUMLAH KEBUTUHAN -->
        <div>
          <FieldNumber label="Jumlah Kebutuhan (Orang)" :bind="{ readonly: !actionText }" :value="values.jumlah_kebutuhan"
            class="w-full !mt-1" @input="v=>values.jumlah_kebutuhan=v" :errorText="formErrors.jumlah_kebutuhan?'failed':''"
            :hints="formErrors.jumlah_kebutuhan" placeholder="Jumlah Orang" :check="false" />
        </div>

        <!-- STATUS KARYAWAN -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.status_kary_id"
            @input="v=>values.status_kary_id=v" :errorText="formErrors.status_kary_id?'failed':''"
            :hints="formErrors.status_kary_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  where: `this.group = 'STATUS KARYAWAN'`,
                  simplest: true,
                  transform: false,
                  join: false
                }
            }" placeholder="Pilih Status Hubungan Kerja" label="Status Karyawan" fa-icon="" :check="false" />
        </div>

        <!-- JENIS PERMINTAAN -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.jenis_permintaan_id"
            @input="v=>values.jenis_permintaan_id=v" :errorText="formErrors.jenis_permintaan_id?'failed':''"
            :hints="formErrors.jenis_permintaan_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  where: `this.group = 'JENIS PERMINTAAN KARYAWAN'`,
                  simplest: true,
                  transform: false,
                  join: false
                }
            }" placeholder="Pilih Jenis Permintaan" label="Jenis Permintaan" fa-icon="" :check="false" />
        </div>

        <!-- TANGGAL DIBUTUHKAN -->
        <div>
          <FieldX type="date" label="Tanggal Dibutuhkan (Target Masuk)" :bind="{ readonly: !actionText }" :value="values.tgl_dibutuhkan"
            class="w-full !mt-1" @input="v=>values.tgl_dibutuhkan=v" :errorText="formErrors.tgl_dibutuhkan?'failed':''"
            :hints="formErrors.tgl_dibutuhkan" placeholder="Pilih Tanggal" :check="false" />
        </div>

        <!-- PRIORITAS -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" class="w-full !mt-1" :value="values.prioritas_id"
            @input="v=>values.prioritas_id=v" :errorText="formErrors.prioritas_id?'failed':''"
            :hints="formErrors.prioritas_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  where: `this.group = 'PRIORITAS'`,
                  simplest: true,
                  transform: false,
                  join: false
                }
            }" placeholder="Pilih Prioritas" label="Prioritas Kebutuhan" fa-icon="" :check="false" />
        </div>

        <!-- TARGET HC APPROVAL -->
        <div v-if="route.query.action?.toLowerCase() === 'verifikasi' || route.query.action?.toLowerCase() === 'edit'">
          <FieldSelect :bind="{ disabled: false, clearable:true }" class="w-full !mt-1" :value="values.target_approval_id"
            @input="v=>values.target_approval_id=v" :errorText="formErrors.target_approval_id?'failed':''"
            :hints="formErrors.target_approval_id" valueField="m_kary_id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/t_req_recruitment/get_hc`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest: true,
                  transform: false,
                  join: false
                }
            }" placeholder="Pilih Target HC Approval" label="Target HC Approval" fa-icon="" :check="false" />
        </div>

        <!-- ALASAN KEBUTUHAN -->
        <div class="col-span-1 md:col-span-3">
          <FieldX type="textarea" label="Alasan / Justifikasi Permintaan Karyawan" :bind="{ readonly: !actionText }"
            :value="values.alasan" class="w-full !mt-1" @input="v=>values.alasan=v"
            :errorText="formErrors.alasan?'failed':''" :hints="formErrors.alasan"
            placeholder="Jelaskan alasan kebutuhan penambahan/penggantian personil ini..." :check="false" />
        </div>
      </div>

      <!-- KARYAWAN YANG DIGANTIKAN (HANYA MUNCUL JIKA REPLACEMENT / PENGGANTIAN) -->
      <div v-if="isReplacement" class="mt-4 pt-4 border-t border-gray-200">
        <div class="flex justify-between items-center mb-2">
          <div>
            <h3 class="text-sm font-bold text-gray-700">Daftar Karyawan yang Digantikan <span class="text-red-500">*</span></h3>
            <p class="text-xs text-gray-500">Pilih personil yang akan digantikan pada divisi terpilih (bisa bulk select jika mengganti lebih dari 1 orang).</p>
          </div>
          <div v-if="actionText">
            <ButtonMultiSelect title="Pilih Karyawan" @add="onKaryawanAdd" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
                params: { 
                  where: `this.is_active = 'true'` + (values.m_divisi_id ? ` AND this.m_divisi_id = '${values.m_divisi_id}'` : ''),
                  notin: detailKaryawanDigantikan.length > 0 ? `this.id:${detailKaryawanDigantikan.map(dt => dt.id).join(',')}` : null,
                  searchfield: 'this.kode,this.nama_lengkap'
                },
                onsuccess:(response) => {
                  response.page = response.current_page;
                  response.hasNext = response.has_next;
                  return response;
                }
              }" :columns="[
                {
                  checkboxSelection: true,
                  headerCheckboxSelection: true,
                  headerName: 'No',
                  valueGetter:(params)=>{
                    return ''
                  },
                  width:60,
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
                },
                {
                  flex: 1,
                  headerName:'Kode Karyawan',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  field: 'kode',
                  cellClass: ['justify-start','!border-gray-200']
                },
                {
                  flex: 2,
                  headerName:'Nama Karyawan',
                  field: 'nama_lengkap',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['justify-start','!border-gray-200']
                }
              ]">
              <div
                class="bg-[#005FBF] hover:bg-[#0055ab] text-white text-[12px] px-3 py-2 text-sm flex items-center justify-center space-x-1 rounded cursor-pointer transition shadow-sm">
                <icon fa="plus" size="sm mr-0.5" />
                <span>Pilih Karyawan</span>
              </div>
            </ButtonMultiSelect>
          </div>
        </div>

        <!-- Table List Karyawan Digantikan -->
        <div class="mt-3 overflow-x-auto">
          <table class="w-full table-auto border border-[#CACACA]">
            <thead>
              <tr class="border">
                <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 py-2 text-center w-[50px] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
                <td class="text-[#8F8F8F] font-semibold text-[13px] px-3 py-2 text-left w-[200px] border bg-[#f8f8f8] border-[#CACACA]">Kode Karyawan</td>
                <td class="text-[#8F8F8F] font-semibold text-[13px] px-3 py-2 text-left border bg-[#f8f8f8] border-[#CACACA]">Nama Karyawan</td>
                <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 py-2 text-center w-[70px] border bg-[#f8f8f8] border-[#CACACA]" v-if="actionText">Aksi</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailKaryawanDigantikan" :key="item.id" class="border-t hover:bg-gray-50">
                <td class="p-2 text-center border border-[#CACACA] text-xs text-gray-600">{{ i + 1 }}.</td>
                <td class="p-2 border border-[#CACACA] font-mono text-xs text-gray-800">{{ item.kode || '-' }}</td>
                <td class="p-2 border border-[#CACACA] font-medium text-xs text-gray-800">{{ item.nama_lengkap || item.nama || '-' }}</td>
                <td class="p-2 text-center border border-[#CACACA]" v-if="actionText">
                  <button type="button" @click="removeKaryawanDigantikan(i)" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="inline">
                      <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="detailKaryawanDigantikan.length === 0">
                <td :colspan="actionText ? 4 : 3" class="p-4 text-center text-xs text-gray-400 italic bg-white border border-[#CACACA]">
                  Belum ada karyawan yang dipilih. Klik tombol <b class="text-[#005FBF]">Pilih Karyawan</b> di atas untuk menambahkan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- BANNER FEEDBACK / CATATAN APPROVAL -->
    <div v-if="route.query.is_approval || ['IN APPROVAL', 'APPROVED', 'REJECTED', 'REVISED'].includes(values.status)" 
         :class="{
            'bg-green-50 border-green-200': ['POSTED', 'APPROVED'].includes(values.status),
            'bg-blue-50 border-blue-200': values.status === 'IN APPROVAL',
            'bg-amber-50 border-amber-200': values.status === 'REVISED',
            'bg-red-50 border-red-200': values.status === 'REJECTED',
            'bg-gray-50 border-gray-200': !['POSTED', 'APPROVED', 'IN APPROVAL', 'REVISED', 'REJECTED'].includes(values.status)
         }"
         class="p-5 border rounded-xl">
      <div v-if="route.query.is_approval" class="max-w-2xl">
        <label class="font-bold text-gray-800 block mb-2">Catatan Feedback / Approval <span class="text-sm font-normal text-gray-500">(Wajib jika Revisi / Reject)</span></label>
        <FieldX type="textarea" :bind="{ readonly: false }" class="w-full !mt-0" :value="values.catatan_approval" :errorText="formErrors.catatan_approval?'failed':''" @input="v=>values.catatan_approval=v" :hints="formErrors.catatan_approval" placeholder="Berikan alasan jika Revisi atau Reject..." :check="false" />
      </div>
      <div v-else class="flex flex-col gap-3">
        <div class="flex gap-4 items-center">
          <div :class="{
                'bg-green-200 text-green-800': ['POSTED', 'APPROVED'].includes(values.status),
                'bg-blue-200 text-blue-800': values.status === 'IN APPROVAL',
                'bg-amber-200 text-amber-800': values.status === 'REVISED',
                'bg-red-200 text-red-800': values.status === 'REJECTED',
                'bg-gray-200 text-gray-800': !['POSTED', 'APPROVED', 'IN APPROVAL', 'REVISED', 'REJECTED'].includes(values.status)
               }"
               class="px-3 py-1 rounded text-xs font-bold uppercase">{{ values.status }}</div>
          <p :class="{
                'text-green-900': ['POSTED', 'APPROVED'].includes(values.status),
                'text-blue-900': values.status === 'IN APPROVAL',
                'text-amber-900': values.status === 'REVISED',
                'text-red-900': values.status === 'REJECTED',
                'text-gray-900': !['POSTED', 'APPROVED', 'IN APPROVAL', 'REVISED', 'REJECTED'].includes(values.status)
             }"
             class="text-sm"><span class="font-semibold">Status Pengajuan:</span> {{ values.status }}</p>
        </div>
        <div v-if="['REVISED', 'REJECTED'].includes(values.status)" class="w-full">
          <label :class="{
                'text-amber-900': values.status === 'REVISED',
                'text-red-900': values.status === 'REJECTED'
             }"
             class="font-bold block mb-1">Catatan dari HC:</label>
          <div :class="{
                'border-amber-200 text-amber-900': values.status === 'REVISED',
                'border-red-200 text-red-900': values.status === 'REJECTED'
             }"
             class="p-3 bg-white border rounded text-sm whitespace-pre-wrap">
            {{ values.catatan_hc || '-' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>
  <div class="flex flex-row items-center justify-end space-x-2 p-2">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>

    <!-- Approval Process Buttons -->
    <button v-show="route.query.is_approval" class="mx-1 bg-green-500 text-white hover:bg-green-600 rounded-lg py-[10px] px-[28px]" @click="onProcess('APPROVED')">
      Approve
    </button>
    <button v-show="route.query.is_approval" class="mx-1 bg-rose-500 text-white hover:bg-rose-600 rounded-lg py-[10px] px-[28px]" @click="onProcess('REJECTED')">
      Reject
    </button>
    <button v-show="route.query.is_approval" class="mx-1 bg-amber-500 text-white hover:bg-amber-600 rounded-lg py-[10px] px-[28px]" @click="onProcess('REVISED')">
      Revise
    </button>

    <!-- Normal Form Buttons -->
    <button v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)"
      class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
      @click="onReset">
      <icon fa="times" />
      Reset
    </button>

    <button v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)"
      class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
      @click="onSave">
      <icon fa="save" />
      Simpan
    </button>

    <button v-show="route.query.action?.toLowerCase() === 'verifikasi'"
      class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
      @click="onSendApproval">
      <icon fa="location-arrow" />
      Send Approval
    </button>
  </div>
</div>
@endverbatim
@endif