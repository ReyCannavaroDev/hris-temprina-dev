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
        v-if="currentMenu ? (currentMenu.can_create ?? true) : true"
        class="border border-[#428BCA] text-[#428BCA] font-semibold bg-white hover:bg-[#428BCA] hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1.5 px-3 text-sm flex items-center gap-1 shadow-sm">
        <Icon fa="plus" /> Create New
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
      <div @click.capture="onFieldUploadClick">
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
      </div>

      <!-- PREVIEW GAMBAR -->
      <div v-if="values.file_path" class="mt-3 p-3.5 border border-gray-200 rounded-md bg-gray-50 flex items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
          <span class="text-xs text-gray-600 font-semibold flex items-center gap-1">
            <Icon fa="image" class="text-gray-400" /> Preview :
          </span>
          <img :src="getMediaPreview(values.file_path, values.kode, values.id)" 
               @error="onImgPreviewError"
               @click="openPreviewModal($event.target.currentSrc || $event.target.src, values.nama || 'Aset Media', values.kode || '')"
               alt="Preview Media" 
               title="Klik untuk memperbesar gambar"
               class="max-h-20 max-w-[220px] border border-gray-300 rounded bg-white p-1 object-contain shadow-xs cursor-pointer hover:border-[#428BCA] hover:shadow-md transition-all duration-200" />
        </div>
        <div>
          <button type="button" 
                  @click="openPreviewModal(getMediaPreview(values.file_path, values.kode, values.id), values.nama || 'Aset Media', values.kode || '')"
                  class="border border-[#428BCA] text-[#428BCA] font-semibold bg-white hover:bg-[#428BCA] hover:text-white duration-200 rounded-md py-1.5 px-3 text-xs flex items-center gap-1.5 shadow-sm">
            <Icon fa="expand" /> Lihat Ukuran Penuh
          </button>
        </div>
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

<!-- MODAL PREVIEW IMAGE / LIGHTBOX -->
@verbatim
<div v-show="modalPreview.show" class="fixed inset-0 flex items-center justify-center z-50">
  <div class="modal-overlay fixed inset-0 bg-black opacity-50" @click="modalPreview.show = false"></div>
  <div class="modal-container bg-white w-[90%] md:w-[65%] max-w-4xl mx-auto rounded-md shadow-lg z-50 overflow-y-auto max-h-[90vh]">
    <div class="modal-content py-4 text-left px-6">
      <!-- Modal Header -->
      <div class="modal-header flex items-center justify-between border-b pb-2">
        <div class="flex items-center gap-2">
          <Icon fa="image" class="text-blue-500 mr-1" />
          <h3 class="text-lg font-semibold text-gray-800">{{ modalPreview.title || 'Preview Aset Media' }}</h3>
          <span v-if="modalPreview.code" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-300 font-mono font-semibold">
            {{ modalPreview.code }}
          </span>
        </div>
        <button @click="modalPreview.show = false" class="cursor-pointer text-gray-400 hover:text-gray-700 transition-colors p-1" title="Tutup">
          <Icon fa="times" size="lg" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body py-4 flex flex-col items-center justify-center bg-gray-50 rounded-md mt-3 p-4 border border-gray-200">
        <div class="p-3 bg-white border border-gray-200 rounded shadow-sm max-w-full flex items-center justify-center">
          <img :src="modalPreview.src" 
               @error="onImgPreviewError"
               class="max-h-[55vh] max-w-full object-contain rounded" 
               :alt="modalPreview.title" />
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer flex flex-wrap justify-between items-center mt-3 border-t pt-3">
        <div class="text-xs text-gray-500 font-mono truncate max-w-[380px]" :title="modalPreview.src">
          <Icon fa="link" class="text-gray-400 mr-1" />{{ modalPreview.src }}
        </div>
        <div class="flex items-center gap-2 ml-auto">
          <a :href="modalPreview.src" target="_blank" class="border border-[#428BCA] text-[#428BCA] bg-white hover:bg-[#428BCA] hover:text-white font-semibold px-3 py-1.5 rounded-md text-xs flex items-center gap-1.5 duration-200 shadow-sm">
            <Icon fa="external-link" /> Buka Tab Baru
          </a>
          <button @click="modalPreview.show = false" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1.5 rounded-md text-xs flex items-center gap-1.5 duration-200 shadow-sm">
            <Icon fa="times" /> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endverbatim

<style>
  /* Sembunyikan modal popup_file bawaan framework yang kosong/rusak agar dialihkan sepenuhnya ke modal kustom */
  .popup_file {
    display: none !important;
  }
</style>