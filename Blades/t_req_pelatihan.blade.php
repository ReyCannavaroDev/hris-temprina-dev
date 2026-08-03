<!-- LANDING -->
@if(!$req->has('id'))
@verbatim
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="gap-x-2 flex">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('IN APPROVAL',3)" :class="activeBtn === 3?'bg-blue-600 text-white hover:bg-blue-400':'border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">IN APPROVAL</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('REVISED',4)" :class="activeBtn === 4?'bg-amber-600 text-white hover:bg-amber-400':'border border-amber-600 text-amber-600 bg-white  hover:bg-amber-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">REVISED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('APPROVED',6)" :class="activeBtn === 6?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">APPROVED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('REJECTED',7)" :class="activeBtn === 7?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">REJECTED</button>
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
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="max-h-[450px]">
    <!-- <template #header>
    </template> -->
  </TableApi>
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
        <h1 class="text-20px font-bold">Form Request Pelatihan</h1>
        <p class="text-gray-100">Request Pelatihan</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" type="text" :value="values.kode" class="w-full mt-3" @input="v=>values.kode=v"
        :check="false" placeholder="Auto Generate by System" label="Nomor" />
    </div>


    <!-- SBU  -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt-3" :value="values.m_comp_id"
        @input="v => {
    if (v) {
      values.m_comp_id = v;
    } else {
      values.m_comp_id = null;
      values.m_subcomp_id = null;
      values.m_branch_id = null;
      values.m_divisi_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_comp_id = obj.id;
      values.m_subcomp_id = null;
      values.m_branch_id = null;
      values.m_divisi_id = null;
    } else {
      values.m_comp_id = null;
    }
  }" :errorText="formErrors.m_comp_id ? 'failed' : ''" :hints="formErrors.m_comp_id" :check="false" label="SBU "
        placeholder="Pilih SBU " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_comp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true'`,
      transform: false
    }
  }" />

    </div>
    <!-- SUB  -->
    <div>
      <FieldSelect :bind="{
  disabled: !actionText}" class="w-full !mt-3" :value="values.m_subcomp_id" @input="v => {
    if (v) {
      values.m_subcomp_id = v;
    } else {
      values.m_subcomp_id = null;
      values.m_branch_id = null;
      values.m_divisi_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_subcomp_id = obj.id;
      values.m_branch_id = null;
      values.m_divisi_id = null;
    } else {
      values.m_subcomp_id = null;
    }
  }" :errorText="formErrors.m_subcomp_id ? 'failed' : ''" :hints="formErrors.m_subcomp_id" :check="false" label="SUB "
        placeholder="Pilih SUB " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_subcomp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true' AND this.m_comp_id='${values.m_comp_id}'`,
      transform: false
    }
  }" />

    </div>
    <!-- CABANG -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt-3" :value="values.m_branch_id"
        @input="v => {
    if (v) {
      values.m_branch_id = v;
    } else {
      values.m_branch_id = null;
      values.m_divisi_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_branch_id = obj.id;
      values.m_divisi_id = null;
    } else {
      values.m_branch_id = null;
    }
  }" :errorText="formErrors.m_branch_id ? 'failed' : ''" :hints="formErrors.m_branch_id" :check="false" label="Cabang "
        placeholder="Pilih Cabang " valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_branch`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
    params: {
      simplest: true,
      single: true,
      where: `this.is_active='true' AND this.m_subcomp_id='${values.m_subcomp_id}'`,
      transform: false
    }
  }" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText  }"
        class="w-full mt-3" :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v"
        :errorText="formErrors.m_divisi_id?'failed':''" @update:valueFull="(objVal)=>{
                  values.m_dept_id = null
                }" label="Divisi" placeholder="Pilih Divisi" :hints="formErrors.m_divisi_id" :api="{
                    url: `${store.server.url_backend}/operation/m_divisi`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      scopes:'Name',
                      //simplest:true,
                      where: `this.is_active = 'true'`
                    }
                }" valueField="id" displayField="name.value" :check="false" />
    </div>

    <div>
      <FieldSelect placeholder="Masukan Trainer" label="Trainer" :bind="{ disabled: !actionText, clearable:false }"
        class="w-full mt-3" :value="values.trainer_id" @input="v=>values.trainer_id=v"
        :errorText="formErrors.trainer_id?'failed':''" @update:valueFull="(objVal)=>{
                  $log('ini ',objVal)
                  values.m_divisi_id = null
                }" label="" placeholder="" :hints="formErrors.trainer_id" :api="{
          url: `${store.server.url_backend}/operation/m_trainer`,
          headers: { 
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          params: {
            simplest: true,
            where: `this.is_active='true'`,
            selectfield: 'id, nama_trainer  '
          }
        }" valueField="id" displayField="nama_trainer" :check="false" />
    </div>

    <div>
      <FieldSelect placeholder="Masukan Program Pelatihan" label="Program Pelatihan"
        :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.m_prog_pelatihan_id"
        @input="v=>values.m_prog_pelatihan_id=v" :errorText="formErrors.m_prog_pelatihan_id?'failed':''"
        :hints="formErrors.m_prog_pelatihan_id" @update:valueFull="(objVal)=>{
                  values.m_prog_pelatihan_id = null
                }" displayField="tema_pelatihan" :api="{
                    url: `${store.server.url_backend}/operation/m_prog_pelatihan`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      //where: `this.group='JENIS LOKER'`
                    }
              }" valueField="id" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.date_from"
        :errorText="formErrors.date_from?'failed':''" @input="v=>values.date_from=v" :hints="formErrors.date_from"
        :check="false" label="Tanggal Awal" placeholder="Masukan Tanggal Awal" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.date_to"
        :errorText="formErrors.date_to?'failed':''" @input="v=>values.date_to=v" :hints="formErrors.date_to"
        :check="false" label="Tanggal Akhir" placeholder="Masukan Tanggal Akhir" />
    </div>

    <div>
      <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3" :value="values.desc"
        :errorText="formErrors.desc?'failed':''" @input="v=>values.desc=v" :hints="formErrors.desc" :check="false"
        label="Catatan" placeholder="Masukan Catatan" />
    </div>

    <div>
      <FieldSelect class="w-full mt-3" :bind="{ disabled: !actionText, clearable:false }" :value="values.sarana"
        @input="v=>values.sarana=v" :errorText="formErrors.sarana?'failed':''" :hints="formErrors.sarana"
        valueField="value" displayField="value" :options="[
        { id: 1, value: 'ONLINE' },
        { id: 2, value: 'OFFLINE' }
      ]" placeholder="Pilih Sarana" label="Sarana" fa-icon="" :check="false" />
    </div>

    <div>
      <FieldX placeholder="Masukan Status" label="Status" :bind="{ readonly: true }" type="text" :value="values.status"
        class="w-full mt-3" @input="v=>values.status=v" :check="false" />
    </div>


    <div>
      <FieldSelect class="w-full mt-3" v-show="route.query.action?.toLowerCase() === 'verifikasi'"
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

  </div>
  <div class="col-span-8 md:col-span-12 p-5">
    <ButtonMultiSelect v-if="actionText" title="Add Detail" @add="onDetailAdd" :api="{
                    url: `${store.server.url_backend}/operation/m_kary`,
                    headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
                    params: { 
                      //where:(values.m_cat_id?`this.m_cat1_id=${values.m_cat_id}`:``),
                      //where: `this.status!='DRAFT'`,
                      scopes:'divisi',
                      notin: detailArr.length > 0 ? `this.id:${detailArr.map(dt => dt.m_kary_id).join(',')}` : null,
                      searchfield: 'this.id, this.code,this.name_short,this.name_long,type.value1'
                    },
                  onsuccess:(response) => {
                   response.data = [...response.data].map((dt) => {
                   dt['m_kary_id'] = dt['id'];
                   dt['nama_kary'] = dt['nama_lengkap'];
                   dt['divisi_kary'] = dt['m_divisi.name'];
                   dt['cabang_kary'] = dt['m_branch.name'];
                   dt['posisi_kary'] = dt['m_posisi.name'];
                   //dt['item_code'] = dt['code'];
                   //dt['m_cat_id'] = dt['m_cat1_id'];
                   //dt['m_cat'] = dt['m_cat1.name'];
                   //dt['unit_id'] = dt['unit_price_id'];
                   //dt['unit_1'] = dt['unit_price.value1'];
                   //dt['unit_2_id'] = dt['unit_2_id'];
                   //dt['unit_2'] = dt['unit_2.value1'];    
                   //dt['desc'] = ''          
                   $log(response.data)               
                   return dt;
                  });

                    response.page = response.current_page;
                    response.hasNext = response.has_next;
                    return response;
                    }

                  }" :columns="[{
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
                      headerName:'Kode Karyawan',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      field: 'kode',
                      cellClass: ['justify-start','!border-gray-200']
                    },
                    {
                      flex: 2,
                      headerName:'Nama Karyawan',
                      field: 'nama_lengkap',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      cellClass: ['justify-start','!border-gray-200']
                    },
                    {
                      flex: 2,
                      headerName:'SUB',
                      field: 'm_subcomp.name',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      cellClass: ['justify-start','!border-gray-200']
                    },
                    {
                      flex: 2,
                      headerName:'Cabang',
                      field: 'm_branch.name',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      cellClass: ['justify-start','!border-gray-200']
                    },
                    {
                      flex: 2,
                      headerName:'Divisi',
                      field: 'nama_divisi',
                      sortable: false, resizable: true, filter: false,filter:'ColFilter',
                      cellClass: ['justify-start','!border-gray-200']
                    },]">
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
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
              Nama Karyawan</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[20%] border bg-[#f8f8f8] border-[#CACACA]">
              Cabang</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12,8%] border bg-[#f8f8f8] border-[#CACACA]">
              Divisi</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12,8%] border bg-[#f8f8f8] border-[#CACACA]">
              Posisi</td>
            <td
              class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center w-[12,8%] border bg-[#f8f8f8] border-[#CACACA]">
              Aksi</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
            <td class="p-2 text-center border border-[#CACACA]">
              {{ i + 1 }}.
            </td>
            <td class="text-center border border-[#CACACA]">
              {{item.nama_kary}}
            </td>
            <td class="text-center border border-[#CACACA]">
              {{item.cabang_kary}}
            </td>
            <td class="text-center border border-[#CACACA]">
              <FieldSelect class="mt-0 w-full" :bind="{ disabled: true, clearable:false }" :value="item.divisi_kary"
                @input="v=>item.divisi_kary=v" :errorText="formErrors.divisi_kary?'failed':''"
                :hints="formErrors.divisi_kary" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      simplest:true,
                      transform:false,
                      join:false
                    }
                }" placeholder="" fa-icon="" :check="false" />
            </td>
            <td class="text-center border border-[#CACACA]">{{item.posisi_kary}}</td>
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

    <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">
      <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Kembali
          </button>
      <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="posted" class="bg-orange-500 hover:bg-orange-600 text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Posted
          </button>
      <button v-show="route.query.action?.toLowerCase() === 'verifikasi'" @click="approval" class="bg-orange-500 hover:bg-orange-600 text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Approval
          </button>
    </div>
    <!-- FORM END -->
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