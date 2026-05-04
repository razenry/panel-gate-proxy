<?php

return [
    'key' => env('EXTERNAL_API_KEY'),
    'whitelist' => explode(',', env('EXTERNAL_API_WHITELIST', '127.0.0.1')),
    'jwt_secret' => env('JWT_SECRET'),
];
