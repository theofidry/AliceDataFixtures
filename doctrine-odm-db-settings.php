<?php

declare(strict_types=1);

// To keep in sync with docker-compose.yml
return [
    'username' => get_param('DOCTRINE_ODM_DB_USER', 'root'),
    'password' => get_param('DOCTRINE_ODM_DB_PASSWORD', 'password'),
    'host' => get_param('DOCTRINE_ODM_DB_HOST', '127.0.0.1'),
    'port' => get_param('DOCTRINE_ODM_DB_PORT', 27018),
];
