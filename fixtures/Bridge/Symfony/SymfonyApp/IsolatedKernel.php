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

use function bin2hex;
use function random_bytes;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use function version_compare;

abstract class IsolatedKernel extends Kernel
{
    /**
     * @return static
     */
    public static function create()
    {
        return new static(bin2hex(random_bytes(6)), true);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new class() implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    foreach ($container->getDefinitions() as $id => $definition) {
                        if (!str_starts_with($id, 'fidry_alice_data_fixtures')) {
                            continue;
                        }

                        $definition->setPublic(true);
                    }
                    foreach ($container->getAliases() as $id => $definition) {
                        if (!str_starts_with($id, 'fidry_alice_data_fixtures')) {
                            continue;
                        }

                        $definition->setPublic(true);
                    }
                }
            },
            PassConfig::TYPE_OPTIMIZE,
        );

        $container->setParameter('project_dir', __DIR__);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $baseConfig = version_compare(
            Kernel::VERSION,
            '4.0.0',
            '<'
        )
            ? 'config_symfony_3.yml'
            : 'config.yml';

        $loader->load(__DIR__."/config/$baseConfig");
    }
}
