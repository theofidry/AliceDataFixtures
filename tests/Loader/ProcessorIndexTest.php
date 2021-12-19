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

use Fidry\AliceDataFixtures\Persistence\Persister\NullPersister;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Nelmio\Alice\Loader\NativeLoader;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ProcessorIndexTest extends TestCase
{
    public function testIndexesArePreserved(): void
    {
        $processor = new DummyProcessor();
        $loader = new PersisterLoader(
            new SimpleLoader(
                new NativeLoader()
            ),
            new NullPersister(),
            null,
            [$processor]
        );

        $loader->load([__DIR__.'/../../fixtures/fixture_files/dummy.yml']);

        self::assertSame(['dummy0'], $processor->preIds);
        self::assertSame(['dummy0'], $processor->postIds);
    }
}

class DummyProcessor implements ProcessorInterface
{
    public array $preIds = [];
    public array $postIds = [];

    public function preProcess(string $id, object $object): void
    {
        $this->preIds[] = $id;
    }

    public function postProcess(string $id, object $object): void
    {
        $this->postIds[] = $id;
    }
}
