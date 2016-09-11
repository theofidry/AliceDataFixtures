<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\DependencyInjection;

use Hautelook\AliceBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @coversDefaultClass Hautelook\AliceBundle\DependencyInjection\Configuration
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private static $defaultConfig = [
        'db_drivers'    => [
            'orm'     => null,
            'mongodb' => null,
            'phpcr'   => null,
        ],
        'locale'        => 'en_US',
        'seed'          => 1,
        'persist_once'  => false,
        'loading_limit' => 5,
    ];

    /**
     * @cover ::getConfigTreeBuilder
     */
    public function testDefaultConfig()
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $processor = new Processor();
        $config = $processor->processConfiguration(
            $configuration,
            [
                'hautelook_alice' => [],
            ]
        );
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ConfigurationInterface', $configuration);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $treeBuilder);
        $this->assertSame(self::$defaultConfig, $config);
    }
}
