<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Non Active</button>
      </div>
    </div>

    <ButtonMultiSelect title="Tambah Akses" ref="infoOutstanding" :api="{
    url: `${store.server.url_backend}/operation/m_kary`,
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      m_subcomp_id : `${data.subcomp_id}`,
      m_branch_id : `${data.branch_id}`,
      simplest: true,
      join: store.user.data.is_hc ? false : true,
      scopes: store.user.data.is_hc ? 'landing,nonos' : 'nonos,respo',
      searchfield: 'this.nama_lengkap,m_posisi.name,m_branch.name'
    }
  }" :bind="{ readonly: false, searchable: true }" valueField="id" placeholder="SO" :check="true" @add="multiCreate" :columns="[
    { headerName: 'No', valueGetter:(p)=>p.node.rowIndex + 1, width: 60, cellClass: ['justify-start', 'bg-gray-50'], searchable:true },
    { flex: 1, field: 'kode', headerName: 'Kode', cellClass: ['border-r', '!border-gray-200', 'justify-start'], searchable:true },
    { flex: 1, field: 'nama_lengkap', headerName: 'Nama', cellClass: ['border-r', '!border-gray-200', 'justify-start'], searchable:true },
    { flex: 1, field: 'm_branch.name', headerName: 'Cabang', cellClass: ['border-r', '!border-gray-200', 'justify-end'], searchable:true },
    { flex: 1, field: 'm_posisi.name', headerName: 'Posisi', cellClass: ['border-r', '!border-gray-200', 'justify-end'], searchable:true }
  ]">
      <div
        class="flex justify-center w-full h-full items-center px-2 py-1.5 text-xs rounded text-white bg-blue-500 hover:bg-blue-700 hover:bg-blue-600 transition-all duration-200">
        <icon fa="plus" size="sm mr-0.5" /> Create New
      </div>
    </ButtonMultiSelect>
  </div>

  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions"
    class="h-full">
  </TableApi>

  <div class="flex items-center gap-x-2">
  </div>
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
        <h1 class="text-20px font-bold">Form Jadwal Kerja</h1>
        <p class="text-gray-100">Jadwal Kerja</p>
      </div>
    </div>
  </div>

  <div class="p-4 grid <md:grid-cols-1 grid-cols-2 gap-2 ">

    <div>
      <label class="col-span-12">JADWAL KERJA</label>
      <FieldPopup :bind="{ readonly: !actionText }" :value="values.t_jadwal_kerja_n_id"
        @input="(v)=>values.t_jadwal_kerja_n_id=v" :errorText="formErrors.t_jadwal_kerja_n_id?'failed':''"
        :hints="formErrors.t_jadwal_kerja_n_id" valueField="id" displayField="keterangan"
        @update:valueFull="(response) =>  {
          $log(response);
          values.m_group_barang = response?.tipe;

          if (!response?.t_jadwal_kerja_det_hari || response.t_jadwal_kerja_det_hari.length === 0) {
              alert('DEBUG: Data detail hari (t_jadwal_kerja_det_hari) KOSONG atau UNDEFINED! Ini berarti file Custom.php untuk t_jadwal_kerja_n di generator belum di-update (atau jadwal ini memang tidak punya detail).');
          }

          detailArr = (response?.t_jadwal_kerja_det_hari || []).map((dt)=>({
            ...dt, 
          }));
        }" :api="{
          url: `${store.server.url_backend}/operation/t_jadwal_kerja_n`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
              simplest: true,
              transform:true,
              join:false,
              where: `this.status='AKTIF'`
          }
        }" placeholder="" label="" fa-icon="" :check="false" :columns="[{
          headerName: 'No',
          valueGetter:(p)=>p.node.rowIndex + 1,
          width: 60,
          sortable: false, resizable: false, filter: false,
          cellClass: ['justify-center', 'bg-gray-50']
        },
        {
          flex: 1,
          headerName:'Keterangan',
          field: 'keterangan',
          sortable: false, resizable: true, filter: 'ColFilter',
          cellClass: ['border-r', '!border-gray-200', 'justify-center']
        }]" />
    </div>

    <div>
      <label class="font-semibold">Status</label>
      <FieldSelect :bind="{ disabled: !actionText, clearable: false }" :value="values.status"
        @input="v => values.status = v" :errorText="formErrors.status ? 'failed' : ''" :hints="formErrors.status"
        valueField="id" displayField="name"
        :options="[ { id: 'AKTIF', name: 'Active' }, { id: 'NON AKTIF', name: 'Non Active' } ]" placeholder="" label=""
        :check="false" />
    </div>

    <div>
      <label class="col-span-12">Tanggal Mulai</label>
      <FieldX type="date" :bind="{ readonly: !actionText, disabled : !actionText }" :value="values.start_date"
        :errorText="formErrors.start_date?'failed':''" @input="v=>values.start_date=v" :hints="formErrors.start_date"
        placeholder="" label="" fa-icon="" :check="false" />
    </div>

    <div>
      <label class="col-span-12">SUB</label>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false}" :value="values.m_subcomp_id"
        @input="v=>values.m_subcomp_id=v" :errorText="formErrors.m_subcomp_id?'failed':''"
        :hints="formErrors.m_subcomp_id" valueField="id" displayField="name" :api="{
        url: `${store.server.url_backend}/operation/m_subcomp`,
        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
        params: {
          simplest:true,
          transform:false,
          join:false,
          where: `this.is_active='true'`
        }
    }" placeholder="" label="" fa-icon="" :check="false" />
    </div>

    <div>
      <label class="col-span-12">Branch</label>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.m_branch_id"
        @input="v=>values.m_branch_id=v" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id"
        valueField="id" displayField="name" :api="{
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
        @input="v=>values.m_divisi_id=v" :errorText="formErrors.m_divisi_id?'failed':''" :hints="formErrors.m_divisi_id"
        valueField="id" displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_divisi`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  transform:true,
                  join:false
                }
              }" placeholder="" label="" fa-icon="" :check="false" />
    </div>

    <div>
      <label class="col-span-12">Karyawan</label>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false,multiple:true }" :value="values.m_kary_id"
        @input="v=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
        valueField="id" displayField="nama_lengkap" :api="{
        url: `${store.server.url_backend}/operation/m_kary`,
        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
        params: {
          transform:true,
        }
        }" placeholder="" label="" fa-icon="" :check="false" />
    </div>

    <div>
      <label class="col-span-12">Deskripsi</label>
      <FieldX type="textarea" :bind="{ readonly: !actionText }" :value="values.desc"
        :errorText="formErrors.desc?'failed':''" @input="v=>values.desc=v" :hints="formErrors.desc" placeholder=""
        label="" fa-icon="" :check="false" />
    </div>

    <div class="font- text-20px col-span-2 mt-5 mb-3">
      <h2>Detail Jadwal kerja</h2>
    </div>

    <div class="col-span-2">
      <table class="w-[100%] overflow-x-auto table-auto border border-[#CACACA] pt-4">
        <thead>
          <tr class="border">
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No</td>
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Hari</td>
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Tipe hari</td>
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Jam Kerja</td>
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Start</td>
            <td class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">End</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
            <td class="text-[12px] text-center border border-[#CACACA]">{{ i + 1 }}.</td>
            <td class="text-[12px] text-center border border-[#CACACA]">{{item.day}}</td>
            <td>
              <FieldSelect :bind="{ disabled: true, clearable: false }" :value="item.tipe_hari"
                @input="v => item.tipe_hari = v" valueField="name" displayField="name"
                :options="[ { id: true, name: 'KERJA' }, { id: false, name: 'LIBUR' } ]" placeholder="" label="" :check="false" />
            </td>
            <td class="text-[12px] text-left border border-[#CACACA]">
              <FieldSelect :bind="{ disabled: true, clearable:false }" :value="item.m_jam_kerja_id"
                @input="v=>item.m_jam_kerja_id=v" valueField="id" displayField="desc" @update:valueFull="(response) => {
                    item.waktu_mulai = response.waktu_mulai
                    item.waktu_akhir = response.waktu_akhir
                    }" :api="{
                        url: `${store.server.url_backend}/operation/m_jam_kerja`,
                        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                        params: { transform:false, join:false, selectfield:'id,waktu_akhir,waktu_mulai,this.desc' }
                    }" placeholder="" label="" fa-icon="" :check="false" />
            </td>
            <td>
              <FieldX type="time" :bind="{ readonly: true }" :value="item.waktu_mulai"
                @input="v=>item.waktu_mulai=v" placeholder="" label="" fa-icon="" :check="false" />
            </td>
            <td>
              <FieldX type="time" :bind="{ readonly: true }" :value="item.waktu_akhir"
                @input="v=>item.waktu_akhir=v" placeholder="" label="" fa-icon="" :check="false" />
            </td>
          </tr>
          <tr v-else class="text-center">
            <td colspan="6" class="py-[20px]">No data to show</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex flex-row items-center justify-end space-x-2 p-2" v-show="actionText">
    <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
    <button
        class="bg-red-600 text-white font-semibold hover:bg-red-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        @click="onReset(true)">
        <icon fa="times" /> Reset
    </button>
    <button
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        @click="onSave"
        v-show="currentMenu?.can_create || currentMenu?.can_update">
        <icon fa="save" /> Simpan
    </button>
  </div>
</div>
@endverbatim
@endif
