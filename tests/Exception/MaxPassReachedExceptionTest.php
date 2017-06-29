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

use Fidry\AliceDataFixtures\Loader\ErrorTracker;
use Nelmio\Alice\Throwable\LoadingThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Exception\MaxPassReachedException
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MaxPassReachedExceptionTest extends TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(MaxPassReachedException::class, \RuntimeException::class, true));
    }

    public function testIsALoadingException()
    {
        $this->assertTrue(is_a(MaxPassReachedException::class, LoadingThrowable::class, true));
    }

    public function testInstantiation()
    {
        $exception = new MaxPassReachedException('foo');
        $this->assertEquals('foo', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertCount(0, $exception->getStack());


        $code = 100;
        $previous = new \Error();
        $tracker = new ErrorTracker();
        $tracker->register('/foo.php', new \Exception('bar'));

        $exception = new MaxPassReachedException('foo', $code, $previous, $tracker);
        $this->assertEquals('foo', $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($tracker->getStack(), $exception->getStack());
    }
}
