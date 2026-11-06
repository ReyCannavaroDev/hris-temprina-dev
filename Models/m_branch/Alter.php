<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mbranch extends Migration
{
    protected $tableName = "m_branch";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['iuran_bpjstk']);
            //$table->string('no_rek_cabang',50)->nullable();
            //$table->float('iuran_bpjstk')->nullable();
        });
    }
}
