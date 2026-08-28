<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center px-2.5 py-2 gap-y-3">

    <div class="flex flex-col md:flex-row md:items-center gap-y-2 md:gap-x-4 w-full md:w-auto">
      <p class="text-sm font-medium whitespace-nowrap">Filter Status :</p>
      <div class="flex items-center gap-x-2">	
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">DRAFT</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        	
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">POSTED</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        	
        <button @click="filterShowData('IN APPROVAL',3)" :class="activeBtn === 3?'bg-blue-600 text-white hover:bg-blue-400':'border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">IN APPROVAL</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        <button @click="filterShowData('REVISED',4)" :class="activeBtn === 4?'bg-amber-600 text-white hover:bg-amber-400':'border border-amber-600 text-amber-600 bg-white hover:bg-amber-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">REVISED</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        	
        <button @click="filterShowData('APPROVED',5)" :class="activeBtn === 5?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">APPROVED</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        	
        <button @click="filterShowData('HALF APPROVED',6)" :class="activeBtn === 6?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">HALF APPROVED</button>	
        <div class="h-4 w-0.5 bg-[#6E91D1]"></div>	
        	
        <button @click="filterShowData('REJECTED',7)" :class="activeBtn === 7?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ">REJECTED</button>	
      </div>
    </div>

    <div v-if="data?.can_create" class="w-full md:w-auto text-right">
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="inline-block border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1.5 px-3 text-sm font-medium w-full md:w-auto text-center">
        + Create New
      </RouterLink>
    </div>
  </div>

  <hr>

  <div class="overflow-x-auto">
    <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
      class="">
    </TableApi>
  </div>
</div>
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
        <h1 class="text-20px font-bold">Form Transaksi Pengajuan Perdin</h1>
        <p class="text-gray-100">Transaksi Pengajuan Perdin</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-2 gap-2">
    <!-- START COLUMN -->
    <div>
      <FieldPopup class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.t_perdin_id"
        @input="(v)=>values.t_perdin_id=v" :errorText="formErrors.t_perdin_id?'failed':''"
        :hints="formErrors.t_perdin_id" valueField="id" displayField="tugas" @update:valueFull="obj => {
          $log('res',obj)
                if (obj) {
                  values.provinsi_id = obj['provinsi.id']; 
                  values.kota_id = obj['kota.id']; 
                  values.tugas = obj.tugas; 
                  values.posisi_id = obj['m_kary.m_posisi_id']; 
                  values.m_kary_id = obj['m_kary.id']; 
                  values.tujuan = obj.tujuan; 
                  values.tanggalAwal = obj.date_from; 
                  values.tanggalAkhir = obj.date_to; 
                  values.tujuan = obj.tempat_tujuan; 
                  values.alamat_tujuan = obj.alamat_tujuan;
                  values.m_posisi_id = obj.m_posisi_id;
                } else {
                  values.m_kary_id = null;
                  values.tanggalAwal = null; 
                  values.tanggalAkhir = null; 
                  values.posisi_id = null; 
                  values.provinsi_id = null; 
                  values.kota_id = null; 
                  values.tugas = null; 
                  values.tujuan = null; 
                  values.total_biaya = null; 
                  values.alamat_tujuan = null; 
                  values.m_posisi_id = null
                  detailArr = []
                }
              }" :api="{
          url: `${store.server.url_backend}/operation/t_perdin`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            //scopes: 'listPerdin'
            //where: `this.m_kary_id = ${values.m_kary_id}`
            searchfield: 'm_kary.nama_lengkap, this.tugas, this.date_from, date_to, this.alamat_tujuan'
          }
        }" placeholder="Pilih Perdin" label="Perdin" fa-icon="" :check="false" :columns="[{
          headerName: 'No',
          valueGetter:(p)=>p.node.rowIndex + 1,
          width: 60,
          sortable: false, resizable: false, filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
        {
          flex: 1,
          field: 'tugas',
          headerName:  'Tugas Perdin',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'date_from',
          headerName: 'Tanggal Mulai',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'date_to',
          headerName: 'Tanggal Selesai  ',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'alamat_tujuan',
          headerName: 'Tujuan',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        {
          flex: 1,
          field: 'm_kary.nama_lengkap',
          headerName: 'Karyawan',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        },
        ]" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full !mt-3" :value="values.m_posisi_id" @input="(v) => {
              values.m_posisi_id = v;
            }" :errorText="formErrors.m_posisi_id?'failed':''" displayField="name" :hints="formErrors.m_posisi_id"
        :api="{
                  url: `${store.server.url_backend}/operation/m_posisi`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    //where: `this.group = 'PROVINSI'`,
                    //selectfield:'this.id,this.value'
                  }
            }" valueField="id" :check="false" label="Jabatan" placeholder="Auto Field By System" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full !mt-3" :value="values.provinsi_id" @input="(v) => {
              values.provinsi_id = v;
            }" :errorText="formErrors.provinsi_id?'failed':''" displayField="value" :hints="formErrors.provinsi_id"
        :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    where: `this.group = 'PROVINSI'`,
                    selectfield:'this.id,this.value'
                  }
            }" valueField="id" :check="false" label="Provinsi" placeholder="Auto Field By System" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full !mt-3" :value="values.kota_id" @input="(v) => {
              values.kota_id = v;
            }" :errorText="formErrors.kota_id?'failed':''" displayField="value" :hints="formErrors.kota_id" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    where: `this.group = 'KOTA'`,
                    selectfield:'this.id,this.value'
                  }
            }" valueField="id" :check="false" label="Kota" placeholder="Auto Field By System" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full !mt-3" :value="values.m_kary_id" @input="(v) => {
              values.m_kary_id = v;
            }" :errorText="formErrors.m_kary_id?'failed':''" displayField="nama_depan" :hints="formErrors.m_kary_id"
        :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest: true,
                    join: false,
                    //where: `this.group = 'KOTA'`,
                    //selectfield:'this.id,this.value'
                  }
            }" valueField="id" :check="false" label="Karyawan" placeholder="Auto Field By System" />
    </div>

    <div>
      <FieldNumber :bind="{ readonly: true }" :value="hitungTotalPerdin" :errorText="formErrors.total_biaya?'failed':''"
        @input="v=>values.total_biaya=v" :hints="formErrors.total_biaya" :check="false" class="w-full !mt-3"
        label="Total Biaya" placeholder="Auto Generate By System" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" type="date" :bind="{ readonly: true }" :value="values.tanggalAwal"
        :errorText="formErrors.tanggalAwal?'failed':''" @input="v=>values.tanggalAwal=v" :hints="formErrors.tanggalAwal"
        placeholder="Auto Field By System" label="Tanggal Mulai" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" type="date" :bind="{ readonly: true }" :value="values.tanggalAkhir"
        :errorText="formErrors.tanggalAkhir?'failed':''" @input="v=>values.tanggalAkhir=v"
        :hints="formErrors.tanggalAkhir" placeholder="Auto Field By System" label="Tanggal Selesai" fa-icon=""
        :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.tugas"
        :errorText="formErrors.tugas?'failed':''" @input="v=>values.tugas=v" :hints="formErrors.tugas"
        placeholder="Auto Field By System" label="Tugas" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.tujuan"
        :errorText="formErrors.tujuan?'failed':''" @input="v=>values.tujuan=v" :hints="formErrors.tujuan"
        placeholder="Auto Field By System" label="Tujuan" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.alamat_tujuan"
        :errorText="formErrors.alamat_tujuan?'failed':''" @input="v=>values.alamat_tujuan=v"
        :hints="formErrors.alamat_tujuan" placeholder="Auto Field By System " label="Alamat Tujuan" fa-icon=""
        :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.status"
        :errorText="formErrors.status?'failed':''" @input="v=>values.status=v" :hints="formErrors.status" placeholder=""
        label="" fa-icon="" :check="false" />
    </div>
    <div>
      <FieldSelect v-show="route.query.action?.toLowerCase() === 'verifikasi'" :value="values.target_id"
        @input="v=>values.target_id=v" :errorText="formErrors.target_id?'failed':''" :hints="formErrors.target_id"
        valueField="id" displayField="nama_lengkap" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                selectfield: 'this.id,this.nama_lengkap',
                scopes: 'higherlevel',
                t_m_kary_id: `${values.m_kary_id}`,
              }
          }" placeholder="Pilih Target Approval" label="Target Approval" fa-icon="" :check="false" />

    </div>
    <!-- END COLUMN -->
    <!-- ACTION BUTTON START -->
  </div>

  <div class="grid grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[29px] gap-y-[26px] mx-4">
    <div class="col-span-8 md:col-span-12">

      <div class="flex justify-between w-[18%]">
        <ButtonMultiSelect v-show="actionText" title="Add to list" @add="onDetailAdd" :api="{
            url: `${store.server.url_backend}/operation/m_tarif_perdin`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { 
              where: `this.is_active = true`,
              scopes: 'WithDetail,level',
              t_m_posisi_id: `${values.m_posisi_id}`,
            },
            onsuccess:(response)=>{
              response.data = [...response.data].map((dt)=>{
                $log('dt coy',dt)
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
            headerName:'Nama Tarif',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'desc',
            cellClass: ['justify-start','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Level',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'm_level_posisi.level_name',
            cellClass: ['justify-start','!border-gray-200']
          }
          
          ]">
          <div
            class="bg-[#005FBF] hover:bg-[#0055ab] text-white text-[12px] px-2 py-2 text-sm flex items-center justify-center space-x-1 rounded">
            <icon fa="plus" size="sm mr-0.5" /> Add Tarif
          </div>
        </ButtonMultiSelect>
        <button
  :disabled="!actionText"
  @click="addDetail"
  type="button"
  class="bg-[#005FBF] hover:bg-[#0055ab] text-white text-[12px] px-2 py-2 text-sm flex items-center justify-center space-x-1 rounded"
>
  <icon fa="plus" class="text-[12px]" />
  <span>Add to List</span>
</button>

      </div>
      <div class="mx-1 mt-4">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
                Komponen</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Nominal</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Jumlah</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Total</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                Catatan</td>
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
                <FieldX :bind="{ readonly: !actionText }" class="!mt-0" :value="item.komponen"
                  @input="v=>item.komponen=v" :errorText="formErrors.komponen?'failed':''" :hints="formErrors.komponen"
                  label="" placeholder="Tuliskan Komponen" :check="false" />
              </td> -->
               <td class="p-2 border border-[#CACACA]">
                  <FieldSelect
                    :bind="{ disabled: !actionText, clearable:false }"
                    :value="item.komponen" @input="v=>item.komponen=v"
                    :errorText="formErrors.komponen?'failed':''" 
                    :hints="formErrors.komponen"
                    valueField="value" displayField="value"
                    :api="{
                        url: `${store.server.url_backend}/operation/m_general`,
                        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                        params: {
                          where: `this.group = 'KOMPONEN TARIF PERDIN'`,
                          simplest:true,
                          transform:false,
                          join:false
                        }
                    }"
                    placeholder="" label="" fa-icon="" :check="false"
                  />
                </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.nominal"
                  @input="v => { item.nominal = v; item.total = Number(item.nominal || 0) * Number(item.jumlah || 0) }"
                  :errorText="formErrors.nominal?'failed':''" :hints="formErrors.nominal" placeholder="" label=""
                  :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.jumlah"
                  @input="v => { item.jumlah = v; item.total = Number(item.nominal || 0) * Number(item.jumlah || 0) }"
                  :errorText="formErrors.jumlah?'failed':''" :hints="formErrors.jumlah" placeholder="" label=""
                  :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber class="!mt-0 w-full" :bind="{ readonly: true }" :value="item.total"
                  :errorText="formErrors.total?'failed':''" :hints="formErrors.total" placeholder="" label=""
                  :check="false" />
              </td>

              <td class="p-2 border border-[#CACACA]">
                <FieldX class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.catatan"
                  @input="(v)=>item.catatan=v" :errorText="formErrors.catatan?'failed':''" :hints="formErrors.catatan"
                  placeholder="" label="" :check="false" />
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
      <div v-show="route.query.is_approval || ['APPROVAL', 'APPROVED', 'REJECT', 'REVISED'].includes(values.status)"
        class="<md:col-span-1 col-span-2 p-4 grid <md:grid-cols-1 grid-cols-3 gap-2">

        <!-- <div>
          <table class=" w-[100%] my-3 border">
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Nomor</td>
              <td class="border px-2 py-1">{{ values.approval?.nomor ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Tanggal</td>
              <td class="border px-2 py-1">{{ values.approval?.created_at ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Pemohon</td>
              <td class="border px-2 py-1">{{ values.approval?.creator ?? '-' }}</td>
            </tr>
            <tr class="border">
              <td class="border px-2 py-1 font-semibold">Status</td>
              <td class="border px-2 py-1">{{ values.approval?.status ?? '-' }}</td>
            </tr>
          </table>
        </div> -->
        <!-- <div class="">
          <table class=" w-[100%] my-3 ">
            <tr>
              <td class=" px-2 py-1">
                <button
                  v-show="route.query.is_approval || values.status?.toUpperCase() !== 'APPROVED'"
                    @click="openModal(values?.trx?.id ?? 0)"
                    class="hover:text-blue-500">
                    <icon fa="table" size="sm"/>
                    Log Approval
                  </button>
              </td>
            </tr>
          </table>
        </div> -->
        <div v-show="route.query.is_approval" class="w-1/2 mt-3">
          <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="textarea" :bind="{ readonly: !route.query.is_approval }" class="w-full py-2 !mt-0"
            :value="values.catatan" :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v"
            :hints="formErrors.catatan" :check="false" label="" placeholder="Tuliskan catatan" />
        </div>
      </div>
    </div>
    <!--BUTTON-->
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

  <div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white  w-[70%] mx-auto rounded shadow-lg z-50 overflow-y-auto">
      <div class="modal-content py-4 text-left px-6">
        <div class="modal-header flex items-center justify-between flex-wrap">
          <div class="flex items-center">
            <h3 class="text-xl font-semibold ml-2">Log Approval
              <span v-if="!dataLog?.items.length" class="!text-red-600"> | Belum ada log approval</span>
            </h3>
          </div>
        </div>

        <!-- <div v-if="dataLog?.items.length" class="modal-body">
          <table class="w-[100%] my-3 border">
            <thead>
              <tr class="border">
                <td class="border px-2 py-1 font-medium ">Urutan</td>
                <td class="border px-2 py-1 font-medium ">Nomor Transaksi</td>
                <td class="border px-2 py-1 font-medium ">Tipe Aksi</td>
                <td class="border px-2 py-1 font-medium ">Tanggal Aksi </td>
                <td class="border px-2 py-1 font-medium ">User Aksi</td>
                <td class="border px-2 py-1 font-medium ">Catatan</td>
              </tr>
            </thead>
            <tr class="border" v-for="d,i in dataLog?.items" :key="i">
              <td class="border px-2 py-1">{{ i+1 }}</td>
              <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_at ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_user ?? '-' }}</td>
              <td class="border px-2 py-1">{{ d.action_note ?? '-' }}</td>
            </tr>
          </table>
        </div> -->

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
@endif