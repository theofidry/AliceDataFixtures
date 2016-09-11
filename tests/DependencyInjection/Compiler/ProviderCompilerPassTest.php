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

use Hautelook\AliceBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Prophecy\Argument;

/**
 * @coversDefaultClass Hautelook\AliceBundle\DependencyInjection\Compiler\ProviderCompilerPass
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ProviderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::process
     */
    public function testProcess()
    {
        $providerProviderPass = new ProviderCompilerPass();

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $providerProviderPass);

        $definitionProphecy = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $definitionProphecy->addMethodCall('addProvider', Argument::type('array'))->shouldBeCalled();
        $definitionProphecy->addMethodCall('addProvider', Argument::type('array'))->shouldBeCalled();
        $definitionProphecy->addArgument(Argument::type('array'))->shouldBeCalled();
        $definition = $definitionProphecy->reveal();

        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerBuilderProphecy->findTaggedServiceIds('hautelook_alice.faker.provider')->willReturn(
            ['foo', 'bar']
        )->shouldBeCalled();
        $containerBuilderProphecy->findDefinition('hautelook_alice.faker')->willReturn($definition)->shouldBeCalled();
        $containerBuilderProphecy->findDefinition('hautelook_alice.faker.provider_chain')->willReturn($definition)->shouldBeCalled();
        $containerBuilder = $containerBuilderProphecy->reveal();

        $providerProviderPass->process($containerBuilder);
    }
}
