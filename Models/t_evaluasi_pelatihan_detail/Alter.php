<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tevaluasipelatihandetail extends Migration
{
    protected $tableName = "t_evaluasi_pelatihan_detail";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['t_evaluasi_pelatihan_id']);
            //$table->bigInteger('t_evaluasi_pelatihan_id')->comment('{"fk":"t_evaluasi_pelatihan.id"}')->nullable();
        });
    }
}
