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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\AnotherDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithIdentifier;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithProperty;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithRelatedCascadePersist;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithRelation;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\IdGenerator;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 */
class ObjectManagerPersisterTest extends TestCase
{
    private ObjectManagerPersister $persister;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        $this->entityManager = $GLOBALS['entity_manager_factory']();
        $this->persister = new ObjectManagerPersister($this->entityManager);

        $this->entityManager->getConnection()->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        $this->entityManager->clear();
    }

    public function testIsAPersister(): void
    {
        self::assertTrue(is_a(ObjectManagerPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ObjectManagerPersister::class))->isCloneable());
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

        self::assertCount(1, $result);

        if ($exact) {
            self::assertEquals($originalEntity, $result[0]);
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
        self::assertInstanceOf(DummyWithIdentifier::class, $result);
        self::assertEquals($result->id, $dummy->id);

        $result = $this->entityManager->getRepository(DummyWithRelation::class)->findOneBy(['id' => 200]);
        self::assertInstanceOf(DummyWithRelation::class, $result);
        self::assertEquals($result->id, $dummyWithRelation->id);
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

        self::assertEquals(
            IdGenerator::class,
            get_class($classMetadata->idGenerator),
            'ID generator should be changed.'
        );

        $this->persister->flush();

        $classMetadata = $this->entityManager->getClassMetadata(DummyWithIdentifier::class);

        self::assertNotEquals(
            IdGenerator::class,
            get_class($classMetadata->idGenerator),
            'ID generator should be restored after flush.'
        );

        $entity = $this->entityManager->getRepository(DummyWithIdentifier::class)->find(200);
        self::assertInstanceOf(DummyWithIdentifier::class, $entity);
    }

    public function testClearingUnitOfWorkPersister(): void
    {
        $dummy = new DummyWithProperty();
        $dummy->id = 1;

        $this->persister->persist($dummy);
        $this->persister->flush();

        $dummy = new DummyWithProperty();
        $dummy->property = 'foo';

        $this->entityManager->persist($dummy);
        $this->entityManager->flush();

        $entity = $this->entityManager->getRepository(DummyWithProperty::class)->findOneBy(['property' => 'foo']);
        self::assertInstanceOf(DummyWithProperty::class, $entity);
    }

    public function testCanPersistEntitiesWithoutExplicitIdentifierSetEvenWhenExistingEntitiesHaveOne(): void
    {
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
        self::assertInstanceOf(Dummy::class, $entity);

        $entity = $this->entityManager->getRepository(Dummy::class)->find($dummy2->id);
        self::assertInstanceOf(Dummy::class, $entity);
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
        // Sanity check
        try {
            $this->entityManager->persist($dummy);
            $this->entityManager->flush();

            $this->fail('Expected exception to be thrown.');
        } catch (Throwable) {
            // Expected result
            $this->entityManager->clear();
        }

        $this->persister->persist($dummy);
        $this->persister->flush();

        self::assertTrue(true, 'Everything is fine.');
    }

    public function testIdGeneratorIsDisabledForCascadePersist(): void
    {
        $related = new Dummy();
        $related->id = 100;

        $anotherDummy1 = new AnotherDummy();
        $anotherDummy1->id = 5000;

        $anotherDummy2 = new AnotherDummy();
        $anotherDummy2->id = 5010;

        $anotherDummy3 = new AnotherDummy();

        $entity = new DummyWithRelatedCascadePersist();
        $entity->id = 200;
        $entity->related = $related;
        $entity->relatedMultiple->add($anotherDummy1);
        $entity->relatedMultiple->add($anotherDummy2);
        $entity->relatedMultiple->add($anotherDummy3);

        $this->persister->persist($entity);
        $this->persister->flush();

        $this->entityManager->clear();

        /** @var DummyWithRelatedCascadePersist $fetchedEntity */
        $fetchedEntity = $this->entityManager->getRepository(get_class($entity))->find(200);

        $this->assertSame($entity->id, $fetchedEntity->id);
        $this->assertSame(100, $fetchedEntity->related->id);
        $this->assertSame(5000, $fetchedEntity->relatedMultiple->get(0)->id);
        $this->assertSame(5010, $fetchedEntity->relatedMultiple->get(1)->id);
        $this->assertSame(5011, $fetchedEntity->relatedMultiple->get(2)->id);
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
            (static function () {
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
