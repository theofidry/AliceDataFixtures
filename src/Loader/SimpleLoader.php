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

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;

/**
 * Minimalistic loader implementation.
 *
 * @author Baldur Rensch <brensch@gmail.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @final
 */
/*final*/ class SimpleLoader implements LoaderInterface
{
    use IsAServiceTrait;

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
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
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
