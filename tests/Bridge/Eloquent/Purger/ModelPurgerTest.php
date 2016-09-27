<?php

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Purger;

use Doctrine\ORM\EntityManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\ORM\FakeEntityManager;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger
 */
class ModelPurgerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAPurger()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerInterface::class, true));
    }

    public function testIsAPurgerFactory()
    {
        $this->assertTrue(is_a(ModelPurger::class, PurgerFactoryInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new ModelPurger(new FakeEntityManager(), PurgeMode::createDeleteMode());
    }

    public function testCreatesADoctrineModelPurgerWithTheAppropriateManagerAndPurgeMode()
    {
        $manager = new FakeEntityManager();
        $purgeMode = PurgeMode::createTruncateMode();
        $purger = new ModelPurger($manager, $purgeMode);

        $decoratedPurgerReflection = (new \ReflectionObject($purger))->getProperty('purger');
        $decoratedPurgerReflection->setAccessible(true);
        /** @var DoctrineModelPurger $decoratedPurger */
        $decoratedPurger = $decoratedPurgerReflection->getValue($purger);

        $this->assertInstanceOf(DoctrineModelPurger::class, $decoratedPurger);
        $this->assertEquals($manager, $decoratedPurger->getObjectManager());
        $this->assertEquals(DoctrineModelPurger::PURGE_MODE_TRUNCATE, $decoratedPurger->getPurgeMode());
    }

    public function testEmptyDatabase()
    {
        /** @var EntityManager $manager */
        $manager = $GLOBALS['entity_manager'];

        $dummy = new Dummy();
        $manager->persist($dummy);
        $manager->flush();

        $this->assertEquals(1, count($manager->getRepository(Dummy::class)->findAll()));

        $purger = new ModelPurger($manager, PurgeMode::createDeleteMode());
        $purger->purge();

        $this->assertEquals(0, count($manager->getRepository(Dummy::class)->findAll()));
    }
}
