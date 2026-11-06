@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <RouterLink v-if="data?.can_create||true||store.user.data.username==='developer'"
        :to="$route.path+'/create?'+(Date.parse(new Date()))"
        v-show = "data.can_create"
        class="bg-blue-500 text-white hover:bg-blue-600 rounded-[6px] py-2 px-[12.5px]">
        <icon fa="bolt" />Generate Gaji
      </RouterLink>
    </template>
  </TableApi>
</div>
@else

@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Perhitungan Gaji
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-2">

        <div>
          <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full !mt-3" :value="values.periode_awal"
            :errorText="formErrors.periode_awal?'failed':''" :hints="formErrors.periode_awal" :check="false"
            type="date" label="Periode Awal" placeholder="Pilih Periode Awal" @input="(v)=>{
              values.periode_awal=v
              detailArr = []
            }" />
        </div>
        <div>
          <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full !mt-3" :value="values.periode_akhir"
            :errorText="formErrors.periode_akhir?'failed':''" :hints="formErrors.periode_akhir" :check="false"
            type="date" label="Periode Akhir" placeholder="Pilih Periode Akhir" @input="(v)=>{
              values.periode_akhir=v
              detailArr = []
            }" />
        </div>

        <div>
          <label class="font-semibold">SUB<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:true }" :value="values.sbu"
            :check="false" @input="(v)=>{
              //$log(v)
              values.sbu=v
              values.m_dept_id=''
              detailArr = []
              //$log(values.divisi)
            }" :errorText="formErrors.sbu?'failed':''" :hints="formErrors.sbu" displayField="name" valueField="id"
            :api="{
                url: `${store.server.url_backend}/operation/m_subcomp`,
                headers: {
                  //'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest:true,
                  single:true,
                  where:`this.is_active='true'`,
                  transform:false,
                }
            }" fa-icon="search" :check="true" />
        </div>
        <div>
          <label class="font-semibold">Cabang<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:true }"
            :value="values.m_branch_id" :check="false" @input="(v)=>{
              //$log(v)
              values.m_branch_id=v
              values.m_dept_id=''
              detailArr = []
              //$log(values.divisi)
            }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id" displayField="name"
            valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: {
                  //'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest:true,
                  single:true,
                  //where:`this.is_active='true' AND this.m_comp_id = '${values.sbu}'`,
                  transform:false,
                }
            }" fa-icon="search" :check="true" />
        </div>
        <div>
          <label class="font-semibold">Divisi<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldSelect :bind="{ disabled: !actionText, clearable:true }" :value="values.m_divisi_id"
            @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''"
            :hints="formErrors.m_divisi_id" valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  transform:true,
                  join:true,
                  //where: `this.is_active='true' AND this.m_branch_id = ${values.m_branch_id}`,
                  selectfield:'name.value,this.id'
                }
              }" placeholder="" label="" fa-icon="" :check="false" />
        </div>
        <!-- <div>
          <label class="font-semibold">Departemen<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldSelect
            class="w-full py-2 !mt-0"
            :bind="{ disabled: !actionText, clearable:true }"
            :value="values.m_dept_id" 
            :check="false"
            @input="(v)=>{
              //$log(v)
              values.m_dept_id=v
              detailArr = []
              //$log(values.departemen)
            }"
            :errorText="formErrors.m_dept_id?'failed':''"
            :hints="formErrors.m_dept_id"
            displayField="nama"
            valueField="id"
            :api="{
                url: `${store.server.url_backend}/operation/m_dept`,
                headers: {
                  //'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest:true,
                  single:true,
                  scopes: 'filterDivisi',
                  divisi_id: values.m_divisi_id ?? null,
                  transform:false,
                }
            }"
            fa-icon="search" :check="true" />
        </div> -->
        <!-- <div class="flex flex-col gap-2">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer font-semibold"
            for="is_tunjangan_for_click"
            >Tunjangan & Potongan :</label
          >
          <div class="flex w-40">
            <div class="flex-auto">
              <i class="text-red-500">Tidak</i>
            </div>
            <div class="flex-auto">
              <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.is_tunjangan === false}"
                role="switch"
                id="is_tunjangan_for_click"
                :disabled="!actionText"
                v-model="values.is_tunjangan"
                />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Iya</i>
            </div>
          </div>
        </div> -->
      </div>

      <div class="flex flex-row justify-center space-x-[10px] mt-[1em]">
        <button @click="generatePerhitungan" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-[18px] py-[8px] rounded-[4px] ">
          <Icon fa="bolt"/> Generate
        </button>
        <button @click="detailArr = []" class="bg-[#EF4444] hover:bg-[#ed3232] text-white text-sm px-[18px] py-[8px] rounded-[4px] ">
          Hapus Detail
        </button>
      </div>

      <div class="mt-4">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA] " style="zoom: 80%">
          <thead>
            <tr class="border">
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                No.</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">
                Periode</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                NIK</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
                Karyawan</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                Divisi</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">
                Posisi</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
                Gaji Bersih</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">
                Deskripsi</td>
              <td
                class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                Aksi</td>
            </tr>
          </thead>
          <tbody>
            <tr v-if="detailArr.length" v-for="(item, i) in detailArr" :key="item.id"
              class="border-t hover:bg-yellow-200">
              <td class="text-center border border-[#CACACA] px-2">
                {{ i + 1 }}.
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item.periode }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary.nik'] }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item.karyawan }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary_divisi.name'] }}
              </td>
              <td class="text-left border border-[#CACACA] px-2">
                {{ item['m_kary_posisi.name'] }}
              </td>
              <td class="text-right border border-[#CACACA] px-2">
                {{ formatRupiah(item.netto) }}
              </td>
              <td class="border border-[#CACACA]">
                <FieldX :bind="{ readonly: !actionText}" class="!mt-0" :value="item.deskripsi"
                  @input="v=>item.deskripsi=v" type="textarea" label="" :check="false" />
              </td>
              <td class="border border-[#CACACA]">
                <div class="flex justify-center">
                  <button v-show="actionText" @click="openDetail(i)" class="rounded-lg flex items-center justify-center">
                      <icon fa="circle-info" size="lg">
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

      <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
        <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
          Batal
        </button>
        <button v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update) " @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
@endverbatim
@endif

@verbatim
<!-- Modal Content -->
<div
    v-show="modalOpen"
    class="fixed inset-0 z-[9999] flex items-center justify-center"
  >
    <!-- OVERLAY -->
    <div
      class="absolute inset-0 bg-black/50"
      @click="closeModal"
    ></div>

    <!-- CONTAINER -->
    <div
      class="relative bg-white w-[70%] max-w-[900px] mx-auto rounded shadow-lg z-10 flex flex-col max-h-[90vh]"
    >
      <!-- HEADER -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-xl font-semibold">
          Rincian Gaji {{ titleOpen }}
        </h3>
      </div>

      <!-- BODY -->
      <div class="p-4 overflow-y-auto">
        <table class="w-full table-auto border border-[#CACACA]">
          <thead>
            <tr>
              <td class="font-bold p-2 text-left border bg-gray-800 text-white">
                Komponen
              </td>
              <td class="font-bold p-2 text-right border bg-gray-800 text-white">
                Besaran
              </td>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(a, i) in detailArrOpen.items"
              :key="i"
            >
              <td
                class="text-left border p-2"
                :class="a.factor == '=' ? 'font-bold bg-gray-200' : ''"
              >
                {{ a.label }}
              </td>

              <td
                class="text-right border p-2"
                :class="a.factor == '=' ? 'font-bold bg-gray-200' : ''"
              >
                {{ (a.factor == '-' ? '(-) ' : '') + formatRupiah(a.value) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- FOOTER -->
      <div class="flex justify-center p-4 border-t">
        <button
          @click="closeModal"
          class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>

@endverbatim