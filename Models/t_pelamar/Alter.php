<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
if (!function_exists('mb_strwidth')) {
    function mb_strwidth($str, $encoding = null) {
        return strlen((string)$str);
    }
}
if (!function_exists('mb_ord')) {
    function mb_ord($str, $encoding = null) {
        return ord((string)$str);
    }
}
if (!function_exists('mb_chr')) {
    function mb_chr($code, $encoding = null) {
        return chr($code);
    }
}

class tpelamar extends Migration
{
    protected $tableName = "t_pelamar";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['m_divisi_id', 'm_dept_id', 'm_posisi_id']);
            $table->bigInteger('t_loker_id')->comment('{"src":"t_loker.id"}')->nullable();
            $table->string('file_cv')->nullable();
            $table->string('file_dokumen')->nullable();
        });
    }
}
