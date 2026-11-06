@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan Klaim Rawat Inap
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        <div>
          <label class="font-semibold">Tipe Export</label>
          <FieldSelect 
            :bind="{ readonly: false }" 
            class="w-full py-2 !mt-0"
            :value="values.tipe" 
            :errorText="formErrors.tipe ? 'failed' : ''"
            @input="v => values.tipe = v" 
            :hints="formErrors.tipe" 
            :check="false"
            label=""
            :options="['Excel','PDF','HTML']"
            placeholder="Pilih Tipe Export"
            valueField="key" 
            displayField="key"
          />
        </div>

        <div>
          <label class="font-semibold">Filter Berdasarkan</label>
          <FieldSelect 
            :bind="{ readonly: false, clearable: false }" 
            class="w-full py-2 !mt-0"
            :value="values.filter_type" 
            :errorText="formErrors.filter_type ? 'failed' : ''"
            @input="v => {
              values.filter_type = v;
              resetFilters();
            }" 
            :hints="formErrors.filter_type" 
            :check="false"
            label=""
            :options="[
              {key: 'transaksi', value: 'Per Transaksi'},
              {key: 'periode', value: 'Per Periode'}
            ]"
            placeholder="Pilih Kategori Filter"
            valueField="key" 
            displayField="value"
          />
        </div>

        <!-- FILTER TRANSAKSI -->
        <div v-if="values.filter_type === 'transaksi'" class="col-span-2 grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px]">
          <div>
            <label class="font-semibold">Karyawan</label>
            <FieldPopup
              :value="values.m_kary_id" 
              :errorText="formErrors.m_kary_id ? 'failed' : ''"
              @input="v => values.m_kary_id = v" 
              :hints="formErrors.m_kary_id" 
              class="w-full py-2 !mt-0"
              valueField="id" 
              displayField="nama_lengkap"
              :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest: true,
                  where: `this.is_active=true`,
                  searchfield: 'this.nik, this.nama_lengkap, this.nama_depan, this.nama_belakang'
                }
              }"
              placeholder="Cari Karyawan" label="" :check="false" 
              :columns="[
                {
                  headerName: 'No',
                  valueGetter: (p) => p.node.rowIndex + 1,
                  width: 60,
                  sortable: false, resizable: false, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                {
                  flex: 1,
                  field: 'nik',
                  headerName: 'NIK',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-end']
                },
                {
                  flex: 2,
                  field: 'nama_lengkap',
                  headerName: 'Nama Karyawan',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-start']
                }
              ]"
            />
          </div>
          <div>
            <label class="font-semibold">Kode Klaim Rawat Inap</label>
            <FieldX 
              type="text"
              :bind="{ readonly: false }" 
              class="w-full py-2 !mt-0" 
              :value="values.nomor" 
              label="" 
              placeholder="Masukkan nomor transaksi klaim" 
              :errorText="formErrors.nomor ? 'failed' : ''"
              @input="v => values.nomor = v" 
              :hints="formErrors.nomor" 
              :check="false"
            />
          </div>
        </div>

        <!-- FILTER PERIODE -->
        <div v-if="values.filter_type === 'periode'" class="col-span-2 grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px]">
          <div>
            <label class="font-semibold">Tanggal Awal</label>
            <FieldX 
              type="date"
              :bind="{ readonly: false }" 
              class="w-full py-2 !mt-0" 
              :value="values.periode_awal" 
              label="" 
              placeholder="Pilih Tanggal Awal" 
              :errorText="formErrors.periode_awal ? 'failed' : ''"
              @input="v => values.periode_awal = v" 
              :hints="formErrors.periode_awal" 
              :check="false"
            />
          </div>
          <div>
            <label class="font-semibold">Tanggal Akhir</label>
            <FieldX 
              type="date"
              :bind="{ readonly: false }" 
              class="w-full py-2 !mt-0" 
              :value="values.periode_akhir" 
              label="" 
              placeholder="Pilih Tanggal Akhir" 
              :errorText="formErrors.periode_akhir ? 'failed' : ''"
              @input="v => values.periode_akhir = v" 
              :hints="formErrors.periode_akhir" 
              :check="false"
            />
          </div>
        </div>

      </div>

      <div class="flex flex-row justify-end space-x-[20px] mt-[1em] px-4">
        <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
          {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
        </button>
      </div>

      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr class="mb-4">
        <div id="exportTable">
        </div>
      </div>
    </div>
  </div>
</div>
@endverbatim
