<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="gap-x-2 flex">
        <button
    @click="filterShowData('DRAFT', 1)"
    :class="activeBtn === 1
      ? 'bg-gray-600 text-white hover:bg-gray-400'
      : 'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'"
    class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
    DRAFT
  </button>

        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>

        <button
    @click="filterShowData('POSTED', 2)"
    :class="activeBtn === 2
      ? 'bg-green-600 text-white hover:bg-green-400'
      : 'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'"
    class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
    POSTED
  </button>
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
  <TableApi v-if="landing" ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="max-h-[450px]">
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
        <h1 class="text-20px font-bold">Form Klaim Rawat Inap</h1>
        <p class="text-gray-100">Klaim Rawat Inap</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" type="text" :value="values.nomor" class="w-full mt-3"
        @input="v=>values.nomor=v" :check="false" placeholder="Auto Generate by System" label="Nomor" />
    </div>

    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.m_kary_id"
        @input="(v)=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
        valueField="id" displayField="nama_lengkap" :api="apiKary" @update:valueFull="obj => {
                if (obj) {
                  values.m_kary_id = obj.id; 
                } else {
                  detailArr = [];
                }
              }" placeholder="Pilih Karyawan" label="Karyawan" :check="false" :columns="[
            {
              headerName: 'No',
              valueGetter: (p) => p.node.rowIndex + 1,
              width: 60,
              sortable: false, 
              resizable: false, 
              filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              field: 'kode',
              headerName: 'Kode',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'nama_lengkap',
              headerName: 'Nama',
              sortable: false, 
              resizable: true, 
              filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },    
          ]" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.periode_awal"
        :errorText="formErrors.periode_awal?'failed':''" @input="v=>values.periode_awal=v"
        :hints="formErrors.periode_awal" :check="false" label="Tanggal Awal" placeholder="Masukan Tanggal Awal" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.periode_akhir"
        :errorText="formErrors.periode_akhir?'failed':''" @input="v=>values.periode_akhir=v"
        :hints="formErrors.periode_akhir" :check="false" label="Tanggal Akhir" placeholder="Masukan Tanggal Akhir" />
    </div>





    <div>
      <FieldNumber :bind="{ readonly: true }" class="w-full mt-3" :value="values.total_nominal"
        :errorText="formErrors.total_nominal?'failed':''" @input="v=>values.total_nominal=v"
        :hints="formErrors.total_nominal" :check="false" label="Nominal" placeholder="Autofield By System" />
    </div>

    <div>
      <FieldX placeholder="Masukan Status" label="Status" :bind="{ readonly: true }" type="text" :value="values.status"
        class="w-full mt-3" @input="v=>values.status=v" :check="false" />
    </div>

    <div v-if="route.query.is_approval">
      <FieldX :bind="{ readonly: false }" :value="values.catatan" :errorText="formErrors.catatan?'failed':''"
        @input="v=>values.catatan=v" :hints="formErrors.catatan" :check="false" label="Catatan Approval"
        placeholder="Tuliskan catatan Approval" />
    </div>

    <div>
      <FieldSelect class="w-full mt-3" v-if="route.query.action?.toLowerCase() === 'verifikasi'"
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

    <div class="pl-3 flex flex-col justify-center !mt-3">
      <div class="text-xs text-gray-500 mb-1">
        Status
      </div>
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">InActive</i>
        </div>
        <div class="flex-auto">
          <input
            class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] hover:cursor-pointer focus:outline-none focus:ring-0"
            type="checkbox"
            :class="{'after:bg-gray-500': values.is_active === false}"
            role="switch"
            id="is_active_for_click"
            :disabled="!actionText"
            v-model="values.is_active"
          />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">Active</i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-span-8 md:col-span-12 p-5">

    <ButtonMultiSelect v-if="actionText" title="Add Detail" @add="onDetailAdd" :api="apiRanap" :columns="[{
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
                      headerName:'Nama',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      field: 'klaim_nama',
                      cellClass: ['justify-start','!border-gray-200']
                    },
                  ]">
      <div class="flex items-center space-x-2" v-if="actionText">
        <div v-show=" actionText"
          class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
          <icon fa="plus" />
          Add To List
        </div>
      </div>
    </ButtonMultiSelect>
    <div class="mt-4">
      <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
        <thead>
          <tr class="border">
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
              No.</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[25%] border bg-[#f8f8f8] border-[#CACACA]">
              Nama</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
              Nominal</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
              Tanggal</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
              Bukti</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
              Keterangan</td>


            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
              #</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="i" class="border-t" v-if="detailArr.length > 0">
            <td class="p-2 text-center border border-[#CACACA]">
              {{ i + 1 }}.
            </td>
            <td class="text-center border border-[#CACACA]">
              {{item.klaim_nama}}
            </td>
            <td class="text-center border border-[#CACACA]">
              <FieldNumber class="mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.nominal"
                @input="(v)=>item.nominal=v" :errorText="formErrors.nominal?'failed':''" :hints="formErrors.nominal"
                placeholder="Masukkan Nominal" label="" fa-icon="" :check="false" />
            </td>

            <td class="text-center border border-[#CACACA]">
              <FieldX class="mt-0 w-full" type="date" :bind="{ readonly: !actionText }" :value="item.tanggal"
                :errorText="formErrors.tanggal?'failed':''" @input="v=>item.tanggal=v" :hints="formErrors.tanggal"
                fa-icon="" :check="false" />
            </td>
            <td class="text-center border border-[#CACACA]">
              <FieldUpload class="mt-0 w-full" :value="item.bukti" @input="(v)=>item.bukti=v" :maxSize="10"
                :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/t_klaim_ranap_d/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'bukti' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.bukti" fa-icon="upload" accept="*" :check="false" />
            </td>
            <td class="text-center border border-[#CACACA]">
              <FieldX class="mt-0 w-full" type="textarea" :bind="{ readonly: !actionText }" :value="item.keterangan"
                :errorText="formErrors.keterangan?'failed':''" @input="v=>item.keterangan=v"
                :hints="formErrors.keterangan" fa-icon="" :check="false" />
            </td>

            <td class="p-2 border border-[#CACACA]">
              <div class="flex justify-center">
                <button type="button" @click="removeDetail(i)" :disabled="!actionText">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
              </div>

            </td>
          </tr>
          <tr v-else class="text-center">
            <td colspan="10" class="py-[20px]">
              No data to show
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- FORM END -->
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
</div>
@endverbatim
@endif
