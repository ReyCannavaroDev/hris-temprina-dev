@verbatim
<div class="grid grid-cols-12 gap-x-4 space-x-4 gap-y-4 p-6 rounded-xl">
  <div class="col-span-5 grid-rows-2 space-y-4 h-fit">
    <div class=" bg-white bg-opacity-95 border-t-4 border-[#00AA13] py-4 rounded-lg px-3">
      <div class="flex justify-between p-2">
        <!-- <h3 class="text-lg font-semibold">Calendar</h3> -->
        <FieldX :bind="{ readonly: false }" class="!mt-0" :value="values.month"
          :errorText="formErrors.month?'failed':''" @input="v=>{
              values.month=v
              changeCalendar(v)
              }" :hints="formErrors.month" placeholder="Pilih Bulan" label="" type="month" :check="false" />
      </div>
      <table class="border-collapse text-[#757575] mb-4 w-full">
        <tr>
          <th v-for="day in daysOfWeek" :key="day" class="p-1 font-semibold">{{ day }}</th>
        </tr>
        <tr v-for="row in calendarRows" :key="row" class="">
          <td v-for="cell in row" :key="cell.date"
            :class="{ 'bg-[#00AA13] text-white': cell.isToday, 'cursor-pointer':cell.day }" class="p-1 text-center"
            @click="handleDateClick(cell.date)">{{ cell.day }}</td>
        </tr>
      </table>
      <div class="bg-[#00AA13] h-[1px] mb-2"></div>
      <div class="flex justify-between">
        <i class="text-gray-600">Silahkan Pilih Tanggal Diatas</i>
        <icon fa="times" size="lg mr-0.5 cursor-pointer text-red-600" v-show="isDateSucs" @click="resetAll()" />
      </div>
      
    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mt-2 mb-5">
      <button
        v-for="(item, i) in labelArr"
        :key="i"
        @click="getDate(item.id)"
        :class="values.id == item.id ? 'ring-4 outline-none ring-green-300' : ''"
        class="relative w-full bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2 flex justify-between items-center"
      >
        <span>{{ item.keterangan }}</span>
        <span
          @click.stop="hapusLabel(item)"
          class="text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2"
        >
          ✕
        </span>
      </button>
    </div>



      <!-- PILIH SHIFT -->
      <div class="grid grid-cols-4 gap-x-2 items-center">
        <div class="col-span-3">
          <FieldSelect :bind="{ clearable: false }" :value="values.selectedShiftId"
            @input="v => values.selectedShiftId = v" @update:valueFull="v => values.selectedShiftData = v"
            placeholder="Pilih Shift" label="" valueField="id" displayField="desc" :api="{
        url: `${store.server.url_backend}/operation/m_jam_kerja`,
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        params: {
          simplest: true,
          transform: true,
          join: false,
          selectfield : 'id,desc,waktu_mulai,waktu_akhir'
        }
      }" />
        </div>

        <div class="col-span-1 flex  justify-left items-center">
          <button
              type="button"
              class="w-9 h-9 bg-blue-500 hover:bg-blue-600 text-white rounded-xl shadow-lg flex items-center justify-center transition"
              @click="addShift"
            >
              <i class="fas fa-plus">+</i> <!-- fa => fas (untuk versi 6+) -->
          </button>
        </div>
      </div>

      <!-- TABEL SHIFT -->
      <div class="mt-4">
        <table class="col-span-4 w-full mt-4">
          <thead>
            <tr>
              <th class="text-left px-2 py-1">Jam Kerja</th>
              <th class="text-left px-2 py-1">Jam Mulai</th>
              <th class="text-left px-2 py-1">Jam Selesai</th>
              <th class="text-left px-2 py-1">Tipe Hari</th>
              <th class="text-center px-2 py-1">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(shift, index) in shiftList" :key="shift.id" class="cursor-pointer hover:bg-gray-100 transition"
              @click="openSubDetail(shift)">
              <td class="px-2 py-2">{{ shift.nama }}</td>
              <td class="px-2 py-2">{{ shift.waktu_mulai }}</td>
              <td class="px-2 py-2">{{ shift.waktu_akhir }}</td>
              <td class="px-2 py-2">
                <div class="-mt-5">
                  <FieldSelect :value="shift.tipe_hari" @input="v => shift.tipe_hari = v"
                    :errorText="formErrors.tipe_hari ? 'failed' : ''" :hints="formErrors.tipe_hari" valueField="id"
                    displayField="key" :options="[
            { id: 'Libur', key: 'Libur' },
            { id: 'Kerja', key: 'Kerja' }
          ]" placeholder="" fa-icon="" :check="false" />
                </div>
              </td>
              <td class="px-2 py-2 text-center">
                <button
        @click.stop="removeShift(index)"
        class="hover:opacity-80 transition"
        title="Hapus Shift"
      >
        <svg
          width="14"
          height="14"
          viewBox="0 0 14 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
            fill="#F24E1E"
          />
        </svg>
      </button>
              </td>
            </tr>

            <tr v-if="shiftList.length === 0">
              <td colspan="5" class="text-center text-gray-500 py-4">
                Tidak ada data
              </td>
            </tr>
          </tbody>

        </table>
      </div>

    </div>
  </div>

  <div class=" col-span-7 h-fit">
    <div class="bg-white border-t-4 border-[#00AA13] grid grid-cols-2 gap-x-2 rounded-b-lg rounded-lg p-4">
      <div class="flex justify-between col-span-2 mb-5">
        <h1 class="font-semibold text-lg">Form Jadwal Kerja</h1>
      </div>

      <div>
        <label class="font-semibold">Nomor<span class="text-red-500 space-x-0 pl-0">*</span></label>
        <FieldX :bind="{ readonly: true }" class="w-full py-2 !mt-0" :value="values.nomor" :errorText="formErrors.nomor?'failed':''"
          @input="v=>values.nomor=v" :hints="formErrors.nomor" placeholder="Auto Generate By System" label="" fa-icon="" :check="false" />
      </div>
      <div>
        <label class="font-semibold">Status<span class="text-red-500 space-x-0 pl-0">*</span></label>
        <FieldX class="w-full py-2 !mt-0" :bind="{ readonly: true }" :value="values.status" :errorText="formErrors.status?'failed':''"
          @input="v=>values.status=v" :hints="formErrors.status" placeholder="Draft" label="" fa-icon=""
          :check="false" />
      </div>

      <div>
        <label class="font-semibold">Divisi<span class="text-red-500 space-x-0 pl-0">*</span></label>
        <FieldSelect class="w-full py-2 !mt-0" :value="values.m_divisi_id" @input="v=>values.m_divisi_id=v"
          :errorText="formErrors.m_divisi_id?'failed':''" :hints="formErrors.m_divisi_id" valueField="id"
          displayField="name" :api="{
              url: `${store.server.url_backend}/operation/m_divisi`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
              }
          }" placeholder="Pilih Divisi" label="" fa-icon="" :check="false" />
      </div>
      <div>
        <label class="font-semibold">Departement<span class="text-red-500 space-x-0 pl-0">*</span></label>
        <FieldSelect class="w-full py-2 !mt-0" :value="values.m_dept_id" @input="v=>values.m_dept_id=v"
          :errorText="formErrors.m_dept_id?'failed':''" :hints="formErrors.m_dept_id" valueField="id"
          displayField="nama" :api="{
              url: `${store.server.url_backend}/operation/m_dept`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: {
              }
          }" placeholder="Pilih Departement" label="" fa-icon="" :check="false" />
      </div>

      <div>
        <label class="font-semibold">Keterangan<span class="text-red-500 space-x-0 pl-0">*</span></label>
        <FieldX class="w-full py-2 !mt-0" :value="values.keterangan" :errorText="formErrors.keterangan?'failed':''"
          @input="v=>values.keterangan=v" :hints="formErrors.keterangan" placeholder="Tulis Keterangan" label=""
          fa-icon="" :check="false" />

      </div>

      <!-- Sub-detail karyawan, hanya tampil jika shift dipilih -->
      <div class="col-span-2 mt-4">
        <div class="flex justify-between items-start mb-2 col-span-2">
          <div>
            <h2 class="font-semibold text-base mb-1">Daftar Karyawan</h2>
            <ButtonMultiSelect title="Add Detail" @add="tambahKaryawan" :api="{
                    url: `${store.server.url_backend}/operation/m_kary`,
                    headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
                    params: {  
                      join:true,
                      //notin: detailArr.length > 0 ? `this.id:${detailArr.map(dt => dt.m_item_id).join(',')}` : null,
                      //scopes: 'FilterType,WithCatName,FilterWarehouse',
                      //wh_id: values.wh_id,
                      //type_id: values.item_type_id,
                      //where : `this.item_is_active= true AND this.wh_is_active = true AND this.type_is_active = true AND this.unit_2_is_active = true AND this.unit_is_active = true`
                    },
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
                      flex: 2,
                      headerName:'Nama Lengkap',
                      sortable: false, resizable: true, filter: false,
                      field: 'nama_lengkap',
                      cellClass: ['justify-center','!border-gray-200']
                    }
                    ]">
              <div class="flex items-center space-x-2">
                <div
                  class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
                  <icon fa="plus" />
                  Add To List
                </div>
              </div>
            </ButtonMultiSelect>
          </div>

          <div class="text-right">
            <p v-if="selectedShift?.day && selectedShift?.tanggal" class="text-sm text-gray-700">
              {{ selectedShift.day }}, {{ selectedShift.tanggal }}
            </p>
            <p v-if="selectedShift" class="text-sm text-gray-600">
              {{selectedShift.nama}} {{ selectedShift.kode }} : {{ selectedShift.waktu_mulai }} - {{
              selectedShift.waktu_akhir }}
            </p>


          </div>

        </div>


        <table class="w-full border border-gray-200 rounded overflow-hidden text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-2 py-2 text-center">No</th>
              <th class="px-2 py-2 text-center">Divisi</th>
              <th class="px-2 py-2 text-center">Departemen</th>
              <th class="px-2 py-2 text-center">NIK</th>
              <th class="px-2 py-2 text-center">Nama</th>
              <th class="px-2 py-2 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="karyawanList.length === 0">
              <td colspan="6" class="text-center text-gray-500 py-4">Belum ada karyawan</td>
            </tr>
            <tr v-for="(item, i) in karyawanList" :key="i">
              <td class="px-2 py-2 text-center">{{ i + 1 }}</td>
              <td class="px-2 py-2 text-center">
                {{item.divisi}}
                <!-- <FieldX :value="item.divisi" :errorText="formErrors.divisi?'failed':''" @input="v=>item.divisi=v"
                  :hints="formErrors.divisi" placeholder="" label="" fa-icon="" :check="false" /> -->
              </td>
              <td class="px-2 py-2 text-center">
                {{item.dept}}
                <!-- <FieldX :value="item.dept" :errorText="formErrors.dept?'failed':''" @input="v=>item.dept=v"
                  :hints="formErrors.dept" placeholder="" label="" fa-icon="" :check="false" /> -->
              </td>
              <td class="px-2 py-2 text-center">
                {{item.nik}}
                <!-- <FieldX :value="item.nik" :errorText="formErrors.nik?'failed':''" @input="v=>item.nik=v"
                  :hints="formErrors.nik" placeholder="" label="" fa-icon="" :check="false" /> -->
              </td>
              <td class="px-2 py-2 text-center">
                {{item.nama}}
                <!-- <FieldX :value="item.nama" :errorText="formErrors.nama?'failed':''" @input="v=>item.nama=v"
                  :hints="formErrors.nama" placeholder="" label="" fa-icon="" :check="false" /> -->
              </td>
              <td class="px-2 py-2 text-center">
                <button class="col-start-10 items-center text-white py-3 px-1 rounded" type="button" @click="hapusKaryawan(i)">
                  <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path id="Vector" d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>


      <div class="flex justify-end mt-4 col-span-2">
        <button @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
        </button>
      </div>
    </div>

  </div>

</div>
@endverbatim