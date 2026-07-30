<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tefektifitaspelatihandetail extends Migration
{
    protected $tableName = "t_efektifitas_pelatihan_detail";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            $table->dropColumn(['t_efektifitas_pelatihan_id']);
            //$table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}');
            // $table->bigInteger('t_efektifitas_pelatihan_id')->comment('{"fk":"t_efektifitas_pelatihan.id"}')->nullable();
            // $table->string('komponen_efektifitas')->nullable();
            // $table->integer('nilai')->nullable();
            //$table->integer('sequence')->nullable();
        });
    }
}
