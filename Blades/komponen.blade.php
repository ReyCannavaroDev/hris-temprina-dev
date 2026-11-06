  <ButtonMultiSelect v-show="actionText" title="Add to list" @add="onDetailAdd" :api="{
            url: `${store.server.url_backend}/operation/m_role`,
            headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
            params: { 
              notin: `this.id:${detailArr.map(dt=> dt.m_role_id)}`,
              where: `this.is_active = true`
            },
            onsuccess:(response)=>{
              response.data = [...response.data].map((dt)=>{
                //Object.keys(dt).forEach(k=>dt['m_barang.'+k] = dt[k])
                return dt
              })
              response.page = response.current_page
              response.hasNext = response.has_next
              return response
            }
          }" :columns="[{
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerName: 'No',
            valueGetter:p=>'',
            width:60,
            sortable: false, resizable: true, filter: false,
            cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Kode Akses',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'code',
            cellClass: ['justify-start','!border-gray-200']
          },
          {
            flex: 1,
            headerName:'Nama Akses',
            sortable: false, resizable: true, filter: 'ColFilter',
            field: 'name',
            cellClass: ['justify-start','!border-gray-200']
          }]">
      <div
        class="bg-blue-600 text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5 mt-3">
        <icon fa="plus" size="sm mr-0.5" /> Add to list
      </div>
    </ButtonMultiSelect>

    <!-- ========== JS ============ -->

    const onDetailAdd = (e) => {
  e.forEach(row => {
    row.m_role_id = row.id
    row.is_special = 0
    detailArr.value.push(row)
  })
}
<!-- ===================== End JS ===================== -->