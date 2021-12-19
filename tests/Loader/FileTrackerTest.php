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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Loader\FileTracker
 */
class FileTrackerTest extends TestCase
{
    public function testReturnsAllUnloadedFiles(): void
    {
        $tracker = new FileTracker('foo');

        $this->assertSame(['foo'], $tracker->getUnloadedFiles());
    }

    public function testCanMarkFilesAsLoaded(): void
    {
        $tracker = new FileTracker('foo');
        $tracker->markAsLoaded('foo');

        $this->assertSame([], $tracker->getUnloadedFiles());
    }

    public function testCannotMarkUntrackedFileAsLoaded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "foo" is not being tracked. As such, it cannot be marked as "loaded".');

        $tracker = new FileTracker('');
        $tracker->markAsLoaded('foo');
    }

    public function testCanTellWhenAllFilesHaveBeenLoaded(): void
    {
        $tracker = new FileTracker('foo', 'bar');
        $this->assertFalse($tracker->allFilesHaveBeenLoaded());

        $tracker->markAsLoaded('foo');
        $this->assertFalse($tracker->allFilesHaveBeenLoaded());

        $tracker->markAsLoaded('bar');
        $this->assertTrue($tracker->allFilesHaveBeenLoaded());
    }

    public function testIsDeepClonable(): void
    {
        $tracker = new FileTracker('foo');
        $clone = clone $tracker;

        $clone->markAsLoaded('foo');

        $this->assertFalse($tracker->allFilesHaveBeenLoaded());
        $this->assertTrue($clone->allFilesHaveBeenLoaded());
    }
}
