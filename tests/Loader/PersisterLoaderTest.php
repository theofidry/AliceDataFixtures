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
use Fidry\AliceDataFixtures\Persistence\FakePersister;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

#[CoversClass(PersisterLoader::class)]
class PersisterLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALoader(): void
    {
        self::assertTrue(is_a(PersisterLoader::class, LoaderInterface::class, true));
    }

    public function testIsPersisterAware(): void
    {
        self::assertTrue(is_a(PersisterLoader::class, PersisterAwareInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(PersisterLoader::class))->isCloneable());
    }

    public function testNamedConstructorIsImmutable(): void
    {
        /** @var PersisterInterface $persister */
        $persister = $this->prophesize(PersisterInterface::class)->reveal();

        $loader = new PersisterLoader(new FakeLoader(), new FakePersister(), null, []);
        $newLoader = $loader->withPersister($persister);

        self::assertEquals(
            new PersisterLoader(new FakeLoader(), new FakePersister(), null, []),
            $loader
        );
        self::assertEquals(
            new PersisterLoader(new FakeLoader(), $persister, null, []),
            $newLoader
        );
    }

    public function testDecoratesALoaderAndProcessAndPersistEachLoadedObjectBeforeReturningThem(): void
    {
        $files = [
            'fixtures1.yml',
        ];

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($files, [], [], null)
            ->willReturn(
                [
                    'dummy' => $dummy = new stdClass(),
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
        $firstProcessorProphecy->preProcess('dummy', $dummy)->shouldBeCalled();
        $firstProcessorProphecy->postProcess('dummy', $dummy)->shouldBeCalled();
        /** @var ProcessorInterface $firstProcessor */
        $firstProcessor = $firstProcessorProphecy->reveal();

        $secondProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $secondProcessorProphecy->preProcess('dummy', $dummy)->shouldBeCalled();
        $secondProcessorProphecy->postProcess('dummy', $dummy)->shouldBeCalled();
        /** @var ProcessorInterface $secondProcessor */
        $secondProcessor = $secondProcessorProphecy->reveal();

        $loader = new PersisterLoader($loader, $persister, null, [$firstProcessor, $secondProcessor]);
        $result = $loader->load($files);

        self::assertEquals(
            [
                'dummy' => new stdClass(),
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

    public function testParametersAndObjectsInjectedArePassedToTheDecoratedLoader(): void
    {
        $files = [
            'fixtures1.yml',
        ];
        $parameters = [
            'injected' => true,
        ];
        $objects = [
            'injected' => new stdClass(),
        ];

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($files, $parameters, $objects, null)
            ->willReturn(
                [
                    'dummy' => $dummy = new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $persisterProphecy = $this->prophesize(PersisterInterface::class);
        /** @var PersisterInterface $persister */
        $persister = $persisterProphecy->reveal();

        $loader = new PersisterLoader($loader, $persister, null, []);
        $result = $loader->load($files, $parameters, $objects);

        self::assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $result
        );

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
