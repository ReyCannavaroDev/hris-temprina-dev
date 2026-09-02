@verbatim
<div>
  <!-- Header Section -->
  <header
    class="h-24 flex items-center justify-center bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg">
    <div class="text-center">
      <h1 class="text-3xl font-bold">STRUKTUR JABATAN TEMPRINA</h1>
      <p class="text-sm mt-1 text-gray-200">Visualisasi data jabatan berdasarkan level</p>
    </div>
  </header>

  <div class="h-screen flex flex-col justify-center items-center p-4 bg-gradient-to-br from-gray-100 to-gray-300">
    <div class="w-full max-w-2xl mb-8 p-4 bg-white rounded-xl shadow-md transition-all duration-300 hover:shadow-lg">
      <!-- Instruction Label -->
      <div class="mb-3">
        <span class="text-gray-700 font-medium text-sm">Pilih Data Berdasarkan SBU & Level :</span>
      </div>

      <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">

        <FieldSelect :bind="{ disabled: false, clearable:true }" class="w-full !mt-0" :value="values.m_comp_id" @input="v=>{
              if(v){
                values.m_comp_id=v
              }else{
                values.m_comp_id=null
              }
            }" @update:valueFull="obj => {
                if (obj) {
                  values.m_comp_id = obj.id; 
                } else {
                  values.m_comp_id = null;
                }
              }" :errorText="formErrors.m_comp_id?'failed':''" :hints="formErrors.m_comp_id" valueField="id"
          displayField="name" :api="{
                url: `${store.server.url_backend}/operation/m_comp`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  transform:false,
                  join:false,
                  where:`this.is_active='true'`
                }
                      }" placeholder="" label="" fa-icon="sort-desc" :check="false" />

        <div>
          <FieldX :bind="{ readonly: false , clearable: false }" class="w-full !mt-0" :value="values.start_level"
            :errorText="formErrors.start_level?'failed':''" @input="v=>values.start_level=v"
            :hints="formErrors.start_level" label="" placeholder="Pilih Level" :check="false" />
        </div>

        <div>
          <FieldX :bind="{ readonly: false , clearable: false }" class="w-full !mt-0" :value="values.end_level"
            :errorText="formErrors.end_level?'failed':''" @input="v=>values.end_level=v" :hints="formErrors.end_level"
            label="" placeholder="Pilih Level Akhir" :check="false" />
        </div>
        <!-- Get Data Button -->
        <button
  @click="getData"
  :disabled="isRequesting"
  class="w-full sm:w-auto px-4  bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-md hover:from-blue-600 hover:to-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition duration-300 flex items-center justify-center gap-2"
>
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    class="h-5 w-5" 
    fill="none" 
    viewBox="0 0 24 24" 
    stroke="currentColor"
  >
    <path 
      stroke-linecap="round" 
      stroke-linejoin="round" 
      stroke-width="2" 
      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" 
    />
  </svg>
  {{ isRequesting ? 'Loading...' : '' }}
</button>
      </div>
      <!-- Export as Image Button -->
      <button
    @click="exportAsImage"
    class="mt-4 w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-md hover:from-green-600 hover:to-teal-700 transition duration-300"
  >
    Export as Image
  </button>
    </div>

    <!-- Visualization Container with Label -->
    <div class="w-full h-[600px] sm:h-[800px] mt-8 border rounded-2xl overflow-hidden shadow-lg bg-white ">
      <!-- Label -->
      <div
        class=" bg-blue-500 text-white px-4 py-2 rounded-t-lg text-sm font-semibold shadow-md z-10 text-center">
        Struktur Jabatan
      </div>
      <!-- Content -->
      <div class="w-full h-full border rounded-lg" ref="cyContainer"></div>
    </div>
  </div>
</div>
@endverbatim