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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @private
 */
final class Configuration implements ConfigurationInterface
{
    public const DOCTRINE_ORM_DRIVER = 'doctrine_orm';
    public const DOCTRINE_MONGODB_ODM_DRIVER = 'doctrine_mongodb_odm';
    public const DOCTRINE_PHPCR_ODM_DRIVER = 'doctrine_phpcr_odm';
    public const ELOQUENT_ORM_DRIVER = 'eloquent_orm';

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fidry_alice_data_fixtures');

        $rootNode
            ->children()
                ->scalarNode('default_purge_mode')
                    ->defaultValue('delete')
                    ->validate()
                    ->ifNotInArray(['delete', 'truncate', 'no_purge'])
                        ->thenInvalid('Invalid purge mode %s. Choose either "delete", "truncate" or "no_purge".')
                    ->end()
                ->end()

                ->arrayNode('db_drivers')
                    ->info('The list of enabled drivers.')
                    ->addDefaultsIfNotSet()
                    ->cannotBeOverwritten()
                    ->children()
                        ->booleanNode(self::DOCTRINE_ORM_DRIVER)
                            ->defaultValue(null)
                        ->end()
                        ->booleanNode(self::DOCTRINE_MONGODB_ODM_DRIVER)
                            ->defaultValue(null)
                        ->end()
                        ->booleanNode(self::DOCTRINE_PHPCR_ODM_DRIVER)
                            ->defaultValue(null)
                        ->end()
                        ->booleanNode(self::ELOQUENT_ORM_DRIVER)
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
