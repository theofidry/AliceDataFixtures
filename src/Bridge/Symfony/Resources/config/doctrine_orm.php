<?php

use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    /*
     * Loaders
     */
    $services
        ->alias(
            'fidry_alice_data_fixtures.loader.doctrine',
            'fidry_alice_data_fixtures.doctrine.purger_loader'
        )
        ->public();

    $services
        ->alias(
            'fidry_alice_data_fixtures.doctrine.loader',
            'fidry_alice_data_fixtures.loader.doctrine'
        );
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set(
            'fidry_alice_data_fixtures.doctrine.purger_loader',
            PurgerLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.doctrine.persister_loader'),
            service('fidry_alice_data_fixtures.persistence.purger_factory.doctrine'),
            param('fidry_alice_data_fixtures.default_purge_mode'),
            service('logger')->ignoreOnInvalid(),
        ]);

    $services
        ->set(
            'fidry_alice_data_fixtures.doctrine.persister_loader',
            PersisterLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.loader.simple'),
            service('fidry_alice_data_fixtures.persistence.persister.doctrine'),
            service('logger')->ignoreOnInvalid(),
        ]);
    // Processors are injected via a Compiler pass

    /*
     * Purger Factory
     */
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger_factory.doctrine',
            'fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory',
            Purger::class
        )
        ->lazy()
        ->args([
            service('doctrine.orm.entity_manager'),
        ]);

    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger.doctrine.orm_purger',
            'fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory'
        );
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.purger_modepurger_mode',
            PurgeMode::class
        )
        ->factory([PurgeMode::class, 'createDeleteMode'])
        ->public(false);
    // Deprecated (see DeprecateServicesPass)

    /*
     * Persisters
     */
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.persister.doctrine',
            'fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister',
            ObjectManagerPersister::class
        )
        ->lazy()
        ->args([
            service('doctrine.orm.entity_manager'),
        ]);
};
