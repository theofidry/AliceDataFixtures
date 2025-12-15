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

use Fidry\AliceDataFixtures\Loader\MultiPassLoader;
use Fidry\AliceDataFixtures\Loader\SimpleLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('fidry_alice_data_fixtures.loader.multipass_file', MultiPassLoader::class)
        ->lazy()
        ->args([
            service('nelmio_alice.file_loader'),
        ]);
    // Deprecated (see DeprecateServicesPass)

    $services
        ->set('fidry_alice_data_fixtures.loader.simple', SimpleLoader::class)
        ->lazy()
        ->args([
            service('nelmio_alice.files_loader'),
            service('logger')->ignoreOnInvalid(),
        ]);
};
