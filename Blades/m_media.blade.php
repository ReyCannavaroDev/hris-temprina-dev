<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-4 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p class="font-semibold">Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-3 text-sm">AKTIF</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-3 text-sm">NON AKTIF</button>
      </div>
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        v-if="data?.can_create"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-3 text-sm">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr class="my-2">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="max-h-[500px]">
  </TableApi>
</div>
@else

<!-- CONTENT / FORM -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <!-- HEADER -->
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Master Media</h1>
        <p class="text-gray-100">Manajemen Aset Media & Gambar Perusahaan</p>
      </div>
    </div>
  </div>

  <!-- FORM FIELDS -->
  <div class="p-6 grid <md:grid-cols-1 grid-cols-2 gap-4">
    <!-- KODE -->
    <div>
      <label class="font-semibold text-sm">Kode Media</label>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-1" :value="values.kode"
        :errorText="formErrors.kode?'failed':''" @input="v=>values.kode=v" :hints="formErrors.kode"
        :check="false" label="" placeholder="Contoh: LOGO_UTAMA, LOGO_SBY" />
    </div>

    <!-- NAMA -->
    <div>
      <label class="font-semibold text-sm">Nama Media <span class="text-red-500">*</span></label>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-1" :value="values.nama"
        :errorText="formErrors.nama?'failed':''" @input="v=>values.nama=v" :hints="formErrors.nama"
        :check="false" label="" placeholder="Tuliskan Nama Media / Logo" />
    </div>

    <!-- KATEGORI MEDIA (DARI m_general) -->
    <div>
      <label class="font-semibold text-sm">Kategori Media <span class="text-red-500">*</span></label>
      <FieldSelect class="w-full !mt-1"
        :bind="{ disabled: !actionText, clearable: false }"
        :value="values.kategori_id"
        @input="v=>values.kategori_id=v"
        :errorText="formErrors.kategori_id?'failed':''"
        :hints="formErrors.kategori_id"
        valueField="id"
        displayField="value"
        :api="{
          url: `${store.server.url_backend}/operation/m_general`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            where: `this.group = 'KATEGORI-MEDIA' and this.is_active = true`,
            simplest: true,
            transform: false,
            join: false
          }
        }"
        placeholder="Pilih Kategori Media"
        label=""
        fa-icon=""
        :check="false"
      />
    </div>

    <!-- UNIT / PERUSAHAAN (m_comp) -->
    <div>
      <label class="font-semibold text-sm">Unit / Perusahaan (Opsional)</label>
      <FieldSelect class="w-full !mt-1"
        :bind="{ disabled: !actionText, clearable: true }"
        :value="values.m_comp_id"
        @input="v=>values.m_comp_id=v"
        :errorText="formErrors.m_comp_id?'failed':''"
        :hints="formErrors.m_comp_id"
        valueField="id"
        displayField="name"
        :api="{
          url: `${store.server.url_backend}/operation/m_comp`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            where: `this.is_active = true`,
            simplest: true,
            transform: false,
            join: false
          }
        }"
        placeholder="Semua Unit / Pilih Perusahaan"
        label=""
        fa-icon=""
        :check="false"
      />
    </div>

    <!-- UPLOAD FILE GAMBAR -->
    <div class="col-span-1 md:col-span-2">
      <label class="font-semibold text-sm">File Gambar / Aset Media <span class="text-red-500">*</span></label>
      <FieldUpload class="w-full !mt-1"
        :bind="{ readonly: !actionText }"
        :value="values.file_path"
        @input="(v)=>values.file_path=v"
        :maxSize="10"
        :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]"
        :api="{
          url: `${store.server.url_backend}/operation/m_media/upload`,
          headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
          params: { field: 'file_path' },
          onsuccess: response=>response,
          onerror:(error)=>{},
        }"
        :hints="formErrors.file_path"
        label=""
        placeholder="Klik untuk Upload Gambar (PNG, JPG, JPEG)"
        fa-icon="upload"
        accept="image/*"
        :check="false"
      />

      <!-- PREVIEW GAMBAR -->
      <div v-if="values.file_path" class="mt-3 p-3 border rounded-md bg-gray-50 flex items-center gap-4">
        <span class="text-xs text-gray-500 font-semibold">Preview :</span>
        <img :src="values.file_path.startsWith('http') ? values.file_path : `${store.server.url_backend}/${values.file_path.replace(/^\//, '')}`" 
             alt="Preview Media" 
             class="max-h-20 max-w-[200px] border rounded bg-white p-1 object-contain shadow-sm" />
      </div>
    </div>

    <!-- KETERANGAN -->
    <div>
      <label class="font-semibold text-sm">Keterangan</label>
      <FieldX class="w-full !mt-1"
        :bind="{ readonly: !actionText }"
        :value="values.keterangan"
        :errorText="formErrors.keterangan?'failed':''"
        @input="v=>values.keterangan=v"
        type="textarea"
        :hints="formErrors.keterangan"
        label=""
        placeholder="Tuliskan keterangan atau catatan aset media..."
        :check="false"
      />
    </div>

    <!-- STATUS AKTIF -->
    <div>
      <label class="font-semibold text-sm">Status</label>
      <FieldSelect class="w-full !mt-1"
        :bind="{ disabled: !actionText, clearable: false }"
        :value="values.is_active"
        @input="v=>values.is_active=v"
        :errorText="formErrors.is_active?'failed':''"
        :hints="formErrors.is_active"
        valueField="value"
        displayField="label"
        :options="[
          { value: true, label: 'Aktif' },
          { value: false, label: 'Non Aktif' }
        ]"
        placeholder="Pilih Status"
        label=""
        fa-icon=""
        :check="false"
      />
    </div>
  </div>

  <hr>

  <!-- ACTION BUTTONS -->
  <div class="flex flex-row items-center justify-end space-x-2 p-4">
    <i class="text-gray-500 text-[12px] mr-auto">Tekan CTRL + S untuk shortcut Simpan Data</i>
    <button
      class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md py-2 px-4 text-sm"
      v-show="actionText"
      @click="onReset(true)"
    >
      <icon fa="times" />
      Reset
    </button>
    <button
      class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md py-2 px-4 text-sm"
      v-show="actionText"
      @click="onSave"
    >
      <icon fa="save" />
      Simpan
    </button>
  </div>
</div>
@endverbatim
@endif