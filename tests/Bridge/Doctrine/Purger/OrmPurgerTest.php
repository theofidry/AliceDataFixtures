<?php

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Fidry\AliceDataFixtures\Bridge\Doctrine\ORM\FakeEntityManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\OrmPurger
 */
class OrmPurgerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAPurger()
    {
        $this->assertTrue(is_a(OrmPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(OrmPurger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new OrmPurger(new FakeEntityManager(), PurgeMode::createDeleteMode());
    }

    public function testCreatesADoctrineOrmPurgerWithTheAppropriateManagerAndPurgeMode()
    {
        $manager = new FakeEntityManager();
        $purgeMode = PurgeMode::createTruncateMode();
        $purger = new OrmPurger($manager, $purgeMode);

        $decoratedPurgerReflection = (new \ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineOrmPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        $this->assertInstanceOf(DoctrineOrmPurger::class, $decoratedPurger);
        $this->assertEquals($manager, $decoratedPurger->getObjectManager());
        $this->assertEquals(DoctrineOrmPurger::PURGE_MODE_TRUNCATE, $decoratedPurger->getPurgeMode());
    }
}
