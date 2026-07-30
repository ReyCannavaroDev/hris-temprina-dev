<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex w-[60%] md:w-[80%] flex-wrap items-center gap-x-2 gap-y-2">
      <p class="font-semibold whitespace-nowrap lg:w-[10%]">Show Data :</p>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('DRAFT')" :class="activeBtn?.toUpperCase() === 'DRAFT'?'bg-gray-600 font-semibold !text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform transition hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      </div>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('POST')" :class="activeBtn?.toUpperCase() === 'POSTED'?'bg-amber-600 !text-white hover:bg-amber-400':'border border-amber-600 font-semibold bg-white text-amber-600  hover:bg-amber-600 hover:text-white'" class="duration-300 transition transform hover:-translate-y-0.5 rounded-md py-1 px-2">POST</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      </div>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('APPROVAL')" :class="activeBtn?.toUpperCase() === 'IN APPROVAL'?'bg-blue-600 !text-white hover:bg-blue-400':'border border-blue-600 font-semibold hover:bg-blue-600 text-blue-600 hover:text-white'" class="duration-300 transform transition hover:-translate-y-0.5 rounded-md py-1 px-2"> APPROVAL</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      </div>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('APPROVED')" :class="activeBtn?.toUpperCase() === 'APPROVED'?'bg-green-600 !text-white hover:bg-green-400':'border border-green-600 font-semibold bg-white text-green-600 hover:bg-green-600 hover:text-white'" class="duration-300 transition transform hover:-translate-y-0.5 rounded-md py-1 px-2">APPROVED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      </div>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('REJECTED')" :class="activeBtn?.toUpperCase() === 'REJECTED'?'bg-red-600 !text-white hover:bg-red-400':'border border-red-600 font-semibold bg-white text-red-600 hover:bg-red-600 hover:text-white'" class="duration-300 transform transition hover:-translate-y-0.5 rounded-md py-1 px-2">REJECTED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      </div>
      <div class="flex items-center gap-x-2">
        <button @click="filterShowData('REVISED')" :class="activeBtn?.toUpperCase() === 'REVISED'?'bg-purple-600 !text-white hover:bg-purple-400':'border border-purple-600 font-semibold bg-white text-purple-600 hover:bg-purple-600 hover:text-white'" class="duration-300 transition transform hover:-translate-y-0.5 rounded-md py-1 px-2">REVISED</button>
      </div>
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
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
    <!-- <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.t_perdin_id"
        @input="v=>values.t_perdin_id=v" :errorText="formErrors.t_perdin_id?'failed':''" :hints="formErrors.t_perdin_id"
        valueField="id" displayField="tugas" :api="{
                url: `${store.server.url_backend}/operation/t_perdin`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  scopes: 'usedPerdin,Rincian',
                  simplest:true,
                  transform:false,
                  join:true
                }
            }" placeholder="Pilih Perdin" label="Perjalanan Dinas" fa-icon="" :check="false" />
    </div> -->
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
            scopes: 'usedPerdin',
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
            }" :errorText="formErrors.m_kary_id?'failed':''" displayField="nama_depan" :hints="formErrors.m_kary_id" :api="{
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
      <FieldPopup
        class="w-full !mt-3"
        :bind="{ readonly: !actionText }"
        :value="values.kbs_id"
        @input="v => values.kbs_id = v"
        valueField="id"
        displayField="no"
        placeholder="Pilih KBS"
        label="KBS"
        :check="false"

        @update:valueFull="obj => {
          if (obj) {
            values.kbs_id     = obj.id
            values.t_kbs_id     = obj.id
            values.kbs_no     = obj.no
            values.no_kbs     = obj.no
            values.kbs_date   = obj.date
            values.kbs_duedate= obj.duedate
            values.kbs_amount = obj.amount
            values.nominal = obj.amount
            values.nominal_kbs = obj.amount
            values.kbs_pic    = obj.pic
            values.kbs_status = obj.status
          } else {
            values.kbs_id      = null
            values.kbs_no      = null
            values.kbs_date    = null
            values.kbs_duedate = null
            values.kbs_amount  = null
            values.nominal  = null
            values.kbs_pic     = null
            values.kbs_status  = null
          }
        }"

        :api="{
          url: `https://erp.temprina.com/api/public/t_kbs/getKbs`,
          headers: {
            'Content-Type': 'application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            searchfield: 'date,no,duedate,pic,amount'
          }
        }"

        :columns="[
          {
            headerName: 'No',
            valueGetter: p => p.node.rowIndex + 1,
            width: 60,
            sortable: false,
            cellClass: ['justify-center', 'bg-gray-50']
          },
          {
            field: 'no',
            headerName: 'No KBS',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['border-r', 'justify-center']
          },
          {
            field: 'date',
            headerName: 'Tanggal',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['border-r', 'justify-center']
          },
          {
            field: 'duedate',
            headerName: 'Jatuh Tempo',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['border-r', 'justify-center']
          },
          {
            field: 'pic',
            headerName: 'PIC',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['border-r', 'justify-center']
          },
          {
            field: 'amount',
            headerName: 'Nominal',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['border-r', 'justify-center']
          },
          {
            field: 'status',
            headerName: 'Status',
            flex: 1,
            sortable: true, resizable: true,
            cellClass: ['justify-center']
          }
        ]"
      />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.no_kbs"
        :errorText="formErrors.no_kbs?'failed':''" @input="v=>values.no_kbs=v" :hints="formErrors.no_kbs"
        placeholder="Auto Generate By System" label="Nomor Kas Bon" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldNumber class="w-full !mt-3" :bind="{ readonly: true }" :value="values.nominal"
        :errorText="formErrors.nominal?'failed':''" @input="v=>values.nominal=v" :hints="formErrors.nominal"
        placeholder="Auto Generate By System" label="Nominal Kas Bon" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldNumber class="w-full !mt-3" :bind="{ readonly: true }" :value="hitungTotalPerdin"
        :errorText="formErrors.total_biaya?'failed':''" @input="v=>values.total_biaya=v" :hints="formErrors.total_biaya"
        placeholder="Tuliskan Total Biaya" label="Total Biaya" fa-icon="" :check="false" />
    </div>


    <div>
      <FieldNumber class="w-full !mt-3" :bind="{ readonly: true }" :value="values.nominal - values.total_biaya"
        :errorText="formErrors.sisa_biaya?'failed':''" @input="v=>values.sisa_biaya=v" :hints="formErrors.sisa_biaya"
        placeholder="Auto Generate By System" label="Sisa Biaya" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.status"
        :errorText="formErrors.status?'failed':''" @input="v=>values.status=v" :hints="formErrors.status"
        placeholder="Tuliskan Status" label="Status" fa-icon="" :check="false" />
    </div>

    <div class="col-span-3 mt-4">
      <h2 class="text-18px font-semibold">Detail Perdin</h2>

      <div class="flex items-center space-x-3 mt-3">

      <button
        v-if="actionText"
        title="Add Detail"
        @click="generatePerdin"
        class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-2 flex items-center space-x-2">
        <icon fa="plus" />
        <span>Generate</span>
      </button>

      <button
        v-if="actionText"
        @click="addDetail"
        type="button"
        class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-2 flex items-center space-x-2">
        <icon fa="plus" />
        <span>Add to List</span>
      </button>

      </div>
    </div>


    <div class="<md:col-span-1 col-span-3 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <div class="<md:col-span-1 col-span-3">
        <div class="overflow-scroll lg:overflow-visible <md:col-span-1 col-span-3 mt-2">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA] pt-4">
            <thead>
              <tr class="border">
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                  No</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[25%] border bg-[#f8f8f8] border-[#CACACA]">
                  Komponen</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                  Nominal</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                  Jumlah</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                  Total</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Catatan</td>
                <td
                  class="text-[#8f8f8f] w-[5%] text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                </td>

              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
                <td class="text-[12px] text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>
                <td class="px-1 text-[12px] border border-[#CACACA]">
                  <FieldX :bind="{ readonly: !actionText }" class="w-full" :value="item.komponen"
                    :errorText="formErrors.komponen?'failed':''" @input="v => item.komponen = v"
                    :hints="formErrors.komponen" :check="false" label="" />
                </td>
                <td class="px-1 text-[12px] border border-[#CACACA]">
                  <FieldNumber :bind="{ readonly: !actionText }" class="w-full" :value="item.nominal"
                    :errorText="formErrors.nominal?'failed':''" @input="v => { item.nominal = v; item.total = Number(item.nominal || 0) * Number(item.jumlah || 0) }"
                    :hints="formErrors.nominal" :check="false" label="" />
                </td>
                <td class="px-1 text-[12px] border border-[#CACACA]">
                  <FieldNumber :bind="{ readonly: !actionText }" class="w-full" :value="item.jumlah"
                    :errorText="formErrors.jumlah?'failed':''" @input="v => { item.jumlah = v; item.total = Number(item.nominal || 0) * Number(item.jumlah || 0) }" :hints="formErrors.jumlah"
                    :check="false" label="" />
                </td>
                <td class="px-1 text-[12px] border border-[#CACACA]">
                  <FieldNumber :bind="{ readonly: !actionText }" class="w-full" :value="item.total"
                    :errorText="formErrors.total?'failed':''" @input="v => item.total = v" :hints="formErrors.total"
                    :check="false" label="" />
                </td>
                <td class="text-[12px] text-center border border-[#CACACA]">
                  <FieldX :bind="{ readonly: !actionText }" class="w-full" :value="item.catatan  "
                    :errorText="formErrors.catatan  ?'failed':''" @input="v=>item.catatan =v"
                    :hints="formErrors.catatan " :check="false" label="" type="textarea" />
                </td>
                <td>
                  <div class="flex justify-center">
                    <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                      <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                      </svg>
                </button>
                  </div>
                </td>
              </tr>
              <tr v-else class="text-center">
                <td colspan="12" class="py-[20px]">
                  No data to show
                </td>
              </tr>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- END TABLE DETAIL -->
      <div v-show="route.query.is_approval || ['APPROVAL', 'APPROVED', 'REJECT', 'REVISED'].includes(values.status)"
        class="<md:col-span-1 col-span-2 p-4 grid <md:grid-cols-1 grid-cols-3 gap-2">

        <div>
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
        </div>
        <div class="">
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
            <!-- <tr v-show="isFinish">
              <td class=" px-2 py-1">
                <button
                  @click="downloadDoc()" 
                  class="hover:text-blue-500">
                  <icon fa="download" size="sm"/>
                  Download .docx
                </button>
              </td>
            </tr> -->
          </table>
        </div>
        <div class="w-1/2 mt-3">
          <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: !route.query.is_approval }" class="w-full py-2 !mt-0" :value="values.note"
            :errorText="formErrors.note?'failed':''" @input="v=>values.note=v" :hints="formErrors.note" :check="false"
            label="" placeholder="Tuliskan catatan" />
        </div>
      </div>
    </div>
  </div>
  <hr>
  <div class="grid grid-cols-12 items-center">
    <div class="col-span-7 justify-start ml-5">
      <p v-if="values.created_id">Created By {{values.created_id}} on {{values.created_at}}</p>
      <p v-if="values.edited_id">Last Edit By {{values.edited_id}} on {{values.edited_at}}</p>
    </div>
    <div class="col-span-5 justify-end space-x-2 p-2">
      <div class="flex flex-row items-center justify-end space-x-2 p-2">
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
        <button v-show="route.query.is_approval" class="mx-1 bg-green-500 text-white hover:bg-green-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('APPROVED')">
            Approve
          </button>
        <button v-show="route.query.is_approval" class="mx-1 bg-rose-500 text-white hover:bg-rose-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('REJECTED')">
            Reject
          </button>
        <button v-show="route.query.is_approval" class="mx-1 bg-amber-500 text-white hover:bg-amber-600 rounded-[4px] px-[36.5px] py-[5px]" @click="onProcess('REVISED')">
            Revise
          </button>
      </div>
    </div>

  </div>
</div>
@endverbatim
@endif