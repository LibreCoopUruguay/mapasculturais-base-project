<?php

return [
    // 'auth.provider' => 'Fake',
    'auth.provider' => 'MultipleLocalAuth\Provider',
    'auth.config' => [
        'salt' => env('AUTH_SALT', null),
        'timeout' => '24 hours',
        'strategies' => []
    ],
    'wp_sso.secret' => env('WP_SSO_SECRET', 'fallback_dev_secret_12345')
];