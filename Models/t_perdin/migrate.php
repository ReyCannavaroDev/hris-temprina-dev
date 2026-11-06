<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tperdin extends Migration
{
    protected $tableName = "t_perdin";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);
            $table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('m_atasan_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->string('tugas');
            $table->string('tempat_tujuan');
            $table->bigInteger('provinsi_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('kota_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('kecamatan_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('alamat_tujuan');
            $table->integer('creator_id')->nullable();
            $table->string('status')->nullable();
            $table->string('nomor')->nullable();
            $table->bigInteger('m_posisi_id')->comment('{"src":"m_posisi.id"}')->nullable();
            // $table->boolean('is_active')->nullable()->default(true);
            //$table->string('samplecolumn',100)->nullable();

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