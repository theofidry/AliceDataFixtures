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
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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

    private static $PURGE_MAPPING;

    private $loader;
    private $purgerFactory;
    private $defaultPurgeMode;
    private $logger;

    public function __construct(
        LoaderInterface $decoratedLoader,
        PurgerFactoryInterface $purgerFactory,
        string $defaultPurgeMode,
        LoggerInterface $logger = null
    ) {
        if (null === self::$PURGE_MAPPING) {
            self::$PURGE_MAPPING = [
                'delete' => PurgeMode::createDeleteMode(),
                'truncate' => PurgeMode::createTruncateMode(),
                'no_purge' => PurgeMode::createNoPurgeMode(),
            ];
        }

        $this->loader = $decoratedLoader;
        $this->purgerFactory = $purgerFactory;

        if (false === in_array($defaultPurgeMode, ['delete', 'truncate', 'no_purge'], true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown purge mode "%s". Use "delete", "truncate" or "no_purge".',
                    $defaultPurgeMode
                )
            );
        }

        $this->defaultPurgeMode = self::$PURGE_MAPPING[$defaultPurgeMode];
        $this->logger = $logger ?? new NullLogger();
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

        foreach (self::$PURGE_MAPPING as $string => $mode) {
            if ($mode == $this->defaultPurgeMode) {
                $defaultPurgeMode = $string;
                break;
            }
        }

        return new self($loader, $this->purgerFactory, $defaultPurgeMode, $this->logger);
    }

    /**
     * Pre process, persist and post process each object loaded.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        if (null === $purgeMode) {
            $purgeMode = $this->defaultPurgeMode;
        }

        if ($purgeMode != PurgeMode::createNoPurgeMode()) {
            $this->logger->info(
                sprintf(
                    'Purging database with purge mode "%s".',
                    (string) $purgeMode
                )
            );

            $purger = $this->purgerFactory->create($purgeMode);
            $purger->purge();
        }

        return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
    }
}
