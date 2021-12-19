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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Persister;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithIdentifier;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithRelation;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\IdGenerator;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 */
class ObjectManagerPersisterTest extends TestCase
{
    private ObjectManagerPersister $persister;

    private EntityManagerInterface $entityManager;

    private ORMPurger $purger;

    public function setUp(): void
    {
        $this->entityManager = $GLOBALS['entity_manager'];
        $this->persister = new ObjectManagerPersister($this->entityManager);
        $this->purger = new ORMPurger($this->entityManager);
    }

    public function tearDown(): void
    {
        $this->purger->purge();
    }

    public function testIsAPersister(): void
    {
        $this->assertTrue(is_a(ObjectManagerPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        $this->assertFalse((new ReflectionClass(ObjectManagerPersister::class))->isCloneable());
    }

    /**
     * @dataProvider provideEntities
     */
    public function testCanPersistAnEntity($entity, bool $exact = false): void
    {
        $originalEntity = clone $entity;

        $this->persister->persist($entity);
        $this->persister->flush();

        $this->entityManager->clear();

        $result = $this->entityManager->getRepository(get_class($entity))->findAll();

        $this->assertCount(1, $result);

        if ($exact) {
            $this->assertEquals($originalEntity, $result[0]);
        }
    }

    public function testCanPersistAnEntityWithRelationsAndExplicitIds(): void
    {
        $dummy = new DummyWithIdentifier();
        $dummy->id = 100;

        $dummyWithRelation = new DummyWithRelation();
        $dummyWithRelation->id = 200;
        $dummyWithRelation->dummy = $dummy;

        $this->persister->persist($dummyWithRelation);
        $this->persister->persist($dummy);
        $this->persister->flush();

        $this->entityManager->clear();

        $result = $this->entityManager->getRepository(DummyWithIdentifier::class)->findOneBy(['id' => 100]);
        $this->assertInstanceOf(DummyWithIdentifier::class, $result);
        $this->assertEquals($result->id, $dummy->id);

        $result = $this->entityManager->getRepository(DummyWithRelation::class)->findOneBy(['id' => 200]);
        $this->assertInstanceOf(DummyWithRelation::class, $result);
        $this->assertEquals($result->id, $dummyWithRelation->id);
    }

    public function testCanPersistMultipleEntitiesWithExplicitIdentifierSet(): void
    {
        $dummy = new DummyWithIdentifier();
        $dummy->id = 100;
        $this->persister->persist($dummy);

        $dummy = new DummyWithIdentifier();
        $dummy->id = 200;
        $this->persister->persist($dummy);

        $classMetadata = $this->entityManager->getClassMetadata(DummyWithIdentifier::class);

        $this->assertEquals(
            IdGenerator::class,
            get_class($classMetadata->idGenerator),
            'ID generator should be changed.'
        );

        $this->persister->flush();

        $classMetadata = $this->entityManager->getClassMetadata(DummyWithIdentifier::class);

        $this->assertNotEquals(
            IdGenerator::class,
            get_class($classMetadata->idGenerator),
            'ID generator should be restored after flush.'
        );

        $entity = $this->entityManager->getRepository(DummyWithIdentifier::class)->find(200);
        $this->assertInstanceOf(DummyWithIdentifier::class, $entity);
    }

    public function testCanPersistEntitiesWithoutExplicitIdentifierSetEvenWhenExistingEntitiesHaveOne(): void
    {
        $this->markTestSkipped(
            <<<'EOF'
            This seems to no longer be working. From the look of it, without any
            clear happening the UoW may already be initialized with a (Doctrine)
            persister for the given aggregate (here Dummy). As a result when
            persisting dummy2, the persister is already instantiated with
            and outdated class-metadata, i.e. the one Alice registered is not
            considered.
            The only way ot make it work appears to be to do a clear before-hand
            which causes the issue of detaching all the fixtures we may want
            to pass to it.
            
            Overall this seems to be a clear indication that mixing the ID
            generation is not only a bad idea but no longer viable. 
            EOF
        );

        $dummy1 = new Dummy();
        $this->entityManager->persist($dummy1);
        $this->entityManager->flush();

        // When loading fixtures in real world an existing entity can be persisted again by the persister.
        // e.g. when this entity has been persisted by a relation with the cascade persist option.
        $this->persister->persist($dummy1);

        $dummy2 = new Dummy();
        $this->persister->persist($dummy2);

        $this->persister->flush();

        $entity = $this->entityManager->getRepository(Dummy::class)->find($dummy1->id);
        $this->assertInstanceOf(Dummy::class, $entity);

        $entity = $this->entityManager->getRepository(Dummy::class)->find($dummy2->id);
        $this->assertInstanceOf(Dummy::class, $entity);
    }

    public function testPersistingMultipleEntitiesWithAndWithoutExplicitIdentifierSetWillNotThrowORMException(): void
    {
        $dummy = new DummyWithIdentifier();
        $this->persister->persist($dummy);

        $dummy = new DummyWithIdentifier();
        $dummy->id = 100;
        $this->persister->persist($dummy);

        $dummy = new DummyWithIdentifier();
        $this->persister->persist($dummy);

        $this->persister->flush();
    }

    /**
     * @dataProvider provideNonPersistableEntities
     */
    public function testDoesNotPersistEmbeddables($dummy): void
    {
        try {
            $this->entityManager->persist($dummy);
            $this->entityManager->flush();

            $this->fail('Expected exception to be thrown.');
        } catch (ORMInvalidArgumentException $exception) {
            // Expected result
            $this->entityManager->clear();
        }

        $this->persister->persist($dummy);
        $this->persister->flush();

        $this->assertTrue(true, 'Everything is fine.');
    }

    public static function provideEntities(): iterable
    {
        yield 'simple entity' => [new Dummy()];

        yield 'entity with embeddable' => [
            (static function () {
                $embeddable = new DummyEmbeddable();
                $dummy = new DummyWithEmbeddable();

                $dummy->embeddable = $embeddable;

                return $dummy;
            })()
        ];

        yield 'sub class entity' => [
            (static function () {
                $dummy = new DummySubClass();
                $dummy->status = '200';

                return $dummy;
            })()
        ];

        yield 'entity with explicit ID' => [
            (function () {
                $dummy = new DummyWithIdentifier();
                $dummy->id = 300;

                return $dummy;
            })()
        ];
    }

    public static function provideNonPersistableEntities():iterable
    {
        yield 'embeddable' => [new DummyEmbeddable()];

        yield 'mapped super class' => [new MappedSuperclassDummy()];
    }
}
