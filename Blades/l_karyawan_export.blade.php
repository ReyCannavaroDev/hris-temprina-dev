@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Laporan Karyawan
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-x-[60px] gap-y-[12px] px-4">
        <!-- START COLUMN -->
        
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-semibold">Company</label>
            <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_comp_id"
              :errorText="formErrors.m_comp_id ? 'failed' : ''" @input="v => values.m_comp_id = v"
              :hints="formErrors.m_comp_id" :check="false" label="" placeholder="Pilih Cabang" valueField="id"
              displayField="name" :api="{
                      url: `${store.server.url_backend}/operation/m_comp`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          single: true,
                          join: false,                    
                          where: `this.is_active='true'`
                      }
                  }" />
          </div>
          <div>
            <label class="font-semibold">Sub</label>
            <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_subcomp_id"
              :errorText="formErrors.m_subcomp_id ? 'failed' : ''" @input="v => values.m_subcomp_id = v"
              :hints="formErrors.m_subcomp_id" :check="false" label="" placeholder="Pilih Divisi" valueField="id"
              displayField="name" :api="{
                      url: `${store.server.url_backend}/operation/m_subcomp`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          where: `this.is_active='true' AND this.m_comp_id = ${values.m_comp_id}`
                      }
                  }" />
          </div>

        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-semibold">Cabang</label>
            <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_branch_id"
              :errorText="formErrors.m_branch_id ? 'failed' : ''" @input="v => values.m_branch_id = v"
              :hints="formErrors.m_branch_id" :check="false" label="" placeholder="Pilih Cabang" valueField="id"
              displayField="name" :api="{
                      url: `${store.server.url_backend}/operation/m_branch`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          single: true,
                          join: false,                    
                          where: `this.is_active='true' AND this.m_subcomp_id = ${values.m_subcomp_id}`
                      }
                  }" />
          </div>
          <div>
            <label class="font-semibold">Divisi</label>
            <FieldSelect :bind="{ readonly: !actionText }" class="w-full py-2 !mt-0" :value="values.m_divisi_id"
              :errorText="formErrors.m_divisi_id ? 'failed' : ''" @input="v => values.m_divisi_id = v"
              :hints="formErrors.m_divisi_id" :check="false" label="" placeholder="Pilih Divisi" valueField="id"
              displayField="name.value" :api="{
                      url: `${store.server.url_backend}/operation/m_divisi`,
                      headers: { 
                          'Content-Type': 'Application/json', 
                          Authorization: `${store.user.token_type} ${store.user.token}`
                      },
                      params: {
                          scopes:'Name',
                          where: `this.is_active='true' AND this.m_branch_id = ${values.m_branch_id}`
                      }
                  }" />
          </div>

        </div>
        <!-- <div>
          <label class="font-semibold">Karyawan</label>
          <FieldPopup :bind="{ readonly: false }" class="col-span-12 !mt-2 w-full" :value="values.m_kary_id"
            @input="(v)=>values.m_kary_id=v" :errorText="formErrors.m_kary_id ? 'failed' : ''"
            :hints="formErrors.m_kary_id" valueField="id" displayField="nama_lengkap" :api="{
                url: `${store.server.url_backend}/operation/m_kary`,
                headers: {
                  'Content-Type': 'Application/json',
                  Authorization: `${store.user.token_type} ${store.user.token}`
                },
                params: {
                  scopes: 'nonos,respo',
                  simplest: true,
                  where: buildWhereKary(),
                  searchfield: 'this.kode, this.nama_lengkap, this.nama_depan, this.nama_belakang, m_zona.nama, m_dir.nama, m_divisi.nama, m_dept.nama'
                }
              }" placeholder="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'kode',
                wrapText:true,
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-end']
              },
              {
                flex: 1,
                field: 'nama_lengkap',
                wrapText:true,
                headerName: 'Nama Karyawan',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                field: 'm_zona.nama',
                wrapText:true,
                headerName: 'Zona',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_dir.nama',
                headerName: 'Direktorat',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_divisi.nama',
                headerName: 'Divisi',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              {
                flex: 1,
                wrapText:true,
                field: 'm_dept.nama',
                headerName: 'Departemen',
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-start']
              },
              ]" />
        </div> -->


      </div>
      <div class="flex flex-row justify-end space-x-[20px] mt-[1em]">
        <button @click="onGenerate" class="bg-green-600 hover:bg-green-800 duration-300 text-white px-[36.5px] py-[12px] rounded-[6px] ">
            {{ values.tipe?.toLowerCase() === 'html' ? 'View' : 'Export' }}
          </button>
      </div>
      <!-- END COLUMN -->
      <!-- ACTION BUTTON START -->
      <div class="overflow-x-auto mt-6 mb-4 px-4" v-show="exportHtml">
        <hr>
        <div id="exportTable" class="w-[100%] mt-6 h-screen overflow-auto">
        </div>
      </div>

    </div>

  </div>

</div>
</div>
@endverbatim