<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tlembur extends Migration
{
    protected $tableName = "t_lembur";
    
    public function up()
    {
        if (Schema::hasTable($this->tableName)) {
            Schema::table($this->tableName, function (Blueprint $table) {
                //$table->string('_existColumnName_')->change();
                //$table->string('_columnName_');
                //$table->dropColumn([ ]);
                // $table->integer('interval_min')->nullable();
                if (Schema::hasColumn($this->tableName, 'pic_id')) {
                    $table->bigInteger('pic_id')->comment('{"src":"default_users.id"}')->nullable()->change();
                }
                if (!Schema::hasColumn($this->tableName, 'tanggal_selesai')) {
                    $table->date('tanggal_selesai')->nullable()->after('tanggal');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable($this->tableName)) {
            Schema::table($this->tableName, function (Blueprint $table) {
                if (Schema::hasColumn($this->tableName, 'tanggal_selesai')) {
                    $table->dropColumn(['tanggal_selesai']);
                }
            });
        }
    }
}
