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
class PurgerIntegrationTest extends TestCase
{
    private EntityManager $manager;
    
    protected function setUp(): void
    {
        $this->manager = $GLOBALS['entity_manager'];
        $this->manager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $this->manager->clear();
    }

    public function testEmptyDatabase(): void
    {
        $dummy = new Dummy();
        $this->manager->persist($dummy);
        $this->manager->flush();

        self::assertCount(1, $this->manager->getRepository(Dummy::class)->findAll());

        $purger = new Purger($this->manager, PurgeMode::createDeleteMode());
        $purger->purge();

        self::assertCount(0, $this->manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $this->manager->persist($dummy);
        $this->manager->flush();
        self::assertCount(1, $this->manager->getRepository(Dummy::class)->findAll());
    }
}
