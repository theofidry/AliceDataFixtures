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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

#[CoversClass(MultiPassLoader::class)]
#[UsesClass(ErrorTracker::class)]
#[UsesClass(FileTracker::class)]
#[UsesClass(MaxPassReachedException::class)]
class MultiPassFileLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALoader(): void
    {
        self::assertTrue(is_a(MultiPassLoader::class, LoaderInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(MultiPassLoader::class))->isCloneable());
    }

    #[DataProvider('provideMaxPassValue')]
    public function testMaxPassGivenMustBeAStrictlyPositiveInteger(int $maxPass, ?string $expectedExceptionMessage): void
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

            self::assertEquals($expectedExceptionMessage, $exception->getMessage());
        }

        self::assertTrue(true, 'Everything is fine.');
    }

    public function testReturnsAnEmptySetIfNoFileGiven(): void
    {
        $expected = [];

        $loader = new MultiPassLoader(new FakeFileLoader());
        $actual = $loader->load([]);

        self::assertEquals($expected, $actual);
    }

    public function testReturnsInjectedObjectsAndObjectsIfNoFileGiven(): void
    {
        $parameters = [];
        $expected = $objects = ['dummy' => new stdClass()];

        $loader = new MultiPassLoader(new FakeFileLoader());
        $actual = $loader->load([], $parameters, $objects);

        self::assertEquals($expected, $actual);
    }

    public function testDecoratesTheFileLoaderToReturnTheObjectsLoaded(): void
    {
        $files = ['foo'];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile('foo', [], [])
            ->willReturn(
                new ObjectSet(new ParameterBag(), new ObjectBag(['dummy' => new stdClass()]))
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $expected = ['dummy' => new stdClass()];

        $loader = new MultiPassLoader($fileLoader);
        $actual = $loader->load($files);

        self::assertEquals($expected, $actual);

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testLoadedFilesInSeveralPassesUntilAllFilesAreLoaded(): void
    {
        $files = ['file1', 'file2', 'file3', 'file4'];
        $parameters = ['foo' => 'bar'];
        $objects = ['injected' => new stdClass()];

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
                        'injected' => new stdClass(),
                        'file1' => new stdClass(),
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
                        'injected' => new stdClass(),
                        'file1' => new stdClass(),
                        'file2' => new stdClass(),
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
                        'injected' => new stdClass(),
                        'file1' => new stdClass(),
                        'file2' => new stdClass(),
                        'file4' => new stdClass(),
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
                        'injected' => new stdClass(),
                        'file1' => new stdClass(),
                        'file2' => new stdClass(),
                        'file4' => new stdClass(),
                        'file3' => new stdClass(),
                    ])
                )
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $expected = $objectsReturnedBySecondLoadOfFile3;

        $loader = new MultiPassLoader($fileLoader);
        $actual = $loader->load($files, $parameters, $objects);

        self::assertEquals($expected, $actual);

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(5);
    }

    public function testIfDecoratedLoaderThrowsAGenericLoadingExceptionThenTheExceptionRethrown(): void
    {
        $this->expectException(RootLoadingException::class);

        $files = ['foo'];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy->loadFile(Argument::cetera())->willThrow(RootLoadingException::class);
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new MultiPassLoader($fileLoader);
        $loader->load($files);
    }

    public function testIfFilesCannotBeReloadedTheLoadingStopsWhenTheLimitIsReached(): void
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
            self::assertStringContainsString(
                <<<EOF
Loading files limit of 15 reached. Could not load the following files:
foo:
 - Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException: hello in
EOF
                ,
                $exception->getMessage()
            );
        }
    }

    public static function provideMaxPassValue(): iterable
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
