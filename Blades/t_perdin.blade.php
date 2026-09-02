<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Aktif</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inaktif</button>
      </div>
    </div>
    <div>
      <RouterLink v-if="data?.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <!-- <template #header>
    </template> -->
  </TableApi>
</div>
@else

<!-- CONTENT -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Perdin</h1>
        <p class="text-gray-100">Perjalanan Dinas</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
       <div>
        <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.nomor"
          :errorText="formErrors.nomor?'failed':''" @input="v=>values.nomor=v"
          :hints="formErrors.nomor" placeholder="Auto Generate By System" label="Nomor" fa-icon="" :check="false" />
      </div>

      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.m_kary_id"
        @input="v=>{
          values.m_kary_id = v;
          values.m_posisi_id = null;
          values.m_atasan_id = null;
        }" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
        valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Karyawan" label="Karyawan" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText || !values.m_kary_id, clearable:true }" :value="values.m_posisi_id"
        @input="v=>{
          values.m_posisi_id = v;
          values.m_atasan_id = null;
        }" :errorText="formErrors.m_posisi_id?'failed':''" :hints="formErrors.m_posisi_id"
        valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_posisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  scopes: 'getbykary',
                  m_kary_id: values.m_kary_id ? values.m_kary_id : null,
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" :placeholder="!values.m_kary_id ? 'Pilih Karyawan Terlebih Dahulu' : 'Pilih Jabatan'" label="Jabatan" fa-icon="" :check="false" />
    </div>

     <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText || !values.m_kary_id || !values.m_posisi_id, clearable:true }" :value="values.m_atasan_id"
        @input="v=>values.m_atasan_id=v" :errorText="formErrors.m_atasan_id?'failed':''" :hints="formErrors.m_atasan_id"
        valueField="id" displayField="nama_lengkap" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel',
                t_m_kary_id: values.m_kary_id ? values.m_kary_id : null,
                m_posisi_id: values.m_posisi_id ? values.m_posisi_id : null,
                simplest:true,
              }
          }" :placeholder="!values.m_posisi_id ? 'Pilih Jabatan Terlebih Dahulu' : 'Pilih Atasan'" label="Atasan" fa-icon="" :check="false" />

    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" type="date" :value="values.date_from"
        :errorText="formErrors.date_from?'failed':''" @input="v=>{
          values.date_from = v;
          values.tanggal_surat_tugas = v;
          values.tanggal_rencana_biaya = v;
        }" :hints="formErrors.date_from"
        placeholder="Masukkan Tanggal Surat Tugas" label="Tanggal Surat Tugas" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" type="date" :value="values.date_to"
        :errorText="formErrors.date_to?'failed':''" @input="v=>values.date_to=v" :hints="formErrors.date_to"
        placeholder="Masukkan Tanggal Akhir" label="Tanggal Selesai" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" type="date" :value="values.tanggal_surat_tugas"
        :errorText="formErrors.tanggal_surat_tugas?'failed':''" @input="v=>values.tanggal_surat_tugas=v" :hints="formErrors.tanggal_surat_tugas"
        placeholder="Auto Generate By System" label="Tgl Surat Tugas (Auto)" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" type="date" :value="values.tanggal_rencana_biaya"
        :errorText="formErrors.tanggal_rencana_biaya?'failed':''" @input="v=>values.tanggal_rencana_biaya=v" :hints="formErrors.tanggal_rencana_biaya"
        placeholder="Auto Generate By System" label="Tgl Rencana Biaya (Auto)" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.tugas"
        :errorText="formErrors.tugas?'failed':''" @input="v=>values.tugas=v" :hints="formErrors.tugas"
        placeholder="Tuliskan Detail Perjalanan Dinas" label="Perjalanan Dinas" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.tempat_tujuan"
        :errorText="formErrors.tempat_tujuan?'failed':''" @input="v=>values.tempat_tujuan=v"
        :hints="formErrors.tempat_tujuan" placeholder="Tuliskan Tujuan" label="Tujuan" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.provinsi_id"
        @input="v=>values.provinsi_id=v" :errorText="formErrors.provinsi_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kota_id = '',
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.provinsi_id" label="Provinsi" placeholder="Pilih Provinsi" valueField="id"
        displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genProvinsi',
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.kota_id"
        @input="v=>values.kota_id=v" :errorText="formErrors.kota_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.kota_id" label="Kota" placeholder="Pilih Kota" valueField="id"
        displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKota',
                      provinsi_id: values.provinsi_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.kecamatan_id"
        @input="v=>values.kecamatan_id=v" :errorText="formErrors.kecamatan_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kode_pos = ''
                  }" :hints="formErrors.kecamatan_id" label="Kecamatan" placeholder="Pilih Kecamatan" valueField="id"
        displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKecamatan',
                      kota_id: values.kota_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.alamat_tujuan"
        :errorText="formErrors.alamat_tujuan?'failed':''" @input="v=>values.alamat_tujuan=v"
        :hints="formErrors.alamat_tujuan" placeholder="Tuliskan Alamat" label="Alamat" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.status" @input="v=>values.status=v"
        :errorText="formErrors.status?'failed':''" :hints="formErrors.status" valueField="status" displayField="status"
        placeholder="label" fa-icon="bookmark" :check="false" :options="[{status:'Active'},{status:'InActive'}]" class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.status"
        :errorText="formErrors.status?'failed':''" @input="v=>values.status=v" :hints="formErrors.status"
        placeholder="Tuliskan Status" label="Status" fa-icon="" :check="false" />
    </div>



    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>
  <hr>
  <div class="flex flex-row items-center justify-end space-x-2 p-2">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
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
        v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update) "
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>
@endverbatim
@endif