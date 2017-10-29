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

namespace Fidry\AliceDataFixtures\Bridge\DoctrinePhpCr\Persister;

use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Exception\InvalidArgumentException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\MappedSuperclassDummy;
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
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var PHPCRPurger
     */
    private $purger;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->documentManager = $GLOBALS['document_manager'];
        $this->persister = new ObjectManagerPersister($this->documentManager);
        $this->purger = new PHPCRPurger($this->documentManager);
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
        } catch (InvalidArgumentException $exception) {
            // Expected result
            $this->documentManager->clear();
        }

        $this->persister->persist($dummy);
        $this->persister->flush();
    }

    public function provideDocuments()
    {
        yield 'simple entity' => [
            (function () {
                $dummy = new Dummy();
                $dummy->id = '/dummy_'.uniqid();

                return $dummy;
            })()
        ];

        yield 'sub class entity' => [
            (function () {
                $dummy = new DummySubClass();
                $dummy->id = '/subdummy_'.uniqid();
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
