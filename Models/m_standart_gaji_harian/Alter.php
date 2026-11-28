<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mstandartgajiharian extends Migration
{
    protected $tableName = "m_standart_gaji_harian";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'status')) {
                $table->string('status', 50)->nullable()->default('DRAFT');
            }
            if (!Schema::hasColumn($this->tableName, 'm_branch_id')) {
                $table->bigInteger('m_branch_id')->nullable();
            }
        });

        // Pastikan data historis eksisting memiliki status POSTED dan m_branch_id sinkron dari m_kary
        try {
            \DB::table($this->tableName)
                ->whereNull('status')
                ->orWhere('status', '')
                ->update(['status' => 'POSTED']);

            // Auto-populate m_branch_id dari m_kary untuk data yang belum terisi
            $records = \DB::table($this->tableName)
                ->whereNull('m_branch_id')
                ->whereNotNull('m_kary_id')
                ->select('id', 'm_kary_id')
                ->get();

            foreach ($records as $r) {
                $branchId = \DB::table('m_kary')->where('id', $r->m_kary_id)->value('m_branch_id');
                if ($branchId) {
                    \DB::table($this->tableName)->where('id', $r->id)->update(['m_branch_id' => $branchId]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Sinkronisasi status m_standart_gaji_harian: " . $e->getMessage());
        }

        // Auto-seed master approval jika belum ada di database
        try {
            if (Schema::hasTable('m_approval')) {
                $cekApproval = \DB::table('m_approval')->where('name', 'APPROVAL STANDAR GAJI HARIAN OS')->first();
                if (!$cekApproval) {
                    \DB::table('m_approval')->insert([
                        'name'        => 'APPROVAL STANDAR GAJI HARIAN OS',
                        'code'        => 'APP-STD-GAJI-OS',
                        'desc'        => 'Persetujuan Standar Gaji Harian Karyawan Outsourcing oleh HC Cabang',
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Auto-seed m_approval untuk Standar Gaji OS: " . $e->getMessage());
        }
    }
}

