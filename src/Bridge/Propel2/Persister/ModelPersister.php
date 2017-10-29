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

namespace Fidry\AliceDataFixtures\Bridge\Propel2\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @final
 */
/*final*/ class ModelPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private $connection;

    /**
     * @var Model[]
     */
    private $persistedModels = [];

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        if (false === $object instanceof ActiveRecordInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected object to be an instance of "%s", got "%s" instead.',
                    ActiveRecordInterface::class,
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
        $models = $this->persistedModels;

        $this->connection->transaction(
            function () use ($models) {
                foreach ($models as $model) {
                    $model->save();
                }
            }
        );

        $this->persistedModels = [];
    }
}
