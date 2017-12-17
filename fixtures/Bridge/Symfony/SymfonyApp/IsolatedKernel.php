<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

abstract class IsolatedKernel extends Kernel
{
    /**
     * @return static
     */
    public static function create()
    {
        return new static(uniqid(), true);
    }

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    $definition->setPublic(true);
                }
                foreach ($container->getAliases() as $id => $definition) {
                    $definition->setPublic(true);
                }
            }
        }, PassConfig::TYPE_OPTIMIZE);
    }
}
