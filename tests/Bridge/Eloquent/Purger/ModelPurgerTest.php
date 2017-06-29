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
use Fidry\AliceDataFixtures\Bridge\Eloquent\MigratorFactory;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\AnotherDummy;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger
 *
 * @backupGlobals disabled
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ModelPurgerTest extends TestCase
{
    public function testIsAPurger()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new ModelPurger(new FakeMigrationRepository(), '', MigratorFactory::create());
    }

    public function testRollbackAndRunMigrationsForPurgingTheDatabase()
    {
        $migrationPath = '/path/to/migrations';

        /** @var MigrationRepositoryInterface|ObjectProphecy $migrationRepositoryProphecy */
        $migrationRepositoryProphecy = $this->prophesize(MigrationRepositoryInterface::class);
        $migrationRepositoryProphecy->repositoryExists()->willReturn(true);
        /** @var MigrationRepositoryInterface $migrationRepository */
        $migrationRepository = $migrationRepositoryProphecy->reveal();

        /** @var Migrator|ObjectProphecy $migratorProphecy */
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

    public function testCreatesTheMigrationDatabaseIfItDoesntExist()
    {
        $migrationPath = '/path/to/migrations';

        /** @var MigrationRepositoryInterface|ObjectProphecy $migrationRepositoryProphecy */
        $migrationRepositoryProphecy = $this->prophesize(MigrationRepositoryInterface::class);
        $migrationRepositoryProphecy->repositoryExists()->willReturn(false);
        $migrationRepositoryProphecy->createRepository()->shouldBeCalled();
        /** @var MigrationRepositoryInterface $migrationRepository */
        $migrationRepository = $migrationRepositoryProphecy->reveal();

        /** @var Migrator|ObjectProphecy $migratorProphecy */
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

    public function testCanCreateANewPurger()
    {
        /** @var Migrator|ObjectProphecy $migratorProphecy */
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
            $this->assertEquals($newPurger, $purger);
            $this->assertNotSame($newPurger, $purger);
        }
    }

    public function testCannotCreateANewPurgerWithTruncateMode()
    {
        $expectedExceptionMessage = 'Cannot purge database in truncate mode with '
            .'"Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger" (not supported).'
        ;

        /** @var Migrator|ObjectProphecy $migratorProphecy */
        $migratorProphecy = $this->prophesize(Migrator::class);
        $migratorProphecy->reset(Argument::cetera())->shouldNotBeCalled();
        /** @var Migrator $migrator */
        $migrator = $migratorProphecy->reveal();

        $purger = new ModelPurger(new FakeMigrationRepository(), 'foo', $migrator);
        try {
            $purger->create(PurgeMode::createTruncateMode());
            $this->fail('Expected exception to be thrown.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals($expectedExceptionMessage, $exception->getMessage());
        }

        try {
            $purger->create(
                PurgeMode::createTruncateMode(),
                new ModelPurger(new FakeMigrationRepository(), 'bar', $migrator)
            );
            $this->fail('Expected exception to be thrown.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals($expectedExceptionMessage, $exception->getMessage());
        }
    }

    /**
     * @coversNothing
     */
    public function testEmptyDatabase()
    {
        $purger = new ModelPurger($GLOBALS['repository'], 'migrations', $GLOBALS['migrator']);
        // Doing a purge here is just to make the test slightly more robust when being run multiple times
        // The real purge test is done at the next one
        $purger->purge();

        AnotherDummy::create([
            'address' => 'Wonderlands',
        ]);
        $this->assertEquals(1, AnotherDummy::all()->count());

        $purger = new ModelPurger($GLOBALS['repository'], 'migrations', $GLOBALS['migrator']);
        $purger->purge();

        $this->assertEquals(0, AnotherDummy::all()->count());

        // Ensures the schema has been restored
        AnotherDummy::create([
            'address' => 'Wonderlands'
        ]);
        $this->assertEquals(1, AnotherDummy::all()->count());
    }
}
