<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white  hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',2)" :class="activeBtn === 2?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">POSTED</button>
      </div>
    </div>
    <div>
      <RouterLink v-if="data.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))"
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
</div>
@else

<!-- CONTENT -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md w-full p-0 bg-white border-none min-h-screen">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Mutasi</h1>
        <p class="text-gray-100">Mutasi</p>
      </div>
    </div>
  </div>
  <div class="p-3 grid <md:grid-cols-1 grid-cols-3 gap-x-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: false }" class="w-full !mt0 " :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
        @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false" label="Nomor"
        placeholder="Autofield Nomor" />
    </div>
    <!-- KARYAWAN -->
    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full !mt0 " :value="values.m_kary_id" @input="v => {
      if (v) {
        values.m_kary_id = v
      } else {
        values.m_kary_id = null
        values.status_kary_lama_id = null
        values.m_sbu_lama_id = null
        values.m_sub_lama_id = null
        values.m_branch_lama_id = null
        values.m_divisi_lama_id = null
        values.m_posisi_lama_id = null
      }
    }" @update:valueFull="obj => {
      if (obj) {
        values.status_kary_lama_id = obj.status_kary_id
        values.m_sbu_lama_id = obj['m_comp_id']
        values.m_sub_lama_id = obj['m_subcomp_id']
        values.m_branch_lama_id = obj['m_branch_id']
        values.m_divisi_lama_id = obj['m_divisi_id']
        values.m_posisi_lama_id = obj['m_posisi_id']
        values.jadwal_kerja_lama_id = obj['t_jadwal_kerja_n.id']
      } else {
        values.status_kary_lama_id = null
        values.m_sbu_lama_id = null
        values.m_sub_lama_id = null
        values.m_branch_lama_id = null
        values.m_divisi_lama_id = null
        values.m_posisi_lama_id = null
        values.jadwal_kerja_lama_id = null
      }
    }" :errorText="formErrors.m_kary_id ? 'failed' : ''" :hints="formErrors.m_kary_id" valueField="id"
        displayField="nama_lengkap" :api="{
      url: `${store.server.url_backend}/operation/m_kary`,
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        join: false,
        //scopes: 'jabatan'
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
        field: 'm_comp.name',
        headerName: 'SBU',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_subcomp.name',
        headerName: 'SUB',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_branch.name',
        headerName: 'Cabang',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_divisi.name',
        headerName: 'Divisi',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_posisi.name',
        headerName: 'Jabatan',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      }
    ]" />
    </div>

    <!-- TANGGAL -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full !mt0 " :value="values.tgl" label="Tanggal"
        placeholder="Pilih Tanggal" :errorText="formErrors.tgl?'failed':''" @input="v=>values.tgl=v"
        :hints="formErrors.tgl" :check="false" />
    </div>

    <!-- TIPE MUTASI -->
    <div>
      <FieldSelect class="w-full !mt0 " :value="values.tipe_mutasi" @input="v => values.tipe_mutasi=v"
        placeholder="Pilih Tipe Mutasi" label="Tipe Mutasi" :check="false"
        :options="['Antar SBU', 'Antar SUB', 'Antar Branch / Cabang' , 'Antar Divisi' ]" />
    </div>

    <!-- Jenis Surat -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="w-full !mt0" :value="values.jenis_surat" @input="v=>{
              if(v){
                values.jenis_surat=v
              }else{
                values.jenis_surat=null
              }
            }" @update:valueFull="obj => {
                if (obj) {
                  values.jenis_surat = obj.id; 
                } else {
                  values.jenis_surat = null;
                }
              }" :errorText="formErrors.jenis_surat ? 'failed' : ''" :hints="formErrors.is_active" label="Jenis Surat"
        placeholder="Pilih Jenis Surat" valueField="id" displayField="value" :api="{
        url: `${store.server.url_backend}/operation/m_general`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          where: `this.is_active = true AND this.group='JENIS SURAT'`
        }
      }" :check="false" />
    </div>

    <!-- STATUS KARY LAMA -->
    <div>
      <FieldSelect :bind="{ disabled: true, clearable: true }" class="w-full !mt0" :value="values.status_kary_lama_id"
        @input="v=>{
              if(v){
                values.status_kary_lama_id=v
              }else{
                values.status_kary_lama_id=null
                values.m_company_outsourcing_id=null
              }
            }" @update:valueFull="obj => {
                if (obj) {
                  values.status_kary_lama_id = obj.id; 
                  values.m_company_outsourcing_id = null; 
                } else {
                  values.status_kary_lama_id = null;
                }
              }" :errorText="formErrors.status_kary_lama_id ? 'failed' : ''" :hints="formErrors.is_active"
        label="Status Karyawan" placeholder="Pilih Status Karyawan" valueField="id" displayField="value" :api="{
        url: `${store.server.url_backend}/operation/m_general`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          where: `this.is_active = true AND this.group='STATUS KARYAWAN'`
        }
      }" :check="false" />
    </div>
    <div>
      <FieldSelect :bind="{ disabled: true, clearable:false }" :value="values.jadwal_kerja_lama_id"
        @input="v=>values.jadwal_kerja_lama_id=v" :errorText="formErrors.jadwal_kerja_lama_id?'failed':''"
        :hints="formErrors.jadwal_kerja_lama_id" valueField="id" displayField="keterangan" :api="{
            url: `${store.server.url_backend}/operation/t_jadwal_kerja_n`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              transform:false,
              join:false
            }
        }" label="Jadwal Kerja Lama" placeholder="Pilih Jadwal Kerja Lama" :check="false" />
    </div>

    <!-- SBU LAMA -->
    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0 " :value="values.m_sbu_lama_id"
        :errorText="formErrors.m_sbu_lama_id?'failed':''" @input="v=>values.m_sbu_lama_id=v"
        :hints="formErrors.m_sbu_lama_id" :check="false" label="SBU Lama" placeholder="Autofield SBU Lama"
        valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_comp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                single:true,
                where:`this.is_active='true'`,
                transform:false,
              }
          }" :check="false" />
    </div>
    <!-- SUB LAMA -->
    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0 " :value="values.m_sub_lama_id"
        :errorText="formErrors.m_sub_lama_id?'failed':''" @input="v=>values.m_sub_lama_id=v"
        :hints="formErrors.m_sub_lama_id" :check="false" label="SUB Lama" placeholder="Autofield SUB Lama"
        valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_subcomp`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                single:true,
                where:`this.is_active='true'`,
                transform:false,
              }
          }" :check="false" />
    </div>
    <!-- CABANG LAMA-->
    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0 " :value="values.m_branch_lama_id"
        :errorText="formErrors.m_branch_lama_id?'failed':''" @input="v=>values.m_branch_lama_id=v"
        :hints="formErrors.m_branch_lama_id" :check="false" label="Cabang lama" placeholder="Autofield Cabang lama"
        valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_branch`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                single:true,
                where:`this.is_active='true'`,
                transform:false,
              }
          }" :check="false" />
    </div>
    <!-- DIVISI LAMA -->
    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0 " :value="values.m_divisi_lama_id"
        :errorText="formErrors.m_divisi_lama_id?'failed':''" @input="v=>values.m_divisi_lama_id=v"
        :hints="formErrors.m_divisi_lama_id" :check="false" label="Divisi Lama" placeholder="Autofield Divisi Lama"
        valueField="id" displayField="name.value" :api="{
              url: `${store.server.url_backend}/operation/m_divisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                scopes:'Name',
                where:`this.is_active='true'`,
                transform:false,
              }
          }" :check="false" />
    </div>
    <!-- POSISI LAMA -->
    <div>
      <FieldSelect :bind="{ disabled: true }" class="w-full !mt0 " :value="values.m_posisi_lama_id"
        :errorText="formErrors.m_posisi_lama_id?'failed':''" @input="v=>values.m_posisi_lama_id=v"
        :hints="formErrors.m_posisi_lama_id" :check="false" label="Jabatan Lama" placeholder="Autofield Jabatan Lama"
        valueField="id" displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_posisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                single:true,
                where:`this.is_active='true'`,
                transform:false,
              }
          }" :check="false" />
    </div>


    <!-- STATUS KARY BARU -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="w-full !mt0" :value="values.status_kary_baru_id"
        @input="v=>{
          if(v){
            values.status_kary_baru_id=v
          }else{
            values.status_kary_baru_id=null
            values.m_company_outsourcing_id=null
          }
        }" @update:valueFull="obj => {
            if (obj) {
              values.status_kary_baru_id = obj.id; 
              values.m_company_outsourcing_id = null; 
            } else {
              values.status_kary_baru_id = null;
            }
          }" :errorText="formErrors.status_kary_baru_id ? 'failed' : ''" :hints="formErrors.is_active"
        label="Status Karyawan Baru" placeholder="Pilih Status Karyawan Baru" valueField="id" displayField="value" :api="{
    url: `${store.server.url_backend}/operation/m_general`,
    headers: {
      'Content-Type': 'Application/json',
      Authorization: `${store.user.token_type} ${store.user.token}`
    },
    params: {
      where: `this.is_active = true AND this.group='STATUS KARYAWAN'`
    }
  }" :check="false" />
    </div>

    <div>
      <FieldSelect :bind="{ disabled: !actionText, clearable:true }" :value="values.jadwal_kerja_baru_id"
        @input="v=>values.jadwal_kerja_baru_id=v" :errorText="formErrors.jadwal_kerja_baru_id?'failed':''"
        :hints="formErrors.jadwal_kerja_baru_id" valueField="id" displayField="keterangan" :api="{
            url: `${store.server.url_backend}/operation/t_jadwal_kerja_n`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
              simplest:true,
              transform:false,
              join:false
            }
        }" label="Jadwal Kerja Baru" placeholder="Pilih Jadwal Kerja Baru" :check="false" />
    </div>

    <!-- SBU BARU -->
    <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt0 " :value="values.m_sbu_baru_id" @input="v => {
    if (v) {
      values.m_sbu_baru_id = v;
    } else {
      values.m_sbu_baru_id = null;
      values.m_sub_baru_id = null;
      values.m_branch_baru_id = null;
      values.m_divisi_baru_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_sbu_baru_id = obj.id;
      values.m_sub_baru_id = null;
      values.m_branch_baru_id = null;
      values.m_divisi_baru_id = null;
    } else {
      values.m_sbu_baru_id = null;
    }
  }" :errorText="formErrors.m_sbu_baru_id ? 'failed' : ''" :hints="formErrors.m_sbu_baru_id" :check="false"
      label="SBU Baru" placeholder="Pilih SBU Baru" valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_comp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
    params: {
      simplest:true,
      single:true,
      where:`this.is_active='true'`,
      transform:false,
    }
  }" />

    <!-- SUB BARU -->
    <FieldSelect :bind="{ disabled: !actionText || !values.m_sbu_baru_id }" class="w-full !mt0 "
      :value="values.m_sub_baru_id" @input="v => {
    if (v) {
      values.m_sub_baru_id = v;
    } else {
      values.m_sub_baru_id = null;
      values.m_branch_baru_id = null;
      values.m_divisi_baru_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_sub_baru_id = obj.id;
      values.m_branch_baru_id = null;
      values.m_divisi_baru_id = null;
    } else {
      values.m_sub_baru_id = null;
    }
  }" :errorText="formErrors.m_sub_baru_id ? 'failed' : ''" :hints="formErrors.m_sub_baru_id" :check="false"
      label="SUB Baru" placeholder="Pilih SUB Baru" valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_subcomp`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
    params: {
      simplest:true,
      single:true,
      where:`this.is_active='true' AND this.m_comp_id='${values.m_sbu_baru_id}'`,
      transform:false,
    }
  }" />

    <!-- CABANG BARU -->
    <FieldSelect :bind="{ disabled: !actionText || !values.m_sub_baru_id }" class="w-full !mt0 "
      :value="values.m_branch_baru_id" @input="v => {
    if (v) {
      values.m_branch_baru_id = v;
    } else {
      values.m_branch_baru_id = null;
      values.m_divisi_baru_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_branch_baru_id = obj.id;
      values.m_divisi_baru_id = null;
    } else {
      values.m_branch_baru_id = null;
    }
  }" :errorText="formErrors.m_branch_baru_id ? 'failed' : ''" :hints="formErrors.m_branch_baru_id" :check="false"
      label="Cabang Baru" placeholder="Pilih Cabang Baru" valueField="id" displayField="name" :api="{
    url: `${store.server.url_backend}/operation/m_branch`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
    params: {
      simplest:true,
      single:true,
      where:`this.is_active='true' AND this.m_subcomp_id='${values.m_sub_baru_id}'`,
      transform:false,
    }
  }" />

    <!-- DIVISI BARU -->
    <FieldSelect :bind="{ disabled: !actionText || !values.m_branch_baru_id }" class="w-full !mt0 "
      :value="values.m_divisi_baru_id" @input="v => {
    if (v) {
      values.m_divisi_baru_id = v;
    } else {
      values.m_divisi_baru_id = null;
    }
  }" @update:valueFull="obj => {
    if (obj) {
      values.m_divisi_baru_id = obj.id;
    } else {
      values.m_divisi_baru_id = null;
    }
  }" :errorText="formErrors.m_divisi_baru_id ? 'failed' : ''" :hints="formErrors.m_divisi_baru_id" :check="false"
      label="Divisi Baru" placeholder="Pilih Divisi Baru" valueField="id" displayField="name.value" :api="{
    url: `${store.server.url_backend}/operation/m_divisi`,
    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
    params: {
      scopes: 'Name',
      where:`this.is_active='true' AND this.m_branch_id='${values.m_branch_baru_id}'`,
      transform:false,
    }
  }" />


    <!-- POSISI BARU -->
    <div>
      <FieldSelect :bind="{ disabled: !actionText }" class="w-full !mt0 " :value="values.m_posisi_baru_id"
        :errorText="formErrors.m_posisi_baru_id?'failed':''" @input="v=>values.m_posisi_baru_id=v"
        :hints="formErrors.m_posisi_baru_id" :check="false" label="Jabatan Baru" placeholder="Pilih Jabatan Baru"
        valueField="id" displayField="name" :api="{
          url: `${store.server.url_backend}/operation/m_posisi`,
          headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
          params: {
            simplest:true,
            single:true,
            where:`this.is_active='true'`,
            transform:false,
          }
      }" :check="false" />
    </div>


    <!-- NO DOKUMEN -->
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt0 " :value="values.no_dokumen"
        :errorText="formErrors.no_dokumen?'failed':''" @input="v=>values.no_dokumen=v" :hints="formErrors.no_dokumen"
        :check="false" label="Nomor Dokumen" placeholder="Tulis Nomor Dokumen" />
    </div>

    <!-- Upload Dokumen -->
    <div>
      <FieldUpload class="w-full !mt0 " :bind="{ readonly: !actionText }" :value="values.file_dokumen"
        @input="(v)=>values.file_dokumen=v" :maxSize="10"
        :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                url: `${store.server.url_backend}/operation/t_mutasi/upload`,
                headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                params: { field: 'file_dokumen' },
                onsuccess: response=>response,
                onerror:(error)=>{},
                }" :hints="formErrors.file_dokumen" label="Dokumen" placeholder="Upload Dokumen" fa-icon="upload"
        accept="application/pdf" :check="false" />
    </div>

    <div>
      <FieldX class="w-full !mt0 " :bind="{ readonly: !actionText }" :value="values.deskripsi"
        :errorText="formErrors.deskripsi?'failed':''" @input="v=>values.deskripsi=v" type="textarea"
        :hints="formErrors.deskripsi" label="Deskripsi" placeholder="Tuliskan Deskripsi" :check="false" />
    </div>


    <div>
      <FieldX class="w-full !mt0 " :bind="{ readonly: !actionText }" :value="values.keterangan"
        :errorText="formErrors.keterangan?'failed':''" @input="v=>values.keterangan=v" type="textarea"
        :hints="formErrors.keterangan" label="Keterangan" placeholder="Tuliskan Keterangan" :check="false" />
    </div>


    <div>
      <FieldX class="w-full !mt0 " :bind="{ readonly: !actionText }" :value="values.catatan"
        :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" type="textarea"
        :hints="formErrors.catatan" label="Catatan" placeholder="Tuliskan Catatan" :check="false" />
    </div>

    <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt0 " :value="values.status"
        :errorText="formErrors.status?'failed':''" @input="v=>values.status=v" :hints="formErrors.status" :check="false"
        label="Status" placeholder="Autofield Status" />
    </div>

    <!-- Tanda Tangan -->
    <div>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full !mt0 " :value="values.signature_id" @input="v => {
      if (v) {
        values.signature_id = v
      } else {
        values.signature_id = null
        //values.status_kary_lama_id = null
        //values.m_sbu_lama_id = null
        //values.m_sub_lama_id = null
        //values.m_branch_lama_id = null
        //values.m_divisi_lama_id = null
        //values.m_posisi_lama_id = null
      }
    }" @update:valueFull="obj => {
      if (obj) {
        //values.status_kary_lama_id = obj.status_kary_id
        //values.m_sbu_lama_id = obj['m_comp.id']
        //values.m_sub_lama_id = obj['m_subcomp.id']
        //values.m_branch_lama_id = obj['m_branch.id']
        //values.m_divisi_lama_id = obj['m_divisi.id']
        //values.m_posisi_lama_id = obj['m_posisi.id']
      } else {
        //values.status_kary_lama_id = null
        //values.m_sbu_lama_id = null
        //values.m_sub_lama_id = null
        //values.m_branch_lama_id = null
        //values.m_divisi_lama_id = null
        //values.m_posisi_lama_id = null
      }
    }" :errorText="formErrors.signature_id ? 'failed' : ''" :hints="formErrors.signature_id" valueField="id"
        displayField="nama_lengkap" :api="{
      url: `${store.server.url_backend}/operation/m_kary`,
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      params: {
        simplest: true,
        where: `this.is_active='true'`,
        searchfield: 'this.kode , this.nama_lengkap , m_posisi.name'
      }
    }" placeholder="Pilih Target Tanda Tangan" label="Tanda Tangan" :check="false" :columns="[
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
        field: 'm_comp.name',
        headerName: 'SBU',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_subcomp.name',
        headerName: 'SUB',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_branch.name',
        headerName: 'Cabang',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_divisi.name',
        headerName: 'Divisi',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      },
      {
        flex: 1,
        field: 'm_posisi.name',
        headerName: 'Jabatan',
        sortable: false,
        resizable: true,
        filter: 'ColFilter',
        cellClass: ['border-r', '!border-gray-200', 'justify-center']
      }
    ]" />
    </div>

     <div>
      <FieldX class="w-full !mt0 " :value="values.kompensasi"
        :errorText="formErrors.kompensasi?'failed':''" @input="v=>values.kompensasi=v" type="number"
        inputmode="numeric" :hints="formErrors.kompensasi" :check="false"
        label="kompensasi" placeholder="Kompensasi" />
    </div>

    <!-- END COLUMN -->
    <div class="col-span-full mt-6">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold text-gray-700">Poin Memperhatikan</h3>
        <button :disabled="!actionText" @click="addMemperhatikan" type="button" class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[8px] px-[15px] flex items-center justify-center space-x-2 rounded text-sm">
          <icon fa="plus" /> <span>Tambah Memperhatikan</span>
        </button>
      </div>
      <div class="mx-1 overflow-x-auto">
        <table class="w-full table-auto border border-[#CACACA]">
          <thead>
            <tr class="bg-[#f8f8f8]">
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 text-center w-[5%] border border-[#CACACA]">No.
              </td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 border border-[#CACACA]">Isi Memperhatikan</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 text-center w-[10%] border border-[#CACACA]">Aksi
              </td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in (values.t_mutasi_d_memperhatikan || [])" :key="i">
              <td class="p-2 text-center border border-[#CACACA]">{{ i + 1 }}.</td>
              <td class="p-2 border border-[#CACACA]">
                <FieldX class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.value"
                  @input="(v)=>item.value=v" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <button type="button" @click="values.t_mutasi_d_memperhatikan.splice(i, 1)" :disabled="!actionText">
                    <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!values.t_mutasi_d_memperhatikan || values.t_mutasi_d_memperhatikan.length === 0">
              <td colspan="3" class="p-4 text-center text-gray-400 italic">No data to show</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-span-full mt-6">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-bold text-gray-700">Tembusan</h3>
        <button :disabled="!actionText" @click="addTembusan" type="button" class="bg-[#005FBF] hover:bg-[#0055ab] text-white py-[8px] px-[15px] flex items-center justify-center space-x-2 rounded text-sm">
      <icon fa="plus" /> <span>Tambah Tembusan</span>
    </button>
      </div>
      <div class="mx-1 overflow-x-auto">
        <table class="w-full table-auto border border-[#CACACA]">
          <thead>
            <tr class="bg-[#f8f8f8]">
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 text-center w-[5%] border border-[#CACACA]">No.
              </td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 border border-[#CACACA]">Jabatan / Pihak Terkait
              </td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] p-2 text-center w-[10%] border border-[#CACACA]">Aksi
              </td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in (values.t_mutasi_d_tembusan || [])" :key="i">
              <td class="p-2 text-center border border-[#CACACA]">{{ i + 1 }}.</td>
              <td class="p-2 border border-[#CACACA]">
                <FieldX class="!mt-0 w-full" :bind="{ readonly: !actionText }" :value="item.value"
                  @input="(v)=>item.value=v" :check="false" />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <div class="flex justify-center">
                  <button type="button" @click="values.t_mutasi_d_tembusan.splice(i, 1)" :disabled="!actionText">
                <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                </svg>
              </button>
                </div>
              </td>
            </tr>
            <tr v-if="!values.t_mutasi_d_tembusan || values.t_mutasi_d_tembusan.length === 0">
              <td colspan="3" class="p-4 text-center text-gray-400 italic">No data to show</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- ACTION BUTTON START -->

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
        v-show="actionText  && (currentMenu?.can_create || currentMenu?.can_update)"
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>
@endverbatim
@endif