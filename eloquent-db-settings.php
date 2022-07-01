<?php

declare(strict_types=1);

use Fidry\AliceDataFixtures\ParamReader as PR;

// To keep in sync with docker-compose.yml
return [
    'driver' => PR::get_param('ELOQUENT_DB_DRIVER', 'mysql'),
    'username' => PR::get_param('ELOQUENT_DB_USER', 'root'),
    'password' => PR::get_param('ELOQUENT_DB_PASSWORD', 'password'),
    'database' => PR::get_param('ELOQUENT_DB_NAME', 'fidry_alice_data_fixtures'),
    'host' => PR::get_param('ELOQUENT_DB_HOST', '127.0.0.1'),
    'port' => PR::get_param('ELOQUENT_DB_PORT', 3307),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
    'strict' => true,
];
