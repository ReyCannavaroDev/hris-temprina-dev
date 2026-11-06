<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tmutasi extends Migration
{
    protected $tableName = "t_mutasi";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            $table->string('nomor',50)->nullable();
            $table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}');
            $table->date('tgl');
            $table->string('tipe_mutasi');
            $table->bigInteger('jenis_surat')->comment('{"src":"m_general.id"}')->nullable();

            //lama
            $table->bigInteger('status_kary_lama_id')->comment('{"src":"m_general.id"}');
            $table->bigInteger('m_sbu_lama_id')->comment('{"src":"m_comp.id"}')->nullable();
            $table->bigInteger('m_sub_lama_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            $table->bigInteger('m_branch_lama_id')->comment('{"src":"m_branch.id"}')->nullable();
            $table->bigInteger('m_divisi_lama_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('m_posisi_lama_id')->comment('{"src":"m_posisi.id"}')->nullable();

            //baru
            $table->bigInteger('status_kary_baru_id')->comment('{"src":"m_general.id"}');
            $table->bigInteger('m_sbu_baru_id')->comment('{"src":"m_comp.id"}')->nullable();
            $table->bigInteger('m_sub_baru_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            $table->bigInteger('m_branch_baru_id')->comment('{"src":"m_branch.id"}')->nullable();
            $table->bigInteger('m_divisi_baru_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('m_posisi_baru_id')->comment('{"src":"m_posisi.id"}')->nullable();

            $table->bigInteger('signature_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('jadwal_kerja_lama_id')->comment('{"src":"t_jadwal_kerja_n.id"}')->nullable();
            $table->bigInteger('jadwal_kerja_baru_id')->comment('{"src":"t_jadwal_kerja_n.id"}')->nullable();

            $table->string('no_dokumen');
            $table->string('file_dokumen')->nullable();
            $table->text('deskripsi');
            $table->string('catatan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status',50)->default('DRAFT')->nullable();
            $table->decimal('kompensasi', 22, 2)->nullable();
            $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->timestamps();

        });

        table_config($this->tableName, [
            "guarded"       => ["id"],
            "required"      => [],
            "!createable"   => ["id","created_at","updated_at"],
            "!updateable"   => ["id","created_at","updated_at"],
            "searchable"    => "all",
            "deleteable"    => "true",
            "deleteOnUse"   => "false",
            "extendable"    => "false",
            "casts"     => [
                'created_at' => 'datetime:d/m/Y H:i',
                'updated_at' => 'datetime:d/m/Y H:i'
            ]
        ]);

        // if( $data = \Cache::pull($this->tableName) ){
        //     $fixedData = json_decode( json_encode( $data ), true );
        //     \DB::table($this->tableName)->insert( $fixedData );
        // }
    }
    public function down()
    {
        // if( Schema::hasTable($this->tableName) ){
        //     \Cache::put($this->tableName, \DB::table($this->tableName)->get(), 60*30 );
        // }
        Schema::dropIfExists($this->tableName);
    }
}