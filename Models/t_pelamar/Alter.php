<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tpelamar extends Migration
{
    protected $tableName = "t_pelamar";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['m_divisi_id', 'm_dept_id', 'm_posisi_id']);
            $table->bigInteger('t_loker_id')->comment('{"src":"t_loker.id"}')->nullable();
            $table->string('file_cv')->nullable();
            $table->string('file_dokumen')->nullable();
        });
    }
}
