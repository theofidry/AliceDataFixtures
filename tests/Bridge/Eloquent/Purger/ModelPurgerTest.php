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

use Fidry\AliceDataFixtures\Bridge\Eloquent\Migration\FakeMigrationRepository;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

#[CoversClass(ModelPurger::class)]
#[BackupGlobals(false)]
class ModelPurgerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAPurger(): void
    {
        self::assertTrue(is_a(ModelPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory(): void
    {
        self::assertTrue(is_a(ModelPurger::class, PurgerFactoryInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ModelPurger::class))->isCloneable());
    }

    public function testRollbackAndRunMigrationsForPurgingTheDatabase(): void
    {
        $migrationPath = '/path/to/migrations';

        $migrationRepositoryProphecy = $this->prophesize(MigrationRepositoryInterface::class);
        $migrationRepositoryProphecy->repositoryExists()->willReturn(true);
        /** @var MigrationRepositoryInterface $migrationRepository */
        $migrationRepository = $migrationRepositoryProphecy->reveal();

        $migratorProphecy = $this->prophesize(Migrator::class);
        $migratorProphecy->reset([$migrationPath])->shouldBeCalled();
        $migratorProphecy->run([$migrationPath])->shouldBeCalled();
        /** @var Migrator $migrator */
        $migrator = $migratorProphecy->reveal();

        $purger = new ModelPurger($migrationRepository, $migrationPath, $migrator);
        $purger->purge();

        $migrationRepositoryProphecy->repositoryExists()->shouldHaveBeenCalledTimes(1);
        $migratorProphecy->reset(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $migratorProphecy->run(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCreatesTheMigrationDatabaseIfItDoesntExist(): void
    {
        $migrationPath = '/path/to/migrations';

        $migrationRepositoryProphecy = $this->prophesize(MigrationRepositoryInterface::class);
        $migrationRepositoryProphecy->repositoryExists()->willReturn(false);
        $migrationRepositoryProphecy->createRepository()->shouldBeCalled();
        /** @var MigrationRepositoryInterface $migrationRepository */
        $migrationRepository = $migrationRepositoryProphecy->reveal();

        $migratorProphecy = $this->prophesize(Migrator::class);
        $migratorProphecy->reset([$migrationPath])->shouldBeCalled();
        $migratorProphecy->run([$migrationPath])->shouldBeCalled();
        /** @var Migrator $migrator */
        $migrator = $migratorProphecy->reveal();

        $purger = new ModelPurger($migrationRepository, $migrationPath, $migrator);
        $purger->purge();

        $migrationRepositoryProphecy->repositoryExists()->shouldHaveBeenCalledTimes(1);
        $migrationRepositoryProphecy->createRepository()->shouldHaveBeenCalledTimes(1);
        $migratorProphecy->reset(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $migratorProphecy->run(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanCreateANewPurger(): void
    {
        $migratorProphecy = $this->prophesize(Migrator::class);
        $migratorProphecy->reset(Argument::cetera())->shouldNotBeCalled();
        /** @var Migrator $migrator */
        $migrator = $migratorProphecy->reveal();

        $purger = new ModelPurger(new FakeMigrationRepository(), 'foo', $migrator);
        $newPurgers = [
            $purger->create(PurgeMode::createDeleteMode()),
            $purger->create(
                PurgeMode::createDeleteMode(),
                new ModelPurger(new FakeMigrationRepository(), 'bar', $migrator)
            ),
        ];

        foreach ($newPurgers as $newPurger) {
            self::assertEquals($newPurger, $purger);
            self::assertNotSame($newPurger, $purger);
        }
    }

    public function testCannotCreateANewPurgerWithTruncateMode(): void
    {
        $expectedExceptionMessage = 'Cannot purge database in truncate mode with "Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger" (not supported).';

        $migratorProphecy = $this->prophesize(Migrator::class);
        $migratorProphecy->reset(Argument::cetera())->shouldNotBeCalled();
        /** @var Migrator $migrator */
        $migrator = $migratorProphecy->reveal();

        $purger = new ModelPurger(new FakeMigrationRepository(), 'foo', $migrator);
        try {
            $purger->create(PurgeMode::createTruncateMode());
            $this->fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            self::assertEquals($expectedExceptionMessage, $exception->getMessage());
        }

        try {
            $purger->create(
                PurgeMode::createTruncateMode(),
                new ModelPurger(new FakeMigrationRepository(), 'bar', $migrator)
            );
            $this->fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            self::assertEquals($expectedExceptionMessage, $exception->getMessage());
        }
    }
}
