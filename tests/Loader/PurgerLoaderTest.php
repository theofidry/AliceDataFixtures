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
use Fidry\AliceDataFixtures\Persistence\FakePurgerFactory;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\PurgerLoader
 *
 * @uses \Fidry\AliceDataFixtures\Persistence\PurgeMode
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PurgerLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(PurgerLoader::class, LoaderInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new PurgerLoader(new FakeLoader(), new FakePurgerFactory());
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

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory);
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }

    public function testIfNoPurgeModeIsGivenThenUseDeleteByDefault()
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

        $loader = new PurgerLoader($decoratedLoader, $purgerFactory);
        $actual = $loader->load($files, $parameters, $objects, $purgeMode);

        $this->assertEquals($expected, $actual);

        $decoratedLoaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }
}
