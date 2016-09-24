<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Nelmio\Alice\NotClonableTrait;

/**
 * Loader decorating another loader to purge the database before loading.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class PurgerLoader implements LoaderInterface
{
    use NotClonableTrait;

    const PURGE_MODE_DELETE = 1;
    const PURGE_MODE_TRUNCATE = 2;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var PurgerFactoryInterface
     */
    private $purgerFactory;

    /**
     * @var PurgerInterface
     */
    private $purger;

    public function __construct(
        LoaderInterface $decoratedLoader,
        PurgerFactoryInterface $purgerFactory,
        PurgerInterface $purger
    ) {
        $this->loader = $decoratedLoader;
        $this->purgerFactory = $purgerFactory;
        $this->purger = $purger;
    }

    /**
     * Pre process, persist and post process each object loaded.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], int $purgeMode = null): array
    {
        $purger = $this->createPurger($this->purgerFactory, $this->purger, $purgeMode);
        $purger->purge();

        return $this->loader->load($fixturesFiles, $parameters, $objects);
    }

    private function createPurger(
        PurgerFactoryInterface $purgerFactory,
        PurgerInterface $purger,
        int $purgeMode = null
    ): PurgerInterface
    {
        if (null === $purgeMode) {
            return $purger;
        }

        if (self::PURGE_MODE_DELETE === $purgeMode) {
            return $purgerFactory->withDeletePurgeMode($purger);
        }

        if (self::PURGE_MODE_TRUNCATE === $purgeMode) {
            return $purgerFactory->withTruncatePurgeMode($purger);
        }

        throw new \InvalidArgumentException(
            sprintf('Unknown purge mode "%d"', $purgeMode)
        );
    }
}
