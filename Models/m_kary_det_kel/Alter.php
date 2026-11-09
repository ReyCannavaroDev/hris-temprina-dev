<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mkarydetkel extends Migration
{
    protected $tableName = "m_kary_det_kel";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->date('tgl_lahir')->nullable();
            //$table->dropColumn([ ]);
            //$table->integer('usia')->nullable()->change();
            // $table->renameColumn('m_karyawan_id', 'm_kary_id');
            $table->boolean('include_bpjs')->nullable()->default(true);

        });
    }
}
