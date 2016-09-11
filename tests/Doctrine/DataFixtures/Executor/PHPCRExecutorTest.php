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

use Hautelook\AliceBundle\Doctrine\DataFixtures\Executor\PHPCRExecutor;
use Nelmio\Alice\Persister\Doctrine;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Doctrine\DataFixtures\PHPCRExecutor
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PHPCRExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped('Not supported yet.');
    }

    /**
     * @cover ::__construct
     */
    public function testConstructor()
    {
        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\PHPCRPurger');

        new PHPCRExecutor(
            $documentManagerProphecy->reveal(),
            $loaderProphecy->reveal()
        );

        new PHPCRExecutor(
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

        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($documentManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\PHPCRPurger');
        $purgerProphecy->setDocumentManager($documentManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->purge()->shouldNotBeCalled();

        $executor = new PHPCRExecutor(
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

        $documentManagerProphecy = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');
        $loaderProphecy->load(new Doctrine($documentManagerProphecy->reveal()), $fixtures);

        $purgerProphecy = $this->prophesize('Doctrine\Common\DataFixtures\Purger\PHPCRPurger');
        $purgerProphecy->setDocumentManager($documentManagerProphecy->reveal())->shouldBeCalled();
        $purgerProphecy->purge()->shouldBeCalled();

        $executor = new PHPCRExecutor(
            $documentManagerProphecy->reveal(),
            $loaderProphecy->reveal(),
            $purgerProphecy->reveal()
        );

        $executor->execute($fixtures);
    }
}
