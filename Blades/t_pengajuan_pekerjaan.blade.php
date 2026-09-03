@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px] border-t-10 border-gray-500">
  <!-- <div class="flex items-center gap-x-4">
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
      <button @click="filterShowData('HALF APPROVED',5)" :class="activeBtn === 5?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">HALF APPROVED</button>
      <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      <button @click="filterShowData('APPROVED',6)" :class="activeBtn === 6?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">APPROVED</button>
      <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
      <button @click="filterShowData('REJECTED',7)" :class="activeBtn === 7?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">REJECTED</button>
    </div>
  </div> -->
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
            <h1 class="text-20px text-black font-bold">Form Pengajuan Pekerjaan </h1>
          </div>
        </div>
      </div>
      <!-- HEADER END -->

      <!-- FORM START -->
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-20">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Nomor<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldX :bind="{ readonly: true }" label="" class="w-full py-2 !mt-0" :value="values.kode"
            :errorText="formErrors.kode?'failed':''" @input="v=>values.kode=v" :hints="formErrors.kode" :check="false"
            label="" placeholder="Nomor" />
        </div>

        <div>
          <label class="col-span-12">Tanggal<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: true, disabled:true }" type='date' class="w-full py-2 !mt-0"
            :value="values.created_at" :errorText="formErrors.created_at?'failed':''" @input="v=>values.created_at=v"
            :hints="formErrors.created_at" :check="false" label="" placeholder="Pilih Tanggal" />
        </div>

        <!-- <div v-if="store.user.data.user_type.toLowerCase() === 'admin'">
          <label class="font-semibold">User<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldPopup :bind="{ readonly: !actionText, clearable:false }" class="w-full py-2 !mt-0"
            :value="values.request_id" @input="(v)=>{
              values.request_id=v
              values.m_divisi_id = null
             }" @update:valueFull="(v)=>{
              values.m_divisi_id = v.['m_divisi.id']
              $log(v)
              }" :errorText="formErrors.request_id?'failed':''" :hints="formErrors.request_id" valueField="id"
            displayField="nama_lengkap" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    searchfield:'this.id, this.nama_depan, this.nama_belakang, this.nama_lengkap, this.nik, m_divisi.nama, m_zona.nama, m_dir.nama',
                  }
                }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[
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
                field: 'nik',
                headerName: 'NIK',
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
              {
                flex: 1,
                field: 'm_zona.nama',
                headerName: 'Zona',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },              
              {
                flex: 1,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },      
              {
                flex: 1,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },                                         
            ]" />
        </div> -->

        <div>
          <label class="font-semibold">User<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldPopup :bind="{
    //readonly: store.user.data.user_type?.toLowerCase() === 'user' ? true : !actionText,
    clearable: false
  }" class="w-full py-2 !mt-0" :value="values.request_id" @input="(v)=>{
              values.request_id=v
              values.m_divisi_id = null
              values.m_branch_id = null
             }" @update:valueFull="(v)=>{
              values.m_divisi_id = v.['m_divisi_id']
              values.m_branch_id = v.['m_branch_id']
              $log(v)
              }" :errorText="formErrors.request_id?'failed':''" :hints="formErrors.request_id" valueField="id"
            displayField="nama_lengkap" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    join:false,
                    scopes: 'jabatan',
                  }
                }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[
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
                field: 'nik',
                headerName: 'NIK',
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
              {
                flex: 1,
                field: 'm_zona.nama',
                headerName: 'Zona',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },              
              {
                flex: 1,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },      
              {
                flex: 1,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },                                         
            ]" />
        </div>

        <div>
          <label class="col-span-12">Divisi User<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: true, readonly: true, clearable:false }"
            :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''"
            :hints="formErrors.m_divisi_id" valueField="id" displayField="name.value" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  scopes: 'Name'
                }
            }" placeholder="Auto Field By Karyawan" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Cabang User<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: true, readonly: true, clearable:false }"
            :value="values.m_branch_id" @input="v=>values.m_branch_id=v" :errorText="formErrors.m_branch_id?'failed':''"
            :hints="formErrors.m_branch_id" valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                }
            }" placeholder="Auto Field By Karyawan" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="font-semibold">PIC<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldPopup :bind="{
    //readonly: store.user.data.user_type?.toLowerCase() === 'user' ? true : !actionText,
    clearable: false
  }" class="w-full py-2 !mt-0" :value="values.pic_id" @input="(v)=>{
              values.pic_id=v
              values.m_divisi_pic_id = null
             }" @update:valueFull="(v)=>{
              values.m_divisi_pic_id = v.['m_divisi.id']
              $log(v)
              }" :errorText="formErrors.pic_id?'failed':''" :hints="formErrors.pic_id" valueField="id"
            displayField="nama_lengkap" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    searchfield:'this.id, this.nama_depan, this.nama_belakang, this.nama_lengkap, this.nik, m_divisi.nama, m_zona.nama, m_dir.nama',
                  }
                }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[
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
                field: 'nik',
                headerName: 'NIK',
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
              {
                flex: 1,
                field: 'm_zona.nama',
                headerName: 'Zona',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },              
              {
                flex: 1,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },
              {
                flex: 1,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },      
              {
                flex: 1,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, 
                resizable: true, 
                filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-center']
              },                                         
            ]" />
        </div>

        <div>
          <label class="col-span-12">PIC Divisi</label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: true, readonly: true, clearable:false }"
            :value="values.m_divisi_pic_id" @input="v=>values.m_divisi_pic_id=v"
            :errorText="formErrors.m_divisi_pic_id?'failed':''" :hints="formErrors.m_divisi_pic_id" valueField="id"
            displayField="name.value" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  scopes: 'Name'
                }
            }" placeholder="Auto Field By Karyawan" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Pekerjaan Sebelumnya</label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:false }"
            :value="values.pekerjaan_sebelumnya_id" @input="v=>values.pekerjaan_sebelumnya_id=v"
            :errorText="formErrors.pekerjaan_sebelumnya_id?'failed':''" :hints="formErrors.pekerjaan_sebelumnya_id" valueField="id"
            displayField="pekerjaan" :api="{
                url: `${store.server.url_backend}/operation/t_pengajuan_pekerjaan`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  //where: `this.group = 'JENIS_PEKERJAAN'`,
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Pekerjaan Sebelumnya" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Jenis Pekerjaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldSelect class="w-full py-2 !mt-0" :bind="{ disabled: !actionText, clearable:false }"
            :value="values.jenis_pekerjaan_id" @input="v=>values.jenis_pekerjaan_id=v"
            :errorText="formErrors.jenis_pekerjaan_id?'failed':''" :hints="formErrors.jenis_pekerjaan_id"
            valueField="id" displayField="value" :api="{
                url: `${store.server.url_backend}/operation/m_general`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  where: `this.group = 'JENIS_PEKERJAAN'`,
                  simplest:true,
                  transform:false,
                  join:false
                }
            }" placeholder="Pilih Jenis Pekerjaan" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Pekerjaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.pekerjaan"
            :errorText="formErrors.pekerjaan?'failed':''" @input="v=>values.pekerjaan=v" :hints="formErrors.pekerjaan"
            placeholder="Masukkan Pekerjaan" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Start<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="date" typeProps="date" class="w-full py-2 !mt-0" :bind="{ readonly: false }"
            :value="values.start_date" :errorText="formErrors.start_date?'failed':''" @input="v=>values.start_date=v"
            :hints="formErrors.start_date" placeholder="Masukkan Start " label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Deadline<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX type="date" typeProps="date" class="w-full py-2 !mt-0" :bind="{ readonly: false }"
            :value="values.deadline_date" :errorText="formErrors.deadline_date?'failed':''"
            @input="v=>values.deadline_date=v" :hints="formErrors.deadline_date" placeholder="Masukkan Deadline"
            label="" fa-icon="" :check="false" />
        </div>

        <!-- <div>
          <label class="col-span-12">Finish</label>
          <FieldX type="date" class="w-full py-2 !mt-0" :bind="{ readonly:false, disabled:false }"
            :value="values.finish_date" :errorText="formErrors.finish_date?'failed':''" @input="v=>values.finish_date=v"
            :hints="formErrors.finish_date" placeholder="Masukkan Tanggal Selesai" label="" fa-icon="" :check="false" />
        </div>
        <div>
          <label class="col-span-12">Sebelum</label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.sebelum"
            :errorText="formErrors.sebelum?'failed':''" @input="v=>values.sebelum=v" :hints="formErrors.sebelum"
            placeholder="Masukkan Text" label="" fa-icon="" :check="false" />
        </div>
        <div>
          <label class="col-span-12">Tindakan</label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.tindakan"
            :errorText="formErrors.tindakan?'failed':''" @input="v=>values.tindakan=v" :hints="formErrors.tindakan"
            placeholder="Masukkan Text" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Setelah</label>
          <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.setelah"
            :errorText="formErrors.setelah?'failed':''" @input="v=>values.setelah=v" :hints="formErrors.setelah"
            placeholder="Masukkan Text" label="" fa-icon="" :check="false" />
        </div>

        <div>
          <label class="col-span-12">Catatan</label>
          <FieldX type="textarea" class="w-full py-2 !mt-0" :bind="{ readonly: !actionText }" :value="values.keterangan"
            :errorText="formErrors.keterangan?'failed':''" @input="v=>values.keterangan=v"
            :hints="formErrors.keterangan" placeholder="Masukkan Catatan" label="" fa-icon="" :check="false" />
        </div> -->

      </div>
      <hr class="mt-10">
      <!-- FORM END -->
      <div v-show="route.query.is_approval" class="<md:col-span-1 col-span-3 grid grid-cols-2 mt-4 w-full px-4">
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
                      v-show="route.query.is_approval"
                      @click="openModal(values?.trx?.id ?? 0)"
                      class="hover:text-blue-500">
                      <icon fa="table" size="sm"/>
                      Log Approval
                    </button>
              </td>
            </tr>
          </table>
        </div>
        <div class="w-1/2 mt-3">
          <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0">*</label></label>
          <FieldX :bind="{ readonly: false }" class="w-full py-2 !mt-0" :value="values.catatan"
            :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan"
            :check="false" label="" placeholder="Tuliskan catatan" />
        </div>
      </div>
      <div class="flex justify-end gap-4">
        <button v-show="route.query.is_approval" class="mt-2 bg-green-500 text-white hover:bg-green-600 px-[36.5px] py-[12px] rounded-[6px]" @click="onProcess('approve')">
        Approve
      </button>
        <button v-show="route.query.is_approval" class="mt-2 bg-rose-500 text-white hover:bg-rose-600 px-[36.5px] py-[12px] rounded-[6px]" @click="onProcess('reject')">
        Reject
      </button>
        <button v-show="route.query.is_approval" class="mt-2 bg-amber-500 text-white hover:bg-amber-600 px-[36.5px] py-[12px] rounded-[6px]" @click="onProcess('revise')">
        Revise
      </button>
        <button  @click="onBack" class="mt-2 bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Batal
        </button>
        <button v-show="actionText" @click="onSave" class="mt-2 bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] w-32">
            Simpan
        </button>
      </div>
    </div>

  </div>


</div>

@endverbatim
@endif