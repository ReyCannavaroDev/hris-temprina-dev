<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mstandartgajiharian extends Migration
{
    protected $tableName = "m_standart_gaji_harian";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['total_jam_kerja']);
            //$table->decimal('total_jam_kerja', 5, 2)->nullable()->default(7);
            // $table->boolean('check_absence')->nullable()->default(true);        
        });
    }
}
