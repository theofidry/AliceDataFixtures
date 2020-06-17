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

use Symfony\Component\DependencyInjection\Compiler\AliasDeprecatedPublicServicesPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Flags deprecated services and aliases with pre/post Symfony 5.1 compatibility layer.
 *
 * @private
 */
final class DeprecateServicesPass implements CompilerPassInterface
{
    private const DEPRECATED_SERVICES = [
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
    private const DEPRECATED_ALIASES = [
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
    private const SERVICE_TEMPLATE = 'The service "%service_id%" is deprecated and will be removed in future versions.';
    private const ALIAS_TEMPLATE = 'The service alias "%alias_id%" is deprecated and will be removed in future versions.';

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        // Symfony 5.1
        $canDeprecateAliases = class_exists(AliasDeprecatedPublicServicesPass::class);

        foreach (self::DEPRECATED_SERVICES as $id => [$alternative, $version]) {
            if (false === $container->hasDefinition($id)) {
                continue;
            }

            $definition = $container->getDefinition($id);

            // Compatibility layer for Definition::setDeprecated()
            if ($canDeprecateAliases) {
                $definition->setDeprecated('theofidry/alice-data-fixtures', $version, self::SERVICE_TEMPLATE.$alternative);
            } else {
                $definition->setDeprecated(true, self::SERVICE_TEMPLATE.$alternative);
            }
        }

        if (!$canDeprecateAliases) {
            return;
        }

        foreach (self::DEPRECATED_ALIASES as $id => [$alternative, $version]) {
            if (false === $container->hasAlias($id)) {
                continue;
            }

            $definition = $container->getAlias($id);
            $definition->setDeprecated('theofidry/alice-data-fixtures', $version, self::ALIAS_TEMPLATE.$alternative);
        }
    }
}
