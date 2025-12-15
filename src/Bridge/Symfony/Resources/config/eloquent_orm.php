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

use Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $container->parameters()
        ->set('fidry_alice_data_fixtures.eloquent.migration_path', 'migrations');

    // Loaders
    $services
        ->alias(
            'fidry_alice_data_fixtures.loader.eloquent',
            'fidry_alice_data_fixtures.eloquent.purger_loader'
        )
        ->public();

    $services
        ->alias(
            'fidry_alice_data_fixtures.eloquent.loader',
            'fidry_alice_data_fixtures.loader.eloquent'
        );
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set(
            'fidry_alice_data_fixtures.eloquent.purger_loader',
            PurgerLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.eloquent.persister_loader'),
            service('fidry_alice_data_fixtures.persistence.purger_factory.eloquent'),
            param('fidry_alice_data_fixtures.default_purge_mode'),
            service('logger')->ignoreOnInvalid(),
        ]);

    $services
        ->set(
            'fidry_alice_data_fixtures.eloquent.persister_loader',
            PersisterLoader::class
        )
        ->lazy()
        ->args([
            service('fidry_alice_data_fixtures.loader.simple'),
            service('fidry_alice_data_fixtures.persistence.persister.eloquent'),
            service('logger')->ignoreOnInvalid(),
        ]);

    // Purger Factory
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger_factory.eloquent',
            'fidry_alice_data_fixtures.persistence.eloquent.purger.purger_factory'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.eloquent.purger.purger_factory',
            ModelPurger::class
        )
        ->lazy()
        ->args([
            service('wouterj_eloquent.migrations.repository'),
            param('fidry_alice_data_fixtures.eloquent.migration_path'),
            service('wouterj_eloquent.migrator'),
        ]);

    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.purger.eloquent.model_purger',
            'fidry_alice_data_fixtures.persistence.eloquent.purger.purger_factory'
        );
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.purger_mode',
            PurgeMode::class
        )
        ->factory([PurgeMode::class, 'createDeleteMode'])
        ->private();
    // Deprecated (see DeprecateServicesPass)

    // Persisters
    $services
        ->alias(
            'fidry_alice_data_fixtures.persistence.persister.eloquent',
            'fidry_alice_data_fixtures.persistence.persister.eloquent.model_persister'
        )
        ->public();

    $services
        ->set(
            'fidry_alice_data_fixtures.persistence.persister.eloquent.model_persister',
            ModelPersister::class
        )
        ->lazy()
        ->args([
            service('wouterj_eloquent.database_manager'),
        ]);
};
