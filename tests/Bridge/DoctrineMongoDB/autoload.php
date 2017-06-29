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

require_once __DIR__.'/../../../vendor-bin/doctrine_mongodb/vendor/autoload.php';

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

AnnotationDriver::registerAnnotationClasses();

$config = new Configuration();
$config->setProxyDir(sys_get_temp_dir().'/mongo_proxies_'.crc32((string) mt_rand()));
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(sys_get_temp_dir().'/mongo_hydrators_'.crc32((string) mt_rand()));
$config->setHydratorNamespace('Hydrators');
$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__.'/../../../fixtures/Bridge/Doctrine/MongoDocument'));
$config->setDefaultDB('fidry_alice_data_fixtures');

$dm = DocumentManager::create(new Connection(), $config);

$GLOBALS['document_manager'] = $dm;
