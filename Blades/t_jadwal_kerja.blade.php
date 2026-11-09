@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[570px] border-t-10 border-gray-500">
<!-- <div class="bg-white p-6 rounded-xl h-[570px] border-t-10 border-gray-500"> -->

  <!-- <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <div>
        <RouterLink v-if="data.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))"
          class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
          Create New
        </RouterLink>
      </div>
    </template>
  </TableApi> -->
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData('AKTIF',1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">AKTIF</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('NON AKTIF',2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">NON AKTIF</button>
      </div>
    </div>
    <div v-show="data.can_create">
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

@verbatim

<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded-2xl shadow-sm <md:w-full w-full bg-white">

      <!-- HEADER START -->
      <div class="bg-gray-500 text-white rounded-t-md py-2 px-4 mb-10">
        <div class="flex items-center">
          <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
            @click="onBack" />
          <div>
            <h1 class="text-20px font-bold">Form Jadwal Kerja</h1>
            <p class="text-gray-100">Jadwal Kerja</p>
          </div>
        </div>
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid px-6 <md:grid-cols-1 grid-cols-2 gap-2">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Nomor</label>
          <FieldX :bind="{ readonly: true }" label="" :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
            @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false" label="" placeholder="Nomor" />
        </div>

        <div>
          <label class="font-semibold">Status</label>
          <FieldSelect :bind="{ disabled: !actionText, clearable: false }" :value="values.status"
            @input="v => values.status = v" :errorText="formErrors.status ? 'failed' : ''"
            :hints="formErrors.status" valueField="name" displayField="name"
            :options="[ { id: true, name: 'AKTIF' }, { id: false, name: 'NON AKTIF' } ]" placeholder="" label=""
            :check="false" />

        </div>

        <!-- <div>
          <label class="col-span-12">SBU</label>
          <FieldSelect :bind="{ disabled: true, clearable:false }" :value="values.m_comp_id"
            @input="v=>values.m_comp_id=v" :errorText="formErrors.m_comp_id?'failed':''" :hints="formErrors.m_comp_id"
            valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_comp`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    transform:false,
                    join:false,
                    where:`this.is_active = 'true' `
                  }
              }" placeholder="" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">SUB</label>
          <FieldSelect :bind="{ disabled: true, clearable:false }" :value="values.m_subcomp_id"
            @input="v=>values.m_subcomp_id=v" :errorText="formErrors.m_subcomp_id?'failed':''"
            :hints="formErrors.m_subcomp_id" valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_subcomp`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    transform:false,
                    join:false,
                    where: `this.is_active='true' AND this.m_comp_id='${values.m_comp_id}'`

                  }
              }" placeholder="" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Branch</label>
          <FieldSelect :bind="{ disabled: true, clearable:false }" :value="values.m_branch_id"
            @input="v=>values.m_branch_id=v" :errorText="formErrors.m_branch_id?'failed':''"
            :hints="formErrors.m_branch_id" valueField="id" displayField="name" :api="{
                  url: `${store.server.url_backend}/operation/m_branch`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    transform:false,
                    join:false
                  }
              }" placeholder="" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Divisi</label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.m_divisi_id"
            @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''"
            :hints="formErrors.m_divisi_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  transform:true,
                  join:true,
                  where: `this.is_active='true' AND this.m_branch_id = ${values.m_branch_id}`,
                  selectfield:'name.value,this.id'
                }
              }" placeholder="" label="" fa-icon="" :check="false" />
        </div> -->

        <div>
          <label class="font-semibold">Keterangan</label>
          <FieldX type="textarea" :bind="{ readonly: !actionText }" label="" :value="values.keterangan"
            :errorText="formErrors.keterangan?'failed':''" @input="v=>values.keterangan=v"
            :hints="formErrors.keterangan" :check="false" label="" placeholder="keterangan" />
        </div>
      </div>

      <div class="mt-8 px-6 mb-5 font-bold text-19px">
        <h2>Detail Jadwal Kerja</h2>
      </div>

      <div class="px-6 w-full">
        <div class="mt-4">
          <table class="w-[100%] overflow-x-auto table-auto border border-[#CACACA] pt-4">
            <thead>
              <tr class="border">
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                  No</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Hari</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Tipe hari</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Jam Kerja</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Start</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  End</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Catatan</td>
                <td
                  class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
                <td class="text-[12px] text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>
                <td class="text-[12px] text-center border border-[#CACACA]">
                  {{item.day}}
                </td>
                <td>
                  <FieldSelect :bind="{ disabled: !actionText, clearable: false }" :value="item.tipe_hari"
                    @input="v => {
                      item.tipe_hari = v;
                      if (v === 'LIBUR') {
                        item.m_jam_kerja_id = null;
                        item.waktu_mulai = '00:00';
                        item.waktu_akhir = '00:00';
                      }
                    }" :errorText="formErrors.tipe_hari ? 'failed' : ''"
                    :hints="formErrors.tipe_hari" valueField="name" displayField="name"
                    :options="[ { id: true, name: 'KERJA' }, { id: false, name: 'LIBUR' } ]" placeholder="" label=""
                    :check="false" />
                </td>

                <td class="text-[12px] text-left border border-[#CACACA]">
                  <FieldSelect :bind="{ disabled: !actionText || item.tipe_hari === 'LIBUR', clearable: true }" :value="item.m_jam_kerja_id"
                    @input="v=>item.m_jam_kerja_id=v" :errorText="formErrors.m_jam_kerja_id?'failed':''"
                    :hints="formErrors.m_jam_kerja_id" valueField="id" displayField="desc" @update:valueFull="(response) => {
                    if (response) {
                      item.waktu_mulai = response.waktu_mulai;
                      item.waktu_akhir = response.waktu_akhir;
                    }
                    $log(response);
                    }" :api="{
                        url: `${store.server.url_backend}/operation/m_jam_kerja`,
                        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                        params: {
                          transform:false,
                          join:false,
                          selectfield:'id,waktu_akhir,waktu_mulai,this.desc',
                        }
                    }" placeholder="" label="" fa-icon="" :check="false" />
                </td>

                <td>
                  <FieldX type="time" :bind="{ readonly: !actionText || item.tipe_hari === 'LIBUR' }" :value="item.waktu_mulai"
                    :errorText="formErrors.waktu_mulai?'failed':''" @input="v=>item.waktu_mulai=v"
                    :hints="formErrors.waktu_mulai" placeholder="" label="" fa-icon="" :check="false" />
                </td>
                <td>
                  <FieldX type="time" :bind="{ readonly: !actionText || item.tipe_hari === 'LIBUR' }" :value="item.waktu_akhir"
                    :errorText="formErrors.waktu_akhir?'failed':''" @input="v=>item.waktu_akhir=v"
                    :hints="formErrors.waktu_akhir" placeholder="" label="" fa-icon="" :check="false" />
                </td>
                <td class="text-[12px] text-right border border-[#CACACA]">
                  <FieldX class="text-right" :bind="{ readonly: !actionText }" type="text" :value="item.desc"
                    :errorText="formErrors.desc?'failed':''" @input="v=>item.desc=v" :hints="formErrors.desc"
                    :check="false" placeholder="" label="" />
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
                <td colspan="7" class="py-[20px]">
                  No data to show
                </td>
              </tr>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- END TABLE DETAIL -->
      </div>

      <hr class="mt-6">

      <div class="flex px-6 justify-end mt-6 mb-4 gap-4">
        <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Batal
        </button>
        <button v-show="actionText  && (currentMenu?.can_create || currentMenu?.can_update)" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Simpan
        </button>
      </div>


      <!-- FORM END -->

    </div>

  </div>


</div>

@endverbatim
@endif