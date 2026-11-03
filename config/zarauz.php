<?php

return [
    'monthly_fee' => (int) env('ZARAUZ_MONTHLY_FEE', 10000),
    'club_name' => env('ZARAUZ_CLUB_NAME', 'Zaraúz FC'),
    'admin' => [
        'name' => env('ADMIN_NAME', 'Tesorero Zaraúz FC'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
