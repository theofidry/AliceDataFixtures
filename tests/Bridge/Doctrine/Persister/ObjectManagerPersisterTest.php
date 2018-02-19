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
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\DummyWithIdentifier;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Exception\ObjectGeneratorPersisterException;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
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
     * @inheritdoc
     */
    public function setUp()
    {
        $this->entityManager = $GLOBALS['entity_manager'];
        $this->persister = new ObjectManagerPersister($this->entityManager);
        $this->purger = new ORMPurger($this->entityManager);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->purger->purge();
    }

    public function testIsAPersister()
    {
        $this->assertTrue(is_a(ObjectManagerPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ObjectManagerPersister::class))->isCloneable());
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

    public function testCanPersistMultipleEntitiesWithExplicitIdentifierSet()
    {
        $dummy = new DummyWithIdentifier();
        $dummy->id = 100;
        $this->persister->persist($dummy);

        $dummy = new DummyWithIdentifier();
        $dummy->id = 200;
        $this->persister->persist($dummy);

        $this->persister->flush();

        $entity = $this->entityManager->getRepository(DummyWithIdentifier::class)->find(200);
        $this->assertInstanceOf(DummyWithIdentifier::class, $entity);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /^No ID found for the entity ".*". If this entity has an auto ID generator, this may be due to having it disabled because one instance of the entity had an ID assigned. Either remove this assigned ID to allow the auto ID generator to operate or generate and ID for all the ".*" entities.$/
     */
    public function testPersistingMultipleEntitiesWithAndWithoutExplicitIdentifierSetWillThrowORMException()
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
