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

namespace Fidry\AliceDataFixtures\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\PurgerLoader
 *
 * @uses \Fidry\AliceDataFixtures\Persistence\PurgeMode
 */
class PurgerLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(PurgerLoader::class, LoaderInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(PurgerLoader::class))->isCloneable());
    }

    public function testPurgesTheDatabaseBeforeLoadingTheFixturesAndReturningTheResult()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new stdClass()];
        $purgeMode = PurgeMode::createTruncateMode();

        $decoratedLoaderProphecy = $this->prophesize(LoaderInterface::class);
        $decoratedLoaderProphecy
            ->load($files, $parameters, $objects, $purgeMode)
            ->willReturn(
                $expected = [
                    'dummy' => new stdClass(),
                    'another_dummy' => new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $decoratedLoader */
        $decoratedLoader = $decoratedLoaderProphecy->reveal();

        /** @var PurgerInterface|ObjectProphecy $purgerProphecy */
        $purgerProphecy = $this->prophesize(PurgerInterface::class);
        $purgerProphecy->purge()->shouldBeCalled();
        /** @var PurgerInterface $purger */
        $purger = $purgerProphecy->reveal();

        /** @var PurgerFactoryInterface|ObjectProphecy $purgerFactoryProphecy */
        $purgerFactoryProphecy = $this->prophesize(PurgerFactoryInterface::class);
        $purgerFactoryProphecy->create($purgeMode)->willReturn($purger);
        /** @var PurgerFactoryInterface $purgerFactory */
        $purgerFactory = $purgerFactoryProphecy->reveal();

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory, 'delete', null);
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }

    public function testIfNoPurgeModeIsGivenThenUseDefaultPurgeModeWithDelete()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new stdClass()];
        $purgeMode = null;

        $decoratedLoaderProphecy = $this->prophesize(LoaderInterface::class);
        $decoratedLoaderProphecy
            ->load($files, $parameters, $objects, PurgeMode::createDeleteMode())
            ->willReturn(
                $expected = [
                    'dummy' => new stdClass(),
                    'another_dummy' => new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $decoratedLoader */
        $decoratedLoader = $decoratedLoaderProphecy->reveal();

        /** @var PurgerInterface|ObjectProphecy $purgerProphecy */
        $purgerProphecy = $this->prophesize(PurgerInterface::class);
        $purgerProphecy->purge()->shouldBeCalled();
        /** @var PurgerInterface $purger */
        $purger = $purgerProphecy->reveal();

        /** @var PurgerFactoryInterface|ObjectProphecy $purgerFactoryProphecy */
        $purgerFactoryProphecy = $this->prophesize(PurgerFactoryInterface::class);
        $purgerFactoryProphecy->create(PurgeMode::createDeleteMode())->willReturn($purger);
        /** @var PurgerFactoryInterface $purgerFactory */
        $purgerFactory = $purgerFactoryProphecy->reveal();

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory, 'delete');
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }

    public function testIfNoPurgeModeIsGivenThenUseDefaultPurgeModeWithTruncate()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new stdClass()];
        $purgeMode = null;

        $decoratedLoaderProphecy = $this->prophesize(LoaderInterface::class);
        $decoratedLoaderProphecy
            ->load($files, $parameters, $objects, PurgeMode::createTruncateMode())
            ->willReturn(
                $expected = [
                    'dummy' => new stdClass(),
                    'another_dummy' => new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $decoratedLoader */
        $decoratedLoader = $decoratedLoaderProphecy->reveal();

        /** @var PurgerInterface|ObjectProphecy $purgerProphecy */
        $purgerProphecy = $this->prophesize(PurgerInterface::class);
        $purgerProphecy->purge()->shouldBeCalled();
        /** @var PurgerInterface $purger */
        $purger = $purgerProphecy->reveal();

        /** @var PurgerFactoryInterface|ObjectProphecy $purgerFactoryProphecy */
        $purgerFactoryProphecy = $this->prophesize(PurgerFactoryInterface::class);
        $purgerFactoryProphecy->create(PurgeMode::createTruncateMode())->willReturn($purger);
        /** @var PurgerFactoryInterface $purgerFactory */
        $purgerFactory = $purgerFactoryProphecy->reveal();

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory, 'truncate');
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }

    public function testIfNoPurgeModeIsGivenThenUseDefaultPurgeModeWithNoPurge()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new stdClass()];
        $purgeMode = null;

        $decoratedLoaderProphecy = $this->prophesize(LoaderInterface::class);
        $decoratedLoaderProphecy
            ->load($files, $parameters, $objects, PurgeMode::createNoPurgeMode())
            ->willReturn(
                $expected = [
                    'dummy' => new stdClass(),
                    'another_dummy' => new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $decoratedLoader */
        $decoratedLoader = $decoratedLoaderProphecy->reveal();

        /** @var PurgerInterface|ObjectProphecy $purgerProphecy */
        $purgerProphecy = $this->prophesize(PurgerInterface::class);
        $purgerProphecy->purge()->shouldNotBeenCalled();
        /** @var PurgerInterface $purger */
        $purger = $purgerProphecy->reveal();

        /** @var PurgerFactoryInterface|ObjectProphecy $purgerFactoryProphecy */
        $purgerFactoryProphecy = $this->prophesize(PurgerFactoryInterface::class);
        $purgerFactoryProphecy->create(PurgeMode::createNoPurgeMode())->willReturn($purger);
        /** @var PurgerFactoryInterface $purgerFactory */
        $purgerFactory = $purgerFactoryProphecy->reveal();

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory, 'no_purge');
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldNotBeenCalled();
        $purgerProphecy->purge()->shouldNotBeenCalled();
    }

    public function testDoesNotPurgeOnNoPurgeModeGiven()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new stdClass()];
        $purgeMode = PurgeMode::createNoPurgeMode();

        $decoratedLoaderProphecy = $this->prophesize(LoaderInterface::class);
        $decoratedLoaderProphecy
            ->load($files, $parameters, $objects, $purgeMode)
            ->willReturn(
                $expected = [
                    'dummy' => new stdClass(),
                    'another_dummy' => new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $decoratedLoader */
        $decoratedLoader = $decoratedLoaderProphecy->reveal();

        /** @var PurgerFactoryInterface|ObjectProphecy $purgerFactoryProphecy */
        $purgerFactoryProphecy = $this->prophesize(PurgerFactoryInterface::class);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldNotBeCalled();
        /** @var PurgerFactoryInterface $purgerFactory */
        $purgerFactory = $purgerFactoryProphecy->reveal();

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory, 'delete');
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
