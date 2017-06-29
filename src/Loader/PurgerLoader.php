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
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Loader decorating another loader to purge the database before loading.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @final
 */
/*final*/ class PurgerLoader implements LoaderInterface
{
    use IsAServiceTrait;

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

    public function __construct(
        LoaderInterface $decoratedLoader,
        PurgerFactoryInterface $purgerFactory
    ) {
        $this->loader = $decoratedLoader;
        $this->purgerFactory = $purgerFactory;
    }

    /**
     * Pre process, persist and post process each object loaded.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        if (null === $purgeMode) {
            $purgeMode = PurgeMode::createDeleteMode();
        }

        $purger = $this->purgerFactory->create($purgeMode);
        $purger->purge();

        return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
    }
}
