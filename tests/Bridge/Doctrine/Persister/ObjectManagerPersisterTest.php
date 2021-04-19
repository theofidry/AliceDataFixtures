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
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithIdentifier;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithRelation;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\IdGenerator;
use Fidry\AliceDataFixtures\Exception\ObjectGeneratorPersisterException;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 */
class ObjectManagerPersisterTest extends TestCase
{
    /**
     * @var ObjectManagerPersister
     */
    private $persister;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ORMPurger
     */
    private $purger;

    /**
     * @var ManagerRegistry|MockObject
     */
    private $managerRegistry;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->entityManager = $GLOBALS['entity_manager'];

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManagerForClass')->willReturn($this->entityManager);
        $this->managerRegistry->method('getManagers')->willReturn([$this->entityManager]);

        $this->persister = new ManagerRegistryPersister($this->managerRegistry);
        $this->purger = new ORMPurger($this->entityManager);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->entityManager->getUnitOfWork()->clear();
        $this->purger->purge();
    }

    public function testIsAPersister()
    {
        $this->assertTrue(is_a(ObjectManagerPersister::class, PersisterInterface::class, true));
        $this->assertTrue(is_a(ManagerRegistryPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ObjectManagerPersister::class))->isCloneable());
        $this->assertFalse((new ReflectionClass(ManagerRegistryPersister::class))->isCloneable());
    }

    /**
     * @dataProvider provideEntities
     */
    public function testCanPersistAnEntity($entity, bool $exact = false)
    {
        $originalEntity = clone $entity;

        $this->persister->persist($entity);
        $this->persister->flush();

        $this->entityManager->clear();

        $result = $this->entityManager->getRepository(get_class($entity))->findAll();

        $this->assertEquals(1, count($result));

        if ($exact) {
            $this->assertEquals($originalEntity, $result[0]);
        }
    }

    public function testCanPersistAnEntityWithRelationsAndExplicitIds()
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

    public function testCanPersistMultipleEntitiesWithExplicitIdentifierSet()
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

    public function testCanPersistEntitiesWithoutExplicitIdentifierSetEvenWhenExistingEntitiesHaveOne()
    {
        $dummy1 = new Dummy();
        $this->entityManager->persist($dummy1);
        $this->entityManager->flush();

        // When loading fixtures in real world and existing entity can be persisted again by the persister.
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

    public function testPersistingMultipleEntitiesWithAndWithoutExplicitIdentifierSetWillNotThrowORMException()
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
    public function testDoesNotPersistEmbeddables($dummy)
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

    public function provideEntities()
    {
        yield 'simple entity' => [new Dummy()];

        yield 'entity with embeddable' => [
            (function () {
                $embeddable = new DummyEmbeddable();
                $dummy = new DummyWithEmbeddable();

                $dummy->embeddable = $embeddable;

                return $dummy;
            })()
        ];

        yield 'sub class entity' => [
            (function () {
                $dummy = new DummySubClass();
                $dummy->status = 200;

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

    public function provideNonPersistableEntities()
    {
        yield 'embeddable' => [new DummyEmbeddable()];

        yield 'mapped super class' => [new MappedSuperclassDummy()];
    }
}
