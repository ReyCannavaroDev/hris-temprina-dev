<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tefektifitaspelatihan extends Migration
{
    protected $tableName = "t_efektifitas_pelatihan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            $table->dropColumn([ 't_request_pelatihan_id' ]);
            $table->bigInteger('t_realisasi_pelatihan_id')->comment('{"src":"t_realisasi_pelatihan.id"}')->nullable();
        });
    }
}
