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

$autoload = require_once __DIR__.'/../../../vendor-bin/doctrine_phpcr/vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver;
use Jackalope\RepositoryFactoryDoctrineDBAL;

$params = array(
    'driver' => false !== getenv('DB_DRIVER')? getenv('DB_DRIVER') : 'pdo_mysql',
    'user' => false !== getenv('DB_USER')? getenv('DB_USER') : 'root',
    'password' => false !== getenv('DB_PASSWORD')? getenv('DB_PASSWORD') : null,
    'dbname' => false !== getenv('DB_NAME')? getenv('DB_NAME') : 'fidry_alice_data_fixtures',
);

$workspace = 'default';
$user = 'admin';
$pass = 'admin';

$dbConn = DriverManager::getConnection($params);
$factory = new RepositoryFactoryDoctrineDBAL();

$parameters = array('jackalope.doctrine_dbal_connection' => $dbConn);
$repository = $factory->getRepository($parameters);
$credentials = new \PHPCR\SimpleCredentials(null, null);

$session = $repository->login($credentials, $workspace);

/* prepare the doctrine configuration */

//AnnotationRegistry::registerLoader(array($autoload, 'loadClass'));

$reader = new AnnotationReader();
$driver = new AnnotationDriver($reader, array(
    // this is a list of all folders containing document classes
    __DIR__.'/../../../vendor-bin/doctrine_phpcr/vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Document',
    __DIR__.'/../../../fixtures/Bridge/Doctrine/PhpCrDocument',
));

$config = new Configuration();
$config->setMetadataDriverImpl($driver);

$documentManager = DocumentManager::create($session, $config);

$GLOBALS['document_manager'] = $documentManager;
