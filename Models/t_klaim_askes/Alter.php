<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan DB facade

class tklaimaskes extends Migration
{
    protected $tableName = "t_klaim_askes";
    
    public function up()
    {
        // Update data yang periode_awal atau periode_akhir-nya masih kosong
        // dengan mengekstrak bagian tanggal (DATE) dari kolom created_at
        DB::table($this->tableName)
            ->whereNull('periode_awal')
            ->orWhereNull('periode_akhir')
            ->orWhere('periode_awal', '') // Antisipasi jika string kosong
            ->update([
                'periode_awal' => DB::raw('DATE(created_at)'),
                'periode_akhir' => DB::raw('DATE(created_at)'),
            ]);
            
      
    }

    public function down()
    {
        // Opsional: Jika di-rollback, kamu bisa mengosongkannya kembali (khusus data tertentu)
        // Namun biasanya untuk manipulasi data historis, down() dibiarkan kosong
        // atau disesuaikan dengan kebutuhan bisnis.
    }
}