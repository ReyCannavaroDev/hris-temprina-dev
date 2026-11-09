<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mcomp extends Migration
{
    protected $tableName = "m_comp";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);

            // penting
            // $table->bigInteger('creator_id')->comment('{"src":"default_users.id"}')->nullable();
            // $table->bigInteger('last_editor_id')->comment('{"src":"default_users.id"}')->nullable();
            // $table->timestamps();
            // $table->bigInteger('deletor_id')->nullable();
            // $table->datetime('deleted_at')->nullable();

            // $table->string('code',100)->nullable();
            // $table->string('name',200)->change();
            // $table->bigInteger('city_id')->comment('{"src":"m_gen.id"}')->nullable();
            // $table->string('address',200)->nullable();
            // $table->string('npwp',200)->nullable();
            // $table->string('phone1',200)->nullable();
            // $table->string('phone2',200)->nullable();
            // $table->string('email',200)->nullable();
            // $table->boolean('is_active')->default(true)->nullable();
            // $table->uuid('uid')->unique()->change();
            // $table->bigInteger('city_id')->comment('{"src":"m_general.id"}')->nullable()->change();

        });

        // // // auto generate uuid default pgsql standart v4
        // \DB::statement("ALTER TABLE $this->tableName ALTER COLUMN uid SET DEFAULT uuid_generate_v4();");

    }
}
