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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\ORM\FakeEntityManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionObject;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger
 */
class PurgerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAPurger(): void
    {
        self::assertTrue(is_a(Purger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory(): void
    {
        self::assertTrue(is_a(Purger::class, PurgerFactoryInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(Purger::class))->isCloneable());
    }

    public function testCreatesADoctrineOrmPurgerWithTheAppropriateManagerAndPurgeMode(): void
    {
        $manager = new FakeEntityManager();
        $purgeMode = PurgeMode::createTruncateMode();
        $purger = new Purger($manager, $purgeMode);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        self::assertInstanceOf(DoctrineOrmPurger::class, $decoratedPurger);
        self::assertEquals($manager, $decoratedPurger->getObjectManager());
        self::assertEquals(DoctrineOrmPurger::PURGE_MODE_TRUNCATE, $decoratedPurger->getPurgeMode());
    }

    public function testDisableFKChecksOnDeleteIsPerformed(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getDatabasePlatform()->willReturn($this->prophesize(MySqlPlatform::class)->reveal());
        $connection->exec('SET FOREIGN_KEY_CHECKS = 0;')->shouldBeCalled();
        $connection->exec('SET FOREIGN_KEY_CHECKS = 1;')->shouldBeCalled();

        $manager = $this->prophesize(EntityManager::class);
        $manager->getConnection()->willReturn($connection->reveal());

        $purgerORM = $this->prophesize(DoctrineOrmPurger::class);
        $purgerORM->getObjectManager()->willReturn($manager->reveal());
        $purgerORM->purge()->shouldBeCalled();

        $purgeMode = PurgeMode::createDeleteMode();
        $purger = new Purger($manager->reveal(), $purgeMode);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        $decoratedPurgerReflection->setValue($purger, $purgerORM->reveal());

        $purger->purge();
    }

    public function testEmptyDatabase(): void
    {
        /** @var EntityManager $manager */
        $manager = $GLOBALS['entity_manager'];

        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();

        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());

        $purger = new Purger($manager, PurgeMode::createDeleteMode());
        $purger->purge();

        self::assertCount(0, $manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();
        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());
    }
}
