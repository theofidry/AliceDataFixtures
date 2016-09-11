<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\DependencyInjection\Compiler;

use Hautelook\AliceBundle\DependencyInjection\Compiler\ProcessorCompilerPass;
use Prophecy\Argument;

/**
 * @coversDefaultClass Hautelook\AliceBundle\DependencyInjection\Compiler\ProcessorCompilerPass
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ProcessorCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::process
     */
    public function testProcess()
    {
        $processorProviderPass = new ProcessorCompilerPass();

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $processorProviderPass);

        $definitionProphecy = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $definitionProphecy->addArgument(Argument::type('array'))->shouldBeCalled();
        $definition = $definitionProphecy->reveal();

        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerBuilderProphecy->findTaggedServiceIds('hautelook_alice.alice.processor')->willReturn(
            ['foo', 'bar']
        )->shouldBeCalled();
        $containerBuilderProphecy->findDefinition('hautelook_alice.alice.processor_chain')->willReturn($definition)->shouldBeCalled();
        $containerBuilder = $containerBuilderProphecy->reveal();

        $processorProviderPass->process($containerBuilder);
    }
}
