<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tloker extends Migration
{
    protected $tableName = "t_loker";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['is_active']);
            // $table->boolean('is_active')->nullable()->default(false);
            $table->bigInteger('jk_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('status_kary_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('jumlah')->nullable();
        });
    }
}
