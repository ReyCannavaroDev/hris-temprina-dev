<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class trencanaperdin extends Migration
{
    protected $tableName = "t_rencana_perdin";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn([]);
            // $table->decimal('total_biaya', 22, 2)->nullable();

        });
    }
}
