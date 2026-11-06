
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-white border-none">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-2 font-bold hover:text-yellow-500" title="Kembali" @click="onBack"/>
      <div>
        <h1 class="text-20px font-bold">Akses Responsibility</h1>
        <p class="text-gray-100">Pilih salah satu Responsibility untuk mengakses fitur tertentu</p>
      </div>
    </div>
  </div>
  <div class="m-4 p-2 rounded-lg">
    <div class="grid grid-cols-4 gap-4">
      <div v-for="(item, i) in dataRespo" :key="i">
      
        <div @click="clickItem(item.id)" :class="item.id == values.id_active ? 'font-semibold bg-green-300 text-white hover:!bg-green-500' : ''" href="#" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-green-800 dark:border-green-700 dark:hover:bg-green-700 duration-200 h-50 poin cursor-pointer">
          <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{item.name}}</h5>
          <p class="mb-0.5 font-normal text-[14px] text-gray-700 dark:text-gray-400">ROLE : {{item['roles']}}</p>
          <p class="mb-0.5 font-normal text-[14px] text-gray-700 dark:text-gray-400">SBU : {{item['m_comp.name']}}</p>
          <p class="mb-0.5 font-normal text-[14px] text-gray-700 dark:text-gray-400">SUB : {{item['m_subcomp.name']}}</p>
          <p class="mb-0.5 font-normal text-[14px] text-gray-700 dark:text-gray-400">BRANCH :{{item['m_branch.name']}}</p>
        </div>
<!-- 
        <h1 class="text-white text-xl font-semibold">{{item.name}}</h1>
        <h1>{{item['m_comp.name']}}</h1>
        <h1>{{item['m_subcomp.name']}}</h1>
        <h1>{{item['m_branch.name']}}</h1> -->
      </div>
    </div>
</div>
@endverbatim