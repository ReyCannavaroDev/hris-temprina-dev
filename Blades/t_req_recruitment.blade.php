<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex flex-col md:flex-row md:items-center gap-y-2 md:gap-y-0 gap-x-4">
      <p class="font-semibold">Filter Status:</p>

      <!-- Dropdown Mobile -->
      <div class="block md:hidden">
        <select
          @change="onStatusChange"
          class="border rounded-md text-sm py-1 px-2.5 w-full"
        >
          <option value="">Semua Status</option>
          <option value="1">DRAFT</option>
          <option value="2">IN APPROVAL</option>
          <option value="3">APPROVED</option>
          <option value="4">REJECTED</option>
        </select>
      </div>

      <!-- Button Desktop -->
      <div class="hidden md:flex flex-wrap gap-2">
        <button
          @click="filterShowData('DRAFT', 1)"
          :class="activeBtn === 1 
            ? 'bg-gray-700 text-white' 
            : 'border border-gray-700 text-gray-700 bg-white hover:bg-gray-700 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          DRAFT
        </button>

        <button
          @click="filterShowData('IN APPROVAL', 2)"
          :class="activeBtn === 2 
            ? 'bg-amber-600 text-white' 
            : 'border border-amber-600 text-amber-600 bg-white hover:bg-amber-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          IN APPROVAL
        </button>

        <button
          @click="filterShowData('APPROVED', 3)"
          :class="activeBtn === 3 
            ? 'bg-green-600 text-white' 
            : 'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          APPROVED
        </button>

        <button
          @click="filterShowData('REJECTED', 4)"
          :class="activeBtn === 4 
            ? 'bg-red-600 text-white' 
            : 'border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          REJECTED
        </button>
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
  <ModalX :open="modalLogOpen" @close="modalLogOpen = false" title="Riwayat Approval Permintaan Karyawan">
    <div class="p-4">
      <div v-if="dataLog.items && dataLog.items.length > 0" class="space-y-3">
        <div v-for="(log, idx) in dataLog.items" :key="idx" class="border-b pb-2 flex justify-between items-center text-sm">
          <div>
            <p class="font-semibold text-gray-800">{{ log.action_type || 'APPROVAL' }}</p>
            <p class="text-xs text-gray-500">{{ log.user?.name || log.action_user_name || 'User' }} - {{ log.action_note || 'Tidak ada catatan' }}</p>
          </div>
          <span class="text-xs text-gray-400">{{ log.action_at || log.created_at }}</span>
        </div>
      </div>
      <div v-else class="text-center py-6 text-gray-400">
        Belum ada riwayat approval.
      </div>
    </div>
  </ModalX>
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

        <!-- KARYAWAN DIGANTIKAN (HANYA MUNCUL JIKA REPLACEMENT / PENGGANTIAN) -->
        <div v-if="isReplacement">
          <label class="block text-xs font-semibold text-gray-700 mb-1">Karyawan yang Digantikan <span class="text-red-500">*</span></label>
          <div class="flex items-center space-x-1.5 !mt-1">
            <div class="relative flex-1">
              <input
                type="text"
                readonly
                :value="selectedKaryawanName"
                placeholder="Klik tombol Pilih untuk mencari..."
                class="w-full text-sm border rounded-lg px-3 py-2 bg-gray-50 text-gray-700 cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-500 truncate"
                @click="openKaryawanModal"
                :disabled="!actionText"
              />
              <button
                v-if="actionText && values.karyawan_digantikan_id"
                @click.stop="clearKaryawanDigantikan"
                type="button"
                class="absolute right-2.5 top-2.5 text-gray-400 hover:text-red-500"
                title="Hapus pilihan">
                <Icon fa="times" class="text-xs" />
              </button>
            </div>
            <button
              type="button"
              @click="openKaryawanModal"
              :disabled="!actionText"
              class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm px-3.5 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition font-medium whitespace-nowrap">
              <Icon fa="search" class="text-xs" />
              <span>Pilih</span>
            </button>
          </div>
          <p v-if="formErrors.karyawan_digantikan_id" class="text-xs text-red-500 mt-1">{{ formErrors.karyawan_digantikan_id }}</p>
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

        <!-- ALASAN KEBUTUHAN -->
        <div class="col-span-1 md:col-span-3">
          <FieldX type="textarea" label="Alasan / Justifikasi Permintaan Karyawan" :bind="{ readonly: !actionText }"
            :value="values.alasan" class="w-full !mt-1" @input="v=>values.alasan=v"
            :errorText="formErrors.alasan?'failed':''" :hints="formErrors.alasan"
            placeholder="Jelaskan alasan kebutuhan penambahan/penggantian personil ini..." :check="false" />
        </div>
      </div>
    </div>

    <!-- BANNER FEEDBACK / CATATAN APPROVAL -->
    <div v-if="route.query.is_approval || ['IN APPROVAL', 'APPROVED', 'REJECTED', 'REVISED'].includes(values.status)" class="bg-amber-50 p-5 border border-amber-200 rounded-xl">
      <div v-if="route.query.is_approval" class="max-w-2xl">
        <label class="font-bold text-amber-900 block mb-2">Catatan Feedback / Approval <span class="text-red-500">*</span></label>
        <FieldX type="textarea" :bind="{ readonly: false }" class="w-full !mt-0" :value="values.catatan_approval" :errorText="formErrors.catatan_approval?'failed':''" @input="v=>values.catatan_approval=v" :hints="formErrors.catatan_approval" placeholder="Berikan alasan jika Revisi atau Reject..." :check="false" />
      </div>
      <div v-else class="flex gap-4 items-center">
        <div class="px-3 py-1 bg-amber-200 text-amber-800 rounded text-xs font-bold uppercase">{{ values.status }}</div>
        <p class="text-sm text-amber-900"><span class="font-semibold">Status Pengajuan:</span> {{ values.status }}</p>
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

  <!-- MODAL POPUP PILIH KARYAWAN YANG DIGANTIKAN -->
  <div v-show="modalKaryawanOpen" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-overlay fixed inset-0 bg-black opacity-50" @click="modalKaryawanOpen = false"></div>
    <div class="modal-container bg-white w-11/12 md:w-3/5 max-w-4xl mx-auto rounded-xl shadow-2xl z-50 overflow-hidden flex flex-col max-h-[90vh]">
      
      <!-- Header Modal -->
      <div class="px-6 py-4 bg-gray-700 text-white flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <Icon fa="users" class="text-lg text-blue-400" />
          <h3 class="text-base md:text-lg font-bold">Pilih Karyawan yang Digantikan</h3>
        </div>
        <button @click="modalKaryawanOpen = false" class="text-gray-300 hover:text-white transition">
          <Icon fa="times" class="text-lg" />
        </button>
      </div>

      <!-- Filter & Search Bar -->
      <div class="p-4 bg-gray-50 border-b flex flex-col md:flex-row justify-between items-center gap-3">
        <div class="w-full md:w-80 relative">
          <input
            v-model="karyawanSearch"
            @keyup.enter="fetchKaryawanList(1)"
            type="text"
            placeholder="Cari kode atau nama karyawan..."
            class="w-full text-sm border border-gray-300 rounded-lg pl-9 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <Icon fa="search" class="text-xs" />
          </div>
          <button v-if="karyawanSearch" @click="karyawanSearch = ''; fetchKaryawanList(1)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
            <Icon fa="times" class="text-xs" />
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="fetchKaryawanList(1)"
            type="button"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-1 shadow-sm transition">
            <Icon fa="search" class="text-xs" />
            <span>Cari</span>
          </button>
        </div>
      </div>

      <!-- Table Data Karyawan -->
      <div class="flex-1 overflow-y-auto p-4">
        <div v-if="isLoadingKaryawan" class="flex flex-col items-center justify-center py-12 space-y-2 text-gray-500">
          <Icon fa="spinner" class="fa-spin text-3xl text-blue-600" />
          <p class="text-sm">Memuat data karyawan...</p>
        </div>

        <div v-else-if="karyawanList.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
          <Icon fa="user-slash" class="text-4xl mb-2 text-gray-300" />
          <p class="text-sm font-semibold text-gray-500">Tidak ada data karyawan ditemukan</p>
          <p class="text-xs text-gray-400">Pastikan divisi yang dipilih sudah sesuai.</p>
        </div>

        <table v-else class="w-full text-sm text-left border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b">
              <th class="py-2.5 px-4 w-12 text-center">No</th>
              <th class="py-2.5 px-4 w-48">Kode Karyawan</th>
              <th class="py-2.5 px-4">Nama Karyawan</th>
              <th class="py-2.5 px-4 w-28 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="(kary, idx) in karyawanList" :key="kary.id" class="hover:bg-blue-50/50 transition">
              <td class="py-2.5 px-4 text-center text-gray-500">
                {{ idx + 1 + (karyawanPage - 1) * karyawanPerPage }}
              </td>
              <td class="py-2.5 px-4 font-mono font-medium text-gray-800">
                {{ kary.kode || '-' }}
              </td>
              <td class="py-2.5 px-4 text-gray-800 font-medium">
                {{ kary.nama_lengkap || kary.nama_depan || '-' }}
              </td>
              <td class="py-2.5 px-4 text-center">
                <button
                  @click="selectKaryawan(kary)"
                  type="button"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-md font-semibold transition flex items-center justify-center gap-1 mx-auto shadow-sm">
                  <Icon fa="check" class="text-xs" />
                  <span>Pilih</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer & Pagination -->
      <div class="px-6 py-3 bg-gray-50 border-t flex flex-col md:flex-row justify-between items-center gap-2 text-xs text-gray-600">
        <div>
          <span>Total: <b>{{ karyawanTotal }}</b> Karyawan</span>
        </div>
        <div class="flex items-center space-x-2">
          <button
            :disabled="karyawanPage <= 1 || isLoadingKaryawan"
            @click="fetchKaryawanList(karyawanPage - 1)"
            type="button"
            class="px-2.5 py-1.5 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
            <Icon fa="chevron-left" class="text-xs" />
          </button>
          <span class="font-medium">Hal {{ karyawanPage }} / {{ karyawanLastPage }}</span>
          <button
            :disabled="karyawanPage >= karyawanLastPage || isLoadingKaryawan"
            @click="fetchKaryawanList(karyawanPage + 1)"
            type="button"
            class="px-2.5 py-1.5 border rounded bg-white hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
            <Icon fa="chevron-right" class="text-xs" />
          </button>
        </div>
        <button
          @click="modalKaryawanOpen = false"
          type="button"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs px-4 py-1.5 rounded-lg transition font-medium">
          Tutup
        </button>
      </div>

    </div>
  </div>
</div>
@endverbatim
@endif