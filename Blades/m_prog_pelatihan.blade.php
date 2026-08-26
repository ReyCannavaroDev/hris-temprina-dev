@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl border-t-10 border-gray-500">
      <div class="flex items-center gap-x-4">
        <p>Filter Status :</p>
        <div class="flex gap-x-2">
          <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
          <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
          <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
        </div>
      </div>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <div>
        <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))" v-if="data.can_create"
          class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
          Create New
        </RouterLink>
      </div>
    </template>
  </TableApi>
</div>
@else

@verbatim

<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded-2xl shadow-sm px-6 py-6 <md:w-full w-full bg-white">

      <!-- HEADER START -->
      <div class=" text-white rounded-t-md pt-2 mb-6">
        <div class="flex items-center">
          <div>
            <h1 class="text-20px text-black font-bold">Form Program Pelatihan </h1>
          </div>
        </div>
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-20 gap-y-3">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Nomor Transaksi<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX :bind="{ readonly: true }" label="" class="w-full py-2 !mt-0" :value="values.kode"
            :errorText="formErrors.kode?'failed':''" @input="v=>values.kode=v" :hints="formErrors.kode" :check="false"
            label="" placeholder="Auto Generate By System" />
        </div>

        <div>
          <label class="font-semibold">Bulan<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX type="month" :bind="{ readonly: !actionText }" label="" class="w-full py-2 !mt-0" :value="values.mont"
            :errorText="formErrors.mont?'failed':''" @input="v=>values.mont=v" :hints="formErrors.mont" :check="false"
            label="" placeholder="" />
        </div>

        <div>
          <label class="col-span-12">Tema Pelatihan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !actionText, disabled:!actionText }" class="w-full py-2 !mt-0"
            :value="values.tema_pelatihan" :errorText="formErrors.tema_pelatihan?'failed':''"
            @input="v=>values.tema_pelatihan=v" :hints="formErrors.tema_pelatihan" :check="false" label=""
            placeholder="" />
        </div>

        <div>
          <label class="col-span-12">Sasaran<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <!-- <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.sasaran" :errorText="formErrors.sasaran?'failed':''"
            @input="v=>values.sasaran=v" :hints="formErrors.sasaran" placeholder="" label="" fa-icon=""
            :check="false" /> -->
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:true, multiple: true }"
            :value="values.m_prog_pelatihan_d_level" @input="v=>values.m_prog_pelatihan_d_level=v"
            :errorText="formErrors.m_prog_pelatihan_d_level?'failed':''" :hints="formErrors.m_prog_pelatihan_d_level"
            valueField="id" displayField="level_name" :api="{
                  url: `${store.server.url_backend}/operation/m_level_posisi`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    transform:false,
                    join:false
                  }
              }" placeholder="" label="" fa-icon="" :check="false" />


          <!-- <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:false }"
            :value="values.sasaran" @input="v=>values.sasaran=v" :errorText="formErrors.sasaran?'failed':''"
            :hints="formErrors.sasaran" valueField="id" displayField="key"
            :options="[{'id' : 1 , 'key' : 'Aktif'}, {'id': 0, 'key' : 'Nonaktif'}]" placeholder="label"
            fa-icon="" :check="false" /> -->
        </div>

        <!-- <div>
          <label class="col-span-12">Level Karyawan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="flex flex-col gap-y-2">
            <ButtonMultiSelect title="Tambahkan Ke Table" :api="{
              url: `${store.server.url_backend}/operation/m_level_posisi`,
              headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                searchfield: 'this.code, this.long_name, uom.value1, uom_conv.value1, this.conv',
                //notin: arrDivisi.value.length > 0 ? `id:${arrDivisi.value.map(dt => dt.id).join(',')}`: undefined
                //where: `this.tipe_id=${values.m_tipe_id ?? 0} AND this.is_active=true`
              },
              onsuccess(response) {
                response.page = response.current_page
                response.hasNext = response.has_next
                return response
              }
            }" :columns="[
              {
                checkboxSelection: true,
                headerCheckboxSelection: true,
                headerName: 'No',
                valueGetter: () => '',
                width: 60,
                sortable: false,
                resizable: true,
                filter: false,
                cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
              },
              {
                headerName: 'Kode',
                field: 'code',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'Nama Item',
                field: 'long_name',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'UoM Besar',
                field: 'uom.value1',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'Konversi',
                field: 'conv',
                filter: true,
                sortable: true,
                flex: 1,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'UoM Kecil',
                field: 'uom_conv.value1',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              }
            ]" @add="addLevel">
              <button
            type="button"
            :disabled="!actionText"
            class="bg-[#2d7f28] hover:bg-[#256c21] disabled:bg-[#2d7f2880] disabled:cursor-not-allowed text-white py-2 px-4 text-xs flex items-center justify-center space-x-2 rounded"
          >
            <icon fa="plus" />
            <span class="font-semibold">Tambahkan Ke Tabel</span>
          </button>
            </ButtonMultiSelect>

            <FieldSelect :bind="{ disabled: !actionText, multiple: true, clearable:false }"
            class="w-full py-2 !mt-0"
              :value="values.tampilLevel" @input="v=>values.tampilLevel=v"
              :errorText="formErrors.tampilLevel?'failed':''" :hints="formErrors.tampilLevel"
              valueField="id" displayField="level_name" :api="{
              url: `${store.server.url_backend}/operation/m_level_posisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                transform:false,
                join:false
              }
          }" placeholder="" label="" fa-icon="" :check="false" />
          </div>
        </div> -->

        <!-- <div>
          <label>Divisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <div class="flex flex-col gap-y-2">
            <ButtonMultiSelect title="Tambahkan Ke Table" :api="{
              url: `${store.server.url_backend}/operation/m_divisi`,
              headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                searchfield: 'this.code, this.long_name, uom.value1, uom_conv.value1, this.conv',
                //notin: detailArr.length > 0 ? `this.id:${detailArr.map(dt => dt.m_item_id).join(',')}` : null,
                //where: `this.tipe_id=${values.m_tipe_id ?? 0} AND this.is_active=true`
              },
              onsuccess(response) {
                response.page = response.current_page
                response.hasNext = response.has_next
                return response
              }
            }" :columns="[
              {
                checkboxSelection: true,
                headerCheckboxSelection: true,
                headerName: 'No',
                valueGetter: () => '',
                width: 60,
                sortable: false,
                resizable: true,
                filter: false,
                cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
              },
              {
                headerName: 'Kode',
                field: 'name',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'Nama Item',
                field: 'long_name',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'UoM Besar',
                field: 'uom.value1',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'Konversi',
                field: 'conv',
                filter: true,
                sortable: true,
                flex: 1,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                headerName: 'UoM Kecil',
                field: 'uom_conv.value1',
                filter: true,
                sortable: true,
                flex: 2,
                filter: 'ColFilter',
                resizable: true,
                wrapText: true,
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              }
            ]" @add="addDivisi">
              <button
            type="button"
            :disabled="!actionText"
            class="bg-[#2d7f28] hover:bg-[#256c21] disabled:bg-[#2d7f2880] disabled:cursor-not-allowed text-white py-2 px-4 text-xs flex items-center justify-center space-x-2 rounded"
          >
            <icon fa="plus" />
            <span class="font-semibold">Tambahkan Ke Tabel</span>
          </button>
            </ButtonMultiSelect>

            <FieldSelect :bind="{ disabled: !actionText, multiple: true, clearable:false }"
            class="w-full py-2 !mt-0"
              :value="values.tampilDivisi" @input="v=>values.tampilDivisi=v"
              :errorText="formErrors.tampilDivisi?'failed':''"
              :hints="formErrors.tampilDivisi" valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_divisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                transform:false,
                join:false
              }
          }" placeholder="" label="" fa-icon="" :check="false" />
          </div>
        </div> -->

        <div>
          <label>Sifat/Penyelenggara<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:false }"
            :value="values.sifat_penyelenggara" @input="v=>values.sifat_penyelenggara=v"
            :errorText="formErrors.sifat_penyelenggara?'failed':''" :hints="formErrors.sifat_penyelenggara"
            valueField="key" displayField="key"
            :options="[{'id' : 1 , 'key' : 'INTERNAL'}, {'id': 0, 'key' : 'EKSTERNAL'}]" placeholder="" fa-icon=""
            :check="false" />
          <!-- <div class="flex justify-between gap-x-5 !mt-2"> -->
          <!-- <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.sifat_penyelenggara"
            :errorText="formErrors.sifat_penyelenggara?'failed':''" @input="v=>values.sifat_penyelenggara=v"
            :hints="formErrors.sifat_penyelenggara" placeholder="" label="" fa-icon="" :check="false" /> -->
          <!-- <FieldX :bind="{ readonly: !actionText }" 
              :value="values.sifat_penyelenggara" :errorText="formErrors.sifat_penyelenggara?'failed':''"
              @input="v=>values.sifat_penyelenggara=v" :hints="formErrors.sifat_penyelenggara" 
              placeholder="" label="" fa-icon="" :check="false"
            /> -->
          <!-- <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.sasaran"
              @input="v=>values.sasaran=v" :errorText="formErrors.sasaran?'failed':''" :hints="formErrors.sasaran"
              valueField="id" displayField="key"
              :options="[{'id' : 1 , 'key' : 'Aktif'}, {'id': 0, 'key' : 'Nonaktif'}]" placeholder="" label=""
              fa-icon="" :check="false" />
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.sasaran"
              @input="v=>values.sasaran=v" :errorText="formErrors.sasaran?'failed':''" :hints="formErrors.sasaran"
              valueField="id" displayField="key"
              :options="[{'id' : 1 , 'key' : 'Aktif'}, {'id': 0, 'key' : 'Nonaktif'}]" placeholder="" label=""
              fa-icon="" :check="false" /> -->
          <!-- </div> -->
        </div>

        <div>
          <label class="col-span-12">Jumlah Peserta<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldNumber class="w-full py-2 !mt-0" :bind="{ readonly:!actionText, disabled:!actionText }"
            :value="values.jumlah_peserta" :errorText="formErrors.jumlah_peserta?'failed':''"
            @input="v=>values.jumlah_peserta=v" :hints="formErrors.jumlah_peserta" label="" fa-icon="" :check="false" />
        </div>
        <div>
          <label class="col-span-12">Budget(Rp)<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldNumber class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.total_budget"
            :errorText="formErrors.total_budget?'failed':''" @input="v=>values.total_budget=v"
            :hints="formErrors.total_budget" placeholder="Masukkan Text" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Status<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:false }"
            :value="values.is_active" @input="v=>values.is_active=v" :errorText="formErrors.is_active?'failed':''"
            :hints="formErrors.is_active" valueField="id" displayField="key"
            :options="[{'id' : 1 , 'key' : 'Active'}, {'id': 0, 'key' : 'Inactive'}]" placeholder="" label="" fa-icon=""
            :check="false" />
        </div>
      </div>
      <hr class="mt-10">
      <!-- FORM END -->
      <div class="flex justify-end gap-4">
        <button  @click="onBack" class="mt-2 bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Batal
        </button>
        <button v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)" @click="onSave" class="mt-2 bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Simpan
        </button>
      </div>
    </div>

  </div>


</div>

@endverbatim
@endif