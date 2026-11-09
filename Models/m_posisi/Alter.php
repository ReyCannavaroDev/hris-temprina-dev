<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mposisi extends Migration
{
    protected $tableName = "m_posisi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['m_branch_id' ]);
            // $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable()->change();
            // $table->boolean('is_head')->nullable();
            // $table->boolean('is_parent')->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->decimal('nominal',12,2)->nullable();
            // $table->boolean('no_salary_deduction')->nullable();
            // $table->bigInteger('name')->comment('{"src":"m_general.id"}')->nullable();
            // $table->renameColumn('name','name_old');
        });
    }
}
