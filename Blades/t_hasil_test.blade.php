<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex flex-col md:flex-row md:items-center gap-y-2 md:gap-y-0 gap-x-4">
      <p class="font-semibold">Filter Status:</p>

      <!-- Dropdown Mobile -->
      <div class="block md:hidden">
        <select
          @change="onStatusChange"
          class="border rounded-md text-sm py-1 px-2.5 w-full"
        >
          <option value="">Pilih Status</option>
          <option value="1">Pending</option>
          <option value="2">Proses</option>
          <option value="3">Diterima</option>
          <option value="4">Tidak Diterima</option>
        </select>
      </div>

      <!-- Button Desktop -->
      <div class="hidden md:flex flex-wrap gap-2">

        <button
          @click="filterShowData('Pending',1)"
          :class="activeBtn === 1 
            ? 'bg-gray-700 text-white' 
            : 'border border-gray-700 text-gray-700 bg-white hover:bg-gray-700 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          Pending
        </button>

        <button
          @click="filterShowData('Proses',2)"
          :class="activeBtn === 2 
            ? 'bg-blue-600 text-white' 
            : 'border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          Proses
        </button>

        <button
          @click="filterShowData('Diterima',3)"
          :class="activeBtn === 3 
            ? 'bg-green-600 text-white' 
            : 'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          Diterima
        </button>

        <button
          @click="filterShowData('Tidak Diterima',4)"
          :class="activeBtn === 4 
            ? 'bg-red-600 text-white' 
            : 'border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white'"
          class="rounded-md text-sm py-1 px-3 transition-all duration-300">
          Tidak Diterima
        </button>

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
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="">
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
        <h1 class="text-20px font-bold">Form Hasil Test</h1>
        <p class="text-gray-100">Transaksi Hasil Test</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-2 gap-2">
    <!-- START COLUMN -->
    <!-- <div>
      <FieldX :bind="{ readonly: true }" :value="values.kode" :errorText="formErrors.kode?'failed':''"
        @input="v=>values.kode=v" :hints="formErrors.kode" :check="false" class="w-full !mt-3" label="Kode"
        placeholder="Tuliskan Kode" />
    </div> -->

    <div>
      <FieldX :bind="{ readonly: true }" :value="values.nomor" :errorText="formErrors.desc?'failed':''"
        @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false" class="w-full !mt-3" label="Nomor"
        placeholder="Auto Generate By System" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.t_pelamar_id" class="w-full !mt-3"
        @input="v=>values.t_pelamar_id=v" :errorText="formErrors.t_pelamar_id?'failed':''"
        :hints="formErrors.t_pelamar_id" valueField="id" displayField="nama_depan" :api="{
            url: `${store.server.url_backend}/operation/t_pelamar`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              transform:false,
              join:false
            }
        }" placeholder="Pilih Pelamar" label="Nama Pelamar" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.t_loker_id" class="w-full !mt-3"
        @input="v=>values.t_loker_id=v" :errorText="formErrors.t_loker_id?'failed':''" :hints="formErrors.t_loker_id"
        valueField="id" displayField="title" :api="{
            url: `${store.server.url_backend}/operation/t_loker`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              transform:false,
              join:false
            }
        }" placeholder="Pilih Loker" label="Nama Loker" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.tahapan_id" class="w-full !mt-3"
        @input="v=>values.tahapan_id=v" :errorText="formErrors.tahapan_id?'failed':''"
        :hints="formErrors.tahapan_id" valueField="id" displayField="value" :api="{
            url: `${store.server.url_backend}/operation/m_general`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              where: `this.group = 'TAHAPAN-PENERIMAAN-KARYAWAN'`,
              simplest: true,
              transform: false,
              join: false
            }
        }" placeholder="Pilih Tahapan" label="Tahapan" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" :value="values.deskripsi" :errorText="formErrors.deskripsi?'failed':''"
        @input="v=>values.deskripsi=v" :hints="formErrors.deskripsi" :check="false" class="w-full !mt-3" label="Catatan"
        placeholder="Tulis Catatan" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.status"
        @input="v=>values.status=v" :errorText="formErrors.status?'failed':''" :hints="formErrors.status"
        valueField="value" displayField="value" :options="[
          { value: 'PENDING' },
          { value: 'PROSES' },
          { value: 'DITERIMA' },
          { value: 'TIDAK DITERIMA' }
        ]" placeholder="Pilih Status" label="Status" fa-icon="" :check="false" />
    </div>


    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>

  <div class="grid grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[29px] gap-y-[26px] mx-4">

    <div class="col-span-8 md:col-span-12">
      <button :disabled="!actionText" @click="addDetail" type="button" class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
              <icon fa="plus" /> <span>Add to List</span></button>
      <div class="mx-1 mt-4">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Tanggal</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Nama Test</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Nilai Test</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Dokumen</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[5%] bg-[#f8f8f8] border-[#CACACA]">
                Aksi</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
              <td class="p-2 text-center border border-[#CACACA]">
                {{ i + 1 }}.
              </td>
              <!-- <td class="p-2 border border-[#CACACA]">
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable:false }"
                  :value="item.m_divisi_id" @input="v=>item.m_divisi_id=v"
                  :errorText="formErrors.m_divisi_id?'failed':''" 
                  :hints="formErrors.m_divisi_id"
                  valueField="id" displayField="name.value"
                  :api="{
                      url: `${store.server.url_backend}/operation/m_divisi`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where: `this.is_active = true`,
                        scopes:'Name',
                        simplest:true,
                        transform:false,
                        join:false
                      }
                  }"
                  placeholder="" label="" fa-icon="" :check="false"
                />
              </td> -->
              <td class="p-2 border border-[#CACACA]">
                <FieldX type="date" :bind="{ readonly: !actionText }" :value="item.tanggal"
                  :errorText="formErrors.tanggal?'failed':''" @input="v=>item.tanggal=v" :hints="formErrors.tanggal"
                  placeholder="Masukkan Tanggal" label="" fa-icon="" :check="false" />
              </td>

              <td class="p-2 border border-[#CACACA]">
                <FieldSelect
                  :bind="{ disabled: !actionText, clearable: false }"
                  :value="item.nama_tes"
                  @input="v=>item.nama_tes=v"
                  :errorText="formErrors.nama_tes?'failed':''"
                  :hints="formErrors.nama_tes"
                  valueField="value"
                  displayField="value"
                  :api="{
                      url: `${store.server.url_backend}/operation/m_general`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where: `this.group = 'NAMA-TEST-PELAMAR'`,
                        simplest: true,
                        transform: false,
                        join: false
                      }
                  }"
                  placeholder="Pilih Nama Test"
                  label=""
                  fa-icon=""
                  :check="false"
                />
              </td>

              <td class="p-2 border border-[#CACACA]">
                <FieldNumber :bind="{ readonly: !actionText }" :value="item.nilai_tes"
                  :errorText="formErrors.nilai_tes?'failed':''" @input="v=>item.nilai_tes=v"
                  :hints="formErrors.nilai_tes" placeholder="Masukkan Nilai Test" label="" fa-icon="" :check="false" />
              </td>

              <td>
                <FieldUpload :value="item.dokumen" @input="(v)=>item.dokumen=v" :maxSize="10"
                  :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                    url: `${store.server.url_backend}/operation/t_hasil_tes_det/upload`,
                    headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: { field: 'dokumen' },
                    onsuccess: response=>response,
                    onerror:(error)=>{},
                   }" :hints="formErrors.dokumen" placeholder="Upload Dokumen" label="" fa-icon="upload" accept="*"
                  :check="false" />

              </td>

              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <button type="button" @click="removeDetail(item)" :disabled="!actionText">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
                </div>

              </td>
            </tr>
            <tr v-else class="text-center">
              <td colspan="7" class="py-[20px]">
                No data to show
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!--BUTTON-->
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
        v-show="actionText  "
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>
@endverbatim
@endif