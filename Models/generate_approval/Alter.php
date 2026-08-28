<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class generateapproval extends Migration
{
    protected $tableName = "generate_approval";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            // $table->bigInteger('last_approve_id')->comment('{"src":"default_users.id"}')->nullable();
        });

        // Sinkronisasi form_name untuk pengajuan pelatihan
        \DB::statement("UPDATE generate_approval SET form_name = 't_req_pelatihan' WHERE form_name = 't_request_pelatihan' OR trx_table = 't_request_pelatihan'");
        \DB::statement("UPDATE generate_approval_log SET form_name = 't_req_pelatihan' WHERE form_name = 't_request_pelatihan' OR trx_table = 't_request_pelatihan'");
    }
}
