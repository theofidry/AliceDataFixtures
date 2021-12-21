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

require ROOT.'/vendor-bin/doctrine_mongodb/vendor/autoload.php';

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;

$config = new Configuration();
$config->setProxyDir(sys_get_temp_dir().'/mongo_proxies_'.crc32((string) mt_rand()));
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(sys_get_temp_dir().'/mongo_hydrators_'.crc32((string) mt_rand()));
$config->setHydratorNamespace('Hydrators');
$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__.'/../../../fixtures/Bridge/Doctrine/MongoDocument'));
$config->setDefaultDB('fidry_alice_data_fixtures');

[
    'username' => $userName,
    'password' => $password,
    'host' => $host,
    'port' => $port
] = require ROOT.'/doctrine-odm-db-settings.php';

$uri = sprintf(
    'mongodb://%s:%s@%s:%s',
    $userName,
    $password,
    $host,
    $port,
);

$documentManagerFactory = static fn () => DocumentManager::create(
    new Client(
        $uri,
        [],
        ['typeMap' => DocumentManager::CLIENT_TYPEMAP],
    ),
    $config,
);

$GLOBALS['document_manager_factory'] = $documentManagerFactory;
