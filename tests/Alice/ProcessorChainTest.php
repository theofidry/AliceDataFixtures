<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Alice;

use Hautelook\AliceBundle\Alice\ProcessorChain;
use Hautelook\AliceBundle\Tests\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Alice\ProcessorChain
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ProcessorChainTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var ProcessorChain
     */
    private $processorChain;

    protected function setUp()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->processorChain = $this->application->getKernel()->getContainer()->get('hautelook_alice.alice.processor_chain');
    }

    /**
     * @cover ::__construct
     * @cover ::getProcessors
     */
    public function testConstructor()
    {
        $processorChain = new ProcessorChain([]);
        $this->assertSame([], $processorChain->getProcessors());

        $processors = [
            $this->prophesize('Nelmio\Alice\ProcessorInterface')->reveal(),
            $this->prophesize('Nelmio\Alice\ProcessorInterface')->reveal(),
        ];
        $processorChain = new ProcessorChain($processors);
        $this->assertSame($processors, $processorChain->getProcessors());

        $processors[] = 'foo';
        try {
            new ProcessorChain($processors);
            $this->fail('Expected an exception');
        } catch (\InvalidArgumentException $exception) {
            // Expected result
        }
    }

    /**
     * @coversNothing
     */
    public function testGenerator()
    {
        $this->assertSame(1, count($this->processorChain->getProcessors()));
        $this->assertSame(
            'Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Processor\BrandProcessor',
            get_class($this->processorChain->getProcessors()[0]),
            'Expected custom Faker provider to be registered.'
        );
    }
}
