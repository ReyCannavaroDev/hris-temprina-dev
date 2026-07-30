1<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class defaultusers extends Migration
{
    protected $tableName = "default_users";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['status']);
            // $table->string('telp')->nullable();
            // $table->bigInteger('m_os_id')->comment('{"src":"m_company_outsourcing.id"}')->nullable();
            // $table->string('user_type')->nullable();
            // $table->string('note')->nullable();
            // $table->string('username',60)->change();
            // $table->string('password')->change();
            // $table->string('profil_image', 255)->nullable();
            // $table->boolean('is_sync')->nullable()->default(false);
            // $table->string('status')->nullable()->default('active');
        });
    }
}
