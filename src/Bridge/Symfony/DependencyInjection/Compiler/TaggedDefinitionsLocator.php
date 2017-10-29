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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Utility class to locate tagged service definitions.
 *
 * @private
 */
final class TaggedDefinitionsLocator
{
    /**
     * Finds service definitions tagged by a given tag name.
     *
     * @param ContainerBuilder $container
     * @param string           $tagName
     *
     * @return Reference[]
     */
    public function findReferences(ContainerBuilder $container, string $tagName): array
    {
        $taggedServiceIds = $container->findTaggedServiceIds($tagName);

        $taggedReferences = [];
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $taggedReferences[] = new Reference($taggedServiceId);
        }

        return $taggedReferences;
    }
}
