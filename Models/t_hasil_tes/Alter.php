<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($str, $start, $width, $trimmarker = '', $encoding = null) {
        $str = (string)$str;
        if (strlen($str) <= $width) return $str;
        $markerLen = strlen($trimmarker);
        $sub = substr($str, $start, max(0, $width - $markerLen));
        return $sub . $trimmarker;
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $encoding = null) {
        return strlen((string)$str);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = null) {
        return $length === null ? substr((string)$str, $start) : substr((string)$str, $start, $length);
    }
}

class thasiltes extends Migration
{
    protected $tableName = "t_hasil_tes";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('tahapan_id')->nullable();
        });
    }
}

