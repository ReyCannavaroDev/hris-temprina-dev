<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tklaimranapd extends Migration
{
    protected $tableName = "t_klaim_ranap_d";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);
            $table->integer('t_klaim_ranap_id')->comment('{"fk":"t_klaim_ranap.id"}')->nullable();
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
            $table->integer('santunan_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->boolean('is_active')->default(true); // Active/Inactive status column
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
    }
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
