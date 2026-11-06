<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tefektifitaspelatihan extends Migration
{
    protected $tableName = "t_efektifitas_pelatihan";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);
            $table->string('kode')->nullable();
            $table->bigInteger('t_realisasi_pelatihan_id')->comment('{"src":"t_realisasi_pelatihan.id"}')->nullable();
            $table->bigInteger('trainer_id')->comment('{"src":"m_trainer.id"}')->nullable();
            $table->bigInteger('m_prog_pelatihan_id')->comment('{"src":"m_prog_pelatihan.id"}')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('saran')->nullable();
            $table->text('status')->default('DRAFT')->nullable();
            $table->integer('creator_id')->nullable();
            $table->integer('last_editor_id')->nullable();
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