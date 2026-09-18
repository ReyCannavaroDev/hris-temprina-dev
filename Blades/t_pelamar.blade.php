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
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('PROSES',3)" :class="activeBtn === 3?'bg-blue-600 text-white hover:bg-blue-400':'border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">PROSES</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('DITERIMA',4)" :class="activeBtn === 4?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DITERIMA</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('DITOLAK',5)" :class="activeBtn === 5?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">DITOLAK</button>
      </div>
    </div>
    <div class="flex gap-2">
      <RouterLink v-if="currentMenu?.can_create || data?.can_create" :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>

      <label
      for="fileUpload"
      v-if="currentMenu?.can_create || data?.can_create"
      class="cursor-pointer border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2"
      >
      Upload File
    </label>
      <input
      id="fileUpload"
      type="file"
      class="sr-only"

      @change="(e) => uploadFile(e.target.files[0])"
    />
    </div>

  </div>
  <hr>
  <TableApi v-if="landing" ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="h-full">
    <!-- <template #header>
    </template> -->
  </TableApi>
</div>
@else
@verbatim

<div class="flex flex-col gap-y-2 scroll-auto">
  <div class="flex gap-x-1 px-2">
    <div class="flex flex-col border rounded shadow-sm <md:w-full w-full bg-white ">

      <!-- HEADER START -->
      <div class="bg-gray-500 text-white rounded-t-md py-2 px-4 flex justify-between items-center">
        <div class="flex items-center">
          <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
            @click="onBack" />
          <div>
            <h1 class="text-20px font-bold">Form Pelamar</h1>
            <p class="text-gray-100">Master Pelamar</p>
          </div>
        </div>
        <div class="flex items-center gap-2" v-if="actionText">
          <label
            for="fileUploadCv"
            class="cursor-pointer flex items-center gap-1.5 border border-yellow-400 bg-yellow-500 text-white hover:bg-yellow-600 text-xs font-semibold py-1.5 px-3 rounded shadow transition duration-200"
            title="Unggah berkas CV (.pdf atau .docx) untuk mengisi formulir otomatis"
          >
            <i class="fa fa-bolt"></i>
            <span>Upload CV</span>
          </label>
          <input
            id="fileUploadCv"
            ref="inputCvFile"
            type="file"
            accept=".pdf,.docx,.doc"
            class="sr-only"
            @change="(e) => handleCvUpload(e.target.files[0])"
          />
        </div>
      </div>
      <!-- HEADER END -->
      <div class="flex px-6 items-stretch w-full text-sm overflow-x-auto">
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 0}"
            @click="activeTabIndex = 0"
          >
            Informasi
          </button>
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 6}"
            @click="activeTabIndex = 6"
          >
            Dokumen
          </button>
        <!-- <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 9}"
            @click="activeTabIndex = 9"
          >
            Lokasi
          </button> -->
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 1}"
            @click="activeTabIndex = 1"
          >
            Pendidikan
          </button>
        <button
            class="block w-full flex items-center justify-center border-b-2 hover:text-yellow-500 hover:text-yellow-500 duration-300 border-gray-100 p-3"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 2}"
            @click="activeTabIndex = 2"
          >
            Keluarga
          </button>
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 3}"
            @click="activeTabIndex = 3"
          >
            Pelatihan
          </button>
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 4}"
            @click="activeTabIndex = 4"
          >
            Prestasi
          </button>
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 5}"
            @click="activeTabIndex = 5"
          >
            Organisasi
          </button>
        <!-- <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 6}"
            @click="activeTabIndex = 6"
          >
            Bahasa
          </button> -->
        <button
            class="block w-full flex items-center justify-center border-b-2 border-gray-100 p-3 hover:text-yellow-500 hover:text-yellow-500 duration-300"
            :class="{'border-yellow-500 text-yellow-500 font-bold': activeTabIndex === 7}"
            @click="activeTabIndex = 7"
          >
            Pengalaman Kerja
          </button>
      </div>
      <div v-show="activeTabIndex === 0">
        <!-- Pilihan Lowongan Pekerjaan yang dilamar -->
        <div class="p-4 pb-0" v-show="activeTabIndex === 0">
          <FieldPopup
            class="w-full !mt-0"
            :bind="{ readonly: !actionText }"
            :value="values.t_loker_id"
            @input="v => values.t_loker_id = v"
            :errorText="formErrors.t_loker_id ? 'failed' : ''"
            :hints="formErrors.t_loker_id"
            valueField="id"
            displayField="title"
            :api="{
              url: `${store.server.url_backend}/operation/t_loker`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest: true,
                join: true,
                transform: true,
                where: `this.status = 'OPEN'`
              }
            }"
            placeholder="Pilih Lowongan Pekerjaan yang Dilamar"
            label="Lowongan Pekerjaan yang Dilamar"
            :check="false"
            :columns="[
              { headerName: 'No', valueGetter: (params) => params.node.rowIndex + 1, width: 60 },
              { headerName: 'Kode Loker', field: 'nomor', width: 170 },
              { headerName: 'Nama Lowongan', field: 'title', flex: 1 },
              { headerName: 'Posisi', field: 'm_posisi.name', width: 160 },
              { headerName: 'Divisi', field: 'm_divisi.nama', width: 150 },
              { headerName: 'Status', field: 'status', width: 110 }
            ]"
          />
        </div>

        <!-- Data Pelamar -->
        <h2 class="font-bold mt-5 text-[18px] px-6" v-show="activeTabIndex === 0">Data Pelamar</h2>
        <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
          <div class="flex">
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_depan" label="Nama Depan"
              placeholder="Tuliskan Nama Depan" :errorText="formErrors.nama_depan?'failed':''"
              @input="v=>values.nama_depan=v" :hints="formErrors.nama_depan" :check="false" />
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_belakang"
              label="Nama Belakang" placeholder="Tuliskan Nama Belakang"
              :errorText="formErrors.nama_belakang?'failed':''" @input="v=>values.nama_belakang=v"
              :hints="formErrors.nama_belakang" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.jk_id"
              label="Jenis Kelamin" placeholder="Pilih Jenis Kelamin" @input="v=>values.jk_id=v"
              :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id" valueField="id" displayField="value"
              :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
                    join: true,
                    selectfield: 'this.id, this.code, this.value, this.is_active'
                  }
                }" :check="false" />

          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_panggilan"
              label="Nama Panggilan" placeholder="Tuliskan Nama Panggilan Pelamar"
              :errorText="formErrors.nama_panggilan?'failed':''" @input="v=>values.nama_panggilan=v"
              :hints="formErrors.nama_panggilan" :check="false" />



          </div>
          <div class="flex">
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.tempat_lahir"
              label="Tempat Lahir" placeholder="Tuliskan Tempat Lahir" :errorText="formErrors.tempat_lahir?'failed':''"
              @input="v=>values.tempat_lahir=v" :hints="formErrors.tempat_lahir" :check="false" />
            <!-- <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.tempat_lahir" @input="v=>values.tempat_lahir=v"
              :errorText="formErrors.tempat_lahir?'failed':''" :hints="formErrors.tempat_lahir" label="Tempat Lahir"
              placeholder="Tempat Lahir" valueField="value" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" /> -->
            <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.tgl_lahir"
              label="Tanggal Lahir" placeholder="Pilih Tanggal Lahir" :errorText="formErrors.tgl_lahir?'failed':''"
              @input="v=>values.tgl_lahir=v" :hints="formErrors.tgl_lahir" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3"
              :value="values.alamat_domisili" label="Alamat" placeholder="Tuliskan Alamat"
              :errorText="formErrors.alamat_domisili?'failed':''" @input="v=>values.alamat_domisili=v"
              :hints="formErrors.alamat_domisili" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.provinsi_id" @input="v=>values.provinsi_id=v"
              :errorText="formErrors.provinsi_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kota_id = '',
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.provinsi_id" label="Provinsi" placeholder="Pilih Provinsi" valueField="id"
              displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genProvinsi',
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.kota_id"
              @input="v=>values.kota_id=v" :errorText="formErrors.kota_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kecamatan_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.kota_id" label="Kota" placeholder="Pilih Kota" valueField="id"
              displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKota',
                      provinsi_id: values.provinsi_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.kecamatan_id" @input="v=>values.kecamatan_id=v"
              :errorText="formErrors.kecamatan_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kode_pos = ''
                  }" :hints="formErrors.kecamatan_id" label="Kecamatan" placeholder="Pilih Kecamatan" valueField="id"
              displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKecamatan',
                      kota_id: values.kota_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.kode_pos"
              label="Kode Pos" placeholder="Tuliskan Kode Pos" :errorText="formErrors.kode_pos?'failed':''"
              @input="v=>values.kode_pos=v" :hints="formErrors.kode_pos" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.telp"
              label="Nomer Telepon" placeholder="Tuliskan Nomer Telepon" :errorText="formErrors.telp?'failed':''"
              @input="v=>values.telp=v" :hints="formErrors.telp" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_tlp_lainnya"
              label="No Telepon Lainya" placeholder="Tuliskan Nomer Telepon Lainnya"
              :errorText="formErrors.no_tlp_lainnya?'failed':''" @input="v=>values.no_tlp_lainnya=v"
              :hints="formErrors.no_tlp_lainnya" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.no_darurat"
              label="No Telepon Darurat" placeholder="Tuliskan Nomer Telepon Darurat"
              :errorText="formErrors.no_darurat?'failed':''" @input="v=>values.no_darurat=v"
              :hints="formErrors.no_darurat" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.nama_kontak_darurat"
              label="Nama Kontak Darurat" placeholder="Tuliskan Nama Kontak Darurat"
              :errorText="formErrors.nama_kontak_darurat?'failed':''" @input="v=>values.nama_kontak_darurat=v"
              :hints="formErrors.nama_kontak_darurat" :check="false" />
          </div>
          <div>
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.hub_dgn_karyawan"
              label="Hubungan Dengan Pelamar" placeholder="Tulis Hubungan Dengan Pelamar"
              :errorText="formErrors.hub_dgn_karyawan?'failed':''" @input="v=>values.hub_dgn_karyawan=v"
              :hints="formErrors.hub_dgn_karyawan" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.agama_id"
              @input="v=>values.agama_id=v" :errorText="formErrors.agama_id?'failed':''" :hints="formErrors.agama_id"
              label="Agama" placeholder="Pilih Agama" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='AGAMA' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.gol_darah_id" @input="v=>values.gol_darah_id=v"
              :errorText="formErrors.gol_darah_id?'failed':''" :hints="formErrors.gol_darah_id" label="Golongan Darah"
              placeholder="Pilih Golongan Darah" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='GOLONGAN DARAH' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

          </div>
          <div>

            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.status_nikah_id" @input="v=>values.status_nikah_id=v"
              :errorText="formErrors.status_nikah_id?'failed':''" :hints="formErrors.status_nikah_id"
              label="Status Pernikahan" placeholder="Pilih Status Pernikahan" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='STATUS NIKAH' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.tanggungan_id" @input="v=>values.tanggungan_id=v"
              :errorText="formErrors.tanggungan_id?'failed':''" :hints="formErrors.tanggungan_id" label="Tanggungan"
              placeholder="Pilih Tanggungan" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='TANGGUNGAN' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
          </div>
          <div>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="values.status"
              @input="v => values.status = v" :errorText="formErrors.status ? 'failed' : ''" :hints="formErrors.status"
              valueField="id" displayField="key" :options="[
    { id: 'aktif', key: 'Aktif' },
    { id: 'blacklist', key: 'Blacklist' }
  ]" placeholder="Pilih Status" fa-icon="" :check="false" />
          </div>
          <!-- <div>

            <FieldX :bind="{ readonly: !actionText }" label="Limit Potong" type="number" class="w-full mt-3"
              :value="values.limit_potong?.toString()" :errorText="formErrors.limit_potong?'failed':''"
              @input="v=>values.limit_potong=v" :hints="formErrors.limit_potong" label="Limit Potong"
              placeholder="Limit Potong" :check="false" />

          </div> -->
        </div>
        <!-- Sosial Media -->
        <h2 class="font-bold text-[18px] px-6" v-show="activeTabIndex === 0">Media Sosial</h2>
        <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">
          <div>

            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.email" label="Email"
              placeholder="Email" :errorText="formErrors.email?'failed':''" @input="v=>values.email=v"
              :hints="formErrors.email" :check="false" />

          </div>
          <div>

            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.linkedin" label="LinkedIn"
              placeholder="Linked In" :errorText="formErrors.linkedin?'failed':''" @input="v=>values.linkedin=v"
              :hints="formErrors.linkedin" :check="false" />

          </div>
          <div>

            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.ig" label="Instagram"
              placeholder="Tuliskan Instagram" :errorText="formErrors.ig?'failed':''" @input="v=>values.ig=v"
              :hints="formErrors.ig" :check="false" />

          </div>
          <div>

            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.facebook" label="Facebook"
              placeholder="Tuliskan Facebook" :errorText="formErrors.facebook?'failed':''" @input="v=>values.facebook=v"
              :hints="formErrors.facebook" :check="false" />

          </div>
          <div>

            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.x" label="X"
              placeholder="Tuliskan X" :errorText="formErrors.x?'failed':''" @input="v=>values.x=v"
              :hints="formErrors.x" :check="false" />

          </div>
        </div>
        <!-- Berkas -->
        <h2 class="font-bold text-[18px] col-span-8 md:col-span-6 px-6" v-show="activeTabIndex === 0">Berkas Pelamar
        </h2>
        <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-show="activeTabIndex === 0">


          <div>
            <label >Foto Pelamar</label>
            <div class="w-full mt-3">
              <input :disabled="!actionText ? true : false" ref="refPasFoto" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.pas_foto}" id="inputPasFoto" @change="imageChange">
            </div>
            <img :src="urlPasFoto" class="col-span-12 !mt-0 w-[231px]">
          </div>

          <div>
            <label >Foto KTP</label>
            <div class="w-full mt-3">
              <input :disabled="!actionText ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.ktp_foto}" id="inputKTPFoto" @change="imageChange">

            </div>
            <img :src="urlKTPFoto" class="col-span-12 !mt-0 w-[231px]">
          </div>


          <div>
            <label >No. KTP</label>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.ktp_no" label=""
              placeholder="Tuliskan Nomor Kartu Penduduk" :errorText="formErrors.ktp_no?'failed':''"
              @input="v=>values.ktp_no=v" :hints="formErrors.ktp_no" :check="false" />
          </div>


          <div>
            <label >Alamat Sesuai KTP</label>
            <FieldX :bind="{ readonly: !actionText }" type="textarea" class="w-full mt-3" :value="values.alamat_asli"
              label="" placeholder="Tuliskan Alamat Sesuai KTP" :errorText="formErrors.alamat_asli?'failed':''"
              @input="v=>values.alamat_asli=v" :hints="formErrors.alamat_asli" :check="false" />
          </div>



          <div>
            <label >Foto Kartu Keluarga</label>
            <div class="w-full mt-3">
              <input :disabled="!actionText ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.kk_foto}" id="inputKKFoto" @change="imageChange">

            </div>
            <img :src="urlKKFoto" class="col-span-12 !mt-0 w-[231px]">
          </div>



          <div>
            <label >No. Kartu Keluarga</label>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.kk_no" label=""
              placeholder="Tuliskan Nomor Kartu Keluarga" :errorText="formErrors.kk_no?'failed':''"
              @input="v=>values.kk_no=v" :hints="formErrors.kk_no" :check="false" />
          </div>



          <div>
            <label >Foto NPWP</label>
            <div class="w-full mt-3">
              <input :disabled="!actionText ? true : false" type="file" accept="image/*" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrors.npwp_foto}" id="inputNPWPFoto" @change="imageChange">

            </div>
            <img :src="urlNPWPFoto" class="col-span-12 !mt-0 w-[231px]">
          </div>



          <div>
            <label >No. NPWP</label>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full mt-3" :value="values.npwp_no" label=""
              placeholder="Tuliskan Nomor Pokok Wajib Pajak" :errorText="formErrors.npwp_no?'failed':''"
              @input="v=>values.npwp_no=v" :hints="formErrors.npwp_no" :check="false" />
          </div>


          <div>
            <label >Tanggal Berlaku NPWP</label>
            <FieldX :bind="{ readonly: !actionText }" type="date" class="w-full mt-3" :value="values.npwp_tgl_berlaku"
              label="" placeholder="Masukan Tanggal Berlaku NPWP" :errorText="formErrors.npwp_tgl_berlaku?'failed':''"
              @input="v=>values.npwp_tgl_berlaku=v" :hints="formErrors.npwp_tgl_berlaku" :check="false" />
          </div>


          <div>
            <label >No. BPJS Kesehatan</label>
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.bpjs_no_kesehatan" label=""
              placeholder="Tuliskan Nomor BPJS" :errorText="formErrors.bpjs_no_kesehatan?'failed':''"
              @input="v=>values.bpjs_no_kesehatan=v" :hints="formErrors.bpjs_no_kesehatan" :check="false" />
          </div>

          <div>
            <label >No. BPJS Ketenagakerjaan</label>
            <FieldX :bind="{ readonly: !actionText }" class="w-full mt-3" :value="values.bpjs_no_ketenagakerjaan"
              label="" placeholder="Tuliskan Nomor BPJS" :errorText="formErrors.bpjs_no_ketenagakerjaan?'failed':''"
              @input="v=>values.bpjs_no_ketenagakerjaan=v" :hints="formErrors.bpjs_no_ketenagakerjaan" :check="false" />
          </div>

          <div>
            <label >Tipe BPJS</label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
              :value="values.bpjs_tipe_id" @input="v=>values.bpjs_tipe_id=v"
              :errorText="formErrors.bpjs_tipe_id?'failed':''" :hints="formErrors.bpjs_tipe_id" label=""
              placeholder="Pilih Tipe BPJS" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      where: `this.group='TIPE BPJS' AND this.is_active='true'`,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
          </div>


          <div>
            <label >Berkas Pendukung Lainnya<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText }" :value="values.berkas_lain"
              @input="(v)=>values.berkas_lain=v" :maxSize="10"
              :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/t_pelamar_det_kartu/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'berkas_lain' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.berkas_lain" label="" placeholder="Upload Berkas" fa-icon="upload"
              accept="application/pdf" :check="false" />

          </div>

          <div>
            <label>Curriculum Vitae (CV)<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText }" :value="values.file_cv"
              @input="(v)=>values.file_cv=v" :maxSize="10"
              :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/t_pelamar/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'file_cv' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.file_cv" label="" placeholder="Upload Berkas CV (PDF)" fa-icon="upload"
              accept="application/pdf" :check="false" />
          </div>

          <div>
            <label>Berkas File Pendukung (Portofolio/Sertifikat)<label class="text-red-500 space-x-0 pl-0"></label></label>
            <FieldUpload class="w-full mt-3" :bind="{ readonly: !actionText }" :value="values.file_dokumen"
              @input="(v)=>values.file_dokumen=v" :maxSize="10"
              :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                  url: `${store.server.url_backend}/operation/t_pelamar/upload`,
                  headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: { field: 'file_dokumen' },
                  onsuccess: response=>response,
                  onerror:(error)=>{},
                 }" :hints="formErrors.file_dokumen" label="" placeholder="Upload Berkas Pendukung (PDF)" fa-icon="upload"
              accept="application/pdf" :check="false" />
          </div>
        </div>
        <!-- Ukuran -->
      </div>


      <!-- JABATAN -->
      <div class="p-4 space-y-10" v-show="activeTabIndex === 6">
        <!-- DETAIL -->
        <div class="flex justify-end mb-4">
          <button @click="addDetail" type="button" v-show="actionText" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-md flex items-center justify-center">
            <icon fa="plus" size="sm mr-0.5" /> Tambah Dokumen
          </button>
        </div>
        <div class="overflow-x-auto bg-white rounded-lg shadow-md p-4"
          style="scrollbar-width: thin; -ms-overflow-style: none;">
          <table class="w-full border-collapse border rounded-lg md:w-[100%] lg:w-[100%] 2xl:w-[100%]">
            <thead class="bg-gray-100 sticky top-0">
              <tr class="text-sm text-left">
                <th class="p-3 border text-center w-[5%]">#</th>
                <th class="p-3 border text-center w-[5%]">No</th>
                <th class="p-3 border text-center w-[40%]">Nama Berkas</th>
                <th class="p-3 border text-center w-[50%]">File</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="inDetailArr.length === 0">
                <td colspan="4" class="py-6 text-center text-gray-500">
                  No Data to Show
                </td>
              </tr>

              <tr v-for="(item, i) in inDetailArr" :key="i" class="border-t hover:bg-gray-50">

                <!-- Trash -->
                <td class="p-3 border text-center">
                  <button
          type="button"
          class="text-red-500 hover:text-red-600"
          @click="hapusDetail(i)"
        >
          <icon fa="trash" />
        </button>
                </td>

                <!-- No -->
                <td class="p-3 border text-center font-bold">
                  {{ i + 1 }}
                </td>

                <!-- Nama Berkas -->
                <td class="p-3 border">
                  <FieldX class="!mt-0" :bind="{ readonly: !actionText }" :value="item.nama_dokumen"
                    @input="v => item.nama_dokumen = v" placeholder="Masukkan Nama Berkas" :check="false" />
                </td>

                <!-- File -->
                <td class="p-3 border">
                  <FieldUpload class="!mt-0" :value="item.file" @input="(v)=>item.file=v" :maxSize="10"
                    :reducerDisplay="val=>!val?null:val.split(':::')[val.split(':::').length-1]" :api="{
                      url: `${store.server.url_backend}/operation/t_pelamar_det_dokumen/upload`,
                      headers: { Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: { field: 'file' },
                      onsuccess: response=>response,
                      onerror:(error)=>{},
                     }" :hints="formErrors.file" placeholder="label" fa-icon="upload" accept="*" :check="false" />

                </td>

              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="p-4 space-y-10" v-show="activeTabIndex === 9">

        <ButtonMultiSelect title="Add To List" @add="onDetailAdd_Lokasi" :api="apiLokasi.apiUrlAndParam"
          :columns="apiLokasi.columns">
          <div class="flex items-center space-x-2">
            <div v-show="actionText"
              class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
              <icon fa="plus" />
              Tambah Lokasi
            </div>
          </div>
        </ButtonMultiSelect>

        <div class="mt-4">
          <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
            <thead>
              <tr class="border">
                <td
                  class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                  No.
                </td>
                <td
                  class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                  Lokasi
                </td>
                <td
                  class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA] w-[5%]">
                  Action
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detail_lokasi" :key="item.id" class="border-t" v-if="detail_lokasi.length > 0">
                <td class="p-2 text-center border border-[#CACACA]">
                  {{ i + 1 }}.
                </td>


                <td class="p-2 border border-[#CACACA]">
                  <!-- {{item.nama ?? '-'}} -->
                  <FieldSelect :bind="{ disabled: true, clearable:false }" class="w-full"
                    :value="item.presensi_lokasi_id" @input="v=>item.presensi_lokasi_id=v"
                    :errorText="formErrors.presensi_lokasi_id?'failed':''" :hints="formErrors.presensi_lokasi_id"
                    label="" placeholder="Pilih Lokasi" valueField="id" displayField="nama" :api="{
                    url: `${store.server.url_backend}/operation/presensi_lokasi`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                    }
                  }" :check="false" />
                </td>



                <td class="p-2 border border-[#CACACA]">
                  <div class="flex justify-center">
                    <button type="button" @click="removeDetail_Lokasi(i)" :disabled="!actionText" title="Hapus">
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
            </tbody>
          </table>
        </div>

      </div>


      <!-- Form Pendidikan -->
      <div class="p-4 " v-show="activeTabIndex === 1">
        <div class="grid <md:grid-cols-1 grid-cols-3 gap-2">
          <div>
            <label>Tingkat Pendidikan <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
              :value="valuesPendidikan.tingkat_id" label="" placeholder="Pilih Tingkat Pendidikan"
              @input="v=>valuesPendidikan.tingkat_id=v" :errorText="formErrorsPend.tingkat_id?'failed':''"
              @update:valueFull="(objVal)=>{
                $log('isi pendidikan',objVal)
                  valuesPendidikan.pendidikan = objVal.value
                }" :hints="formErrorsPend.tingkat_id" valueField="id" displayField="value" :api="{
        url: `${store.server.url_backend}/operation/m_general`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          simplest: true,
          transform: false,
          where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
          join: true,
          selectfield: 'this.id, this.code, this.value, this.is_active'
        }
      }" :check="false" />
          </div>
          <div>
            <label>Tahun Masuk <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full" label=""
              placeholder="Pilih Tahun Masuk" :value="valuesPendidikan.thn_masuk"
              @input="v=>valuesPendidikan.thn_masuk=v" :options="ArrTahun"
              :errorText="formErrorsPend.thn_masuk?'failed':''" :hints="formErrorsPend.thn_masuk" valueField="key"
              displayField="key" :check="false" />
          </div>
          <div>
            <label>Nama Sekolah <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" class="w-full" :value="valuesPendidikan.nama_sekolah" label=""
              placeholder="Tuliskan Nama Sekolah" @input="v=>valuesPendidikan.nama_sekolah=v" :check="false"
              :errorText="formErrorsPend.nama_sekolah?'failed':''" :hints="formErrorsPend.nama_sekolah" />
          </div>
          <div>
            <label>Tahun Lulus <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
              :value="valuesPendidikan.thn_lulus" label="" placeholder="Pilih Tahun Lulus"
              @input="v=>valuesPendidikan.thn_lulus=v" :options="ArrTahun"
              :errorText="formErrorsPend.thn_lulus?'failed':''" :hints="formErrorsPend.thn_lulus" valueField="key"
              displayField="key" :check="false" />
          </div>
          <div>
            <label> Kota <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full"
              :value="valuesPendidikan.kota_id" @input="v=>valuesPendidikan.kota_id=v"
              :errorText="formErrorsPend.kota_id?'failed':''" :hints="formErrorsPend.kota_id" label=""
              placeholder="Pilih Kota" valueField="id" displayField="value" :api="{
        url: `${store.server.url_backend}/operation/m_general`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          simplest: true,
          transform: false,
          join: true,
          where: `this.group='KOTA'`,
          paginate: 1000
        }
      }" :check="false" />
          </div>
          <div>
            <label> Nilai<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" type="number" class="w-full" :value="valuesPendidikan.nilai"
              label="" placeholder="Tuliskan Nilai" @input="v=>valuesPendidikan.nilai=v" :check="false"
              :errorText="formErrorsPend.nilai?'failed':''" :hints="formErrorsPend.nilai" />
          </div>
          <div>
            <label> Jurusan <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Jurusan" class="w-full"
              :value="valuesPendidikan.jurusan" :errorText="formErrorsPend.jurusan?'failed':''"
              :hints="formErrorsPend.jurusan" @input="v=>valuesPendidikan.jurusan=v" :check="false" />
          </div>
          <div>
            <label>Ijasah Terakhir</label>
            <div class="w-full flex items-center">
              <input :disabled="!actionText ? true : false" ref="fileIjz" type="file" accept="application/pdf" class="w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
        :class="{'border-red-500': formErrorsPend.ijazah_foto}" @change="fileIjazah" @input="(v)=>valuesPendidikan.ijazah_foto=v" >
            </div>
          </div>
          <div>
            <label>Catatan</label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Catatan" type="textarea"
              class="w-full" :value="valuesPendidikan.desc" :errorText="formErrorsPend.desc?'failed':''"
              @input="v=>valuesPendidikan.desc=v" :hints="formErrorsPend.desc" :check="false" />
          </div>
          <div>
            <label>Pendidikan Akhir <label class="text-red-500 space-x-0 pl-0">*</label></label>
            <div class="flex items-center space-x-5 ">
              <input :disabled="!actionText ? true : false" type="radio" value="1" v-model="valuesPendidikan.is_pend_terakhir" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
              <label for="aktif_status" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Iya</label>

              <input :disabled="!actionText ? true : false" type="radio" value="0" v-model="valuesPendidikan.is_pend_terakhir" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
              <label for="tidak_aktif_status" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak</label>
            </div>
          </div>
        </div>
        <!-- TABLE -->
        <div class="w-full mt-3">
          <TableStatic customClass="h-50vh" ref="detail" :value="detailPendidikan"
            @cellValueChanged="onCellValueChangedPend" :columns="[{
        headerName: 'No',
        cellRenderer: !actionText?null:'ButtonGrid',
        valueGetter:p=>p.node.rowIndex + 1,
        cellRendererParams: !actionText?null:{
          showValue: true,
          icon: 'times',
          class: 'btn-text-danger',
          click:(app)=>{
            if (app && app.params) {
              const row = app.params.node.data
              swal.fire({
                icon: 'warning', showDenyButton: true,
                text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
              }).then((res) => {
                if (res.isConfirmed) {
                  detailPendidikan = detailPendidikan.filter((e) => e._id != app.params.node.data._id)
                  app.params.api.applyTransaction({ remove: [app.params.node.data] })
                }
              })
            }
          }
        },
        width: 60,
        sortable: false, resizable: true, filter: false, wrapText: true,
        cellClass: ['justify-center', 'bg-gray-50']
      },
      {
        flex: 1,
        headerName: 'Tingkat',
        field: 'pendidikan',
        editable: true,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: () => ({
          values: pendidikan,
        }),
        sortable: false, resizable: true, filter: false, wrapText:true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Nama Sekolah',
        editable: true,
        field: 'nama_sekolah',
        sortable: false, resizable: true, filter: false, wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Jurusan',
        field: 'jurusan',
        editable: true,
        sortable: false, resizable: true, filter: false, wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Tahun Masuk',
        field: 'thn_masuk',
        editable: true,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: () => ({
                          values: tahun,
                        }),
        sortable: false,
        resizable: true,
        filter: false,
        wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Nilai',
        field: 'nilai',
        editable: true,
        sortable: false, resizable: true, filter: false, wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Pendidikan Terakhir',
        field: 'is_pend_terakhir',
        editable: true,
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: {
          values: ['Iya', 'Tidak']
        },
        valueFormatter: (params) => {
          if (params.value === '1') return 'Iya'
          if (params.value === '0') return 'Tidak'
          return params.value
        },
        valueParser: (params) => {
          if (params.newValue === 'Iya') return '1'
          if (params.newValue === 'Tidak') return '0'
          return params.newValue
        },
        sortable: false,
        resizable: true,
        filter: false,
        wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      },
      {
        flex: 1,
        headerName: 'Note',
        field: 'desc',
        editable: true,
        sortable: false, resizable: true, filter: false, wrapText: true,
        cellClass: ['!border-gray-200', 'justify-center'],
      }
    ]">
            <template #header>
              <button :disabled="!actionText ? true : false" @click="addPendidikan" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
            <icon fa="plus" /> <span>Add to List</span>
          </button>
              <button :disabled="!actionText ? true : false" @click="detailPendidikan = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
            <icon fa="trash" /> <span>Remove</span>
          </button>
            </template>
          </TableStatic>
        </div>
        <!-- END TABLE -->
      </div>

      <div class="<md:col-span-1 col-span-2 p-4 grid <md:grid-cols-1 grid-cols-3 gap-2" v-if="activeTabIndex === 2">
        <div class="<md:col-span-1 col-span-3">
          <button v-if="actionText" title="Add Detail" @click="onDetailAddKeluarga">
        <div class="flex items-center space-x-2" v-if="actionText">
          <div v-show=" actionText"
            class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
            <icon fa="plus" />
            Add To List
          </div>
        </div>
      </button>
          <div class="overflow-scroll lg:overflow-visible <md:col-span-1 col-span-3 mt-4">
            <table class="w-full overflow-x-auto table-auto border border-[#CACACA] pt-4">
              <thead>
                <tr class="border">
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                    No</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Keluarga</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Nama</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Pekerjaan</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Pendidikan Teakhir</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Jenis Kelamin</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Tanggal Lahir</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Askes
                  </td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center border bg-[#f8f8f8] border-[#CACACA]">
                    Catatan</td>
                  <td
                    class="text-[#8f8f8f] font-semibold text-[12px] text-capitalize p-2 text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in detailKeluarga" :key="item.id" class="border-t">
                  <td class="text-[12px] text-center border border-[#CACACA]">
                    {{ i + 1 }}.
                  </td>
                  <td class="text-[12px] text-left border border-[#CACACA]">
                    <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.keluarga_id"
                      @input="v=>item.keluarga_id=v" :errorText="formErrors.keluarga_id?'failed':''"
                      :hints="formErrors.keluarga_id" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {
                      simplest:true,
                      where: `this.group='HUBUNGAN KELUARGA' AND this.is_active='true'`,
                      order_by:'value',
                      selectfield: 'this.id, this.code, this.value, this.is_active',
                      order_type: 'ASC'
                    }
                  }" fa-icon="caret-down" label="" placeholder="Pilih Bank" :check="false" />
                  </td>
                  <td class="text-[12px] text-left border border-[#CACACA]">
                    <FieldX :bind="{ readonly: !actionText }" type="text" :value="item.nama"
                      :errorText="formErrors.nama?'failed':''" @input="v=>item.nama=v" :hints="formErrors.nama"
                      :check="false" placeholder="" label="" />
                  </td>
                  <td class="text-[12px] text-left border border-[#CACACA]">
                    <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.pekerjaan_id"
                      @input="v=>item.pekerjaan_id=v" :errorText="formErrors.pekerjaan_id?'failed':''"
                      :hints="formErrors.pekerjaan_id" valueField="id" displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    where: `this.group='PEKERJAAN' AND this.is_active='true'`,
                    join: true,
                    selectfield: 'this.id, this.code, this.value, this.is_active'
                  }
                }" label="" :check="false" />
                  </td>
                  <td class="text-[12px] text-left border border-[#CACACA]">
                    <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.pend_terakhir_id"
                      @input="v=>item.pend_terakhir_id=v" :errorText="formErrors.pend_terakhir_id?'failed':''"
                      :hints="formErrors.pend_terakhir_id" valueField="id" displayField="value" :api="{
                        url: `${store.server.url_backend}/operation/m_general`,
                        headers: {
                          'Content-Type': 'Application/json',
                          Authorization: `${store.user.token_type} ${store.user.token}`
                        },
                        params: {
                          simplest: true,
                          transform: false,
                          where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
                          join: true,
                          selectfield: 'this.id, this.code, this.value, this.is_active'
                        }
                      }" label="" :check="false" />
                  </td>
                  <td class="text-[12px] text-left w-[15%] border border-[#CACACA]">
                    <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.jk_id"
                      @input="v=>item.jk_id=v" :errorText="formErrors.jk_id?'failed':''" :hints="formErrors.jk_id"
                      valueField="id" displayField="value" :api="{
                        url: `${store.server.url_backend}/operation/m_general`,
                        headers: {
                          'Content-Type': 'Application/json',
                          Authorization: `${store.user.token_type} ${store.user.token}`
                        },
                        params: {
                          simplest: true,
                          transform: false,
                          where: `this.group='JENIS KELAMIN' AND this.is_active='true'`,
                          join: true,
                          selectfield: 'this.id, this.code, this.value, this.is_active'
                        }
                      }" label="" :check="false" />
                  </td>
                  <td class="text-[12px] text-right border border-[#CACACA]">
                    <FieldX type="date" class="text-right" :bind="{ readonly: !actionText }" type="text"
                      :value="item.tgl_lahir" :errorText="formErrors.tgl_lahir?'failed':''" @input="v=>item.tgl_lahir=v"
                      :hints="formErrors.tgl_lahir" :check="false" placeholder="" label="" />
                  </td>
                  <td class="text-[12px] text-center border border-[#CACACA]">
                    <input
                      type="checkbox"
                      :disabled="!actionText"
                      :checked="item.include_askes"
                      @change="e => item.include_askes = e.target.checked"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                  </td>
                  <td class="text-[12px] text-left border border-[#CACACA]">
                    <FieldX type="textarea" :bind="{ readonly: !actionText }" type="text" :value="item.desc"
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
                <tr v-if="detailKeluarga.length === 0" class="text-center">
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
      </div>


      <!-- Form Pelatihan -->
      <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
        v-show="activeTabIndex === 3">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Nama Pelatihan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Pelatihan"
              class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_pel"
              :errorText="formErrorsPel.nama_pel?'failed':''" @input="v=>valuesPelatihan.nama_pel=v"
              :hints="formErrorsPel.nama_pel" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
              class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.tahun" @input="v=>valuesPelatihan.tahun=v"
              :options="ArrTahun" :errorText="formErrorsPel.tahun?'failed':''" :hints="formErrorsPel.tahun"
              valueField="key" displayField="key" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Nama Lembaga<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Lembaga"
              class="col-span-12 !mt-0 w-full" :value="valuesPelatihan.nama_lem"
              :errorText="formErrorsPel.nama_lem?'failed':''" @input="v=>valuesPelatihan.nama_lem=v"
              :hints="formErrorsPel.nama_lem" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
              :value="valuesPelatihan.kota_id" @input="v=>valuesPelatihan.kota_id=v"
              :errorText="formErrorsPel.kota_id?'failed':''" @update:valueFull="(objVal)=>{
                    valuesPelatihan.kota = objVal.value
                  }" :hints="formErrorsPel.kota_id" label="" placeholder="Pilih Kota" valueField="id"
              displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-12">
          <TableStatic customClass="h-50vh" ref="detail" :value="detailPelatihan" :columns="[{
                  headerName: 'No',
                  cellRenderer: !actionText?null:'ButtonGrid',
                  valueGetter:p=>p.node.rowIndex + 1,
                  cellRendererParams: !actionText?null:{
                    showValue: true,
                    icon: 'times',
                    class: 'btn-text-danger',
                    click:(app)=>{
                      if (app && app.params) {
                        const row = app.params.node.data
                        swal.fire({
                          icon: 'warning', showDenyButton: true,
                          text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                        }).then((res) => {
                          if (res.isConfirmed) {
                            app.params.api.applyTransaction({ remove: [app.params.node.data] })
                            detailPelatihan = detailPelatihan.filter((e) => e._id != app.params.node.data._id)
                          }
                        })
                      }
                    }
                  },
                  width: 60,
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  headerName: 'Nama Pelatihan',
                  field: 'nama_pel',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Nama Lembaga',
                  field: 'nama_lem',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Kota',
                  field: 'kota',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'tahun',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                ]">
            <template #header>
              <button :disabled="!actionText ? true : false" @click="addPelatihan" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="plus" /> <span>Add to List</span>
                </button>
              <button :disabled="!actionText ? true : false" @click="detailPelatihan = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="trash" /> <span>Remove</span>
                </button>
            </template>
          </TableStatic>

        </div>
      </div>

      <!-- Form Prestasi -->
      <div class="grid px-6 grid-cols-8 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
        v-show="activeTabIndex === 4">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tingkat<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tingkat"
              class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tingkat_pres_id" @update:valueFull="(objVal)=>{
                  valuesPrestasi.tingkat = objVal.value
                }" @input="v=>valuesPrestasi.tingkat_pres_id=v" :errorText="formErrorsPres.tingkat_pres_id?'failed':''"
              :hints="formErrorsPres.tingkat_pres_id" valueField="id" displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='PENDIDIKAN' AND this.is_active='true'`,
                    paginate: 1000
                  }
                }" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
              class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.tahun" @input="v=>valuesPrestasi.tahun=v"
              :options="ArrTahun" :errorText="formErrorsPres.tahun?'failed':''" :hints="formErrorsPres.tahun"
              valueField="key" displayField="key" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Prestasi / Penghargaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Prestasi / Penghargaan"
              class="col-span-12 !mt-0 w-full" :value="valuesPrestasi.nama_pres"
              :errorText="formErrorsPres.nama_pres?'failed':''" @input="v=>valuesPrestasi.nama_pres=v"
              :hints="formErrorsPres.nama_pres" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-12">
          <TableStatic customClass="h-50vh" ref="detail" :value="detailPrestasi" :columns="[{
                  headerName: 'No',
                  cellRenderer: !actionText?null:'ButtonGrid',
                  valueGetter:p=>p.node.rowIndex + 1,
                  cellRendererParams: !actionText?null:{
                    showValue: true,
                    icon: 'times',
                    class: 'btn-text-danger',
                    click:(app)=>{
                      if (app && app.params) {
                        const row = app.params.node.data
                        swal.fire({
                          icon: 'warning', showDenyButton: true,
                          text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                        }).then((res) => {
                          if (res.isConfirmed) {
                            detailPrestasi = detailPrestasi.filter((e) => e._id != app.params.node.data._id)
                            app.params.api.applyTransaction({ remove: [app.params.node.data] })
                          }
                        })
                      }
                    }
                  },
                  width: 60,
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  field: 'tingkat',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'nama_pres',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'tahun',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                ]">
            <template #header>
              <button :disabled="!actionText ? true : false" @click="addPrestasi" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="plus" /> <span>Add to List</span>
                </button>
              <button :disabled="!actionText ? true : false" @click="detailPrestasi = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="trash" /> <span>Remove</span>
                </button>
            </template>
          </TableStatic>

        </div>
      </div>

      <!-- Form Organisasi -->
      <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
        v-show="activeTabIndex === 5">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Nama Organisasi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Organisasi"
              class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.nama"
              :errorText="formErrorsOrg.nama?'failed':''" @input="v=>valuesOrganisasi.nama=v"
              :hints="formErrorsOrg.nama" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tahun<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tahun"
              class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.tahun" @input="v=>valuesOrganisasi.tahun=v"
              :options="ArrTahun" :errorText="formErrorsOrg.tahun?'failed':''" :hints="formErrorsOrg.tahun"
              valueField="key" displayField="key" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Jenis Organisasi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label=""
              placeholder="Pilih Jenis Organisasi" class="col-span-12 !mt-0 w-full"
              :value="valuesOrganisasi.jenis_org_id" @input="v=>valuesOrganisasi.jenis_org_id=v"
              :errorText="formErrorsOrg.jenis_org_id?'failed':''" @update:valueFull="(objVal)=>{
                  valuesOrganisasi.jenis = objVal.value
                }" :hints="formErrorsOrg.jenis_org_id" valueField="id" displayField="value" :api="{
                  url: `${store.server.url_backend}/operation/m_general`,
                  headers: {
                    'Content-Type': 'Application/json',
                    Authorization: `${store.user.token_type} ${store.user.token}`
                  },
                  params: {
                    simplest: true,
                    transform: false,
                    join: true,
                    where: `this.group='JENIS ORGANISASI' AND this.is_active='true'`,
                    paginate: 1000
                  }
                }" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" label="" placeholder="Pilih Tingkat"
              class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.kota_id" @input="v=>valuesOrganisasi.kota_id=v"
              :errorText="formErrorsOrg.kota_id?'failed':''" :hints="formErrorsOrg.kota_id" @update:valueFull="(objVal)=>{
                    valuesOrganisasi.kota = objVal.value
                  }" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" />
          </div>
        </div>

        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" class="col-span-12 !mt-0 w-full" :value="valuesOrganisasi.posisi"
              label="" placeholder="Tuliskan Posisi" :errorText="formErrorsOrg.posisi?'failed':''"
              @input="v=>valuesOrganisasi.posisi=v" :hints="formErrorsOrg.posisi" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-12">
          <TableStatic customClass="h-50vh" ref="detail" :value="detailOrganisasi" :columns="[{
                  headerName: 'No',
                  cellRenderer: !actionText?null:'ButtonGrid',
                  valueGetter:p=>p.node.rowIndex + 1,
                  cellRendererParams: !actionText?null:{
                    showValue: true,
                    icon: 'times',
                    class: 'btn-text-danger',
                    click:(app)=>{
                      if (app && app.params) {
                        const row = app.params.node.data
                        swal.fire({
                          icon: 'warning', showDenyButton: true,
                          text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                        }).then((res) => {
                          if (res.isConfirmed) {
                            detailOrganisasi = detailOrganisasi.filter((e) => e._id != app.params.node.data._id)
                            app.params.api.applyTransaction({ remove: [app.params.node.data] })
                          }
                        })
                      }
                    }
                  },
                  width: 60,
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  headerName: 'Nama Organisasi',
                  field: 'nama',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Jenis Organisasi',
                  field: 'jenis',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'posisi',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'tahun',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Kota',
                  field: 'kota',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                ]">
            <template #header>
              <button :disabled="!actionText ? true : false" @click="addOrganisasi" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="plus" /> <span>Add to List</span>
                </button>
              <button :disabled="!actionText ? true : false" @click="detailOrganisasi = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="trash" /> <span>Remove</span>
                </button>
            </template>
          </TableStatic>

        </div>
      </div>

      <!-- Form Pengalaman Kerja -->
      <div class="grid grid-cols-8 px-6 md:grid-cols-12 text-[14px] gap-x-[80px] gap-y-[26px] mt-[36px]"
        v-show="activeTabIndex === 7">
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Nama Perusahaan<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Nama Perusahaan"
              class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.instansi"
              :errorText="formErrorsPK.instansi?'failed':''" @input="v=>valuesPengalaman.instansi=v"
              :hints="formErrorsPK.instansi" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Bidang Usaha<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Bidang Usaha"
              class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.bidang_usaha"
              :errorText="formErrorsPK.bidang_usaha?'failed':''" @input="v=>valuesPengalaman.bidang_usaha=v"
              :hints="formErrorsPK.bidang_usaha" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">No. Telp<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan No Telp" type="number"
              class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.no_tlp"
              :errorText="formErrorsPK.no_tlp?'failed':''" @input="v=>valuesPengalaman.no_tlp=v"
              :hints="formErrorsPK.no_tlp" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Posisi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Posisi" class="col-span-12 !mt-0 w-full"
              :value="valuesPengalaman.posisi" :errorText="formErrorsPK.posisi?'failed':''"
              @input="v=>valuesPengalaman.posisi=v" :hints="formErrorsPK.posisi" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tahun Masuk<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
              :value="valuesPengalaman.thn_masuk" label="" placeholder="Pilih Tahun Masuk"
              @input="v=>valuesPengalaman.thn_masuk=v" :options="ArrTahun"
              :errorText="formErrorsPK.thn_masuk?'failed':''" :hints="formErrorsPK.thn_masuk" valueField="key"
              displayField="key" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Tahun Keluar<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
              :value="valuesPengalaman.thn_keluar" label="" placeholder="Pilih Tahun Keluar"
              @input="v=>valuesPengalaman.thn_keluar=v" :options="ArrTahun"
              :errorText="formErrorsPK.thn_keluar?'failed':''" :hints="formErrorsPK.thn_keluar" valueField="key"
              displayField="key" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Alamat Kantor<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldX :bind="{ readonly: !actionText }" label="" placeholder="Tuliskan Alamat Kantor" type="textarea"
              class="col-span-12 !mt-0 w-full" :value="valuesPengalaman.alamat_kantor"
              :errorText="formErrorsPK.alamat_kantor?'failed':''" @input="v=>valuesPengalaman.alamat_kantor=v"
              :hints="formErrorsPK.alamat_kantor" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Kota<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="col-span-12 !mt-0 w-full"
              :value="valuesPengalaman.kota_id" @input="v=>valuesPengalaman.kota_id=v"
              :errorText="formErrorsPK.kota_id?'failed':''" :hints="formErrorsPK.kota_id" label=""
              placeholder="Pilih Kota" valueField="id" displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      join: true,
                      where: `this.group='KOTA'`,
                      paginate: 1000
                    }
                  }" :check="false" />
          </div>
        </div>
        <div class="col-span-8 md:col-span-6">
          <div class="grid grid-cols-12 items-center">
            <label class="col-span-12">Surat Refrensi<label class="text-red-500 space-x-0 pl-0">*</label></label>
            <div class="col-span-12 flex items-center">
              <input :disabled="!actionText ? true : false" ref="fileSurat" type="file" accept="application/pdf" class="col-span-12 !mt-0 w-full border rounded-[0.25rem] text-[12px] py-[10px] px-[20px]"
                :class="{'border-red-500': formErrorsPK.surat_referensi}" @change="fileSrtRef" @input="(v)=>valuesPengalaman.surat_referensi=v" >

            </div>
          </div>
        </div>
        <div class="col-span-8 md:col-span-12">
          <TableStatic customClass="h-50vh" ref="detail" :value="detailPengalaman" :columns="[{
                  headerName: 'No',
                  cellRenderer: !actionText?null:'ButtonGrid',
                  valueGetter:p=>p.node.rowIndex + 1,
                  cellRendererParams: !actionText?null:{
                    showValue: true,
                    icon: 'times',
                    class: 'btn-text-danger',
                    click:(app)=>{
                      if (app && app.params) {
                        const row = app.params.node.data
                        swal.fire({
                          icon: 'warning', showDenyButton: true,
                          text: `Hapus Baris ${app.params.node.rowIndex-(-1)}?`,
                        }).then((res) => {
                          if (res.isConfirmed) {
                            detailPengalaman = detailPengalaman.filter((e) => e._id != app.params.node.data._id)
                            app.params.api.applyTransaction({ remove: [app.params.node.data] })
                          }
                        })
                      }
                    }
                  },
                  width: 60,
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  headerName: 'Nama Instansi',
                  field: 'instansi',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Bidang Usaha',
                  field: 'bidang_usaha',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  field: 'posisi',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Tahun Masuk',
                  field: 'thn_masuk',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Tahun Keluar',
                  field: 'thn_keluar',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                {
                  flex: 1,
                  headerName: 'Alamat Kantor',
                  field: 'alamat_kantor',
                  sortable: false, resizable: true, filter: false,
                  cellClass: ['!border-gray-200', 'justify-center'],
                },
                ]">
            <template #header>
              <button :disabled="!actionText ? true : false" @click="addPengalaman" type="button" class="mr-[15px] bg-[#005FBF] hover:bg-[#0055ab] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="plus" /> <span>Add to List</span>
                </button>
              <button :disabled="!actionText ? true : false" @click="detailPengalaman = []" type="button" class="bg-[#DD4B39] hover:bg-[#da3c28] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
                  <icon fa="trash" /> <span>Remove</span>
                </button>
            </template>
          </TableStatic>

        </div>
      </div>
      <div class="flex flex-row mb-4 px-6 justify-end space-x-[20px] mt-[5em]">
        <button @click="onBack" v-show="!isProfile" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Batal
          </button>
        <button
          v-if="isRead && (!values.status || values.status.toUpperCase() === 'DRAFT') && (currentMenu?.can_update)"
          @click="posted"
          type="button"
          class="bg-blue-600 hover:bg-blue-700 text-white px-[36.5px] py-[12px] rounded-[6px]"
        >
          Posting
        </button>
        <button
  v-show="(actionText || isProfile) && (currentMenu?.can_create || currentMenu?.can_update) && (!isRead || !values.status || values.status.toUpperCase() === 'DRAFT')"
  @click="onSave"
  class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px]"
>
  Simpan
</button>
      </div>
      <!-- MODAL PROSES SCAN CV & VALIDASI (HRIS TEMPRINA WHITE & GREY THEME) -->
      <div v-show="cvScanModal.open" class="fixed inset-0 flex items-center justify-center z-50 p-4">
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="cvScanModal.status !== 'scanning' ? cvScanModal.open = false : null"></div>

        <!-- Modal Box -->
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-lg max-h-[88vh] flex flex-col overflow-hidden border border-gray-300 z-50">
          
          <!-- Modal Header (HRIS Slate Gray Theme) -->
          <div class="bg-gray-600 text-white px-5 py-3 flex items-center justify-between border-b border-gray-700">
            <div>
              <h3 class="font-bold text-sm leading-tight">Pemindaian & Ekstraksi CV</h3>
              <p class="text-xs text-gray-200">Auto-fill formulir data pelamar</p>
            </div>
            <button 
              v-if="cvScanModal.status !== 'scanning'" 
              type="button"
              @click="cvScanModal.open = false" 
              class="text-gray-300 hover:text-white text-lg font-bold px-2 py-1 leading-none cursor-pointer"
            >
              &times;
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-5 overflow-y-auto space-y-4">
            
            <!-- Info File -->
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-md border border-gray-200">
              <div class="w-8 h-8 rounded bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-sm">
                <i class="fa fa-file-text-o"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate" v-text="cvScanModal.fileName || 'Berkas CV'"></p>
                <p class="text-[11px] text-gray-500" v-text="cvScanModal.fileSize"></p>
              </div>
              <div>
                <span v-if="cvScanModal.status === 'scanning'" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-200 text-gray-700">
                  <i class="fa fa-spinner fa-spin mr-1"></i>
                  Memproses
                </span>
                <span v-else-if="cvScanModal.status === 'success'" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-700 text-white">
                  <i class="fa fa-check mr-1"></i>
                  Selesai
                </span>
                <span v-else-if="cvScanModal.status === 'error'" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-300 text-gray-800">
                  <i class="fa fa-times mr-1"></i>
                  Gagal
                </span>
              </div>
            </div>

            <!-- Progress Bar -->
            <div>
              <div class="flex justify-between text-xs font-semibold text-gray-600 mb-1">
                <span>Progres Ekstraksi</span>
                <span class="text-gray-800 font-bold" v-text="cvScanModal.progress + '%'"></span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div 
                  class="bg-[#E59819] h-2 rounded-full transition-all duration-300" 
                  :style="'width: ' + cvScanModal.progress + '%;'"
                ></div>
              </div>
            </div>

            <!-- Live Steps Tracker -->
            <div class="space-y-2 border-t border-gray-200 pt-3">
              
              <!-- Step 1 -->
              <div class="flex items-center space-x-3 text-xs" :class="cvScanModal.currentStep >= 1 ? 'text-gray-800' : 'text-gray-400'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border"
                  :class="cvScanModal.currentStep > 1 ? 'bg-gray-700 border-gray-700 text-white font-bold' : (cvScanModal.currentStep === 1 ? 'bg-[#E59819] border-[#E59819] text-white' : 'bg-gray-100 border-gray-300 text-gray-400')">
                  <i v-if="cvScanModal.currentStep > 1" class="fa fa-check"></i>
                  <i v-else-if="cvScanModal.currentStep === 1" class="fa fa-spinner fa-spin"></i>
                  <span v-else>1</span>
                </div>
                <span :class="{'font-semibold text-gray-900': cvScanModal.currentStep === 1}">1. Membaca berkas dokumen CV</span>
              </div>

              <!-- Step 2 -->
              <div class="flex items-center space-x-3 text-xs" :class="cvScanModal.currentStep >= 2 ? 'text-gray-800' : 'text-gray-400'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border"
                  :class="cvScanModal.currentStep > 2 ? 'bg-gray-700 border-gray-700 text-white font-bold' : (cvScanModal.currentStep === 2 ? 'bg-[#E59819] border-[#E59819] text-white' : 'bg-gray-100 border-gray-300 text-gray-400')">
                  <i v-if="cvScanModal.currentStep > 2" class="fa fa-check"></i>
                  <i v-else-if="cvScanModal.currentStep === 2" class="fa fa-spinner fa-spin"></i>
                  <span v-else>2</span>
                </div>
                <span :class="{'font-semibold text-gray-900': cvScanModal.currentStep === 2}">2. Ekstraksi nama, kontak & media sosial</span>
              </div>

              <!-- Step 3 -->
              <div class="flex items-center space-x-3 text-xs" :class="cvScanModal.currentStep >= 3 ? 'text-gray-800' : 'text-gray-400'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border"
                  :class="cvScanModal.currentStep > 3 ? 'bg-gray-700 border-gray-700 text-white font-bold' : (cvScanModal.currentStep === 3 ? 'bg-[#E59819] border-[#E59819] text-white' : 'bg-gray-100 border-gray-300 text-gray-400')">
                  <i v-if="cvScanModal.currentStep > 3" class="fa fa-check"></i>
                  <i v-else-if="cvScanModal.currentStep === 3" class="fa fa-spinner fa-spin"></i>
                  <span v-else>3</span>
                </div>
                <span :class="{'font-semibold text-gray-900': cvScanModal.currentStep === 3}">3. Pemindaian riwayat pendidikan & IPK</span>
              </div>

              <!-- Step 4 -->
              <div class="flex items-center space-x-3 text-xs" :class="cvScanModal.currentStep >= 4 ? 'text-gray-800' : 'text-gray-400'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border"
                  :class="cvScanModal.currentStep > 4 ? 'bg-gray-700 border-gray-700 text-white font-bold' : (cvScanModal.currentStep === 4 ? 'bg-[#E59819] border-[#E59819] text-white' : 'bg-gray-100 border-gray-300 text-gray-400')">
                  <i v-if="cvScanModal.currentStep > 4" class="fa fa-check"></i>
                  <i v-else-if="cvScanModal.currentStep === 4" class="fa fa-spinner fa-spin"></i>
                  <span v-else>4</span>
                </div>
                <span :class="{'font-semibold text-gray-900': cvScanModal.currentStep === 4}">4. Ekstraksi riwayat pengalaman kerja</span>
              </div>

              <!-- Step 5 -->
              <div class="flex items-center space-x-3 text-xs" :class="cvScanModal.currentStep >= 5 ? 'text-gray-800' : 'text-gray-400'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border"
                  :class="cvScanModal.status === 'success' ? 'bg-gray-700 border-gray-700 text-white font-bold' : (cvScanModal.currentStep === 5 ? 'bg-[#E59819] border-[#E59819] text-white' : 'bg-gray-100 border-gray-300 text-gray-400')">
                  <i v-if="cvScanModal.status === 'success'" class="fa fa-check"></i>
                  <i v-else-if="cvScanModal.currentStep === 5" class="fa fa-spinner fa-spin"></i>
                  <span v-else>5</span>
                </div>
                <span :class="{'font-semibold text-gray-900': cvScanModal.currentStep === 5}">5. Auto-fill data ke formulir pelamar</span>
              </div>

            </div>

            <!-- Result Summary Box (Saat Selesai - White & Grey Theme) -->
            <div v-if="cvScanModal.status === 'success'" class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-200 text-xs space-y-1">
              <div class="flex items-center space-x-1.5 font-bold text-gray-800 mb-1.5">
                <i class="fa fa-check-circle text-gray-700"></i>
                <span>Hasil Ekstraksi Berhasil:</span>
              </div>
              <p class="text-gray-600"><strong class="text-gray-800">Nama:</strong> <span v-text="cvScanModal.summary.nama || '-'"></span></p>
              <p class="text-gray-600"><strong class="text-gray-800">Kontak:</strong> <span v-text="(cvScanModal.summary.email || '-') + ' | ' + (cvScanModal.summary.telp || '-')"></span></p>
              <p class="text-gray-600"><strong class="text-gray-800">Pendidikan:</strong> <span v-text="cvScanModal.summary.pendidikanCount + ' data terdeteksi'"></span></p>
              <p class="text-gray-600"><strong class="text-gray-800">Pengalaman:</strong> <span v-text="cvScanModal.summary.pengalamanCount + ' data terdeteksi'"></span></p>
              <p class="text-gray-600"><strong class="text-gray-800">Lampiran:</strong> Berkas CV tersimpan ke formulir</p>
            </div>

            <!-- Error Box -->
            <div v-if="cvScanModal.status === 'error'" class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-300 text-xs text-gray-800">
              <p class="font-bold flex items-center space-x-1 mb-1 text-red-600">
                <i class="fa fa-exclamation-triangle"></i>
                <span>Terjadi Kendala:</span>
              </p>
              <p v-text="cvScanModal.errorMessage"></p>
            </div>

          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 px-5 py-3 border-t border-gray-200 flex justify-end space-x-2">
            <button
              v-if="cvScanModal.status === 'scanning'"
              type="button"
              disabled
              class="bg-gray-200 text-gray-500 text-xs font-semibold py-1.5 px-3.5 rounded cursor-not-allowed flex items-center space-x-1.5"
            >
              <i class="fa fa-spinner fa-spin"></i>
              <span>Sedang Mengekstrak...</span>
            </button>
            <button
              v-else-if="cvScanModal.status === 'success'"
              type="button"
              @click="cvScanModal.open = false"
              class="bg-[#E59819] hover:bg-[#d48b14] text-white text-xs font-semibold py-1.5 px-3.5 rounded shadow duration-200 flex items-center space-x-1 cursor-pointer"
            >
              <span>Tutup & Periksa Formulir</span>
              <i class="fa fa-arrow-right"></i>
            </button>
            <button
              v-else-if="cvScanModal.status === 'error'"
              type="button"
              @click="cvScanModal.open = false"
              class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold py-1.5 px-3.5 rounded shadow duration-200 cursor-pointer"
            >
              Tutup
            </button>
          </div>

        </div>
      </div>
      <!-- FORM END -->
    </div>
  </div>
</div>
@endverbatim
@endif