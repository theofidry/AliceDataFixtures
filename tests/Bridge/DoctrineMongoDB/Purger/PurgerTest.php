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
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionObject;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger
 *
 * @requires extension mongodb
 */
class PurgerTest extends TestCase
{
    use ProphecyTrait;

    public function testCreatesADoctrineOdmPurgerWithTheAppropriateManager(): void
    {
        $manager = $this->prophesize(DocumentManager::class)->reveal();
        $purger = new Purger($manager);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        self::assertInstanceOf(MongoDBPurger::class, $decoratedPurger);
        self::assertEquals($manager, $decoratedPurger->getObjectManager());
    }

    public function testEmptyDatabase(): void
    {
        /** @var DocumentManagerInterface $manager */
        $manager = $GLOBALS['document_manager_factory']();

        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();

        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());

        $purger = new Purger($manager);
        $purger->purge();

        self::assertCount(0, $manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();
        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());

        // TODO: move to a tearDown()
        $manager->clear();
    }
}
