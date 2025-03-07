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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

#[CoversClass(SimpleLoader::class)]
class SimpleLoaderTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALoader(): void
    {
        self::assertTrue(is_a(SimpleLoader::class, LoaderInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleLoader::class))->isCloneable());
    }

    public function testDecoratesAliceLoaderToLoadEachFileGivenAndReturnsTheObjectsLoaded(): void
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

        self::assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $result
        );

        $filesLoaderProphecy->loadFiles(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
