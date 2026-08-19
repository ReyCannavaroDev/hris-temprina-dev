<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\BasicModels\default_users::first(); 
auth()->login($user); 
$request = Illuminate\Http\Request::create('/operation/t_request_pelatihan/1', 'GET', ['join' => 'true', 'transform' => 'false']); 
$response = app()->handle($request); 
echo $response->getContent();
