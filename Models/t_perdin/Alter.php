<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tperdin extends Migration
{
    protected $tableName = "t_perdin";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'tanggal_surat_tugas')) {
                $table->date('tanggal_surat_tugas')->nullable();
            }
            if (!Schema::hasColumn($this->tableName, 'tanggal_rencana_biaya')) {
                $table->date('tanggal_rencana_biaya')->nullable();
            }
        });
    }
}