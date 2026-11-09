<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mstandartgajidet extends Migration
{
    protected $tableName = "m_standart_gaji_det";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn([ 'komponen']);
            // $table->bigInteger('komponen_tipe_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->boolean('is_from_thp')->nullable();
            // $table->bigInteger('komponen_id')->comment('{"src":"m_general.id"}')->nullable()->change();
            // $table->bigInteger('komponen_id')->comment('{"src":"m_general.id"}');

        });
    }
}
