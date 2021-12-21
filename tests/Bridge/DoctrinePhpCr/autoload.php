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

$autoload = ROOT.'/vendor-bin/doctrine_phpcr/vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\DriverManager;
use Doctrine\ODM\PHPCR\Configuration;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver;
use Jackalope\RepositoryFactoryDoctrineDBAL;
use PHPCR\SessionInterface;
use PHPCR\SimpleCredentials;

$documentManagerFactory = static function () {
    $session = (static function (): SessionInterface {
        $connection = DriverManager::getConnection(
            require ROOT.'/doctrine-phpcr-db-settings.php',
        );
        $repositoryFactory = new RepositoryFactoryDoctrineDBAL();

        $repository = $repositoryFactory->getRepository([
            'jackalope.doctrine_dbal_connection' => $connection,
        ]);

        return $repository->login(
            new SimpleCredentials(null, null),
            'default',
        );
    })();

    $config = (static function (): Configuration {
        $driver = new AnnotationDriver(
            new AnnotationReader(),
            [
                ROOT.'/vendor-bin/doctrine_phpcr/vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Document',
                ROOT.'/fixtures/Bridge/Doctrine/PhpCrDocument',
            ],
        );

        $config = new Configuration();
        $config->setMetadataDriverImpl($driver);

        return $config;
    })();

    return DocumentManager::create($session, $config);
};

$GLOBALS['document_manager_factory'] = $documentManagerFactory;
