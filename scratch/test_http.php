<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Mocking Http for testing headers
Http::fake();

$token = 'Casktna@to@lJx2t@';
$response = Http::withToken($token)->post('http://example.com');

echo 'Generated Authorization header: '.Http::recorded()[0][0]->header('Authorization')[0]."\n";
