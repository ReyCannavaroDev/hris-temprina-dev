@verbatim
<div class="flex flex-col rounded-md shadow-md w-full p-0 bg-white">
  <div class="bg-gray-500 text-white rounded-t-md py-2 px-4">
    <div class="flex items-center">
      <Icon fa="arrow-left" class="cursor-pointer mr-3 font-bold hover:text-yellow-500" title="Kembali" @click="onBack"/>
      <div class="min-w-0">
        <h1 class="text-20px font-bold leading-tight">Akses Responsibility</h1>
        <p class="text-gray-100 text-sm">Pilih salah satu Responsibility untuk mengakses fitur tertentu</p>
      </div>
    </div>
  </div>

  <div class="p-3 md:p-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4">
      <div
        v-for="(item, i) in dataRespo" :key="i"
        @click="clickItem(item.id)"
        :class="item.id == values.id_active
          ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
          : 'border-gray-200 bg-white hover:border-blue-400 hover:shadow-md'"
        class="relative rounded-xl border p-4 cursor-pointer transition duration-200"
      >
        <div class="flex items-center justify-between gap-2 mb-3">
          <h5 class="text-lg font-bold text-gray-900 truncate">{{ item.name }}</h5>
          <span v-if="item.id == values.id_active" class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-green-500 text-white">Aktif</span>
        </div>
        <div class="grid grid-cols-[72px_1fr] gap-x-2 gap-y-1 text-[13px]">
          <span class="text-gray-500">Role</span>
          <span class="text-gray-800 font-medium break-words">{{ item['roles'] || '-' }}</span>
          <span class="text-gray-500">SBU</span>
          <span class="text-gray-800 font-medium break-words">{{ item['m_comp.name'] || '-' }}</span>
          <span class="text-gray-500">SUB</span>
          <span class="text-gray-800 font-medium break-words">{{ item['m_subcomp.name'] || '-' }}</span>
          <span class="text-gray-500">Branch</span>
          <span class="text-gray-800 font-medium break-words">{{ item['m_branch.name'] || '-' }}</span>
        </div>
      </div>
    </div>
  </div>
</div>
@endverbatim
