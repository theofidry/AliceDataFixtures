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

use Fidry\AliceDataFixtures\Alice\Exception\RootLoadingException;
use Fidry\AliceDataFixtures\Alice\Loader\FakeFileLoader;
use Fidry\AliceDataFixtures\Exception\MaxPassReachedException;
use Fidry\AliceDataFixtures\LoaderInterface;
use InvalidArgumentException;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\MultiPassLoader
 *
 * @uses \Fidry\AliceDataFixtures\Loader\ErrorTracker
 * @uses \Fidry\AliceDataFixtures\Loader\FileTracker
 * @uses \Fidry\AliceDataFixtures\Exception\MaxPassReachedException
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MultiPassFileLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(MultiPassLoader::class, LoaderInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new MultiPassLoader(new FakeFileLoader());
    }

    /**
     * @dataProvider provideMaxPassValue
     */
    public function testMaxPassGivenMustBeAStrictlyPositiveInteger(int $maxPass, string $expectedExceptionMessage = null)
    {
        try {
            new MultiPassLoader(new FakeFileLoader(), $maxPass);

            if (null !== $expectedExceptionMessage) {
                $this->fail('Expected exception to be thrown.');
            }
        } catch (InvalidArgumentException $exception) {
            if (null === $expectedExceptionMessage) {
                $this->fail('Did not except exception to be thrown.');
            }

            $this->assertEquals($expectedExceptionMessage, $exception->getMessage());
        }

        $this->assertTrue(true, 'Everything is fine.');
    }

    public function testReturnsAnEmptySetIfNoFileGiven()
    {
        $expected = [];

        $loader = new MultiPassLoader(new FakeFileLoader());
        $actual = $loader->load([]);

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsInjectedObjectsAndObjectsIfNoFileGiven()
    {
        $parameters = [];
        $expected = $objects = ['dummy' => new \stdClass()];

        $loader = new MultiPassLoader(new FakeFileLoader());
        $actual = $loader->load([], $parameters, $objects);

        $this->assertEquals($expected, $actual);
    }

    public function testDecoratesTheFileLoaderToReturnTheObjectsLoaded()
    {
        $files = ['foo'];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile('foo', [], [])
            ->willReturn(
                new ObjectSet(new ParameterBag(), new ObjectBag(['dummy' => new \stdClass()]))
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $expected = ['dummy' => new \stdClass()];

        $loader = new MultiPassLoader($fileLoader);
        $actual = $loader->load($files);

        $this->assertEquals($expected, $actual);

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testLoadedFilesInSeveralPassesUntilAllFilesAreLoaded()
    {
        $files = ['file1', 'file2', 'file3', 'file4'];
        $parameters = ['foo' => 'bar'];
        $objects = ['injected' => new \stdClass()];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile('file1', $parameters, $objects)
            ->willReturn(
                new ObjectSet(
                    new ParameterBag($parametersReturnedByFirstLoadOfFile1 = [
                        'foo' => 'bar',
                        'file1' => 'pass',
                    ]),
                    new ObjectBag($objectsReturnedByFirstLoadOfFile1 = [
                        'injected' => new \stdClass(),
                        'file1' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoaderProphecy
            ->loadFile(
                'file2',
                $parametersReturnedByFirstLoadOfFile1,
                $objectsReturnedByFirstLoadOfFile1
            )
            ->willReturn(
                new ObjectSet(
                    new ParameterBag($parametersReturnedByFirstLoadOfFile2 = [
                        'foo' => 'bar',
                        'file1' => 'pass',
                        'file2' => 'pass',
                    ]),
                    new ObjectBag($objectsReturnedByFirstLoadOfFile2 = [
                        'injected' => new \stdClass(),
                        'file1' => new \stdClass(),
                        'file2' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoaderProphecy
            ->loadFile(
                'file3',
                $parametersReturnedByFirstLoadOfFile2,
                $objectsReturnedByFirstLoadOfFile2
            )
            ->willThrow(UnresolvableValueDuringGenerationException::class)
        ;
        $fileLoaderProphecy
            ->loadFile(
                'file4',
                $parametersReturnedByFirstLoadOfFile2,
                $objectsReturnedByFirstLoadOfFile2
            )
            ->willReturn(
                new ObjectSet(
                    new ParameterBag($parametersReturnedByFirstLoadOfFile4 = [
                        'foo' => 'bar',
                        'file1' => 'pass',
                        'file2' => 'pass',
                        'file4' => 'pass',
                    ]),
                    new ObjectBag($objectsReturnedByFirstLoadOfFile4 = [
                        'injected' => new \stdClass(),
                        'file1' => new \stdClass(),
                        'file2' => new \stdClass(),
                        'file4' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoaderProphecy
            ->loadFile(
                'file3',
                $parametersReturnedByFirstLoadOfFile4,
                $objectsReturnedByFirstLoadOfFile4
            )
            ->willReturn(
                new ObjectSet(
                    new ParameterBag($parametersReturnedBySecondLoadOfFile3 = [
                        'foo' => 'bar',
                        'file1' => 'pass',
                        'file2' => 'pass',
                        'file4' => 'pass',
                        'file3' => 'pass',
                    ]),
                    new ObjectBag($objectsReturnedBySecondLoadOfFile3 = [
                        'injected' => new \stdClass(),
                        'file1' => new \stdClass(),
                        'file2' => new \stdClass(),
                        'file4' => new \stdClass(),
                        'file3' => new \stdClass(),
                    ])
                )
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $expected = $objectsReturnedBySecondLoadOfFile3;

        $loader = new MultiPassLoader($fileLoader);
        $actual = $loader->load($files, $parameters, $objects);

        $this->assertEquals($expected, $actual);

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(5);
    }

    /**
     * @expectedException \Fidry\AliceDataFixtures\Alice\Exception\RootLoadingException
     */
    public function testIfDecoratedLoaderThrowsAGenericLoadingExceptionThenTheExceptionRethrown()
    {
        $files = ['foo'];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy->loadFile(Argument::cetera())->willThrow(RootLoadingException::class);
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new MultiPassLoader($fileLoader);
        $loader->load($files);
    }

    public function testIfFilesCannotBeReloadedTheLoadingStopsWhenTheLimitIsReached()
    {
        $files = ['foo'];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile(Argument::cetera())
            ->willThrow(
                new UnresolvableValueDuringGenerationException('hello')
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new MultiPassLoader($fileLoader);
        try {
            $loader->load($files);
            $this->fail('Expected exception to be thrown.');
        } catch (MaxPassReachedException $exception) {
            $this->assertContains(<<<EOF
Loading files limit of 15 reached. Could not load the following files:
foo:
 - Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException: hello in
EOF
                , $exception->getMessage()
            );
        }
    }

    public function provideMaxPassValue()
    {
        yield 'negative value' => [
            -1,
            'The maximum number of pass done to load multiple files is expected to be an integer superior or equal to 1'
            .'. Got "-1" instead.',
        ];

        yield 'zero' => [
            0,
            'The maximum number of pass done to load multiple files is expected to be an integer superior or equal to 1'
            .'. Got "0" instead.',
        ];

        yield 'minimal strictly positive value' => [
            1,
            null,
        ];
    }
}
