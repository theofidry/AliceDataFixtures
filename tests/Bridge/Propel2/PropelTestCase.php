<?php

namespace Fidry\AliceDataFixtures\Bridge\Propel2;

use PHPUnit\Framework\TestCase;

abstract class PropelTestCase extends TestCase
{
    private function dbPath()
    {
        return __DIR__ . '/generated/propel.sq3';
    }

    private function backupDbPath()
    {
        return $this->dbPath() . '.back';
    }

    protected function backupDatabase()
    {

        if (file_exists($this->dbPath())) {
            copy($this->dbPath(), $this->backupDbPath());
        }
    }
    protected function restoreDatabase()
    {
        copy($this->backupDbPath(), $this->dbPath());
    }
}
