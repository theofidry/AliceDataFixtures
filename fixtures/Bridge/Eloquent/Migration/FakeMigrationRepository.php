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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Migration;

use Fidry\AliceDataFixtures\NotCallableTrait;
use function func_get_args;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class FakeMigrationRepository implements MigrationRepositoryInterface
{
    use NotCallableTrait;

    public function getRan(): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getMigrations($steps): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getMigrationBatches(): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getLast(): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function log($file, $batch): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function delete($migration): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getNextBatchNumber(): int
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createRepository(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function repositoryExists(): bool
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function setSource($name): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function deleteRepository(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
