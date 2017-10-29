<?php

declare(strict_types=1);

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Propel2;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Propel;

abstract class PropelTestCase extends TestCase
{
    protected function initDatabase()
    {
        $connection = Propel::getConnection('default');
        $connection->exec(file_get_contents(__DIR__ . '/../../../fixtures/Bridge/Propel2/generated/sql/default.sql'));
    }
}
