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

namespace Fidry\AliceDataFixtures\Loader;

use Fidry\AliceDataFixtures\FileResolverInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Decorates another loader to resolve files before loading them.
 *
 * @final
 */
/*final*/ class FileResolverLoader implements LoaderInterface, PersisterAwareInterface
{
    use IsAServiceTrait;

    private $loader;
    private $fileResolver;

    public function __construct(LoaderInterface $decoratedLoader, FileResolverInterface $fileResolver)
    {
        $this->loader = $decoratedLoader;
        $this->fileResolver = $fileResolver;
    }

    /**
     * @inheritdoc
     */
    public function withPersister(PersisterInterface $persister): self
    {
        $loader = $this->loader;

        if ($loader instanceof PersisterAwareInterface) {
            $loader = $loader->withPersister($persister);
        }

        return new self($loader, $this->fileResolver);
    }

    /**
     * Resolves the given files before loading them.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        $fixturesFiles = $this->fileResolver->resolve($fixturesFiles);

        return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
    }
}
