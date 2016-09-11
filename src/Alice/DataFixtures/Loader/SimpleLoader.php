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
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;

/**
 * Bootstraps the given loader to persist the objects retrieved by the loader.
 *
 * @author Baldur Rensch <brensch@gmail.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class SimpleLoader implements LoaderInterface
{
    /**
     * @var FileLoaderInterface
     */
    private $fileLoader;

    public function __construct(FileLoaderInterface $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }

    /**
     * Loads each file one after another.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = []): array
    {
        $objectSet = new ObjectSet(new ParameterBag($parameters), new ObjectBag($objects));
        foreach ($fixturesFiles as $fixturesFile) {
            $objectSet = $this->fileLoader->loadFile(
                $fixturesFile,
                $objectSet->getParameters(),
                $objectSet->getObjects()
            );
        }

        return $objectSet->getObjects();
    }
}
