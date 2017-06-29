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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\ErrorTracker
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ErrorTrackerTest extends TestCase
{
    public function testCanGetStackOfATrackerEvenIfNoErrorHasBeenRegistered()
    {
        $tracker = new ErrorTracker();

        $this->assertSame([], $tracker->getStack());
    }

    public function testKeepsTrackOfErrors()
    {
        $tracker = new ErrorTracker();
        $tracker->register('foo', $exception0 = new \Exception('foo exception'));
        $tracker->register('bar', $exception1 = new \Exception('bar exception 0'));
        $tracker->register('bar', $exception2 = new \Exception('bar exception 1'));

        $this->assertEquals(
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

    public function testIsDeepClonable()
    {
        $tracker = new ErrorTracker();
        $tracker->register('foo', new \Exception($message0 = 'foo exception'));

        $originalTracker = new ErrorTracker();
        $originalTracker->register('foo', new \Exception($message0 = 'foo exception'));

        $newTracker = clone $tracker;
        $newTracker->register('bar', new \Exception($message1 = 'bar exception'));

        $this->assertEquals($originalTracker->getStack(), $tracker->getStack());
        $this->assertNotEquals($tracker->getStack(), $newTracker->getStack());
    }
}
