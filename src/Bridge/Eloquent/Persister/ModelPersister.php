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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @final
 */
/*final*/ class ModelPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private $databaseManager;

    /**
     * @var Model[]
     */
    private $persistedModels = [];

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        if (false === $object instanceof Model) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected object to be an instance of "%s", got "%s" instead.',
                    Model::class,
                    get_class($object)
                )
            );
        }

        $this->persistedModels[] = $object;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $persistModels = function () {
            array_map(
                function (Model $model): void {
                    $model->push();
                },
                $this->persistedModels
            );
        };

        $this->databaseManager->connection()->transaction($persistModels);

        $this->persistedModels = [];
    }
}
