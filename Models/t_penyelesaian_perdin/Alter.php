<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tpenyelesaianperdin extends Migration
{
    protected $tableName = "t_penyelesaian_perdin";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            $table->integer('creator_id')->nullable();
            $table->integer('last_editor_id')->nullable();
        });
    }
}
