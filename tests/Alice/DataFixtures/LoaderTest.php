<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Alice\DataFixtures;

use Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader as FixtureLoader;
use Hautelook\AliceBundle\Alice\DataFixtures\Loader\SimpleLoader;
use Hautelook\AliceBundle\Alice\ProcessorChain;
use Nelmio\Alice\PersisterInterface;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Alice\DataFixtures\SimpleLoader\SimpleLoader
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $aliceLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');

        $processors = [$this->prophesize('Nelmio\Alice\ProcessorInterface')->reveal()];
        $loader = new SimpleLoader($aliceLoaderProphecy->reveal(), new ProcessorChain($processors), false, 5);

        $this->assertSame($processors, $loader->getProcessors());
        $this->assertFalse($loader->getPersistOnce());

        $aliceLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');

        $loader = new Loader\Loader($aliceLoaderProphecy->reveal(), new ProcessorChain([]), true, 5);

        $this->assertSame([], $loader->getProcessors());
        $this->assertTrue($loader->getPersistOnce());
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadEmptyFixturesSet()
    {
        $aliceLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');

        $processors = [$this->prophesize('Nelmio\Alice\ProcessorInterface')->reveal()];
        $loader = new SimpleLoader($aliceLoaderProphecy->reveal(), new ProcessorChain($processors), false, 5);
        $objects = $loader->load($persisterProphecy->reveal(), []);

        $this->assertSame([], $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithFixtures()
    {
        $object = new \stdClass();

        $oldPersister = $this->prophesize('Nelmio\Alice\PersisterInterface')->reveal();

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$object])->shouldBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');
        $fixturesLoaderProphecy->getPersister()->willReturn($oldPersister);
        $fixturesLoaderProphecy->load('random/file', [])->willReturn([$object]);
        $fixturesLoaderProphecy->setPersister($persisterProphecy->reveal())->shouldBeCalled();
        $fixturesLoaderProphecy->setPersister($oldPersister)->shouldBeCalled();

        $loader = new SimpleLoader($fixturesLoaderProphecy->reveal(), new ProcessorChain([]), false, 5);
        $objects = $loader->load($persisterProphecy->reveal(), ['random/file']);

        $this->assertSame([$object], $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithPersistOnceAtFalse()
    {
        $expected = [
            new \stdClass(),
            new \stdClass(),
        ];

        /* @var PersisterInterface $oldPersister */
        $oldPersister = $this->prophesize('Nelmio\Alice\PersisterInterface')->reveal();

        $pass1 = [$expected[0]];
        $pass2 = [$expected[1]];

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist($pass1)->shouldBeCalled();
        $persisterProphecy->persist($pass2)->shouldBeCalled();
        /* @var PersisterInterface $persister */
        $persister = $persisterProphecy->reveal();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');
        $fixturesLoaderProphecy->getPersister()->willReturn($oldPersister);
        $fixturesLoaderProphecy->load('random/file1', [])->willReturn($pass1);
        $fixturesLoaderProphecy->load('random/file2', $pass1)->willReturn($pass2);
        $fixturesLoaderProphecy->setPersister($persisterProphecy->reveal())->shouldBeCalled();
        $fixturesLoaderProphecy->setPersister($oldPersister)->shouldBeCalled();
        /* @var FixtureLoader $fixturesLoader */
        $fixturesLoader = $fixturesLoaderProphecy->reveal();

        $loader = new SimpleLoader($fixturesLoader, new ProcessorChain([]), false, 5);
        $actual = $loader->load(
            $persister,
            [
                'random/file1',
                'random/file2',
            ]
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithPersistOnceAtTrue()
    {
        $expected = [
            new \stdClass(),
            new \stdClass(),
        ];
        $pass1 = [$expected[0]];
        $pass2 = [$expected[1]];

        /* @var PersisterInterface $oldPersister */
        $oldPersister = $this->prophesize('Nelmio\Alice\PersisterInterface')->reveal();

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist($expected)->shouldBeCalled();
        /* @var PersisterInterface $persister */
        $persister = $persisterProphecy->reveal();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');
        $fixturesLoaderProphecy->getPersister()->willReturn($oldPersister);
        $fixturesLoaderProphecy->load('random/file1', [])->willReturn($pass1);
        $fixturesLoaderProphecy->load('random/file2', $pass1)->willReturn($pass2);
        $fixturesLoaderProphecy->setPersister($persisterProphecy->reveal())->shouldBeCalled();
        $fixturesLoaderProphecy->setPersister($oldPersister)->shouldBeCalled();
        /* @var FixtureLoader $fixturesLoader */
        $fixturesLoader = $fixturesLoaderProphecy->reveal();

        $loader = new SimpleLoader($fixturesLoader, new ProcessorChain([]), true, 5);
        $actual = $loader->load(
            $persister,
            [
                'random/file1',
                'random/file2',
            ]
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithFixturesAndProcessors()
    {
        $object = new \stdClass();

        $oldPersister = $this->prophesize('Nelmio\Alice\PersisterInterface')->reveal();

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$object])->shouldBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader');
        $fixturesLoaderProphecy->getPersister()->willReturn($oldPersister);
        $fixturesLoaderProphecy->load('random/file', [])->willReturn([$object]);
        $fixturesLoaderProphecy->setPersister($persisterProphecy->reveal())->shouldBeCalled();
        $fixturesLoaderProphecy->setPersister($oldPersister)->shouldBeCalled();

        $processorProphecy = $this->prophesize('Nelmio\Alice\ProcessorInterface');
        $processorProphecy->preProcess($object)->shouldBeCalled();
        $processorProphecy->postProcess($object)->shouldBeCalled();

        $loader = new Loader\Loader($fixturesLoaderProphecy->reveal(), new ProcessorChain([$processorProphecy->reveal()]), false, 5);
        $objects = $loader->load($persisterProphecy->reveal(), ['random/file']);

        $this->assertSame([$object], $objects);
    }

    public function testLoaderInterface()
    {
        $object = new \stdClass();

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$object])->shouldBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface');
        $fixturesLoaderProphecy->load('random/file', [])->willReturn([$object]);

        $loader = new Loader\Loader($fixturesLoaderProphecy->reveal(), new ProcessorChain([]), false, 5);
        $objects = $loader->load($persisterProphecy->reveal(), ['random/file']);

        $this->assertSame([$object], $objects);
    }

    /**
     * @expectedException \Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException
     */
    public function testLoaderLimit()
    {
        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist()->shouldNotBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface');
        $fixturesLoaderProphecy->load('random/file', [])->willThrow(new \UnexpectedValueException('Instance * is not defined'));
        $fixturesLoaderProphecy->load('random/file', [])->shouldBeCalledTimes(6);

        $loader = new Loader\Loader($fixturesLoaderProphecy->reveal(), new ProcessorChain([]), false, 5);
        $loader->load($persisterProphecy->reveal(), ['random/file']);
    }

    /**
     * @expectedException \Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException
     */
    public function testLoaderWithCustomLimit()
    {
        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist()->shouldNotBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface');
        $fixturesLoaderProphecy->load('random/file', [])->willThrow(new \UnexpectedValueException('Instance * is not defined'));
        $fixturesLoaderProphecy->load('random/file', [])->shouldBeCalledTimes(11);

        $loader = new SimpleLoader($fixturesLoaderProphecy->reveal(), new ProcessorChain([]), false, 10);
        $loader->load($persisterProphecy->reveal(), ['random/file']);
    }

    /**
     * @covers ::load()
     * @covers ::registerErrorMessage()
     */
    public function testLoaderLimitWithMessages()
    {
        $this->setExpectedException(
            '\Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException',
            'Loading files limit of 3 reached. Could not load the following files:'.PHP_EOL
            .'another/file:'.PHP_EOL
            .' - Instance user1 is not defined'.PHP_EOL
            .'empty/message:'.PHP_EOL
            .' - Instance user2 is not defined'.PHP_EOL
            .'random/file:'.PHP_EOL
            .' - Instance user0 is not defined'
        );

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist()->shouldNotBeCalled();

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface');
        $fixturesLoaderProphecy->load('random/file', [])->willThrow(new \UnexpectedValueException('Instance user0 is not defined'));
        $fixturesLoaderProphecy->load('another/file', [])->willThrow(new \UnexpectedValueException('Instance user1 is not defined'));
        $fixturesLoaderProphecy->load('empty/message', [])->willThrow(new \UnexpectedValueException('Instance user2 is not defined'));

        $loader = new Loader\Loader($fixturesLoaderProphecy->reveal(), new ProcessorChain([]), false, 3);
        $loader->load($persisterProphecy->reveal(), ['random/file', 'another/file', 'empty/message']);
    }
}
