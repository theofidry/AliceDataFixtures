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
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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

    private $filesLoader;
    private $logger;

    public function __construct(FilesLoaderInterface $fileLoader, LoggerInterface $logger = null)
    {
        $this->filesLoader = $fileLoader;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Loads each file one after another.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        $this->logger->info('Loading fixtures.');

        return $this->filesLoader->loadFiles($fixturesFiles, $parameters, $objects)->getObjects();
    }
}
