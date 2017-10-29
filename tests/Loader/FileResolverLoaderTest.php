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

use Fidry\AliceDataFixtures\File\Resolver\DummyResolver;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\FileResolverLoader
 */
class FileResolverLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(FileResolverLoader::class, LoaderInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FileResolverLoader::class))->isCloneable());
    }

    public function testResolvesTheFilesBeforePassingThemToTheDecoratedLoader()
    {
        $files = [
            'fixtures1.yml',
        ];

        $fileResolverProphecy = $this->prophesize(FileResolverInterface::class);
        $fileResolverProphecy
            ->resolve($files)
            ->willReturn($resolvedFiles = [
                'resolved_fixtures1.yml',
            ])
        ;
        /** @var FileResolverInterface $fileResolver */
        $fileResolver = $fileResolverProphecy->reveal();

        $loaderProphecy = $this->prophesize(LoaderInterface::class);
        $loaderProphecy
            ->load($resolvedFiles, [], [], null)
            ->willReturn(
                [
                    'dummy' => $dummy = new stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $loader = new FileResolverLoader($loader, $fileResolver);
        $result = $loader->load($files);

        $this->assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $result
        );

        $fileResolverProphecy->resolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
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

        $loader = new FileResolverLoader($loader, new DummyResolver());
        $result = $loader->load($files, $parameters, $objects, null);

        $this->assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $result
        );

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
