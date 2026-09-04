<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($str, $start, $width, $trimmarker = '', $encoding = null) {
        $str = (string)$str;
        if (strlen($str) <= $width) return $str;
        $markerLen = strlen($trimmarker);
        $sub = substr($str, $start, max(0, $width - $markerLen));
        return $sub . $trimmarker;
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $encoding = null) {
        return strlen((string)$str);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = null) {
        return $length === null ? substr((string)$str, $start) : substr((string)$str, $start, $length);
    }
}

class treqrecruitment extends Migration
{
    protected $tableName = "t_req_recruitment";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            // Identitas Pengajuan & Pemohon
            $table->string('nomor', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}')->nullable(); // Karyawan Pemohon (Auto dari User Login)

            // Penempatan Organisasi & Posisi yang Diminta
            $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable();
            $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('m_dept_id')->comment('{"src":"m_dept.id"}')->nullable();
            $table->bigInteger('m_posisi_id')->comment('{"src":"m_posisi.id"}')->nullable();

            // Detail Permintaan Karyawan
            $table->integer('jumlah_kebutuhan')->default(1)->nullable();
            $table->bigInteger('status_kary_id')->comment('{"src":"m_general.id"}')->nullable(); // Status Karyawan (Tetap, Kontrak, Magang, Outsourcing)
            $table->bigInteger('jenis_permintaan_id')->comment('{"src":"m_general.id"}')->nullable(); // Penambahan Baru / Penggantian
            $table->bigInteger('karyawan_digantikan_id')->comment('{"src":"m_kary.id"}')->nullable(); // Karyawan yang digantikan jika replacement
            $table->date('tgl_dibutuhkan')->nullable();
            $table->bigInteger('prioritas_id')->comment('{"src":"m_general.id"}')->nullable(); // Normal / Urgent
            $table->text('alasan')->nullable(); // Alasan / Justifikasi kebutuhan personil

            // Status Approval & Referensi ke Loker
            $table->string('status', 50)->default('DRAFT')->nullable();
            $table->bigInteger('t_loker_id')->comment('{"src":"t_loker.id"}')->nullable();

            // Audit Trail
            $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->timestamps();
        });

        table_config($this->tableName, [
            "guarded"       => ["id"],
            "required"      => ["m_divisi_id", "m_posisi_id", "jumlah_kebutuhan", "tgl_dibutuhkan"],
            "!createable"   => ["id","created_at","updated_at"],
            "!updateable"   => ["id","created_at","updated_at"],
            "searchable"    => "all",
            "deleteable"    => "true",
            "deleteOnUse"   => "false",
            "extendable"    => "false",
            "casts"     => [
                'created_at' => 'datetime:d/m/Y H:i',
                'updated_at' => 'datetime:d/m/Y H:i',
                'tanggal' => 'datetime:d-m-Y',
                'tgl_dibutuhkan' => 'datetime:d-m-Y'
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}