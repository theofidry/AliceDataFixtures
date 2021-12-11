<?php

declare(strict_types=1);

function get_param(string $envName, $default) {
    $env = getenv($envName);

    return false !== $env ? $env : $default;
}

// To keep in sync with docker-compose.yml
return [
    'username' => get_param('DOCTRINE_ODM_DB_USER', 'root'),
    'password' => get_param('DOCTRINE_ODM_DB_PASSWORD', 'password'),
    'host' => get_param('DOCTRINE_ODM_DB_HOST', '127.0.0.1'),
    'port' => get_param('DOCTRINE_ODM_DB_PORT', 27018),
];
