<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tklaimaskesd extends Migration
{
    protected $tableName = "t_klaim_askes_d";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);
            $table->integer('t_klaim_askes_id')->comment('{"fk":"t_klaim_askes.id"}')->nullable();
            $table->decimal('nominal', 22, 2)->default(0)->nullable(); 
            $table->decimal('accepted', 22, 2)->default(0)->nullable(); 
            $table->decimal('reject', 22, 2)->default(0)->nullable(); 
            $table->string('keterangan')->nullable();
            $table->string('santunan')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('bukti')->nullable();
            $table->string('klaim_nama')->nullable();
            $table->integer('klaim_id')->nullable();
            $table->string('klaim_table')->nullable();
            $table->string('nomor_bukti')->nullable();
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