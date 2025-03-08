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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'default_purge_mode' => 'delete',
            'db_drivers' => [
                'doctrine_orm' => null,
                'doctrine_mongodb_odm' => null,
                'doctrine_phpcr_odm' => null,
                'eloquent_orm' => null,
            ],
        ];

        $actual = $processor->processConfiguration($configuration, []);

        self::assertEquals($expected, $actual);
    }

    public function testDefaultValuesCanBeOverridden(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'default_purge_mode' => 'truncate',
            'db_drivers' => [
                'doctrine_orm' => true,
                'doctrine_mongodb_odm' => false,
                'doctrine_phpcr_odm' => false,
                'eloquent_orm' => false,
            ],
        ];

        $actual = $processor->processConfiguration(
            $configuration,
            [
                'fidry_alice_data_fixtures' => [
                    'default_purge_mode' => 'truncate',
                    'db_drivers' => [
                        'doctrine_orm' => true,
                        'doctrine_mongodb_odm' => false,
                        'doctrine_phpcr_odm' => false,
                        'eloquent_orm' => false,
                    ],
                ],
            ]
        );

        self::assertEquals($expected, $actual);
    }
}
