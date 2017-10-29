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

namespace Fidry\AliceDataFixtures\Bridge\Propel2\Persister;

use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Author;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\AuthorQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Book;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\BookQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\PropelTestCase;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Propel\Runtime\Propel;
use ReflectionClass;
use stdClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Propel2\Persister\ModelPersister
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ModelPersisterTest extends PropelTestCase
{
    /**
     * @var ModelPersister
     */
    private $persister;

    public function setUp()
    {
        $this->persister = new ModelPersister(Propel::getConnection());
        $this->initDatabase();
    }

    public function testIsAPersister()
    {
        $this->assertTrue(is_a(ModelPersister::class, PersisterInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ModelPersister::class))->isCloneable());
    }

    public function testCanPersistAModel()
    {
        $model = new Author();
        $model->setName('John Steinbeck');

        $this->assertNull($model->getId());

        $this->persister->persist($model);

        $this->assertNull($model->getId());

        $this->persister->flush();
        $this->assertNotNull($model->getId());

        $this->assertCount(1, AuthorQuery::create()->find());
    }

    public function testCanPersistAModelWithARelationship()
    {
        $book = new Book();
        $book->setTitle('East of Eden');

        $author = new Author();
        $author->setName('John Steinbeck');

        $book->setAuthor($author);

        $this->persister->persist($book);
        $this->assertNull($book->getId());

        $this->persister->flush();
        $this->assertNotNull($book->getId());

        $this->assertCount(1, AuthorQuery::create()->find());
        $this->assertCount(1, BookQuery::create()->find());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected object to be an instance of "Propel\Runtime\ActiveRecord\ActiveRecordInterface", got "stdClass" instead.
     */
    public function testCannotPersistANonModelObject()
    {
        $object = new stdClass();

        $this->persister->persist($object);
    }
}
