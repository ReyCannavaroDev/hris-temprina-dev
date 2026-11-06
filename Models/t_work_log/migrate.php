<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tworklog extends Migration
{
    protected $tableName = "t_work_log";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            $table->string('kode')->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('jenis_pekerjaan_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('pekerjaan');
            $table->datetime('start_date')->nullable();
            $table->datetime('deadline_date')->nullable();
            $table->datetime('finish_date')->nullable();
            $table->bigInteger('pic_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('request_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->decimal('progress')->nullable();
            $table->string('sebelum')->nullable();
            $table->string('tindakan')->nullable();
            $table->string('setelah')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('status')->nullable();
            
            $table->bigInteger('approve_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->bigInteger('recognized_id')->comment('{"src":"m_kary.id"}')->nullable();
            $table->string('score')->nullable();

            $table->integer('creator_id')->nullable();
            $table->integer('last_editor_id')->nullable();

            $table->bigInteger('status_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('t_work_log_id')->nullable();
            $table->bigInteger('m_divisi_pic_id')->comment('{"src":"m_divisi.id"}')->nullable();

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