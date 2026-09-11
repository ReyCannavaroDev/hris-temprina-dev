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

class tloker extends Migration
{
    protected $tableName = "t_loker";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn(['is_active']);
            // $table->boolean('is_active')->nullable()->default(false);
            // $table->bigInteger('jk_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->bigInteger('status_kary_id')->comment('{"src":"m_general.id"}')->nullable();
            // $table->bigInteger('jumlah')->nullable();
            $table->bigInteger('t_req_recruitment_id')->comment('{"src":"t_req_recruitment.id"}')->nullable();
        });
    }
}
