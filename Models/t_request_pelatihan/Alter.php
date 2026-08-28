<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class trequestpelatihan extends Migration
{
    protected $tableName = "t_request_pelatihan";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['no_hp']);
            // $table->bigInteger('m_prog_pelatihan_id')->comment('{"src":"m_prog_pelatihan.id"}')->nullable();
            // $table->string('sarana')->nullable();
            // $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable();
            // $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
        });

        // Ubah form_name notifikasi lama agar mengarah ke modul baru (t_req_pelatihan)
        \DB::statement("UPDATE generate_approval SET form_name = 't_req_pelatihan' WHERE form_name = 't_request_pelatihan'");
    }
}
