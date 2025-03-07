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

use function bin2hex;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Exception\InvalidArgumentException;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\DummySubClass;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\MappedSuperclassDummy;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use PHPUnit\Framework\TestCase;
use function random_bytes;
use ReflectionClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister
 */
class ObjectManagerPersisterTest extends TestCase
{
    private ObjectManagerPersister $persister;

    private DocumentManagerInterface $documentManager;

    private PurgerInterface $purger;

    public function setUp(): void
    {
        $this->documentManager = $GLOBALS['document_manager_factory']();
        $this->persister = new ObjectManagerPersister($this->documentManager);
        $this->purger = new PHPCRPurger($this->documentManager);
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
    public function testCanPersistADocument($document): void
    {
        $this->persister->persist($document);
        $this->persister->flush();

        $result = $this->documentManager->getRepository($document::class)->findAll();

        self::assertCount(1, $result);
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
        } catch (InvalidArgumentException) {
            // Expected result
            $this->documentManager->clear();
        }

        $this->persister->persist($dummy);
        $this->persister->flush();
    }

    public static function provideDocuments(): iterable
    {
        yield 'simple entity' => [
            (static function () {
                $dummy = new Dummy();
                $dummy->id = '/dummy_'.bin2hex(random_bytes(6));

                return $dummy;
            })()
        ];

        yield 'sub class entity' => [
            (static function () {
                $dummy = new DummySubClass();
                $dummy->id = '/subdummy_'.bin2hex(random_bytes(6));
                $dummy->status = '200';

                return $dummy;
            })()
        ];
    }

    public static function provideNonPersistableDocuments(): iterable
    {
        yield 'mapped super class' => [new MappedSuperclassDummy()];
    }
}
