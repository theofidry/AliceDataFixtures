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

use Hautelook\AliceBundle\Doctrine\DataFixtures\Executor\MongoDBExecutor;
use Nelmio\Alice\Persister\Doctrine;
use Prophecy\Argument;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Doctrine\DataFixtures\MongoDBExecutor
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MongoDBExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (false === class_exists('Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle', true)) {
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
                return 'Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener' === get_class($args[0]);
            }
        );

        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\MongoDB\DocumentManager');
        $documentManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\MongoDBPurger');

        new MongoDBExecutor(
            $documentManagerProphecy->reveal(),
            $loaderProphecy->reveal()
        );

        new MongoDBExecutor(
            $documentManagerProphecy->reveal(),
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
                return 'Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener' === get_class($args[0]);
            }
        );

        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\MongoDB\DocumentManager');
        $documentManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($documentManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\MongoDBPurger');
        $purgerProphecy->setDocumentManager($documentManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->purge()->shouldNotBeCalled();

        $executor = new MongoDBExecutor(
            $documentManagerProphecy->reveal(),
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
                return 'Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener' === get_class($args[0]);
            }
        );

        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\MongoDB\DocumentManager');
        $documentManagerProphecy->getEventManager()->willReturn($eventManagerProphecy->reveal());

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($documentManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\MongoDBPurger');
        $purgerProphecy->setDocumentManager($documentManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->purge()->shouldBeCalled();

        $executor = new MongoDBExecutor(
            $documentManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->execute($fixtures);
    }
}
