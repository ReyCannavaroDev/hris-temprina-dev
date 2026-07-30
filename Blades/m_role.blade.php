<!-- LANDING -->
@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-10 border-gray-500">
  <div class="flex justify-between items-center px-2.5 py-1">
    <div class="flex items-center gap-x-4">
      <p>Filter Status :</p>
      <div class="flex gap-x-2">
        <button @click="filterShowData(true,1)" :class="activeBtn === 1?'bg-green-600 text-white hover:bg-green-400':'border border-green-600 text-green-600 bg-white  hover:bg-green-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Aktif</button>
        <div class="flex my-auto h-4 w-0.5 bg-[#6E91D1]"></div>
        <button @click="filterShowData(false,2)" :class="activeBtn === 2?'bg-red-600 text-white hover:bg-red-400':'border border-red-600 text-red-600 bg-white  hover:bg-red-600 hover:text-white'" class="duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">Inaktif</button>
      </div>
    </div>
    <div v-show="currentMenu?.can_create">
      <RouterLink :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="border border-blue-600 text-blue-600 bg-white  hover:bg-blue-600 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
        Create New
      </RouterLink>
    </div>
  </div>
  <hr>
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
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
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali"
        @click="onBack" />
      <div>
        <h1 class="text-20px font-bold">Form Role</h1>
        <p class="text-gray-100">Untuk konfigurasi role setiap user pada sistem</p>
      </div>
    </div>
  </div>
  <div class="p-4 grid <md:grid-cols-1 grid-cols-3 gap-2 ">
    <!-- START COLUMN -->
    <div>
      <FieldX :bind="{ readonly: true }" class="w-full !mt-3" :value="values.code"
        :errorText="formErrors.code?'failed':''" @input="v=>values.code=v" :hints="formErrors.code" label="Kode"
        placeholder="Autogenerate by System" :check="false" />
    </div>
    <div>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.name"
        :errorText="formErrors.name?'failed':''" @input="v=>values.name=v" :hints="formErrors.name" :check="true"
        label="Nama" placeholder="Nama | Ex : Role Admin" />
    </div>

    <!-- STATUS SUPER ADMIN-->
    <div class="pl-3 flex flex-col justify-center !mt-3">
      <span class="text-[15px] font-serif italic">Super Admin</span>
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">False</i>
        </div>
        <div class="flex-auto">
          <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.is_superadmin === false}"
                role="switch"
                id="is_superadmin_for_click"
                :disabled="!actionText"
                v-model="values.is_superadmin"
                />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">True</i>
        </div>
      </div>
    </div>

    <!-- STATUS -->
    <div class="pl-3 flex flex-col justify-center !mt-3">
      <div class="flex w-40">
        <div class="flex-auto">
          <i class="text-red-500">InActive</i>
        </div>
        <div class="flex-auto">
          <input
                class="mr-2 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-blue-500 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]"
                type="checkbox"
                :class="{'after:bg-gray-500': values.is_active === false}"
                role="switch"
                id="is_active_for_click"
                :disabled="!actionText"
                v-model="values.is_active"
                />
        </div>
        <div class="flex-auto">
          <i class="text-green-500">Active</i>
        </div>
      </div>
    </div>
    <!-- ACTION BUTTON START -->
  </div>

  <!-- TABLE -->

  <!-- START TABLE DETAIL -->
  <hr class="<md:col-span-1 col-span-3">
  <div class=" p-4">
    <div class="p-4 flex items-end" v-if="actionText">
      <ButtonMultiSelect title="Tambah Akses" @add="onDetailAdd" :api="{
            url: `${store.server.url_backend}/operation/m_menu`,
            headers: {'Content-Type': 'Application/json', authorization: `${store.user.token_type} ${store.user.token}`},
            params: {
  simplest: true,
  searchfield: 'modul,submodul,menu',
  where:`m_menu.is_active='true'`,
  notin: trx_dtl.length > 0
    ? `this.id:${trx_dtl.map(dt => dt.m_menu_id).join(',')}`
    : undefined,
},

            onsuccess(response) {
            response.page = response.current_page
            response.hasNext = response.has_next
            return response
          }
          }" :columns="[{
              checkboxSelection: true,
              headerCheckboxSelection: true,
              headerName: 'No',
              valueGetter:(params)=>{
                return ''
              },
              width: 60,
              sortable: false, resizable: false, filter: false,
              cellClass: ['justify-center', 'bg-gray-50']
            },
            {
              pinned: false,
              field: 'modul',
              headerName: 'Modul',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            {
              pinned: false,
              field: 'submodul',
              headerName: 'Sub Modul',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            {
              pinned: false,
              field: 'menu',
              headerName: 'Nama Menu',
              cellClass: ['border-r', '!border-gray-200', 'justify-center'],
              filter:true,
              flex: 1
            },
            ]">
        <div
          class="flex justify-center w-full h-full items-center px-2 py-1.5 text-xs rounded text-white bg-blue-500 hover:bg-blue-700 hover:bg-blue-600 transition-all duration-200">
          <icon fa="plus" size="sm mr-0.5" /> Tambah Akses
        </div>
      </ButtonMultiSelect>
      <button class="bg-red-500 hover:bg-red-600 text-white font-semibold ml-2 px-2 py-1 rounded-sm flex items-center justify-center mt-2" @click="clearAll()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3 mr-2" viewBox="0 0 16 16">
              <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
            </svg>
              Hapus Semua
          </button>
    </div>

    <div>
      <TableStatic customClass="h-50vh" ref="detail" :value="trx_dtl" @input="onRetotal" :columns="[{
                headerName: 'No',
                cellRenderer:'ButtonGrid',
                valueGetter:p=>p.node.rowIndex + 1,
                cellRendererParams:{
                  showValue: true,
                  icon: 'times',
                  class: 'btn-text-danger',
                  click:(app)=>{
                    onDeleteRow(app)
                  }
                },
                width: 60,
                sortable: false, resizable: true, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                headerName: 'Nama Menu',
                field: 'menu',
                editable: false,
                sortable: false, resizable: true, filter: false,
                cellClass: ['!border-gray-200'],
              },
              {
                headerName: 'Preview',
                field: 'can_read',
                cellClass: ['justify-center', 'border-r','!border-gray-200', '!text-gray-500'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: false,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_read'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Create',
                field: 'can_create',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_create'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Update',
                field: 'can_update',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_update'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Delete',
                field: 'can_delete',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_delete'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              {
                headerName: 'Verify',
                field: 'can_verify',
                cellClass: ['justify-center', 'border-r','!border-gray-200'],
                width: 100, resizable: false, sortable: false, filter: false,
                cellRenderer: 'ButtonGridCheck',
                cellRendererParams: {
                  readonly: !actionText||values.access_id,
                  change:(app, isChecked)=>{
                    app.params.node.data['can_verify'] = isChecked
                    app.params.api.applyTransaction({ update: [app.params.node.data] })
                  }
                }
              },
              ]">
        <template #header></template>
      </TableStatic>

    </div>
  </div>

  <!-- END TABLE DETAIL -->

  <!-- END TABLE -->


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
        v-show="actionText && (currentMenu?.can_create || currentMenu?.can_update)"
        @click="onSave"
      >
        <icon fa="save" />
        Simpan
      </button>
  </div>
</div>
@endverbatim
@endif