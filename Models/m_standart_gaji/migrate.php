<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mstandartgaji extends Migration
{
    protected $tableName = "m_standart_gaji";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);
            $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable();
            $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp_id"}')->nullable();
            $table->bigInteger('m_branch_id')->comment('{"src":"m_branch.id"}')->nullable();
            $table->string('kode',50)->nullable();
            $table->bigInteger('m_divisi_id')->comment('{"src":"m_divisi.id"}')->nullable();
            $table->bigInteger('m_zona_id')->comment('{"src":"m_zona.id"}')->nullable();
            $table->bigInteger('m_posisi_id')->comment('{"src":"m_posisi.id"}')->nullable();
            $table->bigInteger('grading_id')->comment('{"src":"m_grade.id"}')->nullable();

            $table->decimal('gaji_pokok',12,2);
            $table->string('gaji_pokok_periode',50)->nullable();

            $table->decimal('tunjangan_produktifitas',12,2)->nullable();
            $table->string('tunjangan_produktifitas_periode',50)->nullable();

            $table->decimal('tunjangan_posisi',12,2)->nullable();
            $table->string('tunjangan_posisi_periode',50)->nullable();

            $table->decimal('tunjangan_kemahalan',12,2)->nullable();
            $table->string('tunjangan_kemahalan_periode',50)->nullable();

            $table->decimal('tunjangan_transport',12,2)->nullable();
            $table->string('tunjangan_transport_periode',50)->nullable();

            $table->decimal('tunjangan_fungsional',12,2)->nullable();
            $table->string('tunjangan_fungsional_periode',50)->nullable();

            $table->bigInteger('m_kary_id')->comment('{"src":"m_kary.id"}')->nullable();

            $table->boolean('check_absence')->nullable()->default(true);
            $table->boolean('check_late')->nullable()->default(true);
           
            $table->text('desc')->nullable();
            $table->boolean('is_active')->default(1);
            $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->timestamps();
            
            $table->date('start_date')->nullable();
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