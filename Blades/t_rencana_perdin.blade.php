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
    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.t_perdin_id"
        @input="v=>values.t_perdin_id=v" :errorText="formErrors.t_perdin_id?'failed':''" :hints="formErrors.t_perdin_id"
        valueField="id" displayField="tugas" :api="{
                url: `${store.server.url_backend}/operation/t_perdin`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Perdin" label="Perjalanan Dinas" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full !mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.m_kary_id"
        @input="v=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
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
      <FieldNumber class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.total_biaya"
        :errorText="formErrors.total_biaya?'failed':''" @input="v=>values.total_biaya=v" :hints="formErrors.total_biaya"
        placeholder="Tuliskan Total Biaya" label="Total Biaya" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: true }" :value="values.status" :errorText="formErrors.status?'failed':''"
        @input="v=>values.status=v" :hints="formErrors.status" placeholder="Tuliskan Status" label="Status" fa-icon=""
        :check="false" />
    </div>

    <div class="col-span-3 mt-4">
      <h2 class="text-18px font-semibold">Detail Perdin</h2>

      <button v-if="actionText" title="Add Detail" @click="onDetailAdd">
        <div class="flex items-center mt-2 space-x-2" v-if="actionText">
          <div v-show=" actionText"
            class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
            <icon fa="plus" />
            Add To List
          </div>
        </div>
      </button>
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
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[8%] border bg-[#f8f8f8] border-[#CACACA]">
                  Komponen</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
                  Nominal</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                  Jumlah</td>
                <td
                  class="text-[#8f8f8f] text-[12px] text-capitalize p-2 text-center w-[10%%] border bg-[#f8f8f8] border-[#CACACA]">
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
                    :errorText="formErrors.nominal?'failed':''" @input="v => item.nominal = v"
                    :hints="formErrors.nominal" :check="false" label="" />
                </td>
                <td class="px-1 text-[12px] border border-[#CACACA]">
                  <FieldNumber :bind="{ readonly: !actionText }" class="w-full" :value="item.jumlah"
                    :errorText="formErrors.jumlah?'failed':''" @input="v => item.jumlah = v" :hints="formErrors.jumlah"
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