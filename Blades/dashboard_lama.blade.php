<!-- POPUP MODAL LOWONGAN -->
  <div v-show="isLokerModalOpen" style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 9999999 !important; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;" @click="closePopup"></div>
    <div class="modal-container bg-white w-[80%] mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh; position: relative;">
      
      <div class="modal-content py-4 text-left px-6">
        <!-- Modal Header -->
        <div class="modal-header flex items-center justify-between flex-wrap border-b pb-2">
          <div class="flex items-center">
            <h3 class="text-xl font-semibold ml-2">Detail Lowongan: @{{ selectedLoker && selectedLoker.title ? selectedLoker.title : (selectedLoker && selectedLoker['m_posisi.name'] ? selectedLoker['m_posisi.name'] : '-') }}</h3>
          </div>
          <div>
            <span class="font-medium px-2 py-1 bg-gray-200 rounded text-sm mr-2">Status: @{{ selectedLoker && selectedLoker.status ? selectedLoker.status : 'OPEN' }}</span>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body mt-4">
          <div class="mb-4 text-sm ml-2">
            <p><strong>Divisi:</strong> @{{ selectedLoker && selectedLoker['m_divisi.name'] ? selectedLoker['m_divisi.name'] : '-' }}</p>
            <p><strong>Cabang:</strong> @{{ selectedLoker && selectedLoker['m_branch.name'] ? selectedLoker['m_branch.name'] : '-' }}</p>
          </div>

          <h4 class="text-lg font-semibold ml-2 mb-2">Data Pelamar</h4>
          
          <div v-show="isFetchingLokerApp" class="text-center py-5">
            <p class="text-gray-500 font-medium">Memuat data...</p>
          </div>

          <div v-show="!isFetchingLokerApp && lokerApplicants.length === 0" class="text-center py-5">
            <p class="text-red-500 font-medium">Belum ada data pelamar untuk lowongan ini.</p>
          </div>

          <table v-show="!isFetchingLokerApp && lokerApplicants.length > 0" class="w-[100%] my-3 border">
            <thead>
              <tr class="border bg-gray-100">
                <td class="border px-2 py-1 font-medium text-center">No</td>
                <td class="border px-2 py-1 font-medium">Nama Pelamar</td>
                <td class="border px-2 py-1 font-medium text-center">Tanggal Tes</td>
                <td class="border px-2 py-1 font-medium text-center">Status</td>
                <td class="border px-2 py-1 font-medium text-center">Aksi</td>
              </tr>
            </thead>
            <tbody>
              <tr class="border hover:bg-gray-50" v-for="(applicant, idx) in lokerApplicants" :key="idx">
                <td class="border px-2 py-1 text-center">@{{ idx + 1 }}</td>
                <td class="border px-2 py-1">@{{ applicant.nama_pelamar || applicant['t_pelamar.nama'] || '-' }}</td>
                <td class="border px-2 py-1 text-center">@{{ applicant.tgl_tes || '-' }}</td>
                <td class="border px-2 py-1 text-center">
                  <span :class="applicant.status === 'DITERIMA' || applicant.status === 'APPROVED' ? 'text-green-600' : ''">
                    @{{ applicant.status || 'PROSES' }}
                  </span>
                </td>
                <td class="border px-2 py-1 text-center">
                  <button @click="goToApplicant(applicant.id)" class="text-blue-600 hover:text-blue-800 underline text-sm">
                    Lihat Hasil
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Modal Footer -->
        <div class="modal-footer flex justify-between items-center mt-4">
          <button @click="goToDetail(selectedLoker && selectedLoker.id)" class="text-blue-600 hover:text-blue-800 underline text-sm ml-2 font-medium">
            Buka Form Lowongan Lengkap
          </button>
          
          <button @click="closePopup" class="modal-button bg-yellow-500 hover:bg-yellow-600 text-white font-semibold ml-2 px-3 py-1 rounded-sm">
            Tutup
          </button>
        </div>
      </div>

    </div>
  </div>

<div v-if="beforeLoad" class="h-87vh w-full  items-center">
  <p class="italic font-semibold text-center text-gray-500">Harap tunggu sistem sedang menyusun konten..</p>
</div>
<div class="h-87vh overflow-y-auto w-full items-center rounded text-sm pb-10" v-if="user_type == Admin">
  <div class="grid grid-cols-4 gap-6 px-4 w-full">
    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p>Total SUB Aktif</p>
      <div class="text-green-500 font-bold text-2xl"> @{{totalDivisi}} </div>
    </div>

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p>Total Cabang Aktif</p>
      <div class="text-green-500 font-bold text-2xl"> @{{ totalDepartemen }} </div>
    </div>

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p> Pegawai Absen Hari Ini </p>
      <div class="text-red-500 font-bold text-2xl"> @{{pegawaiAbsen}} </div>
    </div>

    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg py-5 rounded-lg px-7 flex flex-col gap-2">
      <p> Pegawai Masuk Hari Ini </p>
      <div class="text-green-500 font-bold text-2xl"> @{{pegawaiMasuk}}</div>
    </div>
  </div>

  <div class="grid <md:grid-cols-1 grid-cols-1 gap-6 p-4">
    <div class="col-span-2 p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Pengeluaran Gaji Karyawan Bulan Ini Per Cabang</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },

              gridLineWidth: 0,
          },
          xAxis: {
      labels: {
        style: { fontSize: '10px' } // kecilkan font X axis
      }
    },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartData" adapter="highcharts">
      </column-chart>
    </div>
  </div>

  <div class="grid <md:grid-cols-1 grid-cols-1 gap-6 p-4">
    <div class="p-4 !select-none bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full w-full">
      <h2 class="font-semibold text-md justify-start mb-4">Pengeluaran Gaji Karyawan Bulan Ini Per Direktorat</h2>
      <column-chart :stacked="true" :library="{
          accessibility: {
            enabled: false
          },
          yAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              },
              gridLineWidth: 0,
          },
          xAxis: {
              min: 0,
              title: {
                  align: 'high'
              },
              labels: {
                  overflow: 'justify',
                  style: {fontSize : '8px'}
              },
              gridLineWidth: 0,
          },
          chart: {
              backgroundColor: 'rgba(0,0,0,0)',
          }
        }" :data="chartDataSub"
        adapter="highcharts">
      </column-chart>
    </div>
  </div>

  <!-- JOB VACANCIES SECTION -->
  <div class="grid grid-cols-1 gap-6 p-4">
    <div class="bg-white bg-opacity-80 hover:!bg-opacity-95 shadow-lg rounded-lg w-full p-5">
      <div class="flex items-center justify-between mb-4 border-b pb-2">
        <h2 class="text-lg font-bold text-gray-700 flex items-center gap-2">
          <Icon fa="bullhorn" class="text-green-600" /> Lowongan Pekerjaan
        </h2>
        <button @click="fetchLokerData" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 px-3 py-1.5 text-sm font-semibold rounded-md shadow-sm flex items-center gap-2 transition-all duration-300">
          <Icon fa="sync" :class="{'animate-spin': isRequestingJobs}" /> Segarkan Data
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isRequestingJobs && (!openJobs || openJobs.length === 0)" class="text-center py-10">
        <Icon fa="spinner" class="animate-spin text-4xl text-blue-500 mb-3" />
        <p class="text-gray-500 font-medium animate-pulse">Menarik data lowongan terbaru...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!openJobs || openJobs.length === 0" class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
        <div class="bg-gray-200 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <Icon fa="box-open" class="text-3xl text-gray-400" />
        </div>
        <h3 class="text-md font-bold text-gray-700 mb-1">Belum Ada Lowongan</h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto">Saat ini tidak ada lowongan pekerjaan yang berstatus OPEN, PROGRES, atau CLOSED.</p>
      </div>

      <!-- Data Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="(job, index) in openJobs" :key="index" class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 p-4 flex flex-col relative overflow-hidden group">
          
          <!-- Status Badge -->
          <div :class="(job.status === 'OPEN') ? 'from-green-500 to-emerald-600' : ( (job.status === 'PROGRES' || job.status === 'PROGRESS') ? 'from-blue-500 to-cyan-600' : 'from-gray-500 to-gray-700')" class="absolute top-0 right-0 bg-gradient-to-r text-white text-xs font-bold px-3 py-1 rounded-bl-lg z-10 shadow-sm flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-white" :class="{'animate-pulse': job.status === 'OPEN' || job.status === 'PROGRES' || job.status === 'PROGRESS'}"></span>
            @{{ job.status || 'OPEN' }}
          </div>
          
          <div class="mb-3 mt-2 flex items-start gap-3">
            <div class="bg-blue-50 p-2.5 rounded-lg text-blue-600 border border-blue-100 shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
              <Icon fa="briefcase" class="text-xl" />
            </div>
            <div class="flex-1 pr-12">
              <h3 class="text-sm font-bold text-gray-800 leading-tight line-clamp-2" :title="job.title || job['m_posisi.name']">
                @{{ job.title || job['m_posisi.name'] || 'Posisi Tidak Diketahui' }}
              </h3>
              <p class="text-[11px] font-medium text-blue-600 mt-0.5 line-clamp-1">@{{ job['m_comp.name'] || 'PT Temprina Media Grafika' }}</p>
            </div>
          </div>

          <div class="flex-grow space-y-1.5 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
            <div class="text-[12px] text-gray-600 flex items-start gap-2">
              <Icon fa="sitemap" class="w-3.5 mt-0.5 text-center text-gray-400" /> 
              <span class="line-clamp-1 flex-1" :title="job['m_divisi.name']">@{{ job['m_divisi.name'] || '-' }}</span>
            </div>
            <div class="text-[12px] text-gray-600 flex items-start gap-2">
              <Icon fa="map-marker-alt" class="w-3.5 mt-0.5 text-center text-gray-400" /> 
              <span class="line-clamp-1 flex-1" :title="job['m_branch.name']">@{{ job['m_branch.name'] || 'Pusat' }}</span>
            </div>
            <div class="text-[12px] text-gray-600 flex items-start gap-2">
              <Icon fa="users" class="w-3.5 mt-0.5 text-center text-gray-400" /> 
              <span>Terisi / Kuota: <b class="text-gray-800 bg-white px-1.5 py-px rounded border border-gray-200 shadow-sm ml-1">@{{ job.kuota_display || ('0 / ' + (job.jumlah || 0)) }}</b> Org</span>
            </div>
          </div>

          <div class="mt-3 pt-2.5 border-t border-gray-100 text-[11px] text-gray-400 flex justify-between items-center">
            <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-500">@{{ job.nomor || '-' }}</span>
            <button @click.prevent="openJobPopup(job)" class="bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-300 font-bold px-2 py-1 rounded-md flex items-center gap-1 group-hover:bg-blue-50 transition-colors">
              Lihat <Icon fa="arrow-right" class="text-[8px]" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
