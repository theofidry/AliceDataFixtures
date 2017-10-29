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

use Fidry\AliceDataFixtures\Exception\MaxPassReachedException;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use InvalidArgumentException;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException;

/**
 * Alternative to {@se SimpleLoader} to load the files in a smarter way.
 *
 * @final
 *
 * @deprecated As of nelmio/alice 3.1.0 this class is unneeded. Will be removed in future versions.
 */
/*final*/ class MultiPassLoader implements LoaderInterface
{
    use IsAServiceTrait;

    private $loader;
    private $maxPass;

    public function __construct(FileLoaderInterface $fileLoader, int $maxPass = 15)
    {
        if ($maxPass <= 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'The maximum number of pass done to load multiple files is expected to be an integer superior'
                    .' or equal to 1. Got "%d" instead.',
                    $maxPass
                )
            );
        }

        $this->loader = $fileLoader;
        $this->maxPass = $maxPass;
    }

    /**
     * Try to load the set of files in multiple passes by loading as many files as possible. The result of each loading
     * is passed to the next. After the first pass, if some files could not be reloaded, another attempt is made until
     * all files are loaded or the maximum number of pass is reached.
     *
     * {@inheritdoc}
     *
     * @throws MaxPassReachedException
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        $errorTracker = new ErrorTracker();
        $filesTracker = new FileTracker(...$fixturesFiles);
        $attempts = 0;
        $set = new ObjectSet(
            new ParameterBag($parameters),
            new ObjectBag($objects)
        );
        while (true) {
            $set = $this->tryToLoadFiles($filesTracker, $errorTracker, $set);
            if ($filesTracker->allFilesHaveBeenLoaded()) {
                break;
            }

            if ($this->maxPass <= $attempts) {
                throw MaxPassReachedException::createForLimit($this->maxPass, $filesTracker, $errorTracker);
            }
            ++$attempts;
        }

        return $set->getObjects();
    }

    private function tryToLoadFiles(FileTracker $fileTracker, ErrorTracker $errorStack, ObjectSet $set): ObjectSet
    {
        $files = $fileTracker->getUnloadedFiles();
        foreach ($files as $file) {
            try {
                $set = $this->loader->loadFile($file, $set->getParameters(), $set->getObjects());
                $fileTracker->markAsLoaded($file);
            } catch (UnresolvableValueDuringGenerationException $exception) {
                $errorStack->register($file, $exception);
            }
        }

        return $set;
    }
}
