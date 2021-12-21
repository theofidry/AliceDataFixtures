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

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
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
        $manager = $this->prophesize(DocumentManager::class)->reveal();
        $purger = new Purger($manager);

        $decoratedPurgerReflection = (new ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        self::assertInstanceOf(PHPCRPurger::class, $decoratedPurger);
        self::assertEquals($manager, $decoratedPurger->getObjectManager());
    }
}
