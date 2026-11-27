<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mgrade extends Migration
{
    protected $tableName = "m_grade";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'tahun')) {
                $table->integer('tahun')->nullable()->after('m_zona_id');
            }
        });
    }

    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (Schema::hasColumn($this->tableName, 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }
}
