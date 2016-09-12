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
use Fidry\AliceDataFixtures\File\Resolver\DummyResolver;
use Fidry\AliceDataFixtures\FileResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

/**
 * @covers Fidry\AliceDataFixtures\Loader\FileResolverLoader
 */
class FileResolverLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        Assert::implementsInterface(FileResolverLoader::class, LoaderInterface::class);
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
            ->load($resolvedFiles, [], [])
            ->willReturn(
                [
                    'dummy' => $dummy = new \stdClass(),
                ]
            )
        ;
        /** @var LoaderInterface $loader */
        $loader = $loaderProphecy->reveal();

        $loader = new FileResolverLoader($loader, $fileResolver);
        $result = $loader->load($files);

        Assert::eq(
            [
                'dummy' => new \stdClass(),
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

        $loader = new FileResolverLoader($loader, new DummyResolver());
        $result = $loader->load($files, $parameters, $objects);

        Assert::eq(
            [
                'dummy' => new \stdClass(),
            ],
            $result
        );

        $loaderProphecy->load(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
