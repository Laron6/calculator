<?php

return [
    /* Настройки CORS для API */
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
    
    /* Настройки API */
    'version' => 'v1',
    'rate_limit' => 60,
    'cache_ttl' => 3600,
];