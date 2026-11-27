<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class presensilokasi extends Migration
{
    protected $tableName = "presensi_lokasi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            $table->bigInteger('branch_id')->comment('{"src":"m_branch.id"}')->nullable()->change();
            if (!Schema::hasColumn($this->tableName, 'ip_address')) {
                $table->text('ip_address')->nullable();
            }
        });

        // Sinkronisasi data existing jika comp_id berisi branch_id tapi branch_id masih NULL
        if (Schema::hasColumn($this->tableName, 'branch_id') && Schema::hasColumn($this->tableName, 'comp_id')) {
            DB::statement("UPDATE {$this->tableName} SET branch_id = comp_id WHERE branch_id IS NULL AND comp_id IS NOT NULL");
            DB::statement("UPDATE {$this->tableName} SET comp_id = branch_id WHERE comp_id IS NULL AND branch_id IS NOT NULL");
        }
    }
}
