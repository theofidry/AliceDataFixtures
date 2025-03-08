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

namespace Fidry\AliceDataFixtures\Exception;

use Error;
use Exception;
use Fidry\AliceDataFixtures\Loader\ErrorTracker;
use Nelmio\Alice\Throwable\LoadingThrowable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(MaxPassReachedException::class)]
class MaxPassReachedExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(MaxPassReachedException::class, RuntimeException::class, true));
    }

    public function testIsALoadingException(): void
    {
        self::assertTrue(is_a(MaxPassReachedException::class, LoadingThrowable::class, true));
    }

    public function testInstantiation(): void
    {
        $exception = new MaxPassReachedException('foo');
        self::assertEquals('foo', $exception->getMessage());
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
        self::assertCount(0, $exception->getStack());


        $code = 100;
        $previous = new Error();
        $tracker = new ErrorTracker();
        $tracker->register('/foo.php', new Exception('bar'));

        $exception = new MaxPassReachedException('foo', $code, $previous, $tracker);
        self::assertEquals('foo', $exception->getMessage());
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
        self::assertEquals($tracker->getStack(), $exception->getStack());
    }
}
