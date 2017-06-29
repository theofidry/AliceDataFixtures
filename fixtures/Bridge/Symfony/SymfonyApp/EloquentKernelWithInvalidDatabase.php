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

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EloquentKernelWithInvalidDatabase extends EloquentKernel
{
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
        $loader->load(__DIR__.'/config/config_eloquent_with_invalid_database.yml');
    }
}
