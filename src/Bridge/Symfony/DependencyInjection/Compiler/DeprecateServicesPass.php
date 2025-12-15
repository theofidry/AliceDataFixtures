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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Flags deprecated services and aliases.
 *
 * @private
 */
final class DeprecateServicesPass implements CompilerPassInterface
{
    private const array DEPRECATED_SERVICES = [
        'fidry_alice_data_fixtures.loader.multipass_file' => [
            '',
            '1.0'
        ],
        'fidry_alice_data_fixtures.persistence.purger_mode' => [
            'Inject the purger or purger factory directly instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.persistence.purger_modepurger_mode' => [
            'Inject the purger or purger factory directly instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.persistence.purger.doctrine_phpcr.odm_purger' => [
            'Use "fidry_alice_data_fixtures.persistence.doctrine_phpcr.purger.purger_factory" instead.',
            '1.0',
        ],
    ];
    private const array DEPRECATED_ALIASES = [
        'fidry_alice_data_fixtures.persistence.purger.doctrine.orm_purger' => [
            'Use "fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.doctrine.loader' => [
            'Use "fidry_alice_data_fixtures.loader.doctrine" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.persistence.purger.eloquent.model_purger' => [
            'Use "fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.eloquent.loader' => [
            'Use "fidry_alice_data_fixtures.loader.eloquent" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.doctrine_phpcr.loader' => [
            'Use "fidry_alice_data_fixtures.persistence.doctrine_phpcr.purger.purger_factory" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.doctrine_mongodb.loader' => [
            'Use "fidry_alice_data_fixtures.loader.doctrine_mongodb" instead.',
            '1.0',
        ],
        'fidry_alice_data_fixtures.persistence.purger.doctrine_mongodb.odm_purger' => [
            'Use "fidry_alice_data_fixtures.persistence.doctrine_mongodb.purger.purger_factory" instead.',
            '1.0',
        ],
    ];
    private const string SERVICE_TEMPLATE = 'The service "%service_id%" is deprecated and will be removed in future versions.';
    private const string ALIAS_TEMPLATE = 'The service alias "%alias_id%" is deprecated and will be removed in future versions.';

    public function process(ContainerBuilder $container): void
    {
        foreach (self::DEPRECATED_SERVICES as $id => [$alternative, $version]) {
            if (false === $container->hasDefinition($id)) {
                continue;
            }

            $container
                ->getDefinition($id)
                ->setDeprecated('theofidry/alice-data-fixtures', $version, self::SERVICE_TEMPLATE.$alternative);
        }

        foreach (self::DEPRECATED_ALIASES as $id => [$alternative, $version]) {
            if (false === $container->hasAlias($id)) {
                continue;
            }

            $container
                ->getAlias($id)
                ->setDeprecated('theofidry/alice-data-fixtures', $version, self::ALIAS_TEMPLATE.$alternative);
        }
    }
}
