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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\ORM\EntityManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\ORM\FakeEntityManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger
 */
class PurgerTest extends TestCase
{
    public function testIsAPurger()
    {
        $this->assertTrue(is_a(Purger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(Purger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new Purger(new FakeEntityManager(), PurgeMode::createDeleteMode());
    }

    public function testCreatesADoctrineOrmPurgerWithTheAppropriateManagerAndPurgeMode()
    {
        $manager = new FakeEntityManager();
        $purgeMode = PurgeMode::createTruncateMode();
        $purger = new Purger($manager, $purgeMode);

        $decoratedPurgerReflection = (new \ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        $this->assertInstanceOf(DoctrineOrmPurger::class, $decoratedPurger);
        $this->assertEquals($manager, $decoratedPurger->getObjectManager());
        $this->assertEquals(DoctrineOrmPurger::PURGE_MODE_TRUNCATE, $decoratedPurger->getPurgeMode());
    }

    public function testEmptyDatabase()
    {
        /** @var EntityManager $manager */
        $manager = $GLOBALS['entity_manager'];

        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();

        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));

        $purger = new Purger($manager, PurgeMode::createDeleteMode());
        $purger->purge();

        $this->assertEquals(0, count($manager->getRepository(Dummy::class)->findAll()));

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();
        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));
    }
}
