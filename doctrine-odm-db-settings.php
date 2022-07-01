<?php

declare(strict_types=1);

use Fidry\AliceDataFixtures\ParamReader as PR;

// To keep in sync with docker-compose.yml
return [
    'username' => PR::get_param('DOCTRINE_ODM_DB_USER', 'root'),
    'password' => PR::get_param('DOCTRINE_ODM_DB_PASSWORD', 'password'),
    'host' => PR::get_param('DOCTRINE_ODM_DB_HOST', '127.0.0.1'),
    'port' => PR::get_param('DOCTRINE_ODM_DB_PORT', 27018),
];
