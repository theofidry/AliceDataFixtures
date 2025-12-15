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

use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Loaders
    $services
        ->alias(
            'fidry_alice_data_fixtures.loader.doctrine_mongodb',
            'fidry_alice_data_fixtures.doctrine_mongodb.purger_loader'
        )
        ->public();

    $services
        ->alias(
            'fidry_alice_data_fixtures.doctrine_mongodb.loader',
            'fidry_alice_data_fixtures.loader.doctrine_mongodb'
        );
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set(
            'fidry_alice_data_fixtures.doctrine_mongodb.purger_loader',
            PurgerLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.doctrine_mongodb.persister_loader'),
            service('fidry_alice_data_fixtures.persistence.purger_factory.doctrine_mongodb'),
            param('fidry_alice_data_fixtures.default_purge_mode'),
        ]);

    $services
        ->set(
            'fidry_alice_data_fixtures.doctrine_mongodb.persister_loader',
            PersisterLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.loader.simple'),
            service('fidry_alice_data_fixtures.persistence.persister.doctrine_mongodb'),
            service('logger')->ignoreOnInvalid(),
        ]);

    // Purger Factory
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger_factory.doctrine_mongodb',
            'fidry_alice_data_fixtures.persistence.doctrine_mongodb.purger.purger_factory'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.doctrine_mongodb.purger.purger_factory',
            Purger::class
        )
        ->lazy()
        ->args([
            service('doctrine_mongodb.odm.document_manager'),
        ]);

    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger.doctrine_mongodb.odm_purger',
            'fidry_alice_data_fixtures.persistence.doctrine_mongodb.purger.purger_factory'
        );
    // Deprecated (see DeprecateServicesPass)

    // Persisters
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.persister.doctrine_mongodb',
            'fidry_alice_data_fixtures.persistence.persister.doctrine_mongodb.object_manager_persister'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.persister.doctrine_mongodb.object_manager_persister',
            ObjectManagerPersister::class
        )
        ->lazy()
        ->args([
            service('doctrine_mongodb.odm.document_manager'),
        ]);
};
