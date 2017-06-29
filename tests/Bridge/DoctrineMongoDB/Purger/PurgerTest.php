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

namespace Fidry\AliceDataFixtures\Bridge\DoctrineMongoDB\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\DocumentManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger
 */
class PurgerTest extends TestCase
{
    public function testCreatesADoctrineOdmPurgerWithTheAppropriateManager()
    {
        $manager = $this->prophesize(DocumentManager::class)->reveal();
        $purger = new Purger($manager);

        $decoratedPurgerReflection = (new \ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        $this->assertInstanceOf(MongoDBPurger::class, $decoratedPurger);
        $this->assertEquals($manager, $decoratedPurger->getObjectManager());
    }

    public function testEmptyDatabase()
    {
        /** @var DocumentManager $manager */
        $manager = $GLOBALS['document_manager'];

        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();

        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));

        $purger = new Purger($manager);
        $purger->purge();

        $this->assertEquals(0, count($manager->getRepository(Dummy::class)->findAll()));

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();
        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));
    }
}
