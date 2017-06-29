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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\Bundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DoctrineConnectionlessPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        try {
            $proxyCacheWarmerDefinition = $container->findDefinition('doctrine.orm.proxy_cache_warmer');
            $proxyCacheWarmerDefinition->clearTag('kernel.cache_warmer');
        } catch (ServiceNotFoundException $exception) {
        }
    }
}
