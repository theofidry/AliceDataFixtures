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
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Fidry\AliceDataFixtures\Bridge\Doctrine\ORM\FakeEntityManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionObject;

#[CoversNothing]
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
        $mappingDriverProphecy = $this->prophesize(MappingDriver::class);
        $mappingDriverProphecy->getAllClassNames()->willReturn([]);

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl($mappingDriverProphecy->reveal());

        $connection = $this->prophesize(Connection::class);
        $connection->getConfiguration()->willReturn($configuration);
        $connection->getDatabasePlatform()->willReturn($this->prophesize(MySqlPlatform::class)->reveal());
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;')->shouldBeCalled();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1;')->shouldBeCalled();

        $classMetadataFactory = new ClassMetadataFactory();

        $manager = $this->prophesize(EntityManager::class);
        $manager->getConfiguration()->willReturn($configuration);
        $manager->getConnection()->willReturn($connection->reveal());
        $manager->getEventManager()->willReturn(new EventManager());
        $manager->getMetadataFactory()->willReturn($classMetadataFactory);

        $classMetadataFactory->setEntityManager($manager->reveal());

        $purgerORM = new DoctrineOrmPurger(
            $manager->reveal(),
        );

        $purgeMode = PurgeMode::createDeleteMode();
        $purger = new Purger($manager->reveal(), $purgeMode);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        $decoratedPurgerReflection->setValue($purger, $purgerORM);

        $purger->purge();
    }
}
