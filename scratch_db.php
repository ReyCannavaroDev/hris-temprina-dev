<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = DB::table('t_request_pelatihan_d_kary')->where('t_request_pelatihan_id', 13)->get();
echo json_encode($res);
