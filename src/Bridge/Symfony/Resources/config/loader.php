<?php

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
