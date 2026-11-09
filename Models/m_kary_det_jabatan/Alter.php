<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mkarydetjabatan extends Migration
{
    protected $tableName = "m_kary_det_jabatan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['m_branch_id','m_divisi_id']);
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
        });
    }
}
