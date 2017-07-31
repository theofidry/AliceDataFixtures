<?php

namespace Fidry\AliceDataFixtures\Bridge\Propel2;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Propel;

abstract class PropelTestCase extends TestCase
{
    protected function initDatabase()
    {
        $connection = Propel::getConnection('default');
        $connection->exec(file_get_contents(__DIR__ . '/generated/sql/default.sql'));
    }
}
