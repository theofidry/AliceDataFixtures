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

const ROOT = __DIR__.'/../../..';

require_once ROOT.'/vendor-bin/doctrine/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [ROOT.'/fixtures/Bridge/Doctrine/Entity'],
    isDevMode: true,
);

$connection = DriverManager::getConnection(
    require ROOT.'/doctrine-orm-db-settings.php',
    $config,
);

$GLOBALS['entity_manager'] = new EntityManager($connection, $config);
