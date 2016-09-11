<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Doctrine\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Hautelook\AliceBundle\Doctrine\DataFixtures\Executor\ORMExecutor;
use Nelmio\Alice\Persister\Doctrine;
use Prophecy\Argument;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Doctrine\DataFixtures\ORMExecutor
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ORMExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (false === class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', true)) {
            $this->markTestSkipped('Bundle not installed.');
        }
    }
    
    /**
     * @cover ::__construct
     */
    public function testConstructor()
    {
        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');

        new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal()
        );

        new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );
    }

    /**
     * @cover ::execute
     */
    public function testExecutorWithAppend()
    {
        $fixtures = ['fixture1'];

        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());
        $entityManagerProphecy->transactional(Argument::any())->shouldBeCalled();

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($entityManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $purgerProphecy->setEntityManager($entityManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->purge()->shouldNotBeCalled();

        $executor = new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->execute($fixtures, true);
    }

    /**
     * @cover ::execute
     */
    public function testExecutorWithoutAppend()
    {
        $fixtures = ['fixture1'];

        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());
        $entityManagerProphecy->transactional(Argument::any())->shouldBeCalled();

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($entityManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $purgerProphecy->setEntityManager($entityManagerProphecy->reveal())->shouldBeCalled();

        $executor = new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->execute($fixtures);
    }

    /**
     * @cover ::purge
     */
    public function testPurgeWithTruncateUsingMySQLPlatform()
    {
        $platformProphecy = $this->prophesize('Doctrine\DBAL\Platforms\MySqlPlatform');

        $connectionProphecy = $this->prophesize('Doctrine\DBAL\Connection');
        $connectionProphecy->getDatabasePlatform()->willReturn($platformProphecy->reveal());
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 0;')->shouldBeCalled();
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 1;')->shouldBeCalled();

        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());
        $entityManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $purgerProphecy->setEntityManager($entityManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->getPurgeMode()->willReturn(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerProphecy->purge()->shouldBeCalled();

        $executor = new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->purge();
    }

    /**
     * @cover ::purge
     */
    public function testPurgeWithoutTruncateUsingMySQLPlatform()
    {
        $platformProphecy = $this->prophesize('Doctrine\DBAL\Platforms\MySqlPlatform');

        $connectionProphecy = $this->prophesize('Doctrine\DBAL\Connection');
        $connectionProphecy->getDatabasePlatform()->willReturn($platformProphecy->reveal());
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 0;')->shouldNotBeCalled();
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 1;')->shouldNotBeCalled();

        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());
        $entityManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $purgerProphecy->setEntityManager($entityManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->getPurgeMode()->willReturn(ORMPurger::PURGE_MODE_DELETE);
        $purgerProphecy->purge()->shouldBeCalled();

        $executor = new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->purge();
    }

    /**
     * @cover ::purge
     */
    public function testPurgeWithTruncateUsingSqlitePlatform()
    {
        $platformProphecy = $this->prophesize('Doctrine\DBAL\Platforms\SqlitePlatform');

        $connectionProphecy = $this->prophesize('Doctrine\DBAL\Connection');
        $connectionProphecy->getDatabasePlatform()->willReturn($platformProphecy->reveal());
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 0;')->shouldNotBeCalled();
        $connectionProphecy->exec('SET FOREIGN_KEY_CHECKS = 1;')->shouldNotBeCalled();

        $eventManagerProphecy = $this->prophesize('Doctrine\Common\EventManager');
        $eventManagerProphecy->addEventSubscriber(Argument::any())->will(
            function ($args) {
                return 'Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener' === get_class($args[0]);
            }
        );

        $entityManagerProphecy = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $entityManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());
        $entityManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $purgerProphecy->setEntityManager($entityManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->getPurgeMode()->willReturn(ORMPurger::PURGE_MODE_TRUNCATE);
        $purgerProphecy->purge()->shouldBeCalled();

        $executor = new ORMExecutor(
            $entityManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->purge();
    }
}
