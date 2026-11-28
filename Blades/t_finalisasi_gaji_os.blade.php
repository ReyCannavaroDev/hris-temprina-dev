@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p class="text-sm font-semibold text-gray-700">Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData('DRAFT',1)" :class="activeBtn === 1?'bg-gray-600 text-white hover:bg-gray-400':'border border-gray-600 text-gray-600 bg-white hover:bg-gray-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2.5 text-xs font-semibold">DRAFT</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('IN_APPROVAL',2)" :class="activeBtn === 2?'bg-blue-600 text-white hover:bg-blue-400':'border border-blue-600 text-blue-600 bg-white hover:bg-blue-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2.5 text-xs font-semibold">IN APPROVAL</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('POSTED',3)" :class="activeBtn === 3?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2.5 text-xs font-semibold">POSTED</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData('REJECTED',4)" :class="activeBtn === 4?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2.5 text-xs font-semibold">REJECTED</button>
      </div>
    </div>
    <div v-show="data?.can_create == true || store.user.data.username === 'developer'">
      <RouterLink
        :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="bg-green-600 text-white hover:bg-green-700 rounded-[6px] py-2 px-[12.5px] text-sm font-semibold inline-flex items-center gap-1 shadow-sm">
        <icon fa="plus" /> Buat Finalisasi OS
      </RouterLink>
    </div>
  </div>
  <hr class="my-2">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
  </TableApi>
</div>
@else

@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 md:w-full w-full bg-white">
      <div class="mb-4 flex justify-between items-center flex-wrap gap-2">
        <div class="flex items-center gap-2">
          <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500 text-lg" title="Kembali" @click="onBack" />
          <div>
            <h1 class="text-[22px] font-bold text-gray-800">
              Finalisasi Gaji Outsourcing
            </h1>
            <p class="text-xs text-gray-500">Penyusunan & Pengajuan Finalisasi Gaji Karyawan Outsourcing ke HC Cabang</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span 
            v-if="values.status" 
            :class="{
              'bg-gray-100 text-gray-800 border-gray-300': values.status.toUpperCase() === 'DRAFT',
              'bg-blue-100 text-blue-800 border-blue-300': values.status.toUpperCase() === 'IN_APPROVAL' || values.status.toUpperCase() === 'IN APPROVAL',
              'bg-green-100 text-green-800 border-green-300': values.status.toUpperCase() === 'POSTED',
              'bg-red-100 text-red-800 border-red-300': values.status.toUpperCase() === 'REJECTED'
            }"
            class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1 shadow-sm uppercase"
          >
            <icon :fa="values.status.toUpperCase() === 'POSTED' ? 'lock' : (values.status.toUpperCase() === 'IN_APPROVAL' || values.status.toUpperCase() === 'IN APPROVAL' ? 'clock' : 'tag')" />
            <span>{{ values.status }}</span>
          </span>
        </div>
      </div>
      <hr class="mb-4">

      <div class="grid md:grid-cols-3 grid-cols-1 gap-3">
        <div>
          <FieldX :bind="{ readonly: true }" class="w-full !mt-0" :value="values.nomor"
            type="text" label="Nomor Finalisasi" placeholder="Auto-generate" :check="false" />
        </div>
        <div>
          <FieldX :bind="{ readonly: !canEdit, required: true }" class="w-full !mt-0" :value="values.periode_awal"
            :errorText="formErrors.periode_awal?'failed':''" :hints="formErrors.periode_awal" :check="false"
            type="date" label="Periode Awal" placeholder="Pilih Periode Awal" @input="(v)=>{
              values.periode_awal=v
              detailArr = []
            }" />
        </div>
        <div>
          <FieldX :bind="{ readonly: !canEdit, required: true }" class="w-full !mt-0" :value="values.periode_akhir"
            :errorText="formErrors.periode_akhir?'failed':''" :hints="formErrors.periode_akhir" :check="false"
            type="date" label="Periode Akhir" placeholder="Pilih Periode Akhir" @input="(v)=>{
              values.periode_akhir=v
              detailArr = []
            }" />
        </div>

        <div>
          <label class="font-semibold text-sm">PT Outsourcing <span class="text-red-500">*</span></label>
          <FieldSelect class="w-full py-1 !mt-0" :bind="{ disabled: isOsLocked || !canEdit, clearable: false }" 
            :value="values.m_company_outsourcing_id"
            :check="false" @input="(v)=>{
              values.m_company_outsourcing_id=v
              detailArr = []
            }" :errorText="formErrors.m_company_outsourcing_id?'failed':''" :hints="formErrors.m_company_outsourcing_id" displayField="name" valueField="id"
            :api="{
                url: `${store.server.url_backend}/operation/m_company_outsourcing`,
                headers: {
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  single: true,
                  where: `this.is_active='true'`,
                  transform: false,
                }
            }" fa-icon="search" :check="true" />
        </div>

        <div>
          <label class="font-semibold text-sm">Cabang <span class="text-red-500">*</span></label>
          <FieldSelect class="w-full py-1 !mt-0" :bind="{ disabled: !canEdit, clearable: false }"
            :value="values.m_branch_id" :check="false" @input="(v)=>{
              values.m_branch_id=v
              detailArr = []
            }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id" displayField="name"
            valueField="id" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: {
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  single: true,
                  where: `this.is_active='true'`,
                  transform: false,
                }
            }" fa-icon="search" :check="true" />
        </div>

        <div>
          <label class="font-semibold text-sm">Total Pengeluaran Gaji OS</label>
          <FieldNumber :bind="{ readonly: true }" class="!mt-0 flex justify-end" :value="values.total_pengeluaran_gaji"
            type="number" label="" :check="false" />
        </div>

        <div class="md:col-span-3">
          <label class="font-semibold text-sm">Deskripsi / Catatan Pengajuan <span class="text-red-500">*</span></label>
          <FieldX :bind="{ readonly: !canEdit }" class="!mt-0 w-full py-1" :value="values.desc"
            @input="v=>values.desc=v" :errorText="formErrors.desc?'failed':''" :hints="formErrors.desc" type="textarea"
            label="" placeholder="Masukkan keterangan finalisasi gaji OS..." :check="false" />
        </div>
      </div>

      <!-- Action Tarik Data Gaji OS -->
      <div v-show="canEdit" class="flex flex-row justify-center space-x-[10px] mt-4 mb-2">
        <button :disabled="!canEdit" @click="generatePerhitunganOs" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded shadow-sm flex items-center gap-1 font-semibold">
          <Icon fa="bolt"/> Tarik Data Gaji OS
        </button>
        <button :disabled="!canEdit" @click="detailArr = []" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded shadow-sm flex items-center gap-1">
          <Icon fa="trash"/> Kosongkan Rincian
        </button>
      </div>

      <!-- Summary Info Card -->
      <div class="grid grid-cols-2 gap-4 mt-3 mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
        <div>
          <p class="text-xs text-gray-500 uppercase font-semibold">Total Tenaga Kerja OS</p>
          <p class="text-xl font-bold text-gray-800">{{ detailArr.length }} Orang</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-gray-500 uppercase font-semibold">Total Nilai Gaji Bersih</p>
          <p class="text-xl font-bold text-green-700">{{ formatRupiah(values.total_pengeluaran_gaji) }}</p>
        </div>
      </div>

      <!-- Detail Table -->
      <div class="mt-2 overflow-x-auto">
        <table class="w-full overflow-x-auto table-auto border border-[#CACACA]" style="zoom: 85%">
          <thead>
            <tr class="border">
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 py-2 text-center w-[4%] border bg-[#f8f8f8] border-[#CACACA]">No.</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">Periode</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">NIK</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[22%] border bg-[#f8f8f8] border-[#CACACA]">Nama Karyawan OS</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">PT Outsourcing</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[12%] border bg-[#f8f8f8] border-[#CACACA]">Cabang</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[15%] border bg-[#f8f8f8] border-[#CACACA]">Gaji Bersih (Netto)</td>
              <td class="text-[#8F8F8F] font-semibold text-[13px] px-2 text-center w-[10%] border bg-[#f8f8f8] border-[#CACACA]">Aksi</td>
            </tr>
          </thead>
          <tbody>
            <tr v-if="detailArr.length" v-for="(item, i) in detailArr" :key="item.id || i" class="border-t hover:bg-yellow-50">
              <td class="text-center border border-[#CACACA] px-2 py-1.5">{{ i + 1 }}.</td>
              <td class="text-center border border-[#CACACA] px-2">{{ item.periode }}</td>
              <td class="text-left border border-[#CACACA] px-2 font-mono text-xs">{{ item['m_kary.nik'] }}</td>
              <td class="text-left border border-[#CACACA] px-2 font-semibold">{{ item.karyawan }}</td>
              <td class="text-left border border-[#CACACA] px-2 text-xs">{{ item['m_company_outsourcing.name'] || '-' }}</td>
              <td class="text-left border border-[#CACACA] px-2 text-xs">{{ item['m_branch.name'] || '-' }}</td>
              <td class="text-right border border-[#CACACA] px-2 font-semibold text-green-700">{{ formatRupiah(item.netto) }}</td>
              <td class="border border-[#CACACA] text-center">
                <button @click="openDetail(i)" class="text-blue-600 hover:text-blue-800 p-1" title="Lihat Rincian Gaji">
                  <icon fa="circle-info" size="lg" />
                </button>
              </td>
            </tr>
            <tr v-else class="text-center">
              <td colspan="8" class="py-6 text-gray-400 italic">
                Belum ada data rincian gaji OS. Tentukan periode, PT Outsourcing, dan Cabang, lalu klik "Tarik Data Gaji OS".
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bottom Actions -->
      <div class="flex flex-row justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <button @click="onBack" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded font-semibold text-sm flex items-center gap-1">
          <Icon fa="arrow-left"/> Kembali
        </button>

        <div class="flex items-center gap-3">
          <button v-show="canEdit && (currentMenu?.can_create || currentMenu?.can_update)" @click="onSave" 
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded font-semibold text-sm flex items-center gap-1 shadow-sm">
            <Icon fa="save"/> Simpan Draft
          </button>
          <button v-show="values.id && canEdit && values.status !== 'POSTED'" @click="onSendApproval"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded font-semibold text-sm flex items-center gap-1 shadow-sm">
            <Icon fa="paper-plane"/> Ajukan Approval HC Cabang
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endverbatim
@endif

@verbatim
<!-- Modal Rincian Gaji OS -->
<div v-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50">
  <div class="modal-overlay fixed inset-0 bg-black opacity-50" @click="closeModal"></div>
  <div class="modal-container bg-white w-[80%] mx-auto rounded shadow-lg z-50 overflow-y-auto max-h-[90vh]">
    <div class="modal-content py-4 text-left px-6">
      <div class="modal-header flex items-center justify-between flex-wrap">
        <div>
          <h3 class="text-lg font-bold text-gray-800">Rincian Komponen Gaji: {{ titleOpen }}</h3>
          <p class="text-xs text-gray-500">Breakdown upah harian/borongan dan potongan karyawan outsourcing</p>
        </div>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
      </div>
      <hr class="border-t border-gray-200 my-3">

      <div class="modal-body">
        <div class="grid grid-cols-2 gap-4" :class="{'grid-cols-1': !canEdit}">
          <div>
            <h4 class="font-semibold text-sm text-gray-700 mb-2">Komponen Standar Gaji</h4>
            <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
              <thead>
                <tr class="bg-gray-700 text-white text-xs">
                  <td class="font-bold p-2 text-left w-[65%] border border-gray-600">Komponen</td>
                  <td class="font-bold p-2 text-right w-[35%] border border-gray-600">Besaran</td>
                </tr>
              </thead>
              <tbody>
                <tr class="border" v-for="(a,i) in detailArrOpen.items" :key="i">
                  <td class="text-left border border-gray-300 p-1.5" :class="a.factor == '=' ? 'font-bold bg-gray-100' : ''">{{ a.label }}</td>
                  <td class="text-right border border-gray-300 p-1.5" :class="a.factor == '=' ? 'font-bold bg-gray-100' : ''">
                    {{ (a.factor == '-' ? '(-) ' : '') + formatRupiah(a.value) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-show="canEdit">
            <h4 class="font-semibold text-sm text-gray-700 mb-2">Penyesuaian Gaji</h4>
            <div class="overflow-y-auto max-h-[350px] w-full">
              <table class="w-full overflow-x-auto table-auto border border-[#CACACA] text-sm">
                <thead>
                  <tr class="bg-gray-700 text-white text-xs">
                    <td class="font-bold p-2 text-left w-[50%] border border-gray-600">Komponen</td>
                    <td class="font-bold p-2 text-center w-[15%] border border-gray-600">Faktor</td>
                    <td class="font-bold p-2 text-right w-[35%] border border-gray-600">Besaran</td>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border" v-for="(a,i) in detailArrAdjOpen.items" :key="i">
                    <td class="text-left border border-gray-300 p-1" :class="a.factor == '=' ? 'font-bold bg-gray-100' : ''">
                      <span v-if="a.default === true">{{ a.label }}</span>
                      <div v-else class="flex justify-between items-center">
                        <FieldX :bind="{ readonly: !canEdit }" class="!mt-0 w-full"
                          :value="detailArrAdjOpen.items[i]['label']" @input="v=>detailArrAdjOpen.items[i]['label']=v"
                          placeholder="Label penyesuaian" label="" :check="false" />
                        <Icon fa="x" @click="deleteRow(a)" title="Hapus baris" class="text-red-500 cursor-pointer ml-1 p-1 hover:text-red-700" />
                      </div>
                    </td>
                    <td class="text-center border border-gray-300 p-1" :class="a.factor == '=' ? 'font-bold bg-gray-100' : ''">
                      <span v-if="a.default === true">{{ a.factor }}</span>
                      <FieldSelect v-else :bind="{ disabled: !canEdit, clearable: false }"
                        :value="detailArrAdjOpen.items[i]['factor']" @input="v=>detailArrAdjOpen.items[i]['factor']=v"
                        valueField="key" displayField="key" :options="['+','-']" placeholder="" label="" :check="false" />
                    </td>
                    <td class="text-right border border-gray-300 p-1" :class="a.factor == '=' ? 'font-bold bg-gray-100' : ''">
                      <FieldNumber :bind="{ readonly: !canEdit }" class="!mt-0 flex justify-end !p-0"
                        :value="detailArrAdjOpen.items[i]['value']" @input="(v)=>{
                          detailArrAdjOpen.items[i]['value']=v 
                          summaryAdj()
                        }" type="number" label="" :check="false" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div @click="addRowAdj" class="flex justify-end cursor-pointer hover:text-blue-500 text-xs text-blue-600 font-semibold mt-1">
              <i>+ Tambah Komponen Penyesuaian</i>
            </div>
            <table class="w-full overflow-x-auto mt-2 table-auto border border-[#CACACA] text-sm">
              <tbody>
                <tr class="border bg-gray-100">
                  <td class="text-left border border-gray-300 p-1.5 font-bold">Total Penyesuaian Gaji</td>
                  <td class="text-right border border-gray-300 p-1 font-bold">
                    <FieldNumber :bind="{ readonly: !canEdit }" class="!mt-0 flex justify-end !p-0"
                      :value="totalAdjOpen.value" type="number" label="" :check="false" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="modal-footer flex justify-end mt-4 pt-3 border-t border-gray-200">
        <button @click="closeModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold text-xs px-4 py-2 rounded">
          Tutup
        </button>
        <button v-show="canEdit" @click="saveModal" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs ml-2 px-4 py-2 rounded shadow-sm">
          Terapkan
        </button>
      </div>
    </div>
  </div>
</div>
@endverbatim
