<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tfinalgaji extends Migration
{
    protected $tableName = "t_final_gaji";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'is_os')) {
                $table->boolean('is_os')->nullable()->default(false);
            }
            if (!Schema::hasColumn($this->tableName, 'm_company_outsourcing_id')) {
                $table->bigInteger('m_company_outsourcing_id')->nullable();
            }
            if (!Schema::hasColumn($this->tableName, 'm_branch_id')) {
                $table->bigInteger('m_branch_id')->nullable();
            }
        });

        // Set default is_os = false untuk data historis eksisting agar tidak terpengaruh
        try {
            \DB::table($this->tableName)->whereNull('is_os')->update(['is_os' => false]);
        } catch (\Throwable $e) {
            \Log::warning("Set default is_os in t_final_gaji: " . $e->getMessage());
        }

        // Auto-seed master approval untuk Finalisasi Gaji OS jika belum ada
        try {
            if (Schema::hasTable('m_approval')) {
                $cekApproval = \DB::table('m_approval')->where('name', 'APPROVAL FINALISASI GAJI OS')->first();
                if (!$cekApproval) {
                    \DB::table('m_approval')->insert([
                        'name'        => 'APPROVAL FINALISASI GAJI OS',
                        'code'        => 'APP-FIN-GAJI-OS',
                        'desc'        => 'Persetujuan Finalisasi Gaji Karyawan Outsourcing oleh HC Cabang',
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Auto-seed m_approval untuk Finalisasi Gaji OS: " . $e->getMessage());
        }
    }
}

