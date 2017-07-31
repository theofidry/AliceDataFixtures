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

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Author;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\AuthorQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\Book;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\BookQuery;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Fidry\AliceDataFixtures\Bridge\Propel2\Purger\ModelPurger;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ModelPurgerTest extends TestCase
{
    public function testIsAPurger()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new ModelPurger('foo');
    }

    public function testPurge()
    {
        $this->createRecords();

        $this->assertCount(1, AuthorQuery::create()->find());
        $this->assertCount(1, BookQuery::create()->find());

        $purger = new ModelPurger(__DIR__ . '/../generated/sql');
        $purger->purge();

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
