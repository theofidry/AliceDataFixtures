<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\FakePurger;
use Fidry\AliceDataFixtures\Persistence\FakePurgerFactory;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\PurgerLoader
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
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new PurgerLoader(new FakeLoader(), new FakePurgerFactory(), new FakePurger());
    }

    public function testPurgesTheDatabaseBeforeLoadingTheFixturesAndReturningTheResult()
    {
        $this->markTestIncomplete('TODO');
        $files = [
            'fixtures1.yml',
        ];
        $parameters = ['foo' => 'bar'];
        $objects = ['dummy' => new \stdClass()];

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($files, $parameters, $objects)
            ->willReturn(
                $expected = [
                    'dummy' => new \stdClass(),
                    'another_dummy' => new \stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $purgerProphecy = $this->prophesize(PurgerInterface::class);
        $purgerProphecy->purge()->shouldBeCalled();
        /** @var PurgerInterface $purger */
        $purger = $purgerProphecy->reveal();

        $loader = new PurgerLoader($loader, $purger);
        $actual = $loader->load($files, $parameters, $objects);

        $this->assertEquals($expected, $actual);

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $purgerProphecy->purge()->shouldHaveBeenCalledTimes(1);
    }
}
