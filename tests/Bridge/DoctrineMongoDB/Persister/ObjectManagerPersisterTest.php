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

namespace Fidry\AliceDataFixtures\Bridge\DoctrineMongoDB\Persister;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 *
 * @requires extension mongodb
 */
class ObjectManagerPersisterTest extends TestCase
{
    private ObjectManagerPersister $persister;

    private DocumentManager $documentManager;

    private PurgerInterface $purger;

    public function setUp(): void
    {
        $this->documentManager = $GLOBALS['document_manager_factory']();
        $this->persister = new ObjectManagerPersister($this->documentManager);
        $this->purger = new MongoDBPurger($this->documentManager);
    }

    public function tearDown(): void
    {
        $this->purger->purge();
        $this->documentManager->clear();
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
     * @dataProvider provideDocuments
     */
    public function testCanPersistADocument($document, bool $exact = false): void
    {
        try {
            $this->persister->persist($document);
            $this->persister->flush();

            $this->documentManager->clear();

            $result = $this->documentManager->getRepository(get_class($document))->findAll();

            self::assertCount(1, $result);
        } catch (InvalidArgumentException $exception) {
            if ($exact) {
                // Do nothing: expected result as unsupported at the moment
                return;
            }

            throw $exception;
        }
    }

    /**
     * @dataProvider provideNonPersistableDocuments
     */
    public function testDoesNotPersistEmbeddables($dummy): void
    {
        try {
            $this->documentManager->persist($dummy);
            $this->documentManager->flush();
            $this->fail('Expected exception to be thrown.');
        } catch (MongoDBException $exception) {
            // Expected result
            $this->documentManager->clear();
        }

        $this->persister->persist($dummy);
        $this->persister->flush();
    }

    public static function provideDocuments(): iterable
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

        yield 'with explicit ID' => [
            (static function () {
                $dummy = new Dummy();
                $dummy->id = 200;

                return $dummy;
            })(),
            true
        ];
    }

    public static function provideNonPersistableDocuments(): iterable
    {
        yield 'mapped super class' => [new MappedSuperclassDummy()];
    }
}
