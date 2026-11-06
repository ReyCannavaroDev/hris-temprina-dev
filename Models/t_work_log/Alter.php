<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tworklog extends Migration
{
    protected $tableName = "t_work_log";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['t_work_log_id' ]);
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->bigInteger('jenis_pekerjaan_id')->comment('{"src":"m_general.id"}')->nullable()->change();
            // $table->bigInteger('pic_id')->comment('{"src":"m_kary.id"}')->nullable()->change();
        });
    }
}
