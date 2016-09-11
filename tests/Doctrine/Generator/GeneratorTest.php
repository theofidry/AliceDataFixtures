<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Doctrine;

use Hautelook\AliceBundle\Alice\ProcessorChain;
use Hautelook\AliceBundle\Doctrine\Generator\LoaderGenerator;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Doctrine\Generator
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $fixturesFinderProphecy = $this->prophesize('Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder');

        new LoaderGenerator($fixturesFinderProphecy->reveal(), 5);
    }

    /**
     * @cover ::generate
     */
    public function testGenerate()
    {
        $dataloaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\LoaderInterface');

        $processorProphecy = $this->prophesize('Nelmio\Alice\ProcessorInterface');
        $processorProphecy->preProcess('fixtureObject');
        $processorProphecy->postProcess('fixtureObject');

        $bundleProphecy = $this->prophesize('Symfony\Component\HttpKernel\Bundle\BundleInterface');

        $fixturesFinderProphecy = $this->prophesize('Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder');
        $fixturesFinderProphecy
            ->getDataLoaders([$bundleProphecy->reveal()], 'dev')
            ->willReturn([$dataloaderProphecy->reveal()])
        ;

        $fixturesLoaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\LoaderInterface');
        $fixturesLoaderProphecy->addProvider([$dataloaderProphecy->reveal()])->shouldBeCalled();
        $fixturesLoaderProphecy->load('fixtureFile', [])->willReturn(['fixtureObject']);

        $loaderProphecy = $this->prophesize('Hautelook\AliceBundle\Alice\DataFixtures\Loader\Loader');
        $loaderProphecy->getLoadingLimit()->willReturn(5);
        $loaderProphecy->getProcessorChain()->willReturn(new ProcessorChain([$processorProphecy->reveal()]));
        $loaderProphecy->getPersistOnce()->willReturn(true);

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist(['fixtureObject'])->shouldBeCalled();
        $persister = $persisterProphecy->reveal();

        $loaderGenerator = new LoaderGenerator($fixturesFinderProphecy->reveal());
        $loader = $loaderGenerator->generate(
            $loaderProphecy->reveal(),
            $fixturesLoaderProphecy->reveal(),
            [$bundleProphecy->reveal()],
            'dev'
        );

        $this->assertSame([$processorProphecy->reveal()], $loader->getProcessors());
        $this->assertTrue($loader->getPersistOnce());

        $loader->load($persister, ['fixtureFile']);
    }
}
