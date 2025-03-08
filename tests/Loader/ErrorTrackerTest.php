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

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorTracker::class)]
class ErrorTrackerTest extends TestCase
{
    public function testCanGetStackOfATrackerEvenIfNoErrorHasBeenRegistered(): void
    {
        $tracker = new ErrorTracker();

        self::assertSame([], $tracker->getStack());
    }

    public function testKeepsTrackOfErrors(): void
    {
        $tracker = new ErrorTracker();
        $tracker->register('foo', $exception0 = new Exception('foo exception'));
        $tracker->register('bar', $exception1 = new Exception('bar exception 0'));
        $tracker->register('bar', $exception2 = new Exception('bar exception 1'));

        self::assertEquals(
            [
                'foo' => [
                    $exception0,
                ],
                'bar' => [
                    $exception1,
                    $exception2,
                ]
            ],
            $tracker->getStack()
        );
    }

    public function testIsDeepClonable(): void
    {
        $tracker = new ErrorTracker();
        $tracker->register('foo', new Exception('foo exception'));

        $originalTracker = new ErrorTracker();
        $originalTracker->register('foo', new Exception('foo exception'));

        $newTracker = clone $tracker;
        $newTracker->register('bar', new Exception('bar exception'));

        self::assertEquals($originalTracker->getStack(), $tracker->getStack());
        self::assertNotEquals($tracker->getStack(), $newTracker->getStack());
    }
}
