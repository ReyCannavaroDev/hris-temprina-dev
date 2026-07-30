<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tmutasi extends Migration
{
    protected $tableName = "t_mutasi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['status_kary_id_lama', 'status_kary_id_baru']);
            // $table->bigInteger('jenis_surat_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->bigInteger('m_branch_lama_id')->comment('{"src":"m_branch.id"}');
            // $table->bigInteger('m_divisi_lama_id')->comment('{"src":"m_divisi.id"}')->nullable();
            
            // $table->bigInteger('m_posisi_lama_id')->comment('{"src":"m_posisi.id"}');
            // $table->bigInteger('m_standart_posisi_id')->comment('{"src":"m_standart_gaji.id"}');
            // $table->bigInteger('m_devisi_baru_id')->comment('{"src":"m_divisi.id"}');
            // $table->bigInteger('m_dept_baru_id')->comment('{"src":"m_dept.id"}')->nullable();
            // $table->bigInteger('m_posisi_baru_id')->comment('{"src":"m_posisi.id"}');
            // $table->bigInteger('m_standart_baru_id')->comment('{"src":"m_standart_gaji.id"}');
            // $table->bigInteger('jadwal_kerja_lama_id')->comment('{"src":"t_jadwal_kerja_n.id"}')->nullable();
            // $table->bigInteger('jadwal_kerja_baru_id')->comment('{"src":"t_jadwal_kerja_n.id"}')->nullable();
            //$table->bigInteger('status_kary_lama_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->bigInteger('status_kary_baru_id')->comment('{"src":"m_general.id"}')->nullable()->change();
            // $table->string('no_dokumen')->nullable()->change();
            // $table->text('deskripsi')->nullable()->change();
        });
    }
}
