<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mprogpelatihan extends Migration
{
    protected $tableName = "m_prog_pelatihan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // $table->string('sasaran')->nullable()->change();
            //$table->string('_columnName_');
            // $table->dropColumn([ ]);
        });
    }
}
