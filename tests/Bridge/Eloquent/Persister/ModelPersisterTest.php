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
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister
 *
 * @backupGlobals disabled
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ModelPersisterTest extends TestCase
{
    /**
     * @var ModelPersister
     */
    private $persister;

    /**
     * @var Migrator
     */
    private $migrator;

    public function setUp()
    {
        $this->migrator = $GLOBALS['migrator'];
        $this->persister = new ModelPersister($GLOBALS['manager']->getDatabaseManager());
    }

    public function tearDown()
    {
        $this->migrator->reset(['migrations']);
        $this->migrator->run(['migrations']);
    }

    public function testIsAPersister()
    {
        $this->assertTrue(is_a(ModelPersister::class, PersisterInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsClonable()
    {
        clone $this->persister;
    }

    public function testCanPersistAModel()
    {
        $model = new AnotherDummy([
            'address' => 'Wonderlands',
        ]);
        $this->assertNull($model->id);

        $this->persister->persist($model);
        $this->assertNull($model->id);

        $this->persister->flush();
        $this->assertNotNull($model->id);

        $this->assertEquals(1, AnotherDummy::all()->count());
    }

    public function testCanPersistAModelWithARelationship()
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
        $this->assertNull($dummy->id);

        $this->persister->flush();
        $this->assertNotNull($dummy->id);

        $this->assertEquals(1, Dummy::all()->count());
        $this->assertEquals(1, AnotherDummy::all()->count());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected object to be an instance of "Illuminate\Database\Eloquent\Model", got "stdClass" instead.
     */
    public function testCannotPersistANonModelObject()
    {
        $object = new \stdClass();
        $this->persister->persist($object);
    }
}
