@verbatim
	<div class="flex flex-col gap-y-3">
		<div class="flex gap-x-4 px-2">
			<div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
				<div class="mb-4">
					<h1 class="text-[24px] mb-4 font-bold">
						Laporan Klaim Askes
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
						if (values.filter_type !== v) {
						values.filter_type = v;
						resetFilters();
						}
						}"
						:hints="formErrors.filter_type"
						:check="false"
						label=""
						:options="[
						{key: 'semua', value: 'Semua Transaksi'},
						{key: 'transaksi', value: 'Per Transaksi'},
						{key: 'minggu', value: 'Per Minggu'},
						{key: 'bulan', value: 'Per Bulan'},
						{key: 'tahun', value: 'Per Tahun'}
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
							<label class="font-semibold">Kode Klaim Askes</label>
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
						<div>
							<label class="font-semibold">Periode Awal</label>
							<FieldX
							type="date"
							:bind="{ readonly: false }"
							class="w-full py-2 !mt-0"
							:value="values.periode_awal"
							label=""
							:check="false"
							@input="v => values.periode_awal = v"
							/>
						</div>
						<div>
							<label class="font-semibold">Periode Akhir</label>
							<FieldX
							type="date"
							:bind="{ readonly: false }"
							class="w-full py-2 !mt-0"
							:value="values.periode_akhir"
							label=""
							:check="false"
							@input="v => values.periode_akhir = v"
							/>
						</div>
					</div>

					<!-- FILTER MINGGU -->
					<div v-if="values.filter_type === 'minggu'" class="col-span-2 grid <md:grid-cols-1 grid-cols-3 gap-x-[30px] gap-y-[12px]">
						<div>
							<label class="font-semibold">Bulan</label>
							<FieldSelect
							:bind="{ readonly: false, clearable: false }"
							class="w-full py-2 !mt-0"
							:value="values.bulan"
							:errorText="formErrors.bulan ? 'failed' : ''"
							@input="v => values.bulan = v"
							:hints="formErrors.bulan"
							:check="false"
							label=""
							:options="[
							{key: '1', value: 'Januari'},
							{key: '2', value: 'Februari'},
							{key: '3', value: 'Maret'},
							{key: '4', value: 'April'},
							{key: '5', value: 'Mei'},
							{key: '6', value: 'Juni'},
							{key: '7', value: 'Juli'},
							{key: '8', value: 'Agustus'},
							{key: '9', value: 'September'},
							{key: '10', value: 'Oktober'},
							{key: '11', value: 'November'},
							{key: '12', value: 'Desember'}
							]"
							placeholder="Pilih Bulan"
							valueField="key"
							displayField="value"
							/>
						</div>
						<div>
							<label class="font-semibold">Tahun</label>
							<FieldSelect
							:bind="{ readonly: false, clearable: false }"
							class="w-full py-2 !mt-0"
							:value="values.tahun"
							:errorText="formErrors.tahun ? 'failed' : ''"
							@input="v => values.tahun = v"
							:hints="formErrors.tahun"
							:check="false"
							label=""
							:options="yearsList"
							placeholder="Pilih Tahun"
							valueField="key"
							displayField="value"
							/>
						</div>
						<div>
							<label class="font-semibold">Periode Tanggal</label>
							<div class="flex flex-col mt-2 select-none">
								<div class="flex items-center cursor-pointer" style="height: 25px;" @click="values.minggu = '1'">
									<div style="width: 25px; height: 25px; border: 1px solid #000; box-sizing: border-box;" :style="{ backgroundColor: values.minggu === '1' ? '#a6a6a6' : '#ffffff' }"></div>
									<span class="ml-3 text-[11pt] font-serif" style="color: #000;">Tgl 01 - 07</span>
								</div>
								<div class="flex items-center cursor-pointer" style="height: 25px;" @click="values.minggu = '2'">
									<div style="width: 25px; height: 25px; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; box-sizing: border-box;" :style="{ backgroundColor: values.minggu === '2' ? '#a6a6a6' : '#ffffff' }"></div>
									<span class="ml-3 text-[11pt] font-serif" style="color: #000;">Tgl 08 - 14</span>
								</div>
								<div class="flex items-center cursor-pointer" style="height: 25px;" @click="values.minggu = '3'">
									<div style="width: 25px; height: 25px; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; box-sizing: border-box;" :style="{ backgroundColor: values.minggu === '3' ? '#a6a6a6' : '#ffffff' }"></div>
									<span class="ml-3 text-[11pt] font-serif" style="color: #000;">Tgl 15 - 22</span>
								</div>
								<div class="flex items-center cursor-pointer" style="height: 25px;" @click="values.minggu = '4'">
									<div style="width: 25px; height: 25px; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; box-sizing: border-box;" :style="{ backgroundColor: values.minggu === '4' ? '#a6a6a6' : '#ffffff' }"></div>
									<span class="ml-3 text-[11pt] font-serif" style="color: #000;">Tgl 23 - 31</span>
								</div>
							</div>
						</div>
					</div>

					<!-- FILTER BULAN -->
					<div v-if="values.filter_type === 'bulan'" class="col-span-2 grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px]">
						<div>
							<label class="font-semibold">Bulan</label>
							<FieldSelect
							:bind="{ readonly: false, clearable: false }"
							class="w-full py-2 !mt-0"
							:value="values.bulan"
							:errorText="formErrors.bulan ? 'failed' : ''"
							@input="v => values.bulan = v"
							:hints="formErrors.bulan"
							:check="false"
							label=""
							:options="[
							{key: '1', value: 'Januari'},
							{key: '2', value: 'Februari'},
							{key: '3', value: 'Maret'},
							{key: '4', value: 'April'},
							{key: '5', value: 'Mei'},
							{key: '6', value: 'Juni'},
							{key: '7', value: 'Juli'},
							{key: '8', value: 'Agustus'},
							{key: '9', value: 'September'},
							{key: '10', value: 'Oktober'},
							{key: '11', value: 'November'},
							{key: '12', value: 'Desember'}
							]"
							placeholder="Pilih Bulan"
							valueField="key"
							displayField="value"
							/>
						</div>
						<div>
							<label class="font-semibold">Tahun</label>
							<FieldSelect
							:bind="{ readonly: false, clearable: false }"
							class="w-full py-2 !mt-0"
							:value="values.tahun"
							:errorText="formErrors.tahun ? 'failed' : ''"
							@input="v => values.tahun = v"
							:hints="formErrors.tahun"
							:check="false"
							label=""
							:options="yearsList"
							placeholder="Pilih Tahun"
							valueField="key"
							displayField="value"
							/>
						</div>
					</div>

					<!-- FILTER TAHUN -->
					<div v-if="values.filter_type === 'tahun'">
						<label class="font-semibold">Tahun</label>
						<FieldSelect
						:bind="{ readonly: false, clearable: false }"
						class="w-full py-2 !mt-0"
						:value="values.tahun"
						:errorText="formErrors.tahun ? 'failed' : ''"
						@input="v => values.tahun = v"
						:hints="formErrors.tahun"
						:check="false"
						label=""
						:options="yearsList"
						placeholder="Pilih Tahun"
						valueField="key"
						displayField="value"
						/>
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
