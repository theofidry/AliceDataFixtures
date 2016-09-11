<?php

/*
 * This file is part of the Fidry\AlicePersistence package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AlicePersistence\Alice\DataFixtures\Loader;

use Fidry\AlicePersistence\Alice\DataFixtures\LoaderInterface;
use Fidry\AlicePersistence\FileResolverInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class FileResolverLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var FileResolverInterface
     */
    private $fileResolver;

    public function __construct(LoaderInterface $decoratedLoader, FileResolverInterface $fileResolver)
    {
        $this->loader = $decoratedLoader;
        $this->fileResolver = $fileResolver;
    }

    /**
     * Resolves the given files before loading them.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = []): array
    {
        $fixturesFiles = $this->fileResolver->resolve($fixturesFiles);

        return $this->loader->load($fixturesFiles, $parameters, $objects);
    }
}
