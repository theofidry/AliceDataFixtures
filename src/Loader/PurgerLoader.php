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
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Loader decorating another loader to purge the database before loading.
 *
 * @final
 */
/*final*/ class PurgerLoader implements LoaderInterface, PersisterAwareInterface
{
    use IsAServiceTrait;

    public const PURGE_MODE_DELETE = 1;
    public const PURGE_MODE_TRUNCATE = 2;

    private $loader;
    private $purgerFactory;

    public function __construct(
        LoaderInterface $decoratedLoader,
        PurgerFactoryInterface $purgerFactory
    ) {
        $this->loader = $decoratedLoader;
        $this->purgerFactory = $purgerFactory;
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

        return new self($loader, $this->purgerFactory);
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

        if ($purgeMode != PurgeMode::createNoPurgeMode()) {
            $purger = $this->purgerFactory->create($purgeMode);
            $purger->purge();
        }

        return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
    }
}
