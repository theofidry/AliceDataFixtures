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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class PurgeModeTest extends TestCase
{
    public function testThePurgerModesValueIsSynchronizedWithDoctrinePurgerMode()
    {
        $mode = PurgeMode::createDeleteMode();
        $this->assertEquals(ORMPurger::PURGE_MODE_DELETE, $mode->getValue());

        $mode = PurgeMode::createTruncateMode();
        $this->assertEquals(ORMPurger::PURGE_MODE_TRUNCATE, $mode->getValue());
    }
}
