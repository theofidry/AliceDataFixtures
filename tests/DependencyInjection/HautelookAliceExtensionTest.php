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

use Hautelook\AliceBundle\DependencyInjection\HautelookAliceExtension;
use Hautelook\AliceBundle\Tests\Prophecy\Argument as HautelookAliceBundleArgument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @coversDefaultClass Hautelook\AliceBundle\DependencyInjection\HautelookAliceExtension
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class HautelookAliceExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $extension = new HautelookAliceExtension();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Extension\ExtensionInterface', $extension);
        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface',
            $extension
        );
    }

    /**
     * Covers the edge case where using a unknown driver. The other cases are covered with the other tests of the suite.
     *
     * @cover ::isExtensionEnabled
     */
    public function testIsExtensionEnabled()
    {
        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        try {
            $extension->load(
                [
                    'hautelook_alice' => [
                        'db_drivers' => [
                            'unknown' => null,
                        ],
                    ],
                ],
                $containerBuilder
            );
            $this->fail('Expected \InvalidArgumentException exception to be thrown');
        } catch (InvalidConfigurationException $exception) {
            // Expected result
        }

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests the default configuration with all the extensions disabled. Expects no driver loaded.
     */
    public function testLoadDefaultConfigWithAllExtensionsDisabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => null,
                    'mongodb' => null,
                    'phpcr'   => null,
                ]
            )
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load([], $containerBuilder);

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests the default configuration with all the extensions enabled. Expects all drivers loaded.
     */
    public function testLoadDefaultConfigWithAllExtensionsEnabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => null,
                    'mongodb' => null,
                    'phpcr'   => null,
                ]
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy->setParameter('hautelook_alice.locale', 'en_US')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.seed', 1)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.persist_once', false)->shouldBeCalled();

        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();

        $this->addDoctrineORMDefinitions($containerBuilderProphecy);
        $this->addDoctrineODMDefinitions($containerBuilderProphecy);
        $this->addDoctrinePHPCRDefinitions($containerBuilderProphecy);

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([true]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load([], $containerBuilder);

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests with all drivers enabled and the extensions enabled. Expects all drivers loaded.
     */
    public function testLoadWithAllDriversEnabledAndWithAllExtensionsEnabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => true,
                    'mongodb' => true,
                    'phpcr'   => true,
                ]
            )
            ->shouldBeCalled()
        ;

        $this->addDoctrineORMDefinitions($containerBuilderProphecy);
        $this->addDoctrineODMDefinitions($containerBuilderProphecy);
        $this->addDoctrinePHPCRDefinitions($containerBuilderProphecy);

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([true]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load(
            [
                'hautelook_alice' => [
                    'db_drivers' => [
                        'orm'     => true,
                        'mongodb' => true,
                        'phpcr'   => true,
                    ],
                ],
            ],
            $containerBuilder
        );

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests with all drivers enabled and the extensions disabled. Expects all drivers loaded.
     */
    public function testLoadWithAllDriversEnabledAndWithAllExtensionsDisabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => true,
                    'mongodb' => true,
                    'phpcr'   => true,
                ]
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy->setParameter('hautelook_alice.locale', 'en_US')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.seed', 1)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.persist_once', false)->shouldBeCalled();

        $this->addDoctrineORMDefinitions($containerBuilderProphecy);
        $this->addDoctrineODMDefinitions($containerBuilderProphecy);
        $this->addDoctrinePHPCRDefinitions($containerBuilderProphecy);

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load(
            [
                'hautelook_alice' => [
                    'db_drivers' => [
                        'orm'     => true,
                        'mongodb' => true,
                        'phpcr'   => true,
                    ],
                ],
            ],
            $containerBuilder
        );

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests with all drivers disabled and the extensions enabled. Expects no drivers loaded.
     */
    public function testLoadWithAllDriversDisabledAndWithAllExtensionsEnabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => false,
                    'mongodb' => false,
                    'phpcr'   => false,
                ]
            )
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([true]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load(
            [
                'hautelook_alice' => [
                    'db_drivers' => [
                        'orm'     => false,
                        'mongodb' => false,
                        'phpcr'   => false,
                    ],
                ],
            ],
            $containerBuilder
        );

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests with all drivers disabled and the extensions disabled. Expects no drivers loaded.
     */
    public function testLoadWithAllDriversDisabledAndWithAllExtensionsDisabled()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => false,
                    'mongodb' => false,
                    'phpcr'   => false,
                ]
            )
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([true]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([true]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load(
            [
                'hautelook_alice' => [
                    'db_drivers' => [
                        'orm'     => false,
                        'mongodb' => false,
                        'phpcr'   => false,
                    ],
                ],
            ],
            $containerBuilder
        );

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Tests the loading with some drivers enabled and others disabled. Expects the enabled drivers to be loaded.
     */
    public function testLoadWithPartialDrivers()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        $containerBuilderProphecy
            ->setParameter(
                'hautelook_alice.db_drivers',
                [
                    'orm'     => true,
                    'mongodb' => false,
                    'phpcr'   => true,
                ]
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy->setParameter('hautelook_alice.locale', 'en_US')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.seed', 1)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.persist_once', false)->shouldBeCalled();

        $this->addDoctrineORMDefinitions($containerBuilderProphecy);
        $this->addDoctrinePHPCRDefinitions($containerBuilderProphecy);

        $containerBuilderProphecy->getExtensionConfig('doctrine')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_mongodb')->willReturn([]);
        $containerBuilderProphecy->getExtensionConfig('doctrine_phpcr')->willReturn([]);

        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new HautelookAliceExtension();

        $extension->prepend($containerBuilder);

        $extension->load(
            [
                'hautelook_alice' => [
                    'db_drivers' => [
                        'orm'     => true,
                        'mongodb' => false,
                        'phpcr'   => true,
                    ],
                ],
            ],
            $containerBuilder
        );

        $this->assertTrue(true, 'Expected no error to be thrown.');
    }

    /**
     * Gets a Prophecy object for the ContainerBuilder which includes the mandatory called on the services included in
     * the default config.
     *
     * @return ObjectProphecy
     */
    private function getBaseDefaultContainerBuiderProphecy()
    {
        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderProphecy->getParameterBag()->willReturn(new ParameterBag());
        $containerBuilderProphecy->getDefinition('hautelook_alice.alice.fixtures.loader')->willReturn(new Definition(null, ['foo', 'bar', null, false]));
        $containerBuilderProphecy->getDefinition('hautelook_alice.faker')->willReturn(new Definition());
        $containerBuilderProphecy->setParameter('hautelook_alice.loading_limit', 5)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.locale', 'en_US')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.seed', 1)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('hautelook_alice.persist_once', false)->shouldBeCalled();

        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();

        $containerBuilderProphecy
            ->addResource(HautelookAliceBundleArgument::service(getcwd().'/src/Resources/config/services.xml'))
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->setDefinition('hautelook_alice.alice.fixtures.loader', HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface'))
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition('hautelook_alice.doctrine.executor.fixtures_executor', HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\DataFixtures\Executor\FixturesExecutor'))
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition('hautelook_alice.faker', HautelookAliceBundleArgument::definition('Faker\Generator'))
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.alice.processor_chain',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Alice\ProcessorChain')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.faker.provider_chain',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Faker\Provider\ProviderChain')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.fixtures.loader',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Alice\DataFixtures\Loader\Loader')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.bundle_resolver',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Resolver\BundlesResolver')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.command_factory',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Command\CommandFactory')
            )
            ->shouldBeCalled()
        ;

        return $containerBuilderProphecy;
    }

    /**
     * Adds the prophecy call to the ContainerBuilder for when Doctrine ORM is enabled.
     *
     * @param ObjectProphecy $containerBuilderProphecy
     */
    private function addDoctrineORMDefinitions(ObjectProphecy $containerBuilderProphecy)
    {
        $containerBuilderProphecy
            ->addResource(HautelookAliceBundleArgument::service(getcwd().'/src/Resources/config/orm.xml'))
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->getDefinition('hautelook_alice.doctrine.command.deprecated_load_command')
            ->willReturn(new Definition())
        ;

        $containerBuilderProphecy
            ->getDefinition('hautelook_alice.doctrine.command.load_command')
            ->willReturn(new Definition())
        ;

        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.orm.fixtures_finder',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.orm.loader_generator',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Generator\LoaderGenerator')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.command.deprecated_load_command',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Command\LoadDataFixturesCommand')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.command.load_command',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Command\LoadDataFixturesCommand')
            )
            ->shouldBeCalled()
        ;
    }

    /**
     * Adds the prophecy call to the ContainerBuilder for when Doctrine ODM is enabled.
     *
     * @param ObjectProphecy $containerBuilderProphecy
     */
    private function addDoctrineODMDefinitions(ObjectProphecy $containerBuilderProphecy)
    {
        $containerBuilderProphecy
            ->addResource(HautelookAliceBundleArgument::service(getcwd().'/src/Resources/config/mongodb.xml'))
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->getDefinition('hautelook_alice.doctrine.mongodb.command.load_command')
            ->willReturn(new Definition())
        ;

        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.mongodb.fixtures_finder',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.mongodb.loader_generator',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Generator\LoaderGenerator')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.mongodb.command.load_command',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Command\LoadDataFixturesCommand')
            )
            ->shouldBeCalled()
        ;
    }

    /**
     * Adds the prophecy call to the ContainerBuilder for when Doctrine PHPCR is enabled.
     *
     * @param ObjectProphecy $containerBuilderProphecy
     */
    private function addDoctrinePHPCRDefinitions(ObjectProphecy $containerBuilderProphecy)
    {
        $containerBuilderProphecy
            ->addResource(HautelookAliceBundleArgument::service(getcwd().'/src/Resources/config/phpcr.xml'))
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->getDefinition('hautelook_alice.doctrine.phpcr.command.load_command')
            ->willReturn(new Definition())
        ;

        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.phpcr.fixtures_finder',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.phpcr.loader_generator',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Generator\LoaderGenerator')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'hautelook_alice.doctrine.phpcr.command.load_command',
                HautelookAliceBundleArgument::definition('Hautelook\AliceBundle\Doctrine\Command\LoadDataFixturesCommand')
            )
            ->shouldBeCalled()
        ;
    }
}
