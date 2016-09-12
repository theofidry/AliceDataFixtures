<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    const ORM_DRIVER = 'doctrine_orm';

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fidry_alice_data_fixtures');

        $rootNode
            ->children()
                ->arrayNode('db_drivers')
                    ->info('The list of enabled drivers.')
                    ->addDefaultsIfNotSet()
                    ->cannotBeOverwritten()
                    ->children()
                        ->booleanNode(self::ORM_DRIVER)
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
