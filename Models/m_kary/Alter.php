<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mkary extends Migration
{
    protected $tableName = "m_kary";

    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // $table->string("no_registrasi");
            // $table->string("finger_id");
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            $table->dropColumn(['plafond_ranap']);
            //$table->string("nip")->nullable()->after("no_registrasi");
            //$table->integer('cuti_jatah_tahun_lalu')->nullable()->after('cuti_jatah_reguler');
            // $table->string('ig',100)->nullable();
            // $table->string('x',100)->nullable();
            // $table->string('facebook',100)->nullable();
            // $table->string('linkedin',100)->nullable();
            // $table->string('email',100)->nullable();
            // $table->bigInteger('m_fingerprint_machine_id')->comment('{"src":"m_fingerprint_machine.id"}')->nullable();
            // $table->bigInteger('m_company_outsourcing_id')->comment('{"src":"m_company_outsourcing.id"}')->nullable();
            // $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            // $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            // $table->string('finger_id')->nullable();
            // $table->string('no_registrasi')->nullable();
            // $table->json('m_jam_kerja_id')->nullable();
            //
            // $table->string('nama_depan', 100)->required()->change();
            // $table->string('nama_belakang', 100)->required()->change();
            // $table->bigInteger('jk_id')->comment('{"src":"m_general.id"}')->required()->change();
            // $table->string('tempat_lahir', 100)->required()->change();
            // $table->string('no_tlp',20)->required()->change();
            // $table->bigInteger('status_kary_id')->comment('{"src":"m_general.id"}')->required()->change();
            //$table->boolean('can_outscope')->nullable()->default(false);
            // $table->date('tgl_pengangkatan')->nullable();

            // $table->bigInteger('m_jam_kerja_id')->nullable()->change();

        });
    }
}
