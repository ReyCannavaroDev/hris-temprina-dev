<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mbranch extends Migration
{
    protected $tableName = "m_branch";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            // identity pk unique
            // $table->uuid('uid')->unique();
            $table->id()->from(1);

            $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable();
            $table->bigInteger('m_subcomp_id')->comment('{"src":"m_subcomp.id"}')->nullable();
            $table->string('code',100)->nullable();
            $table->string('name',200);
            $table->bigInteger('province_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('city_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->bigInteger('district_id')->comment('{"src":"m_general.id"}')->nullable();
            $table->string('address',200)->nullable();
            $table->string('npwp',200)->nullable();
            $table->string('phone1',200)->nullable();
            $table->string('phone2',200)->nullable();
            $table->string('email',200)->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            // update 14-08-2024

            // penting
            $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            $table->timestamps();
            $table->bigInteger('deletor_id')->nullable();
            $table->datetime('deleted_at')->nullable();
        });

        // auto generate uuid default pgsql standart v4
        // \DB::statement("ALTER TABLE $this->tableName ALTER COLUMN uid SET DEFAULT uuid_generate_v4();");


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