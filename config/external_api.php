<?php

return [
    'key' => env('EXTERNAL_API_KEY'),
    'whitelist' => explode(',', env('EXTERNAL_API_WHITELIST', '127.0.0.1')),
    'jwt_secret' => env('JWT_SECRET'),
    'sso_secret' => env('SSO_SECRET', env('JWT_SECRET')),
];
