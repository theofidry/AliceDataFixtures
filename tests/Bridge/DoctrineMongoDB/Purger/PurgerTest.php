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
}
