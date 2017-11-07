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

namespace Fidry\AliceDataFixtures\Persistence;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Persistence\PurgeMode
 */
class PurgeModeTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown purge mode "3".
     */
    public function testThrowsAnExceptionIfUnknownPurgeModeIsGiven()
    {
        new PurgeMode(3);
    }

    public function testCanCreateDeleteMode()
    {
        $mode = PurgeMode::createDeleteMode();
        $this->assertEquals(1, $mode->getValue());

        $mode = new PurgeMode(1);
        $this->assertEquals(1, $mode->getValue());
    }

    public function testCanCreateTruncateMode()
    {
        $mode = PurgeMode::createTruncateMode();
        $this->assertEquals(2, $mode->getValue());

        $mode = new PurgeMode(2);
        $this->assertEquals(2, $mode->getValue());
    }
}
