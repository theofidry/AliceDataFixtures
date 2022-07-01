<?php

declare(strict_types=1);

use Fidry\AliceDataFixtures\ParamReader as PR;

// To keep in sync with docker-compose.yml
return [
    'driver' => PR::get_param('DOCTRINE_PHPCR_DB_DRIVER', 'pdo_mysql'),
    'user' => PR::get_param('DOCTRINE_PHPCR_DB_USER', 'root'),
    'password' => PR::get_param('DOCTRINE_PHPCR_DB_PASSWORD', 'password'),
    'dbname' => PR::get_param('DOCTRINE_PHPCR_DB_NAME', 'fidry_alice_data_fixtures'),
    'host' => PR::get_param('DOCTRINE_PHPCR_DB_HOST', '127.0.0.1'),
    'port' => PR::get_param('DOCTRINE_PHPCR_DB_PORT', 3307),
];
