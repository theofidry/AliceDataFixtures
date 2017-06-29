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

use Fidry\AliceDataFixtures\Alice\Loader\FakeFileLoader;
use Fidry\AliceDataFixtures\LoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\SimpleLoader
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class SimpleLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(SimpleLoader::class, LoaderInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new SimpleLoader(new FakeFileLoader());
    }

    public function testDecoratesAliceLoaderToLoadEachFileGivenAndReturnsTheObjectsLoaded()
    {
        $files = [
            'fixtures1.yml',
        ];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile($files[0], [], [])
            ->willReturn(
                new ObjectSet(
                    new ParameterBag(),
                    new ObjectBag([
                        'dummy' => new \stdClass(),
                    ])
                )
            )
        ;
        /** @var FileLoaderInterface $fileLoader */
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new SimpleLoader($fileLoader);
        $result = $loader->load($files);

        $this->assertEquals(
            [
                'dummy' => new \stdClass(),
            ],
            $result
        );

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testParametersAndObjectsLoadedAreReinjectedBetweenEachLoader()
    {
        $files = [
            'fixtures1.yml',
            'fixtures2.yml',
        ];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile($files[0], [], [])
            ->willReturn(
                new ObjectSet(
                    new ParameterBag([
                        'first' => true,
                    ]),
                    new ObjectBag([
                        'first' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoaderProphecy
            ->loadFile(
                $files[1],
                [
                    'first' => true,
                ],
                [
                    'first' => new \stdClass(),
                ]
            )
            ->willReturn(
                new ObjectSet(
                    new ParameterBag([
                        'first' => true,
                        'second' => true,
                    ]),
                    new ObjectBag([
                        'first' => new \stdClass(),
                        'second' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new SimpleLoader($fileLoader);
        $result = $loader->load($files);

        $this->assertEquals(
            [
                'first' => new \stdClass(),
                'second' => new \stdClass(),
            ],
            $result
        );

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testParametersAndObjectsPassedAndLoadedAreReinjectedBetweenEachLoader()
    {
        $files = [
            'fixtures1.yml',
            'fixtures2.yml',
        ];
        $parameters = [
            'injected' => true,
        ];
        $objects = [
            'injected' => new \stdClass(),
        ];

        $fileLoaderProphecy = $this->prophesize(FileLoaderInterface::class);
        $fileLoaderProphecy
            ->loadFile($files[0], $parameters, $objects)
            ->willReturn(
                new ObjectSet(
                    new ParameterBag([
                        'injected' => true,
                        'first' => true,
                    ]),
                    new ObjectBag([
                        'injected' => new \stdClass(),
                        'first' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoaderProphecy
            ->loadFile(
                $files[1],
                [
                    'injected' => true,
                    'first' => true,
                ],
                [
                    'injected' => new \stdClass(),
                    'first' => new \stdClass(),
                ]
            )
            ->willReturn(
                new ObjectSet(
                    new ParameterBag([
                        'injected' => true,
                        'first' => true,
                        'second' => true,
                    ]),
                    new ObjectBag([
                        'injected' => new \stdClass(),
                        'first' => new \stdClass(),
                        'second' => new \stdClass(),
                    ])
                )
            )
        ;
        $fileLoader = $fileLoaderProphecy->reveal();

        $loader = new SimpleLoader($fileLoader);
        $result = $loader->load($files, $parameters, $objects);

        $this->assertEquals(
            [
                'injected' => new \stdClass(),
                'first' => new \stdClass(),
                'second' => new \stdClass(),
            ],
            $result
        );

        $fileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
