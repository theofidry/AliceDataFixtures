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

namespace Fidry\AliceDataFixtures\Bridge\DoctrinePhpCr\Purger;

use function bin2hex;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use function random_bytes;
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
        $manager = $this->prophesize(DocumentManager::class)->reveal();
        $purger = new Purger($manager);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        self::assertInstanceOf(PHPCRPurger::class, $decoratedPurger);
        self::assertEquals($manager, $decoratedPurger->getObjectManager());
    }

    public function testEmptyDatabase(): void
    {
        /** @var DocumentManager $manager */
        $manager = $GLOBALS['document_manager'];

        $dummy = new Dummy();
        $dummy->id = '/dummy_'.bin2hex(random_bytes(6));
        $manager->persist($dummy);
        $manager->flush();

        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());

        $purger = new Purger($manager, PurgeMode::createDeleteMode());
        $purger->purge();

        self::assertCount(0, $manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $dummy->id = '/dummy_'.bin2hex(random_bytes(6));
        $manager->persist($dummy);
        $manager->flush();
        self::assertCount(1, $manager->getRepository(Dummy::class)->findAll());
    }
}
