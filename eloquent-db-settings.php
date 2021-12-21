<?php

declare(strict_types=1);

// To keep in sync with docker-compose.yml
return [
    'driver' => get_param('ELOQUENT_DB_DRIVER', 'mysql'),
    'username' => get_param('ELOQUENT_DB_USER', 'root'),
    'password' => get_param('ELOQUENT_DB_PASSWORD', 'password'),
    'database' => get_param('ELOQUENT_DB_NAME', 'fidry_alice_data_fixtures'),
    'host' => get_param('ELOQUENT_DB_HOST', '127.0.0.1'),
    'port' => get_param('ELOQUENT_DB_PORT', 3307),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
    'strict' => true,
];
