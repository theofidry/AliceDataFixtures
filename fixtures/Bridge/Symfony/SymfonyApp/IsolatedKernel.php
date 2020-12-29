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

use Symfony\Component\Config\Loader\LoaderInterface;
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
                    if (strpos($id, 'fidry_alice_data_fixtures') !== 0) {
                        continue;
                    }

                    $definition->setPublic(true);
                }
                foreach ($container->getAliases() as $id => $definition) {
                    if (strpos($id, 'fidry_alice_data_fixtures') !== 0) {
                        continue;
                    }

                    $definition->setPublic(true);
                }
            }
        }, PassConfig::TYPE_OPTIMIZE);

        $container->setParameter('project_dir', __DIR__);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $baseConfig = version_compare(Kernel::VERSION, '4.0.0', '<') ? 'config_symfony_3.yml' : 'config.yml';

        $loader->load(__DIR__."/config/$baseConfig");
    }
}
