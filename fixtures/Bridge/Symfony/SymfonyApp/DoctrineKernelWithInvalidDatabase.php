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

use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\Bundle\DoctrineBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class DoctrineKernelWithInvalidDatabase extends DoctrineKernel
{
    public function getBundles()
    {
        $bundles = parent::getBundles();

        $bundles[] = new DoctrineBundle();

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $baseConfig = version_compare(Kernel::VERSION, '4.4.0', '>=') ? 'config_symfony_5.yml' : 'config.yml';

        $loader->load(__DIR__."/config/$baseConfig");
        $loader->load(__DIR__.'/config/config_doctrine_with_invalid_database.yml');
    }
}
