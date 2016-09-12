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
use Fidry\AliceDataFixtures\PersisterInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers Fidry\AliceDataFixtures\Loader\PersisterLoader
 */
class PersisterLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(PersisterLoader::class, LoaderInterface::class, true));
    }

    public function testDecoratesALoaderAndProcessAndPersistEachLoadedObjectBeforeReturningThem()
    {
        $files = [
            'fixtures1.yml',
        ];

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($files, [], [])
            ->willReturn(
                [
                    'dummy' => $dummy = new \stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $persisterProphecy = $this->prophesize(PersisterInterface::class);
        $persisterProphecy->persist($dummy)->shouldBeCalled();
        $persisterProphecy->flush()->shouldBeCalled();
        /** @var PersisterInterface $persister */
        $persister = $persisterProphecy->reveal();

        $firstProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $firstProcessorProphecy->preProcess($dummy)->shouldBeCalled();
        $firstProcessorProphecy->postProcess($dummy)->shouldBeCalled();
        /** @var ProcessorInterface $firstProcessor */
        $firstProcessor = $firstProcessorProphecy->reveal();

        $secondProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $secondProcessorProphecy->preProcess($dummy)->shouldBeCalled();
        $secondProcessorProphecy->postProcess($dummy)->shouldBeCalled();
        /** @var ProcessorInterface $secondProcessor */
        $secondProcessor = $secondProcessorProphecy->reveal();

        $loader = new PersisterLoader($loader, $persister, [$firstProcessor, $secondProcessor]);
        $result = $loader->load($files);

        $this->assertEquals(
            [
                'dummy' => new \stdClass(),
            ],
            $result
        );

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $persisterProphecy->persist(Argument::any())->shouldHaveBeenCalledTimes(1);
        $persisterProphecy->flush()->shouldHaveBeenCalledTimes(1);
        $firstProcessorProphecy->preProcess(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $firstProcessorProphecy->postProcess(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $secondProcessorProphecy->preProcess(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $secondProcessorProphecy->postProcess(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testParametersAndObjectsInjectedArePassedToTheDecoratedLoader()
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = [
            'injected' => true,
        ];
        $objects = [
            'injected' => new \stdClass(),
        ];

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($files, $parameters, $objects)
            ->willReturn(
                [
                    'dummy' => $dummy = new \stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $persisterProphecy = $this->prophesize(PersisterInterface::class);
        /** @var PersisterInterface $persister */
        $persister = $persisterProphecy->reveal();

        $loader = new PersisterLoader($loader, $persister, []);
        $result = $loader->load($files, $parameters, $objects);

        $this->assertEquals(
            [
                'dummy' => new \stdClass(),
            ],
            $result
        );

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
