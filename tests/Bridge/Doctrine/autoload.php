<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once __DIR__.'/../../../vendor-bin/doctrine/vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

$config = Setup::createAnnotationMetadataConfiguration(
    [
        __DIR__.'/../../../fixtures/Bridge/Doctrine/Entity',
    ],
    true
);

$entityManager = EntityManager::create(
    [
        'driver' => false !== getenv('DB_DRIVER')? getenv('DB_DRIVER') : 'pdo_mysql',
        'user' => false !== getenv('DB_USER')? getenv('DB_USER') : 'root',
        'password' => false !== getenv('DB_PASSWORD')? getenv('DB_PASSWORD') : null,
        'dbname' => false !== getenv('DB_NAME')? getenv('DB_NAME') : 'fidry_alice_data_fixtures',
    ],
    $config
);

$GLOBALS['entity_manager'] = $entityManager;
