<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tpenyelesaianperdindlaporan extends Migration
{
    protected $tableName = "t_penyelesaian_perdin_d_laporan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            $table->renameColumn('kegitatan', 'kegiatan');        
        });
    }
}
