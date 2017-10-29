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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Purger;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @final
 */
/*final*/ class ModelPurger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    private $migrator;
    private $migrationPath;
    private $repository;

    public function __construct(MigrationRepositoryInterface $repository, string $migrationPath, Migrator $migrator)
    {
        $this->migrator = $migrator;
        $this->migrationPath = $migrationPath;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface
    {
        if (PurgeMode::createTruncateMode() == $mode) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot purge database in truncate mode with "%s" (not supported).',
                    __CLASS__
                )
            );
        }

        return new self($this->repository, $this->migrationPath, $this->migrator);
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $this->migrator->reset([$this->migrationPath]);

        if (false === $this->repository->repositoryExists()) {
            $this->repository->createRepository();
        }

        $this->migrator->run([$this->migrationPath]);
    }
}
