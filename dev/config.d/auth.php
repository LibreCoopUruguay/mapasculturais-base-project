<?php

return [
    // 'auth.provider' => 'Fake',
    'auth.provider' => '\MultipleLocalAuth\Provider',
    'auth.config' => [
        'salt' => env('AUTH_SALT', null),
        'timeout' => '24 hours',
        'strategies' => []
    ]
];