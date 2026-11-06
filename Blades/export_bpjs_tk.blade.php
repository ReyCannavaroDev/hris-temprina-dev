
@verbatim
  <div class="flex flex-col gap-y-3">
    <div class="flex gap-x-4 px-2">
      <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
        <div class="mb-4">
          <h1 class="text-[24px] mb-4 font-bold">
            Report BPJS Ketenagakerjaan
          </h1>
          <hr>
        </div>
        <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
          <!-- START COLUMN -->
          <div>
            <label class="font-semibold">Tipe Export</label>
            <FieldSelect
            :bind="{ readonly: !actionText }"
            class="w-full py-2 !mt-0"
            :value="values.tipe"
            :errorText="formErrors.tipe ? 'failed' : ''"
            @input="v => values.tipe = v"
            :hints="formErrors.tipe"
            :check="false"

            label=""
            :options="['excel']"
            placeholder="Pilih Tipe Export"
            valueField="key"
            displayField="key"
            />
          </div>

          <div>
            <label class="font-semibold text-sm">Pilih Periode Final Gaji</label>
            <ButtonMultiSelect 
              title="Pilih Final Gaji" 
              ref="multiSelectGaji" 
              :api="{
                url: `${store.server.url_backend}/operation/t_final_gaji`,
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  simplest: true,
                  //scopes: 'owndata',
                  searchfield: 'this.desc, this.nomor, this.periode_awal, this.periode_akhir'
                }
              }" 
              :bind="{ readonly: false, searchable: true }" 
              valueField="id" 
              placeholder="Pilih satu atau lebih periode gaji..." 
              :check="true" 
              @add="(selectedRows) => {
                values.f_id = selectedRows.map(item => item.id);
              }"              :columns="[
                { 
                  headerName: 'No', 
                  valueGetter: (p) => p.node.rowIndex + 1, 
                  width: 60, 
                  cellClass: ['justify-center', 'bg-gray-50'] 
                },
                { 
                  flex: 1, 
                  field: 'nomor', 
                  headerName: 'Nomor', 
                  cellClass: ['border-r', '!border-gray-200', 'justify-start'], 
                  searchable: true 
                },
                { 
                  flex: 1.5, 
                  field: 'desc', 
                  headerName: 'Keterangan', 
                  cellClass: ['border-r', '!border-gray-200', 'justify-start'], 
                  searchable: true 
                },
                { 
                  flex: 1, 
                  field: 'periode_awal', 
                  headerName: 'Awal', 
                  cellClass: ['border-r', '!border-gray-200', 'justify-center'], 
                  searchable: true 
                },
                { 
                  flex: 1, 
                  field: 'periode_akhir', 
                  headerName: 'Akhir', 
                  cellClass: ['border-r', '!border-gray-200', 'justify-center'], 
                  searchable: true 
                }
              ]"
            >
              <div class="flex items-center justify-between w-full px-3 py-2 mt-2 border rounded-md bg-white cursor-pointer hover:border-blue-500 transition-all shadow-sm">
                <span v-if="!values.f_id || values.f_id.length === 0" class="text-gray-400 text-sm">
                  Belum ada periode dipilih
                </span>
                <span v-else class="text-blue-600 font-bold text-sm">
                  {{ values.f_id.length }} Periode Terpilih
                </span>
                <icon fa="chevron-down" size="xs text-gray-400" />
              </div>
            </ButtonMultiSelect>
          </div>

          <div>
            <label class="font-semibold text-sm">Pilih Cabang</label>
            <FieldSelect :bind="{ disabled: false , clearable:true }" class="w-full !mt-2" :value="values.m_branch_id"
              @input="v=>{
              if(v){
                values.m_branch_id=v
              }else{
                values.m_branch_id=null
             
              }
            }" @update:valueFull="obj => {
                if (obj) {
                  values.m_branch_id = obj.id; 
                
                } else {
                  values.m_branch_id = null;
                }
              }" :errorText="formErrors.m_branch_id?'failed':''" :hints="formErrors.m_branch_id" valueField="id"
              displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_branch`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false,
                }
          }" fa-icon="sort-desc" :check="false" />
          </div>

          <div>
            <label class="font-semibold">Tipe Template</label>
            <FieldSelect
            :bind="{ readonly: !actionText }"
            class="w-full py-2 !mt-0"
            :value="values.tipe_template"
            :errorText="formErrors.tipe_template ? 'failed' : ''"
            @input="v => values.tipe_template = v"
            :hints="formErrors.tipe_template"
            :check="false"

            label=""
            :options="['Karyawan','Coscentre']"
            placeholder="Pilih Tipe Export"
            valueField="key"
            displayField="key"
            />
          </div>

          <!-- <div >
            <label class="font-semibold">Pilih Final Gaji</label>
            <FieldPopup
            :value="values.f_id"
            :errorText="formErrors.f_id ? 'failed' : ''"
            @input="v => values.f_id = v"
            :hints="formErrors.f_id"
            class="w-full py-2 !mt-0"
            valueField="id"
            displayField="nomor"
            :api="{
            url: `${store.server.url_backend}/operation/t_final_gaji`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
            simplest:true,
            scopes: 'owndata',
            searchfield: 'this.desc, this.nomor, this.periode_awal, this.periode_akhir'
            }
            }"
            placeholder="" label="" :check="false"
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
            wrapText:true,
            sortable: false, resizable: true, filter: 'ColFilter',
            cellClass: ['border-r', '!border-gray-200', 'justify-end']
            },
            {
            flex: 1,
            field: 'desc',
            wrapText:true,
            headerName: 'Nama Karyawan',
            sortable: false, resizable: true, filter: 'ColFilter',
            cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
            flex: 1,
            field: 'periode_awal',
            wrapText:true,
            headerName: 'Periode Awal',
            sortable: false, resizable: true, filter: 'ColFilter',
            cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
            flex: 1,
            wrapText:true,
            field: 'periode_akhir',
            headerName: 'Periode Akhir',
            sortable: false, resizable: true, filter: 'ColFilter',
            cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            ]"
            />
          </div> -->
        </div>
        <!-- END COLUMN -->
        <div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
          <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
            {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
          </button>
        </div>
        <!-- ACTION BUTTON START -->
        <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
          <hr>
          <div id="exportTable">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endverbatim