<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Active</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inactive</button>
      </div>
    </div>
    <div>
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))" class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions" class="max-h-[450px]">
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
        <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali" @click="onBack"/>
        <div>
          <h1 class="text-20px font-bold">Form Realisasi Perjalanan Dinas</h1>
          <p class="text-gray-100">Realisasi Perjalanan Dinas</p>
        </div>
      </div>
    </div>
    <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
      <!-- START COLUMN -->
      <div>
        <FieldX
          :bind="{ readonly: true }"  class="w-full !mt-3"
          :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
          @input="v=>values.nomor=v" :hints="formErrors.nomor" :check="false"
          label="Nomor" placeholder="Autofield Nomor"
        />
      </div>
      <div>   
        <FieldPopup
            :bind="{ readonly: !actionText }" class="w-full !mt-3"
            :value="values.t_spd_id" @input="(v)=>values.t_spd_id=v"
            :errorText="formErrors.t_spd_id?'failed':''" 
            :hints="formErrors.t_spd_id" 
            @update:valueFull="(objVal)=>{
                if(objVal){
                  values.direktorat = objVal['m_dir.nama']
                  values.divisi = objVal['m_divisi.nama']
                  values.departemen = objVal['m_dept.nama']
                  values.posisi = objVal['m_posisi.desc_kerja']
                  values.tanggal = objVal.tanggal
                  values.tgl_awal = objVal.tgl_acara_awal
                  values.tgl_akhir = objVal.tgl_acara_akhir
                  values.jml_hari = objVal.tgl_acara_akhir.split('/')[0] - objVal.tgl_acara_awal.split('/')[0]
                  values.zona_asal = objVal['m_zona_asal.nama']
                  values.zona_tujuan = objVal['m_zona_tujuan.nama']
                  values.lokasi_tujuan = objVal['m_lokasi_tujuan.nama']
                  values.nik = objVal['m_kary.nik']
                  values.pic = objVal['pic.nama_lengkap']
                  values.total_biaya_spd = objVal.total_biaya
                  values.is_kend_dinas = objVal.is_kend_dinas
                  getDetailSPD()
                }else if(objVal === null){
                  values.direktorat = null 
                  values.divisi = null 
                  values.departemen = null 
                  values.posisi = null 
                  values.tgl_awal = null 
                  values.tgl_akhir = null 
                  values.jml_hari = null 
                  values.zona_asal = null 
                  values.zona_tujuan = null 
                  values.lokasi_tujuan = null 
                  values.nik = null 
                  values.pic = null 
                  values.total_biaya_spd = null 
                  values.is_kend_dinas = null
                  detailArr = []
                }
                

              }"
            valueField="id" displayField="nomor"
            :api="{
              url: `${store.server.url_backend}/operation/t_spd`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
                simplest:true,
                scopes: 'forREPD',
                searchfield: 'this.nomor, this.id, m_kary.nama_lengkap, m_zona_tujuan.nama, m_lokasi_tujuan.nama'
              }
            }"
            placeholder="Pilih Nomor SPD" label="Nomor SPD" :check="false" 
            :columns="[{
              headerName: 'No',
              valueGetter:(p)=>p.node.rowIndex + 1,
              width: 60,
              sortable: false, resizable: false, filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              flex: 1,
              field: 'nomor',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'm_kary.nama_lengkap',
              headerName: 'Karyawan',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'pic.nama_lengkap',
              headerName: 'PIC',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'm_zona_tujuan.nama',
              headerName: 'Zona',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            {
              flex: 1,
              field: 'm_lokasi_tujuan.nama',
              headerName: 'Lokasi',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-center']
            },
            ]"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }"  class="w-full !mt-3"
          :value="values.direktorat" :errorText="formErrors.direktorat?'failed':''"
          @input="v=>values.direktorat=v" :hints="formErrors.direktorat" :check="false"
          label="Direktorat" placeholder="Autofield Direktorat"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: true }"  class="w-full !mt-3"
          :value="values.divisi" :errorText="formErrors.divisi?'failed':''"
          @input="v=>values.divisi=v" :hints="formErrors.divisi" :check="false"
          label="Divisi" placeholder="Autofield Divisi"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }"  class="w-full !mt-3"
          :value="values.departemen" :errorText="formErrors.departemen?'failed':''"
          @input="v=>values.departemen=v" :hints="formErrors.departemen" :check="false"
          label="Departemen" placeholder="Autofield Departemen"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }"  class="w-full !mt-3"
          :value="values.posisi" :errorText="formErrors.posisi?'failed':''"
          @input="v=>values.posisi=v" :hints="formErrors.posisi" :check="false"
          label="Posisi" placeholder="Autofield Posisi"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true, disabled: true }" type="date"  class="w-full !mt-3"
          :value="values.tanggal" :errorText="formErrors.tanggal?'failed':''"
          @input="v=>values.tanggal=v" :hints="formErrors.tanggal" :check="false"
          label="Tanggal" placeholder="Autofield Tanggal"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: true, disabled: true }" type="date" class="w-full !mt-3"
          :value="values.tgl_awal" :errorText="formErrors.tgl_awal?'failed':''"
          @input="v=>values.tgl_awal=v" :hints="formErrors.tgl_awal" :check="false"
          label="Tanggal Acara Awal" placeholder="Autofield Tanggal Acara Awal"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true, disabled: true }" type="date" class="w-full !mt-3"
          :value="values.tgl_akhir" :errorText="formErrors.tgl_akhir?'failed':''"
          @input="v=>values.tgl_akhir=v" :hints="formErrors.tgl_akhir" :check="false"
          label="Tanggal Acara Akhir" placeholder="Autofield Tanggal Acara Akhir"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.jml_hari" :errorText="formErrors.jml_hari?'failed':''"
          @input="v=>values.jml_hari=v" :hints="formErrors.jml_hari" :check="false"
          label="Jumlah Hari" placeholder="Autofield Jumlah Hari"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.zona_asal" :errorText="formErrors.zona_asal?'failed':''"
          @input="v=>values.zona_asal=v" :hints="formErrors.zona_asal" :check="false"
          label="Zona Asal" placeholder="Autofield Zona Asal"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.zona_tujuan" :errorText="formErrors.zona_tujuan?'failed':''"
          @input="v=>values.zona_tujuan=v" :hints="formErrors.zona_tujuan" :check="false"
          label="Zona Tujuan" placeholder="Autofield Zona Tujuan"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.lokasi_tujuan" :errorText="formErrors.lokasi_tujuan?'failed':''"
          @input="v=>values.lokasi_tujuan=v" :hints="formErrors.lokasi_tujuan" :check="false"
          label="Lokasi Tujuan" placeholder="Autofield Lokasi Tujuan"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.nik" :errorText="formErrors.nik?'failed':''"
          @input="v=>values.nik=v" :hints="formErrors.nik" :check="false"
          label="NIK" placeholder="Autofield NIK"
        />
      </div>
      <div>
        <FieldX
          :bind="{ readonly: true }" class="w-full !mt-3"
          :value="values.pic" :errorText="formErrors.pic?'failed':''"
          @input="v=>values.pic=v" :hints="formErrors.pic" :check="false"
          label="PIC" placeholder="Autofield PIC"
        />
      </div>
      <div>
        <FieldNumber
          class="w-full !mt-3"
          :bind="{ readonly: true }"
          :value="values.total_biaya_spd" @input="(v)=>values.total_biaya_spd=v"
          :errorText="formErrors.total_biaya_spd?'failed':''" 
          :hints="formErrors.total_biaya_spd"
          placeholder="Total Biaya" label="Autofield Total Biaya" :check="false"
        />
      </div>
      <div>
        <FieldX 
          :bind="{ readonly: !actionText }" type="textarea" class="w-full !mt-3"
          :value="values.keterangan" :errorText="formErrors.keterangan?'failed':''"
          @input="v=>values.keterangan=v" :hints="formErrors.keterangan" :check="false"
          label="Keterangan" placeholder="Tuliskan Keterangan"
        />
      </div>
      <div class="flex flex-col">
          <label
            class="inline-block pl-[0.15rem] hover:cursor-pointer font-semibold"
            for="is_kend"
            >Kendaraan Dinas :</label
          >
          <div class="flex w-40">
            <div class="flex-auto">
              <i class="text-red-500">Tidak</i>
            </div>
            <div class="flex-auto">
              <input
                  class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-gray-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                  :class="{'after:bg-blue-500': values.is_kend_dinas === true}"
                  type="checkbox"
                  role="switch"
                  id="is_kend"
                  :disabled="true"
                  v-model="values.is_kend_dinas"
                  
                />
            </div>
            <div class="flex-auto">
              <i class="text-green-500">Iya</i>
            </div>
          </div>
      </div>
      <!-- END COLUMN -->
      <!-- ACTION BUTTON START -->
    </div>

    <div class="mx-4">
      <button :disabled="!actionText" @click="addDetail" type="button" class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2">
        <icon fa="plus" /> <span>Add to List</span>
      </button>
      <div class="mt-4">
        <table class="mx-4">
          <thead>
            <tr class="border">
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 py-[14.5px] text-center w-[5%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[15%] bg-[#f8f8f8] border-[#CACACA]">Tipe</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Biaya</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Keterangan</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Biaya Realisasi</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border bg-[#f8f8f8] border-[#CACACA]">Catatan Realisasi</td>
              <td class="text-[#8F8F8F] font-semibold text-[14px] text-capitalize px-2 text-center border w-[5%] bg-[#f8f8f8] border-[#CACACA]">Aksi</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in detailArr" :key="item.id" class="border-t" v-if="detailArr.length > 0">
              <td class="p-2 text-center border border-[#CACACA]">
                {{ i + 1 }}.
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldSelect
                  :bind="{ disabled: item.isDisabled, clearable:false }" class="!mt-0 w-full"
                  :value="item.tipe_spd_id" @input="v=>item.tipe_spd_id=v"
                  :errorText="formErrors.tipe_spd_id?'failed':''" 
                  label="" placeholder="Pilih Tipe SPD"
                  :hints="formErrors.tipe_spd_id"
                  :api="{
                      url: `${store.server.url_backend}/operation/m_general`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where: `this.group='TIPE SPD' AND this.is_active='true'`
                      }
                  }"
                  valueField="id" displayField="value" :check="false"
                />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber class="!mt-0 w-full"
                  :bind="{ readonly: item.isDisabled }"
                  :value="item.biaya" 
                  @input="(v)=>item.biaya=v"
                  :errorText="formErrors.biaya?'failed':''" 
                  :hints="formErrors.biaya"
                  placeholder="Tuliskan Biaya" label="" :check="false"
                />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldX :bind="{ readonly: item.isDisabled }" class="!mt-0"
                  :value="item.keterangan" @input="v=>item.keterangan=v" label="" placeholder="Tuliskan Keterangan" :check="false"
                />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldNumber
                  :bind="{ readonly: !actionText }" class="!mt-0"
                  :value="item.biaya_realisasi" 
                  @input="(v)=>{
                    item.biaya_realisasi=v
                    countBiayaSelisih()
                    }"
                  placeholder="Tuliskan Biaya" label="" :check="false"
                />
              </td>
              <td class="p-2 border border-[#CACACA]">
                <FieldX :bind="{ readonly: !actionText }" class="!mt-0"
                  :value="item.catatan_realisasi" @input="v=>item.catatan_realisasi=v" placeholder="Tuliskan Catatan Realisasi" label="" :check="false"
                />
              </td>
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
      
      <div class="grid grid-cols-2 mt-6">
        <div v-show="route.query.is_approval"> 
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
      </div>
      
      <div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-overlay fixed inset-0 bg-black opacity-50">
        </div>
        <div class="modal-container bg-white  w-[70%] mx-auto rounded shadow-lg z-50 overflow-y-auto">
          <div class="modal-content py-4 text-left px-6">
            <!-- Modal Header -->
            <div class="modal-header flex items-center justify-between flex-wrap">
              <div class="flex items-center">
                <h3 class="text-xl font-semibold ml-2">Log Approval <span v-if="!dataLog.items.length" class="!text-red-600"> | Belum ada log approval</span></h3>
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
                    <td class="border px-2 py-1 font-medium ">Tanggal Aksi </td>
                    <td class="border px-2 py-1 font-medium ">User Aksi</td>
                    <td class="border px-2 py-1 font-medium ">Catatan</td>
                  </tr>
                </thead>
                <tr class="border" v-for="d,i in dataLog.items" :key="i">
                  <td class="border px-2 py-1">{{ i+1 }}</td>
                  <td class="border px-2 py-1">{{ d.trx_nomor ?? '-' }}</td>
                  <td class="border px-2 py-1">{{ d.action_type ?? '-' }}</td>
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
      <div v-show="route.query.is_approval" class="w-1/2 mt-6">
        <label class="col-span-12 font-semibold">Catatan Approval<label class="text-red-500 space-x-0 pl-0"></label></label>
        <FieldX :bind="{ readonly: false }" class="w-full py-2 !mt-0" :value="values.catatan"
          :errorText="formErrors.catatan?'failed':''" @input="v=>values.catatan=v" :hints="formErrors.catatan"
          :check="false" label="Catatan" placeholder="Tuliskan catatan" />
      </div>
      <!-- FORM END -->
    </div>
      <hr>
    <div class="flex flex-row items-center justify-end space-x-2 p-2">
      <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
      <button 
        class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="route.query.is_approval" 
        @click="onProcess('approve')"
      >
        <icon fa="times" />
        Approve
      </button>
      <button 
        class="bg-rose-600 text-white font-semibold hover:bg-rose-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="route.query.is_approval" 
        @click="onProcess('reject')"
      >
        <icon fa="times" />
        Reject
      </button>
      <button 
        class="bg-amber-600 text-white font-semibold hover:bg-amber-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
        v-show="route.query.is_approval" 
        @click="onProcess('revise')"
      >
        <icon fa="times" />
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
    </div>
  </div>
@endverbatim
@endif