<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tlaporanpekerjaan extends Migration
{
    protected $tableName = "t_laporan_pekerjaan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            //$table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
        });
    }
}
