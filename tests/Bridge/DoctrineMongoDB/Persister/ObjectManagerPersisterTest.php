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
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummyEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\DummyWithEmbeddable;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ObjectManagerPersisterTest extends TestCase
{
    /**
     * @var ObjectManagerPersister
     */
    private $persister;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var MongoDBPurger
     */
    private $purger;

    public function setUp()
    {
        $this->documentManager = $GLOBALS['document_manager'];
        $this->persister = new ObjectManagerPersister($this->documentManager);
        $this->purger = new MongoDBPurger($this->documentManager);
    }

    public function tearDown()
    {
        $this->purger->purge();
    }

    public function testIsAPersister()
    {
        $this->assertTrue(is_a(ObjectManagerPersister::class, PersisterInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone $this->persister;
    }

    /**
     * @dataProvider provideDocuments
     */
    public function testCanPersistADocument($document)
    {
        $this->persister->persist($document);
        $this->persister->flush();

        $result = $this->documentManager->getRepository(get_class($document))->findAll();

        $this->assertEquals(1, count($result));
    }

    /**
     * @dataProvider provideNonPersistableDocuments
     */
    public function testDoesNotPersistEmbeddables($dummy)
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

    public function provideDocuments()
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
    }

    public function provideNonPersistableDocuments()
    {
        yield 'mapped super class' => [new MappedSuperclassDummy()];
    }
}
