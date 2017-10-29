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
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\SimpleLoader
 */
class SimpleLoaderTest extends TestCase
{
    public function testIsALoader()
    {
        $this->assertTrue(is_a(SimpleLoader::class, LoaderInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleLoader::class))->isCloneable());
    }

    public function testDecoratesAliceLoaderToLoadEachFileGivenAndReturnsTheObjectsLoaded()
    {
        $files = [
            'fixtures1.yml',
        ];

        $filesLoaderProphecy = $this->prophesize(FilesLoaderInterface::class);
        $filesLoaderProphecy
            ->loadFiles($files, [], [])
            ->willReturn(
                new ObjectSet(
                    new ParameterBag(),
                    new ObjectBag([
                        'dummy' => new stdClass(),
                    ])
                )
            )
        ;
        /** @var FilesLoaderInterface $filesLoader */
        $filesLoader = $filesLoaderProphecy->reveal();

        $loader = new SimpleLoader($filesLoader);
        $result = $loader->load($files);

        $this->assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $result
        );

        $filesLoaderProphecy->loadFiles(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
