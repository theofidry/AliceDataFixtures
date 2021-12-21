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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Persister;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\AnotherDummy;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\Dummy;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister
 *
 * @backupGlobals disabled
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ModelPersisterTest extends TestCase
{
    private PersisterInterface $persister;

    private Migrator $migrator;

    public function setUp(): void
    {
        /**
         * @var Manager $manager
         * @var MigrationRepositoryInterface $repository
         * @var Migrator $migrator
         */
        [$manager, $repository, $migrator] = $GLOBALS['manager_repository_migrator_factory']();

        $this->migrator = $migrator;
        $this->persister = new ModelPersister($manager->getDatabaseManager());
    }

    public function tearDown(): void
    {
        $this->migrator->reset(['migrations']);
        $this->migrator->run(['migrations']);
    }

    public function testIsAPersister(): void
    {
        self::assertTrue(is_a(ModelPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ModelPersister::class))->isCloneable());
    }

    public function testCanPersistAModel(): void
    {
        $model = new AnotherDummy([
            'address' => 'Wonderlands',
        ]);
        self::assertNull($model->id);

        $this->persister->persist($model);
        self::assertNull($model->id);

        $this->persister->flush();
        self::assertNotNull($model->id);

        self::assertEquals(1, AnotherDummy::all()->count());
    }

    public function testCanPersistAModelWithARelationship(): void
    {
        $anotherDummy = new AnotherDummy([
            'address' => 'Heaven',
        ]);
        $anotherDummy->save();

        $dummy = new Dummy([
            'name' => 'bob',
        ]);
        $dummy->anotherDummy()->associate($anotherDummy);

        $this->persister->persist($dummy);
        self::assertNull($dummy->id);

        $this->persister->flush();
        self::assertNotNull($dummy->id);

        self::assertEquals(1, Dummy::all()->count());
        self::assertEquals(1, AnotherDummy::all()->count());
    }

    public function testCannotPersistANonModelObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected object to be an instance of "Illuminate\Database\Eloquent\Model", got "stdClass" instead.');

        $object = new stdClass();
        $this->persister->persist($object);
    }
}
