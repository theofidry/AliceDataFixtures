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

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PurgeMode::class)]
class PurgeModeTest extends TestCase
{
    public function testThrowsAnExceptionIfUnknownPurgeModeIsGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown purge mode "3".');

        new PurgeMode(3);
    }

    public function testCanCreateDeleteMode(): void
    {
        $mode = PurgeMode::createDeleteMode();
        self::assertEquals(1, $mode->getValue());

        $mode = new PurgeMode(1);
        self::assertEquals(1, $mode->getValue());
    }

    public function testCanCreateTruncateMode(): void
    {
        $mode = PurgeMode::createTruncateMode();
        self::assertEquals(2, $mode->getValue());

        $mode = new PurgeMode(2);
        self::assertEquals(2, $mode->getValue());
    }
}
