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

namespace Fidry\AliceDataFixtures\Bridge\Propel2\Purger;

use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Author;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\AuthorQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Book;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\BookQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\PropelTestCase;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Propel\Runtime\Propel;
use ReflectionClass;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ModelPurgerTest extends PropelTestCase
{
    /**
     * @var ModelPurger
     */
    private $purger;

    public function setUp()
    {
        $this->purger = new ModelPurger(
            Propel::getConnection(),
            __DIR__ . '/../../../../fixtures/Bridge/Propel2/generated/sql'
        );
        $this->initDatabase();
    }

    public function testIsAPurger()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No propel generated SQL file exists
     */
    public function testSqlDoesNotExist()
    {
        (new ModelPurger(Propel::getConnection(), 'asdasd'))->purge();
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ModelPurger::class))->isCloneable());
    }

    public function testPurge()
    {
        $this->createRecords();

        $this->assertCount(1, AuthorQuery::create()->find());
        $this->assertCount(1, BookQuery::create()->find());

        $this->purger->purge();

        $this->assertCount(0, AuthorQuery::create()->find());
        $this->assertCount(0, BookQuery::create()->find());
    }

    private function createRecords()
    {
        $book = new Book();
        $book->setTitle('East of Eden');

        $author = new Author();
        $author->setName('John Steinbeck');

        $book->setAuthor($author);
        $book->save();
        $author->save();
    }
}
