<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tassessmentkary extends Migration
{
    protected $tableName = "t_assessment_kary";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            $table->date('tanggal');
            $table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}');
            $table->bigInteger('atasan_id')->comment('{"src":"m_kary.id"}');
            $table->bigInteger('m_assessment_kary_id')->comment('{"src":"m_assessment_kary.id"}');
            $table->string('tipe_penilaian')->nullable();
            $table->text('catatan_1')->nullable();
            $table->text('catatan_2')->nullable();
            $table->text('catatan_3')->nullable();
            $table->text('catatan_4')->nullable();
            $table->decimal('rata_rata',4,2);
            $table->string('status')->default('DRAFT');

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