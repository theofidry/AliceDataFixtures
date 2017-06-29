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
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    /** @private */
    const DOCTRINE_ORM_DRIVER = 'doctrine_orm';
    /** @private */
    const DOCTRINE_MONGODB_ODM_DRIVER = 'doctrine_mongodb_odm';
    /** @private */
    const DOCTRINE_PHPCR_ODM_DRIVER = 'doctrine_phpcr_odm';
    /** @private */
    const ELOQUENT_ORM_DRIVER = 'eloquent_orm';

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
