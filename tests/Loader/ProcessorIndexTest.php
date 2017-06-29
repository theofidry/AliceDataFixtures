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
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ProcessorIndexTest extends TestCase
{
    public function testIndexesArePreserved()
    {
        $processor = new DummyProcessor();
        $loader = new PersisterLoader(
            new SimpleLoader(
                new NativeLoader()
            ),
            new NullPersister(),
            [$processor]
        );

        $loader->load([__DIR__.'/../../fixtures/fixture_files/dummy.yml']);

        $this->assertSame(['dummy0'], $processor->preIds);
        $this->assertSame(['dummy0'], $processor->postIds);
    }
}

class DummyProcessor implements ProcessorInterface
{
    public $preIds = [];
    public $postIds = [];

    /**
     * @inheritdoc
     */
    public function preProcess(string $id, $object)
    {
        $this->preIds[] = $id;
    }

    /**
     * @inheritdoc
     */
    public function postProcess(string $id, $object)
    {
        $this->postIds[] = $id;
    }
}
