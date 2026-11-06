<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tlaporanpekerjaan extends Migration
{
    protected $tableName = "t_laporan_pekerjaan";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            //dari pengajuan
            $table->bigInteger('t_pengajuan_pekerjaan_id')->comment('{"src":"t_pengajuan_pekerjaan.id"}')->nullable();
            
            $table->string('kode')->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('jenis_pekerjaan_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('pekerjaan');
            $table->date('start_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->bigInteger('pic_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('m_divisi_pic_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('request_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('pekerjaan_sebelumnya_id')->nullable();
            $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            
            //laporan
            $table->string('status')->nullable();
            $table->string('sebelum')->nullable();
            $table->string('tindakan')->nullable();
            $table->string('setelah')->nullable();
            $table->date('finish_date')->nullable();
            $table->string('keterangan')->nullable();

            //finish/approval
            $table->string('score')->nullable();
            $table->string('catatan_penilaian')->nullable();

            $table->integer('creator_id')->nullable();
            $table->integer('last_editor_id')->nullable();

            // $table->integer('creator_id')->nullable();
            // $table->integer('last_editor_id')->nullable();
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