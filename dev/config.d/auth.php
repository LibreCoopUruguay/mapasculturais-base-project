<?php

return [
    // 'auth.provider' => 'Fake',
    'auth.provider' => '\MandatoryMFA\Provider',
    'auth.config' => [
        'salt' => env('AUTH_SALT', null),
        'timeout' => '24 hours',
        'strategies' => []
    ]
];