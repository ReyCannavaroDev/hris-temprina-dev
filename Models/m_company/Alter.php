<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mcompany extends Migration
{
    protected $tableName = "m_company";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->bigInteger('kategori_id')->comment('{"src":"m_general.id"}')->nullable();
            //$table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            //$table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            //$table->string('_columnName_');
            //$table->dropColumn(['kategori_id','m_subcomp_id','m_branch_id']);
        });
    }
}
