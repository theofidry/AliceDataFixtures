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
 * @covers \Fidry\AliceDataFixtures\Loader\FileTracker
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FileTrackerTest extends TestCase
{
    public function testReturnsAllUnloadedFiles()
    {
        $tracker = new FileTracker('foo');

        $this->assertSame(['foo'], $tracker->getUnloadedFiles());
    }

    public function testCanMarkFilesAsLoaded()
    {
        $tracker = new FileTracker('foo');
        $tracker->markAsLoaded('foo');

        $this->assertSame([], $tracker->getUnloadedFiles());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The file "foo" is not being tracked. As such, it cannot be marked as "loaded".
     */
    public function testCannotMarkUntrackedFileAsLoaded()
    {
        $tracker = new FileTracker('');
        $tracker->markAsLoaded('foo');
    }

    public function testCanTellWhenAllFilesHaveBeenLoaded()
    {
        $tracker = new FileTracker('foo', 'bar');
        $this->assertFalse($tracker->allFilesHaveBeenLoaded());

        $tracker->markAsLoaded('foo');
        $this->assertFalse($tracker->allFilesHaveBeenLoaded());

        $tracker->markAsLoaded('bar');
        $this->assertTrue($tracker->allFilesHaveBeenLoaded());
    }

    public function testIsDeepClonable()
    {
        $tracker = new FileTracker('foo');
        $clone = clone $tracker;

        $clone->markAsLoaded('foo');

        $this->assertFalse($tracker->allFilesHaveBeenLoaded());
        $this->assertTrue($clone->allFilesHaveBeenLoaded());
    }
}
