<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class presensiabsensi extends Migration
{
    protected $tableName = "presensi_absensi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            // $table->dropColumn(['catatan']);
            // $table->string('checkin_foto')->nullable()->change();
            // $table->string('checkout_foto')->nullable()->change();
            // $table->text('catatan')->nullable();
            // $table->text('catatan_out')->nullable();
            // $table->boolean('is_face')->nullable();
            // $table->boolean('in_scope')->nullable();
            // $table->bigInteger('m_jam_kerja_id')->comment('{"src":"m_jam_kerja.id"}')->nullable();
            // $table->bigInteger('t_jadwal_kerja_det_hari_id')->comment('{"src":"t_jadwal_kerja_det_hari.id"}')->nullable();
            // $table->bigInteger('jadwal_kerja_id')->comment('{"src":"t_jadwal_kerja.id"}')->nullable();
            // $table->dropColumn(['jadwal_kerja_n_id', 'jadwal_kerja_d_n_id', 'jadwal_kerja_d_hari_n_id']);
            // $table->bigInteger('t_jadwal_kerja_n_id')->comment('{"src":"t_jadwal_kerja_n.id"}')->nullable();
            // $table->bigInteger('t_jadwal_kerja_d_n_id')->comment('{"src":"t_jadwal_kerja_d_n.id"}')->nullable();
            // $table->bigInteger('t_jadwal_kerja_d_hari_n_id')->comment('{"src":"t_jadwal_kerja_d_hari_n.id"}')->nullable();
            // $table->integer('jam_kerja_diakui')->nullable();
        });
    }
}
