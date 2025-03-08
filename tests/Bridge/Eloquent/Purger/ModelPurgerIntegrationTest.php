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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Purger;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\AnotherDummy;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class ModelPurgerIntegrationTest extends TestCase
{
    private MigrationRepositoryInterface $repository;
    private Migrator $migrator;

    protected function setUp(): void
    {
        [$_manager, $repository, $migrator] = $GLOBALS['manager_repository_migrator_factory']();

        $this->repository = $repository;
        $this->migrator = $migrator;
    }

    public function testEmptyDatabase(): void
    {
        $purger = new ModelPurger($this->repository, 'migrations', $this->migrator);
        // Doing a purge here is just to make the test slightly more robust when being run multiple times
        // The real purge test is done at the next one
        $purger->purge();

        AnotherDummy::create([
            'address' => 'Wonderlands',
        ]);
        self::assertEquals(1, AnotherDummy::all()->count());

        $purger = new ModelPurger($this->repository, 'migrations', $this->migrator);
        $purger->purge();

        self::assertEquals(0, AnotherDummy::all()->count());

        // Ensures the schema has been restored
        AnotherDummy::create([
            'address' => 'Wonderlands'
        ]);
        self::assertEquals(1, AnotherDummy::all()->count());
    }
}
