<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mberkas extends Migration
{
    protected $tableName = "m_berkas";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->bigInteger('kategori_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            $table->dropColumn(['kategori']);
        });
    }
}
