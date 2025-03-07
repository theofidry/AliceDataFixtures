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

namespace Fidry\AliceDataFixtures\Persistence\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(NullPersister::class)]
class NullPersisterTest extends TestCase
{
    public function testIsAPersister(): void
    {
        self::assertTrue(is_a(NullPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(NullPersister::class))->isCloneable());
    }
}
