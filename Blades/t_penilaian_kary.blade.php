<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-amber-600 text-white hover:bg-amber-400':'border border-amber-600 text-amber-600 bg-white  hover:bg-amber-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
      </div>
    </div>


    <div v-if="data?.can_create">
       <RouterLink :to="$route.path + '/create?' + Date.now()" class="border border-[#428BCA] font-semibold text-[#428BCA] bg-white hover:bg-[#428BCA] hover:text-white 
        duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
      Tambah Baru
    </RouterLink>
    </div>
  </div>

  <!-- Filter Divisi -->
  <div class="px-2.5 py-1 mb-2 w-1/3">
    <FieldSelect class="w-full" :bind="{ clearable: true }" :value="data.filter_divisi_id"
      @input="v => { data.filter_divisi_id = v; onFilterDivisiChange() }" valueField="id" displayField="name" :api="{
            url: `${store.server.url_backend}/operation/m_divisi`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest: true,
              transform: false,
              join: false
            }
        }" placeholder="Filter Berdasarkan Divisi" label="Divisi" fa-icon="building" :check="false" />
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
<div class="flex flex-col position: sticky border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Penilaian</h1>
        <p class="text-gray-100">Master Penilaian</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <div>
      <FieldPopup
      class="w-full col-span-9 !mt-3"
        :bind="{ readonly: !actionText }"
        :value="values.m_kary_id" @input="(v)=>values.m_kary_id=v"
        @update:valueFull="(response)=>{
          if(response) {
            values.nama_jabatan = response['m_posisi.name'] ?? response['m_posisi.name'];
            onKaryawanSelected(response);
          } else {
            values.nama_jabatan = '';
          }
        }"
        :errorText="formErrors.m_kary_id?'failed':''" 
        :hints="formErrors.m_kary_id" 
        valueField="id" displayField="nama_lengkap"
        label="Karyawan" placeholder="Pilih Karyawan" 
        :api="{
           url:  `${store.server.url_backend}/operation/m_kary`,
              headers: {
                'Content-Type': 'Application/json',
                Authorization: `${store.user.token_type} ${store.user.token}`
              },
              params: {
                kary_id : `${store.user.data.m_kary_id ?? 0}`,
                m_subcomp_id:`${values.m_subcomp_id}`,
                m_branch_id:`${values.m_branch_id}`,
                scopes :'bawahan,nonos,respo,divisi',
                searchfield: 'this.kode,this.nama_lengkap,atasan.nama_lengkap,m_posisi.name'
              }
        }"
        placeholder="" fa-icon="" :check="false" 
        :columns="[{
          headerName: 'No',
          valueGetter:(p)=>p.node.rowIndex + 1,
          width: 60,
          sortable: false, resizable: false, filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
         {
              flex: 1,
              field: 'kode',
              headerName: 'Kode',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'nama_lengkap',
              headerName: 'Nama',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'm_subcomp.name',
              headerName: 'SUB',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-end']
            },
            {
              flex: 1,
              field: 'm_branch.name',
              headerName: 'Branch',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-end']
            },
             {
              flex: 1,
              field: 'nama_divisi',
              headerName: 'Divisi',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-end']
            },
            {
              flex: 1,
              field: 'm_posisi.name',
              headerName: 'Posisi',
              sortable: false, resizable: true, filter: false,
              cellClass: ['border-r', '!border-gray-200', 'justify-end']
            },]"
      />
      
    </div>

    <div>
      <FieldX class="w-full !mt-3" :bind="{ readonly: true }" :value="values.nama_jabatan"
        :errorText="formErrors.nama_jabatan?'failed':''" @input="v=>values.nama_jabatan=v" :hints="formErrors.nama_jabatan"
        placeholder="Jabatan Terisi Otomatis" label="Jabatan Karyawan" fa-icon="user-tie" :check="false" />
    </div>

    <div>
      <FieldX type="date" class="w-full !mt-3" :bind="{ readonly: !actionText }" :value="values.tanggal"
        :errorText="formErrors.tanggal ? 'failed' : ''" @input="v => values.tanggal = v" :options="yearOptions"
        :key="'value'" :displayField="'label'" :hints="formErrors.tanggal" label="Periode" placeholder="Pilih Periode"
        :check="true" />
    </div>

    <div>
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled:true, clearable:true }" :value="values.atasan_id"
        @input="v=>values.atasan_id=v" :errorText="formErrors.atasan_id?'failed':''" :hints="formErrors.atasan_id"
        valueField="id" displayField="nama_depan" :api="{
              url: `${store.server.url_backend}/operation/m_kary`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                //where: `this.group = 'TIPE PURCHASE REQUEST'`,
                simplest:true,
                transform:false,
                join:false
              }
          }" fa-icon="caret-down" label="Atasan" placeholder="tulis" :check="false" />
    </div>
    <div>
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled: true, clearable:true }"
        :value="values.tipe_penilaian" @input="v=>values.tipe_penilaian=v"
        :errorText="formErrors.tipe_penilaian?'failed':''" :hints="formErrors.tipe_penilaian" valueField="value"
        displayField="value" :api="{
              url: `${store.server.url_backend}/operation/m_general`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                where: `this.group = 'TIPE_PENILAIAN'`,
                simplest:true,
                transform:false,
                join:false
              }
          }" fa-icon="caret-down" label="Tipe Penilaian (Terisi Otomatis)" placeholder="Pilih Tipe Penilaian" :check="false" />
    </div>

    <div>
      <FieldSelect class="w-full col-span-9 !mt-3" :bind="{ disabled: actionText !== 'Tambah' , clearable:true }"
        :value="values.m_assessment_kary_id" @input="v=>values.m_assessment_kary_id=v"
        :errorText="formErrors.m_assessment_kary_id?'failed':''" :hints="formErrors.m_assessment_kary_id"
        valueField="id" displayField="deskripsi" @update:valueFull="(response)=>{
          onTipePenilaianSelected(response.id); 
          if(response && response['type.value']) {
            values.tipe_penilaian = response['type.value'];
          }
        }" :api="{
    url: `${store.server.url_backend}/operation/m_assessment_kary`,
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: isRead ? {} : { scopes: 'forKaryawan', karyawan_id: `${values.m_kary_id || 0}`, transform: true }
    }" fa-icon="caret-down" label="Penilaian" placeholder="tulis" :check="false" />
    </div>

    <!-- Penilaian -->
    <div>
      <FieldX :bind="{ readonly: true }" class="w-full mt-3" :value="values.penilaian" label="Penilaian"
        placeholder="Tuliskan Penilaian" :errorText="formErrors.penilaian ? 'failed' : ''"
        @input="v => values.penilaian = v" :hints="formErrors.penilaian" :check="false" />
    </div>
  </div>

  <div class="p-6">

    <div class="flex justify-between mb-4">
      <div>
        <h2 class="text-xl font-semibold">Penilaian</h2>
      </div>
      <div class="flex items-center gap-2 text-right">
        <div>
          <div class="text-sm font-semibold text-blue-600 leading-4">
            {{ values.nama }}
          </div>
          <div class="text-[10px] text-gray-500 leading-3">
            {{ values.Jabatan }} jabatan sementara
          </div>
        </div>
        <div class="px-2 py-1 text-sm bg-blue-50 border border-blue-300 rounded text-blue-600">
          {{ values.rata_rata || '0.00' }}
        </div>
      </div>
    </div>


    <!-- Main Table -->
    <div v-for="(item, index) in detailArr" :key="`assessment-${index}`" class="mb-6 border rounded shadow">

      <div class="flex justify-between items-center text-white bg-[#128AE9] px-4 py-2 rounded-t">
        <div>
          <h2 class="font-semibold">{{ item.nama_kategori }}</h2>
        </div>
        <div class="flex space-x-2 items-center">
          <h2 class="font-semibold">Total Nilai</h2>
          <h2
            class="w-6 h-6 flex items-center justify-center font-semibold rounded-full bg-white text-[#128AE9] text-xs">
            {{ item.total_nilai ?? 0 }}
          </h2>
        </div>
      </div>

      <div class="flex bg-[#F6F7FF] justify-between py-2 px-4">
        <div class="text-[#128AE9]">
          {{item.nama_assessment??'-'}}
        </div>
        <div class="flex items-center space-x-3">
          <div class="flex items-center space-x-1">
            <h2 class="">Bobot</h2>
            <h2 class="w-6 h-6 flex items-center justify-center  rounded-full bg-[#49525D] text-white text-xs">
              {{item.bobot}}</h2>
          </div>
          <div class="flex items-center space-x-1">
            <h2 class="">Rating</h2>
            <h2 class="w-6 h-6 flex items-center justify-center  rounded-full bg-[#49525D] text-white text-xs">4</h2>
          </div>
          <div class="flex items-center space-x-1">
            <h2 class="">Nilai</h2>
            <h2
              class="w-6 h-6 flex items-center justify-center font-semibold rounded-full bg-[#49525D] text-white text-xs">
              {{ item.total_nilai ?? 0 }}
            </h2>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-5 text-xs text-center">
        <div v-for="(sub, subIndex) in item.t_assessment_kary_sub_d" :key="`sub-${index}-${subIndex}`"
          class="relative border pt-3 min-h-[100px] bg-white flex flex-col justify-between"
          :class="{ 'bg-blue-50 border-blue-500': sub.is_selected }">
          <div class="flex-1 flex items-start justify-start px-4 text-[12px] text-[#333333] text-left min-h-24">
            {{ sub.nama_keterangan }}
          </div>
          <div class="flex justify-between items-center mt-2 px-4 border-t py-2">
            <span class="text-[11px] font-semibold">{{sub.nilai }}</span>
            <input
              type="radio"
              :name="'group-' + index"
              :value="sub.nilai"
              v-model="selectedSeq[index]"
              class="appearance-none w-4 h-4 border border-gray-400 rounded-sm checked:bg-blue-600 checked:border-blue-600 cursor-pointer"
            />
          </div>
        </div>
      </div>

    </div>


    <div v-if="values.m_assessment_kary_id" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-6.8">Catatan Manager / OM</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_1"
          @input="v => values.catatan_1 = v" :errorText="formErrors.catatan_1 ? 'failed' : ''"
          :hints="formErrors.catatan_1" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-6.8">Catatan Departemen HRD</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_2"
          @input="v => values.catatan_2 = v" :errorText="formErrors.catatan_2 ? 'failed' : ''"
          :hints="formErrors.catatan_2" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-4">Komentar atasan atas kelebihan & kekurangan karyawan
        </div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_3"
          @input="v => values.catatan_3 = v" :errorText="formErrors.catatan_3 ? 'failed' : ''"
          :hints="formErrors.catatan_3" :check="false" />
      </div>

      <div class="flex flex-col h-full">
        <div class="text-[11px] font-medium text-gray-700 mb-4">Pelatihan & pengembangan yang diusulkan untuk
          meningkatkan prestasi kerja</div>
        <FieldX class="flex-1 min-h-[140px]" type="textarea" :bind="{ readonly: !actionText }" :value="values.catatan_4"
          @input="v => values.catatan_4 = v" :errorText="formErrors.catatan_4 ? 'failed' : ''"
          :hints="formErrors.catatan_4" :check="false" />
      </div>
    </div>



  </div>





  <div class="flex flex-row items-center justify-end space-x-2 p-2">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
    <button
        class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        @click="onReset(true)"
      >
        <icon fa="times" />
        Reset
      </button>
    <button
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="currentMenu?.can_create || currentMenu?.can_update"
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>
@endverbatim
@endif