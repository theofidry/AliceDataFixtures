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
use Fidry\AliceDataFixtures\Bridge\Doctrine\PhpCrDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
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
        clone new Purger($this->prophesize(DocumentManager::class)->reveal());
    }

    public function testCreatesADoctrineOrmPurgerWithTheAppropriateManagerAndPurgeMode()
    {
        $manager = $this->prophesize(DocumentManager::class)->reveal();
        $purger = new Purger($manager);

        $decoratedPurgerReflection = (new \ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        $this->assertInstanceOf(PHPCRPurger::class, $decoratedPurger);
        $this->assertEquals($manager, $decoratedPurger->getObjectManager());
    }

    public function testEmptyDatabase()
    {
        /** @var DocumentManager $manager */
        $manager = $GLOBALS['document_manager'];

        $dummy = new Dummy();
        $dummy->id = '/dummy_'.uniqid();
        $manager->persist($dummy);
        $manager->flush();

        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));

        $purger = new Purger($manager, PurgeMode::createDeleteMode());
        $purger->purge();

        $this->assertEquals(0, count($manager->getRepository(Dummy::class)->findAll()));

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $dummy->id = '/dummy_'.uniqid();
        $manager->persist($dummy);
        $manager->flush();
        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));
    }
}
