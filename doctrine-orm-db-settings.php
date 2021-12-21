<?php

declare(strict_types=1);

// To keep in sync with docker-compose.yml
return [
    'driver' => get_param('DOCTRINE_ORM_DB_DRIVER', 'pdo_mysql'),
    'user' => get_param('DOCTRINE_ORM_DB_USER', 'root'),
    'password' => get_param('DOCTRINE_ORM_DB_PASSWORD', 'password'),
    'dbname' => get_param('DOCTRINE_ORM_DB_NAME', 'fidry_alice_data_fixtures'),
    'host' => get_param('DOCTRINE_ORM_DB_HOST', '127.0.0.1'),
    'port' => get_param('DOCTRINE_ORM_DB_PORT', 3307),
];
