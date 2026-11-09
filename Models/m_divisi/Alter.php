<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mdivisi extends Migration
{
    protected $tableName = "m_divisi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            // $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable()->change();
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->bigInteger('name')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('nomor',100)->nullable()->change();


        });
    }
}
